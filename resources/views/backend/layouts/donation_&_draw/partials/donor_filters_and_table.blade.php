<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">
                    <i class="fe fe-filter me-2"></i>Advanced Filters
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="resetFilters">
                        <i class="fe fe-x me-1" style="font-size: 10px"></i>Reset
                    </button>
                </h5>
            </div>
            <div class="card-body">
                <form id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Week</label>
                            <select class="form-select" id="weekFilter">
                                <option value="">All Weeks</option>
                                @foreach ($weeks as $week)
                                    <option value="{{ $week->id }}">Week #{{ $week->week_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Payment Status</label>
                            <select class="form-select" id="paymentStatusFilter">
                                <option value="">All</option>
                                <option value="succeeded">Paid</option>
                                <option value="pending">Pending</option>
                                <option value="failed">Failed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" class="form-control" id="dateFrom">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" class="form-control" id="dateTo">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Min Amount ($)</label>
                            <input type="number" step="0.01" class="form-control" id="minAmount" placeholder="0.00">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Max Amount ($)</label>
                            <input type="number" step="0.01" class="form-control" id="maxAmount"
                                placeholder="10000.00">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" class="btn btn-primary w-100" id="applyFilters">
                                <i class="fe fe-search me-2"></i>Apply Filters
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
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Week</th>
                                <th>Amount</th>
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
