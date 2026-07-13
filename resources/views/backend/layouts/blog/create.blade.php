@extends('backend.app')

@section('title', 'Create Blog Post')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<style>
    .meta-card {
        background: #f8f9fc;
        border: 1px dashed #d2d6dc;
        border-radius: 8px;
        padding: 1.25rem;
        margin-top: 1rem;
    }
    .meta-card .form-label {
        font-size: 0.85rem;
        font-weight: 600;
        color: #4b5563;
    }
    .slug-preview {
        font-size: 0.8rem;
        color: #6b7280;
        background: #eef0f5;
        padding: 0.2rem 0.6rem;
        border-radius: 4px;
        display: inline-block;
    }
    .seo-badge {
        background: #e8edf5;
        color: #4a5a7a;
        font-size: 0.7rem;
        padding: 0.15rem 0.5rem;
        border-radius: 4px;
    }

    /* ── Summernote fixes ── */
    .note-editor.note-frame {
        border-radius: 8px;
        overflow: hidden;
    }
    .note-editor .note-toolbar {
        background-color: #fafbfc;
        border-bottom: 1px solid #e8eaed;
        padding: 10px 10px 5px 10px;
    }
    .note-editor .note-statusbar {
        background-color: #fafbfc;
        border-top: 1px solid #e8eaed;
    }
    .note-editor .note-editing-area .note-editable {
        padding: 20px;
        font-size: 14px;
    }
    .note-editor .note-editing-area .note-placeholder {
        padding: 20px;
        font-size: 14px;
    }
</style>
@endpush

@section('content')
<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <div class="page-header">
                <div>
                    <h1 class="page-title">Create Blog Post</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('blog.index') }}">Blog</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Create</li>
                    </ol>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header border-bottom">
                            <h3 class="card-title mb-0">New Blog Post</h3>
                        </div>
                        <div class="card-body">
                            <form class="form-horizontal" method="POST" action="{{ route('blog.store') }}" enctype="multipart/form-data">
                                @csrf

                                <div class="row mb-4">
                                    <!-- Title -->
                                    <div class="form-group mb-3">
                                        <label for="title" class="form-label">Blog Title <span class="text-danger">*</span></label>
                                        <input type="text"
                                               class="form-control @error('title') is-invalid @enderror"
                                               name="title"
                                               id="title"
                                               placeholder="Enter an SEO-friendly blog title..."
                                               value="{{ old('title') }}">
                                        @error('title')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        <small class="text-muted">
                                            <span class="seo-badge">SEO</span> The title will be automatically converted to a URL-friendly slug.
                                        </small>
                                    </div>

                                    <!-- Slug Preview -->
                                    <div class="mb-3">
                                        <span class="slug-preview" id="slug-preview">URL: /blog/your-title-here</span>
                                    </div>

                                    <!-- Content -->
                                    <div class="form-group mb-3">
                                        <label for="content" class="form-label">Content <span class="text-danger">*</span></label>
                                        <textarea placeholder="Write your blog content here..."
                                                  id="content"
                                                  name="content"
                                                  class="form-control @error('content') is-invalid @enderror"
                                                  rows="15">{{ old('content') }}</textarea>
                                        @error('content')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Excerpt -->
                                    <div class="form-group mb-3">
                                        <label for="excerpt" class="form-label">Excerpt</label>
                                        <textarea placeholder="Brief summary of the post (auto-generated from content if left empty)..."
                                                  id="excerpt"
                                                  name="excerpt"
                                                  class="form-control @error('excerpt') is-invalid @enderror"
                                                  rows="3">{{ old('excerpt') }}</textarea>
                                        @error('excerpt')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Featured Image -->
                                    <div class="form-group mb-3">
                                        <label for="featured_image" class="form-label">Featured Image</label>
                                        <input type="file"
                                               class="dropify form-control @error('featured_image') is-invalid @enderror"
                                               name="featured_image"
                                               id="featured_image"
                                               accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                                               data-height="250"
                                               data-allowed-file-extensions="jpeg png jpg gif webp">
                                        @error('featured_image')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        <small class="text-muted">Accepted: JPEG, PNG, JPG, GIF, WebP (max 5MB)</small>
                                    </div>

                                    <!-- Status -->
                                    <div class="form-group mb-3">
                                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                        <select name="status" id="status" class="form-control @error('status') is-invalid @enderror">
                                            <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                                            <option value="published" {{ old('status') === 'published' ? 'selected' : '' }}>Published</option>
                                        </select>
                                        @error('status')
                                        <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- SEO Meta Section -->
                                    <div class="meta-card">
                                        <div class="d-flex align-items-center mb-3">
                                            <i class="fa fa-search me-2 text-primary"></i>
                                            <strong class="mb-0">SEO Metadata</strong>
                                            <span class="badge bg-info-soft ms-2 seo-badge">Improves Search Ranking</span>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="meta_title" class="form-label">Meta Title</label>
                                                    <input type="text"
                                                           class="form-control @error('meta_title') is-invalid @enderror"
                                                           name="meta_title"
                                                           id="meta_title"
                                                           placeholder="Defaults to blog title"
                                                           value="{{ old('meta_title') }}">
                                                    @error('meta_title')
                                                    <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                    <small class="text-muted">Recommended: 50-60 characters</small>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group mb-3">
                                                    <label for="meta_keywords" class="form-label">Meta Keywords</label>
                                                    <input type="text"
                                                           class="form-control @error('meta_keywords') is-invalid @enderror"
                                                           name="meta_keywords"
                                                           id="meta_keywords"
                                                           placeholder="blog, laravel, seo"
                                                           value="{{ old('meta_keywords') }}">
                                                    @error('meta_keywords')
                                                    <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                    <small class="text-muted">Comma-separated keywords</small>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label for="meta_description" class="form-label">Meta Description</label>
                                            <textarea placeholder="A concise description for search results..."
                                                      id="meta_description"
                                                      name="meta_description"
                                                      class="form-control @error('meta_description') is-invalid @enderror"
                                                      rows="2">{{ old('meta_description') }}</textarea>
                                            @error('meta_description')
                                            <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                            <small class="text-muted">Recommended: 150-160 characters</small>
                                        </div>
                                    </div>

                                    <!-- Submit -->
                                    <div class="form-group mt-4">
                                        <button class="btn btn-primary" type="submit">
                                            <i class="fa fa-save me-1"></i> Create Post
                                        </button>
                                        <a href="{{ route('blog.index') }}" class="btn btn-danger">
                                            <i class="fa fa-times me-1"></i> Cancel
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- CONTAINER CLOSED -->
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<script>
    // Initialize Summernote for content
    $('#content').summernote({
        placeholder: 'Start writing your blog content...',
        tabsize: 2,
        height: 450,
        disableDragAndDrop: false,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });

    // Auto-generate slug preview on title input
    document.getElementById('title').addEventListener('input', function() {
        let slug = this.value
            .toLowerCase()
            .trim()
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_]+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-+|-+$/g, '');

        document.getElementById('slug-preview').textContent = 'URL: /blog/' + (slug || 'your-title-here');
    });
</script>
@endpush
