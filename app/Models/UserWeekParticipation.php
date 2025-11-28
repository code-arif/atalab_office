<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWeekParticipation extends Model
{
    protected $fillable = [
        'user_id',
        'week_id',
        'participated_at',
        'has_donated',
    ];

    protected $casts = [
        'participated_at' => 'datetime',
        'has_donated' => 'boolean',
    ];

    /**
     * Get the user
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the weekly draw
     */
    public function weeklyDraw()
    {
        return $this->belongsTo(WeeklyDraw::class, 'week_id');
    }
}
