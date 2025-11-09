@extends('backend.app')

@section('title', 'Reviews & Ratings')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title">Reviews & Ratings</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Reviews</a></li>
                            <li class="breadcrumb-item active" aria-current="page">All Reviews</li>
                        </ol>
                    </div>
                </div>

                <!-- Review Counter -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <div class="me-3">
                                        <i class="fe fe-star text-warning" style="font-size: 40px;"></i>
                                    </div>
                                    <div>
                                        <h3 class="mb-0">{{ $count }}</h3>
                                        <p class="text-muted mb-0">Total Reviews</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card box-shadow-0">
                            <div class="card-body">

                                <div class="card-header border-bottom mb-3">
                                    <h3 class="card-title">Manage Reviews</h3>
                                    <div class="card-options ms-auto">
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#reviewModal" id="addReviewBtn">
                                            <i class="fe fe-plus"></i> Add Review
                                        </button>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table text-nowrap mb-0 table-bordered" id="datatable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Author</th>
                                                <th>Review</th>
                                                <th>Rating</th>
                                                <th>Week Label</th>
                                                <th>Avatar</th>
                                                <th>Action</th>
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
    <!-- CONTAINER CLOSED -->

    <!-- Review Modal (Add/Edit) -->
    <div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="reviewForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="id" id="reviewID">

                    <div class="modal-header">
                        <h5 class="modal-title" id="reviewModalLabel">Add Review</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row">

                            <!-- Author Name -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Author Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="author_name" id="author_name"
                                    placeholder="Enter author name">
                                <span class="text-danger error-text author_name_error"></span>
                            </div>

                            <!-- Week Label -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Week Label</label>
                                <input type="text" class="form-control" name="week_label" id="week_label"
                                    placeholder="e.g., Week 1, Week 2">
                                <span class="text-danger error-text week_label_error"></span>
                            </div>

                            <!-- Rating -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Rating (1-5) <span class="text-danger">*</span></label>
                                <div class="rating-stars block" id="rating">
                                    <input type="number" readonly class="rating form-control d-none" name="rating"
                                        id="rating_value" min="1" max="5" value="5">
                                    <div class="stars">
                                        @for ($i = 1; $i <= 5; $i++)
                                            <i class="fa fa-star" data-rating="{{ $i }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                <span class="text-danger error-text rating_error"></span>
                            </div>

                            <!-- Review Text -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Review Text</label>
                                <textarea class="form-control" name="review_text" id="review_text" rows="4" placeholder="Enter review text"></textarea>
                                <span class="text-danger error-text review_text_error"></span>
                            </div>

                            <!-- Avatar -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Author Avatar</label>
                                <input type="file" name="author_avatar" id="author_avatar"
                                    class="form-control dropify" accept="image/*">
                                <span class="text-danger error-text author_avatar_error"></span>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" id="reviewSubmitBtn">Save Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Review Modal -->
    <div class="modal fade" id="viewReviewModal" tabindex="-1" aria-labelledby="viewReviewModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewReviewModalLabel">Review Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12 text-center mb-4">
                            <img id="view_author_avatar" src="" alt="Author Avatar" class="rounded-circle"
                                style="width: 120px; height: 120px; object-fit: cover; display: none;">
                            <div id="no_avatar" class="text-muted" style="display: none;">
                                <i class="fe fe-user" style="font-size: 80px;"></i>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Author Name:</label>
                            <p id="view_author_name" class="text-muted"></p>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Week Label:</label>
                            <p id="view_week_label" class="text-muted"></p>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Rating:</label>
                            <div id="view_rating_stars"></div>
                        </div>

                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Review Text:</label>
                            <p id="view_review_text" class="text-muted"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('backend/plugins/rating/rating.css') }}">
    <style>
        .rating-stars .stars i {
            font-size: 24px;
            color: #ddd;
            cursor: pointer;
            transition: color 0.2s;
        }

        .rating-stars .stars i.active,
        .rating-stars .stars i:hover,
        .rating-stars .stars i:hover~i {
            color: #ffc107;
        }

        .dropify-wrapper .dropify-message p {
            font-size: 14px;
        }

        #view_rating_stars i {
            font-size: 20px;
            color: #ffc107;
        }
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
                responsive: true,
                serverSide: true,
                language: {
                    processing: `<div class="text-center"><img src="{{ asset('default/loader.gif') }}" style="width:50px;"></div>`
                },
                ajax: {
                    url: "{{ route('reviews.index') }}",
                    type: "GET"
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'author_name'
                    },
                    {
                        data: 'review_text'
                    },
                    {
                        data: 'rating'
                    },
                    {
                        data: 'week_label'
                    },
                    {
                        data: 'author_avatar',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
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
                $('#reviewSubmitBtn').prop('disabled', false).text('Save Review');
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

                if (id) formData.append('_method', 'PUT');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    beforeSend: function() {
                        $('.error-text').text('');
                        $('#reviewSubmitBtn').prop('disabled', true).text('Saving...');
                    },
                    success: function(res) {
                        if (res.success) {
                            $('#reviewModal').modal('hide');
                            toastr.success(res.message);
                            dTable.ajax.reload();
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
                        $('#reviewSubmitBtn').prop('disabled', false).text('Save Review');
                        if (xhr.status === 422) {
                            $.each(xhr.responseJSON.errors, function(field, messages) {
                                $('.' + field + '_error').text(messages[0]);
                            });
                        } else {
                            toastr.error(xhr.responseJSON.message || 'Something went wrong!');
                        }
                    },
                    complete: function() {
                        $('#reviewSubmitBtn').prop('disabled', false).text('Save Review');
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

                        // Rating Stars
                        let rating = res.data.rating || 5;
                        $('#rating_value').val(rating);
                        $('#rating .fa-star').removeClass('active');
                        for (let i = 1; i <= rating; i++) {
                            $(`#rating .fa-star[data-rating="${i}"]`).addClass('active');
                        }

                        // Dropify Image Preview
                        let $dropify = $('#author_avatar').dropify();
                        let drify = $dropify.data('dropify');
                        drify.resetPreview();
                        drify.destroy();

                        if (res.data.author_avatar) {
                            let imageUrl = "{{ asset('') }}" + res.data.author_avatar;
                            drify.settings.defaultFile = imageUrl;
                        } else {
                            drify.settings.defaultFile = '';
                        }

                        $dropify.dropify(drify.settings);
                        $('#reviewModal').modal('show');
                    } else {
                        toastr.error(res.message || 'Failed to load review.');
                    }
                }).fail(function() {
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
                            $('#view_author_avatar').attr('src', "{{ asset('') }}" + res.data
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
                                starsHtml += '<i class="fa fa-star"></i> ';
                            } else {
                                starsHtml += '<i class="fa fa-star" style="color: #ddd;"></i> ';
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
        });


        // Delete Confirm
        window.showDeleteConfirm = function(id) {
            Swal.fire({
                title: 'Delete Review?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete!',
                cancelButtonText: 'Cancel'
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
                        toastr.success(res.message);
                        dTable.ajax.reload();
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function() {
                    toastr.error('Failed to delete.');
                }
            });
        }

        // Initialize Dropify
        $('.dropify').dropify({
            messages: {
                'default': 'Drag and drop or click',
                'replace': 'Drag and drop or click to replace',
                'remove': 'Remove',
                'error': 'Oops, something wrong happened.'
            }
        });
    </script>
@endpush
