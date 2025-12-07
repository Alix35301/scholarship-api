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
        return $this->hasManyThrough(
            ScholarshipReceipt::class,
            ScholarshipApplication::class,
            'scholarship_id',     // Foreign key on applications table
            'application_id',     // Foreign key on receipts table
            'id',                 // Local key on scholarships table
            'id'                  // Local key on applications table
        );
    }

    public function getTotalApprovedAmountAttribute()
    {
        $approvedCount = $this->applications()
            ->where('status', 'approved')
            ->count();
        
        return $approvedCount * $this->amount;
    }

    public function getTotalReceiptsAmountAttribute()
    {
        return $this->receipts()
            ->where('scholarship_receipts.status', 'verified')
            ->sum('scholarship_receipts.amount') ?? 0;
    }

    public function getRemainingBudgetAttribute()
    {
        return $this->budget - $this->total_receipts_amount;
    }
}

