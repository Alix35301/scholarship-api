<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Scholarship extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'amount',
        'budget',
        'status',
        'application_deadline',
        'start_date',
        'end_date',
        'requirements',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'budget' => 'decimal:2',
            'application_deadline' => 'date',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function applications()
    {
        return $this->hasMany(ScholarshipApplication::class);
    }

    public function receipts()
    {
        return $this->hasManyThrough(ScholarshipReceipt::class, ScholarshipApplication::class);
    }

    public function getTotalApprovedAmountAttribute()
    {
        return $this->applications()
            ->where('status', 'approved')
            ->sum('amount') ?? 0;
    }

    public function getTotalReceiptsAmountAttribute()
    {
        return $this->receipts()
            ->where('status', 'verified')
            ->sum('amount') ?? 0;
    }

    public function getRemainingBudgetAttribute()
    {
        return $this->budget - $this->total_receipts_amount;
    }
}

