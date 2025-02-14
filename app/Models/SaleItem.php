<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SaleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'product_id',
        'quantity',
        'unit_price',
        'discount',
        'subtotal',
        'is_foc',
        'foc_reason',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'discount' => 'decimal:2',
        'subtotal' => 'decimal:2',
        'is_foc' => 'boolean',
    ];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if ($item->is_foc) {
                $item->subtotal = 0;
                $item->discount = 0;
            } else {
                $item->subtotal = ($item->quantity * $item->unit_price) - $item->discount;
            }
        });

        static::updating(function ($item) {
            if ($item->isDirty(['quantity', 'unit_price', 'discount', 'is_foc'])) {
                if ($item->is_foc) {
                    $item->subtotal = 0;
                    $item->discount = 0;
                } else {
                    $item->subtotal = ($item->quantity * $item->unit_price) - $item->discount;
                }
            }
        });

        static::saved(function ($item) {
            $item->sale->calculateTotals();
        });
    }

    public function getOriginalValueAttribute()
    {
        return $this->quantity * $this->unit_price;
    }
}
