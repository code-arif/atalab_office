<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;

class TestimonialController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $reviews = Review::latest()->get();

        return $this->success(
            ReviewResource::collection($reviews),
            'Customer reviews retrieved successfully'
        );
    }
}
