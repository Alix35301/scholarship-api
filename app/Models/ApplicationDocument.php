<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApplicationDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'application_id',
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
}

