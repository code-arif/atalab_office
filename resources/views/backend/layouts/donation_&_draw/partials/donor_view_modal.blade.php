<style>
    #viewDonorModal .info-label {
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #7b8190;
        font-weight: 600;
        margin-bottom: 2px;
        display: block;
    }
    #viewDonorModal .info-value {
        font-size: 14px;
        color: #333;
        font-weight: 500;
        word-break: break-all;
    }
    #viewDonorModal .card-title-pro {
        font-size: 15px;
        font-weight: 700;
        color: #2c323f;
        margin-bottom: 15px;
        border-bottom: 1px solid #f0f0f5;
        padding-bottom: 10px;
        display: flex;
        align-items: center;
    }
    #viewDonorModal .card-title-pro i {
        margin-right: 8px;
        color: #5066e1;
    }
    #viewDonorModal .pro-card {
        border-radius: 12px;
        border: 1px solid #e9edf4;
        box-shadow: 0 4px 15px rgba(0,0,0,0.02);
        background-color: #ffffff;
    }
    #viewDonorModal .fs-13 { font-size: 13px !important; }
    #viewDonorModal .fs-12 { font-size: 12px !important; }
</style>
<div class="modal fade" id="viewDonorModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-bottom-0 pb-0 pt-4 px-4 bg-white rounded-top-4">
                <div class="d-flex align-items-center w-100">
                    <div class="bg-primary-transparent p-3 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                        <i class="fe fe-user text-primary fs-3"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 fw-bolder text-dark" id="d_name">---</h4>
                        <div class="d-flex align-items-center text-muted fs-13">
                            <i class="fe fe-mail me-1 text-primary"></i> <span id="d_email" class="me-3 fw-medium">---</span>
                            <i class="fe fe-phone me-1 text-primary"></i> <span id="d_phone" class="fw-medium">---</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body p-4" style="background-color: #f9fafb;">
                <div class="row g-4">
                    <!-- LEFT COLUMN -->
                    <div class="col-md-6">
                        <!-- Donor Profile Details -->
                        <div class="card pro-card mb-4 h-auto">
                            <div class="card-body">
                                <h5 class="card-title-pro"><i class="fe fe-file-text"></i>Donor Profile Details</h5>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <span class="info-label">Address</span>
                                        <span class="info-value" id="d_address">---</span>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="info-label">Donor ID</span>
                                        <span class="info-value"><code id="donor_id" class="text-primary fs-13 bg-primary-transparent px-2 py-1 rounded-1 fw-bold border border-primary-transparent">---</code></span>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="info-label">Role</span>
                                        <span class="info-value mt-1 d-block"><span id="d_role" class="badge bg-info p-3">---</span></span>
                                    </div>
                                    <div class="col-sm-6">
                                        <span class="info-label">Registered At</span>
                                        <span class="info-value fs-13" id="d_registered_at">---</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics -->
                        <div class="card pro-card mb-4 h-auto">
                            <div class="card-body">
                                <h5 class="card-title-pro"><i class="fe fe-activity"></i>Donor Activity & Stats</h5>
                                <div class="row g-3 text-center">
                                    <div class="col-4 border-end">
                                        <h3 class="mb-1 fw-bolder text-primary" id="donation_attempt">---</h3>
                                        <span class="info-label text-muted">Total Donations</span>
                                    </div>
                                    <div class="col-4 border-end">
                                        <h3 class="mb-1 fw-bolder text-success" id="lifetime_donate_amount">---</h3>
                                        <span class="info-label text-muted">Lifetime Donated</span>
                                    </div>
                                    <div class="col-4">
                                        <h3 class="mb-1 fw-bolder text-warning" id="times_won">---</h3>
                                        <span class="info-label text-muted">Times Won</span>
                                    </div>
                                </div>
                                <hr class="my-3" style="border-color: #e9edf4">
                                <div class="row">
                                    <div class="col-6">
                                        <span class="info-label">Last Donation</span>
                                        <span class="info-value fs-13" id="last_donation_at">---</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="info-label">Last Won</span>
                                        <span class="info-value fs-13" id="last_won_at">---</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stripe Information -->
                         <div class="card pro-card m-0">
                            <div class="card-body">
                                <h5 class="card-title-pro"><i class="fe fe-credit-card"></i>Stripe Information</h5>
                                <div class="row g-3">
                                    <div class="col-12 d-flex justify-content-between align-items-center border-bottom pb-2">
                                        <div>
                                            <span class="info-label">Customer ID</span>
                                            <span class="info-value fw-bold font-monospace fs-13" id="stripe_customer_id">---</span>
                                        </div>
                                        <button class="btn btn-sm btn-outline-light text-dark border copy-btn" data-clipboard-target="#stripe_customer_id"><i class="fe fe-copy"></i></button>
                                    </div>
                                    <div class="col-12 d-flex justify-content-between align-items-center border-bottom pb-2">
                                        <div>
                                            <span class="info-label">Payment ID</span>
                                            <span class="info-value fw-bold font-monospace fs-13" id="d_payment_id">---</span>
                                        </div>
                                        <button class="btn btn-sm btn-outline-light text-dark border copy-btn" data-clipboard-target="#d_payment_id"><i class="fe fe-copy"></i></button>
                                    </div>
                                    <div class="col-12">
                                        <span class="info-label">Charge ID</span>
                                        <span class="info-value fw-bold font-monospace fs-13" id="stripe_charge_id">---</span>
                                    </div>
                                    <div class="col-12 mt-2">
                                        <span class="info-label">Payment Status</span>
                                        <div class="mt-1" id="d_status">---</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN -->
                    <div class="col-md-6">
                        <!-- Donation Details -->
                        <div class="card pro-card mb-4 h-auto" style="border-top: 4px solid #19b159;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3 border-bottom pb-3">
                                    <h5 class="card-title-pro border-0 p-0 m-0"><i class="fe fe-dollar-sign text-success"></i>Current Donation</h5>
                                    <h2 class="mb-0 text-success fw-bolder" id="d_amount">---</h2>
                                </div>
                                <div class="row g-3">
                                    <div class="col-6">
                                        <span class="info-label">Donation ID</span>
                                        <span class="info-value text-primary fw-bold" id="donation_id">---</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="info-label">Formatted Donation ID</span>
                                        <span class="info-value"><code id="donation_id_formatted" class="text-primary fs-13 bg-primary-transparent px-2 py-1 rounded-1 fw-bold border border-primary-transparent">---</code></span>
                                    </div>
                                    <div class="col-6">
                                        <span class="info-label">Payment Type</span>
                                        <span class="info-value fw-bold" id="payment_type">---</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="info-label">Processing Fee</span>
                                        <span class="info-value fw-bold text-warning" id="d_processing_fee">---</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="info-label">Total Charged</span>
                                        <span class="info-value fw-bold text-info" id="d_total_amount">---</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="info-label">Fee Covered</span>
                                        <div class="mt-1" id="d_is_cover">---</div>
                                    </div>
                                    <div class="col-6">
                                        <span class="info-label">Attempt Number</span>
                                        <span class="info-value fw-bold" id="attempt_number">---</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="info-label">Eligible for Draw</span>
                                        <div class="mt-1" id="is_eligible">---</div>
                                    </div>
                                    <div class="col-12 border-bottom pb-2">
                                        <span class="info-label">Donated At</span>
                                        <span class="info-value fw-semibold" id="d_donated_at">---</span>
                                    </div>
                                    <div class="col-12">
                                        <span class="info-label">Temp Identifier</span>
                                        <span class="info-value text-muted font-monospace fs-12" id="temp_identifier">---</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="info-label">Created At</span>
                                        <span class="info-value text-muted fs-12" id="donation_created_at">---</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="info-label">Updated At</span>
                                        <span class="info-value text-muted fs-12" id="donation_updated_at">---</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Draw Information -->
                        <div class="card pro-card h-auto" style="border-top: 4px solid #5066e1;">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title-pro border-0 p-0 m-0"><i class="fe fe-calendar text-primary"></i>Weekly Draw Info</h5>
                                    <div><span id="d_week">---</span> <span class="badge bg-light text-dark ms-1" id="draw_year">---</span></div>
                                </div>
                                <div class="mb-1" id="draw_status">---</div>

                                <div class="row g-3 bg-primary-transparent rounded-1 p-3 mb-3">
                                    <div class="col-6">
                                        <span class="info-label text-primary">Start Date</span>
                                        <span class="info-value fs-13" id="draw_start_date">---</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="info-label text-primary">End Date</span>
                                        <span class="info-value fs-13" id="draw_end_date">---</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="info-label text-primary">Countdown Ends</span>
                                        <span class="info-value fs-13" id="countdown_ends_at">---</span>
                                    </div>
                                    <div class="col-6">
                                        <span class="info-label text-danger">Claim Deadline</span>
                                        <span class="info-value text-danger fs-13 fw-bold" id="claim_deadline">---</span>
                                    </div>
                                </div>

                                <div class="row g-3">
                                    <div class="col-4">
                                        <span class="info-label">Total Pool</span>
                                        <span class="info-value text-success fw-bold" id="total_pool">---</span>
                                    </div>
                                    <div class="col-4">
                                        <span class="info-label">Commission</span>
                                        <span class="info-value text-primary fw-bold" id="admin_commission">---</span>
                                    </div>
                                    <div class="col-4">
                                        <span class="info-label">Total Part.</span>
                                        <span class="info-value fw-bold" id="total_participants">---</span>
                                    </div>

                                    <div class="col-4">
                                        <span class="info-label">Eligible</span>
                                        <span class="info-value" id="eligible_participants">---</span>
                                    </div>
                                    <div class="col-4">
                                        <span class="info-label">Excluded</span>
                                        <span class="info-value" id="excluded_winners_count">---</span>
                                    </div>
                                    <div class="col-4">
                                        <span class="info-label text-success">Recipients</span>
                                        <span class="info-value text-success fw-bold" id="total_recipients">---</span>
                                    </div>

                                    <div class="col-12 mt-3 pt-3 border-top d-flex align-items-center">
                                        <span class="info-label mb-0 me-3">Winners Selected:</span>
                                        <span id="winners_selected">---</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer border-top-0 pt-3 pb-4 px-4 bg-white rounded-bottom-4 d-flex justify-content-end shadow-sm">
                <button type="button" class="btn btn-dark px-4 py-2 fw-semibold shadow-sm" data-bs-dismiss="modal">
                    Close Details
                </button>
            </div>
        </div>
    </div>
</div>
