<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{
    protected $fillable = [
        'ip_address',
        'user_agent',
        'country',
        'city',
        'visit_date',
        'visit_count'
    ];

    protected $casts = [
        'visit_date' => 'date'
    ];
}
