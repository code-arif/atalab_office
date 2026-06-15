<?php
// app/Models/Donation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    use HasFactory;
    protected $fillable = [
        'donation_id',
        'user_id',
        'temp_identifier',
        'week_id',
        'amount',
        'processing_fee',
        'total_amount',
        'is_cover',
        'stripe_payment_id',
        'stripe_payment_status',
        'stripe_charge_id',
        'is_eligible_for_draw',
        'payment_type',
        'donated_at',
        'attempt_number'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_cover' => 'boolean',
        'is_eligible_for_draw' => 'boolean',
        'donated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];


    /**
     * RELATIONSHIPS
     */

    /**
     * Get the user who made the donation
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the weekly draw this donation belongs to
     */
    public function weeklyDraw(): BelongsTo
    {
        return $this->belongsTo(WeeklyDraw::class, 'week_id');
    }


    /**
     * Get the winner record if this donation won
     */
    public function winner()
    {
        return $this->hasOne(DrawWinner::class);
    }

    public function isWinner(): bool
    {
        return $this->winner()->exists();
    }



    /**
     * SCOPES
     */

    /**
     * Scope to get only completed donations
     */
    public function scopeCompleted($query)
    {
        return $query->where('stripe_payment_status', 'completed');
    }

    /**
     * Scope to get eligible donations for draw
     */
    public function scopeEligible($query)
    {
        return $query->where('is_eligible_for_draw', true)
            ->where('stripe_payment_status', 'completed');
    }

    /**
     * Scope to get donations for specific week
     */
    public function scopeForWeek($query, int $weekId)
    {
        return $query->where('week_id', $weekId);
    }

    /**
     * Scope to get pending donations
     */
    public function scopePending($query)
    {
        return $query->where('stripe_payment_status', 'pending');
    }

    /**
     * ACCESSORS
     */

    /**
     * Check if donation is completed
     */
    public function isCompleted(): bool
    {
        return $this->stripe_payment_status === 'completed';
    }

    /**
     * Check if donation is eligible for draw
     */
    public function isEligible(): bool
    {
        return $this->is_eligible_for_draw && $this->isCompleted();
    }
}
