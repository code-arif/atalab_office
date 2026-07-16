<?php

namespace App\Http\Controllers\Api\Blog;

use App\Models\Blog;
use App\Traits\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\Blog\BlogResource;

class BlogController extends Controller
{
    use ApiResponse;

    /**
     * Get a paginated list of published blog posts.
     *
     * @queryParam per_page int Number of items per page (default: 10). Example: 15
     * @queryParam page int Page number. Example: 2
     */
    public function index()
    {
        $perPage = (int) request()->query('per_page', 10);
        $perPage = max(1, min(50, $perPage)); // Clamp between 1 and 50

        $blogs = Blog::published()
            ->with('author')
            ->orderBy('published_at', 'desc')
            ->paginate($perPage);

        return $this->success(
            [
                'blogs'        => BlogResource::collection($blogs),
                'page'         => $blogs->currentPage(),
                'per_page'     => $blogs->perPage(),
                'current_page' => $blogs->currentPage(),
                'total'        => $blogs->total(),
            ],
            'Blog posts retrieved successfully'
        );
    }

    /**
     * Get a single blog post by its slug.
     *
     * @urlParam slug string required The slug of the blog post. Example: my-first-blog-post
     */
    public function show(string $slug)
    {
        $blog = Blog::published()
            ->with('author')
            ->where('slug', $slug)
            ->first();

        if (!$blog) {
            return $this->error(null, 'Blog post not found', 404);
        }

        return $this->success(
            (new BlogResource($blog))->forDetail(),
            'Blog post retrieved successfully'
        );
    }
}
