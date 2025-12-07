<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DisbursementReceipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'disbursement_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'description',
    ];

    public function disbursement()
    {
        return $this->belongsTo(Disbursement::class);
    }
}

