<?php

namespace App\Http\Controllers\Api\Donation;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Services\StripeService;
use App\Services\DrawService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DonationController extends Controller
{
    public function __construct(
        private StripeService $stripeService,
        private DrawService $drawService
    ) {}

    /**
     * Quick donation ($25 minimum)
     */
    public function quickDonation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'stripe_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        return $this->processDonation(25, $request->stripe_token, 'standard');
    }

    /**
     * Custom donation (user choice amount)
     */
    public function customDonation(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:25|max:100000',
            'stripe_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        return $this->processDonation($request->amount, $request->stripe_token, 'custom');
    }

    /**
     * Process donation logic
     */
    private function processDonation(float $amount, string $stripeToken, string $paymentType)
    {
        // Get active draw
        $activeDraw = $this->drawService->getCurrentActiveDraw();

        if (!$activeDraw) {
            return response()->json([
                'success' => false,
                'message' => 'No active draw available at this time. Please wait for the next draw.'
            ], 400);
        }

        try {
            return DB::transaction(function () use ($amount, $stripeToken, $paymentType, $activeDraw) {
                // Process Stripe payment
                $paymentResult = $this->stripeService->createCharge(
                    $amount,
                    $stripeToken,
                    "Donation for Week {$activeDraw->week_number}"
                );

                if (!$paymentResult['success']) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment failed: ' . $paymentResult['error']
                    ], 400);
                }

                // Save donation
                $donation = Donation::create([
                    'user_id' => auth()->id(),
                    'week_id' => $activeDraw->id,
                    'amount' => $amount,
                    'stripe_payment_id' => $paymentResult['payment_id'],
                    'stripe_charge_id' => $paymentResult['charge_id'],
                    'stripe_payment_status' => $paymentResult['status'],
                    'is_eligible_for_draw' => true,
                    'payment_type' => $paymentType,
                    'donated_at' => now(),
                ]);

                // Update draw statistics
                $activeDraw->increment('total_participants');
                $activeDraw->increment('total_pool', $amount);

                return response()->json([
                    'success' => true,
                    'message' => 'Donation successful! You are now entered in the draw.',
                    'data' => [
                        'donation' => $donation,
                        'draw' => $this->drawService->getDrawStatistics($activeDraw),
                    ]
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's donation history
     */
    public function myDonations()
    {
        $donations = Donation::with(['weeklyDraw', 'winner'])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $donations
        ]);
    }

    /**
     * Get specific donation details
     */
    public function show($id)
    {
        $donation = Donation::with(['weeklyDraw', 'winner'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $donation
        ]);
    }
}
