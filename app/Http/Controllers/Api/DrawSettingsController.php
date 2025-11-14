<?php

namespace App\Http\Controllers\Api;

use App\Models\DrawSetting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\DrawSettingResource;

class DrawSettingsController extends Controller
{
    /**
     * Get all draw settings
     */
    public function index(): JsonResponse
    {
        $settings = DrawSetting::latest()->get();

        return response()->json([
            'success' => true,
            'data' => DrawSettingResource::collection($settings)
        ]);
    }
}
