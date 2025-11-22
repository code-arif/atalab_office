@extends('backend.app')

@section('title', 'Winner Verification')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">

                <!-- Page Header -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">
                            <i class="fe fe-shield me-2"></i>Winner Verification Process
                        </h1>
                        <p class="text-muted">Verify winner eligibility and claim approval</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <a href="{{ route('draw-winners.index') }}" class="btn btn-secondary">
                            <i class="fe fe-arrow-left me-2"></i>Back to Winners
                        </a>
                    </div>
                </div>

                <!-- Winner Info Card -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fe fe-user me-2"></i>Winner Information</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <p class="mb-2"><strong>Name:</strong></p>
                                        <h5 class="text-primary">{{ $winner->user->name }}</h5>
                                    </div>
                                    <div class="col-md-3">
                                        <p class="mb-2"><strong>Email:</strong></p>
                                        <p class="mb-0">{{ $winner->user->email }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <p class="mb-2"><strong>Phone:</strong></p>
                                        <p class="mb-0">{{ $winner->user->phone ?? 'N/A' }}</p>
                                    </div>
                                    <div class="col-md-3">
                                        <p class="mb-2"><strong>Amount Won:</strong></p>
                                        <h5 class="text-success">${{ number_format($winner->amount_won, 2) }}</h5>
                                    </div>
                                </div>
                                <div class="row mt-3">
                                    <div class="col-md-3">
                                        <p class="mb-2"><strong>Week:</strong></p>
                                        <span class="badge bg-info">Week #{{ $winner->weeklyDraw->week_number }}</span>
                                    </div>
                                    <div class="col-md-3">
                                        <p class="mb-2"><strong>Status:</strong></p>
                                        @if ($winner->verification)
                                            @php
                                                $statusColors = [
                                                    'pending' => 'secondary',
                                                    'identity_review' => 'info',
                                                    'contact_verification' => 'info',
                                                    'bank_verification' => 'info',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                ];
                                                $color =
                                                    $statusColors[$winner->verification->verification_status] ??
                                                    'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $color }}">
                                                {{ ucwords(str_replace('_', ' ', $winner->verification->verification_status)) }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Not Started</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="mb-3">Verification Progress</h6>
                                <div class="progress" style="height: 30px;">
                                    <div id="mainProgressBar"
                                        class="progress-bar progress-bar-striped progress-bar-animated bg-success"
                                        role="progressbar"
                                        style="width: {{ $winner->verification ? $winner->verification->getVerificationProgress() : 0 }}%">
                                        <strong>{{ $winner->verification ? round($winner->verification->getVerificationProgress()) : 0 }}%</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Multi-Step Form -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <!-- Step Indicators -->
                                <div class="bs-stepper">
                                    <div class="bs-stepper-header mb-4" role="tablist">
                                        <!-- Step 1 -->
                                        <div class="step" data-target="#step1">
                                            <button type="button" class="step-trigger" role="tab" id="stepTrigger1">
                                                <span class="bs-stepper-circle" id="circle1">1</span>
                                                <span class="bs-stepper-label">Identity Verification</span>
                                            </button>
                                        </div>
                                        <div class="line"></div>

                                        <!-- Step 2 -->
                                        <div class="step" data-target="#step2">
                                            <button type="button" class="step-trigger" role="tab" id="stepTrigger2">
                                                <span class="bs-stepper-circle" id="circle2">2</span>
                                                <span class="bs-stepper-label">Contact Verification</span>
                                            </button>
                                        </div>
                                        <div class="line"></div>

                                        <!-- Step 3 -->
                                        <div class="step" data-target="#step3">
                                            <button type="button" class="step-trigger" role="tab" id="stepTrigger3">
                                                <span class="bs-stepper-circle" id="circle3">3</span>
                                                <span class="bs-stepper-label">Bank Verification</span>
                                            </button>
                                        </div>
                                        <div class="line"></div>

                                        <!-- Step 4 -->
                                        <div class="step" data-target="#step4">
                                            <button type="button" class="step-trigger" role="tab" id="stepTrigger4">
                                                <span class="bs-stepper-circle" id="circle4">4</span>
                                                <span class="bs-stepper-label">Final Approval</span>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="bs-stepper-content">
                                        <!-- Step 1: Identity Verification -->
                                        <div id="step1" class="content" role="tabpanel">
                                            <div class="card border">
                                                <div class="card-header bg-light">
                                                    <h5><i class="fe fe-user me-2"></i>Step 1: Identity Verification</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="alert alert-info">
                                                        <i class="fe fe-info me-2"></i>
                                                        <strong>Required:</strong> Valid U.S. driver's license information
                                                    </div>

                                                    <form id="identityForm">
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Driver's License Number <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="text" class="form-control"
                                                                    name="drivers_license"
                                                                    value="{{ $winner->verification->drivers_license ?? '' }}"
                                                                    required>
                                                            </div>
                                                            <div class="col-md-3 mb-3">
                                                                <label class="form-label">State <span
                                                                        class="text-danger">*</span></label>
                                                                <select class="form-control" name="license_state"
                                                                    required>
                                                                    <option value="">Select State</option>
                                                                    @php
                                                                        $states = [
                                                                            'AL' => 'Alabama',
                                                                            'AK' => 'Alaska',
                                                                            'AZ' => 'Arizona',
                                                                            'AR' => 'Arkansas',
                                                                            'CA' => 'California',
                                                                            'CO' => 'Colorado',
                                                                            'CT' => 'Connecticut',
                                                                            'DE' => 'Delaware',
                                                                            'FL' => 'Florida',
                                                                            'GA' => 'Georgia',
                                                                            'HI' => 'Hawaii',
                                                                            'ID' => 'Idaho',
                                                                            'IL' => 'Illinois',
                                                                            'IN' => 'Indiana',
                                                                            'IA' => 'Iowa',
                                                                            'KS' => 'Kansas',
                                                                            'KY' => 'Kentucky',
                                                                            'LA' => 'Louisiana',
                                                                            'ME' => 'Maine',
                                                                            'MD' => 'Maryland',
                                                                            'MA' => 'Massachusetts',
                                                                            'MI' => 'Michigan',
                                                                            'MN' => 'Minnesota',
                                                                            'MS' => 'Mississippi',
                                                                            'MO' => 'Missouri',
                                                                            'MT' => 'Montana',
                                                                            'NE' => 'Nebraska',
                                                                            'NV' => 'Nevada',
                                                                            'NH' => 'New Hampshire',
                                                                            'NJ' => 'New Jersey',
                                                                            'NM' => 'New Mexico',
                                                                            'NY' => 'New York',
                                                                            'NC' => 'North Carolina',
                                                                            'ND' => 'North Dakota',
                                                                            'OH' => 'Ohio',
                                                                            'OK' => 'Oklahoma',
                                                                            'OR' => 'Oregon',
                                                                            'PA' => 'Pennsylvania',
                                                                            'RI' => 'Rhode Island',
                                                                            'SC' => 'South Carolina',
                                                                            'SD' => 'South Dakota',
                                                                            'TN' => 'Tennessee',
                                                                            'TX' => 'Texas',
                                                                            'UT' => 'Utah',
                                                                            'VT' => 'Vermont',
                                                                            'VA' => 'Virginia',
                                                                            'WA' => 'Washington',
                                                                            'WV' => 'West Virginia',
                                                                            'WI' => 'Wisconsin',
                                                                            'WY' => 'Wyoming',
                                                                        ];
                                                                    @endphp
                                                                    @foreach ($states as $code => $name)
                                                                        <option value="{{ $code }}"
                                                                            {{ ($winner->verification->license_state ?? '') == $code ? 'selected' : '' }}>
                                                                            {{ $name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="col-md-3 mb-3">
                                                                <label class="form-label">Expiry Date <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="date" class="form-control"
                                                                    name="license_expiry"
                                                                    value="{{ $winner->verification->license_expiry ?? '' }}"
                                                                    required>
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label">Admin Notes</label>
                                                            <textarea class="form-control" name="admin_notes" rows="3">{{ $winner->verification->admin_notes ?? '' }}</textarea>
                                                        </div>

                                                        <div class="form-check mb-3">
                                                            <input type="checkbox" class="form-check-input"
                                                                id="identityVerified" name="identity_verified"
                                                                value="1"
                                                                {{ $winner->verification && $winner->verification->identity_verified ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-bold"
                                                                for="identityVerified">
                                                                I confirm that I have verified this information is accurate
                                                                and matches government-issued ID
                                                            </label>
                                                        </div>

                                                        <button type="submit" class="btn btn-primary">
                                                            <i class="fe fe-check me-2"></i>Verify & Continue
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Step 2: Contact Verification -->
                                        <div id="step2" class="content" role="tabpanel">
                                            <div class="card border">
                                                <div class="card-header bg-light">
                                                    <h5><i class="fe fe-mail me-2"></i>Step 2: Contact Verification</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="alert alert-info">
                                                        <i class="fe fe-info me-2"></i>
                                                        Email and phone verification status from user registration
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="card border">
                                                                <div class="card-body text-center">
                                                                    <i class="fe fe-mail fs-1 text-primary mb-3"></i>
                                                                    <h6>Email Address</h6>
                                                                    <p class="mb-2">{{ $winner->user->email }}</p>
                                                                    <span class="badge bg-success">Verified</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="card border">
                                                                <div class="card-body text-center">
                                                                    <i class="fe fe-phone fs-1 text-success mb-3"></i>
                                                                    <h6>Phone Number</h6>
                                                                    <p class="mb-2">
                                                                        {{ $winner->user->phone ?? 'Not provided' }}</p>
                                                                    <span class="badge bg-success">Verified</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="text-center mt-4">
                                                        <button type="button" class="btn btn-secondary me-2"
                                                            onclick="previousStep()">
                                                            <i class="fe fe-arrow-left me-2"></i>Previous
                                                        </button>
                                                        <button type="button" class="btn btn-success"
                                                            onclick="verifyContact()">
                                                            <i class="fe fe-check me-2"></i>Confirm & Continue
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Step 3: Bank Verification -->
                                        <div id="step3" class="content" role="tabpanel">
                                            <div class="card border">
                                                <div class="card-header bg-light">
                                                    <h5><i class="fe fe-credit-card me-2"></i>Step 3: Bank Account
                                                        Verification</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="alert alert-warning">
                                                        <i class="fe fe-alert-triangle me-2"></i>
                                                        <strong>Important:</strong> Bank account must be a valid U.S. bank
                                                        account
                                                    </div>

                                                    <form id="bankForm">
                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Bank Name <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="text" class="form-control"
                                                                    name="bank_name"
                                                                    value="{{ $winner->verification->bank_name ?? '' }}"
                                                                    required>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Account Holder Name <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="text" class="form-control"
                                                                    name="account_holder_name"
                                                                    value="{{ $winner->verification->account_holder_name ?? '' }}"
                                                                    required>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Last 4 Digits of Account <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="text" class="form-control"
                                                                    name="account_number_last4" maxlength="4"
                                                                    value="{{ $winner->verification->account_number_last4 ?? '' }}"
                                                                    required>
                                                                <small class="text-muted">Only last 4 digits for
                                                                    security</small>
                                                            </div>
                                                            <div class="col-md-6 mb-3">
                                                                <label class="form-label">Routing Number <span
                                                                        class="text-danger">*</span></label>
                                                                <input type="text" class="form-control"
                                                                    name="routing_number" maxlength="9"
                                                                    value="{{ $winner->verification->routing_number ?? '' }}"
                                                                    required>
                                                                <small class="text-muted">9-digit routing number</small>
                                                            </div>
                                                        </div>

                                                        <div class="form-check mb-3">
                                                            <input type="checkbox" class="form-check-input"
                                                                id="bankVerified" name="bank_verified" value="1"
                                                                {{ $winner->verification && $winner->verification->bank_verified ? 'checked' : '' }}>
                                                            <label class="form-check-label fw-bold" for="bankVerified">
                                                                I confirm that I have verified this bank account belongs to
                                                                the winner
                                                            </label>
                                                        </div>

                                                        <div class="text-center mt-4">
                                                            <button type="button" class="btn btn-secondary me-2"
                                                                onclick="previousStep()">
                                                                <i class="fe fe-arrow-left me-2"></i>Previous
                                                            </button>
                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="fe fe-check me-2"></i>Verify & Continue
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Step 4: Final Approval -->
                                        <div id="step4" class="content" role="tabpanel">
                                            <div class="card border border-success">
                                                <div class="card-header bg-success text-white">
                                                    <h5 class="mb-0"><i class="fe fe-award me-2"></i>Step 4: Final
                                                        Approval</h5>
                                                </div>
                                                <div class="card-body">
                                                    <div class="alert alert-success">
                                                        <i class="fe fe-check-circle me-2"></i>
                                                        All verification steps completed! Review and approve the claim.
                                                    </div>

                                                    <div class="card border-success mb-4">
                                                        <div class="card-header">
                                                            <h6 class="mb-0">Verification Summary</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <div class="row">
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <i
                                                                            class="fe fe-check-circle text-success me-2"></i>
                                                                        <strong>Identity Verification:</strong>
                                                                        <span
                                                                            class="badge bg-success ms-2">Completed</span>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <i
                                                                            class="fe fe-check-circle text-success me-2"></i>
                                                                        <strong>Contact Verification:</strong>
                                                                        <span
                                                                            class="badge bg-success ms-2">Completed</span>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="mb-3">
                                                                        <i
                                                                            class="fe fe-check-circle text-success me-2"></i>
                                                                        <strong>Bank Verification:</strong>
                                                                        <span
                                                                            class="badge bg-success ms-2">Completed</span>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <i
                                                                            class="fe fe-check-circle text-success me-2"></i>
                                                                        <strong>Eligibility:</strong>
                                                                        <span
                                                                            class="badge bg-success ms-2">Confirmed</span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="mb-4">
                                                        <label class="form-label fw-bold">Final Admin Notes</label>
                                                        <textarea id="finalNotes" class="form-control" rows="3"
                                                            placeholder="Add any final notes about this verification..."></textarea>
                                                    </div>

                                                    <div class="text-center">
                                                        <button type="button" class="btn btn-secondary me-2"
                                                            onclick="previousStep()">
                                                            <i class="fe fe-arrow-left me-2"></i>Previous
                                                        </button>
                                                        <button type="button" class="btn btn-success me-2"
                                                            onclick="approveClaimFinal()">
                                                            <i class="fe fe-check-circle me-2"></i>Approve Claim
                                                        </button>
                                                        <button type="button" class="btn btn-danger"
                                                            onclick="rejectClaimFinal()">
                                                            <i class="fe fe-x-circle me-2"></i>Reject Claim
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .bs-stepper-header {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .step {
            display: flex;
            align-items: center;
        }

        .step-trigger {
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 10px;
            background: none;
            border: none;
            cursor: pointer;
        }

        .bs-stepper-circle {
            width: 45px;
            height: 45px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #e9ecef;
            border-radius: 50%;
            font-weight: bold;
            font-size: 18px;
            color: #6c757d;
            margin-bottom: 8px;
            transition: all 0.3s;
        }

        .step.active .bs-stepper-circle {
            background: #0d6efd;
            color: white;
        }

        .step.completed .bs-stepper-circle {
            background: #28a745;
            color: white;
        }

        .bs-stepper-label {
            font-size: 13px;
            font-weight: 600;
            color: #6c757d;
        }

        .step.active .bs-stepper-label {
            color: #0d6efd;
        }

        .step.completed .bs-stepper-label {
            color: #28a745;
        }

        .line {
            width: 80px;
            height: 2px;
            background: #e9ecef;
            margin: 0 10px;
        }

        .step.completed~.line {
            background: #28a745;
        }

        .content {
            display: none;
        }

        .content.active {
            display: block;
        }
    </style>
@endpush

@push('scripts')
    <script>
        let currentStep = 1;
        const winnerId = {{ $winner->id }};

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Load current verification status
            loadVerificationStatus();

            // Show first step
            showStep(currentStep);
        });

        function showStep(step) {
            // Hide all steps
            $('.content').removeClass('active');
            $('.step').removeClass('active');

            // Show current step
            $('#step' + step).addClass('active');
            $('.step').eq(step - 1).addClass('active');

            // Mark completed steps
            for (let i = 1; i < step; i++) {
                $('.step').eq(i - 1).addClass('completed');
            }

            currentStep = step;
        }

        function nextStep() {
            if (currentStep < 4) {
                showStep(currentStep + 1);
            }
        }

        function previousStep() {
            if (currentStep > 1) {
                showStep(currentStep - 1);
            }
        }

        function loadVerificationStatus() {
            $.get("{{ route('draw-winners.verification-status', $winner->id) }}", function(res) {
                if (res.success && res.verification) {
                    let v = res.verification;

                    // Determine which step to show
                    if (v.verification_status === 'approved') {
                        showStep(4);
                    } else if (v.bank_verified) {
                        showStep(4);
                    } else if (v.email_verified && v.phone_verified) {
                        showStep(3);
                    } else if (v.identity_verified) {
                        showStep(2);
                    }

                    // Update progress bar
                    $('#mainProgressBar').css('width', res.progress + '%').find('strong').text(res.progress +
                        '%');
                }
            });
        }

        /* // Identity Form Submit */
        $('#identityForm').on('submit', function(e) {
            e.preventDefault();

            if (!$('#identityVerified').is(':checked')) {
                toastr.error('Please confirm identity verification');
                return;
            }

            let formData = {
                drivers_license: $('[name="drivers_license"]').val(),
                license_state: $('[name="license_state"]').val(),
                license_expiry: $('[name="license_expiry"]').val(),
                admin_notes: $('[name="admin_notes"]').val()
            };

            $.ajax({
                url: "{{ route('draw-winners.verify-identity', $winner->id) }}",
                type: 'POST',
                data: formData,
                success: function(res) {
                    if (res.success) {
                        toastr.success(res.message);
                        loadVerificationStatus();
                        nextStep();
                    }
                },
                error: function(xhr) {
                    toastr.error('Verification failed');
                    console.error(xhr.responseJSON);
                }
            });
        });

        /* // Contact Verification */
        function verifyContact() {
            $.ajax({
                url: "{{ route('draw-winners.verify-contact', $winner->id) }}",
                type: 'POST',
                success: function(res) {
                    if (res.success) {
                        toastr.success(res.message);
                        loadVerificationStatus();
                        nextStep();
                    }
                },
                error: function(xhr) {
                    toastr.error('Verification failed');
                }
            });
        }

        /* // Bank Form Submit */
        $('#bankForm').on('submit', function(e) {
            e.preventDefault();

            if (!$('#bankVerified').is(':checked')) {
                toastr.error('Please confirm bank account verification');
                return;
            }

            let formData = {
                bank_name: $('[name="bank_name"]').val(),
                account_holder_name: $('[name="account_holder_name"]').val(),
                account_number_last4: $('[name="account_number_last4"]').val(),
                routing_number: $('[name="routing_number"]').val()
            };

            $.ajax({
                url: "{{ route('draw-winners.verify-bank', $winner->id) }}",
                type: 'POST',
                data: formData,
                success: function(res) {
                    if (res.success) {
                        toastr.success(res.message);
                        loadVerificationStatus();
                        nextStep();
                    }
                },
                error: function(xhr) {
                    toastr.error('Verification failed');
                    console.error(xhr.responseJSON);
                }
            });
        });

        /* // Approve Claim */
        function approveClaimFinal() {
            let notes = $('#finalNotes').val();

            Swal.fire({
                title: 'Approve Claim?',
                text: 'This will approve the winner claim and allow payout processing.',
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'Yes, Approve!',
                confirmButtonColor: '#28a745'
            }).then(result => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('draw-winners.approve-claim', $winner->id) }}",
                        type: 'POST',
                        data: {
                            admin_notes: notes
                        },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Approved!',
                                    text: res.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                setTimeout(() => {
                                    window.location.href = res.redirect;
                                }, 2000);
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to approve claim',
                                'error');
                        }
                    });
                }
            });
        }

        /* // Reject Claim */
        function rejectClaimFinal() {
            Swal.fire({
                title: 'Reject Claim?',
                input: 'textarea',
                inputLabel: 'Rejection Reason',
                inputPlaceholder: 'Provide detailed reason for rejection (minimum 10 characters)...',
                inputAttributes: {
                    'aria-label': 'Rejection reason',
                    'rows': 4
                },
                showCancelButton: true,
                confirmButtonText: 'Reject',
                confirmButtonColor: '#dc3545',
                inputValidator: (value) => {
                    if (!value || value.length < 10) {
                        return 'Please provide a detailed reason (minimum 10 characters)';
                    }
                }
            }).then(result => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('draw-winners.reject-claim', $winner->id) }}",
                        type: 'POST',
                        data: {
                            rejection_reason: result.value
                        },
                        success: function(res) {
                            if (res.success) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Rejected!',
                                    text: res.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                                setTimeout(() => {
                                    window.location.href = res.redirect;
                                }, 2000);
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', xhr.responseJSON?.message || 'Failed to reject claim',
                                'error');
                        }
                    });
                }
            });
        }
    </script>
@endpush
