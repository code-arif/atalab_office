@extends('backend.app')

@section('title', 'Dashboard')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Dashboard</h1>
                        <p class="text-muted">Real-time donation system overview</p>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <!-- LEFT COLUMN -->
                    <div class="col-xl-9 col-lg-8">
                        <!-- STATISTICS CARDS -->
                        <div class="row">
                            <!-- Total Donors -->
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="card stats-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="text-muted mb-1 fs-13">Total Donors</p>
                                                <h3 class="mb-0 fw-bold" id="totalDonors">{{ number_format($totalDonors) }}
                                                </h3>
                                            </div>
                                            <div class="stats-icon bg-primary-gradient">
                                                <i class="fe fe-users"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Donations -->
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="card stats-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="text-muted mb-1 fs-13">Total Raised</p>
                                                <h3 class="mb-0 fw-bold">${{ number_format($totalDonations, 2) }}</h3>
                                            </div>
                                            <div class="stats-icon bg-success-gradient">
                                                <i class="fe fe-dollar-sign"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Winners -->
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="card stats-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="text-muted mb-1 fs-13">Total Winners</p>
                                                <h3 class="mb-0 fw-bold">{{ number_format($totalWinners) }}</h3>
                                            </div>
                                            <div class="stats-icon bg-warning-gradient">
                                                <i class="fe fe-award"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Pending Payouts -->
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="card stats-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="text-muted mb-1 fs-13">Pending Payouts</p>
                                                <h3 class="mb-0 fw-bold">{{ number_format($pendingPayouts) }}</h3>
                                            </div>
                                            <div class="stats-icon bg-danger-gradient">
                                                <i class="fe fe-clock"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- VISITOR STATISTICS CARDS -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="mb-3 fw-semibold">
                                    <i class="fe fe-activity text-primary"></i> Website Traffic Analytics
                                </h5>
                            </div>

                            <!-- Today's Unique Visitors -->
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="card stats-card visitor-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="text-muted mb-1 fs-13">Today's Visitors</p>
                                                <h3 class="mb-0 fw-bold" id="todayVisitors">
                                                    {{ number_format($visitorStats['today_unique']) }}
                                                </h3>
                                                <small class="text-muted">
                                                    <span id="liveVisitors" class="text-success fw-semibold">
                                                        {{ $visitorStats['live_now'] }}
                                                    </span> online now
                                                </small>
                                            </div>
                                            <div class="stats-icon bg-info-gradient">
                                                <i class="fe fe-users"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Today's Page Views -->
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="card stats-card visitor-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="text-muted mb-1 fs-13">Today's Views</p>
                                                <h3 class="mb-0 fw-bold" id="todayViews">
                                                    {{ number_format($visitorStats['today_total']) }}
                                                </h3>
                                                <small class="text-muted">Page impressions</small>
                                            </div>
                                            <div class="stats-icon bg-teal-gradient">
                                                <i class="fe fe-eye"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- All Time Unique Visitors -->
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="card stats-card visitor-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="text-muted mb-1 fs-13">Total Visitors</p>
                                                <h3 class="mb-0 fw-bold">
                                                    {{ number_format($visitorStats['all_time_unique']) }}
                                                </h3>
                                                <small class="text-muted">All time unique</small>
                                            </div>
                                            <div class="stats-icon bg-purple-gradient">
                                                <i class="fe fe-trending-up"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Avg Daily Visitors -->
                            <div class="col-lg-3 col-md-6 col-sm-12">
                                <div class="card stats-card visitor-card">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <p class="text-muted mb-1 fs-13">Growth Rate</p>
                                                <h3 class="mb-0 fw-bold text-success" id="growthRate">
                                                    <i class="fe fe-arrow-up"></i> 12.5%
                                                </h3>
                                                <small class="text-muted">vs last week</small>
                                            </div>
                                            <div class="stats-icon bg-success-gradient">
                                                <i class="fe fe-bar-chart-2"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ACTIVE DRAW CARD -->
                        @if ($activeDraw && $activeDrawStats)
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">Active Draw - Week #{{ $activeDrawStats['week_number'] }}
                                    </h4>
                                </div>
                                <div class="card-body">
                                    <div class="row text-center">
                                        <div class="col-md-4">
                                            <div class="active-draw-stat">
                                                <h2 class="mb-0 fw-bold text-primary" id="activePool">
                                                    ${{ number_format($activeDrawStats['total_pool'], 2) }}
                                                </h2>
                                                <p class="text-muted mb-0">Total Pool</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="active-draw-stat">
                                                <h2 class="mb-0 fw-bold text-success" id="activeParticipants">
                                                    {{ number_format($activeDrawStats['total_participants']) }}
                                                </h2>
                                                <p class="text-muted mb-0">Participants</p>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="active-draw-stat">
                                                <h2 class="mb-0 fw-bold" id="countdown"
                                                    style="font-family: 'Courier New', monospace; letter-spacing: 2px;">
                                                    --:--:--
                                                </h2>
                                                <p class="text-muted mb-0">Time Remaining</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- DONATION TREND CHART -->
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">7-Day Donation Trend</h4>
                            </div>
                            <div class="card-body">
                                <canvas id="donationChart" height="100"></canvas>
                            </div>
                        </div>

                        <!-- WEEKLY PERFORMANCE TABLE -->
                        <div class="card">
                            <div class="card-header">
                                <h4 class="card-title mb-0">Weekly Performance</h4>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>Week</th>
                                                <th>Pool Amount</th>
                                                <th>Participants</th>
                                                <th>Winners</th>
                                                <th>Commission</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($weeklyPerformance as $week)
                                                <tr>
                                                    <td><strong>{{ $week['week'] }}</strong></td>
                                                    <td>${{ number_format($week['pool'], 2) }}</td>
                                                    <td>{{ number_format($week['participants']) }}</td>
                                                    <td>{{ number_format($week['winners']) }}</td>
                                                    <td class="text-success">${{ number_format($week['commission'], 2) }}
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">No data available</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT SIDEBAR -->
                    <div class="col-xl-3 col-lg-4">
                        <!-- RECENT DONATIONS FEED -->
                        <div class="card sticky-card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h4 class="card-title mb-0">Live Donations</h4>
                                <span class="badge bg-success pulse-badge">Live</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="donation-feed" id="donationFeed">
                                    @forelse($recentDonations as $donation)
                                        <div class="donation-item" data-timestamp="{{ $donation['timestamp'] }}">
                                            <div class="donation-avatar">
                                                <div class="avatar-circle">
                                                    {{ strtoupper(substr($donation['user_name'], 0, 1)) }}
                                                </div>
                                            </div>
                                            <div class="donation-details">
                                                <p class="mb-0 fw-semibold">{{ $donation['user_name'] }}</p>
                                                <small class="text-muted">{{ $donation['created_at'] }}</small>
                                            </div>
                                            <div class="donation-amount">
                                                <span
                                                    class="badge bg-primary">${{ number_format($donation['amount'], 2) }}</span>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-5">
                                            <p class="text-muted">No recent donations</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- TOP DONORS -->
                        @if (count($topDonors) > 0)
                            <div class="card">
                                <div class="card-header">
                                    <h4 class="card-title mb-0">Top Donors</h4>
                                </div>
                                <div class="card-body p-2">
                                    @foreach ($topDonors as $index => $donor)
                                        <div class="top-donor-item">
                                            <div class="rank-badge rank-{{ $index + 1 }}">
                                                #{{ $index + 1 }}
                                            </div>
                                            <div class="donor-info">
                                                <p class="mb-0 fw-semibold">{{ $donor['name'] }}</p>
                                                <small
                                                    class="text-muted">${{ number_format($donor['total_donated'], 2) }}</small>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --primary-color: #521aac;
            --primary-gradient: linear-gradient(135deg, #521aac 0%, #7c3aed 100%);
            --success-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        /* Stats Cards */
        .stats-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(82, 26, 172, 0.08);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(82, 26, 172, 0.15);
        }

        .stats-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .bg-primary-gradient {
            background: var(--primary-gradient);
        }

        .bg-success-gradient {
            background: var(--success-gradient);
        }

        .bg-warning-gradient {
            background: var(--warning-gradient);
        }

        .bg-danger-gradient {
            background: var(--danger-gradient);
        }

        /* Active Draw */
        .active-draw-stat {
            padding: 1rem;
            border-right: 1px solid #e5e7eb;
        }

        .active-draw-stat:last-child {
            border-right: none;
        }

        /* Donation Feed */
        .donation-feed {
            max-height: 500px;
            overflow-y: auto;
            padding: 0.5rem;
        }

        .donation-feed::-webkit-scrollbar {
            width: 6px;
        }

        .donation-feed::-webkit-scrollbar-thumb {
            background: var(--primary-color);
            border-radius: 10px;
        }

        .donation-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border-bottom: 1px solid #f3f4f6;
            transition: all 0.3s ease;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .donation-item:hover {
            background: #f9fafb;
        }

        .avatar-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-gradient);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 12px;
        }

        .donation-details {
            flex: 1;
        }

        .pulse-badge {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        /* Top Donors */
        .top-donor-item {
            display: flex;
            align-items: center;
            padding: 0.75rem;
            border-bottom: 1px solid #f3f4f6;
        }

        .rank-badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 0.85rem;
            margin-right: 12px;
            color: white;
        }

        .rank-1 {
            background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
        }

        .rank-2 {
            background: linear-gradient(135deg, #c0c0c0 0%, #e8e8e8 100%);
        }

        .rank-3 {
            background: linear-gradient(135deg, #cd7f32 0%, #e8a87c 100%);
        }

        .rank-4,
        .rank-5 {
            background: var(--primary-gradient);
        }

        .donor-info {
            flex: 1;
        }

        /* Chart */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(82, 26, 172, 0.08);
            margin-bottom: 1.5rem;
        }

        .sticky-card {
            position: sticky;
            top: 20px;
        }

        /* Table */
        .table thead th {
            background: #f9fafb;
            border-bottom: 2px solid var(--primary-color);
            font-weight: 600;
        }

        .table tbody tr:hover {
            background: #f9fafb;
        }

        /* Visitor Card Gradients */
        .bg-info-gradient {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        }

        .bg-teal-gradient {
            background: linear-gradient(135deg, #14b8a6 0%, #0d9488 100%);
        }

        .bg-purple-gradient {
            background: linear-gradient(135deg, #a855f7 0%, #9333ea 100%);
        }

        .visitor-card {
            position: relative;
            overflow: hidden;
        }

        .visitor-card::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
            animation: pulse-wave 4s infinite;
        }

        @keyframes pulse-wave {

            0%,
            100% {
                transform: scale(1);
                opacity: 0.5;
            }

            50% {
                transform: scale(1.2);
                opacity: 0.8;
            }
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script type="module">
        // Chart Data
        const donationTrend = @json($donationTrend);

        // Initialize Chart
        const ctx = document.getElementById('donationChart').getContext('2d');
        const chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: donationTrend.map(d => new Date(d.date).toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric'
                })),
                datasets: [{
                    label: 'Total Amount ($)',
                    data: donationTrend.map(d => d.total_amount),
                    borderColor: '#521aac',
                    backgroundColor: 'rgba(82, 26, 172, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 5,
                    pointHoverRadius: 7
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#521aac',
                        padding: 12,
                        titleFont: {
                            size: 14
                        },
                        bodyFont: {
                            size: 13
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // ===== Countdown Timer Function (MUST BE DEFINED BEFORE CALLING) =====
        @if ($activeDraw && $activeDraw->countdown_ends_at)
            const drawEndTime = {{ \Carbon\Carbon::parse($activeDraw->countdown_ends_at)->timestamp }};

            function updateCountdown() {
                const now = Math.floor(Date.now() / 1000);
                let diff = drawEndTime - now;

                const countdownElement = document.getElementById('countdown');
                if (!countdownElement) return; // Guard clause

                if (diff <= 0) {
                    countdownElement.innerHTML = '<span class="text-danger fw-bold">DRAW ENDED</span>';
                    return;
                }

                const days = Math.floor(diff / 86400);
                const hours = Math.floor((diff % 86400) / 3600);
                const minutes = Math.floor((diff % 3600) / 60);
                const seconds = diff % 60;

                let timeStr = '';
                if (days > 0) {
                    timeStr = `${days}d ${hours}h ${minutes}m`;
                } else if (hours > 0) {
                    timeStr = `${hours}h ${minutes}m ${seconds}s`;
                } else if (minutes > 0) {
                    timeStr = `${minutes}m ${seconds}s`;
                } else {
                    timeStr = `${seconds}s`;
                }

                // Last 10 seconds = red + big
                if (diff <= 10) {
                    countdownElement.innerHTML = `<span class="text-danger fw-bold fs-2">${timeStr}</span>`;
                } else {
                    countdownElement.textContent = timeStr;
                }
            }

            // Start countdown immediately
            updateCountdown();

            // Update every 1 second
            setInterval(updateCountdown, 1000);
        @else
            // No active draw
            const countdownElement = document.getElementById('countdown');
            if (countdownElement) {
                countdownElement.innerHTML = '<span class="text-muted">No Active Draw</span>';
            }
        @endif

        // Real-time Updates (every 30 seconds) - UPDATED WITH VISITOR STATS
        setInterval(async () => {
            try {
                // Update Donation Stats
                const statsResponse = await fetch('/admin/dashboard/live-stats');
                const statsData = await statsResponse.json();

                if (statsData.success) {
                    const activePoolElement = document.getElementById('activePool');
                    const activeParticipantsElement = document.getElementById('activeParticipants');

                    if (activePoolElement) {
                        activePoolElement.textContent = '$' + statsData.data.total_pool.toLocaleString(
                            undefined, {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                    }

                    if (activeParticipantsElement) {
                        activeParticipantsElement.textContent = statsData.data.total_participants
                            .toLocaleString();
                    }
                }

                // Update Donation Feed
                const feedResponse = await fetch('/admin/dashboard/recent-donations');
                const feedData = await feedResponse.json();

                if (feedData.success) {
                    updateDonationFeed(feedData.data);
                }

                // ===== Update Visitor Stats =====
                const visitorResponse = await fetch('/api/dashboard/visitor-stats');
                const visitorData = await visitorResponse.json();

                if (visitorData.success) {
                    // Update counts with smooth animation
                    animateCount('todayVisitors', visitorData.data.today.unique);
                    animateCount('todayViews', visitorData.data.today.total);

                    // Update live visitors count
                    const liveElement = document.getElementById('liveVisitors');
                    if (liveElement) {
                        liveElement.textContent = visitorData.data.live_count;
                    }
                }

            } catch (error) {
                console.error('Error updating dashboard:', error);
            }
        }, 30000); // 30 seconds

        // Update Donation Feed
        function updateDonationFeed(donations) {
            const feed = document.getElementById('donationFeed');
            if (!feed) return;

            const currentTimestamp = Math.floor(Date.now() / 1000);

            // Remove donations older than 1 hour
            const items = feed.querySelectorAll('.donation-item');
            items.forEach(item => {
                const timestamp = parseInt(item.dataset.timestamp);
                if (currentTimestamp - timestamp > 3600) {
                    item.remove();
                }
            });

            // Add new donations
            donations.forEach(donation => {
                const exists = feed.querySelector(`[data-timestamp="${donation.timestamp}"]`);
                if (!exists) {
                    const item = createDonationItem(donation);
                    feed.insertBefore(item, feed.firstChild);
                }
            });
        }

        function createDonationItem(donation) {
            const div = document.createElement('div');
            div.className = 'donation-item';
            div.dataset.timestamp = donation.timestamp;
            div.innerHTML = `
            <div class="donation-avatar">
                <div class="avatar-circle">
                    ${donation.user_name.charAt(0).toUpperCase()}
                </div>
            </div>
            <div class="donation-details">
                <p class="mb-0 fw-semibold">${donation.user_name}</p>
                <small class="text-muted">${donation.created_at}</small>
            </div>
            <div class="donation-amount">
                <span class="badge bg-primary">$${donation.amount.toFixed(2)}</span>
            </div>
        `;
            return div;
        }

        // ===== Smooth Number Animation Function =====
        function animateCount(elementId, targetValue) {
            const element = document.getElementById(elementId);
            if (!element) return;

            const currentValue = parseInt(element.textContent.replace(/,/g, '')) || 0;

            // Only animate if value changed
            if (currentValue === targetValue) return;

            const difference = targetValue - currentValue;
            const duration = 1000; // 1 second
            const steps = 20;
            const increment = difference / steps;
            let current = currentValue;
            let step = 0;

            const timer = setInterval(() => {
                step++;
                current += increment;
                element.textContent = Math.round(current).toLocaleString();

                if (step >= steps) {
                    clearInterval(timer);
                    element.textContent = targetValue.toLocaleString();
                }
            }, duration / steps);
        }

        // Listen for donations (Echo/Reverb)
        if (window.Echo) {
            window.Echo.channel('donations')
                .listen('DonationCreated', (e) => {
                    console.log('New donation received:', e);

                    // Add to feed
                    const feed = document.getElementById('donationFeed');
                    if (feed) {
                        const item = createDonationItem(e);
                        feed.insertBefore(item, feed.firstChild);
                    }

                    // Optional: Update counters
                    // updateCounters();

                    // Optional: Show notification
                    // showNotification(`New donation: $${e.amount} by ${e.user_name}`);
                });
        }
    </script>
@endpush
