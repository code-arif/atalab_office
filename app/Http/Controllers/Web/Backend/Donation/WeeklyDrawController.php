<?php

namespace App\Http\Controllers\Web\Backend\Donation;

use App\Http\Controllers\Controller;
use App\Models\DrawAutomateSetting;
use App\Models\WeeklyDraw;
use App\Services\WeeklyDrawService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class WeeklyDrawController extends Controller
{
    protected $weeklyDrawService;

    public function __construct(WeeklyDrawService $weeklyDrawService)
    {
        $this->weeklyDrawService = $weeklyDrawService;
    }

    /**
     * Display the draws listing page.
     */
    public function index()
    {
        // Statistics for cards
        $activeDraws = WeeklyDraw::where('status', 'active')->count();
        $currentDraw = WeeklyDraw::where('status', 'active')->first();
        $totalPool = $currentDraw ? $currentDraw->total_pool : 0;
        $totalParticipants = $currentDraw ? $currentDraw->total_participants : 0;
        $totalDraws = WeeklyDraw::count();

        return view('backend.layouts.donation_&_draw.draw_index', compact(
            'activeDraws',
            'totalPool',
            'totalParticipants',
            'totalDraws'
        ));
    }

    /**
     * DataTables AJAX data source for draws listing.
     */
    public function getData(Request $request)
    {
        $draws = WeeklyDraw::latest('week_number')->get();

        return DataTables::of($draws)
            ->addIndexColumn()
            ->addColumn('week_number', fn($row) => 'Week #' . $row->week_number)
            ->addColumn('status', function ($row) {
                if ($row->is_paused) {
                    return '<span class="badge bg-danger-transparent text-danger d-inline-flex align-items-center px-2 py-1">
                                <i class="fe fe-pause-circle me-1"></i>
                                Paused
                            </span>';
                }
                $badges = [
                    'active' => '<span class="badge badge-status status-active p-2 py-3">Active</span>',
                    'claiming' => '<span class="badge badge-status status-claiming p-2 py-3">Claiming</span>',
                    'completed' => '<span class="badge badge-status status-completed p-2 py-3">Completed</span>',
                ];
                return $badges[$row->status] ?? '<span class="badge bg-secondary">Unknown</span>';
            })
            ->addColumn('start_date', fn($row) => Carbon::parse($row->start_date)->format('M d, Y h:i A'))
            ->addColumn('end_date', fn($row) => Carbon::parse($row->end_date)->format('M d, Y h:i A'))
            ->addColumn('total_pool', fn($row) => '<span class="text-success fw-bold">$' . number_format($row->total_pool, 2) . '</span>')
            ->addColumn('total_participants', fn($row) => '<span class="badge bg-warning text-dark">' . number_format($row->total_participants) . '</span>')
            ->addColumn('action', function ($row) {
                $actions = '<div class="btn-action-group">';

                // View Button
                $actions .= '<button type="button" class="btn btn-info btn-sm viewDraw" data-id="' . $row->id . '" title="View Details">
                    <i class="fe fe-eye"></i>
                </button>';

                // Pause/Active Button for active draws
                if ($row->status === 'active') {
                    if ($row->is_paused) {
                        $actions .= '<button type="button" onclick="togglePauseDraw(' . $row->id . ', false)" class="btn btn-success btn-sm" title="Make Active">
                            <i class="fe fe-play"></i>
                        </button>';
                    } else {
                        $actions .= '<button type="button" onclick="confirmPauseDraw(' . $row->id . ')" class="btn btn-secondary btn-sm" title="Pause Draw">
                            <i class="fe fe-pause"></i>
                        </button>';

                        // Manual Finalize Button (emergency/manual action - only for active, non-paused draws)
                        $actions .= '<a href="' . route('manual-finalize.show', $row->id) . '" class="btn btn-warning btn-sm" title="Manual Finalize">
                            <i class="fe fe-zap"></i>
                        </a>';
                    }
                }

                // Soft Delete Button
                $actions .= '<button type="button" onclick="softDeleteDraw(' . $row->id . ')" class="btn btn-danger btn-sm" title="Delete Draw">
                    <i class="fe fe-trash-2"></i>
                </button>';

                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['status', 'total_pool', 'total_participants', 'action'])
            ->make(true);
    }


    /**
     * Show trashed (deleted) draws
     */
    public function trashed(Request $request)
    {
        if ($request->ajax()) {
            $draws = WeeklyDraw::onlyTrashed()->latest('deleted_at')->get();

            return DataTables::of($draws)
                ->addIndexColumn()
                ->addColumn('week_number', fn($row) => 'Week #' . $row->week_number)
                ->addColumn('deleted_at', fn($row) => Carbon::parse($row->deleted_at)->format('M d, Y h:i A'))
                ->addColumn('status', function ($row) {
                    $badges = [
                        'active' => '<span class="badge bg-primary">Active (Deleted)</span>',
                        'claiming' => '<span class="badge bg-warning">Claiming (Deleted)</span>',
                        'completed' => '<span class="badge bg-secondary">Completed (Deleted)</span>',
                    ];
                    return $badges[$row->status] ?? '<span class="badge bg-danger">Deleted</span>';
                })
                ->addColumn('total_pool', fn($row) => '<span class="text-success fw-bold">$' . number_format($row->total_pool, 2) . '</span>')
                ->addColumn('total_participants', fn($row) => '<span class="badge bg-info">' . number_format($row->total_participants) . '</span>')
                ->addColumn('action', function ($row) {
                    $actions = '<div class="btn-action-group">';

                    // Restore Button
                    $actions .= '<button type="button" onclick="restoreDraw(' . $row->id . ')" class="btn btn-success btn-sm" title="Restore">
                    <i class="fe fe-refresh-cw"></i>
                </button>';

                    // Force Delete Button
                    $actions .= '<button type="button" onclick="forceDeleteDraw(' . $row->id . ')" class="btn btn-danger btn-sm" title="Permanent Delete">
                    <i class="fe fe-trash"></i>
                </button>';

                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['status', 'total_pool', 'total_participants', 'action'])
                ->make(true);
        }

        $trashedCount = WeeklyDraw::onlyTrashed()->count();

        return view('backend.layouts.donation_&_draw.draw_trashed', compact('trashedCount'));
    }


    /**
     * Show draw details
     */
    public function show(int $id): JsonResponse
    {
        try {
            $draw = WeeklyDraw::withTrashed()->findOrFail($id);
            $settings = DrawAutomateSetting::first();
            $expectedWinners = $draw->total_participants > 0 ? (int) ceil($draw->total_participants / $settings->odds_ratio) : 0;
            
            // Participant breakdown
            $rollovers = \App\Models\DrawParticipant::where('weekly_draw_id', $id)->where('is_rollover', true)->count();
            $newParticipants = \App\Models\DrawParticipant::where('weekly_draw_id', $id)->where('is_rollover', false)->count();

            return response()->json([
                'success' => true,
                'data' => array_merge($draw->toArray(), [
                    'expected_winners' => $expectedWinners,
                    'distribution_pool' => $draw->total_pool - $draw->admin_commission,
                    'rollovers' => $rollovers,
                    'new_participants' => $newParticipants,
                    'settings' => $settings
                ])
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Draw not found'
            ], 404);
        }
    }

    /**
     * Delete draw
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $draw = WeeklyDraw::findOrFail($id);

            // Only allow deletion of completed draws
            if ($draw->status !== 'completed') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only completed draws can be deleted'
                ], 400);
            }

            $draw->delete();

            return response()->json([
                'success' => true,
                'message' => 'Draw deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Restore draw
     */
    public function restore(int $id): JsonResponse
    {
        try {
            $draw = WeeklyDraw::onlyTrashed()->findOrFail($id);
            $draw->restore();

            return response()->json([
                'success' => true,
                'message' => 'Draw restored successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Frource delete
     */
    public function forceDelete(int $id): JsonResponse
    {
        try {
            $draw = WeeklyDraw::onlyTrashed()->findOrFail($id);

            // Permanently delete the draw
            $draw->forceDelete();

            return response()->json([
                'success' => true,
                'message' => 'Draw permanently deleted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Terminate / Pause a Draw
     */
    public function togglePause(int $id, Request $request): JsonResponse
    {
        try {
            $draw = WeeklyDraw::findOrFail($id);
            $action = $request->boolean('pause'); // true for pause, false for active

            if ($draw->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only active draws can be paused or resumed'
                ], 400);
            }

            $draw->is_paused = $action;
            $draw->save();

            return response()->json([
                'success' => true,
                'message' => $action ? 'Draw paused successfully. Donations stopped.' : 'Draw is now active.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
