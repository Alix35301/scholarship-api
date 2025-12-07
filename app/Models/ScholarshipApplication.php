<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class ScholarshipApplication extends Model
{
    use HasFactory, LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'reviewed_by', 'reviewed_at', 'rejection_reason', 'scholarship_id', 'student_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => match($eventName) {
                'created' => 'Application created',
                'updated' => 'Application updated',
                default => "Application {$eventName}",
            });
    }

    protected $fillable = [
        'scholarship_id',
        'student_id',
        'status',
        'application_essay',
        'additional_documents',
        'category_costs',
        'rejection_reason',
        'reviewed_by',
        'applied_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'additional_documents' => 'array',
            'category_costs' => 'array',
            'applied_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function scholarship()
    {
        return $this->belongsTo(Scholarship::class);
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function receipts()
    {
        return $this->hasMany(ScholarshipReceipt::class, 'application_id');
    }

    public function documents()
    {
        return $this->hasMany(ApplicationDocument::class, 'application_id');
    }

    public function award()
    {
        return $this->hasOne(Award::class, 'application_id');
    }

    public function costCategories()
    {
        return $this->belongsToMany(CostCategory::class, 'application_cost_category', 'application_id', 'cost_category_id')
            ->withPivot('amount')
            ->withTimestamps();
    }

    public function getAmountAttribute()
    {
        return $this->scholarship->amount ?? 0;
    }
}

