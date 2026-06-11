@extends('backend.app')

@section('title', 'Tax Policy Page')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                {{-- PAGE-HEADER --}}
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Tax Policy Page</h1>
                        <p class="text-muted mb-0 mt-1" style="font-size: 13px;">Manage the content for the Tax Policy section.</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Tax Policy Page</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Index</li>
                        </ol>
                    </div>
                </div>

                {{-- PAGE-CONTENT --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="hero-card">
                            <div class="hero-card-header">
                                <div class="hero-card-header-icon">
                                    <i class="fe fe-file-text"></i>
                                </div>
                                <div>
                                    <h5 class="hero-card-title mb-0">Policy Details</h5>
                                    <small class="text-muted">Enter the title and full tax policy description.</small>
                                </div>
                            </div>
                            <div class="hero-card-body">
                                <form method="post" action="{{ route('cms.tax_policy.hero.section.update') }}" enctype="multipart/form-data">
                                    @csrf
                                    
                                    {{-- Title --}}
                                    <div class="mb-4">
                                        <label for="title" class="pro-label">Page Title</label>
                                        <input type="text" class="pro-input @error('title') is-invalid @enderror"
                                            name="title" placeholder="Enter title" id="title"
                                            value="{{ $data->title ?? old('title') }}">
                                        @error('title')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    {{-- Description --}}
                                    <div class="mb-4">
                                        <label for="description" class="pro-label">Detailed Description</label>
                                        <div class="editor-container @error('description') is-invalid-editor @enderror">
                                            <textarea name="description" id="summernote" class="form-control"
                                                rows="6" placeholder="Enter description">{{ $data->description ?? old('description') }}</textarea>
                                        </div>
                                        @error('description')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    {{-- Submit --}}
                                    <div class="text-end pt-3 border-top" style="border-color: var(--pro-border) !important;">
                                        <button class="pro-btn pro-btn-primary px-5" type="submit">
                                            <i class="fe fe-save me-2"></i> Save Changes
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
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#summernote').summernote({
                placeholder: 'Enter section description...',
                tabsize: 2,
                height: 250,
                disableDragAndDrop: false,

                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'italic', 'underline', 'clear']],
                    ['fontname', ['fontname']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });

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
    <!-- Summernote Lite CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

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
        .hero-card-body { padding: 30px; }

        /* ── Form Controls ── */
        .pro-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--pro-text);
            margin-bottom: 8px;
            letter-spacing: -.01em;
        }
        .pro-input {
            width: 100%;
            padding: 10px 14px;
            border: 1.5px solid var(--pro-border);
            border-radius: var(--pro-radius-sm);
            font-size: 14px;
            color: var(--pro-text);
            background: #fff;
            transition: border-color var(--pro-transition), box-shadow var(--pro-transition);
            outline: none;
        }
        .pro-input:focus {
            border-color: var(--pro-accent);
            box-shadow: 0 0 0 3px rgba(82,26,172,.18);
        }
        .pro-input.is-invalid { border-color: var(--pro-danger); }
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

        /* ── Clean Summernote Overrides ── */
        .editor-container {
            border-radius: var(--pro-radius-sm);
            overflow: hidden;
            border: 1.5px solid var(--pro-border);
            transition: border-color var(--pro-transition);
        }
        .editor-container:focus-within {
            border-color: var(--pro-accent);
        }
        .editor-container.is-invalid-editor {
            border-color: var(--pro-danger);
        }
        .note-editor.note-frame {
            border: none !important;
            border-radius: 0 !important;
            margin-bottom: 0 !important;
            box-shadow: none !important;
        }
        .note-editor .note-toolbar {
            background-color: #fafbfc !important;
            border-bottom: 1px solid var(--pro-border) !important;
            padding: 10px 10px 5px 10px !important;
        }
        .note-editor .note-statusbar {
            background-color: #fafbfc !important;
            border-top: 1px solid var(--pro-border) !important;
        }
        .note-btn {
            border-radius: 6px !important;
            border: 1px solid transparent !important;
            background: transparent !important;
            color: var(--pro-text) !important;
            box-shadow: none !important;
        }
        .note-btn:hover {
            background: rgba(0,0,0,.05) !important;
            border-color: var(--pro-border) !important;
        }
        .note-editor .note-editing-area .note-editable {
            padding: 20px !important;
            color: var(--pro-text) !important;
            font-size: 14px;
        }
        .note-editor .note-editing-area .note-placeholder {
            padding: 20px !important;
            font-size: 14px;
            color: var(--pro-muted) !important;
        }
    </style>
@endpush
