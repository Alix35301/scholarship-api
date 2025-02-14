<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
        'status',
        'location',
        'budget',
        'total_revenue',
        'total_expense'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'total_revenue' => 'decimal:2',
        'total_expense' => 'decimal:2'
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function calculateTotals()
    {
        $this->total_revenue = $this->sales()
            ->where('status', 'completed')
            ->sum('total_amount');

        $this->total_expense = $this->expenses()
            ->where('status', 'approved')
            ->sum('amount');

        $this->save();
    }

    public function getNetProfitAttribute()
    {
        return $this->total_revenue - $this->total_expense;
    }

    public function getTotalSalesCountAttribute()
    {
        return $this->sales()->where('status', 'completed')->count();
    }

    public function getTotalProductsCountAttribute()
    {
        return $this->products()->count();
    }

    public function getFocValueAttribute()
    {
        return $this->sales()->sum('total_foc_amount');
    }

    public function updateStatus()
    {
        $now = now()->startOfDay();

        if ($this->end_date && $now->gt($this->end_date)) {
            $this->status = 'completed';
        } elseif ($now->gte($this->start_date)) {
            $this->status = 'ongoing';
        } else {
            $this->status = 'upcoming';
        }

        $this->save();
    }

    public function topProducts($dateFilter = null)
    {
        $query = $this->products()
            ->withSum(['sales_items as total_quantity' => function($query) use ($dateFilter) {
                $query->whereHas('sale', function($q) use ($dateFilter) {
                    $q->where('status', 'completed');
                    if ($dateFilter) {
                        $this->applyDateFilter($q, $dateFilter);
                    }
                });
            }], 'quantity')
            ->withSum(['sales_items as total_revenue' => function($query) use ($dateFilter) {
                $query->whereHas('sale', function($q) use ($dateFilter) {
                    $q->where('status', 'completed');
                    if ($dateFilter) {
                        $this->applyDateFilter($q, $dateFilter);
                    }
                });
            }], 'subtotal')
            ->orderByDesc('total_revenue')
            ->take(5);

        return $query->get();
    }

    public function topSellers($dateFilter = null)
    {
        $query = $this->sales()
            ->where('status', 'completed')
            ->with('seller')
            ->select('seller_id')
            ->selectRaw('COUNT(*) as total_sales')
            ->selectRaw('SUM(total_amount) as total_revenue')
            ->groupBy('seller_id')
            ->orderByDesc('total_revenue')
            ->take(5);

        if ($dateFilter) {
            $this->applyDateFilter($query, $dateFilter);
        }

        return $query->get();
    }

    public function recentSales($dateFilter = null)
    {
        $query = $this->sales()
            ->with(['items.product', 'seller'])
            ->latest()
            ->take(5);

        if ($dateFilter) {
            $this->applyDateFilter($query, $dateFilter);
        }

        return $query->get();
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
