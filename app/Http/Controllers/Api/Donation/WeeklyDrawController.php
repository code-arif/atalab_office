<?php

namespace App\Http\Controllers\Api\Donation;

use App\Http\Controllers\Controller;
use App\Services\WeeklyDrawService;
use App\Traits\ApiResponse;
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
}
