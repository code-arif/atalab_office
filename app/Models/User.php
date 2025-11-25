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
        'google_access_token',
        'google_refresh_token',
        'google_token_expires_at',
        'otp_code',
        'otp_expires_at',
        'email_verified_at',
        'phone_verified_at',
        'registered_at',
        'registration_ip'
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

    /**
     * Check if user won in last 6 months
     */
    public function hasWonRecently(): bool
    {
        return $this->wins()
            ->where('created_at', '>=', now()->subMonths(6))
            ->exists();
    }
}
