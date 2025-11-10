<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeeklyDraw extends Model
{
    protected $fillable = [
        'week_number',
        'start_date',
        'end_date',
        'countdown_ends_at',
        'claim_deadline',
        'status',
        'total_pool',
        'total_participants',
        'total_recipients',
        'admin_commission',
        'winners_selected',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'countdown_ends_at' => 'datetime',
        'claim_deadline' => 'datetime',
        'total_pool' => 'decimal:2',
        'admin_commission' => 'decimal:2',
        'winners_selected' => 'boolean',
    ];

    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class, 'week_id');
    }

    public function winners(): HasMany
    {
        return $this->hasMany(DrawWinner::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && now()->lt($this->countdown_ends_at);
    }

    public function isClaiming(): bool
    {
        return $this->status === 'claiming' && now()->lt($this->claim_deadline);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed' || now()->gte($this->claim_deadline);
    }

    public function getCountdownSecondsAttribute(): int
    {
        if ($this->isActive()) {
            return max(0, now()->diffInSeconds($this->countdown_ends_at, false));
        }
        return 0;
    }

    public function getClaimTimeLeftSecondsAttribute(): int
    {
        if ($this->isClaiming()) {
            return max(0, now()->diffInSeconds($this->claim_deadline, false));
        }
        return 0;
    }
}
