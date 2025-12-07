<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CostCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'disbursement_type',
        'disbursement_config',
    ];

    protected function casts(): array
    {
        return [
            'disbursement_config' => 'array',
        ];
    }
}
