<?php

namespace App\Http\Controllers\Api\Donation;

use App\Http\Controllers\Controller;
use App\Services\DonationService;
use App\Services\StripeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Stripe\Webhook;

class DonationController extends Controller
{
    protected $donationService;
    protected $stripeService;

    public function __construct(DonationService $donationService, StripeService $stripeService)
    {
        $this->donationService = $donationService;
        $this->stripeService = $stripeService;
    }

    /**
     * Create standard donation (USER ID REQUIRED)
     */
    public function createStandardDonation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'payment_method_type' => 'nullable|in:card,us_bank_account',
            'is_cover' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $baseUrl = config('app.frontend_url', 'https://thedignitydraw.org');
            $successUrl = $baseUrl . '/success?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl = $baseUrl . '/cancel';

            $paymentMethodType = $request->input('payment_method_type', 'card');
            $isCover = $request->boolean('is_cover');

            $result = $this->donationService->createStandardDonation(
                $request->user_id,
                $successUrl,
                $cancelUrl,
                $paymentMethodType,
                $isCover
            );

            return response()->json([
                'success' => true,
                'checkout_url' => $result['checkout_url'],
                'session_id' => $result['session_id'],
                'payment_method_type' => $paymentMethodType,
                'donation_id_formatted' => $result['donation_id_formatted'],
                'is_cover' => $isCover,
                'processing_fee' => $result['processing_fee'],
                'total_amount' => $result['total_amount'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Create custom amount donation (USER ID REQUIRED)
     */
    public function createCustomDonation(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer|exists:users,id',
            'amount' => 'required|numeric|min:26',
            'payment_method_type' => 'nullable|in:card,us_bank_account',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $baseUrl = config('app.frontend_url', 'http://localhost:5173');
            $successUrl = $baseUrl . '/success?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl = $baseUrl . '/donation/cancel';

            $paymentMethodType = $request->input('payment_method_type', 'card');

            $result = $this->donationService->createCustomDonation(
                $request->user_id,
                $request->amount,
                $successUrl,
                $cancelUrl,
                $paymentMethodType
            );

            return response()->json([
                'success' => true,
                'checkout_url' => $result['checkout_url'],
                'session_id' => $result['session_id'],
                'payment_method_type' => $paymentMethodType,
                'donation_id_formatted' => $result['donation_id_formatted'],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Verify payment
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $donation = $this->donationService->verifyPayment($request->session_id);

            return response()->json([
                'success' => true,
                'donation' => $donation,
                'donor_id' => $donation->user->donor_id,
                'donation_id_formatted' => $donation->donation_id,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check payment status
     */
    public function checkPaymentStatus(string $paymentId): JsonResponse
    {
        try {
            $status = $this->donationService->checkPaymentStatus($paymentId);

            return response()->json([
                'success' => true,
                'status' => $status
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Handle Stripe Webhook
     */
    public function handleStripeWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.webhook_secret');

        Log::info('Webhook received', [
            'payload' => $payload,
            'signature' => $sigHeader
        ]);

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);

            Log::info('Webhook event type: ' . $event->type);

            switch ($event->type) {
                case 'checkout.session.completed':
                    $this->donationService->handleCheckoutCompleted($event->data->object);
                    Log::info('Checkout completed webhook processed');
                    break;

                case 'payment_intent.succeeded':
                    $this->donationService->handlePaymentSucceeded($event->data->object);
                Log::info('Payment succeeded webhook processed');
                    break;

                case 'payment_intent.payment_failed':
                    $this->donationService->handlePaymentFailed($event->data->object);
                    Log::info('Payment failed webhook processed');
                    break;

                default:
                    Log::info('Unhandled webhook event: ' . $event->type);
            }

            return response()->json(['success' => true], 200);
        } catch (Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Get all donations (Admin)
     */
    public function getAllDonations(Request $request): JsonResponse
    {
        try {
            $donations = $this->donationService->getAllDonations(
                $request->input('page', 1),
                $request->input('per_page', 50)
            );

            return response()->json([
                'success' => true,
                'donations' => $donations
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get donations by week (Admin)
     */
    public function getDonationsByWeek(int $weekId): JsonResponse
    {
        try {
            $donations = $this->donationService->getDonationsByWeek($weekId);

            return response()->json([
                'success' => true,
                'donations' => $donations
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
