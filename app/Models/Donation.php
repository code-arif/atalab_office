<?php
// app/Models/Donation.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    protected $fillable = [
        'user_id',
        'temp_identifier',
        'week_id',
        'amount',
        'stripe_payment_id',
        'stripe_payment_status',
        'stripe_charge_id',
        'is_eligible_for_draw',
        'payment_type',
        'donated_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_eligible_for_draw' => 'boolean',
        'donated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function weeklyDraw(): BelongsTo
    {
        return $this->belongsTo(WeeklyDraw::class, 'week_id');
    }

    public function winner()
    {
        return $this->hasOne(DrawWinner::class);
    }

    public function isWinner(): bool
    {
        return $this->winner()->exists();
    }
}
