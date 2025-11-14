<?php

namespace App\Http\Controllers\Web\Backend\CMS\Home;

use Exception;
use App\Models\CMS;
use App\Models\Review;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Requests\CmsRequest;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class TestimonialController extends Controller
{
    /**
     * show home page testimonial section data and section item
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $reviews = Review::latest('id')->get();

            return DataTables::of($reviews)
                ->addIndexColumn()
                ->addColumn('author_name', fn($row) => $row->author_name)
                ->addColumn('review_text', fn($row) => Str::limit(strip_tags($row->review_text), 80, '...'))
                ->addColumn('rating', fn($row) => $row->rating ?? '---')
                ->addColumn('week_label', fn($row) => $row->week_label ?? '---')
                ->addColumn('author_avatar', function ($row) {
                    if ($row->author_avatar) {
                        return '<img src="' . asset($row->author_avatar) . '" alt="' . $row->author_name . '" width="80">';
                    }
                    return '---';
                })
                ->addColumn('action', function ($row) {
                    return '<div class="d-flex gap-1">
                    <button type="button" class="btn btn-info btn-sm viewReview" data-id="' . $row->id . '">
                        <i class="fe fe-eye"></i>
                    </button>
                    <button type="button" class="btn btn-primary btn-sm editReview" data-id="' . $row->id . '">
                        <i class="fe fe-edit"></i>
                    </button>
                    <button type="button" onclick="showDeleteConfirm(' . $row->id . ')" class="btn btn-danger btn-sm">
                        <i class="fe fe-trash"></i>
                    </button>
                    </div>';
                })
                ->rawColumns(['author_avatar', 'action'])
                ->make(true);
        }

        $count = Review::count();
        $data = CMS::where('page', 'home')->where('section', 'testimonial')->where('name', 'item')->first();

        return view('backend.layouts.cms.home.testimonial', compact(['count', 'data']));
    }


    /**
     * update testimonial section
     **/
    public function update(CmsRequest $request)
    {
        try {
            $validated_data = $request->validated();

            // get the existing record
            CMS::where('page', 'home')
                ->where('section', 'testimonial')
                ->where('name', 'item')
                ->first();

            CMS::updateOrCreate(
                [
                    'page' => 'home',
                    'section' => 'testimonial',
                    'name' => 'item'
                ],
                $validated_data
            );

            return back()->with('t-success', 'Content updated successfully!');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update: ' . $e->getMessage());
        }
    }
}
