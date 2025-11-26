<?php

namespace App\Services;

use App\Models\User;
use App\Models\OtpLog;
use App\Models\Donation;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class RegistrationService
{
    use ApiResponse;
    protected $twilioService;

    public function __construct(TwilioService $twilioService)
    {
        $this->twilioService = $twilioService;
    }

    /**
     * Register new user with OTP (Cache-based temporary storage)
     */
    public function registerUser(array $data): array
    {
        $email = $data['email'];
        $phone = $data['phone'];

        // STEP 1: Check if user already exists in database
        $existingUser = User::where('email', $email)
            ->orWhere('phone', $phone)
            ->first();

        if ($existingUser) {
            return $this->handleExistingUser($existingUser);
        }

        // STEP 2: Store registration data in cache (5 minutes)
        $cacheKey = "registration:{$email}:{$phone}";

        Cache::put($cacheKey, [
            'full_name' => $data['full_name'],
            'email' => $email,
            'phone' => $phone,
            'address' => $data['address'],
            'registration_ip' => request()->ip(),
        ], now()->addMinutes(5));

        // STEP 3: Generate and send OTP
        $otpCode = $this->generateOTP();
        $expiresAt = now(config('app.timezone'))->addMinutes(10);

        // Store OTP in cache
        Cache::put("otp:{$email}:{$phone}", [
            'otp_code' => $otpCode,
            'expires_at' => $expiresAt,
            'attempts' => 0,
        ], now()->addMinutes(10));

        // Log OTP (without user_id yet)
        OtpLog::create([
            'email' => $email,
            'phone' => $phone,
            'user_id' => null,
            'otp_code' => $otpCode,
            'type' => 'both',
            'status' => 'sent',
            'expires_at' => $expiresAt,
            'ip_address' => request()->ip(),
        ]);

        // Send OTP
        $this->sendOTP($email, $phone, $otpCode);

        Log::info('Registration initiated (cached)', [
            'email' => $email,
            'phone' => $phone,
        ]);

        return [
            'success' => true,
            'status' => 'otp_sent',
            'message' => 'OTP sent to your phone. Please verify within 10 minutes.',
            'otp' => $otpCode,
            'otp_expires_in_minutes' => 10,
        ];
    }

    /**
     * Handle existing user
     */
    protected function handleExistingUser(User $user): array
    {
        // Case 1: User verified but not donated yet
        if ($user->email_verified_at && $user->phone_verified_at && !$user->donor_id) {
            return [
                'success' => true,
                'status' => 'verified_not_donated',
                'data' => [
                    'user_id' => $user->id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'stripe_customer_id' => $user->stripe_customer_id ?? ''
                ],
                'message' => 'You are already verified. You can proceed to donate.',
            ];
        }

        // Case 2: User is a donor (already donated)
        if ($user->donor_id) {
            return [
                'success' => true,
                'status' => 'already_donor',
                'data' => [
                    'user_id' => $user->id,
                    'donor_id' => $user->donor_id,
                    'full_name' => $user->full_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'stripe_customer_id' => $user->stripe_customer_id ?? ''
                ],
                'message' => 'You are already a registered donor.',
            ];
        }

        // Case 3: User registered but not verified - Resend OTP
        if (!$user->email_verified_at || !$user->phone_verified_at) {
            return $this->resendOTPForUser($user);
        }

        return [
            'success' => false,
            'message' => 'Unexpected state. Please contact support.',
        ];
    }

    /**
     * Resend OTP for existing unverified user
     */
    protected function resendOTPForUser(User $user): array
    {
        // Rate limiting
        $recentOTPs = OtpLog::where('user_id', $user->id)
            ->where('created_at', '>', now(config('app.timezone'))->subMinutes(30))
            ->count();

        if ($recentOTPs >= 3) {
            throw new Exception('Too many OTP requests. Please wait 30 minutes.');
        }

        $otpCode = $this->generateOTP();
        $expiresAt = now(config('app.timezone'))->addMinutes(10);

        // Store in cache
        Cache::put("otp:{$user->email}:{$user->phone}", [
            'otp_code' => $otpCode,
            'expires_at' => $expiresAt,
            'attempts' => 0,
        ], now()->addMinutes(10));

        OtpLog::create([
            'email' => $user->email,
            'phone' => $user->phone,
            'user_id' => $user->id,
            'otp_code' => $otpCode,
            'type' => 'both',
            'status' => 'sent',
            'expires_at' => $expiresAt,
            'ip_address' => request()->ip(),
        ]);

        $this->sendOTP($user->email, $user->phone, $otpCode);

        return [
            'success' => true,
            'status' => 'otp_resent',
            'message' => 'OTP has been resent to your email and phone.',
        ];
    }

    /**
     * Verify OTP and create user
     */
    public function verifyOTP(string $identifier, string $otpCode): array
    {
        return DB::transaction(function () use ($identifier, $otpCode) {
            // Check if user already exists
            $existingUser = User::where('email', $identifier)
                ->orWhere('phone', $identifier)
                ->first();

            if ($existingUser) {
                return $this->verifyExistingUserOTP($existingUser, $otpCode);
            }

            // Find registration data and OTP data in cache
            // We need to search by identifier (email or phone)
            $registrationData = null;
            $otpData = null;
            $registrationCacheKey = null;
            $otpCacheKey = null;

            // Get OTP log to find email and phone
            $otpLog = OtpLog::where(function ($query) use ($identifier) {
                $query->where('email', $identifier)
                    ->orWhere('phone', $identifier);
            })
                ->where('status', 'sent')
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$otpLog) {
                throw new Exception('No OTP request found. Please register again.');
            }

            $email = $otpLog->email;
            $phone = $otpLog->phone;

            // Try to get registration data from cache
            $registrationCacheKey = "registration:{$email}:{$phone}";
            $registrationData = Cache::get($registrationCacheKey);

            if (!$registrationData) {
                throw new Exception('Registration data expired (5 minutes). Please register again.');
            }

            // Get OTP data from cache
            $otpCacheKey = "otp:{$email}:{$phone}";
            $otpData = Cache::get($otpCacheKey);

            if (!$otpData) {
                throw new Exception('OTP expired (10 minutes). Please request a new one.');
            }

            // Check OTP code
            if ($otpData['otp_code'] !== $otpCode) {
                $otpData['attempts'] = ($otpData['attempts'] ?? 0) + 1;

                if ($otpData['attempts'] >= 3) {
                    Cache::forget($otpCacheKey);
                    Cache::forget($registrationCacheKey);
                    throw new Exception('Too many failed attempts. Please register again.');
                }

                Cache::put($otpCacheKey, $otpData, now()->addMinutes(10));

                Log::warning('Invalid OTP attempt', [
                    'identifier' => $identifier,
                    'attempts' => $otpData['attempts'],
                ]);

                throw new Exception('Invalid OTP code. ' . (3 - $otpData['attempts']) . ' attempts remaining.');
            }

            // Check expiration
            if (isset($otpData['expires_at']) && $otpData['expires_at']->isPast()) {
                Cache::forget($otpCacheKey);
                Cache::forget($registrationCacheKey);
                throw new Exception('OTP has expired. Please register again.');
            }

            // CREATE USER (OTP verified)
            $user = User::create([
                'name' => $registrationData['full_name'],
                'email' => $registrationData['email'],
                'phone' => $registrationData['phone'],
                'address' => $registrationData['address'],
                'password' => bcrypt(Str::random(32)),
                'role' => 'donor',
                'email_verified_at' => now(config('app.timezone')),
                'phone_verified_at' => now(config('app.timezone')),
                'registered_at' => now(config('app.timezone')),
                'registration_ip' => $registrationData['registration_ip'],
            ]);

            // Update OTP log
            OtpLog::where('email', $user->email)
                ->where('phone', $user->phone)
                ->where('otp_code', $otpCode)
                ->where('status', 'sent')
                ->update([
                    'user_id' => $user->id,
                    'status' => 'verified',
                    'verified_at' => now(config('app.timezone')),
                ]);

            // Clear cache
            Cache::forget($registrationCacheKey);
            Cache::forget($otpCacheKey);

            Log::info('User verified and created', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return [
                'success' => true,
                'status' => 'verified',
                'user_id' => $user->id,
                'message' => 'Verification successful. You can now make a donation.',
            ];
        });
    }

    /**
     * Verify OTP for existing unverified user
     */
    protected function verifyExistingUserOTP(User $user, string $otpCode): array
    {
        $otpCacheKey = "otp:{$user->email}:{$user->phone}";
        $otpData = Cache::get($otpCacheKey);

        if (!$otpData) {
            throw new Exception('OTP expired. Please request a new one.');
        }

        if (!isset($otpData['otp_code']) || $otpData['otp_code'] !== $otpCode) {
            throw new Exception('Invalid OTP code.');
        }

        if (isset($otpData['expires_at']) && $otpData['expires_at']->isPast()) {
            throw new Exception('OTP has expired.');
        }

        // Update user verification
        $user->update([
            'email_verified_at' => now(config('app.timezone')),
            'phone_verified_at' => now(config('app.timezone')),
        ]);

        // Update OTP log
        OtpLog::where('email', $user->email)
            ->where('phone', $user->phone)
            ->where('otp_code', $otpCode)
            ->where('status', 'sent')
            ->update([
                'user_id' => $user->id,
                'status' => 'verified',
                'verified_at' => now(config('app.timezone')),
            ]);

        Cache::forget($otpCacheKey);

        return [
            'success' => true,
            'status' => 'verified',
            'user_id' => $user->id,
            'message' => 'Verification successful. You can now make a donation.',
        ];
    }

    /**
     * Resend OTP
     */
    public function resendOTP(string $identifier): array
    {
        // Check if user exists
        $user = User::where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->first();

        if ($user) {
            return $this->resendOTPForUser($user);
        }

        // Check cache for pending registration
        // This is simplified - in real implementation, search through cache keys
        throw new Exception('No pending registration found. Please register first.');
    }

    /**
     * Assign donor ID after first donation
     */
    public function assignDonorId(int $userId): string
    {
        $user = User::find($userId);

        if (!$user) {
            throw new Exception('User not found');
        }

        if ($user->donor_id) {
            return $user->donor_id; // Already has donor_id
        }

        // Generate sequential donor ID
        $donorId = $this->generateDonorId();

        $user->update(['donor_id' => $donorId]);

        Log::info('Donor ID assigned', [
            'user_id' => $userId,
            'donor_id' => $donorId,
        ]);

        return $donorId;
    }

    /**
     * Generate sequential donor ID
     */
    protected function generateDonorId(): string
    {
        $lastUser = User::whereNotNull('donor_id')
            ->orderBy('id', 'desc')
            ->lockForUpdate() // Prevent race condition
            ->first();

        if (!$lastUser || !$lastUser->donor_id) {
            return 'DN100001';
        }

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
    protected function sendOTP(string $email, string $phone, string $otpCode): void
    {
        try {
            // Send SMS via Twilio
            $this->twilioService->sendOTP($phone, $otpCode);

            Log::info('OTP sent successfully', [
                'email' => $email,
                'phone' => $phone,
            ]);
        } catch (Exception $e) {
            Log::error('Failed to send OTP', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Check if user can donate (verified and no active donation this week)
     */
    public function canUserDonate(int $userId, int $weekId): bool
    {
        $user = User::find($userId);

        if (!$user || !$user->email_verified_at || !$user->phone_verified_at) {
            return false;
        }

        // Check if already donated this week
        $donation = Donation::where('user_id', $userId)
            ->where('week_id', $weekId)
            ->where('stripe_payment_status', 'completed')
            ->exists();

        return !$donation;
    }
}
