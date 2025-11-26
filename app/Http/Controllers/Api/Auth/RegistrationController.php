<?php

namespace App\Http\Controllers\Api\Auth;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\RegistrationService;
use Illuminate\Support\Facades\Validator;

class RegistrationController extends Controller
{
    protected $registrationService;

    public function __construct(RegistrationService $registrationService)
    {
        $this->registrationService = $registrationService;
    }

    /**
     * Register new user (sends OTP, stores in cache)
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone' => [
                'required',
                'string',
                'regex:/^\+1\d{10}$/',
            ],
            'address' => 'required|string|max:500',
        ], [
            'phone.regex' => 'Please enter a valid phone number (e.g., +12345678901)',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->registrationService->registerUser($request->all());

            return response()->json($result, $result['success'] ? 200 : 400);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify OTP (creates user in database)
     */
    public function verifyOTP(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string', // email or phone
            'otp_code' => 'required|string|size:4',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->registrationService->verifyOTP(
                $request->identifier,
                $request->otp_code
            );

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Resend OTP
     */
    public function resendOTP(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $result = $this->registrationService->resendOTP($request->identifier);

            return response()->json($result);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Check user status (for frontend routing)
     */
    public function checkUserStatus(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'identifier' => 'required|string', // email or phone
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $identifier = $request->identifier;

            $user = \App\Models\User::where('email', $identifier)
                ->orWhere('phone', $identifier)
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => true,
                    'status' => 'not_registered',
                    'message' => 'User not found. Please register.'
                ]);
            }

            // Check verification status
            if (!$user->email_verified_at || !$user->phone_verified_at) {
                return response()->json([
                    'success' => true,
                    'status' => 'not_verified',
                    'message' => 'Please verify your OTP.'
                ]);
            }

            // Check if donor
            if ($user->donor_id) {
                return response()->json([
                    'success' => true,
                    'status' => 'donor',
                    'user_id' => $user->id,
                    'donor_id' => $user->donor_id,
                    'message' => 'User is a registered donor.'
                ]);
            }

            // Verified but not donated yet
            return response()->json([
                'success' => true,
                'status' => 'verified',
                'user_id' => $user->id,
                'message' => 'User verified. Ready to donate.'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
