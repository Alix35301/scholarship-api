<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class SellerController extends Controller
{
    public function index()
    {
        return Inertia::render('Sellers/Index', [
            'sellers' => User::query()
                ->where('role', 'seller')
                ->withCount(['sales' => function($query) {
                    $query->where('status', 'completed');
                }])
                ->withSum(['sales as total_revenue' => function($query) {
                    $query->where('status', 'completed');
                }], 'total_amount')
                ->latest()
                ->paginate(10)
        ]);
    }

    public function create()
    {
        return Inertia::render('Sellers/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => 'nullable|string|max:20',
            'status' => ['required', 'in:active,inactive'],
            'commission_rate' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        $user = User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
            'role' => 'seller',
        ]);

        return redirect()->route('sellers.show', $user)
            ->with('message', 'Seller created successfully');
    }

    public function show(User $seller)
    {
        if (!$seller->isSeller()) {
            abort(404);
        }

        $seller->load(['sales' => function($query) {
            $query->with('items.product')
                ->where('status', 'completed')
                ->latest()
                ->take(5);
        }]);

        return Inertia::render('Sellers/Show', [
            'seller' => array_merge($seller->toArray(), [
                'total_sales' => $seller->total_sales,
                'total_revenue' => $seller->total_revenue,
                'total_commission' => $seller->total_commission,
            ])
        ]);
    }

    public function edit(User $seller)
    {
        if (!$seller->isSeller()) {
            abort(404);
        }

        return Inertia::render('Sellers/Edit', [
            'seller' => $seller
        ]);
    }

    public function update(Request $request, User $seller)
    {
        if (!$seller->isSeller()) {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $seller->id,
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'phone' => 'nullable|string|max:20',
            'status' => ['required', 'in:active,inactive'],
            'commission_rate' => 'required|numeric|min:0|max:100',
            'notes' => 'nullable|string',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $seller->update($validated);

        return redirect()->route('sellers.show', $seller)
            ->with('message', 'Seller updated successfully');
    }

    public function destroy(User $seller)
    {
        if (!$seller->isSeller()) {
            abort(404);
        }

        // Check if seller has any sales
        if ($seller->sales()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete seller with existing sales']);
        }

        $seller->delete();

        return redirect()->route('sellers.index')
            ->with('message', 'Seller deleted successfully');
    }
}
