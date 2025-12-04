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
    public function registerOrLoginUser(array $data): array
    {
        $email = $data['email'];
        $phone = $data['phone'];

        // STEP 1: Check if user already exists
        $existingUser = User::where('email', $email)
            ->orWhere('phone', $phone)
            ->first();

        // CASE 1: Existing user - Check week eligibility
        if ($existingUser) {
            return $this->handleExistingUserForDonation($existingUser);
        }

        // CASE 2: New user - Create account
        return $this->createNewUser($data);
    }


    /**
     * ENHANCED: Handle existing users - Check week-specific eligibility
     */
    protected function handleExistingUserForDonation(User $user): array
    {
        // Get current active draw
        $currentDraw = WeeklyDraw::where('status', 'active')->first();

        if (!$currentDraw) {
            return [
                'success' => false,
                'status' => 'no_active_draw',
                'message' => 'No active draw available at the moment.',
            ];
        }

        // Check if user can donate THIS WEEK
        $eligibility = $this->canUserDonateToWeek($user->id, $currentDraw->id);

        if (!$eligibility['eligible']) {
            return [
                'success' => false,
                'status' => $eligibility['status'],
                'message' => $eligibility['reason'],
                'data' => [
                    'user_id' => $user->id,
                    'donor_id' => $user->donor_id,
                    'current_week' => $currentDraw->week_number,
                    'week_start' => $currentDraw->start_date,
                    'week_end' => $currentDraw->end_date,
                ],
            ];
        }

        // User can donate this week
        Log::info('Existing user ready to donate', [
            'user_id' => $user->id,
            'donor_id' => $user->donor_id,
            'week_id' => $currentDraw->id,
        ]);

        return [
            'success' => true,
            'status' => 'existing_user_can_donate',
            'message' => 'Welcome back! You can donate to this week\'s draw.',
            'data' => [
                'user_id' => $user->id,
                'donor_id' => $user->donor_id,
                'full_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'current_week' => $currentDraw->week_number,
                'total_previous_donations' => $user->total_donations_count,
                'lifetime_amount' => $user->lifetime_donation_amount,
            ],
        ];
    }

    /**
     * NEW: Create completely new user
     */
    protected function createNewUser(array $data): array
    {
        // Double-check uniqueness before creation (race condition protection)
        $exists = User::where('email', $data['email'])
            ->orWhere('phone', $data['phone'])
            ->exists();

        if ($exists) {
            // Race condition: User created between first check and this point
            // Retry with existing user flow
            $user = User::where('email', $data['email'])
                ->orWhere('phone', $data['phone'])
                ->first();

            return $this->handleExistingUserForDonation($user);
        }

        try {
            $user = User::create([
                'name' => $data['full_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'],
                'password' => bcrypt(Str::random(32)),
                'role' => 'donor',
                // donor_id assigned later (after first payment)
            ]);

            Log::info('New user created', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return [
                'success' => true,
                'status' => 'new_user_created',
                'message' => 'Registration successful! Please proceed to donation.',
                'data' => [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'full_name' => $user->name,
                    'address' => $user->address,
                    'is_first_time' => true,
                ],
            ];
        } catch (Exception $e) {
            // Handle unique constraint violation at DB level
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                // Another request created user simultaneously
                $user = User::where('email', $data['email'])
                    ->orWhere('phone', $data['phone'])
                    ->first();

                return $this->handleExistingUserForDonation($user);
            }

            throw $e;
        }
    }

    /**
     * CRITICAL: Check if user can donate to specific week
     * This solves your multi-week donation problem
     */
    public function canUserDonateToWeek(int $userId, int $weekId): array
    {
        // Check 1: Completed donation this week?
        $completedDonation = Donation::where('user_id', $userId)
            ->where('week_id', $weekId)
            ->where('stripe_payment_status', 'completed')
            ->first();

        if ($completedDonation) {
            return [
                'eligible' => false,
                'status' => 'already_donated',
                'reason' => 'You have already donated to this week\'s draw. You can donate again next week!',
                'donation_id' => $completedDonation->id,
                'donated_at' => $completedDonation->donated_at,
            ];
        }

        // Check 2: Pending donation this week?
        $pendingDonation = Donation::where('user_id', $userId)
            ->where('week_id', $weekId)
            ->whereIn('stripe_payment_status', ['pending', 'processing'])
            ->where('created_at', '>', now()->subHours(2)) // Valid for 2 hours
            ->first();

        if ($pendingDonation) {
            return [
                'eligible' => false,
                'status' => 'pending_donation',
                'reason' => 'You have a pending donation for this week. Please complete it or wait for it to expire.',
                'pending_donation_id' => $pendingDonation->id,
                'expires_at' => $pendingDonation->created_at->addHours(2),
            ];
        }

        // User eligible to donate this week
        return [
            'eligible' => true,
            'message' => 'User can donate to this week.',
        ];
    }

    /**
     * Generate unique donor ID with conflict protection
     */
    public function generateUniqueDonorId(): string
    {
        $maxRetries = 5;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                return DB::transaction(function () {
                    // Lock for atomic operation
                    $lastUser = User::whereNotNull('donor_id')
                        ->orderByRaw('CAST(SUBSTRING(donor_id, 3) AS UNSIGNED) DESC')
                        ->lockForUpdate()
                        ->first();

                    if (!$lastUser || !$lastUser->donor_id) {
                        $nextNumber = 1; // Start from DN0000000001
                    } else {
                        $lastNumber = (int) substr($lastUser->donor_id, 2);
                        $nextNumber = $lastNumber + 1;
                    }

                    $donorId = 'DN' . str_pad($nextNumber, 10, '0', STR_PAD_LEFT);

                    // Verify uniqueness
                    $exists = User::where('donor_id', $donorId)->exists();
                    if ($exists) {
                        throw new Exception('Donor ID collision detected');
                    }

                    return $donorId;
                });
            } catch (Exception $e) {
                $attempt++;
                Log::warning('Donor ID generation retry', [
                    'attempt' => $attempt,
                    'error' => $e->getMessage()
                ]);

                if ($attempt >= $maxRetries) {
                    throw new Exception('Failed to generate unique donor ID after ' . $maxRetries . ' attempts');
                }

                usleep(100000); // 100ms delay before retry
            }
        }

        throw new Exception('Failed to generate donor ID');
    }

    /**
     * Assign donor ID only if not already assigned
     */
    public function assignDonorIdIfNeeded(int $userId): ?string
    {
        $user = User::lockForUpdate()->find($userId);

        if (!$user) {
            throw new Exception('User not found');
        }

        // Already has donor_id
        if ($user->donor_id) {
            return $user->donor_id;
        }

        // Generate and assign new donor_id
        $donorId = $this->generateUniqueDonorId();

        $user->update(['donor_id' => $donorId]);

        Log::info('Donor ID assigned', [
            'user_id' => $userId,
            'donor_id' => $donorId,
        ]);

        return $donorId;
    }
}
