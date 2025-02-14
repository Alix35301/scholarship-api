<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    protected $fillable = [
        'seller_id',
        'buyer_id',
        'total_amount',
        'total_discount',
        'total_foc_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'total_foc_amount' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function regularItems()
    {
        return $this->hasMany(SaleItem::class)->where('is_foc', false);
    }

    public function focItems()
    {
        return $this->hasMany(SaleItem::class)->where('is_foc', true);
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($sale) {
            if (!$sale->seller_id) {
                $sale->seller_id = auth()->id();
            }
        });
    }

    public function calculateTotals()
    {
        $regularItems = $this->items()->where('is_foc', false)->get();
        $focItems = $this->items()->where('is_foc', true)->get();

        $this->total_amount = $regularItems->sum('subtotal');
        $this->total_discount = $regularItems->sum('discount');
        $this->total_foc_amount = $focItems->sum('original_value');

        $this->save();
    }

    public function getTotalItemsAttribute()
    {
        return $this->items->sum('quantity');
    }

    public function getTotalRegularItemsAttribute()
    {
        return $this->regularItems->sum('quantity');
    }

    public function getTotalFocItemsAttribute()
    {
        return $this->focItems->sum('quantity');
    }
}
