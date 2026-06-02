<!doctype html>
<html lang="en" data-layout="vertical" data-sidebar="dark" data-sidebar-size="lg" data-preloader="disable" data-theme="default" data-topbar="light" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    @php
        $store = \App\Models\StoreSetting::getSettings();
        $pageTitle = $pagetitle ?? 'Dashboard';
        $storeName = $store?->store_name ?? 'Frost Hub';
    @endphp
    <title>{{ $pageTitle }} | {{ $storeName }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Smart Inventory Management System" name="description">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Favicon -->
    @if($store && $store->logo)
        <link rel="shortcut icon" href="{{ $store->getLogoUrlAttribute() }}" type="image/png">
    @else
        <link rel="shortcut icon" href="{{ asset('theme/layouts/assets/images/favicon.ico') }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet">

    <!-- Core CSS -->
    <link href="{{ asset('theme/layouts/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/layouts/assets/css/icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/layouts/assets/css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/layouts/assets/css/custom.min.css') }}" rel="stylesheet">

    <!-- Layout JS -->
    <script src="{{ asset('theme/layouts/assets/js/layout.js') }}"></script>

    <style>
        /* Sidebar scrolling fix */
        .app-menu {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 250px;
            z-index: 1000;
        }
        #scrollbar {
            height: calc(100vh - 70px);
            overflow-y: auto;
            scrollbar-width: thin;
        }
        #scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        #scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
        }
        #scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 4px;
        }

        /* Active menu styles */
        #navbar-nav .nav-link.menu-link.nav-active-parent {
            color: #fff !important;
            background: rgba(79, 142, 247, 0.18) !important;
            border-left: 3px solid #4f8ef7;
        }
        #navbar-nav .nav-sm .nav-link.nav-active-child {
            color: #7eb8fb !important;
        }
        #navbar-nav .nav-sm .nav-link.nav-active-child::before {
            content: '';
            display: inline-block;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #4f8ef7;
            margin-right: 8px;
        }

        /* Chevron rotation */
        #navbar-nav .nav-link.menu-link .ri-arrow-down-s-line {
            transition: transform 0.25s ease;
        }
        #navbar-nav .nav-link.menu-link[aria-expanded="true"] .ri-arrow-down-s-line {
            transform: rotate(180deg);
        }

        /* Avatar hover */
        .header-profile-user-enhanced {
            transition: transform 0.25s ease;
        }
        .header-profile-user-enhanced:hover {
            transform: scale(1.07);
        }

        /* Page fade in */
        .page-content {
            animation: pageFadeIn 0.35s ease;
        }
        @keyframes pageFadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Back to top */
        #back-to-top {
            opacity: 0;
            visibility: hidden;
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 99;
            transition: all 0.3s ease;
        }
        #back-to-top.show {
            opacity: 1;
            visibility: visible;
        }

        /* Dropdown animations */
        .dropdown-menu {
            animation: dropdownFadeIn 0.2s ease;
        }
        @keyframes dropdownFadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body>

    <div id="layout-wrapper">

        <!-- Sidebar -->
        <div class="app-menu navbar-menu">
            <div class="navbar-brand-box">
                <a href="{{ route('dashboard') }}" class="logo logo-dark">
                    <span class="logo-lg">
                        @if($store && $store->logo)
                            <img src="{{ $store->getLogoUrlAttribute() }}" alt="{{ $storeName }}" height="60">
                        @else
                            <img src="{{ asset('theme/layouts/assets/images/logo-dark.png') }}" alt="Logo" height="60">
                        @endif
                    </span>
                </a>
                <button type="button" class="btn btn-sm p-0 fs-3xl header-item float-end btn-vertical-sm-hover" id="vertical-hover">
                    <i class="ri-record-circle-line"></i>
                </button>
            </div>

            <div id="scrollbar">
                <div class="container-fluid">
                    <ul class="navbar-nav" id="navbar-nav">
                        @php $user = Auth::user(); @endphp
                        @if($user)
                            <li class="menu-title"><span>Menu</span></li>

                            <!-- Dashboard -->
                            @can('dashboard')
                            <li class="nav-item">
                                <a class="nav-link menu-link {{ request()->routeIs('dashboard') ? 'nav-active-parent' : 'collapsed' }}"
                                   href="#sidebarDashboards" data-bs-toggle="collapse" role="button"
                                   aria-expanded="{{ request()->routeIs('dashboard') ? 'true' : 'false' }}">
                                    <i class="ph-gauge"></i> <span>Dashboards</span>
                                    <i class="ri-arrow-down-s-line ms-auto"></i>
                                </a>
                                <div class="collapse menu-dropdown {{ request()->routeIs('dashboard') ? 'show' : '' }}" id="sidebarDashboards">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="{{ route('dashboard') }}" class="nav-link">Administration Analytics</a>
                                        </li>
                                        @can('View inventory dashboard')
                                        <li class="nav-item">
                                            <a href="{{ route('inventory.dashboard') }}" class="nav-link">Inventory Dashboard</a>
                                        </li>
                                        @endcan
                                    </ul>
                                </div>
                            </li>
                            @endcan

                            <!-- Users & Privileges -->
                            @can('View user')
                            <li class="menu-title">USERS & PRIVILEGES</li>
                            <li class="nav-item">
                                <a class="nav-link menu-link {{ request()->routeIs('users.*') ? 'nav-active-parent' : 'collapsed' }}"
                                   href="#sidebarusers" data-bs-toggle="collapse" role="button"
                                   aria-expanded="{{ request()->routeIs('users.*') ? 'true' : 'false' }}">
                                    <i class="ph-user-circle"></i> <span>User Management</span>
                                    <i class="ri-arrow-down-s-line ms-auto"></i>
                                </a>
                                <div class="collapse menu-dropdown {{ request()->routeIs('users.*') ? 'show' : '' }}" id="sidebarusers">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="{{ route('users.index') }}" class="nav-link">Users</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            @endcan

                            @can('View role')
                            <li class="nav-item">
                                <a class="nav-link menu-link {{ request()->routeIs('roles.*') || request()->routeIs('permissions.*') ? 'nav-active-parent' : 'collapsed' }}"
                                   href="#sidebarroles" data-bs-toggle="collapse" role="button"
                                   aria-expanded="{{ request()->routeIs('roles.*') || request()->routeIs('permissions.*') ? 'true' : 'false' }}">
                                    <i class="ph-address-book"></i> <span>Roles & Permissions</span>
                                    <i class="ri-arrow-down-s-line ms-auto"></i>
                                </a>
                                <div class="collapse menu-dropdown {{ request()->routeIs('roles.*') || request()->routeIs('permissions.*') ? 'show' : '' }}" id="sidebarroles">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item"><a href="{{ route('roles.index') }}" class="nav-link">Roles</a></li>
                                        <li class="nav-item"><a href="{{ route('permissions.index') }}" class="nav-link">Permissions</a></li>
                                    </ul>
                                </div>
                            </li>
                            @endcan

                            <!-- My Account -->
                            <li class="nav-item">
                                <a class="nav-link menu-link {{ request()->routeIs('user.overview') ? 'nav-active-parent' : 'collapsed' }}"
                                   href="#sidebaraccount" data-bs-toggle="collapse" role="button"
                                   aria-expanded="{{ request()->routeIs('user.overview') ? 'true' : 'false' }}">
                                    <i class="ph-address-book"></i> <span>My Account</span>
                                    <i class="ri-arrow-down-s-line ms-auto"></i>
                                </a>
                                <div class="collapse menu-dropdown {{ request()->routeIs('user.overview') ? 'show' : '' }}" id="sidebaraccount">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item">
                                            <a href="{{ route('user.overview', $user) }}" class="nav-link">Profile Overview</a>
                                        </li>
                                    </ul>
                                </div>
                            </li>

                            <!-- Inventory Management -->
                            <li class="menu-title">INVENTORY MANAGEMENT</li>

                            @can('View product')
                            <li class="nav-item">
                                <a class="nav-link menu-link {{ request()->routeIs('products.*') ? 'nav-active-parent' : 'collapsed' }}"
                                   href="#sidebarproduct" data-bs-toggle="collapse" role="button"
                                   aria-expanded="{{ request()->routeIs('products.*') ? 'true' : 'false' }}">
                                    <i class="ph-package"></i> <span>Product Management</span>
                                    <i class="ri-arrow-down-s-line ms-auto"></i>
                                </a>
                                <div class="collapse menu-dropdown {{ request()->routeIs('products.*') ? 'show' : '' }}" id="sidebarproduct">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item"><a href="{{ route('products.index') }}" class="nav-link">Products</a></li>
                                    </ul>
                                </div>
                            </li>
                            @endcan

                            @can('View pos')
                            <li class="nav-item">
                                <a class="nav-link menu-link {{ request()->routeIs('pos.*') ? 'nav-active-parent' : 'collapsed' }}"
                                   href="#sidebarpos" data-bs-toggle="collapse" role="button"
                                   aria-expanded="{{ request()->routeIs('pos.*') ? 'true' : 'false' }}">
                                    <i class="ph-shopping-cart"></i> <span>POS Management</span>
                                    <i class="ri-arrow-down-s-line ms-auto"></i>
                                </a>
                                <div class="collapse menu-dropdown {{ request()->routeIs('pos.*') ? 'show' : '' }}" id="sidebarpos">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item"><a href="{{ route('pos.index') }}" class="nav-link">Point of Sale</a></li>
                                        <li class="nav-item"><a href="{{ route('pos.grid') }}" class="nav-link">Grid POS</a></li>
                                    </ul>
                                </div>
                            </li>
                            @endcan

                            @can('View inventory')
                            <li class="nav-item">
                                <a class="nav-link menu-link {{ request()->routeIs('inventory.*') || request()->routeIs('stock-locations.*') ? 'nav-active-parent' : 'collapsed' }}"
                                   href="#sidebarmanageinventory" data-bs-toggle="collapse" role="button"
                                   aria-expanded="{{ request()->routeIs('inventory.*') || request()->routeIs('stock-locations.*') ? 'true' : 'false' }}">
                                    <i class="ph-warehouse"></i> <span>Inventory Operations</span>
                                    <i class="ri-arrow-down-s-line ms-auto"></i>
                                </a>
                                <div class="collapse menu-dropdown {{ request()->routeIs('inventory.*') || request()->routeIs('stock-locations.*') ? 'show' : '' }}" id="sidebarmanageinventory">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item"><a href="{{ route('inventory.index') }}" class="nav-link">Transactions</a></li>
                                        <li class="nav-item"><a href="{{ route('inventory.stock-levels') }}" class="nav-link">Stock Levels</a></li>
                                        <li class="nav-item"><a href="{{ route('stock-locations.index') }}" class="nav-link">Stock Locations</a></li>
                                        <li class="nav-item"><a href="{{ route('inventory.low-stock-alerts') }}" class="nav-link">Low Stock Alerts</a></li>
                                    </ul>
                                </div>
                            </li>
                            @endcan

                            <!-- Orders & Sales -->
                            @can('View order')
                            <li class="nav-item">
                                <a class="nav-link menu-link {{ request()->routeIs('orders.*') ? 'nav-active-parent' : 'collapsed' }}"
                                   href="#sidebarorders" data-bs-toggle="collapse" role="button"
                                   aria-expanded="{{ request()->routeIs('orders.*') ? 'true' : 'false' }}">
                                    <i class="ph-shopping-cart-simple"></i> <span>Orders Management</span>
                                    <i class="ri-arrow-down-s-line ms-auto"></i>
                                </a>
                                <div class="collapse menu-dropdown {{ request()->routeIs('orders.*') ? 'show' : '' }}" id="sidebarorders">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item"><a href="{{ route('orders.index') }}" class="nav-link">All Orders</a></li>
                                    </ul>
                                </div>
                            </li>
                            @endcan

                            @can('View customer')
                            <li class="nav-item">
                                <a class="nav-link menu-link {{ request()->routeIs('customers.*') ? 'nav-active-parent' : 'collapsed' }}"
                                   href="#sidebarcustomer" data-bs-toggle="collapse" role="button"
                                   aria-expanded="{{ request()->routeIs('customers.*') ? 'true' : 'false' }}">
                                    <i class="ph-users"></i> <span>Customers Management</span>
                                    <i class="ri-arrow-down-s-line ms-auto"></i>
                                </a>
                                <div class="collapse menu-dropdown {{ request()->routeIs('customers.*') ? 'show' : '' }}" id="sidebarcustomer">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item"><a href="{{ route('customers.index') }}" class="nav-link">All Customers</a></li>
                                    </ul>
                                </div>
                            </li>
                            @endcan

                            <!-- Settings -->
                            <li class="menu-title">Settings</li>

                            @can('View store setting')
                            <li class="nav-item">
                                <a class="nav-link menu-link {{ request()->routeIs('settings.store.*') ? 'nav-active-parent' : 'collapsed' }}"
                                   href="#sidebarsetting" data-bs-toggle="collapse" role="button"
                                   aria-expanded="{{ request()->routeIs('settings.store.*') ? 'true' : 'false' }}">
                                    <i class="ph-gear"></i> <span>Store Settings</span>
                                    <i class="ri-arrow-down-s-line ms-auto"></i>
                                </a>
                                <div class="collapse menu-dropdown {{ request()->routeIs('settings.store.*') ? 'show' : '' }}" id="sidebarsetting">
                                    <ul class="nav nav-sm flex-column">
                                        <li class="nav-item"><a href="{{ route('settings.store.index') }}" class="nav-link">Store Configuration</a></li>
                                    </ul>
                                </div>
                            </li>
                            @endcan
                        @endif
                    </ul>
                </div>
            </div>
        </div>

        <!-- Header -->
        <header id="page-topbar">
            <div class="layout-width">
                <div class="navbar-header">
                    <div class="d-flex">
                        <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger shadow-none" id="topnav-hamburger-icon">
                            <span class="hamburger-icon"><span></span><span></span><span></span></span>
                        </button>
                    </div>

                    <div class="d-flex align-items-center">
                        <!-- Theme Toggle -->
                        <button type="button" class="btn btn-icon btn-topbar btn-ghost-dark rounded-circle" id="theme-toggle">
                            <i class="bi bi-sun align-middle fs-3xl"></i>
                        </button>

                        <!-- User Dropdown -->
                        <div class="dropdown ms-3">
                            <button class="btn btn-link dropdown-toggle text-decoration-none d-flex align-items-center gap-2" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                @php $userdata = Auth::user(); @endphp
                                @if($userdata)
                                    <img class="rounded-circle header-profile-user-enhanced" src="{{ $userdata->profile_image ? asset('storage/' . $userdata->profile_image) : asset('theme/layouts/assets/images/users/user-dummy-img.jpg') }}" alt="{{ $userdata->name }}" width="40" height="40" style="object-fit: cover;">
                                    <span class="d-none d-xl-block">
                                        <span class="fw-medium">{{ $userdata->name }}</span>
                                        <small class="d-block text-muted">{{ $userdata->roles->first()->name ?? 'User' }}</small>
                                    </span>
                                @else
                                    <img class="rounded-circle" src="{{ asset('theme/layouts/assets/images/users/user-dummy-img.jpg') }}" alt="User" width="40" height="40">
                                @endif
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                @if($userdata)
                                    <li><h6 class="dropdown-header">Welcome {{ $userdata->name }}!</h6></li>
                                    <li><a class="dropdown-item" href="{{ route('user.overview', $userdata) }}"><i class="mdi mdi-account-circle me-2"></i> Profile</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item"><i class="mdi mdi-logout me-2"></i> Logout</button>
                                        </form>
                                    </li>
                                @else
                                    <li><a class="dropdown-item" href="{{ route('login') }}">Login</a></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        @yield('content')

        <!-- Footer -->
        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <script>document.write(new Date().getFullYear())</script> © {{ $storeName }}
                    </div>
                    <div class="col-sm-6">
                        <div class="text-sm-end">Powered by Qudroid Systems</div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Back to Top -->
    <button class="btn btn-dark btn-icon" id="back-to-top">
        <i class="bi bi-caret-up fs-3xl"></i>
    </button>

    <!-- Scripts -->
    <script src="{{ asset('theme/layouts/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('theme/layouts/assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('theme/layouts/assets/js/plugins.js') }}"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize SimpleBar for sidebar
        var scrollbar = document.getElementById('scrollbar');
        if (scrollbar && typeof SimpleBar !== 'undefined') {
            new SimpleBar(scrollbar);
        }

        // Theme Toggle
        const themeToggle = document.getElementById('theme-toggle');
        const html = document.documentElement;

        // Load saved theme
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            html.setAttribute('data-bs-theme', 'dark');
            themeToggle.innerHTML = '<i class="bi bi-moon align-middle fs-3xl"></i>';
        }

        themeToggle.addEventListener('click', () => {
            const currentTheme = html.getAttribute('data-bs-theme');
            if (currentTheme === 'dark') {
                html.setAttribute('data-bs-theme', 'light');
                localStorage.setItem('theme', 'light');
                themeToggle.innerHTML = '<i class="bi bi-sun align-middle fs-3xl"></i>';
            } else {
                html.setAttribute('data-bs-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                themeToggle.innerHTML = '<i class="bi bi-moon align-middle fs-3xl"></i>';
            }
        });

        // Back to top
        const backToTop = document.getElementById('back-to-top');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backToTop.classList.add('show');
            } else {
                backToTop.classList.remove('show');
            }
        });
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Active menu highlighting
        const currentPath = window.location.pathname;
        document.querySelectorAll('#navbar-nav .nav-sm a.nav-link').forEach(link => {
            const href = link.getAttribute('href');
            if (href && href !== '#' && currentPath === new URL(href, window.location.origin).pathname) {
                link.classList.add('nav-active-child');
                const parentCollapse = link.closest('.collapse');
                if (parentCollapse) {
                    parentCollapse.classList.add('show');
                    const parentToggle = document.querySelector(`[href="#${parentCollapse.id}"]`);
                    if (parentToggle) {
                        parentToggle.classList.add('nav-active-parent');
                        parentToggle.setAttribute('aria-expanded', 'true');
                    }
                }
            }
        });
    });
    </script>

    <!-- Route-specific JS includes -->
    @if (Route::is('dashboard'))
        @include('layouts.pages-assets.js.dashboard-list-js')
    @endif
    @if (Route::is('users.*'))
        @include('layouts.pages-assets.js.users-list-js')
    @endif
    @if (Route::is('roles.*'))
        @include('layouts.pages-assets.js.role-list-js')
    @endif
    @if (Route::is('permissions.*'))
        @include('layouts.pages-assets.js.permissions-list-js')
    @endif
    @if (Route::is('brands.*'))
        @include('layouts.pages-assets.js.brand-list-js')
    @endif
    @if (Route::is('categories.*'))
        @include('layouts.pages-assets.js.category-list-js')
    @endif
    @if (Route::is('banners.*'))
        @include('layouts.pages-assets.js.banner-list-js')
    @endif
    @if (Route::is('products.*'))
        @include('layouts.pages-assets.js.product-list-js')
    @endif
    @if (Route::is('pos.*'))
        @include('layouts.pages-assets.js.pos-list-js')
    @endif
    @if (Route::is('inventory.*') || Route::is('stock-locations.*'))
        @include('layouts.pages-assets.js.inventory-list-js')
    @endif
    @if (Route::is('orders.*'))
        @include('layouts.pages-assets.js.order-list-js')
    @endif
    @if (Route::is('customers.*'))
        @include('layouts.pages-assets.js.customer-list-js')
    @endif
    @if (Route::is('settings.store.*'))
        @include('layouts.pages-assets.js.storesetting-list-js')
    @endif
    @if (Route::is('sales.*'))
        @include('layouts.pages-assets.js.sale-list-js')
    @endif
    @if (Route::is('salesperson.*'))
        @include('layouts.pages-assets.js.sale-list-js')
    @endif
</body>
</html>
