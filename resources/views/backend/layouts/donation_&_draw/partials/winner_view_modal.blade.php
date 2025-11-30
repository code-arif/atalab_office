<!-- View Winner Modal -->
<div class="modal fade" id="viewWinnerModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fe fe-eye me-2"></i>Winner Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="printableArea">
                <div class="row">
                    <!-- LEFT COLUMN -->
                    <div class="col-md-6">
                        <!-- Winner Information -->
                        <div class="card border mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fe fe-user me-2"></i>Winner Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td class="fw-bold" width="45%">Name:</td>
                                        <td id="view_name">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Email:</td>
                                        <td id="view_email">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Phone:</td>
                                        <td id="view_phone">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Address:</td>
                                        <td id="address">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Donor ID:</td>
                                        <td id="donor_id">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Lifetime Donation:</td>
                                        <td id="lifetime_donation_amount">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Times Won:</td>
                                        <td id="times_won">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Last Donation:</td>
                                        <td id="last_donation_at">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Last Won:</td>
                                        <td id="last_won_at">---</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Draw Information -->
                        <div class="card border mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fe fe-calendar me-2"></i>Draw Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td class="fw-bold" width="45%">Week:</td>
                                        <td id="view_week">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Draw Period:</td>
                                        <td id="view_draw_period">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Total Pool:</td>
                                        <td id="view_pool">---</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Email & Phone Verification -->
                        <div class="card border mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fe fe-check-circle me-2"></i>Contact Verification</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td class="fw-bold" width="45%">Email Verified:</td>
                                        <td id="view_email_verified">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Phone Verified:</td>
                                        <td id="view_phone_verified">---</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT COLUMN -->
                    <div class="col-md-6">
                        <!-- Claim Information -->
                        <div class="card border mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fe fe-dollar-sign me-2"></i>Claim Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td class="fw-bold" width="45%">Amount Won:</td>
                                        <td id="view_amount" class="text-success fw-bold">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Claimed:</td>
                                        <td id="view_claimed">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Claimed At:</td>
                                        <td id="view_claimed_at">---</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Payout Information -->
                        <div class="card border mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fe fe-credit-card me-2"></i>Payout Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td class="fw-bold" width="45%">Status:</td>
                                        <td id="view_payout_status">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Stripe ID:</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <code id="view_stripe_id" class="text-break small me-2">---</code>
                                                <button class="btn btn-sm btn-outline-secondary copy-btn no-print"
                                                    data-clipboard-target="#view_stripe_id" title="Copy">
                                                    <i class="fe fe-copy"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Donation ID:</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <code id="view_donation_id" class="text-break small me-2">---</code>
                                                <button class="btn btn-sm btn-outline-secondary copy-btn no-print"
                                                    data-clipboard-target="#view_donation_id" title="Copy">
                                                    <i class="fe fe-copy"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Identity Information -->
                        <div class="card border mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fe fe-file-text me-2"></i>Identity Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td class="fw-bold" width="45%">Driver's License:</td>
                                        <td id="view_license">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">State:</td>
                                        <td id="view_license_state">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Expiry:</td>
                                        <td id="view_license_expiry">---</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Bank Information -->
                        <div class="card border mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fe fe-credit-card me-2"></i>Bank Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td class="fw-bold" width="45%">Bank Name:</td>
                                        <td id="bank_name">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Account Last 4:</td>
                                        <td id="bank_acc_last4">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Routing Number:</td>
                                        <td id="bank_routing_number">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Verified Date:</td>
                                        <td id="bank_verified_at">---</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Full Width Verification Section -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card border">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fe fe-shield me-2"></i>Verification Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm mb-0">
                                            <tr>
                                                <td class="fw-bold" width="40%">Verified By:</td>
                                                <td id="verified_by">---</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Approved Date:</td>
                                                <td id="approved_date">---</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Rejected Date:</td>
                                                <td id="rejected_date">---</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm mb-0">
                                            <tr>
                                                <td class="fw-bold" width="40%">Verification Progress:</td>
                                                <td>
                                                    <span id="verification_progress" class="badge bg-info">0%</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold" colspan="2">Admin Notes:</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <div class="alert alert-secondary mb-0 py-2" id="admin_notes_box">
                                                        <span id="admin_notes">---</span>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer no-print">
                <button type="button" class="btn btn-success" onclick="printWinnerDetails()">
                    <i class="fe fe-printer me-2"></i>Print Details
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Print Styles -->
<style>
@media print {
    /* Hide everything except modal content */
    body * {
        visibility: hidden;
    }

    #printableArea,
    #printableArea * {
        visibility: visible;
    }

    #printableArea {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }

    /* Hide buttons and non-printable elements */
    .no-print,
    .btn-close,
    .modal-header button,
    .modal-footer {
        display: none !important;
    }

    /* Preserve colors */
    .card-header {
        background-color: #f8f9fa !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .badge {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    .bg-success {
        background-color: #28a745 !important;
        color: white !important;
    }

    .bg-danger {
        background-color: #dc3545 !important;
        color: white !important;
    }

    .bg-warning {
        background-color: #ffc107 !important;
        color: #000 !important;
    }

    .bg-info {
        background-color: #17a2b8 !important;
        color: white !important;
    }

    .bg-secondary {
        background-color: #6c757d !important;
        color: white !important;
    }

    .bg-primary {
        background-color: #0d6efd !important;
        color: white !important;
    }

    .text-success {
        color: #28a745 !important;
    }

    /* Ensure proper page breaks */
    .card {
        page-break-inside: avoid;
    }

    /* Add header for print */
    #printableArea::before {
        content: "Winner Details Report";
        display: block;
        text-align: center;
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 2px solid #0d6efd;
    }

    /* Adjust card borders for print */
    .card {
        border: 1px solid #dee2e6 !important;
        margin-bottom: 15px;
    }

    /* Make tables more readable */
    .table td {
        padding: 8px !important;
    }
}
</style>

<script>
function printWinnerDetails() {
    window.print();
}
</script>
