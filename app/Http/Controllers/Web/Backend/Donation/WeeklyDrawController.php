<?php

namespace App\Http\Controllers\Web\Backend\Donation;

use App\Http\Controllers\Controller;
use App\Services\WeeklyDrawService;
use App\Models\WeeklyDraw;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class WeeklyDrawController extends Controller
{
    protected $weeklyDrawService;

    public function __construct(WeeklyDrawService $weeklyDrawService)
    {
        $this->weeklyDrawService = $weeklyDrawService;
    }

    /**
     * Display listing with DataTables
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $draws = WeeklyDraw::latest('week_number')->get();

            return DataTables::of($draws)
                ->addIndexColumn()
                ->addColumn('week_number', fn($row) => 'Week #' . $row->week_number)
                ->addColumn('status', function ($row) {
                    $badges = [
                        'active' => '<span class="badge badge-status status-active p-3">Active</span>',
                        'claiming' => '<span class="badge badge-status status-claiming p-3">Claiming</span>',
                        'completed' => '<span class="badge badge-status status-completed p-3">Completed</span>',
                    ];
                    return $badges[$row->status] ?? '<span class="badge bg-secondary">Unknown</span>';
                })
                ->addColumn('start_date', fn($row) => Carbon::parse($row->start_date)->format('M d, Y h:i A'))
                ->addColumn('end_date', fn($row) => Carbon::parse($row->end_date)->format('M d, Y h:i A'))
                ->addColumn('total_pool', fn($row) => '<span class="text-success fw-bold">$' . number_format($row->total_pool, 2) . '</span>')
                ->addColumn('total_participants', fn($row) => '<span class="badge bg-warning text-dark">' . $row->total_participants . '</span>')
                ->addColumn('total_recipients', function ($row) {
                    return $row->total_recipients > 0
                        ? '<span class="badge bg-info">' . $row->total_recipients . '</span>'
                        : '<span class="text-muted">---</span>';
                })
                ->addColumn('admin_commission', fn($row) => '<span class="text-info">$' . number_format($row->admin_commission, 2) . '</span>')
                ->addColumn('winners_selected', function ($row) {
                    return $row->winners_selected
                        ? '<span class="badge bg-success"> Yes</span>'
                        : '<span class="badge bg-secondary"> No</span>';
                })
                ->addColumn('action', function ($row) {
                    $actions = '<div class="btn-action-group">';

                    // View Button
                    $actions .= '<button type="button" class="btn btn-info btn-sm viewDraw" data-id="' . $row->id . '" title="View Details">
                        <i class="fe fe-eye"></i>
                    </button>';

                    // Finalize Button (only for active draws)
                    if ($row->status === 'active') {
                        $actions .= '<button type="button" onclick="finalizeDraw(' . $row->id . ')" class="btn btn-warning btn-sm" title="Finalize Draw">
                            <i class="fe fe-check-circle"></i>
                        </button>';
                    }

                    // Select Winners Button (only for claiming status without winners)
                    if ($row->status === 'claiming' && !$row->winners_selected) {
                        $actions .= '<button type="button" onclick="selectWinners(' . $row->id . ')" class="btn btn-success btn-sm" title="Select Winners">
                            <i class="fe fe-users"></i>
                        </button>';
                    }

                    // Delete Button (only for completed draws)
                    if ($row->status === 'completed') {
                        $actions .= '<button type="button" onclick="showDeleteConfirm(' . $row->id . ')" class="btn btn-danger btn-sm" title="Delete Draw">
                            <i class="fe fe-trash"></i>
                        </button>';
                    }

                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['status', 'total_pool', 'total_participants', 'total_recipients', 'admin_commission', 'winners_selected', 'action'])
                ->make(true);
        }

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
     * Create new draw
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $draw = $this->weeklyDrawService->createNewDraw();

            return response()->json([
                'success' => true,
                'draw' => $draw,
                'message' => 'New weekly draw created successfully! Week #' . $draw->week_number
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show draw details
     */
    public function show(int $id): JsonResponse
    {
        try {
            $draw = WeeklyDraw::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $draw
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Draw not found'
            ], 404);
        }
    }

    /**
     * Finalize draw
     */
    public function finalize(int $id): JsonResponse
    {
        try {
            $draw = $this->weeklyDrawService->finalizeDraw($id);

            return response()->json([
                'success' => true,
                'draw' => $draw,
                'message' => 'Draw finalized successfully! Claiming period has started.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Select winners
     */
    public function selectWinners(int $id): JsonResponse
    {
        try {
            $result = $this->weeklyDrawService->selectWinners($id);

            return response()->json([
                'success' => true,
                'winners' => $result['winners'],
                'total_distributed' => number_format($result['total_distributed'], 2),
                'admin_commission' => number_format($result['admin_commission'], 2),
                'recipients' => $result['recipients'],
                'message' => 'Winners selected successfully!'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
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
}
