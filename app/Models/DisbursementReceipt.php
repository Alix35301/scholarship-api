<?php

namespace App\Models;

use App\Enums\ReceiptStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class DisbursementReceipt extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'disbursement_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'description',
        'status',
        'rejection_reason',
        'verified_by',
        'verified_at',
    ];

    protected $casts = [
        'status' => ReceiptStatus::class,
        'verified_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'rejection_reason', 'verified_by', 'verified_at'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function disbursement()
    {
        return $this->belongsTo(Disbursement::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}

