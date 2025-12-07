<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlannedDisbursement extends Model
{
    use HasFactory;

    protected $fillable = [
        'award_id',
        'cost_category_id',
        'allocated_amount',
        'remaining_amount',
    ];

    protected function casts(): array
    {
        return [
            'allocated_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
        ];
    }

    public function award()
    {
        return $this->belongsTo(Award::class);
    }

    public function costCategory()
    {
        return $this->belongsTo(CostCategory::class);
    }

    public function disbursements()
    {
        return $this->hasMany(Disbursement::class);
    }
}

