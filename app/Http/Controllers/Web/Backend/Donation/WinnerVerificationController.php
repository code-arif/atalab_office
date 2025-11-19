<?php

namespace App\Http\Controllers\Web\Backend\Donation;

use App\Models\DrawWinner;
use Illuminate\Http\Request;
use App\Models\WinnerVerification;
use App\Http\Controllers\Controller;

class WinnerVerificationController extends Controller
{
    // inital verification
    public function initiateVerification(DrawWinner $winner)
    {
        $verification = WinnerVerification::firstOrCreate(
            ['draw_winner_id' => $winner->id],
            ['verified_by' => auth()->id()]
        );

        return response()->json(['success' => true, 'verification' => $verification]);
    }

    // identity verification
    public function verifyIdentity(Request $request, DrawWinner $winner)
    {
        $request->validate([
            'drivers_license' => 'required|string',
            'license_state' => 'required|string|size:2',
            'license_expiry' => 'required|date|after:today',
            'admin_notes' => 'nullable|string',
        ]);

        $verification = $winner->verification ?? WinnerVerification::create([
            'draw_winner_id' => $winner->id,
            'verified_by' => auth()->id(),
        ]);

        $verification->update([
            'identity_verified' => true,
            'drivers_license' => $request->drivers_license,
            'license_state' => $request->license_state,
            'license_expiry' => $request->license_expiry,
            'identity_verified_at' => now(),
            'admin_notes' => $request->admin_notes,
            'verification_status' => 'contact_verification',
        ]);

        return response()->json(['success' => true, 'message' => 'Identity verified successfully']);
    }


    // contact info verification
    public function verifyContact(DrawWinner $winner)
    {
        $verification = $winner->verification;

        if (!$verification) {
            return response()->json(['success' => false, 'message' => 'Verification not initiated'], 400);
        }

        $verification->update([
            'email_verified' => true,
            'phone_verified' => true,
            'contact_verified_at' => now(),
            'verification_status' => 'bank_verification',
        ]);

        return response()->json(['success' => true, 'message' => 'Contact verified']);
    }


    // bank info verifcation
    public function verifyBank(Request $request, DrawWinner $winner)
    {
        $request->validate([
            'bank_name' => 'required|string',
            'account_holder_name' => 'required|string',
            'account_number_last4' => 'required|string|size:4',
            'routing_number' => 'required|string|size:9',
        ]);

        $verification = $winner->verification;

        $verification->update([
            'bank_verified' => true,
            'bank_name' => $request->bank_name,
            'account_holder_name' => $request->account_holder_name,
            'account_number_last4' => $request->account_number_last4,
            'routing_number' => $request->routing_number,
            'bank_verified_at' => now(),
            'verification_status' => 'approved', // or keep pending until final approve
        ]);

        return response()->json(['success' => true, 'message' => 'Bank verified']);
    }


    // approvel claim
    public function approveClaim(Request $request, DrawWinner $winner)
    {
        $verification = $winner->verification;

        if (!$verification || !$verification->identity_verified || !$verification->bank_verified) {
            return response()->json(['success' => false, 'message' => 'Incomplete verification'], 400);
        }

        $verification->update([
            'verification_status' => 'approved',
            'approved_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        $winner->update([
            'claimed' => true,
            'claimed_at' => now(),
            'payout_status' => 'pending'
        ]);

        return response()->json(['success' => true, 'message' => 'Claim approved successfully']);
    }

    // reject claim
    public function rejectClaim(Request $request, DrawWinner $winner)
    {
        $request->validate(['rejection_reason' => 'required|string|min:10']);

        $verification = $winner->verification;
        if ($verification) {
            $verification->update([
                'verification_status' => 'rejected',
                'rejection_reason' => $request->rejection_reason,
                'rejected_at' => now(),
            ]);
        }

        return response()->json(['success' => true, 'message' => 'Claim rejected']);
    }


    // get verification status
    public function getVerificationStatus(DrawWinner $winner)
    {
        $verification = $winner->verification;
        $progress = 0;
        if ($verification) {
            if ($verification->identity_verified) $progress += 33;
            if ($verification->email_verified && $verification->phone_verified) $progress += 33;
            if ($verification->bank_verified) $progress += 34;
        }

        return response()->json([
            'verification' => $verification,
            'progress' => $progress,
            'can_approve' => $verification && $verification->identity_verified && $verification->bank_verified
        ]);
    }
}
