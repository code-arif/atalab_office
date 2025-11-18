<!-- Verification Modal -->
<div class="modal fade" id="verificationModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fe fe-shield me-2"></i>Winner Verification Process</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="winnerId">

                <!-- Verification Progress -->
                <div class="card mb-4 border-primary">
                    <div class="card-header bg-primary text-white">
                        <h6 class="mb-0"><i class="fe fe-activity me-2"></i>Verification Progress</h6>
                    </div>
                    <div class="card-body">
                        <div class="progress" style="height: 30px;">
                            <div id="verificationProgressBar" class="progress-bar bg-success" role="progressbar"
                                style="width: 0%">
                                <span class="fw-bold">0%</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Verification Steps -->
                <div class="accordion" id="verificationAccordion">

                    <!-- Step 1: Identity Verification -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingIdentity">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseIdentity">
                                <i class="fe fe-user me-2"></i>
                                <span class="fw-bold">Step 1: Identity Verification</span>
                                <span id="identityStatus" class="badge bg-secondary ms-2">Not Started</span>
                            </button>
                        </h2>
                        <div id="collapseIdentity" class="accordion-collapse collapse show"
                            data-bs-parent="#verificationAccordion">
                            <div class="accordion-body">
                                <form id="identityForm">
                                    <div class="alert alert-info">
                                        <i class="fe fe-info me-2"></i>
                                        <strong>Required:</strong> Valid U.S. driver's license information
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Driver's License Number <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="drivers_license" required>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">State <span class="text-danger">*</span></label>
                                            <select class="form-control" name="license_state" required>
                                                <option value="">Select State</option>
                                                <option value="AL">Alabama</option>
                                                <option value="AK">Alaska</option>
                                                <option value="AZ">Arizona</option>
                                                <option value="CA">California</option>
                                                <option value="FL">Florida</option>
                                                <option value="NY">New York</option>
                                                <option value="TX">Texas</option>
                                                <!-- Add more states -->
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label class="form-label">Expiry Date <span
                                                    class="text-danger">*</span></label>
                                            <input type="date" class="form-control" name="license_expiry" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Admin Notes</label>
                                        <textarea class="form-control" name="admin_notes" rows="3"></textarea>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="identityVerified"
                                            name="identity_verified" value="1">
                                        <label class="form-check-label" for="identityVerified">
                                            <strong>I confirm that I have verified this information is accurate and
                                                matches government-issued ID</strong>
                                        </label>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fe fe-check me-2"></i>Verify Identity
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Step 2: Contact Verification -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingContact">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseContact">
                                <i class="fe fe-mail me-2"></i>
                                <span class="fw-bold">Step 2: Contact Verification</span>
                                <span id="contactStatus" class="badge bg-secondary ms-2">Locked</span>
                            </button>
                        </h2>
                        <div id="collapseContact" class="accordion-collapse collapse"
                            data-bs-parent="#verificationAccordion">
                            <div class="accordion-body">
                                <div class="alert alert-info">
                                    <i class="fe fe-info me-2"></i>
                                    Email and phone verification status from user registration
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="card border">
                                            <div class="card-body text-center">
                                                <i class="fe fe-mail fs-1 text-primary mb-2"></i>
                                                <h6>Email Address</h6>
                                                <p id="userEmail" class="mb-2">---</p>
                                                <span id="emailBadge" class="badge bg-success">Verified</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="card border">
                                            <div class="card-body text-center">
                                                <i class="fe fe-phone fs-1 text-success mb-2"></i>
                                                <h6>Phone Number</h6>
                                                <p id="userPhone" class="mb-2">---</p>
                                                <span id="phoneBadge" class="badge bg-success">Verified</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-center mt-3">
                                    <button type="button" class="btn btn-success"
                                        onclick="confirmContactVerification()">
                                        <i class="fe fe-check me-2"></i>Confirm Contact Info
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Step 3: Bank Account Verification -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingBank">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseBank">
                                <i class="fe fe-credit-card me-2"></i>
                                <span class="fw-bold">Step 3: Bank Account Verification</span>
                                <span id="bankStatus" class="badge bg-secondary ms-2">Locked</span>
                            </button>
                        </h2>
                        <div id="collapseBank" class="accordion-collapse collapse"
                            data-bs-parent="#verificationAccordion">
                            <div class="accordion-body">
                                <form id="bankForm">
                                    <div class="alert alert-warning">
                                        <i class="fe fe-alert-triangle me-2"></i>
                                        <strong>Important:</strong> Bank account must be a valid U.S. bank account
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Bank Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="bank_name" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Account Holder Name <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="account_holder_name"
                                                required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Last 4 Digits of Account <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="account_number_last4"
                                                maxlength="4" required>
                                            <small class="text-muted">Only last 4 digits for security</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Routing Number <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" class="form-control" name="routing_number"
                                                maxlength="9" required>
                                            <small class="text-muted">9-digit routing number</small>
                                        </div>
                                    </div>

                                    <div class="form-check mb-3">
                                        <input type="checkbox" class="form-check-input" id="bankVerified"
                                            name="bank_verified" value="1">
                                        <label class="form-check-label" for="bankVerified">
                                            <strong>I confirm that I have verified this bank account belongs to the
                                                winner</strong>
                                        </label>
                                    </div>

                                    <button type="submit" class="btn btn-primary">
                                        <i class="fe fe-check me-2"></i>Verify Bank Account
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Step 4: Final Approval -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingApproval">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapseApproval">
                                <i class="fe fe-award me-2"></i>
                                <span class="fw-bold">Step 4: Final Approval</span>
                                <span id="approvalStatus" class="badge bg-secondary ms-2">Locked</span>
                            </button>
                        </h2>
                        <div id="collapseApproval" class="accordion-collapse collapse"
                            data-bs-parent="#verificationAccordion">
                            <div class="accordion-body">
                                <div class="alert alert-success">
                                    <i class="fe fe-check-circle me-2"></i>
                                    All verification steps completed! Review and approve the claim.
                                </div>

                                <div class="card border-success mb-3">
                                    <div class="card-header bg-success text-white">
                                        <h6 class="mb-0">Verification Summary</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <p><i
                                                        class="fe fe-check-circle text-success me-2"></i><strong>Identity:</strong>
                                                    <span id="summaryIdentity">Verified</span></p>
                                                <p><i
                                                        class="fe fe-check-circle text-success me-2"></i><strong>Contact:</strong>
                                                    <span id="summaryContact">Verified</span></p>
                                            </div>
                                            <div class="col-md-6">
                                                <p><i class="fe fe-check-circle text-success me-2"></i><strong>Bank
                                                        Account:</strong> <span id="summaryBank">Verified</span></p>
                                                <p><i
                                                        class="fe fe-check-circle text-success me-2"></i><strong>Eligibility:</strong>
                                                    <span id="summaryEligibility">Confirmed</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Final Admin Notes</label>
                                    <textarea id="finalNotes" class="form-control" rows="3"></textarea>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-success flex-fill"
                                        onclick="approveClaimFinal()">
                                        <i class="fe fe-check-circle me-2"></i>Approve Claim
                                    </button>
                                    <button type="button" class="btn btn-danger" onclick="rejectClaimFinal()">
                                        <i class="fe fe-x-circle me-2"></i>Reject Claim
                                    </button>
                                </div>
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

<script>
    // Identity Form Submit
    $('#identityForm').on('submit', function(e) {
        e.preventDefault();

        if (!$('#identityVerified').is(':checked')) {
            toastr.error('Please confirm identity verification');
            return;
        }

        let winnerId = $('#winnerId').val();
        let formData = {
            drivers_license: $('[name="drivers_license"]').val(),
            license_state: $('[name="license_state"]').val(),
            license_expiry: $('[name="license_expiry"]').val(),
            identity_verified: 1,
            admin_notes: $('[name="admin_notes"]').val()
        };

        $.ajax({
            url: "{{ route('draw-winners.verify-identity', '') }}/" + winnerId,
            type: 'POST',
            data: formData,
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    $('#identityStatus').removeClass('bg-secondary').addClass('bg-success').text(
                        'Completed');
                    $('#contactStatus').removeClass('bg-secondary').addClass('bg-warning').text(
                        'In Progress');
                    $('.accordion-button').eq(1).click(); // Open next step
                    loadVerificationStatus(winnerId);
                }
            },
            error: function(xhr) {
                toastr.error('Verification failed');
            }
        });
    });

    // Bank Form Submit
    $('#bankForm').on('submit', function(e) {
        e.preventDefault();

        if (!$('#bankVerified').is(':checked')) {
            toastr.error('Please confirm bank account verification');
            return;
        }

        let winnerId = $('#winnerId').val();
        let formData = {
            bank_name: $('[name="bank_name"]').val(),
            account_holder_name: $('[name="account_holder_name"]').val(),
            account_number_last4: $('[name="account_number_last4"]').val(),
            routing_number: $('[name="routing_number"]').val(),
            bank_verified: 1
        };

        $.ajax({
            url: "{{ route('draw-winners.verify-bank', '') }}/" + winnerId,
            type: 'POST',
            data: formData,
            success: function(res) {
                if (res.success) {
                    toastr.success(res.message);
                    $('#bankStatus').removeClass('bg-secondary bg-warning').addClass('bg-success')
                        .text('Completed');
                    $('#approvalStatus').removeClass('bg-secondary').addClass('bg-warning').text(
                        'Ready');
                    $('.accordion-button').eq(3).click(); // Open approval step
                    loadVerificationStatus(winnerId);
                }
            },
            error: function(xhr) {
                toastr.error('Verification failed');
            }
        });
    });

    // Approve Claim
    function approveClaimFinal() {
        let winnerId = $('#winnerId').val();
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
                    url: "{{ route('draw-winners.approve-claim', '') }}/" + winnerId,
                    type: 'POST',
                    data: {
                        admin_notes: notes
                    },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire('Approved!', res.message, 'success');
                            $('#verificationModal').modal('hide');
                            $('#winnersTable').DataTable().ajax.reload();
                        }
                    }
                });
            }
        });
    }

    // Reject Claim
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
                let winnerId = $('#winnerId').val();

                $.ajax({
                    url: "{{ route('draw-winners.reject-claim', '') }}/" + winnerId,
                    type: 'POST',
                    data: {
                        rejection_reason: result.value
                    },
                    success: function(res) {
                        if (res.success) {
                            Swal.fire('Rejected!', res.message, 'info');
                            $('#verificationModal').modal('hide');
                            $('#winnersTable').DataTable().ajax.reload();
                        }
                    }
                });
            }
        });
    }

    // Update Verification UI
    function updateVerificationUI(data) {
        if (!data.has_verification) {
            // Initialize verification
            $.post("{{ route('draw-winners.initiate-verification', '') }}/" + $('#winnerId').val());
        } else {
            let verification = data.verification;
            let progress = data.progress;

            // Update progress bar
            $('#verificationProgressBar').css('width', progress + '%').find('span').text(Math.round(progress) + '%');

            // Update status badges
            if (verification.identity_verified) {
                $('#identityStatus').removeClass('bg-secondary bg-warning').addClass('bg-success').text('Completed');
            }

            if (verification.email_verified && verification.phone_verified) {
                $('#contactStatus').removeClass('bg-secondary').addClass('bg-success').text('Completed');
            }

            if (verification.bank_verified) {
                $('#bankStatus').removeClass('bg-secondary bg-warning').addClass('bg-success').text('Completed');
            }

            if (data.can_approve) {
                $('#approvalStatus').removeClass('bg-secondary').addClass('bg-success').text('Ready');
            }
        }
    }

    // Confirm Contact Verification
    function confirmContactVerification() {
        $('#contactStatus').removeClass('bg-secondary bg-warning').addClass('bg-success').text('Completed');
        $('#bankStatus').removeClass('bg-secondary').addClass('bg-warning').text('In Progress');
        $('.accordion-button').eq(2).click(); // Open bank verification
        toastr.success('Contact verification confirmed');
    }
</script>
