@extends('backend.app')

@section('title', 'Donors Management')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <h1 class="page-title">Donors Management</h1>
                    <div class="ms-auto">
                        <button class="btn btn-success" id="exportBtn">
                            <i class="fe fe-download me-2"></i>Export Donors
                        </button>
                    </div>
                </div>

                <!-- Stats -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-primary-gradient text-white">
                            <div class="card-body">
                                <h6>Unique Donors</h6>
                                <h3 class="mb-0">{{ number_format($stats['total_donors']) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-success-gradient text-white">
                            <div class="card-body">
                                <h6>Total Donations</h6>
                                <h3 class="mb-0">{{ number_format($stats['total_donations']) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-info-gradient text-white">
                            <div class="card-body">
                                <h6>Total Collected</h6>
                                <h3 class="mb-0">${{ number_format($stats['total_amount'], 2) }}</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-xl-3 col-md-6">
                        <div class="card bg-warning-gradient text-white">
                            <div class="card-body">
                                <h6>Paid Amount</h6>
                                <h3 class="mb-0">${{ number_format($stats['paid_amount'], 2) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters + Table -->
                @include('backend.layouts.donation_&_draw.partials.donor_filters_and_table')
            </div>
        </div>
    </div>

    <!-- View Donor Modal -->
    @include('backend.layouts.donation_&_draw.partials.donor_view_modal')

@endsection

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.11/clipboard.min.js"></script>
    <script>
        $(document).ready(function() {
            let table = $('#donorsTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('donors.index') }}",
                    data: function(d) {
                        d.week_id = $('#weekFilter').val();
                        d.date_from = $('#dateFrom').val();
                        d.date_to = $('#dateTo').val();
                        d.min_amount = $('#minAmount').val();
                        d.max_amount = $('#maxAmount').val();
                        d.payment_status = $('#paymentStatusFilter').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'donor_name',
                        name: 'donor_name'
                    },
                    {
                        data: 'donor_id',
                        name: 'donor_id'
                    },
                    {
                        data: 'email',
                        name: 'email'
                    },
                    {
                        data: 'phone',
                        name: 'phone'
                    },
                    {
                        data: 'week',
                        name: 'week'
                    },
                    {
                        data: 'amount',
                        name: 'amount'
                    },
                    {
                        data: 'donated_at',
                        name: 'donated_at'
                    },
                    {
                        data: 'payment_status',
                        name: 'payment_status'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                drawCallback: function(settings) {
                    $('#totalCount').text(settings.json.recordsFiltered);
                },
                order: [
                    [6, 'desc']
                ],
                pageLength: 50,
                lengthMenu: [
                    [25, 50, 100, 500],
                    [25, 50, 100, 500]
                ]
            });

            $('#applyFilters').click(function() {
                table.ajax.reload();
            });

            $('#resetFilters').click(function() {
                $('#filterForm')[0].reset();
                table.ajax.reload();
            });

            $('#exportBtn').click(function() {
                window.location = "{{ route('donors.export') }}?" + new URLSearchParams({
                    week_id: $('#weekFilter').val(),
                    date_from: $('#dateFrom').val(),
                    date_to: $('#dateTo').val(),
                    min_amount: $('#minAmount').val(),
                    max_amount: $('#maxAmount').val(),
                }).toString();
            });

            // ===== View Donor - Complete Data Population =====
            $(document).on('click', '.viewDonor', function() {
                let id = $(this).data('id');

                $.get("{{ route('donors.show', '') }}/" + id, function(res) {
                    if (res.success) {
                        let d = res.data;
                        let user = d.user || {};
                        let draw = d.weekly_draw || {};

                        // ==== DONOR INFORMATION ====
                        $('#d_name').text(user.name || 'N/A');
                        $('#d_email').text(user.email || 'N/A');
                        $('#d_phone').text(user.phone || 'N/A');
                        $('#d_address').text(user.address || 'N/A');
                        $('#donor_id').text(user.donor_id || 'N/A');
                        $('#d_role').text(user.role ? user.role.toUpperCase() : 'N/A');
                        $('#d_registered_at').text(user.created_at ?
                            new Date(user.created_at).toLocaleString() : 'N/A');

                        // ==== STRIPE INFORMATION ====
                        $('#stripe_customer_id').text(user.stripe_customer_id || 'N/A');
                        $('#d_payment_id').text(d.stripe_payment_id || 'N/A');
                        $('#stripe_charge_id').text(d.stripe_charge_id || 'N/A');

                        // Payment Status Badge
                        let statusBadge = '';
                        if (d.stripe_payment_status === 'succeeded' || d.stripe_payment_status ===
                            'completed') {
                            statusBadge = '<span class="badge bg-success">Paid</span>';
                        } else if (d.stripe_payment_status === 'pending') {
                            statusBadge = '<span class="badge bg-warning">Pending</span>';
                        } else {
                            statusBadge = '<span class="badge bg-secondary">' +
                                (d.stripe_payment_status || 'Unknown') + '</span>';
                        }
                        $('#d_status').html(statusBadge);

                        // ==== DONOR STATISTICS ====
                        $('#donation_attempt').text(user.total_donations_count || 0);
                        $('#lifetime_donate_amount').text('$' + parseFloat(user
                            .lifetime_donation_amount || 0).toFixed(2));
                        $('#times_won').text(user.times_won || 0);
                        $('#last_donation_at').text(user.last_donation_at ?
                            new Date(user.last_donation_at).toLocaleString() : 'Never');
                        $('#last_won_at').text(user.last_won_at ?
                            new Date(user.last_won_at).toLocaleString() : 'Never');

                        // ==== DONATION DETAILS ====
                        $('#donation_id').text(d.id || 'N/A');
                        $('#d_amount').text('$' + parseFloat(d.amount || 0).toFixed(2));
                        $('#d_donated_at').text(d.donated_at ?
                            new Date(d.donated_at).toLocaleString() : 'N/A');
                        $('#payment_type').text(d.payment_type ? d.payment_type.toUpperCase() :
                            'N/A');
                        $('#attempt_number').text(d.attempt_number || 'N/A');

                        // Eligible for Draw
                        let eligibleBadge = d.is_eligible_for_draw ?
                            '<span class="badge badge-sm bg-success"><i class="fe fe-check" style="font-size:10px"></i> Yes</span>' :
                            '<span class="badge badge-sm bg-danger"><i class="fe fe-x" style="font-size:10px"></i> No</span>';
                        $('#is_eligible').html(eligibleBadge);

                        $('#temp_identifier').text(d.temp_identifier || 'N/A');
                        $('#donation_created_at').text(d.created_at ?
                            new Date(d.created_at).toLocaleString() : 'N/A');
                        $('#donation_updated_at').text(d.updated_at ?
                            new Date(d.updated_at).toLocaleString() : 'N/A');

                        // ==== DRAW INFORMATION ====
                        if (draw && draw.id) {
                            $('#d_week').html('<span class="badge bg-primary fs-6">Week #' +
                                draw.week_number + '</span>');
                            $('#draw_year').text(draw.year || 'N/A');

                            // Draw Status Badge
                            let drawStatusBadge = '';
                            if (draw.status === 'active') {
                                drawStatusBadge = '<span class="badge bg-success">Active</span>';
                            } else if (draw.status === 'claiming') {
                                drawStatusBadge = '<span class="badge bg-warning">Claiming</span>';
                            } else if (draw.status === 'completed') {
                                drawStatusBadge =
                                    '<span class="badge bg-secondary">Completed</span>';
                            } else {
                                drawStatusBadge = '<span class="badge bg-info">' +
                                    (draw.status || 'Unknown') + '</span>';
                            }
                            $('#draw_status').html(drawStatusBadge);

                            $('#draw_start_date').text(draw.start_date ?
                                new Date(draw.start_date).toLocaleString() : 'N/A');
                            $('#draw_end_date').text(draw.end_date ?
                                new Date(draw.end_date).toLocaleString() : 'N/A');
                            $('#countdown_ends_at').text(draw.countdown_ends_at ?
                                new Date(draw.countdown_ends_at).toLocaleString() : 'N/A');
                            $('#claim_deadline').text(draw.claim_deadline ?
                                new Date(draw.claim_deadline).toLocaleString() : 'N/A');

                            $('#total_pool').text('$' + parseFloat(draw.total_pool || 0).toFixed(
                            2));
                            $('#total_participants').text((draw.total_participants || 0)
                                .toLocaleString());
                            $('#eligible_participants').text((draw.eligible_participants || 0)
                                .toLocaleString());
                            $('#excluded_winners_count').text(draw.excluded_winners_count || 0);
                            $('#total_recipients').text(draw.total_recipients || 0);
                            $('#admin_commission').text('$' + parseFloat(draw.admin_commission || 0)
                                .toFixed(2));

                            // Winners Selected
                            let winnersSelectedBadge = draw.winners_selected ?
                                '<span class="badge badge-sm bg-success"><i class="fe fe-check" style="font-size:10px"></i> Yes</span>' :
                                '<span class="badge badge-sm bg-secondary"><i class="fe fe-x" style="font-size:10px"></i> No</span>';
                            $('#winners_selected').html(winnersSelectedBadge);
                        } else {
                            // No draw data available
                            $('#d_week').html('<span class="badge bg-secondary">N/A</span>');
                            $('#draw_year, #draw_start_date, #draw_end_date, #countdown_ends_at, #claim_deadline')
                                .text('N/A');
                            $('#draw_status').html('<span class="badge bg-secondary">N/A</span>');
                            $('#total_pool, #admin_commission').text('$0.00');
                            $('#total_participants, #eligible_participants, #excluded_winners_count, #total_recipients')
                                .text('0');
                            $('#winners_selected').html(
                                '<span class="badge bg-secondary">N/A</span>');
                        }

                        // Show Modal
                        $('#viewDonorModal').modal('show');
                    } else {
                        toastr.error('Failed to load donor details');
                    }
                }).fail(function() {
                    toastr.error('Error loading donor data');
                });
            });
        });
    </script>

    {{-- Copy to clipboard --}}
    <script>
        new ClipboardJS('.copy-btn');
        $(document).on('click', '.copy-btn', function() {
            toastr.success('Copied to clipboard!');
        });
    </script>
@endpush
