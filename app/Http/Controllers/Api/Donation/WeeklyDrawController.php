<?php

namespace App\Http\Controllers\Api\Donation;

use App\Http\Controllers\Controller;
use App\Models\DrawWinner;
use App\Models\StripeSetting;
use App\Models\WeeklyDraw;
use App\Services\WeeklyDrawService;
use App\Traits\ApiResponse;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WeeklyDrawController extends Controller
{
    use ApiResponse;
    protected $weeklyDrawService;

    public function __construct(WeeklyDrawService $weeklyDrawService)
    {
        $this->weeklyDrawService = $weeklyDrawService;
    }

    /**
     * Get winners for all weeks with pagination
     */
    public function getWinners(Request $request)
    {
        // How many weeks per page (default 5)
        $perPage = $request->input('per_page', 5);

        // Step 1: Get unique weekly_draw_ids paginated
        $weeklyGroups = WeeklyDraw::orderBy('week_number', 'desc')
            ->paginate($perPage);

        // Step 2: Extract IDs for eager loading winners
        $weeklyDrawIds = $weeklyGroups->pluck('id');

        // Step 3: Load winners for these weeks
        $winners = DrawWinner::with(['user', 'donation'])
            ->whereIn('weekly_draw_id', $weeklyDrawIds)
            ->orderBy('id', 'desc')
            ->get()
            ->groupBy('weekly_draw_id');

        // Step 4: Format the response for frontend
        $formatted = $weeklyGroups->map(function ($week) use ($winners) {
            return [
                'week_number' => $week->week_number,
                'year' => $week->year,
                'winners' => isset($winners[$week->id]) ?
                    $winners[$week->id]->map(function ($item) {
                        return [
                            'name' => $item->user->name,
                            'city' => $item->user->city,
                            'state' => $item->user->state,
                            'image' => $item->user->profile_image_url,
                            'amount_won' => $item->amount_won,
                        ];
                    }) : []
            ];
        });

        // Step 5: Final API response with pagination structure
        return response()->json([
            'status' => true,
            'message' => 'Weekly winners fetched successfully',
            'data' => $formatted,
            'pagination' => [
                'total' => $weeklyGroups->total(),
                'per_page' => $weeklyGroups->perPage(),
                'current_page' => $weeklyGroups->currentPage(),
                'last_page' => $weeklyGroups->lastPage(),
            ]
        ]);
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
     * Get donation price
     */
    public function getPrice(): JsonResponse
    {
        try {
            $price = StripeSetting::select('donation_amount')->first();

            return response()->json([
                'success' => true,
                'message' => 'Donation price fetched successfully',
                'price' => $price->donation_amount
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }
}
