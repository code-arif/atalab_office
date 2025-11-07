<?php

namespace App\Http\Controllers\Api\CMS;

use App\Models\CMS;
use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\CMS\CMSResource;

class CmsController extends Controller
{
    use ApiResponse;
    /**
     * Get all home page CMS data
     */
    public function home()
    {
        $data = CMS::where('page', 'home')->get();

        return $this->success(CMSResource::collection($data), 'Home data retrieved successfully');
    }


    /**
     * Get our story page CMS data
     */
    public function ourStory()
    {
        $data = CMS::where('page', 'our-story')->get();

        return $this->success(CMSResource::collection($data), 'Our story data retrieved successfully');
    }


    /**
     * Get how it works page CMS data
     */
    public function howItWorks()
    {
        $data = CMS::where('page', 'how-it-works')->get();

        return $this->success(CMSResource::collection($data), 'How it works data retrieved successfully');
    }
}
