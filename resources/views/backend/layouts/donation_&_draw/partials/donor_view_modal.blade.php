<div class="modal fade" id="viewDonorModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fe fe-eye me-2"></i>Donor & Donation Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fe fe-user me-2"></i>Donor Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="fw-bold">Name:</td>
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
                                </table>
                            </div>
                        </div>

                        <div class="card border">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fe fe-calendar me-2"></i>Draw Week</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="fw-bold">Week:</td>
                                        <td id="d_week">---</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="card border">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fe fe-credit-card me-2"></i>Payment Information</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="fw-bold">Payment ID:</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <code id="d_payment_id"
                                                    class="text-break text-monospace small me-2">---</code>
                                                <button class="btn btn-sm btn-outline-secondary copy-btn"
                                                    data-clipboard-target="#d_payment_id">
                                                    <i class="fe fe-copy"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fe fe-dollar-sign me-2"></i>Donation Details</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="fw-bold">Amount:</td>
                                        <td class="text-success fw-bold" id="d_amount">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Donated At:</td>
                                        <td id="d_donated_at">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Status:</td>
                                        <td id="d_status">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Donor ID:</td>
                                        <td id="donor_id">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Stripe Customer ID:</td>
                                        <td class="bg-light" id="stripe_customer_id">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Donation Attempt:</td>
                                        <td id="donation_attempt">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Lifetime Donate Amount:</td>
                                        <td id="lifetime_donate_amount">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Times Won:</td>
                                        <td id="times_won">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Last Donate At:</td>
                                        <td id="last_donation_at">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Last Won At:</td>
                                        <td id="last_won_at">---</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border mb-3">
                            <div class="card-header bg-light">
                                <h6 class="mb-0"><i class="fe fe-dollar-sign me-2"></i>Donation Details</h6>
                            </div>
                            <div class="card-body">
                                <table class="table table-borderless table-sm">
                                    <tr>
                                        <td class="fw-bold">Amount:</td>
                                        <td class="text-success fw-bold" id="d_amount">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Donated At:</td>
                                        <td id="d_donated_at">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Status:</td>
                                        <td id="d_status">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Donor ID:</td>
                                        <td id="donor_id">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Stripe Customer ID:</td>
                                        <td class="bg-light" id="stripe_customer_id">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Donation Attempt:</td>
                                        <td id="donation_attempt">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Lifetime Donate Amount:</td>
                                        <td id="lifetime_donate_amount">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Times Won:</td>
                                        <td id="times_won">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Last Donate At:</td>
                                        <td id="last_donation_at">---</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Last Won At:</td>
                                        <td id="last_won_at">---</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
                    <i class="fe fe-x"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
