<?php

namespace App\Http\Controllers\Web\Backend;

use Exception;
use App\Helper\Helper;
use App\Models\Review;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class ReviewController extends Controller
{

    // list of all reviews
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

        return view('backend.layouts.reviews.index', compact('count'));
    }


    // store review
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'author_name'   => 'required|string|max:100|unique:reviews,author_name',
            'review_text'   => 'nullable|string',
            'rating'        => 'nullable|in:1,2,3,4,5',
            'week_label'    => 'nullable|string|max:100',
            'author_avatar' => 'nullable|image|max:5120',
        ]);

        try {
            // Handle image upload if provided
            if ($request->hasFile('author_avatar')) {
                $validatedData['author_avatar'] = Helper::uploadImage($request->file('author_avatar'), 'review/images');
            }

            Review::create($validatedData);
            return response()->json([
                'success' => true,
                'message' => 'Review created successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // Edit review
    public function edit($id)
    {
        try {
            $review = Review::find($id);

            if (!$review) {
                return response()->json(['success' => false, 'message' => 'Review not found.'], 404);
            }

            return response()->json(['success' => true, 'data' => $review]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch review. ' . $e->getMessage()]);
        }
    }

    // View review
    public function show($id)
    {
        try {
            $review = Review::find($id);

            if (!$review) {
                return response()->json(['success' => false, 'message' => 'Review not found.'], 404);
            }

            return response()->json(['success' => true, 'data' => $review]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch review. ' . $e->getMessage()]);
        }
    }

    // update review
    public function update(Request $request, $id)
    {
        $review = Review::find($id);
        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found.'
            ], 404);
        }

        $validatedData = $request->validate([
            'author_name'   => 'required|string|max:100|unique:reviews,author_name,' . $review->id,
            'review_text'   => 'nullable|string',
            'rating'        => 'nullable|in:1,2,3,4,5',
            'week_label'    => 'nullable|string|max:100',
            'author_avatar' => 'nullable|image|max:5120',
        ]);

        try {
            // Handle image upload if provided
            if ($request->hasFile('author_avatar')) {
                // Delete old image
                if ($review->author_avatar) {
                    Helper::deleteImage($review->author_avatar);
                }
                $validatedData['author_avatar'] = Helper::uploadImage($request->file('author_avatar'), 'review/images');
            }

            $review->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Review updated successfully!',
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Review.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // delete review
    public function destroy($id)
    {
        $review = Review::find($id);
        if (!$review) {
            return response()->json([
                'success' => false,
                'message' => 'Review not found.'
            ], 404);
        }

        try {
            // Delete image if exists
            if ($review->author_avatar) {
                Helper::deleteImage($review->author_avatar);
            }

            $review->delete();

            return response()->json([
                'success' => true,
                'message' => 'Review deleted successfully.'
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Review.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
