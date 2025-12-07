<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Disbursement extends Model
{
    use HasFactory;

    protected $fillable = [
        'planned_disbursement_id',
        'amount',
        'date',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'date' => 'date',
        ];
    }

    public function plannedDisbursement()
    {
        return $this->belongsTo(PlannedDisbursement::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}

