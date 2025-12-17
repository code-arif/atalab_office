<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VisitorStatistic extends Model
{
    protected $fillable = [
        'date',
        'total_visitors',
        'unique_visitors'
    ];

    protected $casts = [
        'date' => 'date'
    ];
}
