<?php

namespace App\Http\Controllers\Web\Backend\Donation;

use Carbon\Carbon;
use App\Models\DrawWinner;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Illuminate\Http\JsonResponse;
use App\Models\WinnerVerification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Laravel\Reverb\Protocols\Pusher\Http\Controllers\Controller;

class DrawWinnerController extends Controller
{
    /**
     * show all the winner list
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $winners = DrawWinner::with(['user', 'weeklyDraw', 'verification'])
                ->latest()
                ->get();

            return DataTables::of($winners)
                ->addIndexColumn()
                ->addColumn('winner_name', fn($row) => $row->user->name ?? 'N/A')
                ->addColumn('email', fn($row) => $row->user->email ?? 'N/A')
                ->addColumn('week', fn($row) => 'Week #' . $row->weeklyDraw->week_number)
                ->addColumn('amount', fn($row) => '<span class="badge bg-success fs-6">$' . number_format($row->amount_won, 2) . '</span>')
                ->addColumn('claim_status', function ($row) {
                    $badges = [
                        'pending' => '<span class="badge bg-warning">Pending</span>',
                        'claimed' => '<span class="badge bg-success">Claimed</span>',
                        'expired' => '<span class="badge bg-danger">Expired</span>',
                        'approved_pending_payout' => '<span class="badge bg-info">Approved - Pending Payout</span>',
                    ];
                    return $badges[$row->claim_status] ?? '<span class="badge bg-secondary">Unknown</span>';
                })
                ->addColumn('verification_status', function ($row) {
                    if (!$row->verification) {
                        return '<span class="badge bg-secondary">Not Started</span>';
                    }

                    $statuses = [
                        'pending' => '<span class="badge bg-warning">Pending</span>',
                        'identity_review' => '<span class="badge bg-info">Identity Review</span>',
                        'contact_verification' => '<span class="badge bg-info">Contact Verification</span>',
                        'bank_verification' => '<span class="badge bg-info">Bank Verification</span>',
                        'approved' => '<span class="badge bg-success">Approved</span>',
                        'rejected' => '<span class="badge bg-danger">Rejected</span>',
                    ];

                    return $statuses[$row->verification->verification_status] ?? '<span class="badge bg-secondary">Unknown</span>';
                })
                ->addColumn('progress', function ($row) {
                    if (!$row->verification) {
                        return '<div class="progress" style="height: 20px;">
                            <div class="progress-bar" role="progressbar" style="width: 0%">0%</div>
                        </div>';
                    }

                    $progress = $row->verification->getVerificationProgress();
                    $color = $progress < 50 ? 'bg-danger' : ($progress < 100 ? 'bg-warning' : 'bg-success');

                    return '<div class="progress" style="height: 20px;">
                        <div class="progress-bar ' . $color . '" role="progressbar" style="width: ' . $progress . '%">' . round($progress) . '%</div>
                    </div>';
                })
                ->addColumn('payout_status', function ($row) {
                    $badges = [
                        'pending' => '<span class="badge bg-secondary">Pending</span>',
                        'processing' => '<span class="badge bg-info">Processing</span>',
                        'completed' => '<span class="badge bg-success">Completed</span>',
                        'failed' => '<span class="badge bg-danger">Failed</span>',
                    ];
                    return $badges[$row->payout_status] ?? '<span class="badge bg-secondary">N/A</span>';
                })
                ->addColumn('action', function ($row) {
                    $actions = '<div class="btn-group">';

                    // View Details
                    $actions .= '<button type="button" class="btn btn-sm btn-info viewWinner" data-id="' . $row->id . '">
                        <i class="fe fe-eye"></i>
                    </button>';

                    // Verification Action
                    if (!$row->claimed && !$row->isClaimExpired()) {
                        $actions .= '<button type="button" class="btn btn-sm btn-primary verifyWinner" data-id="' . $row->id . '">
                            <i class="fe fe-check-circle"></i> Verify
                        </button>';
                    }

                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['amount', 'claim_status', 'verification_status', 'progress', 'payout_status', 'action'])
                ->make(true);
        }

        $stats = [
            'total_winners' => DrawWinner::count(),
            'claimed' => DrawWinner::where('claimed', true)->count(),
            'pending_verification' => DrawWinner::whereHas('verification', function ($q) {
                $q->where('verification_status', '!=', 'approved');
            })->count(),
            'total_payouts' => DrawWinner::where('payout_status', 'completed')->sum('amount_won'),
        ];

        return view('backend.layouts.donation_&_draw.winners_index', compact('stats'));
    }

    /**
     * show specific winner
     */
    public function show(int $id): JsonResponse
    {
        try {
            $winner = DrawWinner::with(['user', 'weeklyDraw', 'verification.verifiedBy'])
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $winner
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function initiateVerification(int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $winner = DrawWinner::findOrFail($id);

            // Check if already has verification
            if ($winner->verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification already initiated'
                ], 400);
            }

            // Create verification record
            $verification = WinnerVerification::create([
                'draw_winner_id' => $winner->id,
                'verification_status' => 'pending',
                'verified_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Verification process initiated',
                'data' => $verification
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyIdentity(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'drivers_license' => 'required|string|max:50',
            'license_state' => 'required|string|max:2',
            'license_expiry' => 'required|date|after:today',
            'identity_verified' => 'required|boolean',
            'admin_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $winner = DrawWinner::findOrFail($id);
            $verification = $winner->verification;

            if (!$verification) {
                return response()->json([
                    'success' => false,
                    'message' => 'Verification not initiated'
                ], 400);
            }

            $verification->update([
                'drivers_license' => $request->drivers_license,
                'license_state' => $request->license_state,
                'license_expiry' => $request->license_expiry,
                'identity_verified' => $request->identity_verified,
                'identity_verified_at' => $request->identity_verified ? now() : null,
                'verification_status' => 'contact_verification',
                'admin_notes' => $request->admin_notes,
                'verified_by' => auth()->id(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Identity verification completed'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function verifyBank(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required|string|max:100',
            'account_number_last4' => 'required|string|size:4',
            'routing_number' => 'required|string|size:9',
            'account_holder_name' => 'required|string|max:100',
            'bank_verified' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $winner = DrawWinner::findOrFail($id);
            $verification = $winner->verification;

            $verification->update([
                'bank_name' => $request->bank_name,
                'account_number_last4' => $request->account_number_last4,
                'routing_number' => $request->routing_number,
                'account_holder_name' => $request->account_holder_name,
                'bank_verified' => $request->bank_verified,
                'bank_verified_at' => $request->bank_verified ? now() : null,
                'verification_status' => 'bank_verification',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bank verification completed'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function approveClaim(Request $request, int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $winner = DrawWinner::with('verification')->findOrFail($id);
            $verification = $winner->verification;

            if (!$verification || !$verification->canApprove()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Winner cannot be approved. Complete all verification steps.'
                ], 400);
            }

            $verification->update([
                'verification_status' => 'approved',
                'approved_at' => now(),
                'admin_notes' => $request->admin_notes,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Claim approved successfully! Ready for payout.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function rejectClaim(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rejection_reason' => 'required|string|min:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $winner = DrawWinner::with('verification')->findOrFail($id);
            $verification = $winner->verification;

            $verification->update([
                'verification_status' => 'rejected',
                'rejected_at' => now(),
                'rejection_reason' => $request->rejection_reason,
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Claim rejected'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function verificationStatus(int $id): JsonResponse
    {
        try {
            $winner = DrawWinner::with('verification')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'has_verification' => (bool) $winner->verification,
                    'verification' => $winner->verification,
                    'progress' => $winner->verification ? $winner->verification->getVerificationProgress() : 0,
                    'can_approve' => $winner->verification ? $winner->verification->canApprove() : false,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function processPayout(int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $winner = DrawWinner::with('verification')->findOrFail($id);

            if (!$winner->verification || $winner->verification->verification_status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Winner must be approved before processing payout'
                ], 400);
            }

            // TODO: Integrate with Stripe for actual payout
            // For now, we'll just update the status
            $winner->update([
                'payout_status' => 'processing',
                'claimed' => true,
                'claimed_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payout processing initiated'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
