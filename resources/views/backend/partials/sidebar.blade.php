<!--APP-SIDEBAR-->
<div class="sticky">
    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
    <div class="app-sidebar" style="overflow: scroll">
        <div class="side-header">
            <a class="header-brand1" href="{{ route('dashboard') }}">
                <img src="{{ asset($settings->logo ?? 'default/logo.png') }}" class="header-brand-img desktop-logo"
                    alt="logo">
                <img src="{{ asset($settings->logo ?? 'default/logo.png') }}" class="header-brand-img toggle-logo"
                    alt="logo">
                <img src="{{ asset($settings->logo ?? 'default/logo.png') }}" class="header-brand-img light-logo"
                    alt="logo">
                <img src="{{ asset($settings->logo ?? 'default/logo.png') }}" class="header-brand-img light-logo1"
                    alt="logo">
            </a>
        </div>
        <div class="main-sidemenu">
            <div class="slide-left disabled" id="slide-left"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z" />
                </svg>
            </div>

            <ul class="side-menu mt-2">
                <li>
                    <h3>Menu</h3>
                </li>

                {{-- Dashboard --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('dashboard') ? 'has-link' : '' }}"
                        href="{{ route('dashboard') }}">
                        <i class="fa fa-dashboard"></i>
                        <span class="side-menu__label">Dashboard</span>
                    </a>
                </li>

                {{-- Employee Manage --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('employee.list') ? 'has-link' : '' }}"
                        href="{{ route('employee.list') }}">
                        <i class="fa fa-user"></i>
                        <span class="side-menu__label">Employee Manage</span>
                    </a>
                </li>

                {{-- Team Manage --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('team.list') ? 'has-link' : '' }}"
                        href="{{ route('team.list') }}">
                        <i class="fa fa-users"></i>
                        <span class="side-menu__label">Team Manage</span>
                    </a>
                </li>

                {{-- Work Manage --}}
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <i class="fa fa-lightbulb"></i>
                        <span class="side-menu__label">Work Manage</span>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('work.list') }}" class="slide-item">Work List</a></li>
                        <li><a href="{{ route('reschedule.work.list') }}" class="slide-item">Reschedule Request</a>
                        </li>
                    </ul>
                </li>

                {{-- Calendar --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('calendar.index') ? 'has-link' : '' }}"
                        href="{{ route('calendar.index') }}">
                        <i class="fa fa-calendar"></i>
                        <span class="side-menu__label">Google Calendar</span>
                    </a>
                </li>

                {{-- Map --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('map.global') ? 'has-link' : '' }}"
                        href="{{ route('map.global') }}">
                        <i class="fa fa-map"></i>
                        <span class="side-menu__label">Google Map</span>
                    </a>
                </li>

                {{-- track team --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.tracking.index') ? 'has-link' : '' }}"
                        href="{{ route('admin.tracking.index') }}">
                        <i class="fe fe-map-pin"></i>
                        <span class="side-menu__label">Track Teams</span>
                    </a>
                </li>

                {{-- Settings --}}
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <i class="fa fa-cog"></i>
                        <span class="side-menu__label">Settings</span>
                        <i class="angle fa fa-angle-right ms-auto"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('setting.general.index') }}" class="slide-item">General Settings</a></li>
                        <li><a href="{{ route('setting.profile.index') }}" class="slide-item">Profile Settings</a></li>
                    </ul>
                </li>
            </ul>


            <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" />
                </svg>
            </div>
        </div>
    </div>
</div>
<!--/APP-SIDEBAR-->


{{-- sidebar style --}}
<style>
    /* Base menu item styling */
    .side-menu__item {
        display: flex;
        align-items: center;
        padding: 10px 15px;
        color: #333;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .side-menu__item:hover {
        background-color: #f3f6f9;
        color: #38a3a5;
    }

    /* Icon alignment fix */
    .side-menu__item i,
    .side-menu__item svg {
        width: 20px;
        height: 20px;
        flex-shrink: 0;
        display: inline-block;
        text-align: center;
        margin-right: 10px;
        /* consistent spacing */
        color: inherit;
    }

    /* Label */
    .side-menu__label {
        flex: 1;
        display: inline-block;
    }

    /* Submenu items */
    .slide-menu .slide-item {
        display: block;
        color: #555;
        font-size: 14px;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .slide-menu .slide-item:hover {
        color: #38a3a5;
    }

    /* Optional: heading styling */
    .side-menu h3 {
        font-size: 13px;
        text-transform: uppercase;
        margin: 20px 15px 10px;
        color: #777;
        letter-spacing: 0.5px;
    }
</style>
