@extends('backend.app')

@section('title', 'Testimonial section')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                {{-- PAGE-HEADER --}}
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Testimonial Section</h1>
                        <p class="text-muted mb-0 mt-1" style="font-size: 13px;">Manage customer reviews and testimonial headers.</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Home page</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Testimonial section</li>
                        </ol>
                    </div>
                </div>

                <!-- Review Counter -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="hero-card">
                            <div class="hero-card-body d-flex align-items-center">
                                <div class="hero-card-header-icon me-3" style="background: rgba(255, 193, 7, 0.15); color: #ffc107; font-size: 20px;">
                                    <i class="fe fe-star"></i>
                                </div>
                                <div>
                                    <h3 class="mb-0 fw-bold" style="color: var(--pro-text);">{{ $count }}</h3>
                                    <p class="text-muted mb-0" style="font-size: 13px;">Total Reviews</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4">
                    {{-- Left Column: CMS Header --}}
                    <div class="col-xl-4 col-lg-5">
                        <div class="hero-card">
                            <div class="hero-card-header">
                                <div class="hero-card-header-icon">
                                    <i class="fe fe-type"></i>
                                </div>
                                <div>
                                    <h5 class="hero-card-title mb-0">Header</h5>
                                    <small class="text-muted">Main section title.</small>
                                </div>
                            </div>
                            <div class="hero-card-body">
                                <form method="post" action="{{ route('cms.home.testimonial.section.update') }}" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-4">
                                        <label for="title" class="pro-label">Testimonial Title</label>
                                        <input type="text" class="pro-input @error('title') is-invalid @enderror"
                                            name="title" placeholder="Enter title" id="title"
                                            value="{{ $data->title ?? old('title') }}">
                                        @error('title')
                                            <span class="invalid-feedback d-block mt-2">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="text-end">
                                        <button class="pro-btn pro-btn-primary" type="submit">
                                            <i class="fe fe-save me-2"></i> Save Changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    {{-- Right Column: Dynamic review content manage --}}
                    <div class="col-xl-8 col-lg-7">
                        <div class="hero-card">
                            <div class="hero-card-header d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="hero-card-header-icon">
                                        <i class="fe fe-users"></i>
                                    </div>
                                    <div>
                                        <h5 class="hero-card-title mb-0">Manage Reviews</h5>
                                        <small class="text-muted">Add, edit, or delete user reviews.</small>
                                    </div>
                                </div>
                                <button class="pro-btn pro-btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#reviewModal" id="addReviewBtn">
                                    <i class="fe fe-plus"></i> Add Review
                                </button>
                            </div>

                            <div class="hero-card-body">
                                <div class="table-responsive">
                                    <table class="table text-nowrap mb-0 table-bordered pro-table" id="datatable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Author</th>
                                                <th>Review</th>
                                                <th>Rating</th>
                                                <th>Week Label</th>
                                                <th>Avatar</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Review Modal (Add/Edit) -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content pro-modal-content">
                <form id="reviewForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="reviewID">

                    <div class="modal-header pro-modal-header">
                        <h5 class="modal-title pro-modal-title" id="reviewModalLabel">Add Review</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
                    </div>

                    <div class="modal-body pro-modal-body">
                        <div class="row g-4">
                            <!-- Author Name -->
                            <div class="col-md-6">
                                <label class="pro-label">Author Name <span class="text-danger">*</span></label>
                                <input type="text" class="pro-input" name="author_name" id="author_name" placeholder="Enter author name">
                                <span class="text-danger error-text author_name_error" style="font-size: 12px; margin-top: 4px; display: block;"></span>
                            </div>

                            <!-- Week Label -->
                            <div class="col-md-6">
                                <label class="pro-label">Week Label</label>
                                <input type="text" class="pro-input" name="week_label" id="week_label" placeholder="e.g., Week 1, Week 2">
                                <span class="text-danger error-text week_label_error" style="font-size: 12px; margin-top: 4px; display: block;"></span>
                            </div>

                            <!-- Rating -->
                            <div class="col-md-12">
                                <label class="pro-label">Rating (1-5) <span class="text-danger">*</span></label>
                                <div class="rating-stars block" id="rating">
                                    <input type="number" readonly class="rating form-control d-none" name="rating" id="rating_value" min="1" max="5" value="5">
                                    <div class="stars">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="fa fa-star" data-rating="{{ $i }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                <span class="text-danger error-text rating_error" style="font-size: 12px; margin-top: 4px; display: block;"></span>
                            </div>

                            <!-- Review Text -->
                            <div class="col-md-12">
                                <label class="pro-label">Review Text</label>
                                <textarea class="pro-input" name="review_text" id="review_text" rows="4" placeholder="Enter review text"></textarea>
                                <span class="text-danger error-text review_text_error" style="font-size: 12px; margin-top: 4px; display: block;"></span>
                            </div>

                            <!-- Avatar -->
                            <div class="col-md-12">
                                <label class="pro-label">Author Avatar</label>
                                <input type="file" name="author_avatar" id="author_avatar" class="dropify" accept="image">
                                <span class="text-danger error-text author_avatar_error" style="font-size: 12px; margin-top: 4px; display: block;"></span>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer pro-modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="pro-btn pro-btn-primary" id="reviewSubmitBtn">Save Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Review Modal -->
    <div class="modal fade" id="viewReviewModal" tabindex="-1" aria-labelledby="viewReviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content pro-modal-content">
                <div class="modal-header pro-modal-header">
                    <h5 class="modal-title pro-modal-title" id="viewReviewModalLabel">Review Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">&times;</button>
                </div>
                <div class="modal-body pro-modal-body">
                    <div class="row">
                        <div class="col-md-12 text-center mb-4">
                            <img id="view_author_avatar" src="" alt="Author Avatar" class="rounded-circle shadow-sm border"
                                style="width: 100px; height: 100px; object-fit: cover; display: none;">
                            <div id="no_avatar" class="text-muted" style="display: none;">
                                <div style="width: 100px; height: 100px; border-radius: 50%; background: #f0f2f5; display: inline-flex; align-items: center; justify-content: center;">
                                    <i class="fe fe-user" style="font-size: 40px; color: #a1a5b7;"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="pro-label mb-1">Author Name</label>
                            <p id="view_author_name" class="text-dark fw-bold mb-0"></p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="pro-label mb-1">Week Label</label>
                            <p id="view_week_label" class="text-dark mb-0"></p>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="pro-label mb-1">Rating</label>
                            <div id="view_rating_stars"></div>
                        </div>

                        <div class="col-md-12">
                            <label class="pro-label mb-1">Review Text</label>
                            <div class="p-3 rounded-1" style="background: #fafbfc; border: 1px solid var(--pro-border);">
                                <p id="view_review_text" class="text-muted mb-0" style="font-size: 14px; line-height: 1.6;"></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer pro-modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('backend/plugins/rating/rating.css') }}">

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
            height: 100%;
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
        .hero-card-body { padding: 22px; }

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
            resize: vertical;
        }
        .pro-input:focus {
            border-color: var(--pro-accent);
            box-shadow: 0 0 0 3px rgba(82,26,172,.18);
        }
        .pro-input.is-invalid { border-color: var(--pro-danger); }

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

        .pro-icon-btn {
            width: 32px; height: 32px;
            display: inline-flex; align-items: center; justify-content: center;
            border-radius: 6px;
            border: 1px solid var(--pro-border);
            background: #fff;
            color: var(--pro-muted);
            transition: all 0.2s ease;
            text-decoration: none;
        }
        .pro-icon-btn:hover {
            background: #f8f9fa;
            color: var(--pro-accent);
            border-color: #d1d5db;
        }
        .pro-icon-btn.btn-delete:hover {
            color: var(--pro-danger);
            background: rgba(224, 49, 49, 0.05);
            border-color: rgba(224, 49, 49, 0.2);
        }

        /* ── Table ── */
        .pro-table {
            border-collapse: separate;
            border-spacing: 0;
            border-radius: var(--pro-radius-sm);
            border: 1px solid var(--pro-border);
            overflow: hidden;
            width: 100%;
        }
        .pro-table thead th {
            background: #fafbfc;
            border-bottom: 1px solid var(--pro-border);
            border-top: none;
            font-weight: 600;
            font-size: 12.5px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            color: var(--pro-muted);
            padding: 14px 16px;
        }
        .pro-table tbody td {
            padding: 14px 16px;
            vertical-align: middle;
            border-bottom: 1px solid var(--pro-border);
            font-size: 14px;
            color: var(--pro-text);
        }
        .pro-table tbody tr:last-child td { border-bottom: none; }
        .pro-table tbody tr:hover td { background: rgba(0,0,0,.01); }

        /* ── Modals ── */
        .pro-modal-content {
            border: none;
            border-radius: var(--pro-radius);
            box-shadow: 0 10px 40px rgba(0,0,0,.1);
            overflow: hidden;
        }
        .pro-modal-header {
            background: #fafbfc;
            border-bottom: 1px solid var(--pro-border);
            padding: 20px 24px;
        }
        .pro-modal-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--pro-text);
        }
        .pro-modal-body { padding: 24px; }
        .pro-modal-footer {
            border-top: 1px solid var(--pro-border);
            padding: 16px 24px;
            background: #fff;
        }

        /* Override Dropify styles for a more modern look */
        .dropify-wrapper {
            border: 2px dashed var(--pro-border) !important;
            border-radius: var(--pro-radius-sm) !important;
            background-color: #fafbfc !important;
            padding: 20px !important;
            transition: all var(--pro-transition);
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

        /* Ratings */
        .rating-stars .stars i {
            font-size: 24px;
            color: #e4e6ef;
            cursor: pointer;
            transition: color 0.2s, transform 0.1s;
        }
        .rating-stars .stars i:hover { transform: scale(1.1); }
        .rating-stars .stars i.active,
        .rating-stars .stars i:hover,
        .rating-stars .stars i:hover~i { color: #ffc107; }
        #view_rating_stars i { font-size: 20px; color: #ffc107; margin-right: 2px; }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('backend/plugins/rating/rating.js') }}"></script>
    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize DataTable
            let dTable = $('#datatable').DataTable({
                order: [],
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                processing: true,
                responsive: false,
                serverSide: true,
                language: {
                    processing: `<div class="text-center"><img src="{{ asset('default/loader.gif') }}" style="width:50px;"></div>`
                },
                ajax: {
                    url: "{{ route('cms.home.testimonial.section') }}",
                    type: "GET"
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'author_name',
                        render: function(data, type, row) {
                            return `<span class="fw-medium">${data}</span>`;
                        }
                    },
                    {
                        data: 'review_text',
                        render: function(data, type, row) {
                            if(data && data.length > 50) {
                                return `<span class="text-muted">${data.substring(0, 50)}...</span>`;
                            }
                            return `<span class="text-muted">${data || '-'}</span>`;
                        }
                    },
                    {
                        data: 'rating',
                        render: function(data, type, row) {
                            let starsHtml = '';
                            let rating = data || 0;
                            for (let i = 1; i <= 5; i++) {
                                if (i <= rating) {
                                    starsHtml += '<i class="fa fa-star text-warning" style="font-size: 14px;"></i>';
                                } else {
                                    starsHtml += '<i class="fa fa-star" style="color: #e4e6ef; font-size: 14px;"></i>';
                                }
                            }
                            return `<div class="d-flex align-items-center gap-1">${starsHtml}</div>`;
                        }
                    },
                    {
                        data: 'week_label',
                        render: function(data, type, row) {
                            if(data) {
                                return `<span class="badge bg-light text-dark border">${data}</span>`;
                            }
                            return '-';
                        }
                    },
                    {
                        data: 'author_avatar',
                        orderable: false,
                        searchable: false,
                        render: function(data, type, row) {
                            if(data && data.includes('<img')) {
                                // The backend returns an <img> string. Extract the src attribute to apply our custom styles.
                                let src = $(data).attr('src');
                                return `<img src="${src}" alt="avatar" class="rounded-circle border" style="width: 36px; height: 36px; object-fit: cover;">`;
                            }
                            return `<div class="rounded-circle bg-light d-flex align-items-center justify-content-center border" style="width: 36px; height: 36px;"><i class="fe fe-user text-muted"></i></div>`;
                        }
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            // The backend returns raw HTML buttons. We'll intercept those and style them if needed,
                            // but usually they rely on specific classes. For now, we wrap the output slightly or let it be.
                            // The backend likely outputs standard Bootstrap buttons, so they should look okay.
                            // If we want to replace them, we do it in the controller, but we can't change that here easily.
                            // However, we can style the container.
                            return `<div class="d-flex align-items-center justify-content-center gap-2">${data}</div>`;
                        }
                    }
                ],
                drawCallback: function() {
                    // Re-style backend generated buttons after draw
                    $('#datatable .btn-info').removeClass('btn btn-sm btn-info').addClass('pro-icon-btn').html('<i class="fe fe-eye"></i>');
                    $('#datatable .btn-primary').removeClass('btn btn-sm btn-primary').addClass('pro-icon-btn').html('<i class="fe fe-edit-2"></i>');
                    $('#datatable .btn-danger').removeClass('btn btn-sm btn-danger').addClass('pro-icon-btn btn-delete').html('<i class="fe fe-trash-2"></i>');
                }
            });

            // Rating Star Click
            $(document).on('click', '#rating .fa-star', function() {
                let rating = $(this).data('rating');
                $('#rating_value').val(rating);
                $('#rating .fa-star').removeClass('active');
                for (let i = 1; i <= rating; i++) {
                    $(`#rating .fa-star[data-rating="${i}"]`).addClass('active');
                }
            });

            // Open Modal for Add
            $('#addReviewBtn').click(function() {
                $('#reviewModalLabel').text('Add Review');
                $('#reviewForm')[0].reset();
                $('#reviewID').val('');
                $('.error-text').text('');
                $('#rating_value').val(5);
                $('#rating .fa-star').removeClass('active').slice(0, 5).addClass('active');
                $('.dropify').dropify('destroy').dropify();
                $('#reviewSubmitBtn').prop('disabled', false).html('<i class="fe fe-check me-1"></i> Save Review');
                $('#reviewModal').modal('show');
            });

            // Form Submit (Create & Update)
            $('#reviewForm').on('submit', function(e) {
                e.preventDefault();
                let formData = new FormData(this);
                let id = $('#reviewID').val();

                let url = id ?
                    "{{ route('reviews.update', ':id') }}".replace(':id', id) :
                    "{{ route('reviews.store') }}";

                if (id) formData.append('_method', 'POST');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $('.error-text').text('');
                        $('#reviewSubmitBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Saving...');
                    },
                    success: function(res) {
                        if (res.success) {
                            $('#reviewModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false,
                                confirmButtonColor: '#521aac'
                            });

                            // Reload DataTable
                            dTable.ajax.reload(null, false);

                            $('#reviewForm')[0].reset();
                            $('.dropify').dropify('destroy').dropify();
                        } else {
                            if (res.errors) {
                                $.each(res.errors, function(field, messages) {
                                    $('.' + field + '_error').text(messages[0]);
                                });
                            }
                        }
                    },
                    error: function(xhr) {
                        $('#reviewSubmitBtn').prop('disabled', false).html('<i class="fe fe-check me-1"></i> Save Review');
                        if (xhr.status === 422) {
                            $.each(xhr.responseJSON.errors, function(field, messages) {
                                $('.' + field + '_error').text(messages[0]);
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON.message || 'Something went wrong!',
                                confirmButtonColor: '#521aac'
                            });
                        }
                    },
                    complete: function() {
                        $('#reviewSubmitBtn').prop('disabled', false);
                    }
                });
            });

            // Edit Review
            $(document).on('click', '.editReview', function() {
                let id = $(this).data('id');
                let url = "{{ route('reviews.edit', ':id') }}".replace(':id', id);

                $.get(url, function(res) {
                    if (res.success) {
                        $('#reviewModalLabel').text('Edit Review');
                        $('#reviewID').val(res.data.id);
                        $('#author_name').val(res.data.author_name);
                        $('#review_text').val(res.data.review_text || '');
                        $('#week_label').val(res.data.week_label || '');
                        $('.error-text').text('');

                        // Rating Stars
                        let rating = res.data.rating || 5;
                        $('#rating_value').val(rating);
                        $('#rating .fa-star').removeClass('active');
                        for (let i = 1; i <= rating; i++) {
                            $(`#rating .fa-star[data-rating="${i}"]`).addClass('active');
                        }

                        // Handle Dropify image
                        let imageInput = $('#author_avatar').dropify();
                        imageInput = imageInput.data('dropify');
                        imageInput.resetPreview();
                        imageInput.clearElement();

                        if (res.data.author_avatar) {
                            let baseUrl = "{{ asset('') }}";
                            imageInput.settings.defaultFile = baseUrl + res.data.author_avatar;
                            imageInput.destroy();
                            imageInput.init();
                        }

                        $('#reviewSubmitBtn').html('<i class="fe fe-check me-1"></i> Update Review');
                        $('#reviewModal').modal('show');
                    } else {
                        toastr.error(res.message || 'Failed to load review.');
                    }
                }).fail(function(xhr) {
                    console.error(xhr);
                    toastr.error('Server error. Please try again.');
                });
            });

            // View Review
            $(document).on('click', '.viewReview', function() {
                let id = $(this).data('id');
                let url = "{{ route('reviews.show', ':id') }}".replace(':id', id);

                $.get(url, function(res) {
                    if (res.success) {
                        $('#view_author_name').text(res.data.author_name || 'N/A');
                        $('#view_week_label').text(res.data.week_label || 'N/A');
                        $('#view_review_text').text(res.data.review_text ||
                            'No review text provided');

                        // Display Avatar or Placeholder
                        if (res.data.author_avatar) {
                            $('#view_author_avatar').attr('src', "{{ asset('/') }}" + res.data
                                .author_avatar).show();
                            $('#no_avatar').hide();
                        } else {
                            $('#view_author_avatar').hide();
                            $('#no_avatar').show();
                        }

                        // Display Rating Stars
                        let rating = res.data.rating || 0;
                        let starsHtml = '';
                        for (let i = 1; i <= 5; i++) {
                            if (i <= rating) {
                                starsHtml += '<i class="fa fa-star text-warning me-1"></i>';
                            } else {
                                starsHtml += '<i class="fa fa-star" style="color: #e4e6ef; margin-right: 4px;"></i>';
                            }
                        }
                        $('#view_rating_stars').html(starsHtml);

                        $('#viewReviewModal').modal('show');
                    } else {
                        toastr.error(res.message || 'Failed to load review.');
                    }
                }).fail(function() {
                    toastr.error('Server error. Please try again.');
                });
            });

            // Delete Confirm - Make dTable global accessible
            window.dTable = dTable;
        });

        // Delete Confirm
        window.showDeleteConfirm = function(id) {
            Swal.fire({
                title: 'Delete Review?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete!',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#e03131',
                cancelButtonColor: '#868e96',
                customClass: {
                    confirmButton: 'pro-btn',
                    cancelButton: 'pro-btn'
                }
            }).then(result => {
                if (result.isConfirmed) deleteReview(id);
            });
        };

        function deleteReview(id) {
            $.ajax({
                url: "{{ route('reviews.destroy', '') }}/" + id,
                type: 'DELETE',
                success: function(res) {
                    if (res.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: res.message,
                            timer: 2000,
                            showConfirmButton: false,
                            confirmButtonColor: '#521aac'
                        });

                        // Reload DataTable without page reset
                        window.dTable.ajax.reload(null, false);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message,
                            confirmButtonColor: '#521aac'
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Failed to delete.',
                        confirmButtonColor: '#521aac'
                    });
                }
            });
        }
    </script>
@endpush
