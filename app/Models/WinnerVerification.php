<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WinnerVerification extends Model
{
    protected $fillable = [
        'draw_winner_id',
        'verified_by',
        'identity_verified',
        'drivers_license',
        'license_state',
        'license_expiry',
        'identity_verified_at',
        'email_verified',
        'phone_verified',
        'verification_code',
        'code_expires_at',
        'contact_verified_at',
        'bank_verified',
        'bank_name',
        'account_number_last4',
        'routing_number',
        'account_holder_name',
        'bank_verified_at',
        'verification_status',
        'admin_notes',
        'rejection_reason',
        'approved_at',
        'rejected_at',
    ];

    protected $casts = [
        'identity_verified' => 'boolean',
        'email_verified' => 'boolean',
        'phone_verified' => 'boolean',
        'bank_verified' => 'boolean',
        'license_expiry' => 'date',
        'identity_verified_at' => 'datetime',
        'contact_verified_at' => 'datetime',
        'bank_verified_at' => 'datetime',
        'code_expires_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // Relationships
    public function drawWinner(): BelongsTo
    {
        return $this->belongsTo(DrawWinner::class);
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // Helper Methods
    public function isFullyVerified(): bool
    {
        return $this->identity_verified
            && $this->email_verified
            && $this->phone_verified
            && $this->bank_verified;
    }

    public function getVerificationProgress(): int
    {
        $steps = [
            $this->identity_verified,
            $this->email_verified,
            $this->phone_verified,
            $this->bank_verified,
        ];

        $completed = count(array_filter($steps));
        return ($completed / count($steps)) * 100;
    }

    public function canApprove(): bool
    {
        return $this->isFullyVerified() && $this->verification_status !== 'approved';
    }
}
