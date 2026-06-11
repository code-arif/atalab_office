<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DrawCycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'status',
    ];

    /**
     * Get the weekly draws associated with this cycle.
     */
    public function draws(): HasMany
    {
        return $this->hasMany(WeeklyDraw::class, 'draw_cycle_id');
    }
}
