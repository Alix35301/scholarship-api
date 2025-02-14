<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Expense;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ExpenseController extends Controller
{
    public function index(Event $event)
    {
        $expenses = $event->expenses()
            ->with(['creator', 'sharedUsers', 'user'])
            ->latest()
            ->paginate(10);

        $sellers = User::where('role', 'seller')->where('status', 'active')->get();

        return Inertia::render('Events/Expenses/Index', [
            'event' => $event,
            'expenses' => $expenses,
            'sellers' => $sellers
        ]);
    }

    public function create(Event $event)
    {
        return Inertia::render('Events/Expenses/Create', [
            'event' => $event,
            'sellers' => User::where('role', 'seller')
                ->where('status', 'active')
                ->get()
        ]);
    }

    public function store(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'type' => ['required', 'in:individual,shared'],
            'receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'user_id' => 'required_if:type,individual|exists:users,id',
            'allocations' => 'required_if:type,shared|array',
            'allocations.*.user_id' => 'required_if:type,shared|exists:users,id',
            'allocations.*.share_percentage' => 'required_if:type,shared|numeric|min:0|max:100',
        ]);

        try {
            DB::beginTransaction();

            // Handle receipt upload
            $receiptPath = null;
            if ($request->hasFile('receipt')) {
                $receiptPath = $request->file('receipt')->store('receipts', 'public');
            }

            // Create expense
            $expense = $event->expenses()->create([
                ...$validated,
                'created_by' => Auth::id(),
                'receipt_path' => $receiptPath,
                'status' => 'pending'
            ]);

            // Handle allocations for shared expenses
            if ($validated['type'] === 'shared') {
                $totalPercentage = 0;
                foreach ($validated['allocations'] as $allocation) {
                    $totalPercentage += $allocation['share_percentage'];
                    $shareAmount = ($validated['amount'] * $allocation['share_percentage']) / 100;

                    $expense->allocations()->create([
                        'user_id' => $allocation['user_id'],
                        'share_percentage' => $allocation['share_percentage'],
                        'share_amount' => $shareAmount
                    ]);
                }

                if ($totalPercentage !== 100.0) {
                    throw new \Exception('Total share percentage must equal 100%');
                }
            }

            DB::commit();

            return redirect()->route('events.expenses.index', $event)
                ->with('message', 'Expense added successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            if ($receiptPath) {
                Storage::disk('public')->delete($receiptPath);
            }
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function show(Event $event, Expense $expense)
    {
        $expense->load(['creator', 'sharedUsers', 'allocations.user']);

        return Inertia::render('Events/Expenses/Show', [
            'event' => $event,
            'expense' => $expense
        ]);
    }

    public function update(Request $request, Event $event, Expense $expense)
    {
        if (!$expense->isPending()) {
            return back()->withErrors(['error' => 'Can only update pending expenses']);
        }

        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'rejection_reason' => 'required_if:status,rejected|nullable|string',
        ]);

        $expense->update($validated);

        return redirect()->route('events.expenses.show', [$event, $expense])
            ->with('message', 'Expense status updated successfully');
    }

    public function destroy(Event $event, Expense $expense)
    {
        if (!$expense->isPending()) {
            return back()->withErrors(['error' => 'Can only delete pending expenses']);
        }

        if ($expense->receipt_path) {
            Storage::disk('public')->delete($expense->receipt_path);
        }

        $expense->delete();

        return redirect()->route('events.expenses.index', $event)
            ->with('message', 'Expense deleted successfully');
    }

    /**
     * Import expenses from CSV file
     */
    public function import(Request $request, Event $event)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048'
        ]);

        try {
            DB::beginTransaction();

            $file = $request->file('csv_file');
            $path = $file->getRealPath();
            $handle = fopen($path, 'r');

            // Read and validate headers
            $headers = fgetcsv($handle);
            $requiredHeaders = ['title', 'description', 'amount', 'date', 'type', 'seller_ids'];
            $missingHeaders = array_diff($requiredHeaders, $headers);

            if (!empty($missingHeaders)) {
                throw new \Exception('Missing required columns: ' . implode(', ', $missingHeaders));
            }

            // Process each row
            $row = 2; // Start from row 2 (after headers)
            while (($data = fgetcsv($handle)) !== false) {
                $rowData = array_combine($headers, $data);

                // Validate row data
                if (empty($rowData['title'])) {
                    throw new \Exception("Row {$row}: Title is required");
                }
                if (!is_numeric($rowData['amount']) || $rowData['amount'] < 0) {
                    throw new \Exception("Row {$row}: Amount must be a positive number");
                }
                if (!in_array($rowData['type'], ['individual', 'shared'])) {
                    throw new \Exception("Row {$row}: Type must be either 'individual' or 'shared'");
                }
                if (!strtotime($rowData['date'])) {
                    throw new \Exception("Row {$row}: Invalid date format");
                }

                // Validate seller IDs
                $sellerIds = array_filter(array_map('trim', explode(',', $rowData['seller_ids'])));
                if (empty($sellerIds)) {
                    throw new \Exception("Row {$row}: Seller IDs are required");
                }

                $validSellerIds = User::whereIn('id', $sellerIds)
                    ->where('role', 'seller')
                    ->where('status', 'active')
                    ->pluck('id')
                    ->toArray();

                if (count($validSellerIds) !== count($sellerIds)) {
                    throw new \Exception("Row {$row}: One or more invalid seller IDs");
                }

                // Create expense
                $expense = $event->expenses()->create([
                    'title' => $rowData['title'],
                    'description' => $rowData['description'],
                    'amount' => $rowData['amount'],
                    'date' => $rowData['date'],
                    'type' => $rowData['type'],
                    'created_by' => Auth::id(),
                    'user_id' => $rowData['type'] === 'individual' ? $validSellerIds[0] : null,
                    'status' => 'pending'
                ]);

                // Handle allocations for shared expenses
                if ($rowData['type'] === 'shared') {
                    // Calculate equal share for each seller
                    $sharePercentage = 100 / count($sellerIds);
                    $shareAmount = $rowData['amount'] / count($sellerIds);

                    foreach ($sellerIds as $sellerId) {
                        $expense->allocations()->create([
                            'user_id' => $sellerId,
                            'share_percentage' => $sharePercentage,
                            'share_amount' => $shareAmount
                        ]);
                    }
                }

                $row++;
            }

            fclose($handle);
            DB::commit();

            return back()->with('message', 'Expenses imported successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
