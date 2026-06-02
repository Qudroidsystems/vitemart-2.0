<!doctype html>
<html lang="en" data-layout="vertical" data-sidebar="dark" data-sidebar-size="lg" data-preloader="disable" data-theme="default" data-topbar="light" data-bs-theme="light">
<head>
    <meta charset="utf-8">
    <title>Sign In | {{ $store->store_name ?? 'Frost Hub POS' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Point of Sale System" name="description">
    <meta content="Qudroid Systems" name="author">

    @php
        $store = \App\Models\StoreSetting::getSettings();
        $storeName = $store->store_name ?? 'Frost Hub POS';
        $storeLogo = $store->getLogoUrlAttribute() ?? asset('theme/layouts/assets/images/logo-dark.png');
    @endphp

    <!-- App favicon -->
    @if($store && $store->logo)
        <link rel="shortcut icon" href="{{ $store->getLogoUrlAttribute() }}">
        <link rel="icon" type="image/png" href="{{ $store->getLogoUrlAttribute() }}">
        <link rel="apple-touch-icon" href="{{ $store->getLogoUrlAttribute() }}">
    @else
        <link rel="shortcut icon" href="{{ asset('theme/layouts/assets/images/favicon.ico') }}">
        <link rel="icon" type="image/png" href="{{ asset('theme/layouts/assets/images/logo-dark.png') }}">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com/">
    <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin>
    <link id="fontsLink" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&amp;display=swap" rel="stylesheet">

    <!-- Layout config Js -->
    <script src="{{ asset('theme/layouts/assets/js/layout.js')}}"></script>

    <!-- Bootstrap Css -->
    <link href="{{ asset('theme/layouts/assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css">

    <!-- Icons Css -->
    <link href="{{ asset('theme/layouts/assets/css/icons.min.css')}}" rel="stylesheet" type="text/css">

    <!-- App Css-->
    <link href="{{ asset('theme/layouts/assets/css/app.min.css')}}" rel="stylesheet" type="text/css">

    <!-- custom Css-->
    <link href="{{ asset('theme/layouts/assets/css/custom.min.css')}}" rel="stylesheet" type="text/css">

    <style>
        /* =====================================================
           APPLE OS STYLE LOGIN PAGE - POS VERSION
           ===================================================== */

        /* Smooth page entrance animation */
        @keyframes pageFadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-40px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(40px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-4px); }
            20%, 40%, 60%, 80% { transform: translateX(4px); }
        }

        @keyframes fieldPulse {
            0%, 100% { border-color: #e2e8f0; box-shadow: 0 0 0 0 rgba(79, 142, 247, 0); }
            50% { border-color: #4f8ef7; box-shadow: 0 0 0 4px rgba(79, 142, 247, 0.15); }
        }

        @keyframes successCheck {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); opacity: 1; }
        }

        @keyframes orbitClockwise {
            from { transform: rotate(0deg) translate(120px, 0) rotate(0deg); }
            to { transform: rotate(360deg) translate(120px, 0) rotate(-360deg); }
        }

        @keyframes orbitCounterClockwise {
            from { transform: rotate(0deg) translate(120px, 0) rotate(0deg); }
            to { transform: rotate(-360deg) translate(120px, 0) rotate(360deg); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.9; }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }

        /* Page container animation */
        .auth-page-wrapper {
            animation: pageFadeInUp 0.6s cubic-bezier(0.2, 0.9, 0.4, 1.1) forwards;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }

        /* Animated background bubbles */
        .bg-bubble {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            animation: float 6s ease-in-out infinite;
            pointer-events: none;
        }

        /* Card entrance animation */
        .card {
            animation: fadeInScale 0.5s cubic-bezier(0.2, 0.9, 0.4, 1.1) forwards;
            border-radius: 28px !important;
            overflow: hidden;
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        /* Left panel animation */
        .auth-card {
            animation: slideInLeft 0.6s cubic-bezier(0.2, 0.9, 0.4, 1.1) forwards;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            border-radius: 0 !important;
        }

        /* Right panel animation */
        .col-xxl-7 {
            animation: slideInRight 0.6s cubic-bezier(0.2, 0.9, 0.4, 1.1) forwards;
            animation-delay: 0.1s;
            opacity: 0;
            animation-fill-mode: forwards;
        }

        /* Apple-style input fields */
        .apple-input {
            border-radius: 12px !important;
            border: 1.5px solid #e2e8f0 !important;
            background: #f8fafc !important;
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1) !important;
            font-size: 16px !important;
            padding: 12px 16px !important;
        }

        .apple-input:focus {
            border-color: #667eea !important;
            background: #ffffff !important;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1), 0 2px 8px rgba(0, 0, 0, 0.05) !important;
            outline: none !important;
        }

        .apple-input:hover {
            border-color: #cbd5e1 !important;
            background: #ffffff !important;
        }

        /* Apple-style button */
        .apple-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            border: none !important;
            border-radius: 12px !important;
            padding: 14px 20px !important;
            font-weight: 600 !important;
            font-size: 16px !important;
            transition: all 0.25s cubic-bezier(0.2, 0.9, 0.4, 1.1) !important;
            position: relative;
            overflow: hidden;
        }

        .apple-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.35);
        }

        .apple-button:active {
            transform: translateY(1px);
        }

        /* Shake animation for error */
        .shake-field {
            animation: shake 0.4s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
        }

        /* Field pulse on focus */
        .field-pulse {
            animation: fieldPulse 0.6s ease;
        }

        /* Label styling */
        .form-label {
            transition: all 0.2s ease;
            font-weight: 500;
            font-size: 14px;
            margin-bottom: 6px;
        }

        /* Checkbox styling */
        .form-check-input {
            border-radius: 6px !important;
            border: 1.5px solid #cbd5e1 !important;
            transition: all 0.2s ease;
        }

        .form-check-input:checked {
            background-color: #667eea !important;
            border-color: #667eea !important;
        }

        /* Error message styling */
        .invalid-feedback {
            font-size: 12px;
            margin-top: 6px;
            animation: slideInRight 0.3s ease;
        }

        /* Password toggle button */
        .password-addon {
            border-radius: 0 12px 12px 0 !important;
            padding: 0 16px !important;
            transition: opacity 0.2s ease;
            z-index: 10;
        }

        .password-addon:hover {
            opacity: 0.7;
        }

        /* Loading state on button */
        .apple-button.loading {
            pointer-events: none;
            opacity: 0.7;
        }

        .apple-button.loading::after {
            content: '';
            position: absolute;
            width: 18px;
            height: 18px;
            top: 50%;
            left: 50%;
            margin-left: -9px;
            margin-top: -9px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        .apple-button.loading span {
            opacity: 0;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Effect circles */
        .effect-circle-1,
        .effect-circle-2,
        .effect-circle-3 {
            transition: all 0.3s ease;
        }

        .effect-circle-1 {
            animation: pulse 2s infinite;
        }

        /* Auth effect main container */
        .auth-effect-main {
            position: relative;
            width: 300px;
            height: 300px;
            margin: 0 auto;
        }

        /* Avatar orbit animations */
        .auth-user-list {
            position: absolute;
            width: 100%;
            height: 100%;
            list-style: none;
            padding: 0;
            margin: 0;
            top: 0;
            left: 0;
        }

        .auth-user-list li {
            position: absolute;
            width: 60px;
            height: 60px;
            transform-origin: center center;
            cursor: pointer;
            transition: transform 0.3s ease;
        }

        .auth-user-list li:nth-child(1) {
            animation: orbitClockwise 12s linear infinite;
            transform: translate(120px, 0);
        }

        .auth-user-list li:nth-child(2) {
            animation: orbitCounterClockwise 14s linear infinite;
            transform: rotate(72deg) translate(115px, 0);
        }

        .auth-user-list li:nth-child(3) {
            animation: orbitClockwise 10s linear infinite;
            transform: rotate(144deg) translate(125px, 0);
        }

        .auth-user-list li:nth-child(4) {
            animation: orbitCounterClockwise 11s linear infinite;
            transform: rotate(216deg) translate(118px, 0);
        }

        .auth-user-list li:nth-child(5) {
            animation: orbitClockwise 13s linear infinite;
            transform: rotate(288deg) translate(122px, 0);
        }

        .auth-user-list li:hover {
            animation-play-state: paused !important;
            transform: scale(1.2) !important;
            z-index: 10;
        }

        .auth-user-list li:hover .avatar-title {
            box-shadow: 0 0 15px rgba(255, 255, 255, 0.7);
        }

        /* Avatar styling */
        .avatar-sm {
            width: 60px;
            height: 60px;
        }

        .avatar-title {
            width: 100%;
            height: 100%;
            overflow: hidden;
            border: 2px solid white;
            transition: box-shadow 0.3s ease;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-title i {
            font-size: 32px;
            color: white;
        }

        /* Store logo styling */
        .store-login-logo {
            height: 70px;
            width: auto;
            border-radius: 14px;
            object-fit: contain;
            transition: transform 0.3s ease;
        }

        .store-login-logo:hover {
            transform: scale(1.02);
        }

        /* Logo container animation */
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
            animation: fadeInScale 0.5s ease;
        }

        /* Toast notification */
        .login-success {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #10b981;
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 500;
            z-index: 9999;
            animation: successCheck 0.4s cubic-bezier(0.34, 1.3, 0.64, 1);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .login-success.error {
            background: #ef4444;
        }

        @keyframes fadeOut {
            to { opacity: 0; transform: translateX(20px); }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .auth-effect-main { width: 220px; height: 220px; }
            .auth-user-list li { width: 45px; height: 45px; }
            .auth-user-list li:nth-child(1) { transform: translate(90px, 0); }
            .auth-user-list li:nth-child(2) { transform: rotate(72deg) translate(88px, 0); }
            .auth-user-list li:nth-child(3) { transform: rotate(144deg) translate(92px, 0); }
            .auth-user-list li:nth-child(4) { transform: rotate(216deg) translate(89px, 0); }
            .auth-user-list li:nth-child(5) { transform: rotate(288deg) translate(91px, 0); }
            @keyframes orbitClockwise {
                from { transform: rotate(0deg) translate(90px, 0) rotate(0deg); }
                to { transform: rotate(360deg) translate(90px, 0) rotate(-360deg); }
            }
            @keyframes orbitCounterClockwise {
                from { transform: rotate(0deg) translate(90px, 0) rotate(0deg); }
                to { transform: rotate(-360deg) translate(90px, 0) rotate(360deg); }
            }
            .avatar-title i { font-size: 24px; }
            .store-login-logo { height: 50px; }
        }

        @media (max-width: 576px) {
            .auth-effect-main { width: 180px; height: 180px; }
            .auth-user-list li { width: 40px; height: 40px; }
            .auth-user-list li:nth-child(1) { transform: translate(70px, 0); }
            .auth-user-list li:nth-child(2) { transform: rotate(72deg) translate(68px, 0); }
            .auth-user-list li:nth-child(3) { transform: rotate(144deg) translate(72px, 0); }
            .auth-user-list li:nth-child(4) { transform: rotate(216deg) translate(69px, 0); }
            .auth-user-list li:nth-child(5) { transform: rotate(288deg) translate(71px, 0); }
            @keyframes orbitClockwise {
                from { transform: rotate(0deg) translate(70px, 0) rotate(0deg); }
                to { transform: rotate(360deg) translate(70px, 0) rotate(-360deg); }
            }
            @keyframes orbitCounterClockwise {
                from { transform: rotate(0deg) translate(70px, 0) rotate(0deg); }
                to { transform: rotate(-360deg) translate(70px, 0) rotate(360deg); }
            }
        }
    </style>
</head>

<body>
    <section class="auth-page-wrapper position-relative d-flex align-items-center justify-content-center min-vh-100">
        <!-- Animated background bubbles -->
        <div class="bg-bubble" style="width: 300px; height: 300px; top: -100px; left: -100px; animation-delay: 0s;"></div>
        <div class="bg-bubble" style="width: 200px; height: 200px; bottom: -50px; right: -50px; animation-delay: 2s;"></div>
        <div class="bg-bubble" style="width: 150px; height: 150px; top: 50%; left: 20%; animation-delay: 4s;"></div>

        <div class="container position-relative" style="z-index: 10;">
            <div class="row justify-content-center">
                <div class="col-lg-11">
                    <div class="card mb-0 border-0 shadow-lg">
                        <div class="row g-0 align-items-center">

                            <!-- Left: Hero Section -->
                            <div class="col-xxl-5">
                                <div class="card auth-card bg-secondary h-100 border-0 shadow-none d-none d-sm-block mb-0">
                                    <div class="card-body py-5 d-flex justify-content-between flex-column">
                                        <div class="text-center">
                                            <h3 class="text-white" style="animation: fadeInScale 0.6s ease;">Welcome to POS System</h3>
                                            <p class="text-white opacity-75 fs-base">Fast, reliable, and easy to use</p>
                                        </div>

                                        <div class="auth-effect-main my-5 position-relative rounded-circle d-flex align-items-center justify-content-center mx-auto">
                                            <div class="effect-circle-1 position-relative mx-auto rounded-circle d-flex align-items-center justify-content-center" style="animation: pulse 2s infinite;">
                                                <div class="effect-circle-2 position-relative mx-auto rounded-circle d-flex align-items-center justify-content-center">
                                                    <div class="effect-circle-3 mx-auto rounded-circle position-relative text-white fs-4xl d-flex align-items-center justify-content-center" style="background: rgba(255,255,255,0.1); backdrop-filter: blur(4px); padding: 20px;">
                                                        <span class="text-white text-center" style="font-weight: 600; font-size: 14px;">POS<br>System</span>
                                                    </div>
                                                </div>
                                            </div>

                                            <ul class="auth-user-list list-unstyled">
                                                <li>
                                                    <div class="avatar-sm d-inline-block">
                                                        <div class="avatar-title bg-white bg-opacity-20 shadow-lg overflow-hidden rounded-circle">
                                                            <i class="ri-shopping-cart-2-fill"></i>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="avatar-sm d-inline-block">
                                                        <div class="avatar-title bg-white bg-opacity-20 shadow-lg overflow-hidden rounded-circle">
                                                            <i class="ri-barcode-box-fill"></i>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="avatar-sm d-inline-block">
                                                        <div class="avatar-title bg-white bg-opacity-20 shadow-lg overflow-hidden rounded-circle">
                                                            <i class="ri-cash-line"></i>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="avatar-sm d-inline-block">
                                                        <div class="avatar-title bg-white bg-opacity-20 shadow-lg overflow-hidden rounded-circle">
                                                            <i class="ri-printer-fill"></i>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="avatar-sm d-inline-block">
                                                        <div class="avatar-title bg-white bg-opacity-20 shadow-lg overflow-hidden rounded-circle">
                                                            <i class="ri-user-settings-fill"></i>
                                                        </div>
                                                    </div>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="text-center">
                                            <p class="text-white opacity-75 mb-0 mt-3">
                                                © <script>document.write(new Date().getFullYear())</script> {{ $storeName }}. <br>
                                                Created with <i class="mdi mdi-heart text-danger"></i> by Qudroid Systems
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!--end col-->

                            <!-- Right: Login Form -->
                            <div class="col-xxl-7 mx-auto">
                                <div class="card mb-0 border-0 shadow-none mb-0" style="background: transparent;">
                                    <div class="card-body p-sm-5 m-lg-4">
                                        <!-- Store Logo on Login Form -->
                                        <div class="logo-container">
                                            @if($store && $store->logo)
                                                <img src="{{ $store->getLogoUrlAttribute() }}"
                                                     alt="{{ $storeName }}"
                                                     class="store-login-logo"
                                                     onerror="this.onerror=null; this.src='{{ asset('theme/layouts/assets/images/logo-dark.png') }}'">
                                            @else
                                                <img src="{{ asset('theme/layouts/assets/images/logo-dark.png') }}"
                                                     alt="Store Logo"
                                                     class="store-login-logo">
                                            @endif
                                        </div>

                                        <div class="text-center mt-2">
                                            <h5 class="fs-2xl fw-semibold" style="animation: fadeInScale 0.5s ease;">{{ $storeName }}</h5>
                                            <p class="text-muted">Sign in to your account</p>
                                        </div>

                                        <div class="p-2 mt-3">
                                            <form method="POST" action="{{ route('login') }}" id="loginForm">
                                                @csrf

                                                <div class="mb-4">
                                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                                    <div class="position-relative">
                                                        <input type="email"
                                                               class="form-control apple-input @error('email') is-invalid @enderror"
                                                               id="email"
                                                               name="email"
                                                               placeholder="Enter your email"
                                                               value="{{ old('email') }}"
                                                               required
                                                               autocomplete="email"
                                                               autofocus>
                                                        @error('email')
                                                            <span class="invalid-feedback" role="alert">
                                                                <strong>{{ $message }}</strong>
                                                            </span>
                                                        @enderror
                                                    </div>
                                                </div>

                                                <div class="mb-4">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <label class="form-label" for="password">Password <span class="text-danger">*</span></label>
                                                        @if (Route::has('password.request'))
                                                            <a href="{{ route('password.request') }}" class="text-muted small text-decoration-none">Forgot password?</a>
                                                        @endif
                                                    </div>
                                                    <div class="position-relative auth-pass-inputgroup">
                                                        <input type="password"
                                                               id="password"
                                                               class="form-control apple-input pe-5 @error('password') is-invalid @enderror"
                                                               name="password"
                                                               autocomplete="current-password"
                                                               placeholder="Enter your password"
                                                               required>
                                                        <button class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon" type="button" id="password-addon">
                                                            <i class="ri-eye-fill align-middle"></i>
                                                        </button>
                                                    </div>
                                                    @error('password')
                                                        <span class="invalid-feedback" role="alert">
                                                            <strong>{{ $message }}</strong>
                                                        </span>
                                                    @enderror
                                                </div>

                                                <div class="form-check mb-4">
                                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="remember">Remember me</label>
                                                </div>

                                                <div class="mt-4">
                                                    <button class="btn btn-primary w-100 apple-button" type="submit" id="loginButton">
                                                        <span>Sign In</span>
                                                    </button>
                                                </div>
                                            </form>

                                            <div class="text-center mt-4">
                                                <p class="mb-0 text-muted small">Don't have an account? <a href="{{ route('register') }}" class="fw-semibold text-primary text-decoration-none">Sign Up</a></p>
                                            </div>
                                        </div>
                                    </div><!-- end card body -->
                                </div><!-- end card -->
                            </div>
                            <!--end col-->
                        </div>
                        <!--end row-->
                    </div>
                </div>
                <!--end col-->
            </div>
            <!--end row-->
        </div>
        <!--end container-->
    </section>

    <!-- JAVASCRIPT -->
    <script src="{{ asset('theme/layouts/assets/libs/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
    <script src="{{ asset('theme/layouts/assets/libs/simplebar/simplebar.min.js')}}"></script>
    <script src="{{ asset('theme/layouts/assets/js/plugins.js')}}"></script>

    <script>
        // =====================================================
        // APPLE OS STYLE LOGIN PAGE - POS VERSION
        // =====================================================

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Bootstrap tooltips (if any)
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });

            // Avatar hover pause animation
            const avatarItems = document.querySelectorAll('.auth-user-list li');
            avatarItems.forEach(item => {
                item.addEventListener('mouseenter', function() {
                    this.style.animationPlayState = 'paused';
                });
                item.addEventListener('mouseleave', function() {
                    this.style.animationPlayState = 'running';
                });
            });

            // Field interactions
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');
            const loginForm = document.getElementById('loginForm');
            const loginButton = document.getElementById('loginButton');

            // Add focus pulse animation to fields
            function addFieldPulse(inputElement) {
                if (!inputElement) return;
                inputElement.addEventListener('focus', function() {
                    this.classList.add('field-pulse');
                    setTimeout(() => {
                        this.classList.remove('field-pulse');
                    }, 600);
                });
            }

            if (emailInput) addFieldPulse(emailInput);
            if (passwordInput) addFieldPulse(passwordInput);

            // Shake animation for error
            function shakeElement(element) {
                if (!element) return;
                element.classList.add('shake-field');
                setTimeout(() => {
                    element.classList.remove('shake-field');
                }, 400);
            }

            // Check for existing errors on page load
            @if($errors->any())
                @if($errors->has('email'))
                    if (emailInput) shakeElement(emailInput);
                @endif
                @if($errors->has('password'))
                    if (passwordInput) shakeElement(passwordInput);
                @endif
                // Show error toast for login failure
                showToast('Login Failed', 'Invalid email or password. Please try again.', 'error');
            @endif

            // Real-time validation - remove error on input
            if (emailInput) {
                emailInput.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                    const errorDiv = this.parentElement.querySelector('.invalid-feedback');
                    if (errorDiv && !errorDiv.innerHTML.includes('credentials')) {
                        errorDiv.remove();
                    }
                });
            }

            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
                    this.classList.remove('is-invalid');
                    const errorDiv = this.parentElement.querySelector('.invalid-feedback');
                    if (errorDiv && !errorDiv.innerHTML.includes('credentials')) {
                        errorDiv.remove();
                    }
                });
            }

            // Email validation on blur
            if (emailInput) {
                emailInput.addEventListener('blur', function() {
                    const email = this.value.trim();
                    const emailRegex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
                    if (email && !emailRegex.test(email)) {
                        this.classList.add('is-invalid');
                        let errorDiv = this.parentElement.querySelector('.invalid-feedback');
                        if (!errorDiv) {
                            errorDiv = document.createElement('span');
                            errorDiv.className = 'invalid-feedback';
                            errorDiv.setAttribute('role', 'alert');
                            this.parentElement.appendChild(errorDiv);
                        }
                        errorDiv.innerHTML = '<strong>Please enter a valid email address.</strong>';
                        shakeElement(this);
                    }
                });
            }

            // Password toggle functionality
            const passwordAddon = document.getElementById('password-addon');
            if (passwordAddon && passwordInput) {
                passwordAddon.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    const icon = this.querySelector('i');
                    if (icon) {
                        icon.className = type === 'password' ? 'ri-eye-fill align-middle' : 'ri-eye-off-fill align-middle';
                    }
                });
            }

            // Loading state on form submit
            if (loginForm && loginButton) {
                loginForm.addEventListener('submit', function() {
                    loginButton.classList.add('loading');
                    loginButton.disabled = true;
                });
            }
        });

        // Toast notification function
        function showToast(title, message, type = 'info') {
            const colors = {
                success: '#10b981',
                error: '#ef4444',
                info: '#667eea',
                warning: '#f59e0b'
            };

            // Remove existing toasts
            const existingToasts = document.querySelectorAll('.login-success');
            existingToasts.forEach(toast => toast.remove());

            const toast = document.createElement('div');
            toast.className = 'login-success ' + (type === 'error' ? 'error' : '');
            toast.style.background = colors[type] || colors.info;
            toast.innerHTML = `
                <i class="mdi mdi-${type === 'success' ? 'check-circle' : type === 'error' ? 'alert-circle' : 'information'}"></i>
                <div>
                    <div style="font-weight: 600; font-size: 13px;">${title}</div>
                    <div style="font-size: 11px; opacity: 0.9;">${message}</div>
                </div>
            `;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.animation = 'fadeOut 0.3s ease forwards';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>
