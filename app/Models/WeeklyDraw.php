<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WeeklyDraw extends Model
{
    use HasFactory;
    use SoftDeletes;
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
        'year',
        'is_paused',
        'draw_cycle_id',
        'is_rolled_over'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'countdown_ends_at' => 'datetime',
        'claim_deadline' => 'datetime',
        'total_pool' => 'decimal:2',
        'admin_commission' => 'decimal:2',
        'winners_selected' => 'boolean',
        'is_paused' => 'boolean',
        'is_rolled_over' => 'boolean',
    ];

    /**
     * RELATIONSHIPS
     */

    /**
     * Get the draw cycle this week belongs to
     */
    public function drawCycle()
    {
        return $this->belongsTo(DrawCycle::class);
    }

    /**
     * Get all donations for this week
     */
    public function donations(): HasMany
    {
        return $this->hasMany(Donation::class, 'week_id');
    }


    /**
     * Get all winners for this draw
     */
    public function winners(): HasMany
    {
        return $this->hasMany(DrawWinner::class);
    }


    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->is_paused && now()->lt($this->countdown_ends_at);
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


    /**
     * Get completed donations only
     */
    public function completedDonations()
    {
        return $this->donations()->completed();
    }

    /**
     * Get eligible donations for draw
     */
    public function eligibleDonations()
    {
        return $this->donations()->eligible();
    }

    /**
     * SCOPES
     */

    /**
     * Scope to get active draws
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to get claiming draws
     */
    public function scopeClaiming($query)
    {
        return $query->where('status', 'claiming');
    }

    /**
     * Scope to get completed draws
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * ACCESSORS
     */

    /**
     * Get expected winners based on current participants
     */
    public function getExpectedWinnersAttribute(): int
    {
        return $this->total_participants > 0
            ? (int) ceil($this->total_participants / 400)
            : 0;
    }

    /**
     * Get distribution pool after admin commission
     */
    public function getDistributionPoolAttribute(): float
    {
        return $this->total_pool - $this->admin_commission;
    }
}
