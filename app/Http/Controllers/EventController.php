<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EventController extends Controller
{
    public function index()
    {
        return Inertia::render('Events/Index', [
            'events' => Event::latest()
                ->paginate(10)
        ]);
    }

    public function create()
    {
        return Inertia::render('Events/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'budget' => 'required|numeric|min:0',
        ]);

        $event = Event::create([
            ...$validated,
            'status' => 'upcoming',
        ]);

        return redirect()->route('events.show', $event)
            ->with('message', 'Event created successfully');
    }

    public function show(Event $event)
    {
        $event->load(['products', 'sales' => function($query) {
            $query->with(['items.product', 'seller'])
                ->latest()
                ->take(5);
        }, 'expenses' => function($query) {
            $query->with('creator')
                ->latest()
                ->take(5);
        }]);

        // Update event status based on dates
        $event->updateStatus();

        // Calculate latest totals
        $event->calculateTotals();

        return Inertia::render('Events/Show', [
            'event' => array_merge($event->toArray(), [
                'net_profit' => $event->net_profit,
                'total_sales_count' => $event->total_sales_count,
                'total_products_count' => $event->total_products_count,
                'foc_value' => $event->foc_value,
            ])
        ]);
    }

    public function edit(Event $event)
    {
        return Inertia::render('Events/Edit', [
            'event' => $event
        ]);
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'location' => 'nullable|string|max:255',
            'budget' => 'required|numeric|min:0',
            'status' => ['required', 'in:upcoming,ongoing,completed,cancelled'],
        ]);

        $event->update($validated);

        return redirect()->route('events.show', $event)
            ->with('message', 'Event updated successfully');
    }

    public function destroy(Event $event)
    {
        // Check if event has any sales
        if ($event->sales()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete event with existing sales']);
        }

        $event->products()->delete();
        $event->delete();

        return redirect()->route('events.index')
            ->with('message', 'Event deleted successfully');
    }

    public function dashboard(Request $request, Event $event)
    {
        $dateFilter = $request->input('date');
        $startDate = null;
        $endDate = null;

        // Set date range based on filter
        if ($dateFilter === 'today') {
            $startDate = now()->startOfDay();
            $endDate = now()->endOfDay();
        } elseif ($dateFilter === 'yesterday') {
            $startDate = now()->subDay()->startOfDay();
            $endDate = now()->subDay()->endOfDay();
        } elseif ($dateFilter === 'this_week') {
            $startDate = now()->startOfWeek();
            $endDate = now()->endOfWeek();
        } elseif ($dateFilter === 'this_month') {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
        } elseif ($dateFilter === 'custom' && $request->filled(['start_date', 'end_date'])) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
        }

        $dateQuery = function ($query) use ($startDate, $endDate) {
            if ($startDate && $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }
        };

        $salesByStatus = $event->sales()
            ->when($dateFilter, $dateQuery)
            ->select('status', DB::raw('count(*) as count'), DB::raw('sum(total_amount) as total'))
            ->groupBy('status')
            ->get();

        // Calculate filtered totals
        $filteredTotals = [
            'revenue' => $event->sales()
                ->where('status', 'completed')
                ->when($dateFilter, $dateQuery)
                ->sum('total_amount') ?? 0,
            'expense' => $event->expenses()
                // ->where('status', 'approved')
                ->when($dateFilter, $dateQuery)
                ->sum('amount') ?? 0,
            'net_profit' => 0
        ];

        $filteredTotals['net_profit'] = $filteredTotals['revenue'] - $filteredTotals['expense'];


        return Inertia::render('Events/Dashboard', [
            'event' => array_merge($event->load(['expenses' => function($query) use ($dateFilter) {
                $query->with('creator')
                    ->latest()
                    ->take(5)
                    ->when($dateFilter, function($q) use ($dateFilter) {
                        $this->applyDateFilter($q, $dateFilter);
                    });
            }])->toArray(), [
                'net_profit' => $event->net_profit,
                'total_sales_count' => $event->total_sales_count,
                'total_products_count' => $event->total_products_count,
                'foc_value' => $event->foc_value,
            ]),
            'salesByStatus' => $salesByStatus,
            'topProducts' => $event->topProducts($dateFilter),
            'topSellers' => $event->topSellers($dateFilter),
            'recentSales' => $event->recentSales($dateFilter),
            'filteredTotals' => $filteredTotals,
            'currentDateFilter' => $dateFilter,
        ]);
    }

    public function report(Event $event)
    {
        $event->load(['products', 'sales.items.product', 'sales.seller', 'expenses.creator']);

        // Update event status and calculate totals
        $event->updateStatus();
        $event->calculateTotals();

        // Get sales summary
        $salesSummary = $event->sales()
            ->where('status', 'completed')
            ->select(
                DB::raw('COUNT(*) as total_sales'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('SUM(total_discount) as total_discounts'),
                DB::raw('SUM(total_foc_amount) as total_foc_value')
            )
            ->first();

        // Get expense summary
        $expenseSummary = $event->expenses()
            ->where('status', 'approved')
            ->select(
                DB::raw('COUNT(*) as total_expenses'),
                DB::raw('SUM(amount) as total_amount'),
                DB::raw('COUNT(CASE WHEN type = "shared" THEN 1 END) as shared_expenses_count'),
                DB::raw('SUM(CASE WHEN type = "shared" THEN amount ELSE 0 END) as shared_expenses_amount')
            )
            ->first();

        // Get seller performance
        $sellerPerformance = $event->sales()
            ->where('status', 'completed')
            ->with('seller')
            ->select(
                'seller_id',
                DB::raw('COUNT(*) as total_sales'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('SUM(total_discount) as total_discounts'),
                DB::raw('SUM(total_foc_amount) as total_foc_value')
            )
            ->groupBy('seller_id')
            ->get();

        // Get product performance
        $productPerformance = $event->products()
            ->withSum(['sales_items as total_quantity' => function($query) {
                $query->whereHas('sale', function($q) {
                    $q->where('status', 'completed');
                });
            }], 'quantity')
            ->withSum(['sales_items as total_revenue' => function($query) {
                $query->whereHas('sale', function($q) {
                    $q->where('status', 'completed');
                });
            }], 'subtotal')
            ->withSum(['sales_items as foc_quantity' => function($query) {
                $query->where('is_foc', true)
                    ->whereHas('sale', function($q) {
                        $q->where('status', 'completed');
                    });
            }], 'quantity')
            ->orderByDesc('total_revenue')
            ->get();

        // Get FOC analysis
        $focAnalysis = $event->sales()
            ->where('status', 'completed')
            ->with(['items' => function($query) {
                $query->where('is_foc', true)->with('product');
            }])
            ->has('items')
            ->get()
            ->map(function($sale) {
                return [
                    'sale_id' => $sale->id,
                    'seller' => $sale->seller->name,
                    'date' => $sale->created_at,
                    'items' => $sale->items->map(function($item) {
                        return [
                            'product' => $item->product->name,
                            'quantity' => $item->quantity,
                            'value' => $item->quantity * $item->unit_price,
                            'reason' => $item->foc_reason
                        ];
                    })
                ];
            });

        return Inertia::render('Events/Report', [
            'event' => array_merge($event->toArray(), [
                'net_profit' => $event->net_profit,
                'total_sales_count' => $event->total_sales_count,
                'total_products_count' => $event->total_products_count,
                'foc_value' => $event->foc_value,
            ]),
            'salesSummary' => $salesSummary,
            'expenseSummary' => $expenseSummary,
            'sellerPerformance' => $sellerPerformance,
            'productPerformance' => $productPerformance,
            'focAnalysis' => $focAnalysis
        ]);
    }

    protected function applyDateFilter($query, $dateFilter)
    {
        if ($dateFilter === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($dateFilter === 'yesterday') {
            $query->whereDate('created_at', today()->subDay());
        } elseif ($dateFilter === 'this_week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($dateFilter === 'this_month') {
            $query->whereMonth('created_at', now()->month)
                  ->whereYear('created_at', now()->year);
        } elseif ($dateFilter === 'custom' && request()->filled(['start_date', 'end_date'])) {
            $query->whereBetween('created_at', [
                request()->input('start_date') . ' 00:00:00',
                request()->input('end_date') . ' 23:59:59'
            ]);
        }
    }
}
