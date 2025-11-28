<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WinnerExclusion extends Model
{
    protected $fillable = [
        'user_id',
        'winner_record_id',
        'won_at',
        'exclusion_ends_at',
        'is_active',
    ];

    protected $casts = [
        'won_at' => 'datetime',
        'exclusion_ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who won
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the winner record
     */
    public function winnerRecord()
    {
        return $this->belongsTo(DrawWinner::class, 'winner_record_id');
    }

    /**
     * Check if exclusion is still active
     */
    public function isStillExcluded(): bool
    {
        return $this->is_active && $this->exclusion_ends_at->isFuture();
    }

    /**
     * Get days remaining in exclusion
     */
    public function daysRemaining(): int
    {
        if (!$this->isStillExcluded()) {
            return 0;
        }

        return now()->diffInDays($this->exclusion_ends_at);
    }
}
