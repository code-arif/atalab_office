<div class="row mb-3">
    <div class="col-12">
        <div class="card mb-3">
            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fs-14 d-flex align-items-center">
                    <i class="fe fe-filter me-2"></i>Advanced Filters
                </h5>
                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 fs-12 d-flex align-items-center" id="resetFilters">
                    <i class="fe fe-x me-1"></i> Reset
                </button>
            </div>
            <div class="card-body py-2">
                <form id="filterForm">
                    <div class="row g-2 align-items-end">
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                            <label class="form-label small mb-1">Week</label>
                            <select class="form-select form-select-sm" id="weekFilter">
                                <option value="">All Weeks</option>
                                @foreach ($weeks as $week)
                                    <option value="{{ $week->id }}">Week #{{ $week->week_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-xl-1 col-lg-3 col-md-4 col-sm-6">
                            <label class="form-label small mb-1">Type</label>
                            <select class="form-select form-select-sm" id="participantTypeFilter">
                                <option value="">All</option>
                                <option value="new">New Entry</option>
                                <option value="rollover">Rollover</option>
                            </select>
                        </div>
                        <div class="col-xl-1 col-lg-3 col-md-4 col-sm-6">
                            <label class="form-label small mb-1">Payment Status</label>
                            <select class="form-select form-select-sm" id="paymentStatusFilter">
                                <option value="">All</option>
                                <option value="succeeded">Paid</option>
                                <option value="pending">Pending</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                            <label class="form-label small mb-1">Date From</label>
                            <input type="date" class="form-control form-control-sm" id="dateFrom">
                        </div>
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6">
                            <label class="form-label small mb-1">Date To</label>
                            <input type="date" class="form-control form-control-sm" id="dateTo">
                        </div>
                        <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6">
                            <label class="form-label small mb-1">Min ($)</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" id="minAmount" placeholder="0.00">
                        </div>
                        <div class="col-xl-1 col-lg-2 col-md-3 col-sm-6">
                            <label class="form-label small mb-1">Max ($)</label>
                            <input type="number" step="0.01" class="form-control form-control-sm" id="maxAmount" placeholder="10000">
                        </div>
                        <div class="col-xl-2 col-lg-12 col-md-2 col-sm-12">
                            <label class="form-label small mb-1 d-none d-md-block opacity-0">Action</label>
                            <button type="button" class="btn btn-primary btn-sm w-100 d-flex justify-content-center align-items-center" id="applyFilters">
                                <i class="fe fe-search me-1"></i> Apply Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Main Table -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">All Donors & Donations</h3>
                <div>
                    <span class="badge bg-info me-2">Total Records: <span id="totalCount">0</span></span>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0" id="donorsTable">
                        <thead class="table-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Donor Name</th>
                                <th>Donor ID</th>
                                <th>Donation ID</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Week</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Fee</th>
                                <th>Total</th>
                                <th>Cover</th>
                                <th>Donated At</th>
                                <th>Status</th>
                                <th width="80">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
