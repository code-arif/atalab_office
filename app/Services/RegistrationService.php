<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\OtpLog;
use App\Models\Donation;
use App\Models\WeeklyDraw;
use App\Models\WinnerExclusion;
use App\Models\UserWeekParticipation;
use App\Mail\SendOTPMail;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;

class RegistrationService
{
    use ApiResponse;

    /**
     * ENHANCED: Register user with multi-week support
     * Users can register ONCE but donate to MULTIPLE weeks
     */
    public function registerUser(array $data): array
    {
        $email = $data['email'];
        $phone = $data['phone'];

        // STEP 1: Check if user exists
        $existingUser = User::where('email', $email)
            ->orWhere('phone', $phone)
            ->first();

        if ($existingUser) {
            return $this->handleExistingUser($existingUser);
        }

        // STEP 2: Store registration data in cache (10 minutes)
        $cacheKey = "registration:{$email}:{$phone}";

        Cache::put($cacheKey, [
            'full_name' => $data['full_name'],
            'email' => $email,
            'phone' => $phone,
            'address' => $data['address'],
            'registration_ip' => request()->ip(),
        ], now()->addMinutes(10));

        // STEP 3: Generate and send OTP
        $otpCode = $this->generateOTP();
        $expiresAt = now(config('app.timezone'))->addMinutes(10);

        Cache::put("otp:{$email}:{$phone}", [
            'otp_code' => $otpCode,
            'expires_at' => $expiresAt,
            'attempts' => 0,
        ], now()->addMinutes(10));

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

        $this->sendOTP($email, $phone, $otpCode);

        Log::info('Registration initiated', compact('email', 'phone'));

        return [
            'success' => true,
            'status' => 'otp_sent',
            'message' => 'OTP sent to your email. Please verify within 10 minutes.',
            'otp_expires_in_minutes' => 10,
        ];
    }

    /**
     * ENHANCED: Handle existing users - Check week-specific eligibility
     */
    protected function handleExistingUser(User $user): array
    {
        // Get current active draw
        $currentDraw = WeeklyDraw::where('status', 'active')->first();

        if (!$currentDraw) {
            return [
                'success' => false,
                'message' => 'No active draw available at the moment.',
            ];
        }

        // Case 1: User not verified yet
        if (!$user->email_verified_at || !$user->phone_verified_at) {
            return $this->resendOTPForUser($user);
        }

        // Case 2: Check if user can donate THIS WEEK
        $canDonateThisWeek = $this->canUserDonateToWeek($user->id, $currentDraw->id);

        if (!$canDonateThisWeek['eligible']) {
            return [
                'success' => false,
                'status' => 'already_donated_this_week',
                'message' => $canDonateThisWeek['reason'],
                'data' => [
                    'user_id' => $user->id,
                    'donor_id' => $user->donor_id,
                    'current_week' => $currentDraw->week_number,
                ],
            ];
        }

        // Case 3: User verified and can donate this week
        return [
            'success' => true,
            'status' => 'verified_can_donate',
            'data' => [
                'user_id' => $user->id,
                'donor_id' => $user->donor_id,
                'full_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'stripe_customer_id' => $user->stripe_customer_id,
            ],
            'message' => 'Welcome back! You can donate to this week\'s draw.',
        ];
    }

    /**
     * CRITICAL: Check if user can donate to specific week
     * This solves your multi-week donation problem
     */
    public function canUserDonateToWeek(int $userId, int $weekId): array
    {
        // Check 1: Has user already completed donation this week?
        $existingDonation = Donation::where('user_id', $userId)
            ->where('week_id', $weekId)
            ->where('stripe_payment_status', 'completed')
            ->first();

        if ($existingDonation) {
            return [
                'eligible' => false,
                'reason' => 'You have already donated to this week\'s draw. Only one donation per week is allowed.',
                'donation_id' => $existingDonation->id,
            ];
        }

        // Check 2: Is there a pending donation?
        $pendingDonation = Donation::where('user_id', $userId)
            ->where('week_id', $weekId)
            ->whereIn('stripe_payment_status', ['pending', 'processing'])
            ->where('created_at', '>', now()->subHours(2)) // Within last 2 hours
            ->first();

        if ($pendingDonation) {
            return [
                'eligible' => false,
                'reason' => 'You have a pending donation. Please complete it or wait for it to expire.',
                'pending_donation_id' => $pendingDonation->id,
            ];
        }

        // Check 3: Is user verified?
        $user = User::find($userId);
        if (!$user || !$user->email_verified_at || !$user->phone_verified_at) {
            return [
                'eligible' => false,
                'reason' => 'Please verify your email and phone first.',
            ];
        }

        return [
            'eligible' => true,
            'message' => 'User can donate to this week.',
        ];
    }

    /**
     * Verify OTP and create/update user
     */
    public function verifyOTP(string $identifier, string $otpCode): array
    {
        return DB::transaction(function () use ($identifier, $otpCode) {
            $existingUser = User::where('email', $identifier)
                ->orWhere('phone', $identifier)
                ->first();

            if ($existingUser) {
                return $this->verifyExistingUserOTP($existingUser, $otpCode);
            }

            // Find OTP log
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

            // Get registration data from cache
            $registrationCacheKey = "registration:{$email}:{$phone}";
            $registrationData = Cache::get($registrationCacheKey);

            if (!$registrationData) {
                throw new Exception('Registration data expired. Please register again.');
            }

            // Get OTP data from cache
            $otpCacheKey = "otp:{$email}:{$phone}";
            $otpData = Cache::get($otpCacheKey);

            if (!$otpData) {
                throw new Exception('OTP expired. Please request a new one.');
            }

            // Verify OTP
            if ($otpData['otp_code'] !== $otpCode) {
                $otpData['attempts'] = ($otpData['attempts'] ?? 0) + 1;

                if ($otpData['attempts'] >= 3) {
                    Cache::forget($otpCacheKey);
                    Cache::forget($registrationCacheKey);
                    throw new Exception('Too many failed attempts. Please register again.');
                }

                Cache::put($otpCacheKey, $otpData, now()->addMinutes(10));
                throw new Exception('Invalid OTP code. ' . (3 - $otpData['attempts']) . ' attempts remaining.');
            }

            // Check expiration
            if (isset($otpData['expires_at']) && $otpData['expires_at']->isPast()) {
                Cache::forget($otpCacheKey);
                Cache::forget($registrationCacheKey);
                throw new Exception('OTP has expired. Please register again.');
            }

            // CREATE USER
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
     * Verify OTP for existing user
     */
    protected function verifyExistingUserOTP(User $user, string $otpCode): array
    {
        $otpCacheKey = "otp:{$user->email}:{$user->phone}";
        $otpData = Cache::get($otpCacheKey);

        if (!$otpData || $otpData['otp_code'] !== $otpCode) {
            throw new Exception('Invalid OTP code.');
        }

        if (isset($otpData['expires_at']) && $otpData['expires_at']->isPast()) {
            throw new Exception('OTP has expired.');
        }

        $user->update([
            'email_verified_at' => now(config('app.timezone')),
            'phone_verified_at' => now(config('app.timezone')),
        ]);

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
            'data' => [
                'email' => $user->email,
                'phone' => $user->phone,
                'name' => $user->name,
                'address' => $user->address,
                'id' => $user->id,
            ],
            'message' => 'Verification successful. You can now make a donation.',
        ];
    }

    /**
     * Resend OTP for existing user
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
     * Resend OTP
     */
    public function resendOTP(string $identifier): array
    {
        $user = User::where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->first();

        if ($user) {
            return $this->resendOTPForUser($user);
        }

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
            return $user->donor_id;
        }

        // Generate sequential donor ID with lock
        $donorId = $this->generateDonorId();

        $user->update(['donor_id' => $donorId]);

        Log::info('Donor ID assigned', [
            'user_id' => $userId,
            'donor_id' => $donorId,
        ]);

        return $donorId;
    }

    /**
     * Generate sequential donor ID with proper locking
     */
    protected function generateDonorId(): string
    {
        return DB::transaction(function () {
            $lastUser = User::whereNotNull('donor_id')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            if (!$lastUser || !$lastUser->donor_id) {
                return 'DN100001';
            }

            $lastNumber = (int) substr($lastUser->donor_id, 2);
            $nextNumber = $lastNumber + 1;

            return 'DN' . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
        });
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
            Mail::to($email)->queue(new SendOTPMail($otpCode));
            Log::info('OTP sent successfully', compact('email', 'phone'));
        } catch (Exception $e) {
            Log::error('Failed to send OTP', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
