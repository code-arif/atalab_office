@extends('backend.app')

@section('title', 'Selected Name section')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                {{-- PAGE-HEADER --}}
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Home page - Selected Name section</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Home page</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Selected Name section</li>
                        </ol>
                    </div>
                </div>

                {{-- PAGE-CONTENT --}}
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card box-shadow-0">
                            <div class="card-header bg-light">
                                <h4 class="card-title">Header, Description & Image</h4>
                            </div>
                            <div class="card-body">
                                <form class="form-horizontal" method="post"
                                    action="{{ route('cms.home.selected_name.section.update') }}"
                                    enctype="multipart/form-data">
                                    @csrf
                                    <div class="row mb-4">

                                        {{-- Title --}}
                                        <div class="form-group mb-3">
                                            <label for="title" class="form-label">Title</label>
                                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                                name="title" placeholder="Enter title" id="title"
                                                value="{{ $data->title ?? old('title') }}">
                                            @error('title')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- Description --}}
                                        <div class="form-group mb-3">
                                            <label for="description" class="form-label">Description</label>
                                            <textarea name="description" id="summernote" class="form-control @error('description') is-invalid @enderror"
                                                rows="6" placeholder="Enter description">{{ $data->description ?? old('description') }}</textarea>
                                            @error('description')
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        {{-- slected name --}}
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <label for="image" class="form-label">Image</label>
                                                    <input type="file"
                                                        class="dropify form-control @error('image') is-invalid @enderror"
                                                        data-default-file="{{ !empty($data->image) && file_exists(public_path($data->image)) ? asset($data->image) : asset('default/placeholder-image.avif') }}"
                                                        name="image" id="image">
                                                    @error('image')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Submit --}}
                                        <div class="form-group">
                                            <button class="btn btn-primary" type="submit">Save changes</button>
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

@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#summernote').summernote({
                placeholder: 'Enter section description...',
                tabsize: 2,
                height: 300,
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
                ],

                callbacks: {
                    onInit: function() {
                        $('.note-editable').css('color', '#e0e0e0');
                    },
                    onChange: function(contents, $editable) {
                        $('.note-editable').css('color', '#e0e0e0');
                    }
                }
            });

            $('#summernote').on('summernote.change', function() {
                $('.note-editable').css('color', '#e0e0e0');
            });
        });
    </script>
@endpush


@push('styles')
    <!-- Summernote Lite CSS -->
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

    <!-- Dark Mode Custom CSS -->
    <style>
        /* Summernote Dark Theme */
        .note-editor.note-frame {
            border: 1px solid #444;
            border-radius: 8px;
            background-color: #1e1e1e !important;
        }

        .note-editor .note-toolbar {
            background-color: #2d2d2d !important;
            border-bottom: 1px solid #444;
            color: #fff;
        }

        .note-editor .note-editing-area .note-editable {
            background-color: #1e1e1e !important;
            color: #e0e0e0 !important;
            caret-color: #fff;
        }

        .note-editor .note-statusbar {
            background-color: #2d2d2d !important;
            color: #aaa;
        }

        /* Toolbar buttons color fix */
        .note-editor .note-btn {
            background-color: #333 !important;
            color: #ddd !important;
            border: none !important;
        }

        .note-editor .note-btn:hover {
            background-color: #444 !important;
        }

        /* Dropdown menu dark */
        .note-editor .dropdown-menu {
            background-color: #2d2d2d !important;
            border: 1px solid #555;
        }

        .note-editor .dropdown-item {
            color: #ddd !important;
        }

        .note-editor .dropdown-item:hover {
            background-color: #444 !important;
        }
    </style>
@endpush
