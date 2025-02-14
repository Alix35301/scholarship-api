<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'amount',
        'date',
        'event_id',
        'created_by',
        'user_id',
        'type',
        'status',
        'rejection_reason',
        'receipt_path'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function allocations()
    {
        return $this->hasMany(ExpenseAllocation::class);
    }

    public function sharedUsers()
    {
        return $this->belongsToMany(User::class, 'expense_allocations')
            ->withPivot(['share_percentage', 'share_amount'])
            ->withTimestamps();
    }

    public function isShared()
    {
        return $this->type === 'shared';
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function getTotalAllocatedAmountAttribute()
    {
        return $this->allocations->sum('share_amount');
    }

    public function getTotalAllocatedPercentageAttribute()
    {
        return $this->allocations->sum('share_percentage');
    }
}
