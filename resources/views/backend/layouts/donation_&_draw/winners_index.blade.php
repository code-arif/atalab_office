@extends('backend.app')

@section('title', 'Draw Winners Management')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title">Draw Winners Management</h1>
                        <p class="text-muted">Manage winner claims and payouts</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <button type="button" class="btn btn-success" id="exportBtn">
                            <i class="fe fe-download me-2"></i>Export Data
                        </button>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card overflow-hidden sales-card bg-primary-gradient">
                            <div class="ps-3 pt-3 pe-3 pb-2">
                                <h6 class="mb-3 tx-12 text-white">TOTAL WINNERS</h6>
                                <div class="pb-0 mt-0">
                                    <div class="d-flex">
                                        <div>
                                            <h4 class="tx-20 fw-bold mb-1 text-white">
                                                {{ number_format($stats['total_winners']) }}</h4>
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

                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card overflow-hidden sales-card bg-success-gradient">
                            <div class="ps-3 pt-3 pe-3 pb-2">
                                <h6 class="mb-3 tx-12 text-white">CLAIMED WINNERS</h6>
                                <div class="pb-0 mt-0">
                                    <div class="d-flex">
                                        <div>
                                            <h4 class="tx-20 fw-bold mb-1 text-white">{{ number_format($stats['claimed']) }}
                                            </h4>
                                            <p class="mb-0 tx-12 text-white op-7">Successfully Claimed</p>
                                        </div>
                                        <span class="float-end my-auto ms-auto">
                                            <i class="fas fa-check-circle text-white fs-3"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card overflow-hidden sales-card bg-warning-gradient">
                            <div class="ps-3 pt-3 pe-3 pb-2">
                                <h6 class="mb-3 tx-12 text-white">UNCLAIMED</h6>
                                <div class="pb-0 mt-0">
                                    <div class="d-flex">
                                        <div>
                                            <h4 class="tx-20 fw-bold mb-1 text-white">
                                                {{ number_format($stats['unclaimed']) }}</h4>
                                            <p class="mb-0 tx-12 text-white op-7">Awaiting Claim</p>
                                        </div>
                                        <span class="float-end my-auto ms-auto">
                                            <i class="fas fa-clock text-white fs-3"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-lg-6 col-md-6">
                        <div class="card overflow-hidden sales-card bg-info-gradient">
                            <div class="ps-3 pt-3 pe-3 pb-2">
                                <h6 class="mb-3 tx-12 text-white">TOTAL PAYOUTS</h6>
                                <div class="pb-0 mt-0">
                                    <div class="d-flex">
                                        <div>
                                            <h4 class="tx-20 fw-bold mb-1 text-white">
                                                ${{ number_format($stats['total_payouts'], 2) }}</h4>
                                            <p class="mb-0 tx-12 text-white op-7">Completed</p>
                                        </div>
                                        <span class="float-end my-auto ms-auto">
                                            <i class="fas fa-dollar-sign text-white fs-3"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced Filters -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-light">
                                <h5 class="card-title mb-0">
                                    <i class="fe fe-filter me-2"></i>Advanced Filters
                                    <button type="button" class="btn btn-sm btn-outline-secondary" id="resetFilters">
                                        <i class="fe fe-x me-1" style="font-size: 10px"></i>Reset
                                    </button>
                                </h5>
                            </div>
                            <div class="card-body">
                                <form id="filterForm">
                                    <div class="row g-3">
                                        <div class="col-md-3">
                                            <label class="form-label">Claim Status</label>
                                            <select class="form-select" id="claimStatusFilter">
                                                <option value="">All</option>
                                                <option value="claimed">Claimed</option>
                                                <option value="unclaimed">Unclaimed</option>
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Payout Status</label>
                                            <select class="form-select" id="payoutStatusFilter">
                                                <option value="">All</option>
                                                <option value="pending">Pending</option>
                                                <option value="processing">Processing</option>
                                                <option value="completed">Completed</option>
                                                <option value="failed">Failed</option>
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Week</label>
                                            <select class="form-select" id="weekFilter">
                                                <option value="">All Weeks</option>
                                                @foreach ($weeks as $week)
                                                    <option value="{{ $week->id }}">Week #{{ $week->week_number }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Date From</label>
                                            <input type="date" class="form-control" id="dateFrom">
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Date To</label>
                                            <input type="date" class="form-control" id="dateTo">
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Min Amount ($)</label>
                                            <input type="number" class="form-control" id="minAmount" placeholder="0.00"
                                                step="0.01">
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Max Amount ($)</label>
                                            <input type="number" class="form-control" id="maxAmount"
                                                placeholder="1000.00" step="0.01">
                                        </div>

                                        <div class="col-md-3 d-flex align-items-end">
                                            <button type="button" class="btn btn-primary w-100" id="applyFilters">
                                                <i class="fe fe-search me-2"></i>Apply Filters
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">All Draw Winners</h3>
                                <div>
                                    <span class="badge bg-info me-2">Total: <span id="totalCount">0</span></span>
                                    <span class="badge bg-primary">Pending Payouts: $<span
                                            id="pendingAmount">{{ number_format($stats['pending_payouts'], 2) }}</span></span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover mb-0" id="winnersTable">
                                        <thead class="table-light">
                                            <tr>
                                                <th width="50">#</th>
                                                <th>Winner Name</th>
                                                <th>Email</th>
                                                <th>Week</th>
                                                <th>Amount Won</th>
                                                <th>Claim Status</th>
                                                <th>Claimed Date</th>
                                                <th>Payout Status</th>
                                                <th>Stripe ID</th>
                                                <th width="120">Action</th>
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

    <!-- View Winner Modal -->
    <div class="modal fade" id="viewWinnerModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fe fe-eye me-2"></i>Winner Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fe fe-user me-2"></i>Winner Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless table-sm mb-0">
                                        <tr>
                                            <td class="fw-bold" width="40%">Name:</td>
                                            <td id="view_name">---</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Email:</td>
                                            <td id="view_email">---</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Phone:</td>
                                            <td id="view_phone">---</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fe fe-calendar me-2"></i>Draw Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless table-sm mb-0">
                                        <tr>
                                            <td class="fw-bold" width="40%">Week:</td>
                                            <td id="view_week">---</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Draw Period:</td>
                                            <td id="view_draw_period">---</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Total Pool:</td>
                                            <td id="view_pool">---</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border mb-3">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fe fe-dollar-sign me-2"></i>Claim Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless table-sm mb-0">
                                        <tr>
                                            <td class="fw-bold" width="40%">Amount Won:</td>
                                            <td id="view_amount" class="text-success fw-bold">---</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Claimed:</td>
                                            <td id="view_claimed">---</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Claimed At:</td>
                                            <td id="view_claimed_at">---</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="card border">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="fe fe-credit-card me-2"></i>Payout Information</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-borderless table-sm mb-0">
                                        <tr>
                                            <td class="fw-bold" width="40%">Status:</td>
                                            <td id="view_payout_status">---</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Stripe ID:</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <code id="view_stripe_id"
                                                        class="text-break text-monospace small me-2">---</code>
                                                    <button class="btn btn-sm btn-outline-secondary copy-btn"
                                                        data-clipboard-target="#view_stripe_id" title="Copy">
                                                        <i class="fe fe-copy"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td class="fw-bold">Donation ID:</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <code id="view_donation_id"
                                                        class="text-break text-monospace small me-2">---</code>
                                                    <button class="btn btn-sm btn-outline-secondary copy-btn"
                                                        data-clipboard-target="#view_donation_id" title="Copy">
                                                        <i class="fe fe-copy"></i>
                                                    </button>
                                                </div>
                                            </td>
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

    <!-- Mark Claimed Modal -->
    <div class="modal fade" id="markClaimedModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title"><i class="fe fe-check-circle me-2"></i>Mark as Claimed</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="claimForm">
                    <div class="modal-body">
                        <input type="hidden" id="claim_winner_id">

                        <div class="alert alert-info">
                            <i class="fe fe-info me-2"></i>
                            <strong>Note:</strong> Enter the Stripe payout ID to mark this winner as claimed.
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Stripe Payout ID <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="stripe_payout_id" required
                                placeholder="po_1xxxxxxxxxxxxx">
                            <small class="text-muted">The Stripe payout transaction ID</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Admin Notes (Optional)</label>
                            <textarea class="form-control" id="admin_notes" rows="3" placeholder="Add any additional notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">
                            <i class="fe fe-check me-2"></i>Mark as Claimed
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .table-hover tbody tr:hover {
            background-color: #f8f9fa;
        }

        .btn-group {
            gap: 3px;
        }

        #winnersTable {
            font-size: 14px;
        }

        #winnersTable th {
            font-weight: 600;
            font-size: 13px;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.11/clipboard.min.js"></script>

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Initialize DataTable with server-side processing for better performance
            let dTable = $('#winnersTable').DataTable({
                order: [
                    [0, 'desc']
                ],
                lengthMenu: [
                    [25, 50, 100, 250],
                    [25, 50, 100, 250]
                ],
                pageLength: 25,
                processing: true,
                serverSide: true,
                responsive: false,
                deferRender: true,
                ajax: {
                    url: "{{ route('draw-winners.index') }}",
                    type: 'GET',
                    data: function(d) {
                        d.claim_status = $('#claimStatusFilter').val();
                        d.payout_status = $('#payoutStatusFilter').val();
                        d.week_id = $('#weekFilter').val();
                        d.date_from = $('#dateFrom').val();
                        d.date_to = $('#dateTo').val();
                        d.min_amount = $('#minAmount').val();
                        d.max_amount = $('#maxAmount').val();
                    },
                    error: function(xhr, error, code) {
                        console.log('Ajax Error Details:');
                        console.log('Status:', xhr.status);
                        console.log('Response:', xhr.responseText);
                        console.log('Error:', error);
                        console.log('Code:', code);

                        toastr.error('Failed to load data. Check console for details.');
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'winner_name'
                    },
                    {
                        data: 'email'
                    },
                    {
                        data: 'week'
                    },
                    {
                        data: 'amount'
                    },
                    {
                        data: 'claim_status'
                    },
                    {
                        data: 'claimed_date'
                    },
                    {
                        data: 'payout_status'
                    },
                    {
                        data: 'stripe_info'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                drawCallback: function(settings) {
                    $('#totalCount').text(settings.json.recordsFiltered);
                }
            });

            // Apply Filters
            $('#applyFilters').on('click', function() {
                dTable.ajax.reload();
            });

            // Reset Filters
            $('#resetFilters').on('click', function() {
                $('#filterForm')[0].reset();
                dTable.ajax.reload();
            });

            // Filter on Enter key
            $('#filterForm input').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    dTable.ajax.reload();
                }
            });

            // View Winner
            $(document).on('click', '.viewWinner', function() {
                let id = $(this).data('id');
                $.get("{{ route('draw-winners.show', '') }}/" + id, function(res) {
                    if (res.success) {
                        let d = res.data;
                        $('#view_name').text(d.user.name);
                        $('#view_email').text(d.user.email);
                        $('#view_phone').text(d.user.phone || 'N/A');
                        $('#view_week').html('<span class="badge bg-primary">Week #' + d.weekly_draw
                            .week_number + '</span>');
                        $('#view_draw_period').text(
                            new Date(d.weekly_draw.start_date).toLocaleDateString() + ' - ' +
                            new Date(d.weekly_draw.end_date).toLocaleDateString()
                        );
                        $('#view_pool').text('$' + parseFloat(d.weekly_draw.total_pool).toFixed(2));
                        $('#view_amount').text('$' + parseFloat(d.amount_won).toFixed(2));
                        $('#view_claimed').html(d.claimed ?
                            '<span class="badge bg-success">Yes</span>' :
                            '<span class="badge bg-warning">No</span>'
                        );
                        $('#view_claimed_at').text(d.claimed_at ?
                            new Date(d.claimed_at).toLocaleString() :
                            'Not claimed yet'
                        );
                        $('#view_payout_status').html(
                            '<span class="badge bg-' +
                            (d.payout_status === 'completed' ? 'success' :
                                d.payout_status === 'processing' ? 'info' :
                                d.payout_status === 'failed' ? 'danger' : 'secondary') +
                            '">' + d.payout_status + '</span>'
                        );
                        $('#view_stripe_id').text(d.payout_stripe_id || 'N/A');
                        $('#view_donation_id').text(d.donation.stripe_payment_id);

                        $('#viewWinnerModal').modal('show');
                    }
                });
            });

            // Mark as Claimed
            $(document).on('click', '.markClaimed', function() {
                let id = $(this).data('id');
                $('#claim_winner_id').val(id);
                $('#markClaimedModal').modal('show');
            });

            // Submit Claim Form
            $('#claimForm').on('submit', function(e) {
                e.preventDefault();

                let id = $('#claim_winner_id').val();
                let formData = {
                    stripe_payout_id: $('#stripe_payout_id').val(),
                    admin_notes: $('#admin_notes').val()
                };

                $.ajax({
                    url: "{{ route('draw-winners.mark-claimed', '') }}/" + id,
                    type: 'POST',
                    data: formData,
                    success: function(res) {
                        if (res.success) {
                            toastr.success(res.message);
                            $('#markClaimedModal').modal('hide');
                            $('#claimForm')[0].reset();
                            dTable.ajax.reload();
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            Object.keys(errors).forEach(function(key) {
                                toastr.error(errors[key][0]);
                            });
                        } else {
                            toastr.error('Failed to mark as claimed');
                        }
                    }
                });
            });

            // Process Payout
            $(document).on('click', '.processPayout', function() {
                let id = $(this).data('id');

                Swal.fire({
                    title: 'Process Payout?',
                    text: 'This will initiate the payout process through Stripe.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, Process!',
                    confirmButtonColor: '#0d6efd'
                }).then(result => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('draw-winners.process-payout', '') }}/" + id,
                            type: 'POST',
                            success: function(res) {
                                if (res.success) {
                                    Swal.fire('Success!', res.message, 'success');
                                    dTable.ajax.reload();
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', xhr.responseJSON.message ||
                                    'Payout processing failed', 'error');
                            }
                        });
                    }
                });
            });

            // Export Data
            $('#exportBtn').on('click', function() {
                let params = new URLSearchParams({
                    claim_status: $('#claimStatusFilter').val(),
                    payout_status: $('#payoutStatusFilter').val(),
                    week_id: $('#weekFilter').val(),
                    date_from: $('#dateFrom').val(),
                    date_to: $('#dateTo').val(),
                    min_amount: $('#minAmount').val(),
                    max_amount: $('#maxAmount').val()
                });

                window.location.href = "{{ route('draw-winners.export') }}?" + params.toString();
            });
        });
    </script>

    {{-- copy stripe id funciton --}}
    <script>
        // Copy to clipboard
        new ClipboardJS('.copy-btn');
        $(document).on('click', '.copy-btn', function() {
            toastr.success('Copied to clipboard!');
        });
    </script>
@endpush
