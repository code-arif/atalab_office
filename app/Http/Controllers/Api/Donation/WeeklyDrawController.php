<?php

namespace App\Http\Controllers\Api\Donation;

use App\Http\Controllers\Controller;
use App\Services\WeeklyDrawService;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WeeklyDrawController extends Controller
{
    use ApiResponse;
    protected $weeklyDrawService;

    public function __construct(WeeklyDrawService $weeklyDrawService)
    {
        $this->weeklyDrawService = $weeklyDrawService;
    }

    /**
     * Get current active draw
     */
    public function getCurrentDraw(): JsonResponse
    {
        try {
            $draw = $this->weeklyDrawService->getCurrentDraw();

            return response()->json([
                'success' => true,
                'draw' => $draw
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get draw by week number
     */
    public function getDrawByWeek(int $weekNumber): JsonResponse
    {
        try {
            $draw = $this->weeklyDrawService->getDrawByWeekNumber($weekNumber);

            return response()->json([
                'success' => true,
                'draw' => $draw
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get draw statistics
     */
    public function getDrawStats(int $weekId): JsonResponse
    {
        try {
            $stats = $this->weeklyDrawService->getDrawStats($weekId);

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create new draw (Admin)
     */
    public function createNewDraw(Request $request): JsonResponse
    {
        try {
            $draw = $this->weeklyDrawService->createNewDraw();

            return response()->json([
                'success' => true,
                'draw' => $draw,
                'message' => 'New weekly draw created successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finalize draw and start claiming period (Admin)
     */
    public function finalizeDraw(int $weekId): JsonResponse
    {
        try {
            $draw = $this->weeklyDrawService->finalizeDraw($weekId);

            return response()->json([
                'success' => true,
                'draw' => $draw,
                'message' => 'Draw finalized and claiming period started'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Select winners for the draw (Admin)
     */
    public function selectWinners(int $weekId): JsonResponse
    {
        try {
            $result = $this->weeklyDrawService->selectWinners($weekId);

            return response()->json([
                'success' => true,
                'winners' => $result['winners'],
                'total_distributed' => $result['total_distributed'],
                'admin_commission' => $result['admin_commission'],
                'message' => 'Winners selected successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all draws (Admin)
     */
    public function getAllDraws(Request $request): JsonResponse
    {
        try {
            $draws = $this->weeklyDrawService->getAllDraws(
                $request->input('page', 1),
                $request->input('per_page', 20)
            );

            return response()->json([
                'success' => true,
                'draws' => $draws
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get overview statistics (Admin)
     */
    public function getOverviewStats(): JsonResponse
    {
        try {
            $stats = $this->weeklyDrawService->getOverviewStats();

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
