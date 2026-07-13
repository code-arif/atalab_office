@extends('backend.app', ['title' => $blog->title])

@push('styles')
<style>
    .blog-detail-header {
        position: relative;
        padding: 2rem 0 1.5rem;
    }
    .blog-detail-header .blog-title {
        font-size: 1.75rem;
        font-weight: 700;
        line-height: 1.3;
        color: #1a1a2e;
    }
    .blog-detail-header .blog-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1.25rem;
        color: #6b7280;
        font-size: 0.875rem;
        margin-top: 0.75rem;
    }
    .blog-detail-header .blog-meta span {
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }
    .blog-detail-header .blog-meta i {
        font-size: 0.9rem;
        color: #9ca3af;
    }
    .blog-detail-image {
        width: 100%;
        max-height: 400px;
        object-fit: cover;
        border-radius: 12px;
        margin: 1.25rem 0;
    }
    .blog-detail-content {
        font-size: 1rem;
        line-height: 1.8;
        color: #374151;
    }
    .blog-detail-content h2,
    .blog-detail-content h3,
    .blog-detail-content h4 {
        margin-top: 1.75rem;
        margin-bottom: 0.75rem;
        color: #1a1a2e;
    }
    .blog-detail-content p {
        margin-bottom: 1rem;
    }
    .blog-detail-content img {
        max-width: 100%;
        border-radius: 8px;
        margin: 1rem 0;
    }
    .blog-detail-content blockquote {
        border-left: 4px solid #5066e1;
        padding-left: 1rem;
        margin: 1.5rem 0;
        color: #4b5563;
        font-style: italic;
        background: #f8f9fc;
        padding: 1rem 1.5rem;
        border-radius: 0 8px 8px 0;
    }
    .blog-detail-content table {
        width: 100%;
        border-collapse: collapse;
        margin: 1rem 0;
    }
    .blog-detail-content table th,
    .blog-detail-content table td {
        border: 1px solid #e5e7eb;
        padding: 0.5rem 0.75rem;
        text-align: left;
    }
    .blog-detail-content table th {
        background: #f3f4f6;
        font-weight: 600;
    }
    .seo-metadata {
        background: #f8f9fc;
        border-radius: 8px;
        padding: 1.25rem;
        margin-top: 2rem;
    }
    .seo-metadata dt {
        font-weight: 600;
        color: #4b5563;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        margin-bottom: 0.15rem;
    }
    .seo-metadata dd {
        color: #6b7280;
        font-size: 0.9rem;
        margin-bottom: 0.85rem;
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.35rem 1rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
    }
    .status-badge.published {
        background: #d4edda;
        color: #155724;
    }
    .status-badge.draft {
        background: #fff3cd;
        color: #856404;
    }
</style>
@endpush

@section('content')
<!--app-content open-->
<div class="app-content main-content mt-0">
    <div class="side-app">

        <!-- CONTAINER -->
        <div class="main-container container-fluid">

            <!-- PAGE-HEADER -->
            <div class="page-header">
                <div>
                    <h1 class="page-title">Blog Post</h1>
                </div>
                <div class="ms-auto pageheader-btn">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('blog.index') }}">Blog</a></li>
                        <li class="breadcrumb-item active" aria-current="page">View</li>
                    </ol>
                </div>
            </div>
            <!-- PAGE-HEADER END -->

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">Post Preview</h3>
                            <div class="d-flex gap-2">
                                <a href="{{ route('blog.edit', $blog->id) }}" class="btn btn-primary btn-sm">
                                    <i class="fa fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('blog.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fa fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Blog Header -->
                            <div class="blog-detail-header">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="status-badge {{ $blog->status }}">
                                        <i class="fa {{ $blog->status === 'published' ? 'fa-check-circle' : 'fa-clock' }}"></i>
                                        {{ ucfirst($blog->status) }}
                                    </span>                                    <span class="badge bg-info-soft text-dark">
                                        <i class="fa fa-clock-o"></i> {{ $blog->reading_time }} min read
                                    </span>
                                </div>
                                <h1 class="blog-title">{{ $blog->title }}</h1>
                                <div class="blog-meta">
                                    @if($blog->author)
                                    <span>
                                        <i class="fa fa-user"></i>
                                        {{ $blog->author->name }}
                                    </span>
                                    @endif
                                    @if($blog->published_at)
                                    <span>
                                        <i class="fa fa-calendar"></i>
                                        {{ $blog->published_at->format('F d, Y') }}
                                    </span>
                                    @endif
                                    <span>
                                        <i class="fa fa-folder"></i>
                                        Blog
                                    </span>
                                </div>
                            </div>

                            <!-- Featured Image -->
                            @if($blog->featured_image)
                            <img src="{{ asset($blog->featured_image) }}" alt="{{ $blog->title }}" class="blog-detail-image">
                            @endif

                            <!-- Excerpt -->
                            @if($blog->excerpt)
                            <div class="alert alert-info bg-light border-0 ps-4 py-3 mb-4" style="border-left: 4px solid #5066e1;">
                                <strong>Excerpt:</strong>
                                <p class="mb-0 text-muted mt-1">{{ $blog->excerpt }}</p>
                            </div>
                            @endif

                            <!-- Content -->
                            <div class="blog-detail-content">
                                {!! $blog->content !!}
                            </div>

                            <!-- SEO Metadata -->
                            <div class="seo-metadata">
                                <h5 class="mb-3"><i class="fa fa-search me-1"></i> SEO Metadata</h5>
                                <dl class="mb-0">
                                    <dt>Slug</dt>
                                    <dd><code>/blog/{{ $blog->slug }}</code></dd>

                                    @if($blog->meta_title)
                                    <dt>Meta Title</dt>
                                    <dd>{{ $blog->meta_title }} <span class="text-muted">({{ strlen($blog->meta_title) }} chars)</span></dd>
                                    @endif

                                    @if($blog->meta_description)
                                    <dt>Meta Description</dt>
                                    <dd>{{ $blog->meta_description }} <span class="text-muted">({{ strlen($blog->meta_description) }} chars)</span></dd>
                                    @endif

                                    @if($blog->meta_keywords)
                                    <dt>Meta Keywords</dt>
                                    <dd>
                                        @foreach(explode(',', $blog->meta_keywords) as $keyword)
                                        <span class="badge bg-light text-dark me-1">{{ trim($keyword) }}</span>
                                        @endforeach
                                    </dd>
                                    @endif
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
<!-- CONTAINER CLOSED -->
@endsection
