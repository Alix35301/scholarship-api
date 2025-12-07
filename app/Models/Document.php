<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
        'disbursement_id',
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
        'description',
    ];

    public function application()
    {
        return $this->belongsTo(ScholarshipApplication::class, 'application_id');
    }

    public function disbursement()
    {
        return $this->belongsTo(Disbursement::class, 'disbursement_id');
    }
}

