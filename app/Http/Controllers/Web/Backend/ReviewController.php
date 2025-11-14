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
