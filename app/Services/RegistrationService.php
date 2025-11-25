<?php

namespace App\Services;

use App\Models\User;
use App\Models\OtpLog;
use App\Models\UserSession;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RegistrationService
{
    use ApiResponse;
    protected $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    /**
     * Register new user with OTP
     */
    public function registerUser(array $data): array
    {
        return DB::transaction(function () use ($data) {
            // Check if user already exists (within 1 hour)
            $existingUser = $this->checkExistingUser($data['email'], $data['phone']);

            if ($existingUser) {
                return $this->handleExistingUser($existingUser);
            }

            // Generate sequential donor ID
            $donorId = $this->generateDonorId();

            // Create user
            $user = User::create([
                'name' => $data['full_name'],
                'email' => $data['email'],
                // 'phone' => $this->formatPhone($data['phone']),
                'phone' => $data['phone'],
                'address' => $data['address'],
                'donor_id' => $donorId,
                'password' => bcrypt(Str::random(32)), // Random secure password
                'role' => 'donor',
                'registered_at' => now(config('app.timezone')),
                'registration_ip' => request()->ip(),
            ]);

            // Generate and send OTP
            $otpCode = $this->generateOTP();
            $expiresAt = now(config('app.timezone'))->addMinutes(10);

            $user->update([
                'otp_code' => $otpCode,
                'otp_expires_at' => $expiresAt,
            ]);

            // Log OTP
            OtpLog::create([
                'user_id' => $user->id,
                'otp_code' => $otpCode,
                'type' => 'both',
                'status' => 'sent',
                'expires_at' => $expiresAt,
                'ip_address' => request()->ip(),
            ]);

            // Send OTP via Email and SMS
            $this->sendOTP($user, $otpCode);

            // Create session token (1-hour validity)
            $sessionToken = $this->createUserSession($user);

            Log::info('User registered successfully', [
                'user_id' => $user->id,
                'donor_id' => $donorId,
                'email' => $user->email,
            ]);

            return [
                'success' => true,
                'user_id' => $user->id,
                'donor_id' => $donorId,
                'session_token' => $sessionToken,
                'message' => 'Registration successful. OTP sent to your email and phone.',
                'otp_expires_in_minutes' => 10,
            ];
        });
    }

    /**
     * Check if user exists within 1 hour
     */
    protected function checkExistingUser(string $email, string $phone): ?User
    {
        $oneHourAgo = now(config('app.timezone'))->subHour();

        return User::where(function ($query) use ($email, $phone) {
            $query->where('email', $email)
                ->orWhere('phone', $phone);
        })
            ->where('registered_at', '>=', $oneHourAgo)
            ->first();
    }

    /**
     * Handle existing user within 1-hour window
     */
    protected function handleExistingUser(User $user): array
    {
        // Check if already verified
        if ($user->email_verified_at && $user->phone_verified_at) {
            // Check active session
            // $activeSession = UserSession::where('user_id', $user->id)
            //     ->where('status', 'pending_donation')
            //     ->where('expires_at', '>', now(config('app.timezone')))
            //     ->first();

            // if ($activeSession) {
            //     return [
            //         'success' => true,
            //         'already_registered' => true,
            //         'redirect_to_donation' => true,
            //         'session_token' => $activeSession->session_token,
            //         'message' => 'You are already registered. Redirecting to donation page.',
            //     ];
            // }

            return [
                'success' => false,
                'already_registered' => true,
                'message' => 'You are already registered. Redirecting to donation page.',
            ];
        }

        // Resend OTP if not verified
        if (!$user->email_verified_at || !$user->phone_verified_at) {
            $otpCode = $this->generateOTP();
            $expiresAt = now(config('app.timezone'))->addMinutes(10);

            $user->update([
                'otp_code' => $otpCode,
                'otp_expires_at' => $expiresAt,
            ]);

            OtpLog::create([
                'user_id' => $user->id,
                'otp_code' => $otpCode,
                'type' => 'both',
                'status' => 'sent',
                'expires_at' => $expiresAt,
                'ip_address' => request()->ip(),
            ]);

            $this->sendOTP($user, $otpCode);

            return [
                'success' => true,
                'already_registered' => true,
                'otp_resent' => true,
                'message' => 'OTP has been resent to your email and phone.',
            ];
        }

        return [
            'success' => false,
            'message' => 'Unexpected state. Please contact support.',
        ];
    }

    /**
     * Verify OTP
     */
    public function verifyOTP(string $identifier, string $otpCode): array
    {
        return DB::transaction(function () use ($identifier, $otpCode) {
            // Find user by email or phone
            $user = User::where('email', $identifier)
                ->orWhere('phone', $identifier)
                ->first();

            if (!$user) {
                throw new Exception('User not found');
            }

            // Check OTP
            if ($user->otp_code !== $otpCode) {
                Log::warning('Invalid OTP attempt', [
                    'user_id' => $user->id,
                    'ip' => request()->ip(),
                ]);
                throw new Exception('Invalid OTP code');
            }

            // Check expiration
            if ($user->otp_expires_at < now(config('app.timezone'))) {
                throw new Exception('OTP has expired. Please request a new one.');
            }

            // Verify user
            $user->update([
                'email_verified_at' => now(config('app.timezone')),
                'phone_verified_at' => now(config('app.timezone')),
                'otp_code' => null,
                'otp_expires_at' => null,
            ]);

            // Update OTP log
            OtpLog::where('user_id', $user->id)
                ->where('otp_code', $otpCode)
                ->update([
                    'status' => 'verified',
                    'verified_at' => now(config('app.timezone')),
                ]);

            // Create session token for donation
            $sessionToken = $this->createUserSession($user);

            Log::info('OTP verified successfully', [
                'user_id' => $user->id,
                'donor_id' => $user->donor_id,
            ]);

            return [
                'success' => true,
                'user_id' => $user->id,
                'donor_id' => $user->donor_id,
                'session_token' => $sessionToken,
                'message' => 'Verification successful. You can now make a donation.',
            ];
        });
    }

    /**
     * Resend OTP
     */
    public function resendOTP(string $identifier): array
    {
        $user = User::where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->first();

        if (!$user) {
            throw new Exception('User not found');
        }

        // Rate limiting: max 3 OTPs per 30 minutes
        $recentOTPs = OtpLog::where('user_id', $user->id)
            ->where('created_at', '>', now(config('app.timezone'))->subMinutes(30))
            ->count();

        if ($recentOTPs >= 3) {
            throw new Exception('Too many OTP requests. Please wait 30 minutes.');
        }

        $otpCode = $this->generateOTP();
        $expiresAt = now(config('app.timezone'))->addMinutes(10);

        $user->update([
            'otp_code' => $otpCode,
            'otp_expires_at' => $expiresAt,
        ]);

        OtpLog::create([
            'user_id' => $user->id,
            'otp_code' => $otpCode,
            'type' => 'both',
            'status' => 'sent',
            'expires_at' => $expiresAt,
            'ip_address' => request()->ip(),
        ]);

        $this->sendOTP($user, $otpCode);

        return [
            'success' => true,
            'message' => 'New OTP sent successfully.',
        ];
    }

    /**
     * Generate sequential donor ID
     */
    protected function generateDonorId(): string
    {
        $lastUser = User::whereNotNull('donor_id')
            ->orderBy('id', 'desc')
            ->first();

        if (!$lastUser || !$lastUser->donor_id) {
            return 'DN100001'; // Start from DN100001
        }

        // Extract number from last donor_id (DN100001 -> 100001)
        $lastNumber = (int) substr($lastUser->donor_id, 2);
        $nextNumber = $lastNumber + 1;

        return 'DN' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate 4-digit OTP
     */
    protected function generateOTP(): string
    {
        return str_pad(random_int(0, 9999), 4, '0', STR_PAD_LEFT);
    }

    /**
     * Send OTP via Email and SMS
     */
    protected function sendOTP(User $user, string $otpCode): void
    {
        try {
            // Send Email
            // Mail::send('emails.otp', ['otp' => $otpCode, 'user' => $user], function ($message) use ($user) {
            //     $message->to($user->email)
            //         ->subject('Your Verification Code - ' . config('app.name'));
            // });

            // Send SMS via Twilio
            $this->twilioService->sendOTP($user->phone, $otpCode);

            Log::info('OTP sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'phone' => $user->phone,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send OTP', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
            // Don't throw - user still created
        }
    }

    /**
     * Create user session (1-hour validity)
     */
    protected function createUserSession(User $user): string
    {
        $token = Str::random(64);

        UserSession::create([
            'user_id' => $user->id,
            'session_token' => $token,
            'status' => 'pending_donation',
            'expires_at' => now(config('app.timezone'))->addHour(),
        ]);

        return $token;
    }

    /**
     * Validate session token
     */
    public function validateSession(string $token): ?User
    {
        $session = UserSession::where('session_token', $token)
            ->where('status', 'pending_donation')
            ->where('expires_at', '>', now(config('app.timezone')))
            ->first();

        return $session ? $session->user : null;
    }

    /**
     * Mark session as donated
     */
    public function markSessionAsDonated(string $token): void
    {
        UserSession::where('session_token', $token)
            ->update(['status' => 'donated']);
    }

    /**
     * Format phone number (BD format)
     */
    // protected function formatPhone(string $phone): string
    // {
    //     // Remove all non-numeric characters
    //     $phone = preg_replace('/[^0-9]/', '', $phone);

    //     // Add country code if missing
    //     if (!str_starts_with($phone, '880')) {
    //         $phone = '880' . ltrim($phone, '0');
    //     }

    //     return $phone;
    // }
}
