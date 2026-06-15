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
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="card mb-3">
                            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0 fs-14 d-flex align-items-center">
                                    <i class="fe fe-filter me-2"></i>Advanced Filters
                                </h5>
                                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 fs-12 d-flex align-items-center" id="resetFilters">
                                    <i class="fe fe-x me-1"></i> Reset
                                </button>
                            </div>
                            <div class="card-body py-2">
                                <form id="filterForm">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-xl col-lg-3 col-md-4 col-sm-6">
                                            <label class="form-label small mb-1">Claim Status</label>
                                            <select class="form-select form-select-sm" id="claimStatusFilter">
                                                <option value="">All</option>
                                                <option value="claimed">Claimed</option>
                                                <option value="unclaimed">Unclaimed</option>
                                            </select>
                                        </div>
                                        <div class="col-xl col-lg-3 col-md-4 col-sm-6">
                                            <label class="form-label small mb-1">Payout Status</label>
                                            <select class="form-select form-select-sm" id="payoutStatusFilter">
                                                <option value="">All</option>
                                                <option value="pending">Pending</option>
                                                <option value="processing">Processing</option>
                                                <option value="completed">Completed</option>
                                                <option value="failed">Failed</option>
                                            </select>
                                        </div>
                                        <div class="col-xl col-lg-3 col-md-4 col-sm-6">
                                            <label class="form-label small mb-1">Week</label>
                                            <select class="form-select form-select-sm" id="weekFilter">
                                                <option value="">All Weeks</option>
                                                @foreach ($weeks as $week)
                                                    <option value="{{ $week->id }}">Wk #{{ $week->week_number }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-xl col-lg-3 col-md-4 col-sm-6">
                                            <label class="form-label small mb-1">Date From</label>
                                            <input type="date" class="form-control form-control-sm" id="dateFrom">
                                        </div>
                                        <div class="col-xl col-lg-3 col-md-4 col-sm-6">
                                            <label class="form-label small mb-1">Date To</label>
                                            <input type="date" class="form-control form-control-sm" id="dateTo">
                                        </div>
                                        <div class="col-xl col-lg-2 col-md-4 col-sm-6">
                                            <label class="form-label small mb-1">Min ($)</label>
                                            <input type="number" class="form-control form-control-sm" id="minAmount" placeholder="0.00" step="0.01">
                                        </div>
                                        <div class="col-xl col-lg-2 col-md-4 col-sm-6">
                                            <label class="form-label small mb-1">Max ($)</label>
                                            <input type="number" class="form-control form-control-sm" id="maxAmount" placeholder="1000" step="0.01">
                                        </div>
                                        <div class="col-xl-auto col-lg-12 col-md-4 col-sm-12 ms-auto mt-2 mt-xl-0">
                                            <button type="button" class="btn btn-primary btn-sm w-100 px-3 d-flex justify-content-center align-items-center" id="applyFilters">
                                                <i class="fe fe-search me-1"></i> Apply
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
                                    {{-- <span class="badge bg-primary">Pending Payouts: $<span
                                            id="pendingAmount">{{ number_format($stats['pending_payouts'], 2) }}</span></span> --}}
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

    @include('backend.layouts.donation_&_draw.partials.winner_view_modal')
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

            // Initialize DataTable with server-side processing
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
                    // Update total count
                    $('#totalCount').text(settings.json.recordsFiltered);

                    // Update pending payouts from AJAX response
                    if (settings.json.pending_payouts !== undefined) {
                        $('#pendingAmount').text(settings.json.pending_payouts);
                    }
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

            // View Winner Modal - Fixed Version
            $(document).on('click', '.viewWinner', function() {
                let id = $(this).data('id');

                $.get("{{ route('draw-winners.show', '') }}/" + id, function(res) {
                    if (res.success) {
                        let d = res.data;
                        let u = d.user;
                        let v = d.verification;

                        // User Information
                        $('#view_name').text(u.name || 'N/A');
                        $('#view_email').text(u.email || 'N/A');
                        $('#view_phone').text(u.phone || 'N/A');
                        $('#address').text(u.address || 'N/A');
                        $('#donor_id').text(u.donor_id || 'N/A');
                        $('#lifetime_donation_amount').text(u.lifetime_donation_amount ? '$' +
                            parseFloat(u.lifetime_donation_amount).toFixed(2) : 'N/A');
                        $('#times_won').text(u.times_won || '0');

                        // Format dates safely
                        $('#last_donation_at').text(u.last_donation_at ? new Date(u
                            .last_donation_at).toLocaleDateString() : 'N/A');
                        $('#last_won_at').text(u.last_won_at ? new Date(u.last_won_at)
                            .toLocaleDateString() : 'N/A');

                        // Draw Information
                        $('#view_week').html('<span class="badge bg-primary">Week #' + d.weekly_draw
                            .week_number + '</span>');
                        $('#view_draw_period').text(
                            new Date(d.weekly_draw.start_date).toLocaleDateString() + ' - ' +
                            new Date(d.weekly_draw.end_date).toLocaleDateString()
                        );
                        $('#view_pool').text('$' + parseFloat(d.weekly_draw.total_pool).toFixed(2));

                        // Claim Information
                        $('#view_amount').text('$' + parseFloat(d.amount_won).toFixed(2));
                        $('#view_claimed').html(d.claimed ?
                            '<span class="badge bg-success">Yes</span>' :
                            '<span class="badge bg-warning">No</span>'
                        );
                        $('#view_claimed_at').text(d.claimed_at ?
                            new Date(d.claimed_at).toLocaleString() :
                            'Not claimed yet'
                        );

                        // Payout Information
                        let payoutColors = {
                            'completed': 'success',
                            'processing': 'info',
                            'failed': 'danger',
                            'pending': 'secondary'
                        };
                        $('#view_payout_status').html(
                            '<span class="badge bg-' + (payoutColors[d.payout_status] ||
                                'secondary') + '">' +
                            d.payout_status.toUpperCase() + '</span>'
                        );
                        $('#view_stripe_id').text(d.payout_stripe_id || 'N/A');
                        $('#view_donation_id').text(d.donation?.stripe_payment_id || 'N/A');
                        $('#view_donation_id_formatted').text(d.donation?.donation_id || 'N/A');

                        // =============================
                        // VERIFICATION INFORMATION
                        // =============================
                        if (v) {
                            // Verified By
                            $('#verified_by').text(v.verified_by || 'N/A');

                            // Admin Notes
                            $('#admin_notes').text(v.admin_notes || 'N/A');

                            // Approved Date
                            $('#approved_date').text(v.approved_at ?
                                new Date(v.approved_at).toLocaleString() : 'N/A');

                            // Rejected Date
                            $('#rejected_date').text(v.rejected_at ?
                                new Date(v.rejected_at).toLocaleString() : 'N/A');

                            // Email Verified
                            $('#view_email_verified').html(
                                u.email_verified_at ?
                                '<span class="badge bg-success"><i class="fe fe-check me-1"></i>Verified</span>' :
                                '<span class="badge bg-danger"><i class="fe fe-x me-1"></i>Not Verified</span>'
                            );

                            // Phone Verified
                            $('#view_phone_verified').html(
                                u.phone_verified_at ?
                                '<span class="badge bg-success"><i class="fe fe-check me-1"></i>Verified</span>' :
                                '<span class="badge bg-danger"><i class="fe fe-x me-1"></i>Not Verified</span>'
                            );

                            // =============================
                            // DRIVER LICENSE INFORMATION
                            // =============================
                            $('#view_license').text(v.license_number || 'N/A');
                            $('#view_license_state').text(v.license_state || 'N/A');
                            $('#view_license_expiry').text(
                                v.license_expiry ? new Date(v.license_expiry)
                                .toLocaleDateString() : 'N/A'
                            );

                            // Show/Hide identity card
                            if (v.license_number) {
                                $('#identityInfoCard').show();
                            } else {
                                $('#identityInfoCard').hide();
                            }

                            // =============================
                            // BANK INFORMATION
                            // =============================
                            $('#bank_name').text(v.bank_name || 'N/A');
                            $('#bank_acc_last4').text(v.bank_acc_last4 ? '****' + v.bank_acc_last4 :
                                'N/A');
                            $('#bank_routing_number').text(v.bank_routing || 'N/A');
                            $('#bank_verified_at').text(
                                v.bank_verified_at ? new Date(v.bank_verified_at)
                                .toLocaleString() : 'N/A'
                            );
                        } else {
                            // No verification data
                            $('#verified_by').text('N/A');
                            $('#admin_notes').text('N/A');
                            $('#approved_date').text('N/A');
                            $('#rejected_date').text('N/A');
                            $('#view_email_verified').html(
                                '<span class="badge bg-secondary">N/A</span>');
                            $('#view_phone_verified').html(
                                '<span class="badge bg-secondary">N/A</span>');
                            $('#view_license').text('N/A');
                            $('#view_license_state').text('N/A');
                            $('#view_license_expiry').text('N/A');
                            $('#bank_name').text('N/A');
                            $('#bank_acc_last4').text('N/A');
                            $('#bank_routing_number').text('N/A');
                            $('#bank_verified_at').text('N/A');
                            $('#identityInfoCard').hide();
                        }

                        // Verification Progress
                        $('#verification_progress').text(d.verification_progress + '%');

                        $('#viewWinnerModal').modal('show');
                    }
                }).fail(function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Failed to load winner details');
                });
            });

            // Mark as Claimed - Fixed
            $(document).on('click', '.markClaimed', function() {
                let id = $(this).data('id');
                $('#claim_winner_id').val(id);
                $('#markClaimedModal').modal('show');
            });

            // Submit Claim Form - Fixed Error Handling
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
                        } else {
                            toastr.error(res.message || 'Failed to mark as claimed');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            if (errors) {
                                Object.keys(errors).forEach(function(key) {
                                    toastr.error(errors[key][0]);
                                });
                            } else {
                                toastr.error(xhr.responseJSON?.message || 'Validation failed');
                            }
                        } else if (xhr.status === 400) {
                            toastr.warning(xhr.responseJSON?.message || 'Bad request');
                        } else {
                            toastr.error(xhr.responseJSON?.message ||
                                'Failed to mark as claimed');
                        }
                    }
                });
            });

            // Process Payout - Fixed Error Handling
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
                                } else {
                                    Swal.fire('Error!', res.message ||
                                        'Payout processing failed', 'error');
                                }
                            },
                            error: function(xhr) {
                                let errorMsg = 'Payout processing failed';
                                if (xhr.status === 400) {
                                    errorMsg = xhr.responseJSON?.message ||
                                        'Invalid request';
                                } else if (xhr.status === 500) {
                                    errorMsg = xhr.responseJSON?.message ||
                                        'Server error occurred';
                                } else {
                                    errorMsg = xhr.responseJSON?.message || errorMsg;
                                }
                                Swal.fire('Error!', errorMsg, 'error');
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
