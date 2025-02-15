<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\SellerController;
use App\Http\Controllers\ExpenseController;
use App\Models\Sale;
use App\Models\Product;
use App\Models\SaleItem;
use App\Models\Event;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

Route::get('/', function (Request $request) {
    Log::info('Root route headers:', [
        'headers' => $request->headers->all()
    ]);
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard now shows list of events
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard', [
            'events' => Event::latest()
                ->get()
                ->map(function($event) {
                    return [
                        'id' => $event->id,
                        'name' => $event->name,
                        'status' => $event->status,
                        'start_date' => $event->start_date,
                        'end_date' => $event->end_date,
                        'organizer' => $event->organizer,
                        'total_sales' => $event->total_sales_count,
                        'total_revenue' => $event->total_revenue,
                        'net_profit' => $event->net_profit,
                    ];
                })
        ]);
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Seller management routes (admin only)
    Route::middleware(['admin'])->group(function () {
        Route::resource('sellers', SellerController::class);
    });

    // Event routes
    Route::resource('events', EventController::class);
    Route::get('events/{event}/dashboard', [EventController::class, 'dashboard'])->name('events.dashboard');
    Route::get('events/{event}/report', [EventController::class, 'report'])->name('events.report');

    // Product routes (scoped to events)
    Route::get('events/{event}/products', [ProductController::class, 'index'])->name('events.products.index');
    Route::get('events/{event}/products/create', [ProductController::class, 'create'])->name('events.products.create');
    Route::post('events/{event}/products', [ProductController::class, 'store'])->name('events.products.store');
    Route::get('events/{event}/products/{product}', [ProductController::class, 'show'])->name('events.products.show');
    Route::get('events/{event}/products/{product}/edit', [ProductController::class, 'edit'])->name('events.products.edit');
    Route::put('events/{event}/products/{product}', [ProductController::class, 'update'])->name('events.products.update');
    Route::delete('events/{event}/products/{product}', [ProductController::class, 'destroy'])->name('events.products.destroy');

    // Sales routes (scoped to events)
    Route::get('events/{event}/sales', [SaleController::class, 'index'])->name('events.sales.index');
    Route::get('events/{event}/sales/create', [SaleController::class, 'create'])->name('events.sales.create');
    Route::post('events/{event}/sales', [SaleController::class, 'store'])->name('events.sales.store');
    Route::get('events/{event}/sales/{sale}', [SaleController::class, 'show'])->name('events.sales.show');
    Route::get('events/{event}/sales/{sale}/edit', [SaleController::class, 'edit'])->name('events.sales.edit');
    Route::put('events/{event}/sales/{sale}', [SaleController::class, 'update'])->name('events.sales.update');
    Route::delete('events/{event}/sales/{sale}', [SaleController::class, 'destroy'])->name('events.sales.destroy');
    Route::patch('events/{event}/sales/{sale}/status', [SaleController::class, 'updateStatus'])->name('events.sales.update-status');

    // Expense routes (scoped to events)
    Route::resource('events.expenses', ExpenseController::class);
    Route::post('events/{event}/expenses/import', [ExpenseController::class, 'import'])->name('events.expenses.import');
});


require __DIR__.'/auth.php';
