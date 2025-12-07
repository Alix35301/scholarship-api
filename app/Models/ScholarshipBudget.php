<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScholarshipBudget extends Model
{
    use HasFactory;

    protected $fillable = [
        'scholarship_id',
        'cost_category_id',
        'budget',
    ];

    protected function casts(): array
    {
        return [
            'budget' => 'decimal:2',
        ];
    }

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function costCategory()
    {
        return $this->belongsTo(CostCategory::class);
    }
}

