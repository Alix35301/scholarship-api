<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Seller extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'status',
        'commission_rate',
        'notes'
    ];

    protected $casts = [
        'commission_rate' => 'decimal:2',
        'status' => 'string'
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function getTotalSalesAttribute()
    {
        return $this->sales()->where('status', 'completed')->count();
    }

    public function getTotalRevenueAttribute()
    {
        return $this->sales()->where('status', 'completed')->sum('total_amount');
    }

    public function getTotalCommissionAttribute()
    {
        return $this->total_revenue * ($this->commission_rate / 100);
    }
}
