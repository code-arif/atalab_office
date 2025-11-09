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
        $data = CMS::where('page', 'how-it-works',)->get();

        return $this->success(CMSResource::collection($data), 'How it works data retrieved successfully');
    }

    /**
     * Get structure page CMS data
     */
    public function structure()
    {
        $data = CMS::where('page', 'structure')->get();

        return $this->success(CMSResource::collection($data), 'Structure data retrieved successfully');
    }

    /**
     * Get eligibility page CMS data
     */
    public function eligibility()
    {
        $data = CMS::where('page', 'eligibility')->get();
        return $this->success(CMSResource::collection($data), 'Eligibility data retrieved successfully');
    }

    /**
     * Get payment policy page CMS data
     */
    public function paymentPolicy()
    {
        $data = CMS::where('page', 'payment-policy')->get();

        return $this->success(CMSResource::collection($data), 'Payment policy data retrieved successfully');
    }

    /**
     * Get tax policy page CMS data
     */
    public function taxPolicy()
    {
        $data = CMS::where('page', 'tax-policy')->get();

        return $this->success(CMSResource::collection($data), 'Tax policy data retrieved successfully');
    }

    /**
     * Get ethical boundaries page CMS data
     */
    public function ethicalBoundaries()
    {
        $data = CMS::where('page', 'ethical-boundaries')->get();

        return $this->success(CMSResource::collection($data), 'Ethical boundaries data retrieved successfully');
    }

    /**
     * Get officer compensation policy page CMS data
     */
    public function officerCompensationPolicy()
    {
        $data = CMS::where('page', 'officer_compensation_policy')->get();

        return $this->success(CMSResource::collection($data), 'Officer compensation policy data retrieved successfully');
    }

    /**
     * Get archives page CMS data
     */
    public function archives()
    {
        $data = CMS::where('page', 'archives')->get();

        return $this->success(CMSResource::collection($data), 'Archives data retrieved successfully');
    }

    /**
     * Get contact us page CMS data
     */    public function contactUs()
    {
        $data = CMS::where('page', 'contact-us')->get();
        return $this->success(CMSResource::collection($data), 'Contact us data retrieved successfully');
    }

    /**
     * Get footer partials CMS data
     */
    public function topbarPartials()
    {
        $data = CMS::where('page', 'partials')->where ('section', 'topbar')->get();
        return $this->success($data, 'Topbar partials data retrieved successfully');
    }
}
