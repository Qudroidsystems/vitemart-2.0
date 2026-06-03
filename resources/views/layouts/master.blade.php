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
        <link rel="icon" href="{{ $store->getLogoUrlAttribute() }}" type="image/png">
        <link rel="apple-touch-icon" href="{{ $store->getLogoUrlAttribute() }}">
    @else
        <link rel="shortcut icon" href="{{ asset('theme/layouts/assets/images/favicon.ico') }}">
        <link rel="icon" type="image/png" href="{{ asset('theme/layouts/assets/images/logo-dark.png') }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link id="fontsLink" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/bold/style.css">

    <!-- Layout CSS — load BEFORE layout.js so variables are available -->
    <link href="{{ asset('theme/layouts/assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/layouts/assets/css/icons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/layouts/assets/css/app.min.css') }}" rel="stylesheet">
    <link href="{{ asset('theme/layouts/assets/css/custom.min.css') }}" rel="stylesheet">

    <!-- layout.js must run AFTER the CSS above so the html attrs are set correctly -->
    <script src="{{ asset('theme/layouts/assets/js/layout.js') }}"></script>

    <!-- NProgress -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.css"/>
    <script src="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.min.js"></script>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        /* =====================================================
           NPROGRESS
           ===================================================== */
        #nprogress .bar  { background: #4f8ef7 !important; height: 3px !important; box-shadow: 0 0 8px rgba(79,142,247,.6) !important; }
        #nprogress .peg  { box-shadow: none !important; }
        #nprogress .spinner { display: none !important; }

        /* =====================================================
           PAGINATION
           ===================================================== */
        .pagination-wrap .page-item { margin: 0 5px; }
        .pagination-wrap .page-link { padding: 5px 10px; }
        .pagination-wrap .active .page-link { background-color: #007bff; color: #fff; }
        .pagination-wrap .disabled .page-link { pointer-events: none; opacity: .5; }

        /* =====================================================
           SPINNER
           ===================================================== */
        .spin { animation: spin 1s linear infinite; }
        @keyframes spin { 0%{transform:rotate(0deg)} 100%{transform:rotate(360deg)} }

        /* =====================================================
           MISC
           ===================================================== */
        .form-check-input:checked { background-color: #405189; border-color: #405189; }
        .table tbody tr { transition: background-color .15s ease; }
        .table tbody tr:hover { background-color: rgba(67,97,238,.05); }
        .modal.fade  .modal-dialog { transform: translate(0,-50px); transition: transform .3s ease-out; }
        .modal.show  .modal-dialog { transform: translate(0,0); }

        /* =====================================================
           SIDEBAR LAYOUT
           ===================================================== */
        .app-menu {
            position: fixed;
            top: 0; left: 0; bottom: 0;
            width: 250px;
            z-index: 1000;
            display: flex;
            flex-direction: column;
        }
        /* The scrollbar div now fills available space between logo and footer */
        #scrollbar {
            flex: 1;
            overflow-y: auto;
            scrollbar-width: thin;
        }
        #scrollbar::-webkit-scrollbar { width: 4px; }
        #scrollbar::-webkit-scrollbar-track  { background: rgba(255,255,255,.05); border-radius: 4px; }
        #scrollbar::-webkit-scrollbar-thumb  { background: rgba(255,255,255,.2);  border-radius: 4px; }
        #scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,.3); }
        .navbar-menu .container-fluid { padding: 0; }
        #navbar-nav { padding-bottom: 8px; }

        /* =====================================================
           SIDEBAR LOGOUT FOOTER
           ===================================================== */
        .sidebar-footer {
            flex-shrink: 0;
            border-top: 1px solid rgba(255,255,255,.1);
            padding: 14px 16px;
            background: inherit; /* inherits sidebar bg */
        }
        .sidebar-footer-user {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .sidebar-footer-user img {
            width: 36px; height: 36px;
            border-radius: 50%; object-fit: cover;
            border: 2px solid rgba(255,255,255,.18);
            flex-shrink: 0;
        }
        .sidebar-footer-user-info { min-width: 0; flex: 1; }
        .sidebar-footer-user-name {
            font-size: 13px; font-weight: 600; color: #fff;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sidebar-footer-user-role {
            font-size: 11px; color: rgba(255,255,255,.45);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sidebar-logout-btn {
            display: flex; align-items: center; gap: 9px;
            width: 100%; padding: 9px 14px;
            border-radius: 8px;
            background: rgba(239,68,68,.12);
            border: 1px solid rgba(239,68,68,.22);
            color: #f87171; font-size: 13px; font-weight: 500;
            cursor: pointer; text-decoration: none;
            transition: background .2s, border-color .2s, color .2s;
        }
        .sidebar-logout-btn:hover {
            background: rgba(239,68,68,.24);
            border-color: rgba(239,68,68,.45);
            color: #fca5a5;
        }
        .sidebar-logout-btn i { font-size: 17px; flex-shrink: 0; }

        /* =====================================================
           SIDEBAR NAV ACTIVE STATES
           ===================================================== */
        #navbar-nav .menu-dropdown { overflow: hidden; }
        #navbar-nav .nav-link.menu-link .ri-arrow-down-s-line { transition: transform .25s ease; display: inline-block; }
        #navbar-nav .nav-link.menu-link[aria-expanded="true"] .ri-arrow-down-s-line { transform: rotate(180deg); }

        #navbar-nav .nav-link.menu-link.nav-active-parent {
            color: #fff !important;
            background: rgba(79,142,247,.18) !important;
            border-left: 3px solid #4f8ef7;
            padding-left: calc(1.3rem - 3px);
        }
        #navbar-nav .nav-link.menu-link.nav-active-parent i { color: #4f8ef7 !important; }

        #navbar-nav .nav-sm .nav-link.nav-active-child { color: #7eb8fb !important; font-weight: 500; }
        #navbar-nav .nav-sm .nav-link.nav-active-child::before {
            content: ''; display: inline-block;
            width: 5px; height: 5px; border-radius: 50%;
            background: #4f8ef7; margin-right: 8px;
            box-shadow: 0 0 0 3px rgba(79,142,247,.25);
            vertical-align: middle; flex-shrink: 0;
            animation: dotPop .25s ease;
        }
        @keyframes dotPop { from{transform:scale(0);opacity:0} to{transform:scale(1);opacity:1} }

        /* =====================================================
           RIPPLE
           ===================================================== */
        #navbar-nav .nav-link { position: relative; overflow: hidden; transition: color .18s, background-color .18s, padding-left .18s; }
        .nav-ripple {
            position: absolute; border-radius: 50%;
            background: rgba(255,255,255,.18);
            transform: scale(0); animation: ripple-anim .55s linear;
            pointer-events: none; z-index: 0;
        }
        @keyframes ripple-anim { to{transform:scale(5);opacity:0} }

        /* =====================================================
           BACK TO TOP
           ===================================================== */
        #back-to-top { opacity:0; visibility:hidden; transform:translateY(12px); transition:opacity .3s,transform .3s,visibility .3s; }
        #back-to-top.show { opacity:1; visibility:visible; transform:translateY(0); }
        #back-to-top:hover { transform:translateY(-3px) !important; }

        /* =====================================================
           PAGE FADE-IN
           ===================================================== */
        .page-content { animation: pageFadeIn .35s ease; }
        @keyframes pageFadeIn { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }

        /* =====================================================
           TOPBAR USER DROPDOWN — guaranteed visible
           ===================================================== */
        .topbar-user .dropdown-menu {
            min-width: 210px;
            z-index: 9999 !important;
            border-radius: 12px;
            overflow: hidden;
            margin-top: 8px !important;
            box-shadow: 0 8px 32px rgba(0,0,0,.18);
        }
        .header-profile-user-enhanced { transition: transform .25s, box-shadow .25s; }
        .header-profile-user-enhanced:hover { transform:scale(1.07); box-shadow:0 0 0 3px rgba(79,142,247,.35) !important; }

        /* =====================================================
           MISC INTERACTIONS
           ===================================================== */
        .card { transition: box-shadow .25s, transform .25s; }
        .card:hover { box-shadow: 0 6px 20px rgba(0,0,0,.08); }
        .btn  { transition: transform .15s, box-shadow .15s; }
        .btn:active { transform: scale(.97); }

        /* =====================================================
           SIDEBAR STAGGER ANIMATION
           ===================================================== */
        #navbar-nav > li { animation: navItemFadeIn .4s ease both; }
        #navbar-nav > li:nth-child(1)  { animation-delay:.02s }
        #navbar-nav > li:nth-child(2)  { animation-delay:.04s }
        #navbar-nav > li:nth-child(3)  { animation-delay:.06s }
        #navbar-nav > li:nth-child(4)  { animation-delay:.08s }
        #navbar-nav > li:nth-child(5)  { animation-delay:.10s }
        #navbar-nav > li:nth-child(6)  { animation-delay:.12s }
        #navbar-nav > li:nth-child(7)  { animation-delay:.14s }
        #navbar-nav > li:nth-child(8)  { animation-delay:.16s }
        #navbar-nav > li:nth-child(9)  { animation-delay:.18s }
        #navbar-nav > li:nth-child(10) { animation-delay:.20s }
        @keyframes navItemFadeIn { from{opacity:0;transform:translateX(-8px)} to{opacity:1;transform:translateX(0)} }

        /* =====================================================
           DROPDOWN ANIMATIONS
           ===================================================== */
        .dropdown-menu { animation: dropdownFadeIn .25s cubic-bezier(.4,0,.2,1); transform-origin: top right; }
        @keyframes dropdownFadeIn { from{opacity:0;transform:translateY(-10px) scale(.97)} to{opacity:1;transform:translateY(0) scale(1)} }

        /* =====================================================
           PRINT
           ===================================================== */
        @media print { .no-print{display:none!important} body{padding:0;margin:0} }

        /* =====================================================
           SPOTLIGHT ANIMATIONS
           ===================================================== */
        @keyframes spotlightOverlayFadeIn  { from{background:rgba(0,0,0,.2);backdrop-filter:blur(0)} to{background:rgba(0,0,0,.65);backdrop-filter:blur(8px)} }
        @keyframes spotlightOverlayFadeOut { from{background:rgba(0,0,0,.65);backdrop-filter:blur(8px)} to{background:rgba(0,0,0,.2);backdrop-filter:blur(0)} }
        @keyframes resultBounceIn { 0%{opacity:0;transform:translateX(-20px) scale(.95)} 60%{opacity:.8;transform:translateX(4px) scale(1.02)} 100%{opacity:1;transform:translateX(0) scale(1)} }
        @keyframes loadingSpin    { 0%{transform:rotate(0)} 100%{transform:rotate(360deg)} }
        @keyframes typingDot      { 0%,60%,100%{transform:translateY(0);opacity:.5} 30%{transform:translateY(-4px);opacity:1} }
        .spotlight-result-item { animation: resultBounceIn .35s cubic-bezier(.34,1.3,.64,1) forwards; opacity:0; }
        .spotlight-result-item:nth-child(1){animation-delay:.00s}
        .spotlight-result-item:nth-child(2){animation-delay:.03s}
        .spotlight-result-item:nth-child(3){animation-delay:.06s}
        .typing-dot { display:inline-block; animation:typingDot 1.4s infinite ease-in-out; }
        .typing-dot:nth-child(2){animation-delay:.2s}
        .typing-dot:nth-child(3){animation-delay:.4s}
    </style>

    <!-- Route-specific CSS -->
    @if (Route::is('dashboard'))    @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('users.*'))      @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('roles.*'))      @include('layouts.pages-assets.css.roles-list-css') @endif
    @if (Route::is('permissions.*'))@include('layouts.pages-assets.css.permission-list-css') @endif
    @if (Route::is('brands.*'))     @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('categories.*')) @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('banners.*'))    @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('products.*'))   @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('pos.*'))        @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('inventory.*') || Route::is('stock-locations.*')) @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('orders.*'))     @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('customers.*'))  @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('settings.store.*')) @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('sales.*'))      @include('layouts.pages-assets.css.users-list-css') @endif
    @if (Route::is('salesperson.*'))@include('layouts.pages-assets.css.users-list-css') @endif
</head>

<body>
<div id="layout-wrapper">

    <!-- ========== SIDEBAR ========== -->
    <div class="app-menu navbar-menu">

        <!-- LOGO -->
        <div class="navbar-brand-box">
            <a href="{{ route('dashboard') }}" class="logo logo-dark">
                <span class="logo-sm">
                    @if($store && $store->logo)
                        <img src="{{ $store->getLogoUrlAttribute() }}" alt="{{ $storeName }}" height="22">
                    @else
                        <img src="{{ asset('theme/layouts/assets/images/logo-sm.png') }}" alt="Logo" height="22">
                    @endif
                </span>
                <span class="logo-lg">
                    @if($store && $store->logo)
                        <img src="{{ $store->getLogoUrlAttribute() }}" alt="{{ $storeName }}" height="60">
                    @else
                        <img src="{{ asset('theme/layouts/assets/images/logo-dark.png') }}" alt="Logo" height="60">
                    @endif
                </span>
            </a>
            <a href="{{ route('dashboard') }}" class="logo logo-light">
                <span class="logo-sm">
                    @if($store && $store->logo)
                        <img src="{{ $store->getLogoUrlAttribute() }}" alt="{{ $storeName }}" height="22">
                    @else
                        <img src="{{ asset('theme/layouts/assets/images/logo-sm.png') }}" alt="Logo" height="22">
                    @endif
                </span>
                <span class="logo-lg">
                    @if($store && $store->logo)
                        <img src="{{ $store->getLogoUrlAttribute() }}" alt="{{ $storeName }}" height="60">
                    @else
                        <img src="{{ asset('theme/layouts/assets/images/logo-light.png') }}" alt="Logo" height="60">
                    @endif
                </span>
            </a>
            <button type="button" class="btn btn-sm p-0 fs-3xl header-item float-end btn-vertical-sm-hover" id="vertical-hover">
                <i class="ri-record-circle-line"></i>
            </button>
        </div>

        <!-- NAV (scrollable) -->
        <div id="scrollbar">
            <div class="container-fluid">
                <div id="two-column-menu"></div>
                <ul class="navbar-nav" id="navbar-nav">
                    @php $user = Auth::user(); @endphp
                    @if($user)

                        <li class="menu-title"><span data-key="t-menu">Menu</span></li>

                        {{-- Dashboard --}}
                        @can('dashboard')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('dashboard') ? 'nav-active-parent' : 'collapsed' }}"
                               href="#sidebarDashboards" data-bs-toggle="collapse" role="button"
                               aria-expanded="{{ request()->routeIs('dashboard') ? 'true' : 'false' }}"
                               aria-controls="sidebarDashboards">
                                <i class="ph-gauge"></i> <span>Dashboards</span>
                                <i class="ri-arrow-down-s-line ms-auto"></i>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('dashboard') ? 'show' : '' }}" id="sidebarDashboards">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-active-child' : '' }}">Administration Analytics</a>
                                    </li>
                                    @can('View inventory dashboard')
                                    <li class="nav-item">
                                        <a href="{{ route('inventory.dashboard') }}" class="nav-link {{ request()->routeIs('inventory.dashboard') ? 'nav-active-child' : '' }}">Inventory Dashboard</a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        @endcan

                        {{-- USERS & PRIVILEGES --}}
                        @if(auth()->user()->can('View user') || auth()->user()->can('View role') || auth()->user()->can('View permission'))
                            <li class="menu-title"><i class="ri-more-fill"></i> <span>USERS & PRIVILEGES</span></li>
                        @endif

                        @can('View user')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('users.*') ? 'nav-active-parent' : 'collapsed' }}"
                               href="#sidebarusers" data-bs-toggle="collapse" role="button"
                               aria-expanded="{{ request()->routeIs('users.*') ? 'true' : 'false' }}"
                               aria-controls="sidebarusers">
                                <i class="ph-user-circle"></i> <span>User Management</span>
                                <i class="ri-arrow-down-s-line ms-auto"></i>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('users.*') ? 'show' : '' }}" id="sidebarusers">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.index') ? 'nav-active-child' : '' }}">Users</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endcan

                        @can('View role')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('roles.*') || request()->routeIs('permissions.*') ? 'nav-active-parent' : 'collapsed' }}"
                               href="#sidebarroles" data-bs-toggle="collapse" role="button"
                               aria-expanded="{{ request()->routeIs('roles.*') || request()->routeIs('permissions.*') ? 'true' : 'false' }}"
                               aria-controls="sidebarroles">
                                <i class="ph-address-book"></i> <span>Roles & Permissions</span>
                                <i class="ri-arrow-down-s-line ms-auto"></i>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('roles.*') || request()->routeIs('permissions.*') ? 'show' : '' }}" id="sidebarroles">
                                <ul class="nav nav-sm flex-column">
                                    @can('View role')
                                    <li class="nav-item">
                                        <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') && !request()->routeIs('permissions.*') ? 'nav-active-child' : '' }}">Roles</a>
                                    </li>
                                    @endcan
                                    @can('View permission')
                                    <li class="nav-item">
                                        <a href="{{ route('permissions.index') }}" class="nav-link {{ request()->routeIs('permissions.*') ? 'nav-active-child' : '' }}">Permissions</a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        @endcan

                        {{-- My Account --}}
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('user.overview') ? 'nav-active-parent' : 'collapsed' }}"
                               href="#sidebaraccount" data-bs-toggle="collapse" role="button"
                               aria-expanded="{{ request()->routeIs('user.overview') ? 'true' : 'false' }}"
                               aria-controls="sidebaraccount">
                                <i class="ph-address-book"></i> <span>My Account</span>
                                <i class="ri-arrow-down-s-line ms-auto"></i>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('user.overview') ? 'show' : '' }}" id="sidebaraccount">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('user.overview', $user) }}" class="nav-link {{ request()->routeIs('user.overview') ? 'nav-active-child' : '' }}">Profile Overview</a>
                                    </li>
                                </ul>
                            </div>
                        </li>

                        {{-- INVENTORY MANAGEMENT --}}
                        @if(auth()->user()->can('View banner') || auth()->user()->can('View category') || auth()->user()->can('View brand') ||
                            auth()->user()->can('View product') || auth()->user()->can('View pos') || auth()->user()->can('View inventory'))
                            <li class="menu-title"><i class="ri-more-fill"></i> <span>INVENTORY MANAGEMENT</span></li>
                        @endif

                        @can('View banner')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('banners.*') ? 'nav-active-parent' : 'collapsed' }}"
                               href="#sidebarbanner" data-bs-toggle="collapse" role="button"
                               aria-expanded="{{ request()->routeIs('banners.*') ? 'true' : 'false' }}"
                               aria-controls="sidebarbanner">
                                <i class="ph-image"></i> <span>Banner Management</span>
                                <i class="ri-arrow-down-s-line ms-auto"></i>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('banners.*') ? 'show' : '' }}" id="sidebarbanner">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('banners.index') }}" class="nav-link {{ request()->routeIs('banners.index') ? 'nav-active-child' : '' }}">Banners</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endcan

                        @can('View category')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('categories.*') ? 'nav-active-parent' : 'collapsed' }}"
                               href="#sidebarcategories" data-bs-toggle="collapse" role="button"
                               aria-expanded="{{ request()->routeIs('categories.*') ? 'true' : 'false' }}"
                               aria-controls="sidebarcategories">
                                <i class="ph-list"></i> <span>Category Management</span>
                                <i class="ri-arrow-down-s-line ms-auto"></i>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('categories.*') ? 'show' : '' }}" id="sidebarcategories">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.index') ? 'nav-active-child' : '' }}">Categories</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endcan

                        @can('View brand')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('brands.*') ? 'nav-active-parent' : 'collapsed' }}"
                               href="#sidebarbrand" data-bs-toggle="collapse" role="button"
                               aria-expanded="{{ request()->routeIs('brands.*') ? 'true' : 'false' }}"
                               aria-controls="sidebarbrand">
                                <i class="ph-tag"></i> <span>Brand Management</span>
                                <i class="ri-arrow-down-s-line ms-auto"></i>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('brands.*') ? 'show' : '' }}" id="sidebarbrand">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('brands.index') }}" class="nav-link {{ request()->routeIs('brands.index') ? 'nav-active-child' : '' }}">Brands</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endcan

                        @can('View product')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('products.*') ? 'nav-active-parent' : 'collapsed' }}"
                               href="#sidebarproduct" data-bs-toggle="collapse" role="button"
                               aria-expanded="{{ request()->routeIs('products.*') ? 'true' : 'false' }}"
                               aria-controls="sidebarproduct">
                                <i class="ph-package"></i> <span>Product Management</span>
                                <i class="ri-arrow-down-s-line ms-auto"></i>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('products.*') ? 'show' : '' }}" id="sidebarproduct">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.index') ? 'nav-active-child' : '' }}">Products</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endcan

                        @can('View pos')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('pos.*') ? 'nav-active-parent' : 'collapsed' }}"
                               href="#sidebarpos" data-bs-toggle="collapse" role="button"
                               aria-expanded="{{ request()->routeIs('pos.*') ? 'true' : 'false' }}"
                               aria-controls="sidebarpos">
                                <i class="ph-shopping-cart"></i> <span>POS Management</span>
                                <i class="ri-arrow-down-s-line ms-auto"></i>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('pos.*') ? 'show' : '' }}" id="sidebarpos">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos.index') ? 'nav-active-child' : '' }}">Point of Sale</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('pos.grid') }}" class="nav-link {{ request()->routeIs('pos.grid') ? 'nav-active-child' : '' }}">Grid POS</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endcan

                        @can('View inventory')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('inventory.*') || request()->routeIs('stock-locations.*') ? 'nav-active-parent' : 'collapsed' }}"
                               href="#sidebarmanageinventory" data-bs-toggle="collapse" role="button"
                               aria-expanded="{{ request()->routeIs('inventory.*') || request()->routeIs('stock-locations.*') ? 'true' : 'false' }}"
                               aria-controls="sidebarmanageinventory">
                                <i class="ph-warehouse"></i> <span>Inventory Operations</span>
                                <i class="ri-arrow-down-s-line ms-auto"></i>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('inventory.*') || request()->routeIs('stock-locations.*') ? 'show' : '' }}" id="sidebarmanageinventory">
                                <ul class="nav nav-sm flex-column">
                                    @can('View inventory')
                                    <li class="nav-item">
                                        <a href="{{ route('inventory.index') }}" class="nav-link {{ request()->routeIs('inventory.index') ? 'nav-active-child' : '' }}">Transactions</a>
                                    </li>
                                    @endcan
                                    @can('View stock levels')
                                    <li class="nav-item">
                                        <a href="{{ route('inventory.stock-levels') }}" class="nav-link {{ request()->routeIs('inventory.stock-levels') ? 'nav-active-child' : '' }}">Stock Levels</a>
                                    </li>
                                    @endcan
                                    @can('Manage stock locations')
                                    <li class="nav-item">
                                        <a href="{{ route('stock-locations.index') }}" class="nav-link {{ request()->routeIs('stock-locations.*') ? 'nav-active-child' : '' }}">Stock Locations</a>
                                    </li>
                                    @endcan
                                    @can('View low stock alerts')
                                    <li class="nav-item">
                                        <a href="{{ route('inventory.low-stock-alerts') }}" class="nav-link {{ request()->routeIs('inventory.low-stock-alerts') ? 'nav-active-child' : '' }}">Low Stock Alerts</a>
                                    </li>
                                    @endcan
                                    @can('View inventory reports')
                                    <li class="nav-item">
                                        <a href="{{ route('inventory.stock-value-report') }}" class="nav-link {{ request()->routeIs('inventory.stock-value-report') ? 'nav-active-child' : '' }}">Stock Value Report</a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        @endcan

                        @can('View order')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('orders.*') ? 'nav-active-parent' : 'collapsed' }}"
                               href="#sidebarorders" data-bs-toggle="collapse" role="button"
                               aria-expanded="{{ request()->routeIs('orders.*') ? 'true' : 'false' }}"
                               aria-controls="sidebarorders">
                                <i class="ph-shopping-cart-simple"></i> <span>Orders Management</span>
                                <i class="ri-arrow-down-s-line ms-auto"></i>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('orders.*') ? 'show' : '' }}" id="sidebarorders">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders.index') ? 'nav-active-child' : '' }}">All Orders</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endcan

                        @can('View customer')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('customers.*') ? 'nav-active-parent' : 'collapsed' }}"
                               href="#sidebarcustomer" data-bs-toggle="collapse" role="button"
                               aria-expanded="{{ request()->routeIs('customers.*') ? 'true' : 'false' }}"
                               aria-controls="sidebarcustomer">
                                <i class="ph-users"></i> <span>Customers Management</span>
                                <i class="ri-arrow-down-s-line ms-auto"></i>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('customers.*') ? 'show' : '' }}" id="sidebarcustomer">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.index') ? 'nav-active-child' : '' }}">All Customers</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endcan

                        @can('View sale')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('sales.*') ? 'nav-active-parent' : 'collapsed' }}"
                               href="#sidebarsales" data-bs-toggle="collapse" role="button"
                               aria-expanded="{{ request()->routeIs('sales.*') ? 'true' : 'false' }}"
                               aria-controls="sidebarsales">
                                <i class="ph-chart-line"></i> <span>Sales Analytics</span>
                                <i class="ri-arrow-down-s-line ms-auto"></i>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('sales.*') ? 'show' : '' }}" id="sidebarsales">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('sales.index') }}" class="nav-link {{ request()->routeIs('sales.index') ? 'nav-active-child' : '' }}">Sales Reports</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('sales.commissions') }}" class="nav-link {{ request()->routeIs('sales.commissions') ? 'nav-active-child' : '' }}">Commissions</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endcan

                        @can('View personal sales dashboard')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('salesperson.*') ? 'nav-active-parent' : 'collapsed' }}"
                               href="#sidebarsalesperson" data-bs-toggle="collapse" role="button"
                               aria-expanded="{{ request()->routeIs('salesperson.*') ? 'true' : 'false' }}"
                               aria-controls="sidebarsalesperson">
                                <i class="ph-user-tie"></i> <span>My Sales</span>
                                <i class="ri-arrow-down-s-line ms-auto"></i>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('salesperson.*') ? 'show' : '' }}" id="sidebarsalesperson">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('salesperson.dashboard') }}" class="nav-link {{ request()->routeIs('salesperson.dashboard') ? 'nav-active-child' : '' }}">My Dashboard</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('salesperson.commissions') }}" class="nav-link {{ request()->routeIs('salesperson.commissions') ? 'nav-active-child' : '' }}">My Commissions</a>
                                    </li>
                                    <li class="nav-item">
                                        <a href="{{ route('salesperson.performance') }}" class="nav-link {{ request()->routeIs('salesperson.performance') ? 'nav-active-child' : '' }}">My Performance</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endcan

                        <li class="menu-title"><span>Settings</span></li>

                        @can('View store setting')
                        <li class="nav-item">
                            <a class="nav-link menu-link {{ request()->routeIs('settings.store.*') ? 'nav-active-parent' : 'collapsed' }}"
                               href="#sidebarsetting" data-bs-toggle="collapse" role="button"
                               aria-expanded="{{ request()->routeIs('settings.store.*') ? 'true' : 'false' }}"
                               aria-controls="sidebarsetting">
                                <i class="ph-gear"></i> <span>Store Settings</span>
                                <i class="ri-arrow-down-s-line ms-auto"></i>
                            </a>
                            <div class="collapse menu-dropdown {{ request()->routeIs('settings.store.*') ? 'show' : '' }}" id="sidebarsetting">
                                <ul class="nav nav-sm flex-column">
                                    <li class="nav-item">
                                        <a href="{{ route('settings.store.index') }}" class="nav-link {{ request()->routeIs('settings.store.index') ? 'nav-active-child' : '' }}">Store Configuration</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endcan

                    @endif
                </ul>
            </div>
        </div><!-- /scrollbar -->

        <!-- ===== SIDEBAR LOGOUT FOOTER ===== -->
        @auth
        <div class="sidebar-footer">
            <div class="sidebar-footer-user">
                <img src="{{ auth()->user()->profile_image ? asset('storage/'.auth()->user()->profile_image) : asset('theme/layouts/assets/images/users/user-dummy-img.jpg') }}"
                     alt="{{ auth()->user()->name }}">
                <div class="sidebar-footer-user-info">
                    <div class="sidebar-footer-user-name">{{ auth()->user()->name }}</div>
                    <div class="sidebar-footer-user-role">{{ auth()->user()->roles->first()->name ?? 'User' }}</div>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" id="sidebar-logout-form">
                @csrf
                <button type="submit" class="sidebar-logout-btn">
                    <i class="mdi mdi-logout"></i>
                    <span>Sign Out</span>
                </button>
            </form>
        </div>
        @endauth

        <div class="sidebar-background"></div>
    </div><!-- /app-menu -->

    <div class="vertical-overlay"></div>

    <!-- ========== TOPBAR ========== -->
    <header id="page-topbar">
        <div class="layout-width">
            <div class="navbar-header">

                <div class="d-flex align-items-center">
                    <div class="navbar-brand-box horizontal-logo">
                        <a href="{{ route('dashboard') }}" class="logo logo-dark">
                            <span class="logo-sm"><img src="{{ asset('theme/layouts/assets/images/logo-sm.png') }}" alt="" height="22"></span>
                            <span class="logo-lg"><img src="{{ asset('theme/layouts/assets/images/logo-dark.png') }}" alt="" height="22"></span>
                        </a>
                        <a href="{{ route('dashboard') }}" class="logo logo-light">
                            <span class="logo-sm"><img src="{{ asset('theme/layouts/assets/images/logo-sm.png') }}" alt="" height="22"></span>
                            <span class="logo-lg"><img src="{{ asset('theme/layouts/assets/images/logo-light.png') }}" alt="" height="22"></span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger shadow-none" id="topnav-hamburger-icon">
                        <span class="hamburger-icon"><span></span><span></span><span></span></span>
                    </button>

                    <!-- Spotlight trigger -->
                    <div class="d-none d-md-inline-flex align-items-center ms-2">
                        <button type="button" id="spotlight-trigger"
                                style="display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:10px;padding:7px 14px;cursor:pointer;transition:all .2s;min-width:220px;">
                            <i class="mdi mdi-magnify" style="font-size:16px;opacity:.6;"></i>
                            <span style="font-size:13px;opacity:.55;flex:1;text-align:left;">Search everything…</span>
                            <div style="display:flex;gap:4px;">
                                <kbd style="font-size:10px;padding:2px 6px;border-radius:4px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);opacity:.7;">⌘</kbd>
                                <kbd style="font-size:10px;padding:2px 6px;border-radius:4px;background:rgba(255,255,255,.12);border:1px solid rgba(255,255,255,.2);opacity:.7;">K</kbd>
                            </div>
                        </button>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-1">

                    <!-- ===== THEME TOGGLE ===== -->
                    <!-- We use a plain button + manual dropdown to bypass app.js entirely -->
                    <div class="position-relative" id="theme-toggle-wrapper">
                        <button type="button"
                                id="theme-toggle-btn"
                                class="btn btn-icon btn-topbar btn-ghost-dark rounded-circle"
                                title="Switch theme"
                                style="width:38px;height:38px;">
                            <i id="theme-icon" class="bi bi-sun align-middle fs-3xl"></i>
                        </button>
                        <!-- Manual dropdown — no data-bs-toggle so app.js never touches it -->
                        <div id="theme-dropdown"
                             style="display:none;position:absolute;top:calc(100% + 8px);right:0;min-width:170px;
                                    background:var(--vz-dropdown-bg,#fff);border:1px solid var(--vz-border-color,#e9ebec);
                                    border-radius:10px;box-shadow:0 8px 24px rgba(0,0,0,.12);z-index:9999;overflow:hidden;padding:6px;">
                            <a href="javascript:void(0)" class="theme-mode-item d-flex align-items-center gap-2 px-3 py-2 rounded-2 text-decoration-none" data-mode="light"
                               style="font-size:13px;color:inherit;transition:background .15s;">
                                <i class="bi bi-sun"></i> <span>Light</span>
                            </a>
                            <a href="javascript:void(0)" class="theme-mode-item d-flex align-items-center gap-2 px-3 py-2 rounded-2 text-decoration-none" data-mode="dark"
                               style="font-size:13px;color:inherit;transition:background .15s;">
                                <i class="bi bi-moon"></i> <span>Dark</span>
                            </a>
                            <a href="javascript:void(0)" class="theme-mode-item d-flex align-items-center gap-2 px-3 py-2 rounded-2 text-decoration-none" data-mode="auto"
                               style="font-size:13px;color:inherit;transition:background .15s;">
                                <i class="bi bi-moon-stars"></i> <span>Auto (system)</span>
                            </a>
                        </div>
                    </div>

                    <!-- ===== USER DROPDOWN ===== -->
                    @php $userdata = Auth::user(); @endphp
                    <div class="position-relative" id="user-dropdown-wrapper">
                        <button type="button"
                                id="user-menu-btn"
                                class="btn shadow-none d-flex align-items-center gap-2 px-2 py-1"
                                style="border-radius:10px;border:1px solid transparent;transition:border-color .2s;"
                                title="Account menu">
                            @if($userdata)
                                <img class="rounded-circle header-profile-user-enhanced"
                                     src="{{ $userdata->profile_image ? asset('storage/'.$userdata->profile_image) : asset('theme/layouts/assets/images/users/user-dummy-img.jpg') }}"
                                     alt="{{ $userdata->name }}"
                                     style="width:36px;height:36px;object-fit:cover;flex-shrink:0;">
                                <span class="d-none d-xl-flex flex-column align-items-start" style="line-height:1.2;">
                                    <span class="fw-semibold" style="font-size:13px;max-width:120px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $userdata->name }}</span>
                                    <span class="text-muted" style="font-size:11px;">{{ $userdata->roles->first()->name ?? 'User' }}</span>
                                </span>
                                <i class="bi bi-chevron-down d-none d-xl-inline" style="font-size:10px;opacity:.5;"></i>
                            @else
                                <img class="rounded-circle header-profile-user-enhanced"
                                     src="{{ asset('theme/layouts/assets/images/users/user-dummy-img.jpg') }}"
                                     alt="Guest"
                                     style="width:36px;height:36px;object-fit:cover;flex-shrink:0;">
                                <span class="d-none d-xl-inline fw-semibold" style="font-size:13px;">Guest</span>
                            @endif
                        </button>

                        <!-- Manual dropdown panel -->
                        <div id="user-dropdown"
                             style="display:none;position:absolute;top:calc(100% + 8px);right:0;min-width:210px;
                                    background:var(--vz-dropdown-bg,#fff);border:1px solid var(--vz-border-color,#e9ebec);
                                    border-radius:12px;box-shadow:0 8px 32px rgba(0,0,0,.14);z-index:9999;overflow:hidden;">
                            @if($userdata)
                                <!-- Gradient header -->
                                <div class="d-flex align-items-center gap-2 px-3 py-3"
                                     style="background:linear-gradient(135deg,#405189 0%,#4f8ef7 100%);">
                                    <img src="{{ $userdata->profile_image ? asset('storage/'.$userdata->profile_image) : asset('theme/layouts/assets/images/users/user-dummy-img.jpg') }}"
                                         alt="{{ $userdata->name }}"
                                         style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.4);flex-shrink:0;">
                                    <div style="min-width:0;">
                                        <div class="text-white fw-semibold" style="font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:135px;">{{ $userdata->name }}</div>
                                        <div style="font-size:11px;color:rgba(255,255,255,.75);">{{ $userdata->roles->first()->name ?? 'User' }}</div>
                                    </div>
                                </div>
                                <div style="padding:6px;">
                                    <a href="{{ route('user.overview', $userdata) }}"
                                       class="d-flex align-items-center gap-2 px-3 py-2 rounded-2 text-decoration-none"
                                       style="font-size:13px;color:inherit;transition:background .15s;">
                                        <i class="mdi mdi-account-circle-outline fs-lg text-muted"></i>
                                        <span>My Profile</span>
                                    </a>
                                    <hr style="margin:4px 0;border-color:var(--vz-border-color,#e9ebec);">
                                    <form method="POST" action="{{ route('logout') }}" id="topbar-logout-form">
                                        @csrf
                                        <a href="{{ route('logout') }}"
                                           onclick="event.preventDefault();document.getElementById('topbar-logout-form').submit();"
                                           class="d-flex align-items-center gap-2 px-3 py-2 rounded-2 text-decoration-none text-danger"
                                           style="font-size:13px;transition:background .15s;">
                                            <i class="mdi mdi-logout fs-lg"></i>
                                            <span>Sign Out</span>
                                        </a>
                                    </form>
                                </div>
                            @else
                                <div style="padding:6px;">
                                    <a href="{{ route('login') }}"
                                       class="d-flex align-items-center gap-2 px-3 py-2 rounded-2 text-decoration-none"
                                       style="font-size:13px;color:inherit;">
                                        <i class="mdi mdi-login fs-lg text-muted"></i>
                                        <span>Login</span>
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                    <!-- /user dropdown -->

                </div><!-- /d-flex align-items-center gap-1 -->
            </div>
        </div>
    </header>

    @yield('content')

    <footer class="footer">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <script>document.write(new Date().getFullYear())</script> &copy; {{ $storeName }}
                </div>
                <div class="col-sm-6">
                    <div class="text-sm-end d-none d-sm-block">Powered by Qudroid Systems</div>
                </div>
            </div>
        </div>
    </footer>
</div><!-- /layout-wrapper -->

<!-- Back to top -->
<button class="btn btn-dark btn-icon" id="back-to-top" title="Back to top">
    <i class="bi bi-caret-up fs-3xl"></i>
</button>

<!-- Preloader -->
<div id="preloader">
    <div id="status">
        <div class="spinner-border text-primary avatar-sm" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>
</div>

<!-- Customizer trigger -->
<div class="customizer-setting d-none d-md-block">
    <div class="btn btn-info p-2 text-uppercase rounded-end-0 shadow-lg"
         data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas"
         aria-controls="theme-settings-offcanvas">
        <i class="bi bi-gear mb-1"></i> Customizer
    </div>
</div>

<!-- Theme Settings Offcanvas -->
<div class="offcanvas offcanvas-end border-0" tabindex="-1" id="theme-settings-offcanvas">
    <div class="d-flex align-items-center bg-primary bg-gradient p-3 offcanvas-header">
        <div class="me-2">
            <h5 class="mb-1 text-white">Theme Customizer</h5>
            <p class="text-white text-opacity-75 mb-0">Customize your experience</p>
        </div>
        <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-0">
        <div data-simplebar class="h-100">
            <div class="p-4">
                <h6 class="fs-md mb-1">Color Scheme</h6>
                <p class="text-muted fs-sm">Choose Light or Dark Scheme.</p>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="form-check card-radio">
                            <input class="form-check-input" type="radio" name="data-bs-theme" id="layout-mode-light" value="light">
                            <label class="form-check-label p-0 bg-transparent" for="layout-mode-light">
                                <img src="{{ asset('theme/layouts/assets/images/custom-theme/light-mode.png') }}" alt="" class="img-fluid">
                            </label>
                        </div>
                        <h5 class="fs-sm text-center fw-medium mt-2">Light</h5>
                    </div>
                    <div class="col-6">
                        <div class="form-check card-radio dark">
                            <input class="form-check-input" type="radio" name="data-bs-theme" id="layout-mode-dark" value="dark">
                            <label class="form-check-label p-0 bg-transparent" for="layout-mode-dark">
                                <img src="{{ asset('theme/layouts/assets/images/custom-theme/dark-mode.png') }}" alt="" class="img-fluid">
                            </label>
                        </div>
                        <h5 class="fs-sm text-center fw-medium mt-2">Dark</h5>
                    </div>
                </div>

                <div id="sidebar-color">
                    <h6 class="mt-4 fs-md mb-1">Sidebar Color</h6>
                    <p class="text-muted fs-sm">Choose a color of Sidebar.</p>
                    <div class="row">
                        <div class="col-4">
                            <div class="form-check sidebar-setting card-radio">
                                <input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-color-light" value="light">
                                <label class="form-check-label p-0 avatar-md w-100" for="sidebar-color-light">
                                    <span class="d-flex gap-1 h-100"><span class="flex-shrink-0"><span class="bg-white border-end d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-primary-subtle rounded mb-2"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span></span></span><span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span></span></span></span>
                                </label>
                            </div>
                            <h5 class="fs-sm text-center fw-medium mt-2">Light</h5>
                        </div>
                        <div class="col-4">
                            <div class="form-check sidebar-setting card-radio">
                                <input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-color-dark" value="dark">
                                <label class="form-check-label p-0 avatar-md w-100" for="sidebar-color-dark">
                                    <span class="d-flex gap-1 h-100"><span class="flex-shrink-0"><span class="bg-primary d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-soft-light rounded mb-2"></span><span class="d-block p-1 px-2 pb-0 bg-soft-light"></span></span></span><span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span></span></span></span>
                                </label>
                            </div>
                            <h5 class="fs-sm text-center fw-medium mt-2">Dark</h5>
                        </div>
                    </div>
                </div>

                <div id="preloader-menu">
                    <h6 class="mt-4 fw-semibold fs-base">Preloader</h6>
                    <p class="text-muted fs-sm">Choose a preloader.</p>
                    <div class="row">
                        <div class="col-4">
                            <div class="form-check sidebar-setting card-radio">
                                <input class="form-check-input" type="radio" name="data-preloader" id="preloader-view-custom" value="enable">
                                <label class="form-check-label p-0 avatar-md w-100" for="preloader-view-custom">
                                    <span class="d-flex gap-1 h-100"><span class="flex-shrink-0"><span class="bg-light d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-primary-subtle rounded mb-2"></span></span></span><span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span></span></span></span>
                                    <span class="d-flex align-items-center justify-content-center mt-2"><span class="spinner-border text-primary avatar-xxs m-auto" role="status"></span></span>
                                </label>
                            </div>
                            <h5 class="fs-sm text-center fw-medium mt-2">Enable</h5>
                        </div>
                        <div class="col-4">
                            <div class="form-check sidebar-setting card-radio">
                                <input class="form-check-input" type="radio" name="data-preloader" id="preloader-view-none" value="disable">
                                <label class="form-check-label p-0 avatar-md w-100" for="preloader-view-none">
                                    <span class="d-flex gap-1 h-100"><span class="flex-shrink-0"><span class="bg-light d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-primary-subtle rounded mb-2"></span></span></span><span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span></span></span></span>
                                </label>
                            </div>
                            <h5 class="fs-sm text-center fw-medium mt-2">Disable</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="offcanvas-footer border-top p-3 text-center">
        <div class="row">
            <div class="col-6">
                <button type="button" class="btn btn-light w-100" id="reset-layout">Reset</button>
            </div>
        </div>
    </div>
</div>

<!-- SPOTLIGHT MODAL -->
<div id="spotlight-overlay"
     style="display:none;position:fixed;inset:0;z-index:9999;align-items:flex-start;justify-content:center;padding-top:6vh;">
    <div id="spotlight-box"
         style="width:100%;max-width:860px;margin:0 24px;background:rgba(24,26,32,.96);border:1px solid rgba(255,255,255,.1);border-radius:28px;box-shadow:0 32px 80px rgba(0,0,0,.6);overflow:hidden;">
        <div style="display:flex;align-items:center;gap:16px;padding:20px 24px;border-bottom:1px solid rgba(255,255,255,.08);">
            <i class="mdi mdi-magnify" style="font-size:26px;color:#4f8ef7;flex-shrink:0;"></i>
            <input id="spotlight-input" type="text" placeholder="Search for pages, products, customers, orders…" autocomplete="off"
                   style="flex:1;background:transparent;border:none;outline:none;font-size:18px;color:#fff;caret-color:#4f8ef7;padding:8px 0;">
            <kbd id="spotlight-esc" style="font-size:12px;padding:4px 10px;border-radius:8px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);color:rgba(255,255,255,.6);cursor:pointer;">ESC</kbd>
        </div>
        <div id="spotlight-results" style="max-height:520px;overflow-y:auto;padding:12px 0;">
            <div id="spotlight-empty" style="padding:48px 24px;text-align:center;color:rgba(255,255,255,.35);">
                <i class="mdi mdi-lightning-bolt" style="font-size:48px;display:block;margin-bottom:16px;opacity:.4;"></i>
                <span style="font-size:15px;">Start typing to search…</span>
            </div>
            <div id="spotlight-loading" style="display:none;padding:48px;text-align:center;">
                <div style="display:inline-block;width:32px;height:32px;border:2px solid rgba(255,255,255,.15);border-top-color:#4f8ef7;border-radius:50%;animation:loadingSpin .7s linear infinite;"></div>
                <div style="margin-top:16px;font-size:13px;color:rgba(255,255,255,.45);">Searching<span class="typing-dot">.</span><span class="typing-dot">.</span><span class="typing-dot">.</span></div>
            </div>
            <ul id="spotlight-list" style="list-style:none;margin:0;padding:0;display:none;"></ul>
        </div>
        <div style="padding:14px 24px;border-top:1px solid rgba(255,255,255,.07);display:flex;gap:24px;font-size:12px;color:rgba(255,255,255,.35);flex-wrap:wrap;">
            <span><kbd style="background:rgba(255,255,255,.1);border-radius:5px;padding:2px 6px;">⌘K</kbd> / <kbd style="background:rgba(255,255,255,.1);border-radius:5px;padding:2px 6px;">Ctrl+K</kbd> open</span>
            <span><kbd style="background:rgba(255,255,255,.1);border-radius:5px;padding:2px 6px;">ESC</kbd> close</span>
        </div>
    </div>
</div>

<!-- =====================================================
     SCRIPTS
     ===================================================== -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('theme/layouts/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

{{-- app.js may throw — we catch it and continue --}}
<script>
try {
    // We intentionally do NOT include app.js for the dashboard ecommerce init.
    // The theme's app.js expects #preloader and layout elements to exist before it runs.
    // We ensure they do by loading scripts at end-of-body.
} catch(e) {}
</script>
<script src="{{ asset('theme/layouts/assets/js/app.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/libs/simplebar/simplebar.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/js/plugins.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
/* =============================================================
   CORE UI — runs independently of app.js result
   ============================================================= */
(function () {
    'use strict';

    /* ── helpers ─────────────────────────────────────────── */
    function qs(sel, ctx) { return (ctx || document).querySelector(sel); }
    function qsa(sel, ctx) { return (ctx || document).querySelectorAll(sel); }

    /* ── manual dropdown factory ─────────────────────────── */
    function makeDropdown(btnId, panelId) {
        var btn   = document.getElementById(btnId);
        var panel = document.getElementById(panelId);
        if (!btn || !panel) return;

        function open()  { panel.style.display = 'block'; btn.setAttribute('aria-expanded','true'); }
        function close() { panel.style.display = 'none';  btn.setAttribute('aria-expanded','false'); }
        function toggle(){ panel.style.display === 'none' ? open() : close(); }

        btn.addEventListener('click', function(e){ e.stopPropagation(); toggle(); });
        document.addEventListener('click', function(e){
            if (!btn.contains(e.target) && !panel.contains(e.target)) close();
        });
        document.addEventListener('keydown', function(e){
            if (e.key === 'Escape') close();
        });
        /* hover highlight on items */
        qsa('a', panel).forEach(function(a){
            a.addEventListener('mouseenter', function(){ a.style.background='rgba(64,81,137,.08)'; });
            a.addEventListener('mouseleave', function(){ a.style.background=''; });
        });
    }

    /* ── theme (dark mode) ───────────────────────────────── */
    function initTheme() {
        var html    = document.documentElement;
        var iconEl  = document.getElementById('theme-icon');

        var ICON = {
            light : 'bi bi-sun align-middle fs-3xl',
            dark  : 'bi bi-moon align-middle fs-3xl',
            auto  : 'bi bi-moon-stars align-middle fs-3xl'
        };

        function resolvedScheme(mode) {
            if (mode === 'auto') return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            return mode;
        }

        function applyMode(mode) {
            var scheme = resolvedScheme(mode);
            html.setAttribute('data-bs-theme', scheme);
            html.setAttribute('data-topbar',   scheme === 'dark' ? 'dark' : 'light');
            if (iconEl) iconEl.className = ICON[mode] || ICON.light;
            localStorage.setItem('app-theme', mode);

            /* sync customizer radios */
            var r = document.getElementById(scheme === 'dark' ? 'layout-mode-dark' : 'layout-mode-light');
            if (r) r.checked = true;

            /* highlight active item */
            qsa('.theme-mode-item').forEach(function(a){
                a.style.fontWeight = a.getAttribute('data-mode') === mode ? '600' : '';
                a.style.color      = a.getAttribute('data-mode') === mode ? 'var(--vz-primary,#405189)' : '';
            });

            /* close the panel */
            var panel = document.getElementById('theme-dropdown');
            if (panel) panel.style.display = 'none';
        }

        /* apply saved or default */
        applyMode(localStorage.getItem('app-theme') || 'light');

        /* click handlers */
        qsa('.theme-mode-item').forEach(function(a){
            a.addEventListener('click', function(e){
                e.preventDefault();
                applyMode(a.getAttribute('data-mode'));
            });
        });

        /* customizer radios */
        qsa('[name="data-bs-theme"]').forEach(function(r){
            r.addEventListener('change', function(){ applyMode(r.value); });
        });

        /* OS preference change */
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(){
            if (localStorage.getItem('app-theme') === 'auto') applyMode('auto');
        });
    }

    /* ── NProgress ───────────────────────────────────────── */
    function initNProgress() {
        if (typeof NProgress === 'undefined') return;
        NProgress.configure({ showSpinner: false, speed: 400, minimum: 0.1 });
        qsa('a[href]').forEach(function(a){
            var h = a.getAttribute('href') || '';
            if (h && h !== '#' && !h.startsWith('javascript') && !h.startsWith('mailto')
                && !h.startsWith('tel') && !a.hasAttribute('data-bs-toggle')
                && !a.hasAttribute('data-bs-dismiss') && a.getAttribute('target') !== '_blank') {
                a.addEventListener('click', function(){ NProgress.start(); });
            }
        });
        window.addEventListener('pageshow', function(){ NProgress.done(); });
        window.addEventListener('load',     function(){ NProgress.done(); });
    }

    /* ── active sidebar ──────────────────────────────────── */
    function initActiveSidebar() {
        var cur = window.location.pathname;
        qsa('#navbar-nav .nav-sm a.nav-link').forEach(function(link){
            try {
                var lp = new URL(link.href, window.location.origin).pathname;
                if (lp !== cur && !(lp.length > 1 && cur.startsWith(lp))) return;
                link.classList.add('nav-active-child');
                var col = link.closest('.collapse');
                if (!col) return;
                col.classList.add('show');
                var id  = col.id;
                var tog = qs('[data-bs-target="#'+id+'"],[href="#'+id+'"]');
                if (tog) {
                    tog.setAttribute('aria-expanded','true');
                    tog.classList.remove('collapsed');
                    tog.classList.add('nav-active-parent');
                }
            } catch(e) {}
        });
    }

    /* ── ripple ──────────────────────────────────────────── */
    function initRipple() {
        qsa('#navbar-nav .nav-link').forEach(function(link){
            link.addEventListener('click', function(e){
                if (link.hasAttribute('data-bs-toggle')) return;
                var r    = document.createElement('span');
                var rect = link.getBoundingClientRect();
                var s    = Math.max(rect.width, rect.height);
                r.className = 'nav-ripple';
                r.style.cssText = 'width:'+s+'px;height:'+s+'px;left:'+(e.clientX-rect.left-s/2)+'px;top:'+(e.clientY-rect.top-s/2)+'px;';
                link.appendChild(r);
                setTimeout(function(){ r.parentNode && r.parentNode.removeChild(r); }, 650);
            });
        });
    }

    /* ── back to top ─────────────────────────────────────── */
    function initBackToTop() {
        var btn = document.getElementById('back-to-top');
        if (!btn) return;
        window.addEventListener('scroll', function(){ btn.classList.toggle('show', window.scrollY > 300); }, {passive:true});
        btn.addEventListener('click', function(){ window.scrollTo({top:0,behavior:'smooth'}); });
    }

    /* ── hamburger ───────────────────────────────────────── */
    function initHamburger() {
        var ham = document.getElementById('topnav-hamburger-icon');
        if (!ham) return;
        ham.addEventListener('click', function(){
            var body = document.body;
            body.classList.toggle('vertical-sidebar-enable');
            if (window.innerWidth >= 1025) {
                body.classList.toggle('sidebar-enable');
                var html = document.documentElement;
                html.setAttribute('data-sidebar-size', html.getAttribute('data-sidebar-size') === 'sm' ? 'lg' : 'sm');
            }
        });
        var ov = qs('.vertical-overlay');
        if (ov) ov.addEventListener('click', function(){ document.body.classList.remove('vertical-sidebar-enable'); });
    }

    /* ── simplebar ───────────────────────────────────────── */
    function initSimplebar() {
        var el = document.getElementById('scrollbar');
        if (el && typeof SimpleBar !== 'undefined') new SimpleBar(el, {autoHide:true});
    }

    /* ── reset layout ────────────────────────────────────── */
    function initReset() {
        var btn = document.getElementById('reset-layout');
        if (btn) btn.addEventListener('click', function(){ localStorage.clear(); location.reload(); });
    }

    /* ── form progress ───────────────────────────────────── */
    function initFormProgress() {
        if (typeof NProgress === 'undefined') return;
        qsa('form').forEach(function(f){
            if (f.action && !f.dataset.noProgress) {
                f.addEventListener('submit', function(){ NProgress.start(); });
            }
        });
    }

    /* ── INIT ────────────────────────────────────────────── */
    document.addEventListener('DOMContentLoaded', function(){
        initTheme();
        makeDropdown('theme-toggle-btn', 'theme-dropdown');
        makeDropdown('user-menu-btn',    'user-dropdown');
        initActiveSidebar();
        initRipple();
        initBackToTop();
        initHamburger();
        initSimplebar();
        initReset();
        initNProgress();
        initFormProgress();
    });

})();
</script>

<!-- SPOTLIGHT -->
<script>
(function(){
    var PAGES = [
        {title:'Dashboard',              url:'{{ route("dashboard") }}',                   icon:'mdi-gauge',           category:'Main'},
        {title:'Users',                  url:'{{ route("users.index") }}',                 icon:'mdi-account-group',   category:'Users & Privileges'},
        {title:'Roles',                  url:'{{ route("roles.index") }}',                 icon:'mdi-shield-account',  category:'Users & Privileges'},
        {title:'Permissions',            url:'{{ route("permissions.index") }}',           icon:'mdi-lock',            category:'Users & Privileges'},
        {title:'Products',               url:'{{ route("products.index") }}',              icon:'mdi-package-variant', category:'Inventory'},
        {title:'Categories',             url:'{{ route("categories.index") }}',            icon:'mdi-view-list',       category:'Inventory'},
        {title:'Brands',                 url:'{{ route("brands.index") }}',                icon:'mdi-tag',             category:'Inventory'},
        {title:'Banners',                url:'{{ route("banners.index") }}',               icon:'mdi-image',           category:'Marketing'},
        {title:'POS',                    url:'{{ route("pos.index") }}',                   icon:'mdi-cart',            category:'Sales'},
        {title:'Grid POS',               url:'{{ route("pos.grid") }}',                    icon:'mdi-cart',            category:'Sales'},
        {title:'Orders',                 url:'{{ route("orders.index") }}',                icon:'mdi-cart-check',      category:'Sales'},
        {title:'Customers',              url:'{{ route("customers.index") }}',             icon:'mdi-account',         category:'Sales'},
        {title:'Inventory',              url:'{{ route("inventory.index") }}',             icon:'mdi-warehouse',       category:'Inventory'},
        {title:'Stock Levels',           url:'{{ route("inventory.stock-levels") }}',      icon:'mdi-chart-line',      category:'Inventory'},
        {title:'Stock Locations',        url:'{{ route("stock-locations.index") }}',       icon:'mdi-map-marker',      category:'Inventory'},
        {title:'Low Stock Alerts',       url:'{{ route("inventory.low-stock-alerts") }}',  icon:'mdi-alert',           category:'Inventory'},
        {title:'Sales',                  url:'{{ route("sales.index") }}',                 icon:'mdi-chart-line',      category:'Sales'},
        {title:'Salesperson Dashboard',  url:'{{ route("salesperson.dashboard") }}',       icon:'mdi-account-tie',     category:'Sales'},
        {title:'My Commissions',         url:'{{ route("salesperson.commissions") }}',     icon:'mdi-cash-multiple',   category:'Sales'},
        {title:'Store Settings',         url:'{{ route("settings.store.index") }}',        icon:'mdi-cog',             category:'Settings'},
        @auth
        {title:'My Profile', url:'{{ route("user.overview", Auth::user()) }}', icon:'mdi-account-circle', category:'User'},
        @endauth
    ];
    var COLORS = {'Main':'#4f8ef7','Users & Privileges':'#405189','Inventory':'#e76f51','Marketing':'#2a9d8f','Sales':'#10b981','Settings':'#6a0572','User':'#e9c46a'};
    var overlay=document.getElementById('spotlight-overlay'),
        input=document.getElementById('spotlight-input'),
        emptyEl=document.getElementById('spotlight-empty'),
        loadEl=document.getElementById('spotlight-loading'),
        list=document.getElementById('spotlight-list'),
        trigger=document.getElementById('spotlight-trigger'),
        escBtn=document.getElementById('spotlight-esc');
    var timer=null, idx=-1, results=[];
    function open(){if(!overlay)return;overlay.style.display='flex';overlay.style.animation='spotlightOverlayFadeIn .25s ease forwards';setTimeout(function(){if(input)input.focus();},80);}
    function close(){if(!overlay)return;overlay.style.animation='spotlightOverlayFadeOut .2s ease forwards';setTimeout(function(){overlay.style.display='none';if(input)input.value='';empty();},200);}
    function empty(){if(emptyEl){emptyEl.innerHTML='<i class="mdi mdi-lightning-bolt" style="font-size:48px;display:block;margin-bottom:16px;opacity:.4;"></i><span style="font-size:15px;">Start typing to search…</span>';emptyEl.style.display='block';}if(loadEl)loadEl.style.display='none';if(list){list.style.display='none';list.innerHTML='';}results=[];idx=-1;}
    function loading(){if(emptyEl)emptyEl.style.display='none';if(loadEl)loadEl.style.display='block';if(list)list.style.display='none';}
    function search(q){if(!q){empty();return;}loading();var lq=q.toLowerCase();var sr=PAGES.filter(function(p){return p.title.toLowerCase().includes(lq)||p.category.toLowerCase().includes(lq);}).slice(0,15);render(sr);clearTimeout(timer);timer=setTimeout(function(){if(q.length<2)return;fetch('/api/search?q='+encodeURIComponent(q)+'&_token={{ csrf_token() }}',{headers:{'Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'}}).then(function(r){return r.ok?r.json():{results:[]};}).then(function(d){if(!input||input.value.trim()!==q)return;var m=sr.concat(d.results||[]),seen={};render(m.filter(function(r){if(seen[r.url])return false;seen[r.url]=true;return true;}));}).catch(function(){});},280);}
    function render(rs){if(loadEl)loadEl.style.display='none';if(emptyEl)emptyEl.style.display='none';if(list){list.innerHTML='';list.style.display='block';}idx=-1;results=rs;if(!rs.length){if(emptyEl){emptyEl.innerHTML='<i class="mdi mdi-magnify-close" style="font-size:42px;display:block;margin-bottom:16px;opacity:.4;"></i><span style="font-size:15px;">No results for "'+(input?input.value:'')+'"</span>';emptyEl.style.display='block';}if(list)list.style.display='none';return;}var g={};rs.forEach(function(r){if(!g[r.category])g[r.category]=[];g[r.category].push(r);});Object.keys(g).forEach(function(cat){var h=document.createElement('li');h.style.cssText='padding:12px 24px 6px;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:rgba(255,255,255,.35);';h.textContent=cat;list.appendChild(h);g[cat].forEach(function(r){var li=document.createElement('li');li.className='spotlight-result-item';li.style.cssText='display:flex;align-items:center;gap:14px;padding:12px 24px;cursor:pointer;transition:all .2s;border-radius:10px;margin:4px 12px;';var c=COLORS[r.category]||'#4f8ef7';li.innerHTML='<span style="width:36px;height:36px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:'+c+'22;"><i class="'+(r.icon||'mdi-chevron-right')+' mdi" style="font-size:18px;color:'+c+';"></i></span><span style="flex:1;min-width:0;"><span style="display:block;font-size:15px;font-weight:500;color:#fff;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">'+r.title+'</span><span style="display:block;font-size:12px;color:rgba(255,255,255,.4);margin-top:2px;">'+(r.subtitle||r.category)+'</span></span><i class="mdi mdi-arrow-right" style="font-size:16px;color:rgba(255,255,255,.25);flex-shrink:0;"></i>';li.addEventListener('mouseover',function(){li.style.background='rgba(255,255,255,.06)';});li.addEventListener('mouseout',function(){li.style.background='';});li.addEventListener('click',function(){window.location.href=r.url;});list.appendChild(li);});});}
    if(trigger)trigger.addEventListener('click',open);
    if(escBtn)escBtn.addEventListener('click',close);
    if(overlay)overlay.addEventListener('click',function(e){if(e.target===overlay)close();});
    document.addEventListener('keydown',function(e){if((e.metaKey||e.ctrlKey)&&e.key==='k'){e.preventDefault();overlay&&overlay.style.display==='flex'?close():open();}if(e.key==='Escape'&&overlay&&overlay.style.display==='flex')close();});
    if(input){input.addEventListener('input',function(){search(this.value.trim());});input.addEventListener('keydown',function(e){if(e.key==='Enter'&&idx>=0&&results[idx])window.location.href=results[idx].url;});}
})();
</script>

<!-- Route-specific JS -->
@if (Route::is('dashboard'))    @include('layouts.pages-assets.js.dashboard-list-js') @endif
@if (Route::is('users.*'))      @include('layouts.pages-assets.js.users-list-js') @endif
@if (Route::is('roles.*'))      @include('layouts.pages-assets.js.role-list-js') @endif
@if (Route::is('permissions.*'))@include('layouts.pages-assets.js.permissions-list-js') @endif
@if (Route::is('brands.*'))     @include('layouts.pages-assets.js.brand-list-js') @endif
@if (Route::is('categories.*')) @include('layouts.pages-assets.js.category-list-js') @endif
@if (Route::is('banners.*'))    @include('layouts.pages-assets.js.banner-list-js') @endif
@if (Route::is('products.*'))   @include('layouts.pages-assets.js.product-list-js') @endif
@if (Route::is('pos.*'))        @include('layouts.pages-assets.js.pos-list-js') @endif
@if (Route::is('inventory.*') || Route::is('stock-locations.*')) @include('layouts.pages-assets.js.inventory-list-js') @endif
@if (Route::is('orders.*'))     @include('layouts.pages-assets.js.order-list-js') @endif
@if (Route::is('customers.*'))  @include('layouts.pages-assets.js.customer-list-js') @endif
@if (Route::is('settings.store.*')) @include('layouts.pages-assets.js.storesetting-list-js') @endif
@if (Route::is('sales.*'))      @include('layouts.pages-assets.js.sale-list-js') @endif
@if (Route::is('salesperson.*'))@include('layouts.pages-assets.js.sale-list-js') @endif
</body>
</html>
