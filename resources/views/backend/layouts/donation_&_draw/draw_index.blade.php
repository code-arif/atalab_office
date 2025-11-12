@extends('backend.app')

@section('title', 'Weekly Draws Management')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title">Weekly Draws Management</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Draws</a></li>
                            <li class="breadcrumb-item active" aria-current="page">All Draws</li>
                        </ol>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <!-- Active Draws -->
                    <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
                        <div class="card overflow-hidden sales-card bg-primary-gradient">
                            <div class="ps-3 pt-3 pe-3 pb-2 pt-0">
                                <div class="">
                                    <h6 class="mb-3 tx-12 text-white">ACTIVE DRAWS</h6>
                                </div>
                                <div class="pb-0 mt-0">
                                    <div class="d-flex">
                                        <div class="">
                                            <h4 class="tx-20 fw-bold mb-1 text-white" id="active_draws_count">
                                                {{ $activeDraws }}</h4>
                                            <p class="mb-0 tx-12 text-white op-7">Currently Running</p>
                                        </div>
                                        <span class="float-end my-auto ms-auto">
                                            <i class="fas fa-play-circle text-white"></i>
                                            <span class="text-white op-7"> Draws</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Pool -->
                    <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
                        <div class="card overflow-hidden sales-card bg-success-gradient">
                            <div class="ps-3 pt-3 pe-3 pb-2 pt-0">
                                <div class="">
                                    <h6 class="mb-3 tx-12 text-white">TOTAL POOL</h6>
                                </div>
                                <div class="pb-0 mt-0">
                                    <div class="d-flex">
                                        <div class="">
                                            <h4 class="tx-20 fw-bold mb-1 text-white">${{ number_format($totalPool, 2) }}
                                            </h4>
                                            <p class="mb-0 tx-12 text-white op-7">Current Week</p>
                                        </div>
                                        <span class="float-end my-auto ms-auto">
                                            <i class="fas fa-dollar-sign text-white"></i>
                                            <span class="text-white op-7"> USD</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Participants -->
                    <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
                        <div class="card overflow-hidden sales-card bg-warning-gradient">
                            <div class="ps-3 pt-3 pe-3 pb-2 pt-0">
                                <div class="">
                                    <h6 class="mb-3 tx-12 text-white">PARTICIPANTS</h6>
                                </div>
                                <div class="pb-0 mt-0">
                                    <div class="d-flex">
                                        <div class="">
                                            <h4 class="tx-20 fw-bold mb-1 text-white">{{ $totalParticipants }}</h4>
                                            <p class="mb-0 tx-12 text-white op-7">Current Week</p>
                                        </div>
                                        <span class="float-end my-auto ms-auto">
                                            <i class="fas fa-users text-white"></i>
                                            <span class="text-white op-7"> Users</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Draws -->
                    <div class="col-xl-3 col-lg-6 col-md-6 col-xm-12">
                        <div class="card overflow-hidden sales-card bg-danger-gradient">
                            <div class="ps-3 pt-3 pe-3 pb-2 pt-0">
                                <div class="">
                                    <h6 class="mb-3 tx-12 text-white">TOTAL DRAWS</h6>
                                </div>
                                <div class="pb-0 mt-0">
                                    <div class="d-flex">
                                        <div class="">
                                            <h4 class="tx-20 fw-bold mb-1 text-white">{{ $totalDraws }}</h4>
                                            <p class="mb-0 tx-12 text-white op-7">All Time</p>
                                        </div>
                                        <span class="float-end my-auto ms-auto">
                                            <i class="fas fa-trophy text-white"></i>
                                            <span class="text-white op-7"> Draws</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card box-shadow-0">
                            <div class="card-body">

                                <div class="border-bottom mb-3 pb-5">
                                    <h3 class="card-title">All Weekly Draws</h3>
                                </div>

                                <div class="table-responsive">
                                    <table class="table text-nowrap mb-0 table-bordered" id="datatable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Week Number</th>
                                                <th>Status</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>Total Pool</th>
                                                <th>Participants</th>
                                                <th>Recipients</th>
                                                <th>Commission</th>
                                                <th>Winners Selected</th>
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

    <!-- View Draw Details Modal -->
    <div class="modal fade" id="viewDrawModal" tabindex="-1" aria-labelledby="viewDrawModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="viewDrawModalLabel">
                        <i class="fe fe-eye me-2"></i>Draw Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <!-- Left Column -->
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fe fe-info me-2"></i>Basic Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <td class="fw-bold" width="40%">Week Number:</td>
                                            <td id="view_week_number">---</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Status:</td>
                                            <td id="view_status">---</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Winners Selected:</td>
                                            <td id="view_winners_selected">---</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="card border mt-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fe fe-calendar me-2"></i>Timeline</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <td class="fw-bold" width="40%">Start Date:</td>
                                            <td id="view_start_date">---</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">End Date:</td>
                                            <td id="view_end_date">---</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Countdown Ends:</td>
                                            <td id="view_countdown_ends">---</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Claim Deadline:</td>
                                            <td id="view_claim_deadline">---</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="col-md-6">
                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fe fe-dollar-sign me-2"></i>Financial Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <td class="fw-bold" width="40%">Total Pool:</td>
                                            <td id="view_total_pool" class="text-success fw-bold">---</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Admin Commission:</td>
                                            <td id="view_commission" class="text-info fw-bold">---</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Distribution Pool:</td>
                                            <td id="view_distribution_pool" class="text-primary fw-bold">---</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="card border mt-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fe fe-users me-2"></i>Participation Stats</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless mb-0">
                                        <tr>
                                            <td class="fw-bold" width="40%">Total Participants:</td>
                                            <td id="view_participants" class="text-warning fw-bold">---</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Total Recipients:</td>
                                            <td id="view_recipients" class="text-danger fw-bold">---</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Odds of Winning:</td>
                                            <td id="view_odds" class="text-muted">---</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
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
    <style>
        .sales-card {
            border-radius: 10px;
        }

        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-active {
            background: #28a745;
            color: white;
        }

        .status-claiming {
            background: #ffc107;
            color: #000;
        }

        .status-completed {
            background: #6c757d;
            color: white;
        }

        .card-header.bg-light {
            background-color: #f8f9fa !important;
        }

        #datatable tbody tr:hover {
            background-color: #f8f9fa;
        }

        .btn-action-group {
            display: flex;
            gap: 5px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize DataTable
            let dTable = $('#datatable').DataTable({
                order: [
                    [1, 'desc']
                ], // Order by week number descending
                lengthMenu: [
                    [20, 50, 100, 300, 500],
                    [20, 50, 100, 300, "All"]
                ],
                processing: true,
                responsive: true,
                serverSide: true,
                language: {
                    processing: `<div class="text-center"><img src="{{ asset('default/loader.gif') }}" style="width:50px;"></div>`
                },
                ajax: {
                    url: "{{ route('weekly-draws.index') }}",
                    type: "GET"
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'week_number'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'start_date'
                    },
                    {
                        data: 'end_date'
                    },
                    {
                        data: 'total_pool'
                    },
                    {
                        data: 'total_participants'
                    },
                    {
                        data: 'total_recipients'
                    },
                    {
                        data: 'admin_commission'
                    },
                    {
                        data: 'winners_selected'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Create New Draw
            $('#createDrawBtn').click(function() {
                Swal.fire({
                    title: 'Create New Weekly Draw?',
                    text: 'This will start a new 7-day draw cycle.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Create!',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                }).then((result) => {
                    if (result.isConfirmed) {
                        createNewDraw();
                    }
                });
            });

            function createNewDraw() {
                $.ajax({
                    url: "{{ route('weekly-draws.store') }}",
                    type: 'POST',
                    beforeSend: function() {
                        Swal.fire({
                            title: 'Creating Draw...',
                            text: 'Please wait',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(res) {
                        Swal.close();
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: res.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            dTable.ajax.reload();
                            setTimeout(() => {
                                location.reload();
                            }, 2000);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: res.message
                            });
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: xhr.responseJSON?.message || 'Failed to create draw'
                        });
                    }
                });
            }

            // View Draw Details
            $(document).on('click', '.viewDraw', function() {
                let id = $(this).data('id');
                let url = "{{ route('weekly-draws.show', ':id') }}".replace(':id', id);

                $.get(url, function(res) {
                    if (res.success) {
                        let data = res.data;

                        // Basic Info
                        $('#view_week_number').text('Week #' + data.week_number);

                        // Status Badge
                        let statusBadge = '';
                        if (data.status === 'active') {
                            statusBadge =
                                '<span class="badge badge-status status-active">Active</span>';
                        } else if (data.status === 'claiming') {
                            statusBadge =
                                '<span class="badge badge-status status-claiming">Claiming</span>';
                        } else {
                            statusBadge =
                                '<span class="badge badge-status status-completed">Completed</span>';
                        }
                        $('#view_status').html(statusBadge);

                        // Winners Selected
                        let winnersSelected = data.winners_selected ?
                            '<span class="badge bg-success">Yes</span>' :
                            '<span class="badge bg-secondary">No</span>';
                        $('#view_winners_selected').html(winnersSelected);

                        // Timeline
                        $('#view_start_date').text(formatDate(data.start_date));
                        $('#view_end_date').text(formatDate(data.end_date));
                        $('#view_countdown_ends').text(formatDate(data.countdown_ends_at));
                        $('#view_claim_deadline').text(formatDate(data.claim_deadline));

                        // Financial
                        $('#view_total_pool').text('$' + parseFloat(data.total_pool).toFixed(2));
                        $('#view_commission').text('$' + parseFloat(data.admin_commission).toFixed(
                            2));

                        let distributionPool = parseFloat(data.total_pool) - parseFloat(data
                            .admin_commission);
                        $('#view_distribution_pool').text('$' + distributionPool.toFixed(2));

                        // Stats
                        $('#view_participants').text(data.total_participants);
                        $('#view_recipients').text(data.total_recipients || 'TBD');

                        let odds = data.total_participants > 0 && data.total_recipients > 0 ?
                            '1 in ' + Math.ceil(data.total_participants / data.total_recipients) :
                            'N/A';
                        $('#view_odds').text(odds);

                        $('#viewDrawModal').modal('show');
                    } else {
                        toastr.error(res.message || 'Failed to load draw details.');
                    }
                }).fail(function() {
                    toastr.error('Server error. Please try again.');
                });
            });

            // Format Date Helper
            function formatDate(dateString) {
                if (!dateString) return 'N/A';
                let date = new Date(dateString);
                return date.toLocaleString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }

            // Finalize Draw
            window.finalizeDraw = function(id) {
                Swal.fire({
                    title: 'Finalize Draw?',
                    text: 'This will end the active period and start the claiming period.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Finalize!',
                    cancelButtonText: 'Cancel'
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('weekly-draws.finalize', ':id') }}".replace(':id',
                                id),
                            type: 'POST',
                            success: function(res) {
                                if (res.success) {
                                    toastr.success(res.message);
                                    dTable.ajax.reload();
                                } else {
                                    toastr.error(res.message);
                                }
                            },
                            error: function(xhr) {
                                toastr.error(xhr.responseJSON?.message ||
                                    'Failed to finalize draw');
                            }
                        });
                    }
                });
            };

            // Select Winners
            window.selectWinners = function(id) {
                Swal.fire({
                    title: 'Select Winners?',
                    text: 'This will randomly select winners for this draw.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Select Winners!',
                    cancelButtonText: 'Cancel'
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('weekly-draws.select-winners', ':id') }}".replace(
                                ':id', id),
                            type: 'POST',
                            beforeSend: function() {
                                Swal.fire({
                                    title: 'Selecting Winners...',
                                    text: 'Please wait',
                                    allowOutsideClick: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });
                            },
                            success: function(res) {
                                Swal.close();
                                if (res.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Winners Selected!',
                                        html: `<p>${res.winners.length} winners have been selected!</p>
                                               <p>Total Distributed: $${res.total_distributed}</p>`,
                                        confirmButtonText: 'OK'
                                    });
                                    dTable.ajax.reload();
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error!',
                                        text: res.message
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.close();
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error!',
                                    text: xhr.responseJSON?.message ||
                                        'Failed to select winners'
                                });
                            }
                        });
                    }
                });
            };

            // Delete Draw
            window.showDeleteConfirm = function(id) {
                Swal.fire({
                    title: 'Delete Draw?',
                    text: 'This action cannot be undone!',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Delete!',
                    cancelButtonText: 'Cancel',
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6'
                }).then(result => {
                    if (result.isConfirmed) deleteDraw(id);
                });
            };

            function deleteDraw(id) {
                $.ajax({
                    url: "{{ route('weekly-draws.destroy', '') }}/" + id,
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
        });
    </script>
@endpush
