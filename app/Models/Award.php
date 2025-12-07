<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'application_id',
        'total_amount',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
        ];
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function application()
    {
        return $this->belongsTo(ScholarshipApplication::class);
    }

    public function plannedDisbursements()
    {
        return $this->hasMany(PlannedDisbursement::class);
    }
}

