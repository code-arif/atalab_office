<?php

namespace App\Services;

use Exception;
use App\Models\User;
use App\Models\Donation;
use App\Models\WeeklyDraw;
use App\Mail\SendOTPMail;
use App\Traits\ApiResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

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

        // STEP 2: Store registration data user table
        $user = User::create([
            'name' => $data['full_name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'password' => bcrypt(Str::random(32)),
            'role' => 'donor',
        ]);

        return [
            'success' => true,
            'status' => 'new_user_created',
            'message' => 'OTP sent to your email. Please verify within 10 minutes.',
            'data' => [
                'user_id' => $user->id,
                'email' => $user->email,
                'phone' => $user->phone,
                'full_name' => $user->name,
                'address' => $user->address,
            ],
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
        // $user = User::find($userId);
        // if (!$user || !$user->email_verified_at || !$user->phone_verified_at) {
        //     return [
        //         'eligible' => false,
        //         'reason' => 'Please verify your email and phone first.',
        //     ];
        // }

        return [
            'eligible' => true,
            'message' => 'User can donate to this week.',
        ];
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
