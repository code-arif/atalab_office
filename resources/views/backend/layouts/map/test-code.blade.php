@extends('backend.app')

@section('title', 'Real-Time Location Tracking')

@section('content')
    <div class="app-content main-content mt-0">
        <div class="side-app">
            <div class="main-container container-fluid">
                <!-- Header -->
                <div class="page-header">
                    <div class="card shadow-sm mb-3 border-0">
                        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
                            <div>
                                <h1 class="page-title mb-1">
                                    <i class="fa fa-map-marker text-danger me-2"></i>
                                    Real-Time Location Tracking
                                </h1>
                                <p class="text-muted mb-0">Monitor team leaders' live location and work progress</p>
                            </div>
                            <div class="d-flex gap-2 align-items-center">
                                <button class="btn btn-primary" id="refreshLocations">
                                    <i class="fa fa-refresh"></i> Refresh
                                </button>
                                <button class="btn btn-outline-primary" id="fitBoundsBtn">
                                    <i class="fa fa-arrows-alt"></i> Fit All
                                </button>
                                <span class="badge bg-success fs-6 px-3 py-2" id="statusBadge">
                                    <i class="fa fa-circle pulse"></i> Live
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Map Container -->
                    <div class="col-xl-9 col-lg-8 col-12">
                        <div class="card shadow-sm">
                            <!-- Map Controls -->
                            <div class="card-header bg-white border-bottom">
                                <div class="d-flex justify-content-between align-items-center flex-wrap">
                                    <h3 class="card-title mb-0">
                                        <i class="fa fa-map text-primary me-2"></i>
                                        Live Map View
                                    </h3>

                                    <!-- Controls Row -->
                                    <div class="d-flex align-items-center flex-wrap gap-3">
                                        <!-- Team Filter -->
                                        <div class="d-flex align-items-center" style="margin-left:50px">
                                            <select class="form-select" id="teamFilter" style="min-width: 200px;">
                                                <option value="">All Teams</option>
                                                @foreach ($teams as $team)
                                                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Auto Refresh Toggle -->
                                        <div class="form-check form-switch mb-0 d-flex align-items-center" style="margin-left:50px">
                                            <input class="form-check-input custom-switch" type="checkbox" id="autoRefresh"
                                                checked>
                                            <label class="form-check-label ms-2 small fw-semibold" for="autoRefresh">
                                                Auto Refresh (10s)
                                            </label>
                                        </div>

                                        <!-- Show Routes Toggle -->
                                        <div class="form-check form-switch mb-0 d-flex align-items-center" style="margin-left:50px">
                                            <input class="form-check-input custom-switch" type="checkbox" id="showRoutes">
                                            <label class="form-check-label ms-2 small fw-semibold" for="showRoutes">
                                                Show Routes
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Map -->
                            <div class="card-body p-0">
                                <div id="map" style="height: 700px; width: 100%;"></div>
                            </div>

                            <!-- Map Legend -->
                            <div class="card-footer bg-light">
                                <div class="d-flex justify-content-around flex-wrap">
                                    <div class="legend-item">
                                        <span class="legend-marker" style="background: #28a745;"></span>
                                        <small>Work Completed</small>
                                    </div>
                                    <div class="legend-item">
                                        <span class="legend-marker" style="background: #ffc107;"></span>
                                        <small>Work Rescheduled</small>
                                    </div>
                                    <div class="legend-item">
                                        <span class="legend-marker" style="background: #007bff;"></span>
                                        <small>Work Pending</small>
                                    </div>
                                    <div class="legend-item">
                                        <span class="legend-marker" style="background: #dc3545;"></span>
                                        <small>Work In Progress</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-xl-3 col-lg-4 col-12">
                        <!-- Stats Cards -->
                        <div class="row mb-3">
                            <div class="col-6">
                                <div class="card shadow-sm">
                                    <div class="card-body text-center py-3">
                                        <i class="fa fa-users fa-2x text-primary mb-2"></i>
                                        <h3 class="mb-0" id="totalTeams">0</h3>
                                        <small class="text-muted">Active Teams</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="card shadow-sm">
                                    <div class="card-body text-center py-3">
                                        <i class="fa fa-user fa-2x text-success mb-2"></i>
                                        <h3 class="mb-0" id="totalLeaders">0</h3>
                                        <small class="text-muted">Team Leaders</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Active Teams List -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h4 class="card-title mb-0">
                                    <i class="fa fa-list-ul me-2"></i>
                                    Active Teams
                                </h4>
                            </div>
                            <div class="card-body p-2">
                                <input type="text" class="form-control mb-2" id="teamSearch"
                                    placeholder="🔍 Search teams...">
                            </div>
                            <div id="teamList" style="max-height: 550px; overflow-y: auto;">
                                <div class="text-center p-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    <p class="mt-2 mb-0">Loading teams...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    <div id="loadingOverlay" style="display: none;">
        <div class="spinner-border text-light" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --primary-color: #007bff;
            --success-color: #28a745;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
        }

        /* Pulse Animation */
        .pulse {
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

        /* Map Styling */
        #map {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 0;
        }

        /* Legend */
        .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 5px 10px;
        }

        .legend-marker {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            border: 3px solid white;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
            display: inline-block;
        }

        /* Team List Item */
        .team-item {
            transition: all 0.3s ease;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
        }

        .team-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }

        .team-item.active {
            background: linear-gradient(90deg, #e3f2fd 0%, #ffffff 100%);
            border-left: 4px solid var(--primary-color);
        }

        .team-avatar {
            width: 50px;
            height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            font-weight: bold;
            color: white;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--success-color) 100%);
        }

        /* Info Window Styling */
        .gm-style .gm-style-iw-c {
            border-radius: 10px;
            padding: 0;
        }

        .gm-style .gm-style-iw-d {
            overflow: auto !important;
        }

        /* Loading Overlay */
        #loadingOverlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        /* Responsive */
        @media (max-width: 768px) {
            #map {
                height: 500px !important;
            }

            .legend-item {
                font-size: 11px;
            }
        }
    </style>

    <style>
        /* Bigger, cleaner toggles */
        .custom-switch {
            width: 1.5rem;
            height: 1.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        /* Red active state */
        .custom-switch:checked {
            background-color: #dc3545;
            border-color: #dc3545;
        }

        /* Better label spacing & weight */
        .form-check-label {
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Responsive fix: keep everything inline on desktop */
        @media (max-width: 767px) {
            .card-header .d-flex.flex-wrap {
                gap: 10px;
                justify-content: start !important;
            }
        }
    </style>
@endpush

@push('scripts')
    <script>
        let map;
        let markers = {};
        let infoWindows = {};
        let polylines = {};
        let workMarkers = {};
        let autoRefreshInterval;
        let selectedTeamId = null;
        let allLocationsData = [];

        // Initialize Google Map
        function initMap() {
            console.log('🗺️ Initializing Google Maps...');

            try {
                map = new google.maps.Map(document.getElementById('map'), {
                    center: {
                        lat: 23.8103,
                        lng: 90.4125
                    }, // Dhaka, Bangladesh
                    zoom: 12,
                    mapTypeId: 'roadmap',
                    mapTypeControl: true,
                    mapTypeControlOptions: {
                        style: google.maps.MapTypeControlStyle.DROPDOWN_MENU,
                        position: google.maps.ControlPosition.TOP_RIGHT
                    },
                    streetViewControl: true,
                    streetViewControlOptions: {
                        position: google.maps.ControlPosition.RIGHT_BOTTOM
                    },
                    fullscreenControl: true,
                    fullscreenControlOptions: {
                        position: google.maps.ControlPosition.RIGHT_TOP
                    },
                    zoomControl: true,
                    zoomControlOptions: {
                        position: google.maps.ControlPosition.RIGHT_CENTER
                    },
                    styles: [{
                        featureType: 'poi',
                        elementType: 'labels',
                        stylers: [{
                            visibility: 'on'
                        }]
                    }]
                });

                console.log('✅ Map initialized successfully');
            } catch (error) {
                console.error('❌ Map initialization error:', error);
                showError('Failed to load Google Maps. Please check your API key.');
            }
        }

        // Fetch Locations from Server
        function fetchLocations() {
            console.log('📡 Fetching locations...');
            updateStatusBadge('updating');

            const teamId = $('#teamFilter').val();
            const url = '{{ route('admin.tracking.locations') }}' + (teamId ? `?team_id=${teamId}` : '');

            $.ajax({
                url: url,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'Accept': 'application/json'
                },
                success: function(response) {
                    if (response.success) {
                        console.log(`✅ Received ${response.data.length} locations`);
                        allLocationsData = response.data;
                        updateMap(response.data);
                        updateTeamList(response.data);
                        updateStatusBadge('live');
                    } else {
                        console.error('❌ Response not successful:', response);
                        updateStatusBadge('error');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ AJAX Error:', {
                        xhr,
                        status,
                        error
                    });
                    updateStatusBadge('error');
                    showError('Failed to load locations. Please try again.');
                }
            });
        }

        // Update Map with Locations
        function updateMap(locations) {
            if (!map) {
                console.error('❌ Map not initialized');
                return;
            }

            const bounds = new google.maps.LatLngBounds();
            let hasLocations = false;

            // Clear old markers
            Object.keys(markers).forEach(key => {
                if (!locations.find(l => `${l.team_id}_${l.user_id}` === key)) {
                    markers[key].setMap(null);
                    delete markers[key];
                    if (infoWindows[key]) delete infoWindows[key];
                }
            });

            // Add/Update Markers
            locations.forEach(location => {
                const key = `${location.team_id}_${location.user_id}`;
                const position = {
                    lat: parseFloat(location.latitude),
                    lng: parseFloat(location.longitude)
                };

                bounds.extend(position);
                hasLocations = true;

                if (markers[key]) {
                    // Update existing marker
                    markers[key].setPosition(position);
                } else {
                    // Create new marker
                    const marker = new google.maps.Marker({
                        position: position,
                        map: map,
                        title: `${location.user_name} (${location.team_name})`,
                        animation: google.maps.Animation.DROP,
                        icon: createCustomMarker(location.team_id, location.is_leader)
                    });

                    const infoWindow = new google.maps.InfoWindow({
                        content: createInfoWindowContent(location)
                    });

                    marker.addListener('click', () => {
                        closeAllInfoWindows();
                        infoWindow.open(map, marker);
                    });

                    markers[key] = marker;
                    infoWindows[key] = infoWindow;
                }

                // Update info window content
                if (infoWindows[key]) {
                    infoWindows[key].setContent(createInfoWindowContent(location));
                }
            });

            // Fit bounds if no team selected
            if (hasLocations && !selectedTeamId) {
                map.fitBounds(bounds);
                if (map.getZoom() > 15) map.setZoom(15);
            }

            updateStats(locations);

            // Load routes if enabled
            if ($('#showRoutes').is(':checked')) {
                loadTeamRoutes();
            }
        }

        // Create Custom Marker Icon
        function createCustomMarker(teamId, isLeader) {
            const color = getTeamColor(teamId);
            const size = isLeader ? 16 : 12;

            return {
                path: google.maps.SymbolPath.CIRCLE,
                scale: size,
                fillColor: color,
                fillOpacity: 1,
                strokeColor: '#ffffff',
                strokeWeight: isLeader ? 4 : 3,
                anchor: new google.maps.Point(0, 0)
            };
        }

        // Create Info Window Content
        function createInfoWindowContent(location) {
            const time = new Date(location.tracked_at);
            const timeStr = time.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });

            const speed = location.speed ? parseFloat(location.speed).toFixed(1) + ' km/h' : 'N/A';
            const accuracy = location.accuracy ? Math.round(location.accuracy) + 'm' : 'N/A';
            const battery = location.battery_level || 'N/A';
            const teamColor = getTeamColor(location.team_id);
            const leaderBadge = location.is_leader ?
                '<span style="background: #ffc107; color: #000; padding: 2px 6px; border-radius: 3px; font-size: 11px; margin-left: 5px;">LEADER</span>' :
                '';

            return '<div style="padding: 15px; min-width: 250px; font-family: Arial, sans-serif;">' +
                '<h5 style="margin: 0 0 10px 0; color: ' + teamColor + '; border-bottom: 2px solid ' + teamColor +
                '; padding-bottom: 8px;">' +
                '<i class="fa fa-user-circle"></i> ' + location.user_name + ' ' + leaderBadge +
                '</h5>' +
                '<div style="font-size: 13px; line-height: 1.8;">' +
                '<div><strong>📋 Team:</strong> ' + location.team_name + '</div>' +
                '<div><strong>🕐 Time:</strong> ' + timeStr + '</div>' +
                '<div><strong>⚡ Speed:</strong> ' + speed + '</div>' +
                '<div><strong>🎯 Accuracy:</strong> ' + accuracy + '</div>' +
                '<div><strong>🔋 Battery:</strong> ' + battery + '</div>' +
                '</div>' +
                '</div>';
        }

        // Load Team Routes
        function loadTeamRoutes() {
            const teamId = selectedTeamId || $('#teamFilter').val();
            if (!teamId) return;

            $.ajax({
                url: `/admin/tracking/team/${teamId}/route`,
                method: 'GET',
                success: function(response) {
                    if (response.success) {
                        drawTeamRoute(response.data.route, teamId);
                        drawWorkMarkers(response.data.works);
                    }
                },
                error: function(error) {
                    console.error('Failed to load route:', error);
                }
            });
        }

        // Draw Team Route (Polyline)
        function drawTeamRoute(locations, teamId) {
            // Clear existing polyline
            if (polylines[teamId]) {
                polylines[teamId].setMap(null);
            }

            if (locations.length < 2) return;

            const path = locations.map(loc => ({
                lat: parseFloat(loc.latitude),
                lng: parseFloat(loc.longitude)
            }));

            const polyline = new google.maps.Polyline({
                path: path,
                geodesic: true,
                strokeColor: getTeamColor(teamId),
                strokeOpacity: 0.8,
                strokeWeight: 4,
                map: map
            });

            polylines[teamId] = polyline;
        }

        // Draw Work Markers
        function drawWorkMarkers(works) {
            // Clear existing work markers
            Object.values(workMarkers).forEach(marker => marker.setMap(null));
            workMarkers = {};

            works.forEach(work => {
                const status = work.is_completed ? 'completed' :
                    (work.is_rescheduled ? 'rescheduled' :
                        (work.tracking_status === 'in_progress' ? 'in_progress' : 'pending'));

                const color = getWorkColor(status);
                const statusText = status.replace('_', ' ').toUpperCase();

                const marker = new google.maps.Marker({
                    position: {
                        lat: parseFloat(work.latitude),
                        lng: parseFloat(work.longitude)
                    },
                    map: map,
                    icon: {
                        path: google.maps.SymbolPath.CIRCLE,
                        scale: 10,
                        fillColor: color,
                        fillOpacity: 1,
                        strokeColor: '#ffffff',
                        strokeWeight: 3
                    },
                    title: work.title
                });

                const infoContent = '<div style="padding: 10px;">' +
                    '<h6 style="color: ' + color + '; margin-bottom: 8px;">' + work.title + '</h6>' +
                    '<p style="margin: 0; font-size: 13px;">' + (work.description || 'No description') + '</p>' +
                    '<div style="margin-top: 8px; padding-top: 8px; border-top: 1px solid #ddd;">' +
                    '<strong>Status:</strong> ' +
                    '<span style="color: ' + color + '; text-transform: uppercase; font-weight: bold;">' +
                    statusText +
                    '</span>' +
                    '</div>' +
                    '</div>';

                const infoWindow = new google.maps.InfoWindow({
                    content: infoContent
                });

                marker.addListener('click', () => {
                    closeAllInfoWindows();
                    infoWindow.open(map, marker);
                });

                workMarkers[work.id] = marker;
            });
        }

        // Get Work Status Color
        function getWorkColor(status) {
            const colors = {
                'completed': '#28a745',
                'rescheduled': '#ffc107',
                'in_progress': '#dc3545',
                'pending': '#007bff'
            };
            return colors[status] || '#6c757d';
        }

        // Update Team List
        function updateTeamList(locations) {
            const teamData = {};

            locations.forEach(loc => {
                if (!teamData[loc.team_id]) {
                    teamData[loc.team_id] = {
                        name: loc.team_name,
                        leaders: []
                    };
                }
                teamData[loc.team_id].leaders.push(loc);
            });

            let html = '';
            Object.keys(teamData).forEach(teamId => {
                const team = teamData[teamId];
                const lastUpdate = new Date(team.leaders[0].tracked_at);
                const timeAgo = getTimeAgo(lastUpdate);
                const teamColor = getTeamColor(teamId);

                html += '<div class="team-item p-3" data-team-id="' + teamId + '">' +
                    '<div class="d-flex align-items-center">' +
                    '<div class="team-avatar me-3" style="background: ' + teamColor + ';">' +
                    team.name.charAt(0).toUpperCase() +
                    '</div>' +
                    '<div class="flex-fill">' +
                    '<strong class="d-block">' + team.name + '</strong>' +
                    '<small class="text-muted">' +
                    '<i class="fa fa-user-circle"></i> ' + team.leaders.length + ' Leader(s)' +
                    '</small>' +
                    '<div class="text-muted small mt-1">' +
                    '<i class="fa fa-clock-o"></i> ' + timeAgo +
                    '</div>' +
                    '</div>' +
                    '<span class="badge bg-success">' + team.leaders.length + '</span>' +
                    '</div>' +
                    '</div>';
            });

            $('#teamList').html(html || '<div class="text-center p-4 text-muted">No active teams</div>');

            // Click handler
            $('.team-item').click(function() {
                const teamId = $(this).data('team-id');
                selectedTeamId = teamId;
                $('#teamFilter').val(teamId);
                fetchLocations();

                $('.team-item').removeClass('active');
                $(this).addClass('active');
            });
        }

        // Update Statistics
        function updateStats(locations) {
            const teams = new Set(locations.map(l => l.team_id));
            $('#totalTeams').text(teams.size);
            $('#totalLeaders').text(locations.length);
        }

        // Get Team Color
        function getTeamColor(teamId) {
            const colors = [
                '#007bff', '#28a745', '#dc3545', '#ffc107',
                '#17a2b8', '#6f42c1', '#fd7e14', '#20c997'
            ];
            return colors[teamId % colors.length];
        }

        // Get Time Ago
        function getTimeAgo(date) {
            const seconds = Math.floor((new Date() - date) / 1000);

            if (seconds < 60) return seconds + 's ago';
            if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
            if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago';
            return Math.floor(seconds / 86400) + 'd ago';
        }

        // Close All Info Windows
        function closeAllInfoWindows() {
            Object.values(infoWindows).forEach(iw => iw.close());
        }

        // Update Status Badge
        function updateStatusBadge(status) {
            const badges = {
                'live': '<i class="fa fa-circle pulse"></i> Live',
                'updating': '<i class="fa fa-refresh fa-spin"></i> Updating...',
                'error': '<i class="fa fa-exclamation-circle"></i> Error'
            };
            $('#statusBadge').html(badges[status]);
            $('#statusBadge').removeClass().addClass('badge fs-6 px-3 py-2');
            $('#statusBadge').addClass(status === 'live' ? 'bg-success' :
                status === 'updating' ? 'bg-info' : 'bg-danger');
        }

        // Show Error Message
        function showError(message) {
            $('#teamList').html(
                '<div class="text-center p-4 text-danger">' +
                '<i class="fa fa-exclamation-triangle fa-3x mb-3"></i>' +
                '<p class="mb-0">' + message + '</p>' +
                '</div>'
            );
        }

        // Setup Auto Refresh
        function setupAutoRefresh() {
            clearInterval(autoRefreshInterval);

            if ($('#autoRefresh').is(':checked')) {
                autoRefreshInterval = setInterval(fetchLocations, 10000);
                console.log('✅ Auto-refresh enabled (10s)');
            } else {
                console.log('⏸️ Auto-refresh disabled');
            }
        }

        // Load Google Maps API
        function loadGoogleMaps() {
            if (typeof google !== 'undefined') {
                initializeApp();
            } else {
                const script = document.createElement('script');
                script.src =
                    'https://maps.googleapis.com/maps/api/js?key=AIzaSyBfGOjmqKtEBRsfVN9szUo_tac20wcI9HM&libraries=geometry&callback=initializeApp';
                script.async = true;
                script.defer = true;
                document.head.appendChild(script);
            }
        }

        // Initialize Application
        function initializeApp() {
            console.log('🚀 Initializing application...');
            initMap();
            fetchLocations();
            setupAutoRefresh();
        }

        // Document Ready
        $(document).ready(function() {
            // Event Handlers
            $('#autoRefresh').change(setupAutoRefresh);
            $('#refreshLocations').click(fetchLocations);
            $('#teamFilter').change(function() {
                selectedTeamId = $(this).val() || null;
                fetchLocations();
            });
            $('#showRoutes').change(function() {
                if ($(this).is(':checked')) {
                    loadTeamRoutes();
                } else {
                    Object.values(polylines).forEach(p => p.setMap(null));
                    Object.values(workMarkers).forEach(m => m.setMap(null));
                    polylines = {};
                    workMarkers = {};
                }
            });
            $('#fitBoundsBtn').click(function() {
                if (allLocationsData.length > 0) {
                    const bounds = new google.maps.LatLngBounds();
                    allLocationsData.forEach(loc => {
                        bounds.extend({
                            lat: parseFloat(loc.latitude),
                            lng: parseFloat(loc.longitude)
                        });
                    });
                    map.fitBounds(bounds);
                }
            });
            $('#teamSearch').on('keyup', function() {
                const search = $(this).val().toLowerCase();
                $('.team-item').each(function() {
                    const text = $(this).text().toLowerCase();
                    $(this).toggle(text.includes(search));
                });
            });

            // Load Maps
            loadGoogleMaps();
        });

        // Expose to global scope
        window.initializeApp = initializeApp;
    </script>
@endpush
