<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, Billable;

    // fillable
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'avatar',
        'address',
        'donor_id',
        'email_verified_at',
        'phone_verified_at',
        'registered_at',
        'registration_ip',
        'stripe_customer_id',
    ];

    protected $hidden = [
        'remember_token',
        'google_access_token',
        'google_refresh_token',
    ];

    protected $casts = [
        'google_token_expires_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }


    /**
     * RELATIONSHIPS
     */

    /**
     * Get all donations made by this user
     */
    public function donations()
    {
        return $this->hasMany(Donation::class);
    }

    /**
     * Get all wins by this user
     */
    public function wins()
    {
        return $this->hasMany(DrawWinner::class);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is donor
     */
    public function isDonor(): bool
    {
        return $this->role === 'donor';
    }

    /**
     * Get total donated amount
     */
    public function getTotalDonatedAttribute(): float
    {
        return $this->donations()
            ->where('stripe_payment_status', 'completed')
            ->sum('amount');
    }

    /**
     * Get total won amount
     */
    public function getTotalWonAttribute(): float
    {
        return $this->wins()->sum('amount_won');
    }

    /**
     * Check if user has donated in specific week
     */
    public function hasDonatedInWeek(int $weekId): bool
    {
        return $this->donations()
            ->where('week_id', $weekId)
            ->where('stripe_payment_status', 'completed')
            ->exists();
    }


    public function weekParticipations()
    {
        return $this->hasMany(UserWeekParticipation::class);
    }

    public function winnerExclusions()
    {
        return $this->hasMany(WinnerExclusion::class);
    }

    /**
     * Check if user won in last 6 months
     */
    public function hasWonRecently(): bool
    {
        return $this->wins()
            ->where('created_at', '>=', now()->subMonths(6))
            ->exists();
    }

    //chat model relation
    public function senders()
    {
        return $this->hasMany(Chat::class, 'sender_id');
    }

    public function receivers()
    {
        return $this->hasMany(Chat::class, 'receiver_id');
    }

    public function sentMessages()
    {
        return $this->morphMany(Chat::class, 'sender', 'sender_type', 'sender_id');
    }

    public function receivedMessages()
    {
        return $this->morphMany(Chat::class, 'receiver', 'receiver_type', 'receiver_id');
    }
}
