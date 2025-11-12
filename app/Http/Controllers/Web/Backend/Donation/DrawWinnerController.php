<?php

namespace App\Http\Controllers\Web\Backend\Donation;

use App\Http\Controllers\Controller;
use App\Services\DrawWinnerService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DrawWinnerController extends Controller
{
    protected $drawWinnerService;

    public function __construct(DrawWinnerService $drawWinnerService)
    {
        $this->drawWinnerService = $drawWinnerService;
    }

    /**
     * Get winners by week number
     */
    public function getWeeklyWinners(int $weekNumber): JsonResponse
    {
        try {
            $winners = $this->drawWinnerService->getWinnersByWeek($weekNumber);

            return response()->json([
                'success' => true,
                'winners' => $winners
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get latest winners
     */
    public function getLatestWinners(): JsonResponse
    {
        try {
            $winners = $this->drawWinnerService->getLatestWinners();

            return response()->json([
                'success' => true,
                'winners' => $winners
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get all winners (Admin)
     */
    public function getAllWinners(Request $request): JsonResponse
    {
        try {
            $winners = $this->drawWinnerService->getAllWinners(
                $request->input('page', 1),
                $request->input('per_page', 50)
            );

            return response()->json([
                'success' => true,
                'winners' => $winners
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process payout for winner (Admin)
     */
    public function processPayout(int $winnerId): JsonResponse
    {
        try {
            $result = $this->drawWinnerService->processPayout($winnerId);

            return response()->json([
                'success' => true,
                'winner' => $result,
                'message' => 'Payout processed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending payouts (Admin)
     */
    public function getPendingPayouts(): JsonResponse
    {
        try {
            $payouts = $this->drawWinnerService->getPendingPayouts();

            return response()->json([
                'success' => true,
                'payouts' => $payouts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
