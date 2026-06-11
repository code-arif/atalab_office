@extends('backend.app')

@section('title', 'Manual Finalize - Week #' . $draw->week_number)

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Manual Finalization</h1>
                        <p class="text-muted">
                            Week #{{ $draw->week_number }} &middot; Year {{ $draw->year }} &middot;
                            <span class="badge p-3 badge-status status-active">Active</span>
                        </p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <a href="{{ route('weekly-draws.index') }}"
                            class="btn btn-secondary btn-sm d-inline-flex align-items-center">
                            <i class="fe fe-arrow-left me-1"></i>
                            Back to Draws
                        </a>
                    </div>
                </div>

                <!-- Warning Alert -->
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fe fe-alert-triangle me-2"></i>
                    <strong>Accidental/emergency use only.</strong> The draw system is fully automated. Use this only when
                    the automated process fails.
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>

                <!-- Summary Row -->
                <div class="row mb-4">
                    <div class="col-md-9">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Draw Summary</h3>
                            </div>
                            <div class="card-body">
                                <div class="row g-4">
                                    <div class="col-sm-6 col-md-3">
                                        <span class="detail-label">Total Pool</span>
                                        <span class="detail-value text-success">${{ number_format($totalPool, 2) }}</span>
                                    </div>
                                    <div class="col-sm-6 col-md-3">
                                        <span class="detail-label">Participants</span>
                                        <span class="detail-value">{{ number_format($totalParticipants) }}</span>
                                    </div>
                                    <div class="col-sm-6 col-md-3">
                                        <span class="detail-label">Expected Winners</span>
                                        <span class="detail-value">{{ $expectedWinners }} <small
                                                class="text-muted">({{ $settings->odds_ratio }}:1)</small></span>
                                    </div>
                                    <div class="col-sm-6 col-md-3">
                                        <span class="detail-label">Admin Commission</span>
                                        <span class="detail-value">${{ number_format($adminCommission, 2) }}</span>
                                    </div>
                                </div>
                                <hr class="my-3">
                                <div class="row g-4">
                                    <div class="col-sm-6 col-md-3">
                                        <span class="detail-label">Start Date</span>
                                        <span
                                            class="detail-value">{{ \Carbon\Carbon::parse($draw->start_date)->format('M d, Y h:i A') }}</span>
                                    </div>
                                    <div class="col-sm-6 col-md-3">
                                        <span class="detail-label">End Date</span>
                                        <span
                                            class="detail-value">{{ \Carbon\Carbon::parse($draw->end_date)->format('M d, Y h:i A') }}</span>
                                    </div>
                                    <div class="col-sm-6 col-md-3">
                                        <span class="detail-label">Countdown Ends</span>
                                        <span
                                            class="detail-value">{{ \Carbon\Carbon::parse($draw->countdown_ends_at)->format('M d, Y h:i A') }}</span>
                                    </div>
                                    <div class="col-sm-6 col-md-3">
                                        <span class="detail-label text-danger">Claim Deadline</span>
                                        <span
                                            class="detail-value text-danger fw-bold">{{ \Carbon\Carbon::parse($draw->claim_deadline)->format('M d, Y h:i A') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100">
                            <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                                <div class="mb-3">
                                    <i class="fe fe-zap text-danger" style="font-size: 2rem;"></i>
                                </div>
                                <p class="text-muted mb-3" style="font-size: 13px;">
                                    Manually finalize this draw and select winners.
                                </p>
                                <button type="button" id="manualFinalizeBtn" class="btn btn-danger fw-semibold"
                                    onclick="confirmManualFinalize()">
                                    <i class="fe fe-zap me-1"></i> Finalize & Select Winners
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Current Week Donations -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">
                                    Donations — Week #{{ $draw->week_number }}
                                    <span class="text-muted fw-normal fs-13">({{ $donations->count() }})</span>
                                </h3>
                                <span class="text-success fw-bold">
                                    ${{ number_format($donations->sum('amount'), 2) }}
                                </span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th class="ps-3">#</th>
                                                <th>Donor</th>
                                                <th>Email</th>
                                                <th>Amount</th>
                                                <th>Status</th>
                                                <th>Eligible</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($donations as $index => $donation)
                                                <tr>
                                                    <td class="ps-3">{{ $index + 1 }}</td>
                                                    <td class="fw-medium">{{ $donation->user->name ?? 'N/A' }}</td>
                                                    <td class="text-muted">{{ $donation->user->email ?? 'N/A' }}</td>
                                                    <td class="fw-medium">${{ number_format($donation->amount, 2) }}</td>
                                                    <td>
                                                        @if ($donation->stripe_payment_status === 'completed')
                                                            <span
                                                                class="badge bg-success-transparent text-success">{{ ucfirst($donation->stripe_payment_status) }}</span>
                                                        @else
                                                            <span
                                                                class="badge bg-warning-transparent text-warning">{{ ucfirst($donation->stripe_payment_status) }}</span>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if ($donation->is_eligible_for_draw)
                                                            <i class="fe fe-check-circle text-success"></i>
                                                        @else
                                                            <i class="fe fe-x-circle text-muted"></i>
                                                        @endif
                                                    </td>
                                                    <td class="text-muted">
                                                        {{ \Carbon\Carbon::parse($donation->created_at)->format('M d, Y h:i A') }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-4 text-muted">
                                                        No completed donations found for this week.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cycle Donations -->
                @if ($cycleDonations->count() > 0 && $donations->count() !== $cycleDonations->count())
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h3 class="card-title mb-0">
                                        All Cycle Donations
                                        <span class="text-muted fw-normal fs-13">({{ $cycleDonations->count() }})</span>
                                    </h3>
                                    <span class="text-success fw-bold">
                                        ${{ number_format($cycleDonations->sum('amount'), 2) }}
                                    </span>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                        <table class="table table-hover mb-0">
                                            <thead style="position: sticky; top: 0; z-index: 1;">
                                                <tr>
                                                    <th class="ps-3">#</th>
                                                    <th>Donor</th>
                                                    <th>Email</th>
                                                    <th>Amount</th>
                                                    <th>Week</th>
                                                    <th>Status</th>
                                                    <th>Date</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($cycleDonations as $index => $donation)
                                                    <tr>
                                                        <td class="ps-3">{{ $index + 1 }}</td>
                                                        <td class="fw-medium">{{ $donation->user->name ?? 'N/A' }}</td>
                                                        <td class="text-muted">{{ $donation->user->email ?? 'N/A' }}</td>
                                                        <td class="fw-medium">${{ number_format($donation->amount, 2) }}
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-light text-dark">Week
                                                                #{{ $donation->weeklyDraw->week_number ?? 'N/A' }}</span>
                                                        </td>
                                                        <td>
                                                            @if ($donation->stripe_payment_status === 'completed')
                                                                <span
                                                                    class="badge bg-success-transparent text-success">{{ ucfirst($donation->stripe_payment_status) }}</span>
                                                            @else
                                                                <span
                                                                    class="badge bg-warning-transparent text-warning">{{ ucfirst($donation->stripe_payment_status) }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-muted">
                                                            {{ \Carbon\Carbon::parse($donation->created_at)->format('M d, Y h:i A') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmFinalizeModal" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        <i class="fe fe-alert-triangle me-2 text-warning"></i>Confirm Finalization
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">
                        This will finalize the draw and <strong>randomly select winners</strong>. This action cannot be
                        undone.
                    </p>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <div class="bg-light rounded-1 p-2 text-center">
                                <small class="text-muted d-block" style="font-size: 11px;">Pool</small>
                                <strong>${{ number_format($totalPool, 2) }}</strong>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="bg-light rounded-1 p-2 text-center">
                                <small class="text-muted d-block" style="font-size: 11px;">Participants</small>
                                <strong>{{ number_format($totalParticipants) }}</strong>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="bg-light rounded-1 p-2 text-center">
                                <small class="text-muted d-block" style="font-size: 11px;">Expected Winners</small>
                                <strong>{{ $expectedWinners }}</strong>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <div class="bg-light rounded-1 p-2 text-center">
                                <small class="text-muted d-block" style="font-size: 11px;">Commission</small>
                                <strong>${{ number_format($adminCommission, 2) }}</strong>
                            </div>
                        </div>
                    </div>

                    <ul class="text-muted" style="font-size: 13px;">
                        <li>Draw status will change to <strong>Completed</strong></li>
                        <li>Winners will be randomly selected from eligible donors</li>
                        <li>Winner exclusion records created ({{ $settings->winner_exclusion_months }} months)</li>
                        <li>Draw cycle will be closed</li>
                    </ul>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger fw-semibold" onclick="executeManualFinalize()">
                        <i class="fe fe-zap me-1"></i>Finalize Now
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="finalizingOverlay" style="display: none;">
        <div class="finalizing-overlay-backdrop"></div>
        <div class="finalizing-overlay-content">
            <div class="spinner-border text-light mb-3" style="width: 3rem; height: 3rem;" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <h4 class="text-white mb-1">Finalizing Draw</h4>
            <p class="text-white-50 mb-3" id="finalizingStatus" style="font-size: 14px;">Initializing...</p>
            <div class="progress" style="width: 280px; height: 4px; border-radius: 4px;">
                <div class="progress-bar" id="finalizingProgress" style="width: 0%; background-color: #fff;"></div>
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <style>
        .detail-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #8c919a;
            font-weight: 600;
            display: block;
            margin-bottom: 2px;
        }

        .detail-value {
            font-size: 15px;
            color: #1f2937;
            font-weight: 600;
            display: block;
        }

        .badge-status {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        .status-active {
            background: #d1fae5;
            color: #065f46;
        }

        .bg-success-transparent {
            background-color: #d1fae5;
        }

        .bg-warning-transparent {
            background-color: #fef3c7;
        }

        /* Loading Overlay */
        #finalizingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 99999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .finalizing-overlay-backdrop {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(6px);
        }

        .finalizing-overlay-content {
            position: relative;
            z-index: 1;
            text-align: center;
        }
    </style>
@endpush

@push('scripts')
    <script>
        let isFinalizing = false;

        function confirmManualFinalize() {
            if (isFinalizing) return;
            $('#confirmFinalizeModal').modal('show');
        }

        function executeManualFinalize() {
            if (isFinalizing) return;
            isFinalizing = true;

            $('#confirmFinalizeModal').modal('hide');
            showFinalizingOverlay();

            const drawId = {{ $draw->id }};
            let progress = 0;
            const progressInterval = setInterval(function() {
                progress += Math.random() * 15;
                if (progress > 90) progress = 90;
                $('#finalizingProgress').css('width', progress + '%');
            }, 500);

            const statusMessages = [
                'Initializing...',
                'Calculating donation pool...',
                'Verifying participants...',
                'Applying winner exclusions...',
                'Selecting winners...',
                'Finalizing...',
                'Almost done...'
            ];
            let msgIndex = 0;
            const statusInterval = setInterval(function() {
                msgIndex++;
                if (msgIndex < statusMessages.length) {
                    $('#finalizingStatus').text(statusMessages[msgIndex]);
                }
            }, 1200);

            $.ajax({
                url: "{{ url('admin/manual-finalize') }}/" + drawId + "/finalize",
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(res) {
                    clearInterval(progressInterval);
                    clearInterval(statusInterval);

                    if (res.success) {
                        $('#finalizingProgress').css('width', '100%');
                        $('#finalizingStatus').text('Complete!');

                        setTimeout(function() {
                            hideFinalizingOverlay();

                            if (res.rollover) {
                                Swal.fire({
                                    title: 'Draw Rolled Over',
                                    text: res.message,
                                    icon: 'info',
                                    confirmButtonText: 'Back to Draws'
                                }).then(() => {
                                    window.location.href = "{{ route('weekly-draws.index') }}";
                                });
                            } else {
                                Swal.fire({
                                    title: 'Done',
                                    html: '<p class="mb-2">' + res.message + '</p>' +
                                        '<hr>' +
                                        '<p class="mb-1"><strong>Winners:</strong> ' + res.data
                                        .recipients + '</p>' +
                                        '<p class="mb-1"><strong>Distributed:</strong> $' + res
                                        .data.total_distributed + '</p>' +
                                        '<p class="mb-0"><strong>Commission:</strong> $' + res
                                        .data.admin_commission + '</p>',
                                    icon: 'success',
                                    confirmButtonText: 'Back to Draws'
                                }).then(() => {
                                    window.location.href = "{{ route('weekly-draws.index') }}";
                                });
                            }
                        }, 800);
                    } else {
                        hideFinalizingOverlay();
                        Swal.fire({
                            title: 'Failed',
                            text: res.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                        isFinalizing = false;
                    }
                },
                error: function(xhr) {
                    clearInterval(progressInterval);
                    clearInterval(statusInterval);
                    hideFinalizingOverlay();

                    let errMsg = 'An error occurred during finalization.';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errMsg = xhr.responseJSON.message;
                    }
                    Swal.fire({
                        title: 'Error',
                        text: errMsg,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                    isFinalizing = false;
                }
            });
        }

        function showFinalizingOverlay() {
            $('#finalizingProgress').css('width', '0%');
            $('#finalizingStatus').text('Initializing...');
            $('#finalizingOverlay').fadeIn(200);
        }

        function hideFinalizingOverlay() {
            $('#finalizingOverlay').fadeOut(200);
        }
    </script>
@endpush
