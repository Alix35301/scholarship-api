<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Event;
use App\Http\Requests\ProductRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Event $event)
    {
        return Inertia::render('Events/Products/Index', [
            'event' => $event,
            'products' => $event->products()
                ->with('owner')
                ->latest()
                ->paginate(10)
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Event $event)
    {
        return Inertia::render('Events/Products/Create', [
            'event' => $event,
            'sellers' => User::where('role', 'seller')
                ->where('status', 'active')
                ->select('id', 'name', 'email')
                ->get()
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request, Event $event)
    {
        $product = $event->products()->create([
            ...$request->validated(),
            'owner_id' => $request->input('owner_id', Auth::id()),
        ]);

        return redirect()->route('events.products.index', $event)
            ->with('message', 'Product added successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event, Product $product)
    {
        return Inertia::render('Events/Products/Show', [
            'event' => $event,
            'product' => $product->load('owner')
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event, Product $product)
    {
        return Inertia::render('Events/Products/Edit', [
            'event' => $event,
            'product' => $product
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, Event $event, Product $product)
    {
        $product->update($request->validated());

        return redirect()->route('events.products.index', $event)
            ->with('message', 'Product updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event, Product $product)
    {
        // Check if product has any sales
        if ($product->sales()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete product with existing sales']);
        }

        $product->delete();

        return redirect()->route('events.products.index', $event)
            ->with('message', 'Product deleted successfully');
    }
}
