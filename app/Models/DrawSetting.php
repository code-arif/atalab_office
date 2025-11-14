<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DrawSetting extends Model
{
    protected $fillable = [
        'participants',
        'total_pool',
        'recipients',
        'odds_numerator',
        'odds_denominator',
        'net_per_recipient',
    ];
}
