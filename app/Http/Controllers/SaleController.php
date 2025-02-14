<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\User;
use App\Models\Product;
use App\Models\Event;
use App\Http\Requests\SaleRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Event $event)
    {
        return Inertia::render('Sales/Index', [
            'event' => $event,
            'sales' => $event->sales()
                ->with(['items.product', 'seller'])
                ->latest()
                ->paginate(10)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Event $event)
    {
        return Inertia::render('Sales/Create', [
            'event' => $event,
            'products' => $event->products()->where('status', 'active')->get(),
            'buyers' => User::all()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, Event $event)
    {
        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.is_foc' => ['boolean'],
            'items.*.foc_reason' => ['required_if:items.*.is_foc,true', 'nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            DB::beginTransaction();

            // Create the sale
            $sale = $event->sales()->create([
                'seller_id' => Auth::id(),
                'notes' => $request->notes,
                'total_amount' => 0, // Will be calculated from items
                'total_discount' => 0,
                'total_foc_amount' => 0,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Add items and check stock
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Not enough stock for product: {$product->name}");
                }

                // Create sale item
                $sale->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount' => $item['discount'] ?? 0,
                    'is_foc' => $item['is_foc'] ?? false,
                    'foc_reason' => $item['foc_reason'],
                    'subtotal' => $item['is_foc'] ? 0 : ($item['quantity'] * $item['unit_price']) - ($item['discount'] ?? 0),
                ]);

                // Update product stock
                $product->decrement('stock', $item['quantity']);
            }

            // Calculate totals
            $sale->calculateTotals();

            DB::commit();

            return redirect()->route('events.sales.index', $event)
                ->with('message', 'Sale created successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event, Sale $sale)
    {
        return Inertia::render('Sales/Show', [
            'event' => $event,
            'sale' => $sale->load(['items.product', 'seller'])
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        $request->validate([
            'status' => ['required', Rule::in(['pending', 'completed', 'cancelled'])]
        ]);

        try {
            DB::beginTransaction();

            if ($request->status === 'cancelled' && $sale->status !== 'cancelled') {
                // Return stock for all items
                foreach ($sale->items as $item) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            if ($sale->status === 'cancelled' && $request->status !== 'cancelled') {
                // Check and deduct stock again
                foreach ($sale->items as $item) {
                    if ($item->product->stock < $item->quantity) {
                        throw new \Exception("Not enough stock for product: {$item->product->name}");
                    }
                    $item->product->decrement('stock', $item->quantity);
                }
            }

            $sale->update([
                'status' => $request->status,
                'completed_at' => $request->status === 'completed' ? now() : null
            ]);

            DB::commit();

            return back()->with('message', 'Sale status updated successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event, Sale $sale)
    {
        try {
            DB::beginTransaction();

            // If the sale is not cancelled, return stock for all items
            if ($sale->status !== 'cancelled') {
                foreach ($sale->items as $item) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            $sale->items()->delete();
            $sale->delete();

            DB::commit();

            return redirect()->route('events.sales.index', $event)
                ->with('message', 'Sale deleted successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update the status of a sale
     */
    public function updateStatus(Request $request, Sale $sale)
    {
        return $this->update($request, $sale);
    }
}
