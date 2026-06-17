<?php

namespace App\Http\Controllers\Api\V2\Donation;

use App\Http\Controllers\Controller;
use App\Http\Requests\V2\Donation\CustomDonationRequest;
use App\Http\Requests\V2\Donation\StandardDonationRequest;
use App\Http\Requests\V2\Donation\VerifyPaymentRequest;
use App\Http\Resources\V2\Donation\DonationResource;
use App\Models\WeeklyDraw;
use App\Services\V2\PaymentV2Service;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Webhook;

class DonationV2Controller extends Controller
{
    protected PaymentV2Service $paymentV2Service;

    public function __construct(PaymentV2Service $paymentV2Service)
    {
        $this->paymentV2Service = $paymentV2Service;
    }

    /**
     * V2: Create a standard (predefined amount) donation.
     *
     * @bodyParam user_id int required The user's ID.
     * @bodyParam payment_method_type string Optional. "card" or "us_bank_account".
     * @bodyParam is_cover bool Optional. Whether to cover processing fees.
     */
    public function createStandardDonation(StandardDonationRequest $request): JsonResponse
    {
        try {
            $baseUrl = config('app.frontend_url', 'https://thedignitydraw.org');
            $successUrl = $baseUrl . '/success?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl = $baseUrl . '/cancel';

            $result = $this->paymentV2Service->createStandardDonation(
                $request->user_id,
                $successUrl,
                $cancelUrl,
                $request->input('payment_method_type', 'card'),
                $request->boolean('is_cover')
            );

            return response()->json([
                'success' => true,
                'message' => 'Standard donation session created.',
                'checkout_url' => $result['checkout_url'],
                'session_id' => $result['session_id'],
                'donation_id_formatted' => $result['donation_id_formatted'],
                'is_cover' => $result['is_cover'],
                'processing_fee' => $result['processing_fee'],
                'total_amount' => $result['total_amount'],
                'version' => 'v2',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * V2: Create a custom amount donation.
     *
     * @bodyParam user_id int required The user's ID.
     * @bodyParam amount numeric required The donation amount (min $26).
     * @bodyParam payment_method_type string Optional. "card" or "us_bank_account".
     */
    public function createCustomDonation(CustomDonationRequest $request): JsonResponse
    {
        try {
            $baseUrl = config('app.frontend_url', 'https://thedignitydraw.org');
            $successUrl = $baseUrl . '/success?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl = $baseUrl . '/cancel';

            $result = $this->paymentV2Service->createCustomDonation(
                $request->user_id,
                $request->amount,
                $successUrl,
                $cancelUrl,
                $request->input('payment_method_type', 'card')
            );

            return response()->json([
                'success' => true,
                'message' => 'Custom donation session created.',
                'checkout_url' => $result['checkout_url'],
                'session_id' => $result['session_id'],
                'donation_id_formatted' => $result['donation_id_formatted'],
                'version' => 'v2',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * V2: Verify payment after redirect from Stripe.
     * Includes card fingerprint extraction.
     *
     * @bodyParam session_id string required The Stripe Checkout session ID.
     */
    public function verifyPayment(VerifyPaymentRequest $request): JsonResponse
    {
        try {
            $donation = $this->paymentV2Service->verifyPayment($request->session_id);

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully.',
                'data' => (new DonationResource($donation))->withDuplicateCheck(true),
                'donor_id' => $donation->user->donor_id,
                'donation_id_formatted' => $donation->donation_id,
                'version' => 'v2',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * V2: Check payment status by Stripe payment ID.
     */
    public function checkPaymentStatus(string $paymentId): JsonResponse
    {
        try {
            $status = $this->paymentV2Service->checkPaymentStatus($paymentId);

            return response()->json([
                'success' => true,
                'data' => $status,
                'version' => 'v2',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * V2: Check if a card has been used for the current draw.
     * Useful for the frontend to warn users before submitting.
     *
     * @bodyParam card_fingerprint string required The card fingerprint to check.
     * @bodyParam week_id int The draw week ID (defaults to current active draw).
     */
    public function checkDuplicateCard(Request $request): JsonResponse
    {
        $request->validate([
            'card_fingerprint' => 'required|string|size:16',
            'week_id' => 'nullable|integer|exists:weekly_draws,id',
        ]);

        try {
            $weekId = $request->week_id;
            if (!$weekId) {
                $currentDraw = WeeklyDraw::where('status', 'active')->first();
                if (!$currentDraw) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No active draw found.',
                    ], 404);
                }
                $weekId = $currentDraw->id;
            }

            $result = $this->paymentV2Service->checkDuplicateCard(
                $request->card_fingerprint,
                $weekId
            );

            return response()->json([
                'success' => true,
                'is_duplicate' => $result['is_duplicate'],
                'message' => $result['is_duplicate']
                    ? 'This card has already been used for the current draw.'
                    : 'No duplicate card found.',
                'data' => $result,
                'version' => 'v2',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // -----------------------------------------------------------------------
    //  V2 STRIPE WEBHOOK
    // -----------------------------------------------------------------------

    /**
     * V2: Handle Stripe Webhook with fingerprint extraction and duplicate detection.
     *
     * This is a separate webhook endpoint that should be configured in Stripe
     * Dashboard as a second webhook endpoint alongside the V1 webhook.
     *
     * Endpoint URL: https://your-domain.com/api/v2/webhook/stripe
     */
    public function handleStripeWebhook(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $webhookSecret = config('services.stripe.v2_webhook_secret');

        Log::info('[V2] Webhook received', [
            'signature_present' => !empty($sigHeader),
        ]);

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $webhookSecret);

            Log::info('[V2] Webhook event type: ' . $event->type);

            switch ($event->type) {
                case 'checkout.session.completed':
                    $this->paymentV2Service->handleCheckoutCompleted($event->data->object);
                    Log::info('[V2] Checkout completed webhook processed');
                    break;

                case 'payment_intent.succeeded':
                    $this->paymentV2Service->handlePaymentSucceeded($event->data->object);
                    Log::info('[V2] Payment succeeded webhook processed');
                    break;

                case 'payment_intent.payment_failed':
                    $this->paymentV2Service->handlePaymentFailed($event->data->object);
                    Log::info('[V2] Payment failed webhook processed');
                    break;

                default:
                    Log::info('[V2] Unhandled webhook event: ' . $event->type);
            }

            return response()->json(['success' => true], 200);
        } catch (Exception $e) {
            Log::error('[V2] Webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
