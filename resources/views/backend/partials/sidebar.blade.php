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
                        <span class="side-menu__label mb-1">Dashboard</span>
                    </a>
                </li>

                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <i class="fa-solid fa-people-roof"></i>
                        <span class="mb-1">Donors & Draws</span>
                        <i class="angle fa fa-angle-right ms-auto"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('weekly-draws.index') }}" class="slide-item">Weekly Draw</a></li>
                        <li><a href="{{ route('donors.index') }}" class="slide-item">Donors</a></li>
                        <li><a href="{{ route('draw-winners.index') }}" class="slide-item">Winners</a></li>
                    </ul>
                </li>

                {{-- chatting --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('chat') ? 'has-link' : '' }}"
                        href="{{ route('chat.index') }}">
                        <i class="fa-regular fa-comment-dots"></i>
                        <span class="side-menu__label mb-1">Chat</span>
                    </a>
                </li>

                <hr>
                {{-- subscribe newsletter --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('subscribers.index') ? 'has-link' : '' }}"
                        href="{{ route('subscribers.index') }}">
                        <i class="fa-solid fa-bell"></i>
                        <span class="side-menu__label mb-1">Newsletter Subscribers</span>
                    </a>
                </li>

                {{-- contact me --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('contact.me') ? 'has-link' : '' }}"
                        href="{{ route('contact.me') }}">
                        <i class="fa fa-envelope"></i>
                        <span class="side-menu__label mb-1">Contact Me</span>
                    </a>
                </li>

                <hr>
                <li>
                    <h3>CMS</h3>
                </li>

                {{-- Cms Manage --}}

                {{-- home page --}}
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <i class="fa fa-home"></i>
                        <span class="mb-1">Home Page</span>
                        <i class="angle fa fa-angle-right ms-auto"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('cms.home.hero.section') }}" class="slide-item">Hero Section</a></li>
                        <li><a href="{{ route('cms.home.distribution.section') }}" class="slide-item">Distribution
                                Table</a>
                        <li><a href="{{ route('cms.home.video.section') }}" class="slide-item">Video</a>
                        </li>
                        <li><a href="{{ route('cms.home.percentage.section') }}" class="slide-item">Percentage</a></li>
                        <li><a href="{{ route('cms.home.selected_name.section') }}" class="slide-item">Selected
                                Name</a></li>
                        <li><a href="{{ route('cms.home.quote.section') }}" class="slide-item">Quote</a></li>
                        <li><a href="{{ route('cms.home.our_story.section') }}" class="slide-item">Our Story</a></li>
                        <li><a href="{{ route('cms.home.testimonial.section') }}" class="slide-item">Testimonial</a>
                        </li>
                        <li><a href="{{ route('cms.home.gallery.section') }}" class="slide-item">Gallery</a></li>
                        <li><a href="{{ route('cms.home.disclaimer.section') }}" class="slide-item">Disclaimer</a>
                        </li>
                        <li><a href="{{ route('cms.home.we_believe.section') }}" class="slide-item">We Believe</a>
                        </li>
                        <li><a href="{{ route('cms.home.founder_statement.section') }}" class="slide-item">Founder
                                Statement</a>
                        </li>
                    </ul>
                </li>

                {{-- Our story page --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('cms.our_story.hero.section') ? 'has-link' : '' }}"
                        href="{{ route('cms.our_story.hero.section') }}">
                        <i class="fa-solid fa-store"></i>
                        <span class="side-menu__label mb-1">Our Story Page</span>
                    </a>
                </li>

                {{-- how it works --}}
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <i class="fa-solid fa-briefcase"></i>
                        <span class="mb-1">How It Works Page</span>
                        <i class="angle fa fa-angle-right ms-auto"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('cms.how_it_works.hero.section') }}" class="slide-item">How It
                                Works</a>
                        </li>
                        <li><a href="{{ route('cms.structure.hero.section') }}" class="slide-item">Structure</a>
                        </li>
                    </ul>
                </li>

                {{-- Eligibility page --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('cms.eligibility.hero.section') ? 'has-link' : '' }}"
                        href="{{ route('cms.eligibility.hero.section') }}">
                        <i class="fa-solid fa-yin-yang"></i>
                        <span class="side-menu__label mb-1">Eligibility Page</span>
                    </a>
                </li>

                {{-- Payment Policy page --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('cms.payment_policy.hero.section') ? 'has-link' : '' }}"
                        href="{{ route('cms.payment_policy.hero.section') }}">
                        <i class="fa-solid fa-cart-shopping"></i>
                        <span class="side-menu__label mb-1">Payment Policy Page</span>
                    </a>
                </li>

                {{-- tax policy --}}
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <i class="fa-solid fa-tower-observation"></i>
                        <span class="mb-1">Tax Policy Page</span>
                        <i class="angle fa fa-angle-right ms-auto"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('cms.tax_policy.hero.section') }}" class="slide-item">Tax Policy</a>
                        </li>
                        <li><a href="{{ route('cms.ethical_boundaries.hero.section') }}" class="slide-item">Ethical
                                boundaries</a>
                        </li>
                    </ul>
                </li>

                {{-- Officers Comp --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('cms.officer_compensation.hero.section') ? 'has-link' : '' }}"
                        href="{{ route('cms.officer_compensation.hero.section') }}">
                        <i class="fa-solid fa-building"></i>
                        <span class="side-menu__label mb-1">Officers Comp Page</span>
                    </a>
                </li>

                {{-- Archives Comp --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('cms.archive.hero.section') ? 'has-link' : '' }}"
                        href="{{ route('cms.archive.hero.section') }}">
                        <i class="fa-solid fa-box-archive"></i>
                        <span class="side-menu__label mb-1">Archives Page</span>
                    </a>
                </li>

                {{-- Contact Us Comp --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('cms.contact_us.hero.section') ? 'has-link' : '' }}"
                        href="{{ route('cms.contact_us.hero.section') }}">
                        <i class="fa-solid fa-address-book"></i>
                        <span class="side-menu__label mb-1">Contact Us Page</span>
                    </a>
                </li>


                {{-- topbar, footer --}}
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <i class="fa-solid fa-file-half-dashed"></i>
                        <span class="side-menu__label mb-1">Partials</span>
                        <i class="angle fa fa-angle-right ms-auto"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('cms.topbar.section') }}" class="slide-item">Topbar</a>
                        </li>
                        <li><a href="{{ route('cms.footer.section') }}" class="slide-item">Footer</a>
                        </li>
                    </ul>
                </li>

                <hr>

                {{-- settings --}}
                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <i class="fa fa-cog"></i>
                        <span class="side-menu__label mb-2">Settings</span>
                        <i class="angle fa fa-angle-right ms-auto"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('setting.general.index') }}" class="slide-item">General Settings</a>
                        </li>
                        <li><a href="{{ route('setting.profile.index') }}" class="slide-item">Profile Settings</a>
                        </li>
                        <li><a href="{{ route('setting.stripe.index') }}" class="slide-item">Stripe Settings</a>
                        </li>
                        <li><a href="{{ route('setting.draw-automate.index') }}" class="slide-item">Draw
                                Automation</a>
                        </li>
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
        padding: 9px 14px;
        margin: 4px 12px;
        border-radius: 6px;
        color: #4b5563;
        font-weight: 500;
        font-size: 0.9rem;
        text-decoration: none;
        transition: all 0.2s ease;
        position: relative;
        border: 1px solid transparent;
        /* Prevent layout shift */
    }

    /* White Background with Colored Border on Hover */
    .side-menu__item:hover {
        background-color: #ffffff;
        color: #5066e1;
        border-color: #5066e1;
        /* A slightly brighter blue-purple border for interaction contrast */
        transition: all 0.2s ease !important;
    }

    /* Active State - matched to standard purple-blue theme */
    .side-menu__item.has-link,
    .side-menu__item.active,
    .slide.is-expanded .side-menu__item {
        background-color: #f0f2fe;
        /* Light purple-blue */
        color: #5066e1;
        /* Theme purple-blue */
        font-weight: 600;
    }

    /* Active Left Border - subtle rounded pill */
    .side-menu__item.has-link::after,
    .side-menu__item.active::after {
        content: '';
        position: absolute;
        left: -12px;
        top: 50%;
        transform: translateY(-50%);
        height: 18px;
        width: 4px;
        background-color: #5066e1;
        border-radius: 4px;
    }

    /* Icon styling alignment fix */
    .side-menu__item i,
    .side-menu__item svg,
    .side-menu__item [class^="fa-"] {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        font-size: 16px;
        flex-shrink: 0;
        margin-right: 12px;
        color: inherit;
        opacity: 0.8;
        transition: opacity 0.2s ease, color 0.2s ease;
    }

    .side-menu__item:hover i,
    .side-menu__item.has-link i,
    .side-menu__item.active i,
    .slide.is-expanded .side-menu__item i {
        opacity: 1;
    }

    /* Label perfectly aligned */
    .side-menu__item .side-menu__label,
    .side-menu__item .mb-1 {
        flex: 1;
        display: flex;
        align-items: center;
        white-space: nowrap;
        margin-bottom: 0 !important;
        margin-top: 1px;
    }

    /* Dropdown Arrow Indicator */
    .side-menu__item .angle {
        width: auto;
        margin-right: 0;
        font-size: 11px;
        opacity: 0.5;
        transition: transform 0.3s ease, opacity 0.3s ease;
    }

    .slide.is-expanded .side-menu__item .angle {
        transform: rotate(90deg);
        opacity: 1;
    }

    /* Submenu Wrapper */
    .slide-menu {
        padding: 4px 0 10px 48px;
        margin: 0;
        list-style: none;
        display: none;
        /* Inherit JS toggle visibility */
    }

    .slide.is-expanded .slide-menu {
        display: block;
    }

    /* Submenu items */
    .slide-menu .slide-item {
        display: block;
        padding: 7px 12px 7px 28px !important;
        color: #6b7280;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        border-radius: 6px;
        margin-bottom: 2px;
        margin-right: 15px;
        transition: all 0.25s ease;
        position: relative;
    }

    /* Remove base theme default icons cleanly */
    .slide-menu .slide-item::after {
        display: none !important;
    }

    /* Restore Custom Animated Dot Icon */
    .slide-menu .slide-item::before {
        content: '' !important;
        display: block !important;
        position: absolute !important;
        left: 10px !important;
        top: 50% !important;
        transform: translateY(-50%) !important;
        width: 6px !important;
        height: 6px !important;
        border-radius: 50% !important;
        background-color: #cbd3da !important;
        transition: all 0.3s ease !important;
        border: none !important;
        box-shadow: none !important;
    }

    /* Clean hover interaction */
    .slide-menu .slide-item:hover,
    .slide-menu .slide-item.active,
    .slide-menu .slide-item.has-link {
        color: #5066e1 !important;
        background-color: #f0f2fe !important;
        transform: translateX(4px);
    }

    /* Animated Dot Hover */
    .slide-menu .slide-item:hover::before,
    .slide-menu .slide-item.active::before,
    .slide-menu .slide-item.has-link::before {
        background-color: #5066e1 !important;
        box-shadow: 0 0 0 3px rgba(80, 102, 225, 0.2) !important;
    }

    /* Menu headings */
    .side-menu h3 {
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: 700;
        margin: 24px 20px 10px;
        color: #9ca3af;
        letter-spacing: 0.05em;
    }
</style>
