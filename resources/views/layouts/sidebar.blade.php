@php
    $user = Auth::user();
@endphp

@if($user)
    <li class="menu-title"><span data-key="t-menu">Menu</span></li>

    {{-- Dashboard --}}
    @can('dashboard')
        <li class="nav-item">
            <a class="nav-link menu-link {{ request()->routeIs('dashboard') ? 'nav-active-parent' : 'collapsed' }}"
               href="#sidebarDashboards" data-bs-toggle="collapse" role="button"
               aria-expanded="{{ request()->routeIs('dashboard') ? 'true' : 'false' }}"
               aria-controls="sidebarDashboards">
                <i class="ph-gauge"></i> <span data-key="t-dashboards">Dashboards</span>
                <i class="ri-arrow-down-s-line ms-auto"></i>
            </a>
            <div class="collapse menu-dropdown {{ request()->routeIs('dashboard') ? 'show' : '' }}" id="sidebarDashboards">
                <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'nav-active-child' : '' }}" data-key="t-analytics">
                            Administration Analytics
                        </a>
                    </li>
                    @can('View inventory dashboard')
                        <li class="nav-item">
                            <a href="{{ route('inventory.dashboard') }}" class="nav-link {{ request()->routeIs('inventory.dashboard') ? 'nav-active-child' : '' }}" data-key="t-inventory">
                                Inventory Dashboard
                            </a>
                        </li>
                    @endcan
                </ul>
            </div>
        </li>
    @endcan

    {{-- USERS & PRIVILEGES --}}
    @if(auth()->user()->can('View user') || auth()->user()->can('View role') || auth()->user()->can('View permission'))
        <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-pages">USERS & PRIVILEGES</span></li>
    @endif

    @can('View user')
        <li class="nav-item">
            <a class="nav-link menu-link {{ request()->routeIs('users.*') ? 'nav-active-parent' : 'collapsed' }}"
               href="#sidebarusers" data-bs-toggle="collapse" role="button"
               aria-expanded="{{ request()->routeIs('users.*') ? 'true' : 'false' }}"
               aria-controls="sidebarusers">
                <i class="ph-user-circle"></i> <span data-key="t-authentication">User Management</span>
                <i class="ri-arrow-down-s-line ms-auto"></i>
            </a>
            <div class="collapse menu-dropdown {{ request()->routeIs('users.*') ? 'show' : '' }}" id="sidebarusers">
                <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.index') ? 'nav-active-child' : '' }}" data-key="t-signin">
                            Users
                        </a>
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
                <i class="ph-address-book"></i> <span data-key="t-pages">Roles & Permissions</span>
                <i class="ri-arrow-down-s-line ms-auto"></i>
            </a>
            <div class="collapse menu-dropdown {{ request()->routeIs('roles.*') || request()->routeIs('permissions.*') ? 'show' : '' }}" id="sidebarroles">
                <ul class="nav nav-sm flex-column">
                    @can('View role')
                        <li class="nav-item">
                            <a href="{{ route('roles.index') }}" class="nav-link {{ request()->routeIs('roles.*') && !request()->routeIs('permissions.*') ? 'nav-active-child' : '' }}" data-key="t-starter">
                                Roles
                            </a>
                        </li>
                    @endcan
                    @can('View permission')
                        <li class="nav-item">
                            <a href="{{ route('permissions.index') }}" class="nav-link {{ request()->routeIs('permissions.*') ? 'nav-active-child' : '' }}" data-key="t-profile">
                                Permissions
                            </a>
                        </li>
                    @endcan
                </ul>
            </div>
        </li>
    @endcan

    {{-- User Account --}}
    <li class="nav-item">
        <a class="nav-link menu-link {{ request()->routeIs('user.overview') ? 'nav-active-parent' : 'collapsed' }}"
           href="#sidebaraccount" data-bs-toggle="collapse" role="button"
           aria-expanded="{{ request()->routeIs('user.overview') ? 'true' : 'false' }}"
           aria-controls="sidebaraccount">
            <i class="ph-address-book"></i> <span data-key="t-pages">My Account</span>
            <i class="ri-arrow-down-s-line ms-auto"></i>
        </a>
        <div class="collapse menu-dropdown {{ request()->routeIs('user.overview') ? 'show' : '' }}" id="sidebaraccount">
            <ul class="nav nav-sm flex-column">
                <li class="nav-item">
                    <a href="{{ route('user.overview', auth()->id()) }}" class="nav-link {{ request()->routeIs('user.overview') ? 'nav-active-child' : '' }}" data-key="t-starter">
                        Profile Overview
                    </a>
                </li>
            </ul>
        </div>
    </li>

    {{-- INVENTORY MANAGEMENT --}}
    @if(auth()->user()->can('View banner') || auth()->user()->can('View category') || auth()->user()->can('View brand') ||
        auth()->user()->can('View product') || auth()->user()->can('View pos') || auth()->user()->can('View inventory'))
        <li class="menu-title"><i class="ri-more-fill"></i> <span data-key="t-pages">INVENTORY MANAGEMENT</span></li>
    @endif

    @can('View banner')
        <li class="nav-item">
            <a class="nav-link menu-link {{ request()->routeIs('banners.*') ? 'nav-active-parent' : 'collapsed' }}"
               href="#sidebarbanner" data-bs-toggle="collapse" role="button"
               aria-expanded="{{ request()->routeIs('banners.*') ? 'true' : 'false' }}"
               aria-controls="sidebarbanner">
                <i class="ph-image"></i> <span data-key="t-authentication">Banner Management</span>
                <i class="ri-arrow-down-s-line ms-auto"></i>
            </a>
            <div class="collapse menu-dropdown {{ request()->routeIs('banners.*') ? 'show' : '' }}" id="sidebarbanner">
                <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                        <a href="{{ route('banners.index') }}" class="nav-link {{ request()->routeIs('banners.index') ? 'nav-active-child' : '' }}" data-key="t-signin">
                            Banners
                        </a>
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
                <i class="ph-list"></i> <span data-key="t-authentication">Category Management</span>
                <i class="ri-arrow-down-s-line ms-auto"></i>
            </a>
            <div class="collapse menu-dropdown {{ request()->routeIs('categories.*') ? 'show' : '' }}" id="sidebarcategories">
                <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                        <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.index') ? 'nav-active-child' : '' }}" data-key="t-signin">
                            Categories
                        </a>
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
                <i class="ph-tag"></i> <span data-key="t-authentication">Brand Management</span>
                <i class="ri-arrow-down-s-line ms-auto"></i>
            </a>
            <div class="collapse menu-dropdown {{ request()->routeIs('brands.*') ? 'show' : '' }}" id="sidebarbrand">
                <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                        <a href="{{ route('brands.index') }}" class="nav-link {{ request()->routeIs('brands.index') ? 'nav-active-child' : '' }}" data-key="t-signin">
                            Brands
                        </a>
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
                <i class="ph-package"></i> <span data-key="t-authentication">Product Management</span>
                <i class="ri-arrow-down-s-line ms-auto"></i>
            </a>
            <div class="collapse menu-dropdown {{ request()->routeIs('products.*') ? 'show' : '' }}" id="sidebarproduct">
                <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                        <a href="{{ route('products.index') }}" class="nav-link {{ request()->routeIs('products.index') ? 'nav-active-child' : '' }}" data-key="t-signin">
                            Products
                        </a>
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
                <i class="ph-shopping-cart"></i> <span data-key="t-authentication">POS Management</span>
                <i class="ri-arrow-down-s-line ms-auto"></i>
            </a>
            <div class="collapse menu-dropdown {{ request()->routeIs('pos.*') ? 'show' : '' }}" id="sidebarpos">
                <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                        <a href="{{ route('pos.index') }}" class="nav-link {{ request()->routeIs('pos.index') ? 'nav-active-child' : '' }}" data-key="t-signin">
                            Point of Sale
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('pos.grid') }}" class="nav-link {{ request()->routeIs('pos.grid') ? 'nav-active-child' : '' }}" data-key="t-grid-pos">
                            Grid POS
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    @endcan

    {{-- Inventory Operations --}}
    @can('View inventory')
        <li class="nav-item">
            <a class="nav-link menu-link {{ request()->routeIs('inventory.*') || request()->routeIs('stock-locations.*') ? 'nav-active-parent' : 'collapsed' }}"
               href="#sidebarmanageinventory" data-bs-toggle="collapse" role="button"
               aria-expanded="{{ request()->routeIs('inventory.*') || request()->routeIs('stock-locations.*') ? 'true' : 'false' }}"
               aria-controls="sidebarmanageinventory">
                <i class="ph-warehouse"></i> <span data-key="t-authentication">Inventory Operations</span>
                <i class="ri-arrow-down-s-line ms-auto"></i>
            </a>
            <div class="collapse menu-dropdown {{ request()->routeIs('inventory.*') || request()->routeIs('stock-locations.*') ? 'show' : '' }}" id="sidebarmanageinventory">
                <ul class="nav nav-sm flex-column">
                    @can('View inventory')
                        <li class="nav-item">
                            <a href="{{ route('inventory.index') }}" class="nav-link {{ request()->routeIs('inventory.index') ? 'nav-active-child' : '' }}" data-key="t-signin">
                                Transactions
                            </a>
                        </li>
                    @endcan
                    @can('View stock levels')
                        <li class="nav-item">
                            <a href="{{ route('inventory.stock-levels') }}" class="nav-link {{ request()->routeIs('inventory.stock-levels') ? 'nav-active-child' : '' }}" data-key="t-signin">
                                Stock Levels
                            </a>
                        </li>
                    @endcan
                    @can('Manage stock locations')
                        <li class="nav-item">
                            <a href="{{ route('stock-locations.index') }}" class="nav-link {{ request()->routeIs('stock-locations.*') ? 'nav-active-child' : '' }}" data-key="t-signin">
                                Stock Locations
                            </a>
                        </li>
                    @endcan
                    @can('View low stock alerts')
                        <li class="nav-item">
                            <a href="{{ route('inventory.low-stock-alerts') }}" class="nav-link {{ request()->routeIs('inventory.low-stock-alerts') ? 'nav-active-child' : '' }}" data-key="t-signin">
                                Low Stock Alerts
                            </a>
                        </li>
                    @endcan
                    @can('View inventory reports')
                        <li class="nav-item">
                            <a href="{{ route('inventory.stock-value-report') }}" class="nav-link {{ request()->routeIs('inventory.stock-value-report') ? 'nav-active-child' : '' }}" data-key="t-signin">
                                Stock Value Report
                            </a>
                        </li>
                    @endcan
                </ul>
            </div>
        </li>
    @endcan

    {{-- Orders & Sales --}}
    @can('View order')
        <li class="nav-item">
            <a class="nav-link menu-link {{ request()->routeIs('orders.*') ? 'nav-active-parent' : 'collapsed' }}"
               href="#sidebarorders" data-bs-toggle="collapse" role="button"
               aria-expanded="{{ request()->routeIs('orders.*') ? 'true' : 'false' }}"
               aria-controls="sidebarorders">
                <i class="ph-shopping-cart-simple"></i> <span data-key="t-authentication">Orders Management</span>
                <i class="ri-arrow-down-s-line ms-auto"></i>
            </a>
            <div class="collapse menu-dropdown {{ request()->routeIs('orders.*') ? 'show' : '' }}" id="sidebarorders">
                <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                        <a href="{{ route('orders.index') }}" class="nav-link {{ request()->routeIs('orders.index') ? 'nav-active-child' : '' }}" data-key="t-signin">
                            All Orders
                        </a>
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
                <i class="ph-users"></i> <span data-key="t-authentication">Customers Management</span>
                <i class="ri-arrow-down-s-line ms-auto"></i>
            </a>
            <div class="collapse menu-dropdown {{ request()->routeIs('customers.*') ? 'show' : '' }}" id="sidebarcustomer">
                <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                        <a href="{{ route('customers.index') }}" class="nav-link {{ request()->routeIs('customers.index') ? 'nav-active-child' : '' }}" data-key="t-signin">
                            All Customers
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    @endcan

    {{-- Sales Analytics --}}
    @can('View sale')
        <li class="nav-item">
            <a class="nav-link menu-link {{ request()->routeIs('sales.*') ? 'nav-active-parent' : 'collapsed' }}"
               href="#sidebarsales" data-bs-toggle="collapse" role="button"
               aria-expanded="{{ request()->routeIs('sales.*') ? 'true' : 'false' }}"
               aria-controls="sidebarsales">
                <i class="ph-chart-line"></i> <span data-key="t-authentication">Sales Analytics</span>
                <i class="ri-arrow-down-s-line ms-auto"></i>
            </a>
            <div class="collapse menu-dropdown {{ request()->routeIs('sales.*') ? 'show' : '' }}" id="sidebarsales">
                <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                        <a href="{{ route('sales.index') }}" class="nav-link {{ request()->routeIs('sales.index') ? 'nav-active-child' : '' }}" data-key="t-signin">
                            Sales Reports
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('sales.commissions') }}" class="nav-link {{ request()->routeIs('sales.commissions') ? 'nav-active-child' : '' }}" data-key="t-signin">
                            Commissions
                        </a>
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
                <i class="ph-user-tie"></i> <span data-key="t-authentication">My Sales</span>
                <i class="ri-arrow-down-s-line ms-auto"></i>
            </a>
            <div class="collapse menu-dropdown {{ request()->routeIs('salesperson.*') ? 'show' : '' }}" id="sidebarsalesperson">
                <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                        <a href="{{ route('salesperson.dashboard') }}" class="nav-link {{ request()->routeIs('salesperson.dashboard') ? 'nav-active-child' : '' }}" data-key="t-signin">
                            My Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('salesperson.commissions') }}" class="nav-link {{ request()->routeIs('salesperson.commissions') ? 'nav-active-child' : '' }}" data-key="t-signin">
                            My Commissions
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('salesperson.performance') }}" class="nav-link {{ request()->routeIs('salesperson.performance') ? 'nav-active-child' : '' }}" data-key="t-signin">
                            My Performance
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    @endcan

    {{-- Settings --}}
    <li class="menu-title"><span data-key="t-menu">Settings</span></li>

    @can('View store setting')
        <li class="nav-item">
            <a class="nav-link menu-link {{ request()->routeIs('settings.store.*') ? 'nav-active-parent' : 'collapsed' }}"
               href="#sidebarsetting" data-bs-toggle="collapse" role="button"
               aria-expanded="{{ request()->routeIs('settings.store.*') ? 'true' : 'false' }}"
               aria-controls="sidebarsetting">
                <i class="ph-gear"></i> <span data-key="t-authentication">Store Settings</span>
                <i class="ri-arrow-down-s-line ms-auto"></i>
            </a>
            <div class="collapse menu-dropdown {{ request()->routeIs('settings.store.*') ? 'show' : '' }}" id="sidebarsetting">
                <ul class="nav nav-sm flex-column">
                    <li class="nav-item">
                        <a href="{{ route('settings.store.index') }}" class="nav-link {{ request()->routeIs('settings.store.index') ? 'nav-active-child' : '' }}" data-key="t-signin">
                            Store Configuration
                        </a>
                    </li>
                </ul>
            </div>
        </li>
    @endcan
@endif
