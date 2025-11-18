<!-- View Winner Modal -->
<div class="modal fade" id="viewWinnerModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fe fe-trophy me-2"></i>Winner Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <!-- Winner Information -->
                        <div class="card border mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fe fe-user me-2"></i>Winner Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <td class="fw-bold" width="40%">Name:</td>
                                        <td id="view_winner_name">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Email:</td>
                                        <td id="view_winner_email">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Phone:</td>
                                        <td id="view_winner_phone">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Week Number:</td>
                                        <td id="view_week_info">---</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Prize Information -->
                        <div class="card border mb-3 border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="fe fe-dollar-sign me-2"></i>Prize Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <td class="fw-bold" width="40%">Amount Won:</td>
                                        <td id="view_amount_won" class="text-success fw-bold fs-5">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Claim Status:</td>
                                        <td id="view_claim_status">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Payout Status:</td>
                                        <td id="view_payout_status">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Claimed At:</td>
                                        <td id="view_claimed_at">---</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <!-- Verification Status -->
                        <div class="card border mb-3" id="verificationCard">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fe fe-shield me-2"></i>Verification Status</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Overall Status:</label>
                                    <div id="view_verification_status">Not Started</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Progress:</label>
                                    <div class="progress" style="height: 25px;">
                                        <div id="view_progress_bar" class="progress-bar" role="progressbar"
                                            style="width: 0%">0%</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <i id="identity_icon" class="fe fe-user me-2"></i>
                                            <span id="identity_text">Identity</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <i id="contact_icon" class="fe fe-mail me-2"></i>
                                            <span id="contact_text">Contact</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <i id="bank_icon" class="fe fe-credit-card me-2"></i>
                                            <span id="bank_text">Bank Account</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="d-flex align-items-center mb-2">
                                            <i id="approval_icon" class="fe fe-check-circle me-2"></i>
                                            <span id="approval_text">Approval</span>
                                        </div>
                                    </div>
                                </div>

                                <div id="verificationDetails" class="mt-3" style="display: none;">
                                    <hr>
                                    <h6 class="fw-bold">Verification Details:</h6>
                                    <p><strong>Verified By:</strong> <span id="verified_by">---</span></p>
                                    <p><strong>Approved At:</strong> <span id="approved_at">---</span></p>
                                    <p><strong>Admin Notes:</strong></p>
                                    <div id="admin_notes" class="alert alert-info">---</div>
                                </div>
                            </div>
                        </div>

                        <!-- Identity Information (if verified) -->
                        <div class="card border mb-3" id="identityInfoCard" style="display: none;">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fe fe-file-text me-2"></i>Identity Information</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Driver's License:</strong> <span id="view_license">---</span></p>
                                <p><strong>State:</strong> <span id="view_license_state">---</span></p>
                                <p><strong>Expiry:</strong> <span id="view_license_expiry">---</span></p>
                            </div>
                        </div>

                        <!-- Bank Information (if verified) -->
                        <div class="card border mb-3" id="bankInfoCard" style="display: none;">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fe fe-credit-card me-2"></i>Bank Information</h6>
                            </div>
                            <div class="card-body">
                                <p><strong>Bank Name:</strong> <span id="view_bank_name">---</span></p>
                                <p><strong>Account Holder:</strong> <span id="view_account_holder">---</span></p>
                                <p><strong>Account (Last 4):</strong> <span id="view_account_last4">---</span></p>
                                <p><strong>Routing Number:</strong> <span id="view_routing">---</span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="d-flex gap-2 justify-content-end">
                            <button type="button" id="processPayoutBtn" class="btn btn-success"
                                style="display: none;" onclick="processPayoutAction()">
                                <i class="fe fe-send me-2"></i>Process Payout
                            </button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function populateViewModal(data) {
        // Winner Information
        $('#view_winner_name').text(data.user?.name || 'N/A');
        $('#view_winner_email').text(data.user?.email || 'N/A');
        $('#view_winner_phone').text(data.user?.phone || 'N/A');
        $('#view_week_info').text('Week #' + data.weekly_draw?.week_number);

        // Prize Information
        $('#view_amount_won').text('$' + parseFloat(data.amount_won).toFixed(2));

        const claimBadges = {
            'pending': '<span class="badge bg-warning">Pending</span>',
            'claimed': '<span class="badge bg-success">Claimed</span>',
            'expired': '<span class="badge bg-danger">Expired</span>',
            'approved_pending_payout': '<span class="badge bg-info">Approved - Pending Payout</span>',
        };
        $('#view_claim_status').html(claimBadges[data.claim_status] ||
            '<span class="badge bg-secondary">Unknown</span>');

        const payoutBadges = {
            'pending': '<span class="badge bg-secondary">Pending</span>',
            'processing': '<span class="badge bg-info">Processing</span>',
            'completed': '<span class="badge bg-success">Completed</span>',
            'failed': '<span class="badge bg-danger">Failed</span>',
        };
        $('#view_payout_status').html(payoutBadges[data.payout_status]);
        $('#view_claimed_at').text(data.claimed_at ? new Date(data.claimed_at).toLocaleString() : 'Not claimed yet');

        // Verification Information
        if (data.verification) {
            const v = data.verification;
            const progress = ((v.identity_verified ? 25 : 0) + (v.email_verified ? 25 : 0) +
                (v.phone_verified ? 25 : 0) + (v.bank_verified ? 25 : 0));

            $('#view_progress_bar').css('width', progress + '%').text(progress + '%');

            // Update icons and text
            updateVerificationIcon('identity', v.identity_verified);
            updateVerificationIcon('contact', v.email_verified && v.phone_verified);
            updateVerificationIcon('bank', v.bank_verified);
            updateVerificationIcon('approval', v.verification_status === 'approved');

            const statusBadges = {
                'pending': '<span class="badge bg-warning">Pending</span>',
                'identity_review': '<span class="badge bg-info">Identity Review</span>',
                'contact_verification': '<span class="badge bg-info">Contact Verification</span>',
                'bank_verification': '<span class="badge bg-info">Bank Verification</span>',
                'approved': '<span class="badge bg-success">Approved</span>',
                'rejected': '<span class="badge bg-danger">Rejected</span>',
            };
            $('#view_verification_status').html(statusBadges[v.verification_status]);

            // Show verification details if approved
            if (v.verification_status === 'approved') {
                $('#verificationDetails').show();
                $('#verified_by').text(v.verified_by?.name || 'System');
                $('#approved_at').text(v.approved_at ? new Date(v.approved_at).toLocaleString() : 'N/A');
                $('#admin_notes').text(v.admin_notes || 'No notes');
            }

            // Show identity info if verified
            if (v.identity_verified) {
                $('#identityInfoCard').show();
                $('#view_license').text(v.drivers_license);
                $('#view_license_state').text(v.license_state);
                $('#view_license_expiry').text(new Date(v.license_expiry).toLocaleDateString());
            }

            // Show bank info if verified
            if (v.bank_verified) {
                $('#bankInfoCard').show();
                $('#view_bank_name').text(v.bank_name);
                $('#view_account_holder').text(v.account_holder_name);
                $('#view_account_last4').text('****' + v.account_number_last4);
                $('#view_routing').text(v.routing_number);
            }

            // Show process payout button if approved
            if (v.verification_status === 'approved' && data.payout_status === 'pending') {
                $('#processPayoutBtn').show().data('winner-id', data.id);
            }
        }
    }

    function updateVerificationIcon(type, verified) {
        const icon = $('#' + type + '_icon');
        const text = $('#' + type + '_text');

        if (verified) {
            icon.removeClass().addClass('fe fe-check-circle text-success me-2');
            text.removeClass().addClass('text-success');
        } else {
            icon.removeClass().addClass('fe fe-x-circle text-muted me-2');
            text.removeClass().addClass('text-muted');
        }
    }

    function processPayoutAction() {
        const winnerId = $('#processPayoutBtn').data('winner-id');

        Swal.fire({
            title: 'Process Payout?',
            text: 'This will initiate the payout process to the winner\'s bank account.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Process!',
            confirmButtonColor: '#28a745'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ route('draw-winners.process-payout', '') }}/" + winnerId,
                    type: 'POST',
                    success: function(res) {
                        if (res.success) {
                            Swal.fire('Success!', res.message, 'success');
                            $('#viewWinnerModal').modal('hide');
                            $('#winnersTable').DataTable().ajax.reload();
                        }
                    },
                    error: function(xhr) {
                        Swal.fire('Error!', 'Failed to process payout', 'error');
                    }
                });
            }
        });
    }
</script>
