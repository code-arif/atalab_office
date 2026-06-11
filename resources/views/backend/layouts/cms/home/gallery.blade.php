@extends('backend.app')

@section('title', 'Gallery section')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                {{-- PAGE-HEADER --}}
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Gallery Section</h1>
                        <p class="text-muted mb-0 mt-1" style="font-size: 13px;">Manage the main display image for the gallery section.</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Home Page</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Gallery Section</li>
                        </ol>
                    </div>
                </div>

                {{-- PAGE-CONTENT --}}
                <div class="row">
                    <div class="col-lg-8 col-xl-6">
                        <div class="hero-card">
                            <div class="hero-card-header">
                                <div class="hero-card-header-icon">
                                    <i class="fe fe-image"></i>
                                </div>
                                <div>
                                    <h5 class="hero-card-title mb-0">Gallery Image</h5>
                                    <small class="text-muted">Upload the featured image.</small>
                                </div>
                            </div>
                            <div class="hero-card-body">
                                <form method="post" action="{{ route('cms.home.gallery.section.update') }}" enctype="multipart/form-data">
                                    @csrf
                                    
                                    <div class="mb-4">
                                        <label for="image" class="pro-label">Display Image</label>
                                        <input type="file"
                                            class="dropify @error('image') is-invalid @enderror"
                                            data-default-file="{{ !empty($data->image) && file_exists(public_path($data->image)) ? asset($data->image) : asset('default/placeholder-image.avif') }}"
                                            name="image" id="image">
                                        @error('image')
                                            <span class="invalid-feedback d-block mt-2">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    {{-- Submit --}}
                                    <div class="text-end border-top pt-3" style="border-color: var(--pro-border) !important;">
                                        <button class="pro-btn pro-btn-primary" type="submit">
                                            <i class="fe fe-save me-2"></i> Save Image
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Show success message with SweetAlert2
        @if (session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false,
                confirmButtonColor: '#521aac'
            });
        @endif
    </script>
@endpush

@push('styles')
    <style>
        /* ── Design Tokens ── */
        :root {
            --pro-radius: 12px;
            --pro-radius-sm: 8px;
            --pro-border: #e8eaed;
            --pro-shadow: 0 1px 3px rgba(0,0,0,.06), 0 1px 8px rgba(0,0,0,.04);
            --pro-shadow-hover: 0 4px 16px rgba(0,0,0,.10);
            --pro-accent: var(--primary-bg-color, #521aac);
            --pro-accent-light: rgba(82, 26, 172, .08);
            --pro-danger: #e03131;
            --pro-text: #1a1d23;
            --pro-muted: #6c757d;
            --pro-transition: .18s ease;
        }

        /* ── Card ── */
        .hero-card {
            background: #fff;
            border: 1px solid var(--pro-border);
            border-radius: var(--pro-radius);
            box-shadow: var(--pro-shadow);
            overflow: hidden;
            transition: box-shadow var(--pro-transition);
        }
        .hero-card:hover { box-shadow: var(--pro-shadow-hover); }

        .hero-card-header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 18px 22px;
            border-bottom: 1px solid var(--pro-border);
            background: #fafbfc;
        }
        .hero-card-header-icon {
            width: 38px; height: 38px;
            border-radius: var(--pro-radius-sm);
            background: var(--pro-accent-light);
            color: var(--pro-accent);
            display: flex; align-items: center; justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }
        .hero-card-title {
            font-size: 15px;
            font-weight: 600;
            color: var(--pro-text);
            letter-spacing: -.01em;
        }
        .hero-card-body { padding: 24px; }

        /* ── Form Controls ── */
        .pro-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--pro-text);
            margin-bottom: 8px;
            letter-spacing: -.01em;
        }
        .invalid-feedback { font-size: 12px; color: var(--pro-danger); margin-top: 4px; }

        /* ── Buttons ── */
        .pro-btn {
            display: inline-flex; align-items: center; justify-content: center;
            padding: 10px 20px;
            border-radius: var(--pro-radius-sm);
            font-size: 13.5px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all var(--pro-transition);
            letter-spacing: -.01em;
        }
        .pro-btn-primary {
            background: var(--pro-accent);
            color: #fff;
        }
        .pro-btn-primary:hover {
            filter: brightness(1.12);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(82,26,172,.35);
            color: #fff;
        }
        .pro-btn-primary:active { transform: translateY(0); }

        /* Override Dropify styles for a more modern look */
        .dropify-wrapper {
            border: 2px dashed var(--pro-border) !important;
            border-radius: var(--pro-radius-sm) !important;
            background-color: #fafbfc !important;
            padding: 20px !important;
            transition: all var(--pro-transition);
            min-height: 250px;
        }
        .dropify-wrapper:hover {
            border-color: var(--pro-accent) !important;
            background-color: #f8f9fa !important;
        }
        .dropify-wrapper .dropify-message p {
            font-family: inherit;
            font-size: 14px;
            color: var(--pro-muted);
        }
        .dropify-wrapper .dropify-message span.file-icon {
            font-size: 40px !important;
            color: var(--pro-muted) !important;
            margin-bottom: 10px;
        }
    </style>
@endpush
