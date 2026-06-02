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
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fe fe-eye me-2"></i>Draw Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
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
                                            <td class="fw-bold" width="40%">Year:</td>
                                            <td id="view_year">---</td>
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
                                            <td class="fw-bold">Expected Winners:</td>
                                            <td id="view_expected_winners" class="text-info fw-bold">---</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Actual Recipients:</td>
                                            <td id="view_recipients" class="text-success fw-bold">---</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Odds:</td>
                                            <td id="view_odds" class="text-muted">1:400</td>
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
                        <li class="list-group-item"><i class="fe fe-info text-info me-2"></i> Donors will no longer be able to donate to this draw.</li>
                        <li class="list-group-item"><i class="fe fe-info text-info me-2"></i> The donation button will become inactive on the public page.</li>
                        <li class="list-group-item"><i class="fe fe-info text-info me-2"></i> If paused, you must resume it before the Draw End Date to allow proper finalization.</li>
                        <li class="list-group-item"><i class="fe fe-info text-info me-2"></i> Current participants and pool size will be frozen until resumed.</li>
                    </ul>
                    <p class="text-muted small">You can restart the draw later by clicking "Make Active" in the actions menu.</p>
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
                        $('#view_status').html(
                            `<span class="badge status-${d.status}">${d.status}</span>`);
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
                    data: { pause: isPausing },
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
