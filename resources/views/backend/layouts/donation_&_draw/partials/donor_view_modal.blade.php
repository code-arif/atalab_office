<div class="modal fade" id="viewDonorModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fe fe-eye me-2"></i>Donor & Donation Complete Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <!-- LEFT COLUMN: Donor Information -->
                    <div class="col-md-6">
                        <!-- Donor Basic Info -->
                        <div class="card border mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fe fe-user me-2"></i>Donor Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td class="fw-bold" style="width: 40%;">Name:</td>
                                        <td id="d_name">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Email:</td>
                                        <td id="d_email">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Phone:</td>
                                        <td id="d_phone">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Address:</td>
                                        <td id="d_address">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Donor ID:</td>
                                        <td>
                                            <code id="donor_id" class="text-primary">---</code>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Role:</td>
                                        <td>
                                            <span id="d_role" class="badge bg-info">---</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Registered At:</td>
                                        <td id="d_registered_at">---</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Stripe Information -->
                        <div class="card border mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fe fe-credit-card me-2"></i>Stripe Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td class="fw-bold" style="width: 40%;">Customer ID:</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <code id="stripe_customer_id" class="text-break small me-2">---</code>
                                                <button class="btn btn-sm btn-outline-secondary copy-btn"
                                                    data-clipboard-target="#stripe_customer_id" title="Copy">
                                                    <i class="fe fe-copy"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Payment ID:</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <code id="d_payment_id" class="text-break small me-2">---</code>
                                                <button class="btn btn-sm btn-outline-secondary copy-btn"
                                                    data-clipboard-target="#d_payment_id" title="Copy">
                                                    <i class="fe fe-copy"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Charge ID:</td>
                                        <td>
                                            <code id="stripe_charge_id" class="text-break small">---</code>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Payment Status:</td>
                                        <td id="d_status">---</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Donor Statistics -->
                        <div class="card border">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fe fe-bar-chart me-2"></i>Donor Statistics</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td class="fw-bold" style="width: 40%;">Total Donations:</td>
                                        <td>
                                            <span id="donation_attempt" class="badge bg-primary">---</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Lifetime Amount:</td>
                                        <td>
                                            <span id="lifetime_donate_amount" class="text-success fw-bold">---</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Times Won:</td>
                                        <td>
                                            <span id="times_won" class="badge bg-warning">---</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Last Donation:</td>
                                        <td id="last_donation_at" class="text-muted small">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Last Won:</td>
                                        <td id="last_won_at" class="text-muted small">---</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN: Donation & Draw Details -->
                    <div class="col-md-6">
                        <!-- Donation Details -->
                        <div class="card border mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fe fe-dollar-sign me-2"></i>Current Donation Details</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td class="fw-bold" style="width: 40%;">Donation ID:</td>
                                        <td>
                                            <code id="donation_id" class="text-primary">---</code>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Amount:</td>
                                        <td>
                                            <span id="d_amount" class="text-success fw-bold fs-5">---</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Donated At:</td>
                                        <td id="d_donated_at">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Payment Type:</td>
                                        <td>
                                            <span id="payment_type" class="badge bg-secondary">---</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Attempt Number:</td>
                                        <td id="attempt_number">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Eligible for Draw:</td>
                                        <td id="is_eligible">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Temp Identifier:</td>
                                        <td>
                                            <code id="temp_identifier" class="small">---</code>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Created At:</td>
                                        <td id="donation_created_at" class="text-muted small">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Updated At:</td>
                                        <td id="donation_updated_at" class="text-muted small">---</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Draw Week Information -->
                        <div class="card border">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fe fe-calendar me-2"></i>Weekly Draw Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td class="fw-bold" style="width: 40%;">Week Number:</td>
                                        <td id="d_week">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Year:</td>
                                        <td id="draw_year">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Draw Status:</td>
                                        <td id="draw_status">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Start Date:</td>
                                        <td id="draw_start_date" class="small">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">End Date:</td>
                                        <td id="draw_end_date" class="small">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Countdown Ends:</td>
                                        <td id="countdown_ends_at" class="small">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Claim Deadline:</td>
                                        <td id="claim_deadline" class="small text-danger">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Total Pool:</td>
                                        <td>
                                            <span id="total_pool" class="text-success fw-bold">---</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Total Participants:</td>
                                        <td id="total_participants">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Eligible Participants:</td>
                                        <td id="eligible_participants">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Excluded Winners:</td>
                                        <td id="excluded_winners_count">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Total Recipients:</td>
                                        <td id="total_recipients">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Admin Commission:</td>
                                        <td>
                                            <span id="admin_commission" class="text-primary fw-bold">---</span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Winners Selected:</td>
                                        <td id="winners_selected">---</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fe fe-x"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
