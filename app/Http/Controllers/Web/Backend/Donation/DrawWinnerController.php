<?php

namespace App\Http\Controllers\Web\Backend\Donation;

use Carbon\Carbon;
use App\Models\DrawWinner;
use Illuminate\Http\Request;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;

class DrawWinnerController extends Controller
{
    /**
     * Display all winners with advanced filtering
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = DrawWinner::with(['user:id,name,email', 'weeklyDraw:id,week_number'])
                ->select('draw_winners.*');

            // Apply filters
            if ($request->filled('claim_status')) {
                if ($request->claim_status === 'claimed') {
                    $query->where('claimed', true);
                } elseif ($request->claim_status === 'unclaimed') {
                    $query->where('claimed', false);
                }
            }

            if ($request->filled('payout_status')) {
                $query->where('payout_status', $request->payout_status);
            }

            if ($request->filled('week_id')) {
                $query->where('weekly_draw_id', $request->week_id);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('draw_winners.created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('draw_winners.created_at', '<=', $request->date_to);
            }

            if ($request->filled('min_amount')) {
                $query->where('amount_won', '>=', $request->min_amount);
            }

            if ($request->filled('max_amount')) {
                $query->where('amount_won', '<=', $request->max_amount);
            }


            return DataTables::eloquent($query)
                ->addIndexColumn()
                ->addColumn('winner_name', fn($row) => $row->user->name ?? 'N/A')
                ->addColumn('email', fn($row) => $row->user->email ?? 'N/A')
                ->addColumn('week', fn($row) => '<span class="badge bg-primary">Week #' . $row->weeklyDraw->week_number . '</span>')
                ->addColumn('amount', fn($row) => '<span class="badge bg-success fs-6">$' . number_format($row->amount_won, 2) . '</span>')
                ->addColumn('claim_status', function ($row) {
                    if ($row->claimed) {
                        return '<span class="badge badge-sm bg-success py-2"><i class="fe fe-check me-1" style="font-size:10px;"></i>Claimed</span>';
                    } else {
                        return '<span class="badge badge-sm bg-warning py-2"><i class="fe fe-clock me-1" style="font-size:10px;"></i>Unclaimed</span>';
                    }
                })
                ->addColumn('claimed_date', function ($row) {
                    return $row->claimed_at
                        ? Carbon::parse($row->claimed_at)->format('M d, Y h:i A')
                        : '<span class="text-muted">Not claimed yet</span>';
                })
                ->addColumn('payout_status', function ($row) {
                    $badges = [
                        'pending' => '<span class="badge bg-secondary">Pending</span>',
                        'processing' => '<span class="badge bg-info">Processing</span>',
                        'completed' => '<span class="badge bg-success badge-sm py-2"><i class="fe fe-check-circle me-1" style="font-size:10px;"></i>Completed</span>',
                        'failed' => '<span class="badge badge-sm bg-danger"><i class="fe fe-x-circle me-1"></i>Failed</span>',
                    ];
                    return $badges[$row->payout_status] ?? '<span class="badge bg-secondary">N/A</span>';
                })
                ->addColumn('stripe_info', function ($row) {
                    if ($row->payout_stripe_id) {
                        return '<small class="text-muted" title="' . $row->payout_stripe_id . '">'
                            . substr($row->payout_stripe_id, 0, 20) . '...</small>';
                    }
                    return '<span class="text-muted">-</span>';
                })
                ->addColumn('action', function ($row) {
                    $actions = '<div class="btn-group" role="group">';

                    // View Details
                    $actions .= '<button type="button" class="btn btn-sm btn-info viewWinner"
                    data-id="' . $row->id . '"
                    title="View Details">
                     View Details
                    </button>';

                    // Verify Winner - Main Action Button
                    if (!$row->claimed) {
                        $actions .= '<a href="' . route('draw-winners.verify', $row->id) . '"
                        class="btn btn-sm btn-primary" title="Verify Winner"> Verify
                        </a>';
                    }

                    // Process Payout - If already verified and approved
                    if ($row->claimed && $row->payout_status === 'pending') {
                        // Check if verification is approved
                        $isApproved = $row->verification && $row->verification->verification_status === 'approved';

                        if ($isApproved) {
                            $actions .= '<button type="button" class="btn btn-sm btn-success processPayout"
                            data-id="' . $row->id . '" title="Process Payout">
                            Process Payout
                        </button>';
                        }
                    }

                    // Show verification status badge
                    if ($row->verification) {
                        $statusColors = [
                            'pending' => 'secondary',
                            'identity_review' => 'info',
                            'contact_verification' => 'info',
                            'bank_verification' => 'info',
                            'approved' => 'success',
                            'rejected' => 'danger'
                        ];
                        $color = $statusColors[$row->verification->verification_status] ?? 'secondary';
                        $statusText = ucfirst(str_replace('_', ' ', $row->verification->verification_status));

                        $actions .= '<span class="badge bg-' . $color . ' ms-2" style="font-size: 10px;">' .
                            $statusText .
                            '</span>';
                    }

                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['week', 'amount', 'claim_status', 'claimed_date', 'payout_status', 'stripe_info', 'action'])
                ->make(true);
        }

        // Statistics
        $stats = [
            'total_winners' => DrawWinner::count(),
            'claimed' => DrawWinner::where('claimed', true)->count(),
            'unclaimed' => DrawWinner::where('claimed', false)->count(),
            'total_payouts' => DrawWinner::where('payout_status', 'completed')->sum('amount_won'),
            'pending_payouts' => DrawWinner::where('claimed', true)
                ->where('payout_status', 'pending')
                ->sum('amount_won'),
        ];

        // Get weeks for filter dropdown
        $weeks = DB::table('weekly_draws')
            ->select('id', 'week_number')
            ->orderBy('week_number', 'desc')
            ->get();

        return view('backend.layouts.donation_&_draw.winners_index', compact('stats', 'weeks'));
    }


    /**
     * Show specific winner details
     */
    public function show(int $id): JsonResponse
    {
        try {
            $winner = DrawWinner::with([
                'user:id,name,email,phone',
                'weeklyDraw:id,week_number,start_date,end_date,total_pool',
                'donation:id,amount,stripe_payment_id,donated_at',
                'verification' => function ($query) {
                    $query->with('verifiedBy:id,name');
                }
            ])->findOrFail($id);

            // Add verification progress
            $verificationProgress = 0;
            if ($winner->verification) {
                if ($winner->verification->identity_verified) $verificationProgress += 25;
                if ($winner->verification->email_verified && $winner->verification->phone_verified) $verificationProgress += 25;
                if ($winner->verification->bank_verified) $verificationProgress += 25;
                if ($winner->verification->verification_status === 'approved') $verificationProgress += 25;
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $winner->id,
                    'user' => $winner->user,
                    'weekly_draw' => $winner->weeklyDraw,
                    'donation' => $winner->donation,
                    'amount_won' => $winner->amount_won,
                    'claimed' => $winner->claimed,
                    'claimed_at' => $winner->claimed_at,
                    'claim_status' => $this->getClaimStatus($winner),
                    'payout_status' => $winner->payout_status,
                    'payout_stripe_id' => $winner->payout_stripe_id,
                    'verification' => $winner->verification,
                    'verification_progress' => $verificationProgress
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Winner not found: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Mark winner as claimed
     */
    public function markClaimed(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'stripe_payout_id' => 'required|string|max:255',
            'admin_notes' => 'nullable|string|max:1000',
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

            if ($winner->claimed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Winner already claimed'
                ], 400);
            }

            $winner->update([
                'claimed' => true,
                'claimed_at' => now(),
                'payout_stripe_id' => $request->stripe_payout_id,
                'payout_status' => 'pending',
            ]);

            // Optional: Store admin notes in a separate table if needed
            if ($request->admin_notes) {
                DB::table('winner_notes')->insert([
                    'draw_winner_id' => $winner->id,
                    'admin_id' => auth()->id(),
                    'notes' => $request->admin_notes,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Winner marked as claimed successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process payout
     */
    public function processPayout(Request $request, int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $winner = DrawWinner::findOrFail($id);

            if (!$winner->claimed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Winner must be claimed first'
                ], 400);
            }

            if ($winner->payout_status === 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payout already completed'
                ], 400);
            }

            // Update payout status to processing
            $winner->update([
                'payout_status' => 'processing',
            ]);

            // After successful payout
            $winner->update([
                'payout_status' => 'completed',
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payout processed successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            // Mark as failed
            DrawWinner::where('id', $id)->update(['payout_status' => 'failed']);

            return response()->json([
                'success' => false,
                'message' => 'Payout failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update payout status
     */
    public function updatePayoutStatus(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,completed,failed',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $winner = DrawWinner::findOrFail($id);

            $winner->update([
                'payout_status' => $request->status,
            ]);

            if ($request->notes) {
                DB::table('winner_notes')->insert([
                    'draw_winner_id' => $winner->id,
                    'admin_id' => auth()->id(),
                    'notes' => $request->notes,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payout status updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export winners data
     */
    public function export(Request $request)
    {
        $query = DrawWinner::with(['user', 'weeklyDraw']);

        // Apply same filters as index
        if ($request->has('claim_status') && $request->claim_status !== '') {
            if ($request->claim_status === 'claimed') {
                $query->where('claimed', true);
            } elseif ($request->claim_status === 'unclaimed') {
                $query->where('claimed', false);
            }
        }

        $winners = $query->get();

        $filename = 'winners_' . now()->format('Y_m_d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($winners) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Winner Name', 'Email', 'Week', 'Amount Won', 'Claimed', 'Claimed At', 'Payout Status', 'Stripe ID']);

            foreach ($winners as $winner) {
                fputcsv($file, [
                    $winner->id,
                    $winner->user->name ?? 'N/A',
                    $winner->user->email ?? 'N/A',
                    'Week #' . $winner->weeklyDraw->week_number,
                    '$' . number_format($winner->amount_won, 2),
                    $winner->claimed ? 'Yes' : 'No',
                    $winner->claimed_at ? $winner->claimed_at->format('Y-m-d H:i:s') : 'N/A',
                    $winner->payout_status,
                    $winner->payout_stripe_id ?? 'N/A',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }



    /**
     * Helper method to determine claim status
     */
    private function getClaimStatus($winner)
    {
        if ($winner->claimed && $winner->verification && $winner->verification->verification_status === 'approved') {
            if ($winner->payout_status === 'completed') {
                return 'paid';
            }
            return 'approved_pending_payout';
        }

        if ($winner->claimed) {
            return 'claimed';
        }

        return 'pending';
    }
}
