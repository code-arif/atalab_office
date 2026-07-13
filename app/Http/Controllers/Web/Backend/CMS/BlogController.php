<?php

namespace App\Http\Controllers\Web\Backend\CMS;

use App\Helper\Helper;
use App\Models\Blog;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class BlogController extends Controller
{
    /**
     * Display a listing of the blog posts (DataTable).
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $blogs = Blog::with('author')->latest();

            return DataTables::of($blogs)
                ->addIndexColumn()
                ->addColumn('featured_image', fn($item) => $item->featured_image
                    ? '<div class="d-flex justify-content-center">
                        <a href="' . e(asset($item->featured_image)) . '" target="_blank" title="View full image">
                            <img src="' . e(asset($item->featured_image)) . '" class="rounded" style="width:56px;height:56px;object-fit:cover;border:1px solid #e5e7eb;" alt="">
                        </a>
                       </div>'
                    : '<div class="d-flex justify-content-center">
                        <div class="rounded d-flex align-items-center justify-content-center bg-light" style="width:56px;height:56px;">
                            <i class="fa fa-image text-muted" style="font-size:18px;"></i>
                        </div>
                       </div>')
                ->addColumn('title', fn($item) => '
                    <div class="d-flex align-items-center">
                        <div>
                            <strong>' . e(Str::limit($item->title, 60)) . '</strong>
                            <br>
                            <small class="text-muted">' . e($item->slug) . '</small>
                        </div>
                    </div>')
                ->addColumn('author', fn($item) => $item->author
                    ? '<div class="d-flex align-items-center">
                        <span class="avatar avatar-xs me-2" style="background-image: url(' . e(asset($item->author->profile_photo_path ?? 'default/avatar.png')) . ')"></span>
                        ' . e($item->author->name) . '
                       </div>'
                    : '<em class="text-muted">—</em>')
                ->addColumn('status', fn($item) => '
                    <div class="d-flex align-items-center">
                        <label class="custom-switch">
                            <input type="checkbox" class="custom-switch-input status-toggle" data-id="' . $item->id . '" ' . ($item->status === 'published' ? 'checked' : '') . '>
                            <span class="custom-switch-indicator"></span>
                        </label>
                        <span class="ms-2 badge ' . ($item->status === 'published' ? 'bg-success' : 'bg-warning') . '">
                            ' . ucfirst($item->status) . '
                        </span>
                    </div>')
                ->addColumn('published_at', fn($item) => $item->published_at
                    ? $item->published_at->format('d M, Y')
                    : '<em class="text-muted">—</em>')
                ->addColumn('action', fn($item) => '
                    <div class="d-flex gap-1 justify-content-center">
                        <a href="' . route('blog.show', $item->id) . '" class="btn btn-sm btn-info" title="View">
                            <i class="fa fa-eye"></i>
                        </a>
                        <a href="' . route('blog.edit', $item->id) . '" class="btn btn-sm btn-primary" title="Edit">
                            <i class="fa fa-edit"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-danger" onclick="showDeleteConfirm(' . $item->id . ')" title="Delete">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>')
                ->rawColumns(['featured_image', 'title', 'author', 'status', 'published_at', 'action'])
                ->make(true);
        }

        return view('backend.layouts.blog.index');
    }

    /**
     * Show the form for creating a new blog post.
     */
    public function create()
    {
        return view('backend.layouts.blog.create');
    }

    /**
     * Store a newly created blog post in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'content'          => 'required|string',
            'excerpt'          => 'nullable|string|max:500',
            'featured_image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'status'           => 'required|in:draft,published',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords'    => 'nullable|string|max:255',
        ]);

        $validated['author_id'] = auth()->id();

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $imagePath = Helper::uploadImage($request->file('featured_image'), 'blog');
            if ($imagePath) {
                $validated['featured_image'] = $imagePath;
            }
        }

        if (empty($validated['meta_title'])) {
            $validated['meta_title'] = $validated['title'];
        }

        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        Blog::create($validated);

        return redirect()->route('blog.index')
            ->with('success', 'Blog post created successfully.');
    }

    /**
     * Display the specified blog post.
     */
    public function show(string $id)
    {
        $blog = Blog::with('author')->findOrFail($id);
        return view('backend.layouts.blog.show', compact('blog'));
    }

    /**
     * Show the form for editing the specified blog post.
     */
    public function edit(string $id)
    {
        $blog = Blog::with('author')->findOrFail($id);
        return view('backend.layouts.blog.edit', compact('blog'));
    }

    /**
     * Update the specified blog post in storage.
     */
    public function update(Request $request, string $id)
    {
        $blog = Blog::findOrFail($id);

        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'content'          => 'required|string',
            'excerpt'          => 'nullable|string|max:500',
            'featured_image'   => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'status'           => 'required|in:draft,published',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
            'meta_keywords'    => 'nullable|string|max:255',
        ]);

        // Regenerate slug if title changed
        if ($blog->title !== $validated['title']) {
            $validated['slug'] = $blog->generateUniqueSlug($validated['title']);
        }

        // Handle featured image upload — delete old image if a new one is uploaded
        if ($request->hasFile('featured_image')) {
            // Delete the old featured image
            if ($blog->featured_image) {
                Helper::deleteImage($blog->featured_image);
            }

            $imagePath = Helper::uploadImage($request->file('featured_image'), 'blog');
            if ($imagePath) {
                $validated['featured_image'] = $imagePath;
            }
        } else {
            // Keep the existing image if no new file uploaded
            $validated['featured_image'] = $blog->featured_image;
        }

        if (empty($validated['meta_title'])) {
            $validated['meta_title'] = $validated['title'];
        }

        // Set published_at when transitioning to published
        if ($validated['status'] === 'published' && $blog->status !== 'published') {
            $validated['published_at'] = now();
        }

        $blog->update($validated);

        return redirect()->route('blog.index')
            ->with('success', 'Blog post updated successfully.');
    }

    /**
     * Remove the specified blog post from storage.
     */
    public function destroy(string $id)
    {
        $blog = Blog::findOrFail($id);

        // Delete the featured image if it exists
        if ($blog->featured_image) {
            Helper::deleteImage($blog->featured_image);
        }

        $blog->delete();

        return response()->json([
            'success' => true,
            'message' => 'Blog post deleted successfully.',
        ]);
    }

    /**
     * Toggle the status of a blog post (draft <-> published).
     */
    public function toggleStatus(string $id)
    {
        $blog = Blog::findOrFail($id);

        if ($blog->status === 'published') {
            $blog->update([
                'status' => 'draft',
                'published_at' => null,
            ]);
            $message = 'Blog post moved to draft.';
        } else {
            $blog->update([
                'status' => 'published',
                'published_at' => now(),
            ]);
            $message = 'Blog post published successfully.';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
        ]);
    }
}
