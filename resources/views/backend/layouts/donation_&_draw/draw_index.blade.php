@extends('backend.app')

@section('title', 'Weekly Draws Management')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title">Weekly Draws Management</h1>
                        <p class="text-muted">Automated draw system - No manual intervention required</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <a href="{{ route('weekly-draws.trashed') }}" class="btn btn-secondary">
                            <i class="fe fe-trash-2 me-2"></i>Trash Bin
                        </a>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card overflow-hidden sales-card bg-primary-gradient">
                            <div class="ps-3 pt-3 pe-3 pb-2">
                                <h6 class="mb-3 tx-12 text-white">ACTIVE DRAWS</h6>
                                <div class="pb-0 mt-0">
                                    <div class="d-flex">
                                        <div>
                                            <h4 class="tx-20 fw-bold mb-1 text-white">{{ $activeDraws }}</h4>
                                            <p class="mb-0 tx-12 text-white op-7">Currently Running</p>
                                        </div>
                                        <span class="float-end my-auto ms-auto">
                                            <i class="fas fa-play-circle text-white fs-3"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card overflow-hidden sales-card bg-success-gradient">
                            <div class="ps-3 pt-3 pe-3 pb-2">
                                <h6 class="mb-3 tx-12 text-white">TOTAL POOL</h6>
                                <div class="pb-0 mt-0">
                                    <div class="d-flex">
                                        <div>
                                            <h4 class="tx-20 fw-bold mb-1 text-white">${{ number_format($totalPool, 2) }}
                                            </h4>
                                            <p class="mb-0 tx-12 text-white op-7">Current Week</p>
                                        </div>
                                        <span class="float-end my-auto ms-auto">
                                            <i class="fas fa-dollar-sign text-white fs-3"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card overflow-hidden sales-card bg-warning-gradient">
                            <div class="ps-3 pt-3 pe-3 pb-2">
                                <h6 class="mb-3 tx-12 text-white">PARTICIPANTS</h6>
                                <div class="pb-0 mt-0">
                                    <div class="d-flex">
                                        <div>
                                            <h4 class="tx-20 fw-bold mb-1 text-white">
                                                {{ number_format($totalParticipants) }}</h4>
                                            <p class="mb-0 tx-12 text-white op-7">Current Week</p>
                                        </div>
                                        <span class="float-end my-auto ms-auto">
                                            <i class="fas fa-users text-white fs-3"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card overflow-hidden sales-card bg-info-gradient">
                            <div class="ps-3 pt-3 pe-3 pb-2">
                                <h6 class="mb-3 tx-12 text-white">TOTAL DRAWS</h6>
                                <div class="pb-0 mt-0">
                                    <div class="d-flex">
                                        <div>
                                            <h4 class="tx-20 fw-bold mb-1 text-white">{{ $totalDraws }}</h4>
                                            <p class="mb-0 tx-12 text-white op-7">All Time</p>
                                        </div>
                                        <span class="float-end my-auto ms-auto">
                                            <i class="fas fa-trophy text-white fs-3"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Alert -->
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fe fe-info me-2"></i>
                    <strong>Automated System:</strong> Draws are automatically created every Monday at 12:00 AM and
                    finalized every Sunday at 5:00 PM. Winner selection is fully automated.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

                <!-- Main Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">All Weekly Draws</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0" id="datatable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Week Number</th>
                                                <th>Year</th>
                                                <th>Status</th>
                                                <th>Start Date</th>
                                                <th>End Date</th>
                                                <th>Total Pool</th>
                                                <th>Participants</th>
                                                <th>Expected Winners</th>
                                                <th>Actual Recipients</th>
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


    <!-- View Draw Modal -->
    <div class="modal fade" id="viewDrawModal" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header border-bottom-0 pb-0 pt-4 px-4 bg-white rounded-top-4">
                    <div class="d-flex align-items-center w-100">
                        <div class="bg-primary-transparent p-3 rounded-circle me-3 d-flex align-items-center justify-content-center"
                            style="width: 50px; height: 50px;">
                            <i class="fe fe-calendar text-primary fs-3"></i>
                        </div>
                        <div>
                            <h4 class="mb-1 fw-bolder text-dark">Weekly Draw Details</h4>
                            <div class="d-flex align-items-center text-muted fs-13">
                                <i class="fe fe-hash me-1 text-primary"></i> <span id="view_week_number"
                                    class="me-3 fw-medium">---</span>
                                <i class="fe fe-clock me-1 text-primary"></i> <span id="view_year"
                                    class="fw-medium">---</span>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body p-4" style="background-color: #f9fafb;">
                    <div class="row g-4">
                        <!-- LEFT COLUMN -->
                        <div class="col-md-6">
                            <!-- Basic Information -->
                            <div class="card pro-card mb-4 h-auto" style="border-top: 4px solid #5066e1;">
                                <div class="card-body">
                                    <h5 class="card-title-pro"><i class="fe fe-info"></i>Basic Information</h5>
                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <span class="info-label">Current Status</span>
                                            <div class="mt-1" id="view_status">---</div>
                                        </div>
                                        <div class="col-sm-6">
                                            <span class="info-label">Winners Selected</span>
                                            <div class="mt-1" id="view_winners_selected">---</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Timeline -->
                            <div class="card pro-card mb-4 h-auto">
                                <div class="card-body">
                                    <h5 class="card-title-pro"><i class="fe fe-clock"></i>Draw Timeline</h5>
                                    <div class="row g-3 bg-primary-transparent rounded-1 p-3 mb-2">
                                        <div class="col-6">
                                            <span class="info-label text-primary">Start Date</span>
                                            <span class="info-value fs-13" id="view_start_date">---</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="info-label text-primary">End Date</span>
                                            <span class="info-value fs-13" id="view_end_date">---</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="info-label text-primary">Countdown Ends</span>
                                            <span class="info-value fs-13" id="view_countdown_ends">---</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="info-label text-danger">Claim Deadline</span>
                                            <span class="info-value text-danger fs-13 fw-bold"
                                                id="view_claim_deadline">---</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="col-md-6">
                            <!-- Financial Details -->
                            <div class="card pro-card mb-4 h-auto" style="border-top: 4px solid #19b159;">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-3 border-bottom pb-3">
                                        <h5 class="card-title-pro border-0 p-0 m-0"><i
                                                class="fe fe-dollar-sign text-success"></i>Total Pool Size</h5>
                                        <h2 class="mb-0 text-success fw-bolder" id="view_total_pool">---</h2>
                                    </div>
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <span class="info-label">Distribution Pool</span>
                                            <span class="info-value text-primary fw-bold fs-5"
                                                id="view_distribution_pool">---</span>
                                        </div>
                                        <div class="col-6">
                                            <span class="info-label">Admin Commission</span>
                                            <span class="info-value text-info fw-bold fs-5"
                                                id="view_commission">---</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Statistics -->
                            <div class="card pro-card m-0">
                                <div class="card-body">
                                    <h5 class="card-title-pro"><i class="fe fe-users"></i>Participation Stats</h5>
                                    <div class="row g-3">
                                        <div class="col-6 border-end border-bottom pb-3">
                                            <span class="info-label">Total Participants</span>
                                            <span class="info-value fw-bolder text-warning fs-5"
                                                id="view_participants">---</span>
                                        </div>
                                        <div class="col-6 border-bottom pb-3">
                                            <span class="info-label">Expected Winners</span>
                                            <span class="info-value fw-bolder text-info fs-5"
                                                id="view_expected_winners">---</span>
                                        </div>
                                        <div class="col-6 pt-2">
                                            <span class="info-label">Actual Recipients</span>
                                            <span class="info-value fw-bolder text-success fs-5"
                                                id="view_recipients">---</span>
                                        </div>
                                        <div class="col-6 pt-2">
                                            <span class="info-label">Global Odds</span>
                                            <span class="info-value text-muted fs-5 fw-bold" id="view_odds">1:400</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="modal-footer border-top-0 pt-3 pb-4 px-4 bg-white rounded-bottom-4 d-flex justify-content-end shadow-sm">
                    <button type="button" class="btn btn-dark px-4 py-2 fw-semibold shadow-sm" data-bs-dismiss="modal">
                        Close Details
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Pause Draw Modal -->
    <div class="modal fade" id="pauseDrawModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title"><i class="fe fe-alert-triangle me-2"></i>Pause Active Draw</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="mb-3 fw-bold text-danger">Are you sure you want to pause this draw?</p>
                    <ul class="list-group list-group-flush mb-3">
                        <li class="list-group-item"><i class="fe fe-info text-info me-2"></i> Donors will no longer be
                            able to donate to this draw.</li>
                        <li class="list-group-item"><i class="fe fe-info text-info me-2"></i> The donation button will
                            become inactive on the public page.</li>
                        <li class="list-group-item"><i class="fe fe-info text-info me-2"></i> If paused, you must resume
                            it before the Draw End Date to allow proper finalization.</li>
                        <li class="list-group-item"><i class="fe fe-info text-info me-2"></i> Current participants and
                            pool size will be frozen until resumed.</li>
                    </ul>
                    <p class="text-muted small">You can restart the draw later by clicking "Make Active" in the actions
                        menu.</p>
                    <input type="hidden" id="pause_draw_id">
                </div>
                <div class="modal-footer d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning" onclick="executePauseDraw()">Yes, Pause Draw</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .badge-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
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

        .btn-action-group {
            display: flex;
            gap: 5px;
        }
    </style>

    <style>
        #viewDrawModal .info-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #7b8190;
            font-weight: 600;
            margin-bottom: 2px;
            display: block;
        }

        #viewDrawModal .info-value {
            font-size: 14px;
            color: #333;
            font-weight: 500;
            word-break: break-all;
        }

        #viewDrawModal .card-title-pro {
            font-size: 15px;
            font-weight: 700;
            color: #2c323f;
            margin-bottom: 15px;
            border-bottom: 1px solid #f0f0f5;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
        }

        #viewDrawModal .card-title-pro i {
            margin-right: 8px;
            color: #5066e1;
        }

        #viewDrawModal .pro-card {
            border-radius: 12px;
            border: 1px solid #e9edf4;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
            background-color: #ffffff;
        }

        #viewDrawModal .fs-13 {
            font-size: 13px !important;
        }

        #viewDrawModal .fs-12 {
            font-size: 12px !important;
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

            let dTable = $('#datatable').DataTable({
                order: [
                    [1, 'desc']
                ],
                lengthMenu: [
                    [20, 50, 100],
                    [20, 50, 100]
                ],
                processing: true,
                responsive: false,
                serverSide: true,
                ajax: "{{ route('weekly-draws.index') }}",
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'week_number'
                    },
                    {
                        data: 'year'
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
                        data: 'expected_winners'
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

            // View Draw
            $(document).on('click', '.viewDraw', function() {
                let id = $(this).data('id');
                $.get("{{ route('weekly-draws.show', '') }}/" + id, function(res) {
                    if (res.success) {
                        let d = res.data;
                        // alert(d);
                        $('#view_week_number').text('Week #' + d.week_number);
                        $('#view_year').text(d.year);

                        let statusHtml = '';
                        if (d.is_paused) {
                            statusHtml = '<span class="badge bg-danger-transparent text-danger d-inline-flex align-items-center px-2 py-1"> <i class="fe fe-pause-circle me-1"></i> Paused </span>';
                        } else {
                            statusHtml = `<span class="badge status-${d.status} px-2 py-1 text-uppercase">${d.status}</span>`;
                        }
                        $('#view_status').html(statusHtml);

                        $('#view_winners_selected').html(d.winners_selected ?
                            '<span class="badge bg-success">Yes</span>' :
                            '<span class="badge bg-secondary">No</span>');
                        $('#view_start_date').text(new Date(d.start_date).toLocaleString());
                        $('#view_end_date').text(new Date(d.end_date).toLocaleString());
                        $('#view_countdown_ends').text(new Date(d.countdown_ends_at)
                            .toLocaleString());
                        $('#view_claim_deadline').text(new Date(d.claim_deadline).toLocaleString());
                        $('#view_total_pool').text('$' + parseFloat(d.total_pool).toFixed(2));
                        $('#view_commission').text('$' + parseFloat(d.admin_commission).toFixed(2));
                        $('#view_distribution_pool').text('$' + d.distribution_pool.toFixed(2));
                        $('#view_participants').text(d.total_participants);
                        $('#view_expected_winners').text(d.expected_winners);
                        $('#view_recipients').text(d.total_recipients || 'Pending');
                        $('#view_odds').text('1: ' + d.settings.odds_ratio);
                        $('#viewDrawModal').modal('show');
                    }
                });
            });

            // Pause Draw Handling
            window.confirmPauseDraw = function(id) {
                $('#pause_draw_id').val(id);
                $('#pauseDrawModal').modal('show');
            };

            window.executePauseDraw = function() {
                let id = $('#pause_draw_id').val();
                togglePauseDraw(id, true);
                $('#pauseDrawModal').modal('hide');
            };

            window.togglePauseDraw = function(id, isPausing) {
                let actionText = isPausing ? 'pausing...' : 'activating...';
                $.ajax({
                    url: "{{ url('admin/weekly-draws') }}/" + id + "/toggle-pause",
                    type: 'POST',
                    data: {
                        pause: isPausing
                    },
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message);
                            dTable.ajax.reload(null, false);
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    error: function(err) {
                        toastr.error('An error occurred while toggling draw status.');
                    }
                });
            };

            // Soft Delete
            window.softDeleteDraw = function(id) {
                Swal.fire({
                    title: 'Move to Trash?',
                    text: 'You can restore it later from trash bin.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Move!',
                    confirmButtonColor: '#d33'
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('weekly-draws.destroy', '') }}/" + id,
                            type: 'DELETE',
                            success: function(res) {
                                if (res.success) {
                                    toastr.success(res.message);
                                    dTable.ajax.reload();
                                }
                            }
                        });
                    }
                });
            };
        });
    </script>
@endpush
