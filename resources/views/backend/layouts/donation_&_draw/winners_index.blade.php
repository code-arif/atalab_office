@extends('backend.app')

@section('title', 'Draw Winners Management')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <div class="page-header">
                    <div>
                        <h1 class="page-title">Draw Winners Management</h1>
                        <p class="text-muted">Manage winner verification and claim process</p>
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
                                            <h4 class="tx-20 fw-bold mb-1 text-white">{{ $stats['total_winners'] }}</h4>
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
                                <h6 class="mb-3 tx-12 text-white">CLAIMED</h6>
                                <div class="pb-0 mt-0">
                                    <div class="d-flex">
                                        <div>
                                            <h4 class="tx-20 fw-bold mb-1 text-white">{{ $stats['claimed'] }}</h4>
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
                                <h6 class="mb-3 tx-12 text-white">PENDING VERIFICATION</h6>
                                <div class="pb-0 mt-0">
                                    <div class="d-flex">
                                        <div>
                                            <h4 class="tx-20 fw-bold mb-1 text-white">{{ $stats['pending_verification'] }}
                                            </h4>
                                            <p class="mb-0 tx-12 text-white op-7">Needs Attention</p>
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

                <!-- Main Table -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">All Draw Winners</h3>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0" id="winnersTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Winner Name</th>
                                                <th>Email</th>
                                                <th>Week</th>
                                                <th>Amount Won</th>
                                                <th>Claim Status</th>
                                                <th>Verification</th>
                                                <th>Progress</th>
                                                <th>Payout Status</th>
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

    <!-- View Winner Modal -->
    @include('backend.layouts.donation_&_draw.view_winner_modal')

    <!-- Verification Modal -->
    @include('backend.layouts.donation_&_draw.verification_modal')

@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let dTable = $('#winnersTable').DataTable({
                order: [
                    [0, 'desc']
                ],
                lengthMenu: [
                    [20, 50, 100],
                    [20, 50, 100]
                ],
                processing: true,
                responsive: true,
                serverSide: true,
                ajax: "{{ route('draw-winners.index') }}",
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
                        data: 'verification_status'
                    },
                    {
                        data: 'progress'
                    },
                    {
                        data: 'payout_status'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // View Winner
            $(document).on('click', '.viewWinner', function() {
                let id = $(this).data('id');
                loadWinnerDetails(id);
            });

            // Verify Winner
            $(document).on('click', '.verifyWinner', function() {
                let id = $(this).data('id');
                openVerificationModal(id);
            });
        });

        function loadWinnerDetails(id) {
            $.get("{{ route('draw-winners.show', '') }}/" + id, function(res) {
                if (res.success) {
                    populateViewModal(res.data);
                    $('#viewWinnerModal').modal('show');
                }
            });
        }

        function openVerificationModal(id) {
            $('#winnerId').val(id);
            $('#verificationModal').modal('show');
            loadVerificationStatus(id);
        }

        function loadVerificationStatus(id) {
            $.get("{{ route('draw-winners.verification-status', '') }}/" + id, function(res) {
                if (res.success) {
                    updateVerificationUI(res.data);
                }
            });
        }
    </script>
@endpush
