<!doctype html>
<html lang="en" data-layout="vertical" data-sidebar="dark" data-sidebar-size="lg" data-preloader="disable" data-theme="default" data-topbar="light" data-bs-theme="light">

<head>
    <meta charset="utf-8">
    <!-- Dynamic Title -->
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
    <link id="fontsLink" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" rel="stylesheet">
    <link href="{{ asset('theme/layouts/assets/fonts/materialdesignicons-webfont.woff2') }}?v=6.5.95" rel="stylesheet" type="font/woff2">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.0.3/src/bold/style.css">

    <!-- NProgress -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.css"/>
    <script src="https://cdn.jsdelivr.net/npm/nprogress@0.2.0/nprogress.min.js"></script>

    <style>
        /* =====================================================
           NPROGRESS CUSTOM STYLE
           ===================================================== */
        #nprogress .bar {
            background: #4f8ef7 !important;
            height: 3px !important;
            box-shadow: 0 0 8px rgba(79, 142, 247, 0.6) !important;
        }
        #nprogress .peg { box-shadow: none !important; }
        #nprogress .spinner { display: none !important; }

        /* =====================================================
           PAGINATION
           ===================================================== */
        .pagination-wrap .page-item { margin: 0 5px; }
        .pagination-wrap .page-link { padding: 5px 10px; }
        .pagination-wrap .active .page-link { background-color: #007bff; color: white; }
        .pagination-wrap .disabled .page-link { pointer-events: none; opacity: 0.5; }

        /* =====================================================
           LOADING SPINNER
           ===================================================== */
        .spin { animation: spin 1s linear infinite; }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* =====================================================
           CHECKBOX
           ===================================================== */
        .form-check-input:checked { background-color: #405189; border-color: #405189; }

        /* =====================================================
           TABLE ROW HOVER
           ===================================================== */
        .table tbody tr {
            transition: background-color 0.15s ease;
        }
        .table tbody tr:hover { background-color: rgba(67, 97, 238, 0.05); }

        /* =====================================================
           MODAL ANIMATIONS
           ===================================================== */
        .modal.fade .modal-dialog {
            transform: translate(0, -50px);
            transition: transform 0.3s ease-out;
        }
        .modal.show .modal-dialog { transform: translate(0, 0); }

        /* =====================================================
           SIDEBAR SCROLLBAR
           ===================================================== */
        #scrollbar { overflow-y: auto; }
        #scrollbar::-webkit-scrollbar { width: 4px; }
        #scrollbar::-webkit-scrollbar-track { background: transparent; }
        #scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.12); border-radius: 4px; }
        #scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.28); }
        .bg-light::-webkit-scrollbar { width: 6px; }
        .bg-light::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        .bg-light::-webkit-scrollbar-thumb { background: #888; border-radius: 10px; }
        .bg-light::-webkit-scrollbar-thumb:hover { background: #555; }

        /* =====================================================
           SIDEBAR: SMOOTH ACCORDION
           ===================================================== */
        #navbar-nav .menu-dropdown { overflow: hidden; }

        /* Chevron rotation */
        #navbar-nav .nav-link.menu-link .ri-arrow-down-s-line {
            transition: transform 0.25s ease;
            display: inline-block;
        }
        #navbar-nav .nav-link.menu-link[aria-expanded="true"] .ri-arrow-down-s-line {
            transform: rotate(180deg);
        }

        /* =====================================================
           SIDEBAR: ACTIVE PARENT ITEM
           ===================================================== */
        #navbar-nav .nav-link.menu-link.nav-active-parent {
            color: #fff !important;
            background: rgba(79, 142, 247, 0.18) !important;
            border-left: 3px solid #4f8ef7;
            padding-left: calc(1.3rem - 3px);
        }
        #navbar-nav .nav-link.menu-link.nav-active-parent i {
            color: #4f8ef7 !important;
        }

        /* =====================================================
           SIDEBAR: ACTIVE CHILD LINK
           ===================================================== */
        #navbar-nav .nav-sm .nav-link.nav-active-child {
            color: #7eb8fb !important;
            font-weight: 500;
        }
        #navbar-nav .nav-sm .nav-link.nav-active-child::before {
            content: '';
            display: inline-block;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: #4f8ef7;
            margin-right: 8px;
            box-shadow: 0 0 0 3px rgba(79, 142, 247, 0.25);
            vertical-align: middle;
            flex-shrink: 0;
            animation: dotPop 0.25s ease;
        }
        @keyframes dotPop {
            from { transform: scale(0); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        /* =====================================================
           RIPPLE EFFECT ON NAV LINKS
           ===================================================== */
        #navbar-nav .nav-link {
            position: relative;
            overflow: hidden;
            transition: color 0.18s ease, background-color 0.18s ease, padding-left 0.18s ease;
        }
        .nav-ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.18);
            transform: scale(0);
            animation: ripple-anim 0.55s linear;
            pointer-events: none;
            z-index: 0;
        }
        @keyframes ripple-anim {
            to { transform: scale(5); opacity: 0; }
        }

        /* =====================================================
           BACK TO TOP BUTTON
           ===================================================== */
        #back-to-top {
            opacity: 0;
            visibility: hidden;
            transform: translateY(12px);
            transition: opacity 0.3s ease, transform 0.3s ease, visibility 0.3s ease;
        }
        #back-to-top.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        #back-to-top:hover {
            transform: translateY(-3px) !important;
        }

        /* =====================================================
           PAGE CONTENT FADE-IN
           ===================================================== */
        .page-content {
            animation: pageFadeIn 0.35s ease;
        }
        @keyframes pageFadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* =====================================================
           PROFILE AVATAR HOVER
           ===================================================== */
        .header-profile-user-enhanced {
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .header-profile-user-enhanced:hover {
            transform: scale(1.07);
            box-shadow: 0 0 0 3px rgba(79, 142, 247, 0.35) !important;
        }

        /* =====================================================
           CARD HOVER LIFT
           ===================================================== */
        .card {
            transition: box-shadow 0.25s ease, transform 0.25s ease;
        }
        .card:hover {
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.08);
        }

        /* =====================================================
           BUTTON MICRO-INTERACTION
           ===================================================== */
        .btn {
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }
        .btn:active {
            transform: scale(0.97);
        }

        /* =====================================================
           MENU TITLE FADE-IN (stagger)
           ===================================================== */
        #navbar-nav > li {
            animation: navItemFadeIn 0.4s ease both;
        }
        #navbar-nav > li:nth-child(1)  { animation-delay: 0.02s; }
        #navbar-nav > li:nth-child(2)  { animation-delay: 0.04s; }
        #navbar-nav > li:nth-child(3)  { animation-delay: 0.06s; }
        #navbar-nav > li:nth-child(4)  { animation-delay: 0.08s; }
        #navbar-nav > li:nth-child(5)  { animation-delay: 0.10s; }
        #navbar-nav > li:nth-child(6)  { animation-delay: 0.12s; }
        #navbar-nav > li:nth-child(7)  { animation-delay: 0.14s; }
        #navbar-nav > li:nth-child(8)  { animation-delay: 0.16s; }
        #navbar-nav > li:nth-child(9)  { animation-delay: 0.18s; }
        #navbar-nav > li:nth-child(10) { animation-delay: 0.20s; }
        @keyframes navItemFadeIn {
            from { opacity: 0; transform: translateX(-8px); }
            to { opacity: 1; transform: translateX(0); }
        }

        /* =====================================================
           DROPDOWN MENU ANIMATION
           ===================================================== */
        .dropdown-menu {
            animation: dropdownFadeIn 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            transform-origin: top right;
        }
        @keyframes dropdownFadeIn {
            from { opacity: 0; transform: translateY(-15px) scale(0.96); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        .topbar-user .dropdown-menu {
            animation: userDropdownSlideIn 0.4s cubic-bezier(0.34, 1.2, 0.64, 1);
        }
        @keyframes userDropdownSlideIn {
            from { opacity: 0; transform: translateY(-20px) scale(0.94); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* =====================================================
           PRINT STYLES
           ===================================================== */
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
        }

        /* =====================================================
           SPOTLIGHT SEARCH ENHANCED ANIMATIONS
           ===================================================== */
        @keyframes spotlightOverlayFadeIn {
            from { background: rgba(0, 0, 0, 0.2); backdrop-filter: blur(0px); }
            to { background: rgba(0, 0, 0, 0.65); backdrop-filter: blur(8px); }
        }
        @keyframes spotlightOverlayFadeOut {
            from { background: rgba(0, 0, 0, 0.65); backdrop-filter: blur(8px); }
            to { background: rgba(0, 0, 0, 0.2); backdrop-filter: blur(0px); }
        }
        @keyframes spotlightModalBounceIn {
            0% { opacity: 0; transform: translateY(-40px) scale(0.9); }
            40% { opacity: 0.8; transform: translateY(8px) scale(1.02); }
            70% { opacity: 0.95; transform: translateY(-3px) scale(0.99); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes resultBounceIn {
            0% { opacity: 0; transform: translateX(-20px) scale(0.95); }
            60% { opacity: 0.8; transform: translateX(4px) scale(1.02); }
            100% { opacity: 1; transform: translateX(0) scale(1); }
        }
        @keyframes loadingSpin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        @keyframes typingDot {
            0%, 60%, 100% { transform: translateY(0); opacity: 0.5; }
            30% { transform: translateY(-4px); opacity: 1; }
        }

        .spotlight-result-item {
            animation: resultBounceIn 0.35s cubic-bezier(0.34, 1.3, 0.64, 1) forwards;
            opacity: 0;
        }
        .spotlight-result-item:nth-child(1) { animation-delay: 0.00s; }
        .spotlight-result-item:nth-child(2) { animation-delay: 0.03s; }
        .spotlight-result-item:nth-child(3) { animation-delay: 0.06s; }
        .spotlight-result-item.top-match {
            animation: resultBounceIn 0.4s cubic-bezier(0.34, 1.3, 0.64, 1) forwards;
            border-left: 3px solid #4f8ef7;
            background: linear-gradient(90deg, rgba(79, 142, 247, 0.08) 0%, transparent 100%);
        }

        .typing-dot {
            display: inline-block;
            animation: typingDot 1.4s infinite ease-in-out;
        }
        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }
    </style>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Route-specific CSS includes -->
    @if (Route::is('dashboard'))
        @include('layouts.pages-assets.css.users-list-css')
    @endif
    @if (Route::is('users.*'))
        @include('layouts.pages-assets.css.users-list-css')
    @endif
    @if (Route::is('roles.*'))
        @include('layouts.pages-assets.css.roles-list-css')
    @endif
    @if (Route::is('permissions.*'))
        @include('layouts.pages-assets.css.permission-list-css')
    @endif
    @if (Route::is('brands.*'))
        @include('layouts.pages-assets.css.users-list-css')
    @endif
    @if (Route::is('categories.*'))
        @include('layouts.pages-assets.css.users-list-css')
    @endif
    @if (Route::is('banners.*'))
        @include('layouts.pages-assets.css.users-list-css')
    @endif
    @if (Route::is('products.*'))
        @include('layouts.pages-assets.css.users-list-css')
    @endif
    @if (Route::is('pos.*'))
        @include('layouts.pages-assets.css.users-list-css')
    @endif
    @if (Route::is('inventory.*') || Route::is('stock-locations.*'))
        @include('layouts.pages-assets.css.users-list-css')
    @endif
    @if (Route::is('orders.*'))
        @include('layouts.pages-assets.css.users-list-css')
    @endif
    @if (Route::is('customers.*'))
        @include('layouts.pages-assets.css.users-list-css')
    @endif
    @if (Route::is('settings.store.*'))
        @include('layouts.pages-assets.css.users-list-css')
    @endif
    @if (Route::is('sales.*'))
        @include('layouts.pages-assets.css.users-list-css')
    @endif
    @if (Route::is('salesperson.*'))
        @include('layouts.pages-assets.css.users-list-css')
    @endif
</head>

<body>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <!-- ========== App Menu ========== -->
        <div class="app-menu navbar-menu">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="{{ route('dashboard') }}" class="logo logo-dark">
                    <span class="logo-sm">
                        @if($store && $store->logo)
                            <img src="{{ $store->getLogoUrlAttribute() }}" alt="{{ $storeName }}" height="22">
                        else
                            <img src="{{ asset('theme/layouts/assets/images/logo-sm.png') }}" alt="Logo" height="22">
                        @endif
                    </span>
                    <span class="logo-lg">
                        @if($store && $store->logo)
                            <img src="{{ $store->getLogoUrlAttribute() }}" alt="{{ $storeName }}" height="60">
                        else
                            <img src="{{ asset('theme/layouts/assets/images/logo-dark.png') }}" alt="Logo" height="60">
                        @endif
                    </span>
                </a>

                <a href="{{ route('dashboard') }}" class="logo logo-light">
                    <span class="logo-sm">
                        @if($store && $store->logo)
                            <img src="{{ $store->getLogoUrlAttribute() }}" alt="{{ $storeName }}" height="22">
                        else
                            <img src="{{ asset('theme/layouts/assets/images/logo-sm.png') }}" alt="Logo" height="22">
                        @endif
                    </span>
                    <span class="logo-lg">
                        @if($store && $store->logo)
                            <img src="{{ $store->getLogoUrlAttribute() }}" alt="{{ $storeName }}" height="60">
                        else
                            <img src="{{ asset('theme/layouts/assets/images/logo-light.png') }}" alt="Logo" height="60">
                        @endif
                    </span>
                </a>

                <button type="button" class="btn btn-sm p-0 fs-3xl header-item float-end btn-vertical-sm-hover" id="vertical-hover">
                    <i class="ri-record-circle-line"></i>
                </button>
            </div>

            <div id="scrollbar">
                <div class="container-fluid">
                    <div id="two-column-menu"></div>
                    <ul class="navbar-nav" id="navbar-nav">
                        @yield('sidebar')
                    </ul>
                </div>
            </div>
            <div class="sidebar-background"></div>
        </div>
        <!-- Left Sidebar End -->

        <div class="vertical-overlay"></div>

        <!-- ========== Header ========== -->
        <header id="page-topbar">
            <div class="layout-width">
                <div class="navbar-header">
                    <div class="d-flex">
                        <div class="navbar-brand-box horizontal-logo">
                            <a href="{{ route('dashboard') }}" class="logo logo-dark">
                                <span class="logo-sm">
                                    <img src="{{ asset('theme/layouts/assets/images/logo-sm.png') }}" alt="" height="22">
                                </span>
                                <span class="logo-lg">
                                    <img src="{{ asset('theme/layouts/assets/images/logo-dark.png') }}" alt="" height="22">
                                </span>
                            </a>
                            <a href="{{ route('dashboard') }}" class="logo logo-light">
                                <span class="logo-sm">
                                    <img src="{{ asset('theme/layouts/assets/images/logo-sm.png') }}" alt="" height="22">
                                </span>
                                <span class="logo-lg">
                                    <img src="{{ asset('theme/layouts/assets/images/logo-light.png') }}" alt="" height="22">
                                </span>
                            </a>
                        </div>

                        <button type="button" class="btn btn-sm px-3 fs-16 header-item vertical-menu-btn topnav-hamburger shadow-none" id="topnav-hamburger-icon">
                            <span class="hamburger-icon"><span></span><span></span><span></span></span>
                        </button>

                        <!-- SPOTLIGHT SEARCH TRIGGER BUTTON -->
                        <div class="d-none d-md-inline-flex align-items-center" style="position:relative;">
                            <button type="button"
                                    id="spotlight-trigger"
                                    style="display:flex; align-items:center; gap:8px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); border-radius:10px; padding:7px 14px; cursor:pointer; transition:all 0.2s ease; min-width:220px;">
                                <i class="mdi mdi-magnify" style="font-size:16px; opacity:0.6;"></i>
                                <span style="font-size:13px; opacity:0.55; flex:1; text-align:left;">Search everything…</span>
                                <div style="display:flex; gap:4px;">
                                    <kbd style="font-size:10px; padding:2px 6px; border-radius:4px; background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2); opacity:0.7;">⌘</kbd>
                                    <kbd style="font-size:10px; padding:2px 6px; border-radius:4px; background:rgba(255,255,255,0.12); border:1px solid rgba(255,255,255,0.2); opacity:0.7;">K</kbd>
                                </div>
                            </button>
                            <div class="search-tooltip"
                                 style="position:absolute; bottom:-35px; left:0; background:rgba(0,0,0,0.85); color:#fff; font-size:11px; padding:4px 10px; border-radius:6px; white-space:nowrap; opacity:0; transition:opacity 0.2s; pointer-events:none; z-index:100; backdrop-filter:blur(4px);">
                                Press <kbd style="background:rgba(255,255,255,0.2); padding:2px 5px; border-radius:4px; margin:0 2px;">⌘K</kbd> or
                                <kbd style="background:rgba(255,255,255,0.2); padding:2px 5px; border-radius:4px; margin:0 2px;">Ctrl+K</kbd> to search
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center">
                        <div class="dropdown topbar-head-dropdown ms-1 header-item">
                            <button type="button" class="btn btn-icon btn-topbar btn-ghost-dark rounded-circle mode-layout" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="bi bi-sun align-middle fs-3xl"></i>
                            </button>
                            <div class="dropdown-menu p-2 dropdown-menu-end" id="light-dark-mode">
                                <a href="#!" class="dropdown-item" data-mode="light"><i class="bi bi-sun align-middle me-2"></i> Default (light mode)</a>
                                <a href="#!" class="dropdown-item" data-mode="dark"><i class="bi bi-moon align-middle me-2"></i> Dark</a>
                                <a href="#!" class="dropdown-item" data-mode="auto"><i class="bi bi-moon-stars align-middle me-2"></i> Auto (system default)</a>
                            </div>
                        </div>

                        <div class="dropdown ms-sm-3 header-item topbar-user">
                            <button type="button" class="btn shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="d-flex align-items-center">
                                    @php
                                        use App\Models\User;
                                        $userdata = Auth::user();
                                    @endphp

                                    @if($userdata)
                                        <img class="rounded-circle header-profile-user-enhanced" src="{{ $userdata->profile_image ? asset('storage/' . $userdata->profile_image) : asset('theme/layouts/assets/images/users/user-dummy-img.jpg') }}" alt="{{ $userdata->name }}" style="width:42px; height:42px; object-fit:cover;">
                                        <span class="text-start ms-xl-2">
                                            <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">{{ $userdata->name }}</span>
                                            <span class="d-none d-xl-block ms-1 fs-sm user-name-sub-text">{{ $userdata->roles->first()->name ?? 'User' }}</span>
                                        </span>
                                    @else
                                        <img class="rounded-circle header-profile-user-enhanced" src="{{ asset('theme/layouts/assets/images/users/user-dummy-img.jpg') }}" alt="User" style="width:42px; height:42px; object-fit:cover;">
                                        <span class="text-start ms-xl-2">
                                            <span class="d-none d-xl-inline-block ms-1 fw-medium user-name-text">Guest</span>
                                            <span class="d-none d-xl-block ms-1 fs-sm user-name-sub-text">Not logged in</span>
                                        </span>
                                    @endif
                                </span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                @if($userdata)
                                    <h6 class="dropdown-header">Welcome {{ $userdata->name }}!</h6>
                                    <a class="dropdown-item" href="{{ route('user.overview', $userdata->id) }}">
                                        <i class="mdi mdi-account-circle text-muted fs-lg align-middle me-1"></i>
                                        <span class="align-middle">Profile</span>
                                    </a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); this.closest('form').submit();">
                                            <i class="mdi mdi-logout text-muted fs-lg align-middle me-1"></i>
                                            <span class="align-middle" data-key="t-logout">Logout</span>
                                        </a>
                                    </form>
                                @else
                                    <a class="dropdown-item" href="{{ route('login') }}">
                                        <i class="mdi mdi-login text-muted fs-lg align-middle me-1"></i>
                                        <span class="align-middle">Login</span>
                                    </a>
                                @endif
                            </div>
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
                        <div class="text-sm-end d-none d-sm-block">Powered by Qudroid Systems</div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- Back to Top -->
    <button class="btn btn-dark btn-icon" id="back-to-top" title="Back to top">
        <i class="bi bi-caret-up fs-3xl"></i>
    </button>

    <!-- Preloader -->
    <div id="preloader"><div id="status"><div class="spinner-border text-primary avatar-sm" role="status"><span class="visually-hidden">Loading...</span></div></div></div>

    <!-- Customizer -->
    <div class="customizer-setting d-none d-md-block">
        <div class="btn btn-info p-2 text-uppercase rounded-end-0 shadow-lg" data-bs-toggle="offcanvas" data-bs-target="#theme-settings-offcanvas" aria-controls="theme-settings-offcanvas">
            <i class="bi bi-gear mb-1"></i> Customizer
        </div>
    </div>

    <!-- Theme Settings Offcanvas -->
    <div class="offcanvas offcanvas-end border-0" tabindex="-1" id="theme-settings-offcanvas">
        <div class="d-flex align-items-center bg-primary bg-gradient p-3 offcanvas-header">
            <div class="me-2"><h5 class="mb-1 text-white">Theme Customizer</h5><p class="text-white text-opacity-75 mb-0">Customize your experience</p></div>
            <button type="button" class="btn-close btn-close-white ms-auto" id="customizerclose-btn" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body p-0">
            <div data-simplebar class="h-100">
                <div class="p-4">
                    <h6 class="fs-md mb-1">Layout</h6>
                    <p class="text-muted fs-sm">Choose your layout</p>
                    <div class="row">
                        <div class="col-4">
                            <div class="form-check card-radio">
                                <input id="customizer-layout01" name="data-layout" type="radio" value="vertical" class="form-check-input">
                                <label class="form-check-label p-0 avatar-md w-100" for="customizer-layout01">
                                    <span class="d-flex gap-1 h-100">
                                        <span class="flex-shrink-0"><span class="bg-light d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-primary-subtle rounded mb-2"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span></span></span>
                                        <span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span></span></span>
                                    </span>
                                </label>
                            </div>
                            <h5 class="fs-sm text-center fw-medium mt-2">Vertical</h5>
                        </div>
                        <div class="col-4">
                            <div class="form-check card-radio">
                                <input id="customizer-layout02" name="data-layout" type="radio" value="horizontal" class="form-check-input">
                                <label class="form-check-label p-0 avatar-md w-100" for="customizer-layout02">
                                    <span class="d-flex h-100 flex-column gap-1"><span class="bg-light d-flex p-1 gap-1 align-items-center"><span class="d-block p-1 bg-primary-subtle rounded me-1"></span><span class="d-block p-1 pb-0 px-2 bg-primary-subtle ms-auto"></span><span class="d-block p-1 pb-0 px-2 bg-primary-subtle"></span></span><span class="bg-light d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span></span>
                                </label>
                            </div>
                            <h5 class="fs-sm text-center fw-medium mt-2">Horizontal</h5>
                        </div>
                    </div>

                    <h6 class="mt-4 fs-md mb-1">Color Scheme</h6>
                    <p class="text-muted fs-sm">Choose Light or Dark Scheme.</p>
                    <div class="colorscheme-cardradio">
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="form-check card-radio">
                                    <input class="form-check-input" type="radio" name="data-bs-theme" id="layout-mode-light" value="light">
                                    <label class="form-check-label p-0 bg-transparent" for="layout-mode-light">
                                        <img src="{{ asset('theme/layouts/assets/images/custom-theme/light-mode.png')}}" alt="" class="img-fluid">
                                    </label>
                                </div>
                                <h5 class="fs-sm text-center fw-medium mt-2">Light</h5>
                            </div>
                            <div class="col-6">
                                <div class="form-check card-radio dark">
                                    <input class="form-check-input" type="radio" name="data-bs-theme" id="layout-mode-dark" value="dark">
                                    <label class="form-check-label p-0 bg-transparent" for="layout-mode-dark">
                                        <img src="{{ asset('theme/layouts/assets/images/custom-theme/dark-mode.png')}}" alt="" class="img-fluid">
                                    </label>
                                </div>
                                <h5 class="fs-sm text-center fw-medium mt-2">Dark</h5>
                            </div>
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
                                        <span class="d-flex gap-1 h-100"><span class="flex-shrink-0"><span class="bg-white border-end d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-primary-subtle rounded mb-2"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span></span></span><span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span></span></span></span>
                                    </label>
                                </div>
                                <h5 class="fs-sm text-center fw-medium mt-2">Light</h5>
                            </div>
                            <div class="col-4">
                                <div class="form-check sidebar-setting card-radio">
                                    <input class="form-check-input" type="radio" name="data-sidebar" id="sidebar-color-dark" value="dark">
                                    <label class="form-check-label p-0 avatar-md w-100" for="sidebar-color-dark">
                                        <span class="d-flex gap-1 h-100"><span class="flex-shrink-0"><span class="bg-primary d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-soft-light rounded mb-2"></span><span class="d-block p-1 px-2 pb-0 bg-soft-light"></span><span class="d-block p-1 px-2 pb-0 bg-soft-light"></span><span class="d-block p-1 px-2 pb-0 bg-soft-light"></span></span></span><span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span></span></span></span>
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
                                        <span class="d-flex gap-1 h-100"><span class="flex-shrink-0"><span class="bg-light d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-primary-subtle rounded mb-2"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span></span></span><span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span></span></span></span>
                                        <span class="d-flex align-items-center justify-content-center mt-2"><span class="spinner-border text-primary avatar-xxs m-auto" role="status"></span></span>
                                    </label>
                                </div>
                                <h5 class="fs-sm text-center fw-medium mt-2">Enable</h5>
                            </div>
                            <div class="col-4">
                                <div class="form-check sidebar-setting card-radio">
                                    <input class="form-check-input" type="radio" name="data-preloader" id="preloader-view-none" value="disable">
                                    <label class="form-check-label p-0 avatar-md w-100" for="preloader-view-none">
                                        <span class="d-flex gap-1 h-100"><span class="flex-shrink-0"><span class="bg-light d-flex h-100 flex-column gap-1 p-1"><span class="d-block p-1 px-2 bg-primary-subtle rounded mb-2"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span><span class="d-block p-1 px-2 pb-0 bg-primary-subtle"></span></span></span><span class="flex-grow-1"><span class="d-flex h-100 flex-column"><span class="bg-light d-block p-1"></span><span class="bg-light d-block p-1 mt-auto"></span></span></span></span>
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

    <!-- SPOTLIGHT SEARCH MODAL -->
    <div id="spotlight-overlay"
         style="display:none; position:fixed; inset:0; z-index:9999; align-items:flex-start; justify-content:center; padding-top:6vh;">
        <div id="spotlight-box"
             style="width:100%; max-width:860px; margin:0 24px; background:rgba(24, 26, 32, 0.96); border:1px solid rgba(255,255,255,0.1); border-radius:28px; box-shadow:0 32px 80px rgba(0,0,0,0.6); overflow:hidden;">
            <div style="display:flex; align-items:center; gap:16px; padding:20px 24px; border-bottom:1px solid rgba(255,255,255,0.08);">
                <i class="mdi mdi-magnify" style="font-size:26px; color:#4f8ef7; flex-shrink:0;"></i>
                <input id="spotlight-input" type="text" placeholder="Search for pages, products, customers, orders…" autocomplete="off"
                    style="flex:1; background:transparent; border:none; outline:none; font-size:18px; color:#fff; caret-color:#4f8ef7; padding:8px 0;">
                <div style="display:flex; gap:8px;">
                    <kbd id="spotlight-esc"
                         style="font-size:12px; padding:4px 10px; border-radius:8px; background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.15); color:rgba(255,255,255,0.6); cursor:pointer;">
                        ESC
                    </kbd>
                </div>
            </div>
            <div id="spotlight-results" style="max-height:520px; overflow-y:auto; padding:12px 0;">
                <div id="spotlight-empty" style="padding:48px 24px; text-align:center; color:rgba(255,255,255,0.35);">
                    <i class="mdi mdi-lightning-bolt" style="font-size:48px; display:block; margin-bottom:16px; opacity:0.4;"></i>
                    <span style="font-size:15px;">Start typing to search…</span>
                </div>
                <div id="spotlight-loading" style="display:none; padding:48px; text-align:center;">
                    <div style="display:inline-block; width:32px; height:32px; border:2px solid rgba(255,255,255,0.15); border-top-color:#4f8ef7; border-radius:50%; animation:loadingSpin 0.7s linear infinite;"></div>
                    <div style="margin-top:16px; font-size:13px; color:rgba(255,255,255,0.45);">Searching<span class="typing-dot">.</span><span class="typing-dot">.</span><span class="typing-dot">.</span></div>
                </div>
                <ul id="spotlight-list" style="list-style:none; margin:0; padding:0; display:none;"></ul>
            </div>
            <div style="padding:14px 24px; border-top:1px solid rgba(255,255,255,0.07); display:flex; gap:24px; font-size:12px; color:rgba(255,255,255,0.35); flex-wrap:wrap;">
                <span><kbd style="background:rgba(255,255,255,0.1); border-radius:5px; padding:2px 6px;">⌘K</kbd> or <kbd style="background:rgba(255,255,255,0.1); border-radius:5px; padding:2px 6px;">Ctrl+K</kbd> open</span>
                <span><kbd style="background:rgba(255,255,255,0.1); border-radius:5px; padding:2px 6px;">↑↓</kbd> navigate</span>
                <span><kbd style="background:rgba(255,255,255,0.1); border-radius:5px; padding:2px 6px;">↵</kbd> open</span>
                <span><kbd style="background:rgba(255,255,255,0.1); border-radius:5px; padding:2px 6px;">ESC</kbd> close</span>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('theme/layouts/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('theme/layouts/assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('theme/layouts/assets/js/plugins.js') }}"></script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // NProgress
        if (typeof NProgress !== 'undefined') {
            NProgress.configure({ showSpinner: false, speed: 400, minimum: 0.1 });
            document.querySelectorAll('a[href]').forEach(function (a) {
                var href = a.getAttribute('href');
                if (href && !href.startsWith('#') && !href.startsWith('javascript') && !href.startsWith('mailto') && !href.startsWith('tel') && !a.hasAttribute('data-bs-toggle') && !a.hasAttribute('data-bs-dismiss') && a.getAttribute('target') !== '_blank') {
                    a.addEventListener('click', function () { NProgress.start(); });
                }
            });
            window.addEventListener('pageshow', function () { NProgress.done(); });
            window.addEventListener('load', function () { NProgress.done(); });
        }

        // Active Link Detection
        (function () {
            var currentPath = window.location.pathname;
            var childLinks = document.querySelectorAll('#navbar-nav .nav-sm a.nav-link');
            childLinks.forEach(function (link) {
                try {
                    var linkPath = new URL(link.href, window.location.origin).pathname;
                    var isActive = linkPath === currentPath || (linkPath.length > 1 && currentPath.startsWith(linkPath));
                    if (!isActive) return;
                    link.classList.add('nav-active-child');
                    var parentCollapse = link.closest('.collapse');
                    if (parentCollapse) {
                        parentCollapse.classList.add('show');
                        var collapseId = parentCollapse.getAttribute('id');
                        var parentToggle = document.querySelector('[data-bs-target="#' + collapseId + '"], [href="#' + collapseId + '"]');
                        if (parentToggle) {
                            parentToggle.setAttribute('aria-expanded', 'true');
                            parentToggle.classList.remove('collapsed');
                            parentToggle.classList.add('nav-active-parent');
                        }
                    }
                } catch (e) {}
            });
        })();

        // Ripple Effect
        document.querySelectorAll('#navbar-nav .nav-link').forEach(function (link) {
            link.addEventListener('click', function (e) {
                if (link.hasAttribute('data-bs-toggle')) return;
                var ripple = document.createElement('span');
                ripple.classList.add('nav-ripple');
                var rect = link.getBoundingClientRect();
                var size = Math.max(rect.width, rect.height);
                var x = e.clientX - rect.left - size / 2;
                var y = e.clientY - rect.top - size / 2;
                ripple.style.cssText = 'width:' + size + 'px;height:' + size + 'px;left:' + x + 'px;top:' + y + 'px;';
                link.appendChild(ripple);
                setTimeout(function () { if (ripple.parentNode) ripple.parentNode.removeChild(ripple); }, 650);
            });
        });

        // Back to Top
        var backToTop = document.getElementById('back-to-top');
        if (backToTop) {
            window.addEventListener('scroll', function () { backToTop.classList.toggle('show', window.scrollY > 300); }, { passive: true });
            backToTop.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });
        }

        // Reset Layout
        var resetBtn = document.getElementById('reset-layout');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () { localStorage.clear(); location.reload(); });
        }

        // Form Submission NProgress
        document.querySelectorAll('form').forEach(function (form) {
            if (form.getAttribute('action') && !form.dataset.noProgress) {
                form.addEventListener('submit', function () { if (typeof NProgress !== 'undefined') NProgress.start(); });
            }
        });
    });
    </script>

    <!-- SPOTLIGHT SEARCH JAVASCRIPT -->
    <script>
    (function () {
        var STATIC_PAGES = [
            { title: 'Dashboard', url: '{{ route("dashboard") }}', icon: 'mdi-gauge', category: 'Main' },
            { title: 'Users', url: '{{ route("users.index") }}', icon: 'mdi-account-group', category: 'Users & Privileges' },
            { title: 'Roles', url: '{{ route("roles.index") }}', icon: 'mdi-shield-account', category: 'Users & Privileges' },
            { title: 'Permissions', url: '{{ route("permissions.index") }}', icon: 'mdi-lock', category: 'Users & Privileges' },
            { title: 'Products', url: '{{ route("products.index") }}', icon: 'mdi-package-variant', category: 'Inventory' },
            { title: 'Categories', url: '{{ route("categories.index") }}', icon: 'mdi-view-list', category: 'Inventory' },
            { title: 'Brands', url: '{{ route("brands.index") }}', icon: 'mdi-tag', category: 'Inventory' },
            { title: 'Banners', url: '{{ route("banners.index") }}', icon: 'mdi-image', category: 'Marketing' },
            { title: 'POS', url: '{{ route("pos.index") }}', icon: 'mdi-cart', category: 'Sales' },
            { title: 'Orders', url: '{{ route("orders.index") }}', icon: 'mdi-cart-check', category: 'Sales' },
            { title: 'Customers', url: '{{ route("customers.index") }}', icon: 'mdi-account', category: 'Sales' },
            { title: 'Inventory', url: '{{ route("inventory.index") }}', icon: 'mdi-warehouse', category: 'Inventory' },
            { title: 'Stock Levels', url: '{{ route("inventory.stock-levels") }}', icon: 'mdi-chart-line', category: 'Inventory' },
            { title: 'Stock Locations', url: '{{ route("stock-locations.index") }}', icon: 'mdi-map-marker', category: 'Inventory' },
            { title: 'Low Stock Alerts', url: '{{ route("inventory.low-stock-alerts") }}', icon: 'mdi-alert', category: 'Inventory' },
            { title: 'Sales', url: '{{ route("sales.index") }}', icon: 'mdi-chart-line', category: 'Sales' },
            { title: 'Sales Person Dashboard', url: '{{ route("salesperson.dashboard") }}', icon: 'mdi-account-tie', category: 'Sales' },
            { title: 'Store Settings', url: '{{ route("settings.store.index") }}', icon: 'mdi-cog', category: 'Settings' },
            { title: 'My Account', url: '{{ route("user.overview", ["id" => Auth::id()]) }}', icon: 'mdi-account-circle', category: 'User' },
        ];

        var CAT_COLORS = {
            'Main': '#4f8ef7', 'Users & Privileges': '#405189', 'Inventory': '#e76f51',
            'Marketing': '#2a9d8f', 'Sales': '#10b981', 'Settings': '#6a0572', 'User': '#e9c46a'
        };

        var overlay = document.getElementById('spotlight-overlay');
        var input = document.getElementById('spotlight-input');
        var emptyState = document.getElementById('spotlight-empty');
        var loadingEl = document.getElementById('spotlight-loading');
        var list = document.getElementById('spotlight-list');
        var trigger = document.getElementById('spotlight-trigger');
        var escBtn = document.getElementById('spotlight-esc');

        var debounceTimer = null;
        var activeIndex = -1;
        var currentResults = [];

        function openSpotlight() {
            if (!overlay) return;
            overlay.style.display = 'flex';
            overlay.style.animation = 'spotlightOverlayFadeIn 0.25s ease forwards';
            setTimeout(function() { if (input) input.focus(); }, 100);
        }

        function closeSpotlight() {
            if (overlay) overlay.style.animation = 'spotlightOverlayFadeOut 0.2s ease forwards';
            setTimeout(function() {
                if (overlay) overlay.style.display = 'none';
                if (input) input.value = '';
                showEmptyState();
            }, 200);
        }

        function showEmptyState() {
            if (emptyState) emptyState.style.display = 'block';
            if (loadingEl) loadingEl.style.display = 'none';
            if (list) { list.style.display = 'none'; list.innerHTML = ''; }
            currentResults = [];
            activeIndex = -1;
        }

        function showLoading() {
            if (emptyState) emptyState.style.display = 'none';
            if (loadingEl) loadingEl.style.display = 'block';
            if (list) list.style.display = 'none';
        }

        function performSearch(query) {
            if (!query || query.trim().length === 0) { showEmptyState(); return; }
            showLoading();
            var staticResults = STATIC_PAGES.filter(function(p) { return p.title.toLowerCase().includes(query.toLowerCase()) || p.category.toLowerCase().includes(query.toLowerCase()); }).slice(0, 15);
            renderResults(staticResults);

            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() {
                if (query.length < 2) return;
                fetch('/api/search?q=' + encodeURIComponent(query) + '&_token={{ csrf_token() }}', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                }).then(function(r) { return r.ok ? r.json() : { results: [] }; }).then(function(data) {
                    if (input.value.trim() !== query) return;
                    var dynamicResults = data.results || [];
                    var merged = staticResults.concat(dynamicResults);
                    var seen = {};
                    var deduped = merged.filter(function(r) { if (seen[r.url]) return false; seen[r.url] = true; return true; });
                    renderResults(deduped);
                }).catch(function() {});
            }, 280);
        }

        function renderResults(results) {
            if (loadingEl) loadingEl.style.display = 'none';
            if (emptyState) emptyState.style.display = 'none';
            if (list) { list.innerHTML = ''; list.style.display = 'block'; }
            activeIndex = -1;
            currentResults = results;

            if (!results.length) {
                if (emptyState) {
                    emptyState.innerHTML = '<i class="mdi mdi-magnify-close" style="font-size:42px; display:block; margin-bottom:16px; opacity:0.4;"></i><span style="font-size:15px;">No results found for "' + (input ? input.value : '') + '"</span>';
                    emptyState.style.display = 'block';
                }
                if (list) list.style.display = 'none';
                return;
            }

            var grouped = {};
            results.forEach(function(r) { if (!grouped[r.category]) grouped[r.category] = []; grouped[r.category].push(r); });

            var idx = 0;
            Object.keys(grouped).forEach(function(cat) {
                var header = document.createElement('li');
                header.style.cssText = 'padding:12px 24px 6px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; color:rgba(255,255,255,0.35);';
                header.textContent = cat;
                list.appendChild(header);

                grouped[cat].forEach(function(r) {
                    var li = document.createElement('li');
                    li.className = 'spotlight-result-item';
                    li.style.cssText = 'display:flex; align-items:center; gap:14px; padding:12px 24px; cursor:pointer; transition:all 0.2s ease; border-radius:10px; margin:4px 12px;';

                    var accentColor = CAT_COLORS[r.category] || '#4f8ef7';
                    var iconWrap = document.createElement('span');
                    iconWrap.style.cssText = 'width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; background:' + accentColor + '22;';
                    var icon = document.createElement('i');
                    icon.className = (r.icon || 'mdi-chevron-right') + ' mdi';
                    icon.style.cssText = 'font-size:18px; color:' + accentColor + ';';
                    iconWrap.appendChild(icon);

                    var textWrap = document.createElement('span');
                    textWrap.style.cssText = 'flex:1; min-width:0;';
                    var title = document.createElement('span');
                    title.style.cssText = 'display:block; font-size:15px; font-weight:500; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;';
                    title.textContent = r.title;
                    var sub = document.createElement('span');
                    sub.style.cssText = 'display:block; font-size:12px; color:rgba(255,255,255,0.4); margin-top:2px;';
                    sub.textContent = r.subtitle || r.category;
                    textWrap.appendChild(title);
                    textWrap.appendChild(sub);

                    var arrow = document.createElement('i');
                    arrow.className = 'mdi mdi-arrow-right';
                    arrow.style.cssText = 'font-size:16px; color:rgba(255,255,255,0.25); flex-shrink:0; transition:transform 0.2s ease;';

                    li.appendChild(iconWrap);
                    li.appendChild(textWrap);
                    li.appendChild(arrow);
                    li.addEventListener('click', function() { window.location.href = r.url; });
                    list.appendChild(li);
                    idx++;
                });
            });
        }

        if (trigger) trigger.addEventListener('click', openSpotlight);
        if (escBtn) escBtn.addEventListener('click', closeSpotlight);
        if (overlay) overlay.addEventListener('click', function(e) { if (e.target === overlay) closeSpotlight(); });

        document.addEventListener('keydown', function(e) {
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') { e.preventDefault(); overlay && overlay.style.display === 'flex' ? closeSpotlight() : openSpotlight(); }
            if (e.key === 'Escape' && overlay && overlay.style.display === 'flex') closeSpotlight();
        });

        if (input) {
            input.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowDown') { e.preventDefault(); }
                else if (e.key === 'ArrowUp') { e.preventDefault(); }
                else if (e.key === 'Enter' && activeIndex >= 0 && currentResults[activeIndex]) { window.location.href = currentResults[activeIndex].url; }
            });
            input.addEventListener('input', function() { performSearch(this.value.trim()); });
        }
    })();
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
