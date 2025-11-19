<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrawWinner extends Model
{
    protected $fillable = [
        'weekly_draw_id',
        'user_id',
        'donation_id',
        'amount_won',
        'claimed',
        'claimed_at',
        'payout_stripe_id',
        'payout_status',
    ];

    protected $casts = [
        'amount_won' => 'decimal:2',
        'claimed' => 'boolean',
        'claimed_at' => 'datetime',
    ];

    public function weeklyDraw(): BelongsTo
    {
        return $this->belongsTo(WeeklyDraw::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
    }

    public function canClaim(): bool
    {
        return !$this->claimed
            && $this->weeklyDraw->isClaiming()
            && $this->payout_status === 'pending';
    }

    public function verification()
    {
        return $this->hasOne(WinnerVerification::class);
    }

    // Helper Methods
    // public function canClaim(): bool
    // {
    //     if ($this->claimed) {
    //         return false;
    //     }

    //     $claimDeadline = $this->weeklyDraw->claim_deadline;
    //     return now()->lessThan($claimDeadline);
    // }

    public function isClaimExpired(): bool
    {
        $claimDeadline = $this->weeklyDraw->claim_deadline;
        return now()->greaterThan($claimDeadline);
    }

    public function getClaimStatusAttribute(): string
    {
        if ($this->claimed) {
            return 'claimed';
        }

        if ($this->isClaimExpired()) {
            return 'expired';
        }

        if ($this->verification && $this->verification->verification_status === 'approved') {
            return 'approved_pending_payout';
        }

        return 'pending';
    }
}
