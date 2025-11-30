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

            // View Donor
            $(document).on('click', '.viewDonor', function() {
                let id = $(this).data('id');
                $.get("{{ route('donors.show', '') }}/" + id, function(res) {
                    if (res.success) {
                        let d = res.data;
                        $('#d_name').text(d.user.name);
                        $('#d_email').text(d.user.email);
                        $('#d_phone').text(d.user.phone || 'N/A');
                        $('#d_week').html(d.weekly_draw ? '<span class="badge bg-primary">Week #' +
                            d.weekly_draw.week_number + '</span>' : 'N/A');
                        $('#d_amount').text('$' + parseFloat(d.amount).toFixed(2));
                        $('#d_donated_at').text(new Date(d.donated_at).toLocaleString());
                        $('#d_payment_id').text(d.stripe_payment_id);
                        $('#d_status').html(d.stripe_payment_status === 'succeeded' ?
                            '<span class="badge bg-success">Paid</span>' :
                            '<span class="badge bg-warning">Pending</span>');
                        $('#donor_id').text(d.user.donor_id);
                        $('#stripe_customer_id').text(d.user.stripe_customer_id);
                        $('#donation_attempt').text(d.user.total_donations_count);
                        $('#lifetime_donate_amount').text(d.user.lifetime_donation_amount);
                        $('#times_won').text(d.user.times_won);
                        $('#last_donation_at').text(d.user.last_donation_at ?
                            new Date(d.user.last_donation_at).toLocaleString() : 'Never');

                        $('#last_won_at').text(d.user.last_won_at ?
                            new Date(d.user.last_won_at).toLocaleString() : 'Never');
                        $('#viewDonorModal').modal('show');
                    }
                });
            });
        });
    </script>


    // Copy to clipboard
    <script>
        new ClipboardJS('.copy-btn');
        $(document).on('click', '.copy-btn', function() {
            toastr.success('Copied to clipboard!');
        });
    </script>
@endpush
