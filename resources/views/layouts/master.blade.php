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
    <meta content="Qudroid Systems" name="author">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Dynamic Favicon -->
    @if($store && $store->logo)
        <link rel="shortcut icon" href="{{ $store->getLogoUrlAttribute() }}" type="image/png">
    @else
        <link rel="shortcut icon" href="{{ asset('theme/layouts/assets/images/favicon.ico') }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Layout CSS -->
    <link href="{{ asset('theme/layouts/assets/css/icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/layouts/assets/css/app.min.css') }}" rel="stylesheet">

    <style>
        /* Simple Sidebar Styles */
        .app-menu {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 250px;
            background: #1e293b;
            z-index: 1000;
            overflow-y: auto;
        }
        .navbar-brand-box {
            padding: 15px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .navbar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .nav-item {
            margin: 2px 0;
        }
        .nav-link {
            display: flex;
            align-items: center;
            padding: 10px 20px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            transition: all 0.3s;
        }
        .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,0.1);
        }
        .nav-link i {
            margin-right: 10px;
            font-size: 18px;
        }
        .menu-title {
            padding: 10px 20px;
            color: rgba(255,255,255,0.4);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .menu-dropdown {
            padding-left: 35px;
        }
        .menu-dropdown .nav-link {
            padding: 8px 15px;
            font-size: 14px;
        }
        .vertical-overlay {
            margin-left: 250px;
        }
        #page-topbar {
            position: fixed;
            top: 0;
            right: 0;
            left: 250px;
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            z-index: 999;
            padding: 10px 20px;
        }
        .page-content {
            margin-left: 250px;
            margin-top: 70px;
            padding: 20px;
        }
        .footer {
            margin-left: 250px;
            padding: 15px 20px;
            background: #fff;
            border-top: 1px solid #e9ecef;
        }
        .dropdown-menu {
            position: absolute;
            z-index: 1000;
        }
        .navbar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-icon {
            width: 38px;
            height: 38px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        .header-profile-user {
            width: 36px;
            height: 36px;
            object-fit: cover;
            border-radius: 50%;
        }
        @media (max-width: 768px) {
            .app-menu {
                transform: translateX(-100%);
                transition: transform 0.3s;
            }
            .app-menu.show {
                transform: translateX(0);
            }
            .vertical-overlay, .page-content, .footer {
                margin-left: 0;
            }
            #page-topbar {
                left: 0;
            }
        }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <div class="app-menu navbar-menu" id="app-menu">
        <div class="navbar-brand-box">
            @if($store && $store->logo)
                <img src="{{ $store->getLogoUrlAttribute() }}" alt="{{ $storeName }}" height="50">
            @else
                <h4 class="text-white mb-0">{{ $storeName }}</h4>
            @endif
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
                                <a class="nav-link" href="{{ route('dashboard') }}">
                                    <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
                                </a>
                            </li>
                        @endcan

                        <!-- Users -->
                        @can('View user')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('users.index') }}">
                                    <i class="bi bi-people"></i> <span>Users</span>
                                </a>
                            </li>
                        @endcan

                        <!-- Roles -->
                        @can('View role')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('roles.index') }}">
                                    <i class="bi bi-shield-lock"></i> <span>Roles</span>
                                </a>
                            </li>
                        @endcan

                        <!-- Products -->
                        @can('View product')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('products.index') }}">
                                    <i class="bi bi-box-seam"></i> <span>Products</span>
                                </a>
                            </li>
                        @endcan

                        <!-- Categories -->
                        @can('View category')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('categories.index') }}">
                                    <i class="bi bi-tags"></i> <span>Categories</span>
                                </a>
                            </li>
                        @endcan

                        <!-- POS -->
                        @can('View pos')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('pos.index') }}">
                                    <i class="bi bi-cart"></i> <span>POS</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('pos.grid') }}">
                                    <i class="bi bi-grid-3x3"></i> <span>Grid POS</span>
                                </a>
                            </li>
                        @endcan

                        <!-- Orders -->
                        @can('View order')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('orders.index') }}">
                                    <i class="bi bi-receipt"></i> <span>Orders</span>
                                </a>
                            </li>
                        @endcan

                        <!-- Customers -->
                        @can('View customer')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('customers.index') }}">
                                    <i class="bi bi-person-badge"></i> <span>Customers</span>
                                </a>
                            </li>
                        @endcan

                        <!-- Inventory -->
                        @can('View inventory')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('inventory.index') }}">
                                    <i class="bi bi-archive"></i> <span>Inventory</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('inventory.stock-levels') }}">
                                    <i class="bi bi-bar-chart"></i> <span>Stock Levels</span>
                                </a>
                            </li>
                        @endcan

                        <!-- Sales -->
                        @can('View sale')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('sales.index') }}">
                                    <i class="bi bi-graph-up"></i> <span>Sales Reports</span>
                                </a>
                            </li>
                        @endcan

                        <!-- Store Settings -->
                        @can('View store setting')
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('settings.store.index') }}">
                                    <i class="bi bi-gear"></i> <span>Store Settings</span>
                                </a>
                            </li>
                        @endcan
                    @endif
                </ul>
            </div>
        </div>
    </div>

    <div class="vertical-overlay"></div>

    <!-- Header -->
    <header id="page-topbar">
        <div class="navbar-header">
            <div class="d-flex">
                <button class="btn btn-sm btn-icon" id="sidebar-toggle" style="background: transparent; border: none;">
                    <i class="bi bi-list fs-4"></i>
                </button>
            </div>

            <div class="d-flex align-items-center">
                <!-- Theme Toggle -->
                <button class="btn btn-sm btn-icon me-2" id="theme-toggle" style="background: transparent; border: none;">
                    <i class="bi bi-sun fs-5"></i>
                </button>

                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="background: transparent; border: none;">
                        @php $userdata = Auth::user(); @endphp
                        @if($userdata)
                            <img src="{{ $userdata->profile_image ? asset('storage/' . $userdata->profile_image) : asset('theme/layouts/assets/images/users/user-dummy-img.jpg') }}" alt="{{ $userdata->name }}" class="header-profile-user">
                            <span class="ms-2 d-none d-md-inline">{{ $userdata->name }}</span>
                        @else
                            <img src="{{ asset('theme/layouts/assets/images/users/user-dummy-img.jpg') }}" alt="User" class="header-profile-user">
                            <span class="ms-2 d-none d-md-inline">Guest</span>
                        @endif
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                        @if($userdata)
                            <li><h6 class="dropdown-header">Welcome {{ $userdata->name }}!</h6></li>
                            <li><a class="dropdown-item" href="{{ route('user.overview', $userdata) }}"><i class="bi bi-person me-2"></i> Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}" id="logout-form">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        @else
                            <li><a class="dropdown-item" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right me-2"></i> Login</a></li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="page-content">
        @yield('content')
    </div>

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

    <!-- Mobile Sidebar Overlay -->
    <div class="mobile-overlay" id="mobile-overlay" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 999;"></div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sidebar Toggle for Mobile
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const appMenu = document.getElementById('app-menu');
        const mobileOverlay = document.getElementById('mobile-overlay');

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                appMenu.classList.toggle('show');
                if (appMenu.classList.contains('show')) {
                    mobileOverlay.style.display = 'block';
                } else {
                    mobileOverlay.style.display = 'none';
                }
            });
        }

        if (mobileOverlay) {
            mobileOverlay.addEventListener('click', function() {
                appMenu.classList.remove('show');
                mobileOverlay.style.display = 'none';
            });
        }

        // Theme Toggle
        const themeToggle = document.getElementById('theme-toggle');
        const htmlElement = document.documentElement;
        const themeIcon = themeToggle ? themeToggle.querySelector('i') : null;

        // Load saved theme
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            htmlElement.setAttribute('data-bs-theme', 'dark');
            if (themeIcon) themeIcon.className = 'bi bi-moon fs-5';
        } else {
            htmlElement.setAttribute('data-bs-theme', 'light');
            if (themeIcon) themeIcon.className = 'bi bi-sun fs-5';
        }

        if (themeToggle) {
            themeToggle.addEventListener('click', function() {
                const currentTheme = htmlElement.getAttribute('data-bs-theme');
                if (currentTheme === 'dark') {
                    htmlElement.setAttribute('data-bs-theme', 'light');
                    localStorage.setItem('theme', 'light');
                    if (themeIcon) themeIcon.className = 'bi bi-sun fs-5';
                } else {
                    htmlElement.setAttribute('data-bs-theme', 'dark');
                    localStorage.setItem('theme', 'dark');
                    if (themeIcon) themeIcon.className = 'bi bi-moon fs-5';
                }
            });
        }

        // Active link highlighting
        const currentPath = window.location.pathname;
        document.querySelectorAll('#navbar-nav .nav-link').forEach(function(link) {
            const href = link.getAttribute('href');
            if (href && href !== '#' && currentPath === href) {
                link.style.background = 'rgba(79, 142, 247, 0.2)';
                link.style.color = '#fff';
            }
        });

        // Initialize all dropdowns
        var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'));
        dropdownElementList.map(function(dropdownToggleEl) {
            return new bootstrap.Dropdown(dropdownToggleEl);
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
    @if (Route::is('products.*'))
        @include('layouts.pages-assets.js.product-list-js')
    @endif
    @if (Route::is('categories.*'))
        @include('layouts.pages-assets.js.category-list-js')
    @endif
    @if (Route::is('orders.*'))
        @include('layouts.pages-assets.js.order-list-js')
    @endif
    @if (Route::is('customers.*'))
        @include('layouts.pages-assets.js.customer-list-js')
    @endif
    @if (Route::is('inventory.*'))
        @include('layouts.pages-assets.js.inventory-list-js')
    @endif
    @if (Route::is('sales.*'))
        @include('layouts.pages-assets.js.sale-list-js')
    @endif
    @if (Route::is('settings.store.*'))
        @include('layouts.pages-assets.js.storesetting-list-js')
    @endif
</body>
</html>
