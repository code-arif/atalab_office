@extends('backend.app')

@section('title', 'Dashboard')

@section('content')
    <!--app-content open-->
    <div class="app-content main-content mt-0">
        <div class="side-app">

            <!-- CONTAINER -->
            <div class="main-container container-fluid">
                <!-- PAGE-HEADER -->
                <div class="page-header">
                    <div>
                        <h1 class="page-title">Dashboard</h1>
                    </div>
                    <div class="ms-auto pageheader-btn">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="javascript:void(0);">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                        </ol>
                    </div>
                </div>
                <!-- PAGE-HEADER END -->

                <!-- ROW-1 -->
                <div class="row">
                    <!-- Total Employees -->
                    <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
                        <a href="{{ route('employee.list') }}" class="clickable-card">
                            <div class="card overflow-hidden">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <h3 class="mb-2 fw-semibold">{{ $totalEmployees }}</h3>
                                            <p class="text-muted fs-13 mb-0">Total Employees</p>
                                        </div>
                                        <div class="col col-auto top-icn dash">
                                            <div class="counter-icon bg-success dash ms-auto box-shadow-success">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="fill-white"
                                                    viewBox="0 0 16 16">
                                                    <path
                                                        d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm-5 6s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zM11 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 0 1h-4a.5.5 0 0 1-.5-.5zm.5 2.5a.5.5 0 0 0 0 1h4a.5.5 0 0 0 0-1h-4zm2 3a.5.5 0 0 0 0 1h2a.5.5 0 0 0 0-1h-2z" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Total Works -->
                    <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
                        <a href="{{ route('work.list') }}" class="clickable-card">
                            <div class="card overflow-hidden">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <h3 class="mb-2 fw-semibold">{{ $totalWorks }}</h3>
                                            <p class="text-muted fs-13 mb-0">Total Works</p>
                                        </div>
                                        <div class="col col-auto top-icn dash">
                                            <div class="counter-icon bg-info dash ms-auto box-shadow-info">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="fill-white"
                                                    viewBox="0 0 16 16">
                                                    <path
                                                        d="M9.5 13a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0zm0-5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0z" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Rescheduled Works -->
                    <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
                        <a href="{{ route('reschedule.work.list') }}" class="clickable-card">
                            <div class="card overflow-hidden">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <h3 class="mb-2 fw-semibold">{{ $rescheduledWorks }}</h3>
                                            <p class="text-muted fs-13 mb-0">Rescheduled Works</p>
                                        </div>
                                        <div class="col col-auto top-icn dash">
                                            <div class="counter-icon bg-danger dash ms-auto box-shadow-danger">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="fill-white"
                                                    viewBox="0 0 16 16">
                                                    <path
                                                        d="M11 5.5a.5.5 0 0 1 .5-.5h.5a.5.5 0 0 1 0 1h-.5a.5.5 0 0 1-.5-.5zm-2-3a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm-2 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5zm3 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 0 1h-1a.5.5 0 0 1-.5-.5z" />
                                                    <path
                                                        d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>

                    <!-- Total Teams -->
                    <div class="col-lg-6 col-sm-12 col-md-6 col-xl-3">
                        <a href="{{ route('team.list') }}" class="clickable-card">
                            <div class="card overflow-hidden">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col">
                                            <h3 class="mb-2 fw-semibold">{{ $totalTeams }}</h3>
                                            <p class="text-muted fs-13 mb-0">Total Teams</p>
                                        </div>
                                        <div class="col col-auto top-icn dash">
                                            <div class="counter-icon bg-purple dash ms-auto box-shadow-purple">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="fill-white"
                                                    viewBox="0 0 16 16">
                                                    <path
                                                        d="M7 14s-1 0-1-1 1-4 5-4 5 3 5 4-1 1-1 1H7Zm4-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Zm-5.784 6A2.238 2.238 0 0 1 5 13c0-1.355.68-2.75 1.936-3.72A6.325 6.325 0 0 0 5 9c-4 0-5 3-5 4s1 1 1 1h4.216ZM4.5 8a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z" />
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- ROW: Work & Team Analytics -->
                <div class="row">
                    <!-- Work Completion Pie -->
                    <div class="col-sm-12 col-md-6 col-lg-6">
                        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title">Work Completion</h3>
                            </div>
                            <div class="card-body">
                                <div id="work-completion-piechart" style="width: 100%; height: 400px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Teams Bar Chart -->
                    <div class="col-sm-12 col-md-6 col-lg-6">
                        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                            <div class="card-header bg-success text-white">
                                <h3 class="card-title">Top Teams (Last 30 Days)</h3>
                            </div>
                            <div class="card-body">
                                <div id="top-teams-barchart" style="width: 100%; height: 400px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- CONTAINER CLOSED -->
@endsection

@push('scripts')
    <!-- Load Google Charts -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Load Google Charts
            google.charts.load('current', {
                'packages': ['corechart', 'bar']
            });
            google.charts.setOnLoadCallback(fetchDashboardData);

            function fetchDashboardData() {
                fetch('/admin/dashboard/data')
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        const contentType = response.headers.get('content-type');
                        if (!contentType || !contentType.includes('application/json')) {
                            throw new Error("Response wasn't JSON");
                        }
                        return response.json();
                    })
                    .then(data => {
                        // Draw all charts
                        drawWorkCompletionPieChart(data.work_completion);
                        drawTopTeamsBarChart(data.top_teams_by_work);
                    })
                    .catch(error => {
                        console.error('Error fetching data:', error);
                        alert('Failed to load dashboard data. Please try again later.');
                    });
            }

            // Work Completion Pie Chart
            function drawWorkCompletionPieChart(data) {
                const chartData = [
                    ['Status', 'Count'],
                    ['Completed', data.completed],
                    ['Incomplete', data.incomplete]
                ];
                const gData = google.visualization.arrayToDataTable(chartData);
                const options = {
                    pieHole: 0.5,
                    is3D: true,
                    colors: ['#28a745', '#dc3545'],
                    legend: {
                        position: 'bottom',
                        textStyle: {
                            color: '#333',
                            fontSize: 14,
                            fontName: 'Roboto'
                        }
                    },
                    chartArea: {
                        width: '90%',
                        height: '80%'
                    },
                    animation: {
                        startup: true,
                        duration: 1000,
                        easing: 'out'
                    },
                    tooltip: {
                        trigger: 'focus',
                        showColorCode: true,
                        textStyle: {
                            fontSize: 12
                        }
                    },
                    pieSliceText: 'percentage',
                    pieSliceTextStyle: {
                        color: '#fff',
                        fontSize: 12,
                        bold: true
                    },
                    backgroundColor: 'transparent'
                };
                const chart = new google.visualization.PieChart(document.getElementById(
                'work-completion-piechart'));
                // Add click event for interactivity
                google.visualization.events.addListener(chart, 'select', function() {
                    const selection = chart.getSelection();
                    if (selection.length) {
                        const row = selection[0].row;
                        const status = chartData[row + 1][0];
                        console.log(`Clicked on ${status}: ${chartData[row + 1][1]} works`);
                        // Optionally redirect to a filtered works page, e.g.:
                        // window.location.href = `/works?status=${status.toLowerCase()}`;
                    }
                });
                chart.draw(gData, options);
            }

            // Top Teams Bar Chart
            function drawTopTeamsBarChart(data) {
                const rows = Object.entries(data).map(([team, count]) => [team, count]);
                const chartData = [
                    ['Team', 'Works', {
                        role: 'style'
                    }]
                ].concat(rows.map(([team, count]) => [team, count, '#17a2b8']));
                const gData = google.visualization.arrayToDataTable(chartData);
                const options = {
                    legend: {
                        position: 'none'
                    },
                    hAxis: {
                        title: 'Number of Works',
                        titleTextStyle: {
                            color: '#333',
                            fontSize: 14,
                            bold: true
                        },
                        textStyle: {
                            color: '#555',
                            fontSize: 12
                        },
                        gridlines: {
                            color: '#e9ecef'
                        }
                    },
                    vAxis: {
                        title: 'Team',
                        titleTextStyle: {
                            color: '#333',
                            fontSize: 14,
                            bold: true
                        },
                        textStyle: {
                            color: '#555',
                            fontSize: 12
                        }
                    },
                    chartArea: {
                        width: '70%',
                        height: '80%'
                    },
                    animation: {
                        startup: true,
                        duration: 1000,
                        easing: 'out'
                    },
                    tooltip: {
                        trigger: 'focus',
                        showColorCode: true,
                        textStyle: {
                            fontSize: 12
                        }
                    },
                    bar: {
                        groupWidth: '50%'
                    },
                    backgroundColor: 'transparent'
                };
                const chart = new google.visualization.BarChart(document.getElementById('top-teams-barchart'));
                // Add click event for interactivity
                google.visualization.events.addListener(chart, 'select', function() {
                    const selection = chart.getSelection();
                    if (selection.length) {
                        const row = selection[0].row;
                        const team = chartData[row + 1][0];
                        console.log(`Clicked on team ${team}: ${chartData[row + 1][1]} works`);
                        // Optionally redirect to a team-specific page, e.g.:
                        // window.location.href = `/teams/${team}`;
                    }
                });
                chart.draw(gData, options);
            }
        });
    </script>

    <!-- Inline CSS for additional styling -->
    <style>
        .card {
            transition: transform 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
        }

        .card-header {
            padding: 1rem;
            border-bottom: none;
        }

        .card-title {
            font-size: 1.25rem;
            font-weight: 600;
        }

        .card-body {
            padding: 1.5rem;
        }
    </style>
@endpush


@push('styles')
    <style>
        .clickable-card {
            display: block;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
        }

        .clickable-card .card {
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        .clickable-card:hover .card {
            background-color: #13bfa6 !important;
            color: #fff !important;
            transform: translateY(-3px);
        }

        .clickable-card:hover p,
        .clickable-card:hover h3,
        .clickable-card:hover svg path {
            color: #fff !important;
            fill: #fff !important;
        }
    </style>
@endpush
