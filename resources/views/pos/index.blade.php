@extends('layouts.master')
@section('title', $pagetitle ?? 'Point of Sale')
@section('content')

{{-- ══════════════════════════════════════════════════════════
     POS PAGE — Supermarket-Grade UI
══════════════════════════════════════════════════════════ --}}

<div class="pos-shell">

    {{-- ── TOP BAR ──────────────────────────────────────────── --}}
    <header class="pos-topbar">
        <div class="pos-topbar__left">
            <div class="pos-logo">
                <i class="bi bi-bag-check-fill"></i>
                <span>QuickSell POS</span>
            </div>
            <div class="pos-topbar__breadcrumb">
                <a href="{{ route('dashboard') }}">Dashboard</a>
                <i class="bi bi-chevron-right"></i>
                <span>Point of Sale</span>
            </div>
        </div>
        <div class="pos-topbar__center">
            <div class="pos-search-wrap">
                <i class="bi bi-upc-scan pos-search-icon"></i>
                <input
                    type="text"
                    id="barcodeInput"
                    class="pos-search-input"
                    placeholder="Scan barcode or search by name / SKU…"
                    autofocus
                    autocomplete="off"
                    aria-label="Search or scan products"
                >
                <div class="pos-search-clear" id="searchClearBtn" style="display:none;">
                    <i class="bi bi-x-circle-fill"></i>
                </div>
                <div class="pos-search-loader" id="searchLoader" style="display:none;">
                    <div class="pos-spinner-sm"></div>
                </div>
            </div>
        </div>
        <div class="pos-topbar__right">
            <div class="pos-status-pill" id="connectionStatus" data-online="true">
                <span class="pos-status-dot"></span>
                <span class="pos-status-label">Online</span>
            </div>
            <label class="pos-offline-toggle" title="Force offline mode">
                <input type="checkbox" id="offlineModeToggle">
                <span class="pos-toggle-track">
                    <span class="pos-toggle-thumb"></span>
                </span>
                <span class="pos-toggle-label">Offline</span>
            </label>
            <div class="pos-topbar__shortcuts">
                <span class="kbd-badge">F1</span> Search &nbsp;
                <span class="kbd-badge">F2</span> Clear &nbsp;
                <span class="kbd-badge">F3</span> Pay &nbsp;
                <span class="kbd-badge">F6</span> View
            </div>
        </div>
    </header>

    {{-- ── MAIN TWO-COLUMN LAYOUT ──────────────────────────── --}}
    <div class="pos-body">

        {{-- ── LEFT: PRODUCTS PANEL ──────────────────────── --}}
        <section class="pos-products-panel">

            {{-- Toolbar row --}}
            <div class="pos-toolbar">
                <div class="pos-view-toggle" role="group" aria-label="Product view mode" id="viewToggleGroup">
                    <input type="radio" class="pos-vt-radio" name="viewMode" id="viewModeGrid" value="grid" checked>
                    <label class="pos-vt-btn" for="viewModeGrid">
                        <i class="bi bi-grid-3x3-gap-fill"></i> Grid
                    </label>
                    <input type="radio" class="pos-vt-radio" name="viewMode" id="viewModeList" value="list">
                    <label class="pos-vt-btn" for="viewModeList">
                        <i class="bi bi-list-ul"></i> List
                    </label>
                </div>

                <div class="pos-cat-scroll" id="categoryTabs">
                    <button class="pos-cat-chip active" data-cat="all">All</button>
                </div>

                <div class="pos-toolbar__actions">
                    <button class="pos-icon-btn" id="refreshProductsBtn" title="Refresh products">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>

            {{-- Loading overlay --}}
            <div class="pos-loading-overlay" id="searchLoading" style="display:none;">
                <div class="pos-loading-inner">
                    <div class="pos-spinner"></div>
                    <p>Searching products…</p>
                </div>
            </div>

            {{-- ── GRID VIEW ─────────────────────────────── --}}
            <div id="gridViewContainer" class="pos-grid-panel">
                <div id="gridViewBody" class="pos-grid"></div>
                <div id="emptyGridRow" class="pos-empty" style="display:none;">
                    <div class="pos-empty__icon"><i class="bi bi-search"></i></div>
                    <h5>No products found</h5>
                    <p>Try a different search term or scan a barcode</p>
                </div>
            </div>

            {{-- ── LIST VIEW ─────────────────────────────── --}}
            <div id="listViewContainer" class="pos-list-panel" style="display:none;">
                <table class="pos-list-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th class="text-center">Stock</th>
                            <th class="text-center">Action</th>
                            <th class="text-end">Price</th>
                            <th class="text-center">Unit</th>
                        </tr>
                    </thead>
                    <tbody id="resultsBody">
                        <tr id="emptySearchRow">
                            <td colspan="5" class="pos-empty pos-empty--table">
                                <div class="pos-empty__icon"><i class="bi bi-search"></i></div>
                                <h5>Ready to sell</h5>
                                <p>Type or scan to find products</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </section>

        {{-- ── RIGHT: ORDER PANEL ─────────────────────────── --}}
        <aside class="pos-order-panel">

            {{-- Customer row --}}
            <div class="pos-customer-row">
                <div class="pos-customer-select-wrap">
                    <i class="bi bi-person-circle pos-customer-icon"></i>
                    <select class="pos-customer-select" id="customerSelect">
                        <option value="">Walk-in Customer</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">
                                {{ $customer->first_name }} {{ $customer->last_name }}
                                @if($customer->phone_number)– {{ $customer->phone_number }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <button class="pos-icon-btn pos-icon-btn--accent" id="quickCustomerBtn" title="Add new customer">
                    <i class="bi bi-person-plus-fill"></i>
                </button>
            </div>
            <div class="pos-customer-search-row">
                <input type="text" id="customerSearchInput" class="pos-input-sm" placeholder="Search customers… (Ctrl+F)">
                <small class="pos-customer-count">
                    <span id="customerCount">{{ count($customers) }}</span> customers
                </small>
            </div>

            {{-- Cart header --}}
            <div class="pos-cart-header">
                <h6><i class="bi bi-cart3"></i> Cart <span class="pos-cart-count" id="cartCount">0</span></h6>
                <div class="pos-cart-actions">
                    <button class="pos-pill-btn pos-pill-btn--warn" id="holdOrderBtn" title="Hold">
                        <i class="bi bi-pause-fill"></i> Hold
                    </button>
                    <button class="pos-pill-btn pos-pill-btn--info" id="loadHeldBtn" title="Load held">
                        <i class="bi bi-folder2-open"></i> Load
                    </button>
                    <button class="pos-pill-btn pos-pill-btn--danger" id="clearCart" title="Clear cart">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </div>

            {{-- Cart items --}}
            <div class="pos-cart-list" id="cartList">
                <div class="pos-empty pos-empty--cart" id="emptyCartRow">
                    <div class="pos-empty__icon pos-empty__icon--cart"><i class="bi bi-cart-x"></i></div>
                    <p>Cart is empty</p>
                    <small>Search or scan items to add</small>
                </div>
            </div>

            {{-- Discount row --}}
            <div class="pos-discount-row">
                <span class="pos-discount-label">Order Discount</span>
                <div class="pos-discount-controls">
                    <input type="number" id="discountValue" class="pos-input-sm pos-input-sm--num"
                           placeholder="0" min="0" step="0.01" value="0">
                    <select id="discountType" class="pos-input-sm pos-input-sm--sel">
                        <option value="fixed">₦</option>
                        <option value="percent" selected>%</option>
                    </select>
                    <button class="pos-apply-disc" id="applyDiscountBtn" title="Apply">
                        <i class="bi bi-check-lg"></i>
                    </button>
                </div>
            </div>

            {{-- Totals --}}
            <div class="pos-totals">
                <div class="pos-totals__row">
                    <span>Subtotal</span>
                    <span id="subtotal">₦0.00</span>
                </div>
                <div class="pos-totals__row pos-totals__row--disc" id="discountRow" style="display:none;">
                    <span><i class="bi bi-tag-fill"></i> Discount</span>
                    <span id="discountAmount" class="pos-totals__disc">-₦0.00</span>
                </div>
                <div class="pos-totals__row">
                    <span>Tax ({{ config('pos.tax_rate', 0) }}%)</span>
                    <span id="taxAmount">₦0.00</span>
                </div>
                <div class="pos-totals__grand">
                    <span>Total</span>
                    <span id="grandTotal">₦0.00</span>
                </div>
            </div>

            {{-- Payment method --}}
            <div class="pos-payment-row">
                <label class="pos-payment-opt">
                    <input type="radio" name="payment" value="cash" checked>
                    <span class="pos-payment-card">
                        <i class="bi bi-cash-stack"></i>
                        <b>Cash</b>
                    </span>
                </label>
                <label class="pos-payment-opt">
                    <input type="radio" name="payment" value="card">
                    <span class="pos-payment-card">
                        <i class="bi bi-credit-card-2-front"></i>
                        <b>Card</b>
                    </span>
                </label>
                <label class="pos-payment-opt">
                    <input type="radio" name="payment" value="transfer">
                    <span class="pos-payment-card">
                        <i class="bi bi-bank2"></i>
                        <b>Transfer</b>
                    </span>
                </label>
            </div>

            {{-- Complete button --}}
            <button class="pos-complete-btn" id="completeOrder">
                <i class="bi bi-printer-fill"></i>
                Complete &amp; Print
            </button>

        </aside>

    </div>{{-- /pos-body --}}
</div>{{-- /pos-shell --}}

{{-- ══════════════════════════════════════════════════════════
     CART NOTIFICATION TOAST (rich slide-in)
══════════════════════════════════════════════════════════ --}}
<div id="cartNotification" class="cart-notif" role="alert" aria-live="polite">
    <div class="cart-notif__thumb" id="cartNotifThumb"></div>
    <div class="cart-notif__body">
        <div class="cart-notif__name" id="cartNotifName"></div>
        <div class="cart-notif__meta" id="cartNotifMeta"></div>
        <div class="cart-notif__price" id="cartNotifPrice"></div>
    </div>
    <div class="cart-notif__close" id="cartNotifClose"><i class="bi bi-x"></i></div>
    <div class="cart-notif__bar"></div>
</div>

{{-- ══════════════════════════════════════════════════════════
     QUANTITY / UNIT MODAL
══════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="quantityModal" tabindex="-1" role="dialog" aria-labelledby="quantityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content pos-modal">
            <div class="pos-modal__header">
                <div class="pos-modal__product-thumb" id="modalThumbWrap">
                    <i class="bi bi-box-seam"></i>
                </div>
                <div class="pos-modal__product-info">
                    <h5 id="modalProductLabel">Product Name</h5>
                    <div class="pos-modal__badges">
                        <span class="pos-badge pos-badge--price" id="modalProductPrice"></span>
                        <span class="pos-badge pos-badge--stock" id="modalProductStock"></span>
                        <span class="pos-badge pos-badge--unit" id="modalProductUnit"></span>
                    </div>
                    <div class="pos-modal__ids">
                        <span id="modalProductSku"></span>
                        <span id="modalProductBarcode"></span>
                    </div>
                </div>
                <button class="pos-modal__close" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="pos-modal__body">
                {{-- Mode toggle --}}
                <div class="pos-mode-toggle">
                    <label class="pos-mode-opt">
                        <input type="radio" name="measurementType" id="measureQuantity" value="quantity" checked>
                        <span><i class="bi bi-123"></i> Quantity</span>
                    </label>
                    <label class="pos-mode-opt">
                        <input type="radio" name="measurementType" id="measureUnit" value="unit">
                        <span><i class="bi bi-scale"></i> By Unit</span>
                    </label>
                </div>

                {{-- Unit selector (unit mode) --}}
                <div id="unitSelection" class="pos-unit-select-wrap" style="display:none;">
                    <label class="pos-field-label">Select Unit <span class="pos-required">*</span></label>
                    <select class="pos-select" id="unitSelect">
                        <option value="">-- Select unit --</option>
                    </select>
                    <label class="pos-check-label">
                        <input type="checkbox" id="rememberUnitPreference">
                        Remember preference for this product
                    </label>
                </div>

                {{-- Numpad display --}}
                <div class="pos-qty-display">
                    <div class="pos-qty-display__prev" id="previousQtyText"></div>
                    <div class="pos-qty-display__label" id="measurementLabel">Enter Quantity</div>

                    <div id="quantityInputSection">
                        <div class="pos-numpad-input-row">
                            <button class="pos-stepper-btn" id="decreaseQty"><i class="bi bi-dash-lg"></i></button>
                            <input type="number" id="modalQty" class="pos-qty-input" min="1" value="1" step="1" autofocus>
                            <button class="pos-stepper-btn" id="increaseQty"><i class="bi bi-plus-lg"></i></button>
                        </div>
                    </div>

                    <div id="unitInputSection" style="display:none;">
                        <div class="pos-numpad-input-row">
                            <button class="pos-stepper-btn" id="decreaseUnit" disabled><i class="bi bi-dash-lg"></i></button>
                            <input type="number" id="modalUnit" class="pos-qty-input" min="0.001" value="1" step="0.001" disabled>
                            <button class="pos-stepper-btn" id="increaseUnit" disabled><i class="bi bi-plus-lg"></i></button>
                        </div>
                    </div>

                    <div class="pos-total-preview" id="totalPriceDisplay">Total: ₦0.00</div>
                </div>

                {{-- Price input (unit mode) --}}
                <div id="priceInputSection" style="display:none;" class="pos-price-input-wrap">
                    <label class="pos-field-label" id="totalPriceLabel">Total Price</label>
                    <div class="pos-price-input-row">
                        <span class="pos-price-symbol">₦</span>
                        <input type="number" id="pricePerUnit" class="pos-price-input" min="0" step="0.01" placeholder="0.00" disabled>
                    </div>
                    <small id="originalPriceText" class="pos-price-hint"></small>
                    <small id="calculatedPriceDisplay" class="pos-calc-price"></small>
                </div>

                {{-- Quick buttons (qty) --}}
                <div id="quantityQuickButtons" class="pos-quick-btns">
                    <button class="pos-qb" data-value="1">1</button>
                    <button class="pos-qb" data-value="2">2</button>
                    <button class="pos-qb" data-value="3">3</button>
                    <button class="pos-qb" data-value="5">5</button>
                    <button class="pos-qb" data-value="10">10</button>
                    <button class="pos-qb" data-value="20">20</button>
                    <button class="pos-qb" data-value="50">50</button>
                    <button class="pos-qb" data-value="100">100</button>
                </div>

                {{-- Quick buttons (unit) --}}
                <div id="unitQuickButtons" class="pos-quick-btns pos-quick-btns--unit" style="display:none;">
                    <button class="pos-qb pos-qb--unit" data-value="0.25">¼</button>
                    <button class="pos-qb pos-qb--unit" data-value="0.5">½</button>
                    <button class="pos-qb pos-qb--unit" data-value="0.75">¾</button>
                    <button class="pos-qb pos-qb--unit" data-value="1">1</button>
                    <button class="pos-qb pos-qb--unit" data-value="2.5">2.5</button>
                    <button class="pos-qb pos-qb--unit" data-value="5">5</button>
                    <button class="pos-qb pos-qb--unit" data-value="10">10</button>
                    <button class="pos-qb pos-qb--unit" data-value="25">25</button>
                </div>
            </div>

            <div class="pos-modal__footer">
                <button class="pos-btn pos-btn--ghost-danger" id="removeFromCartBtn" style="display:none;">
                    <i class="bi bi-trash3"></i> Remove
                </button>
                <button class="pos-btn pos-btn--ghost" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
                <button class="pos-btn pos-btn--primary" id="confirmAddBtn">
                    <i class="bi bi-check-circle-fill"></i> Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Item Discount Modal --}}
<div class="modal fade" id="itemDiscountModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content pos-modal">
            <div class="pos-modal__header pos-modal__header--warn">
                <h5><i class="bi bi-percent me-2"></i> Item Discount</h5>
                <button class="pos-modal__close" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="pos-modal__body">
                <p id="itemName" class="pos-disc-item-name"></p>
                <div class="pos-price-input-row">
                    <input type="number" id="itemDiscountValue" class="pos-price-input" placeholder="0" min="0" step="0.01">
                    <select id="itemDiscountType" class="pos-select" style="max-width:80px;">
                        <option value="percent" selected>%</option>
                        <option value="fixed">₦</option>
                    </select>
                </div>
                <small class="pos-price-hint">Leave 0 to remove discount</small>
            </div>
            <div class="pos-modal__footer">
                <button class="pos-btn pos-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                <button class="pos-btn pos-btn--warn" id="applyItemDiscountBtn">Apply</button>
            </div>
        </div>
    </div>
</div>

{{-- Load Order Modal --}}
<div class="modal fade" id="loadOrderModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content pos-modal">
            <div class="pos-modal__header">
                <h5><i class="bi bi-folder2-open me-2"></i> Held Orders</h5>
                <button class="pos-modal__close" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="pos-modal__body">
                <div id="heldOrdersList"></div>
                <div id="noHeldOrders" class="pos-empty" style="display:none;">
                    <div class="pos-empty__icon"><i class="bi bi-inbox"></i></div>
                    <p>No held orders</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Customer Modal --}}
<div class="modal fade" id="quickCustomerModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content pos-modal">
            <div class="pos-modal__header">
                <h5><i class="bi bi-person-plus-fill me-2"></i> Quick Add Customer</h5>
                <button class="pos-modal__close" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="pos-modal__body">
                <form id="quickCustomerForm">
                    @csrf
                    <div class="pos-form-grid">
                        <div class="pos-field">
                            <label>First Name <span class="pos-required">*</span></label>
                            <input type="text" class="pos-input" id="firstName" required>
                        </div>
                        <div class="pos-field">
                            <label>Last Name <span class="pos-required">*</span></label>
                            <input type="text" class="pos-input" id="lastName" required>
                        </div>
                        <div class="pos-field pos-field--full">
                            <label>Phone Number</label>
                            <input type="tel" class="pos-input" id="phoneNumber">
                        </div>
                        <div class="pos-field pos-field--full">
                            <label>Email</label>
                            <input type="email" class="pos-input" id="email">
                        </div>
                    </div>
                </form>
            </div>
            <div class="pos-modal__footer">
                <button class="pos-btn pos-btn--ghost" data-bs-dismiss="modal">Cancel</button>
                <button class="pos-btn pos-btn--primary" id="saveQuickCustomerBtn">Save Customer</button>
            </div>
        </div>
    </div>
</div>

{{-- Offline Orders Modal --}}
<div class="modal fade" id="offlineOrdersModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content pos-modal">
            <div class="pos-modal__header pos-modal__header--warn">
                <h5><i class="bi bi-wifi-off me-2"></i> Offline Orders</h5>
                <button class="pos-modal__close" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="pos-modal__body">
                <div class="pos-alert pos-alert--info">
                    <i class="bi bi-info-circle-fill me-2"></i>
                    Orders saved while offline — will sync automatically when back online.
                </div>
                <div id="offlineOrdersList"></div>
            </div>
            <div class="pos-modal__footer">
                <button class="pos-btn pos-btn--ghost" data-bs-dismiss="modal">Close</button>
                <button class="pos-btn pos-btn--warn" id="syncOfflineOrdersBtn" style="display:none;">
                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Sync Now
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Accessibility live regions --}}
<div class="visually-hidden" role="status" aria-live="polite" id="cartStatus"></div>
<div class="visually-hidden" role="status" aria-live="polite" id="searchStatus"></div>

<script src="{{ asset('theme/layouts/assets/libs/axios/axios.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

// ══════════════════════════════════════════════
// ELEMENTS
// ══════════════════════════════════════════════
const input             = document.getElementById('barcodeInput');
const resultsBody       = document.getElementById('resultsBody');
const gridViewBody      = document.getElementById('gridViewBody');
const emptyGridRow      = document.getElementById('emptyGridRow');
const listViewContainer = document.getElementById('listViewContainer');
const gridViewContainer = document.getElementById('gridViewContainer');
const cartList          = document.getElementById('cartList');
const emptyCartRow      = document.getElementById('emptyCartRow');
const subtotalEl        = document.getElementById('subtotal');
const discountEl        = document.getElementById('discountAmount');
const grandTotalEl      = document.getElementById('grandTotal');
const searchLoading     = document.getElementById('searchLoading');
const modalQty          = document.getElementById('modalQty');
const modalUnit         = document.getElementById('modalUnit');
const confirmAddBtn     = document.getElementById('confirmAddBtn');
const removeFromCartBtn = document.getElementById('removeFromCartBtn');
const customerSelect    = document.getElementById('customerSelect');
const discountValue     = document.getElementById('discountValue');
const discountType      = document.getElementById('discountType');
const applyDiscountBtn  = document.getElementById('applyDiscountBtn');
const pricePerUnitInput = document.getElementById('pricePerUnit');
const unitSelect        = document.getElementById('unitSelect');
const searchClearBtn    = document.getElementById('searchClearBtn');
const searchLoader      = document.getElementById('searchLoader');
const cartCountEl       = document.getElementById('cartCount');

// Bootstrap tooltips & modals
const tooltipEls = document.querySelectorAll('[data-bs-toggle="tooltip"]');
[...tooltipEls].map(el => new bootstrap.Tooltip(el));

// ══════════════════════════════════════════════
// STATE
// ══════════════════════════════════════════════
let cart                    = [];
let allSearchedProducts     = [];
let featuredProducts        = [];   // pre-loaded on page open
let currentSearchQuery      = '';
let productQuantityCache    = {};
let currentProduct          = null;
let currentItemIndex        = null;
let orderDiscountType       = 'percent';
let orderDiscountValue      = 0;
let printWindow             = null;
let printCheckInterval      = null;
let quantityModal           = null;
let quickCustomerModal      = null;
let isProcessingOrder       = false;
let thankYouAudio           = null;
let currentViewMode         = localStorage.getItem('posViewMode') || 'grid';
let activeCategoryFilter    = 'all';
let availableUnits          = [];
let selectedUnit            = null;
let currentMeasurementType  = 'quantity';
let originalPricePerUnit    = 0;
let isPriceInputActive      = false;
let cartNotifTimeout        = null;

// ══════════════════════════════════════════════
// INIT
// ══════════════════════════════════════════════
function initializeApp() {
    quantityModal      = new bootstrap.Modal(document.getElementById('quantityModal'));
    quickCustomerModal = new bootstrap.Modal(document.getElementById('quickCustomerModal'));

    thankYouAudio         = new Audio('/audio/thank-you-sweet-man-235977.mp3');
    thankYouAudio.preload = 'auto';

    setupQuantityModal();
    initializeCustomerSearch();
    initializeQuickCustomerModal();
    loadUnitPreferences();
    applyViewMode(currentViewMode);
    loadFeaturedProducts();

    // Re-focus search on idle click
    document.addEventListener('click', function (e) {
        if (!e.target.closest('.modal')
            && !e.target.closest('#customerSelect')
            && !e.target.closest('#customerSearchInput')
            && !e.target.closest('#discountValue')
            && !e.target.closest('.pos-grid-card')
            && !e.target.closest('#viewToggleGroup')
            && !e.target.closest('#categoryTabs')
            && e.target.id !== 'barcodeInput') {
            input.focus();
        }
    });
}

// ══════════════════════════════════════════════
// FEATURED / PRE-LOADED PRODUCTS
// ══════════════════════════════════════════════
async function loadFeaturedProducts() {
    showSearchLoading();
    try {
        // Load recent/popular products on page load
        const res = await axios.get('{{ route("pos.search") }}', { params: { q: 'a', limit: 30 } });
        featuredProducts = res.data || [];
        // Merge into allSearchedProducts without duplication
        featuredProducts.forEach(p => {
            if (!allSearchedProducts.find(sp => sp.id === p.id)) {
                allSearchedProducts.push(p);
            }
        });
        renderAll();
    } catch(e) {
        // Silent fail — grid stays empty until search
    } finally {
        hideSearchLoading();
    }
}

// ══════════════════════════════════════════════
// BARCODE INPUT
// ══════════════════════════════════════════════
input.addEventListener('keydown', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        const code = input.value.trim();
        if (code) processBarcode(code);
    }
});

input.addEventListener('input', debounce(() => {
    const q = input.value.trim();
    currentSearchQuery = q;
    searchClearBtn.style.display = q.length > 0 ? 'flex' : 'none';

    if (q.length >= 2 && !isLikelyBarcode(q)) {
        searchProducts(q);
    } else if (q.length === 0) {
        clearAllUnselectedItems();
        // Restore featured
        featuredProducts.forEach(p => {
            if (!allSearchedProducts.find(sp => sp.id === p.id)) allSearchedProducts.push(p);
        });
        renderAll();
    } else {
        if (allSearchedProducts.length > 0) renderAll();
    }
}, 280));

searchClearBtn.addEventListener('click', () => {
    input.value = '';
    searchClearBtn.style.display = 'none';
    clearAllUnselectedItems();
    featuredProducts.forEach(p => {
        if (!allSearchedProducts.find(sp => sp.id === p.id)) allSearchedProducts.push(p);
    });
    renderAll();
    input.focus();
});

document.getElementById('refreshProductsBtn').addEventListener('click', () => {
    allSearchedProducts = [];
    loadFeaturedProducts();
});

async function processBarcode(barcode) {
    if (!barcode) return;
    input.value = '';
    searchClearBtn.style.display = 'none';
    showSearchLoading();
    try {
        const res = await axios.get('{{ route("pos.search") }}', { params: { q: barcode } });
        const products = res.data || [];
        if (products.length > 0) {
            const product = products[0];
            const idx = allSearchedProducts.findIndex(p => p.id === product.id);
            if (idx === -1) allSearchedProducts.unshift(product);
            else allSearchedProducts[idx] = product;
            const cartItem = cart.find(i => i.product_id === product.id);
            if (cartItem) {
                const btn = document.createElement('button');
                btn.dataset.product   = JSON.stringify(product).replace(/'/g, "&apos;");
                btn.dataset.productId = product.id;
                openQuantityModal(btn);
            } else {
                const btn = document.createElement('button');
                btn.dataset.product   = JSON.stringify(product).replace(/'/g, "&apos;");
                btn.dataset.productId = product.id;
                openQuantityModal(btn);
            }
            renderAll();
            playScanSound();
        } else {
            showToast('Product not found', 'error');
        }
    } catch (e) {
        showToast('Error scanning barcode', 'error');
    } finally {
        hideSearchLoading();
    }
}

function isLikelyBarcode(str) {
    if (str.length >= 8 && str.length <= 14 && /^\d+$/.test(str)) return true;
    if (str.startsWith('PROD') && str.length > 10) return true;
    return false;
}

function playScanSound() {
    try {
        const ctx  = new (window.AudioContext || window.webkitAudioContext)();
        const osc  = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain); gain.connect(ctx.destination);
        osc.frequency.value = 880; osc.type = 'sine';
        gain.gain.setValueAtTime(0.3, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.12);
        osc.start(ctx.currentTime); osc.stop(ctx.currentTime + 0.12);
    } catch (e) {}
}

// ══════════════════════════════════════════════
// FORMATTING
// ══════════════════════════════════════════════
function fmt(n, dec = 0) {
    n = parseFloat(n) || 0;
    return new Intl.NumberFormat('en-NG', { minimumFractionDigits: dec, maximumFractionDigits: dec }).format(n);
}
function fmtCur(n) { return '₦' + fmt(n, 2); }
function fmtQty(q, isUnit = false) { q = parseFloat(q) || 0; return isUnit ? fmt(q, 2) : fmt(q, 0); }

// ══════════════════════════════════════════════
// VIEW MODE
// ══════════════════════════════════════════════
function applyViewMode(mode) {
    currentViewMode = mode;
    localStorage.setItem('posViewMode', mode);
    if (mode === 'grid') {
        gridViewContainer.style.display = '';
        listViewContainer.style.display = 'none';
        document.getElementById('viewModeGrid').checked = true;
    } else {
        gridViewContainer.style.display = 'none';
        listViewContainer.style.display = '';
        document.getElementById('viewModeList').checked = true;
    }
    buildCategoryTabs();
    renderAll();
}

document.getElementById('viewModeGrid').addEventListener('change', function () { if (this.checked) applyViewMode('grid'); });
document.getElementById('viewModeList').addEventListener('change', function () { if (this.checked) applyViewMode('list'); });

// ══════════════════════════════════════════════
// CATEGORY TABS
// ══════════════════════════════════════════════
function buildCategoryTabs() {
    const tabs = document.getElementById('categoryTabs');
    const cats = ['all', ...new Set(allSearchedProducts.map(p => p.category || '').filter(Boolean))];
    tabs.innerHTML = '';
    cats.forEach(cat => {
        const btn = document.createElement('button');
        btn.className = 'pos-cat-chip' + (cat === activeCategoryFilter ? ' active' : '');
        btn.dataset.cat  = cat;
        btn.textContent  = cat === 'all' ? 'All' : cat.charAt(0).toUpperCase() + cat.slice(1);
        btn.addEventListener('click', function () {
            activeCategoryFilter = this.dataset.cat;
            buildCategoryTabs();
            renderAll();
        });
        tabs.appendChild(btn);
    });
}

// ══════════════════════════════════════════════
// RENDER DISPATCHER
// ══════════════════════════════════════════════
function renderAll() {
    buildCategoryTabs();
    if (currentViewMode === 'grid') renderGridView();
    else renderListView();
}

// ══════════════════════════════════════════════
// GRID VIEW
// ══════════════════════════════════════════════
function renderGridView() {
    const filtered = allSearchedProducts.filter(p =>
        activeCategoryFilter === 'all' || (p.category || '') === activeCategoryFilter
    );

    gridViewBody.innerHTML = '';

    if (allSearchedProducts.length === 0) {
        emptyGridRow.style.display = 'flex';
        return;
    }
    emptyGridRow.style.display = 'none';

    if (filtered.length === 0) {
        gridViewBody.innerHTML = `<div class="pos-no-cat"><i class="bi bi-filter-circle"></i><p>No products in this category</p></div>`;
        return;
    }

    filtered.forEach(product => {
        const price    = parseFloat(product.sale_price || product.price) || 0;
        const cartItem = cart.find(i => i.product_id === product.id);
        const addedQty = cartItem ? cartItem.qty : 0;
        const isOut    = product.stock <= 0;
        const saved    = getSavedUnitPreference(product.id);
        const stockLvl = product.stock > 10 ? 'high' : product.stock > 0 ? 'low' : 'out';

        const card = document.createElement('div');
        card.className = 'pos-grid-card'
            + (isOut ? ' is-out' : '')
            + (addedQty > 0 ? ' in-cart' : '');
        card.dataset.productId = product.id;
        card.setAttribute('role', 'button');
        card.setAttribute('tabindex', isOut ? '-1' : '0');

        const thumbHTML = product.thumbnail
            ? `<img src="${product.thumbnail}" alt="${product.title}" loading="lazy">`
            : `<i class="bi bi-box-seam"></i>`;

        card.innerHTML = `
            ${addedQty > 0 ? `
            <div class="pgc-in-cart-badge">
                <i class="bi bi-check2"></i> ${fmtQty(addedQty, cartItem?.is_unit_mode)}
            </div>` : ''}
            <div class="pgc-stock pgc-stock--${stockLvl}">
                ${isOut ? 'Out' : fmt(product.stock)}
            </div>
            <div class="pgc-thumb">${thumbHTML}</div>
            <div class="pgc-info">
                <div class="pgc-name" title="${product.title}">${product.title}</div>
                <div class="pgc-price">${fmtCur(price)}</div>
                <div class="pgc-sku">${product.sku}</div>
                ${saved ? `<div class="pgc-pref"><i class="bi bi-star-fill"></i> ${saved.shortName}</div>` : ''}
            </div>
            ${!isOut ? `<div class="pgc-add-overlay"><i class="bi bi-cart-plus"></i> Add</div>` : `<div class="pgc-out-label">Out of Stock</div>`}
        `;

        if (!isOut) {
            const open = () => {
                const btn = document.createElement('button');
                btn.dataset.product   = JSON.stringify(product).replace(/'/g, "&apos;");
                btn.dataset.productId = product.id;
                openQuantityModal(btn);
            };
            card.addEventListener('click', open);
            card.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); open(); } });
        }

        card.addEventListener('contextmenu', e => { e.preventDefault(); removeProductFromSearchAndCart(product.id); });

        gridViewBody.appendChild(card);
    });
}

// ══════════════════════════════════════════════
// LIST VIEW
// ══════════════════════════════════════════════
function renderListView() {
    if (allSearchedProducts.length === 0) {
        resultsBody.innerHTML = `<tr><td colspan="5" class="pos-empty pos-empty--table">
            <div class="pos-empty__icon"><i class="bi bi-search"></i></div>
            <h5>Ready to sell</h5><p>Type or scan to find products</p></td></tr>`;
        return;
    }
    resultsBody.innerHTML = '';
    [...allSearchedProducts].reverse().forEach(product => renderProductRow(product));
}

function renderProductRow(product) {
    const price     = parseFloat(product.sale_price || product.price) || 0;
    const unit      = product.primary_unit || 'Unit';
    const cartItem  = cart.find(i => i.product_id === product.id);
    const addedQty  = cartItem ? cartItem.qty : 0;
    const isOut     = product.stock <= 0;
    const saved     = getSavedUnitPreference(product.id);
    const btnClass  = addedQty > 0 ? 'pos-btn pos-btn--cart-added' : (isOut ? 'pos-btn pos-btn--disabled' : 'pos-btn pos-btn--primary-sm');
    const btnText   = addedQty > 0 ? `<i class="bi bi-check2"></i> ${fmtQty(addedQty, cartItem?.is_unit_mode)}` : (isOut ? 'Out of Stock' : `Add`);

    const row = document.createElement('tr');
    row.className = addedQty > 0 ? 'list-row-incart' : '';
    row.innerHTML = `
        <td>
            <div class="pos-list-product">
                ${product.thumbnail
                    ? `<img src="${product.thumbnail}" class="pos-list-thumb" alt="">`
                    : `<div class="pos-list-thumb pos-list-thumb--placeholder"><i class="bi bi-image"></i></div>`}
                <div>
                    <strong>${product.title}</strong>
                    <div class="pos-list-meta">
                        <span class="pos-meta-chip"><i class="bi bi-upc-scan"></i> ${product.sku}</span>
                        <span class="pos-meta-chip pos-meta-chip--blue"><i class="bi bi-barcode"></i> ${product.barcode}</span>
                        ${saved ? `<span class="pos-meta-chip pos-meta-chip--gold"><i class="bi bi-star-fill"></i> ${saved.shortName}</span>` : ''}
                    </div>
                </div>
            </div>
        </td>
        <td class="text-center">
            <span class="pgc-stock pgc-stock--${product.stock > 10 ? 'high' : product.stock > 0 ? 'low' : 'out'} pgc-stock--inline">
                ${fmt(product.stock)}
            </span>
        </td>
        <td class="text-center">
            <button class="${btnClass} qty-btn" data-product='${JSON.stringify(product).replace(/'/g,"&apos;")}' data-product-id="${product.id}" ${isOut ? 'disabled' : ''}>
                ${btnText}
            </button>
            <button class="pos-btn pos-btn--ghost-sm remove-btn ms-1" data-product-id="${product.id}" title="Remove">
                <i class="bi bi-x-circle"></i>
            </button>
        </td>
        <td class="text-end fw-bold">${fmtCur(price)}</td>
        <td class="text-center">
            <span class="pos-unit-badge">${unit}</span>
        </td>
    `;

    if (!isOut) {
        row.querySelector('.qty-btn').addEventListener('click', function () { if (!this.disabled) openQuantityModal(this); });
    }
    row.querySelector('.remove-btn').addEventListener('click', function () {
        removeProductFromSearchAndCart(this.dataset.productId);
    });

    resultsBody.appendChild(row);
}

// ══════════════════════════════════════════════
// CART NOTIFICATION (RICH SLIDE-IN)
// ══════════════════════════════════════════════
function showCartNotification(item, action = 'add') {
    const notif     = document.getElementById('cartNotification');
    const thumbEl   = document.getElementById('cartNotifThumb');
    const nameEl    = document.getElementById('cartNotifName');
    const metaEl    = document.getElementById('cartNotifMeta');
    const priceEl   = document.getElementById('cartNotifPrice');

    // Thumb
    if (item.thumbnail) {
        thumbEl.innerHTML = `<img src="${item.thumbnail}" alt="">`;
        thumbEl.className = 'cart-notif__thumb cart-notif__thumb--img';
    } else {
        thumbEl.innerHTML = `<i class="bi bi-bag-fill"></i>`;
        thumbEl.className = 'cart-notif__thumb';
    }

    nameEl.textContent = item.title;
    metaEl.innerHTML   = `
        <span class="cn-qty">${fmtQty(item.qty, item.is_unit_mode)} ${item.unit_short_name || item.unit_name || 'unit'}</span>
        <span class="cn-sep">·</span>
        <span class="cn-sku">${item.sku}</span>
        ${item.is_unit_mode ? `<span class="cn-sep">·</span><i class="bi bi-scale cn-unit-icon"></i>` : ''}
    `;

    const total = item.qty * (item.unit_price || item.price_per_unit || 0);
    priceEl.textContent = fmtCur(total);
    notif.dataset.action = action;

    // Trigger animation
    notif.classList.remove('is-visible', 'is-removing');
    void notif.offsetWidth; // reflow
    notif.classList.add('is-visible');

    if (cartNotifTimeout) clearTimeout(cartNotifTimeout);
    cartNotifTimeout = setTimeout(() => dismissCartNotif(), 3200);
}

function dismissCartNotif() {
    const notif = document.getElementById('cartNotification');
    notif.classList.add('is-removing');
    notif.addEventListener('animationend', () => notif.classList.remove('is-visible', 'is-removing'), { once: true });
}

document.getElementById('cartNotifClose').addEventListener('click', dismissCartNotif);

// ══════════════════════════════════════════════
// SEARCH
// ══════════════════════════════════════════════
function showSearchLoading() {
    searchLoading.style.display = 'flex';
    searchLoader.style.display  = 'flex';
}
function hideSearchLoading() {
    searchLoading.style.display = 'none';
    searchLoader.style.display  = 'none';
}

function searchProducts(query) {
    if (!query) return;
    showSearchLoading();
    axios.get('{{ route("pos.search") }}', { params: { q: query } })
        .then(res => {
            const newProducts = res.data || [];
            newProducts.forEach(np => {
                const idx = allSearchedProducts.findIndex(p => p.id === np.id);
                if (idx === -1) allSearchedProducts.unshift(np);
                else allSearchedProducts[idx] = np;
            });
            renderAll();
        })
        .catch(() => showToast('Failed to search products', 'error'))
        .finally(() => hideSearchLoading());
}

function clearAllUnselectedItems() {
    allSearchedProducts = allSearchedProducts.filter(p => cart.some(i => i.product_id === p.id));
}

function removeProductFromSearchAndCart(productId) {
    allSearchedProducts = allSearchedProducts.filter(p => p.id != productId);
    featuredProducts    = featuredProducts.filter(p => p.id != productId);
    if (cart.some(i => i.product_id == productId)) {
        cart = cart.filter(i => i.product_id != productId);
        delete productQuantityCache[productId];
        updateCart();
    }
    renderAll();
    showToast('Product removed', 'success');
}

// ══════════════════════════════════════════════
// QUANTITY MODAL
// ══════════════════════════════════════════════
function openQuantityModal(button) {
    try {
        const product   = JSON.parse(button.dataset.product);
        const productId = button.dataset.productId;
        currentProduct  = product;

        // Bubble to top
        const idx = allSearchedProducts.findIndex(p => p.id == productId);
        if (idx !== -1) { const [m] = allSearchedProducts.splice(idx, 1); allSearchedProducts.unshift(m); }

        const cartItem    = cart.find(i => i.product_id === productId);
        const cachedQty   = productQuantityCache[productId] || 1;
        const previousQty = cartItem ? cartItem.qty : cachedQty;

        // Set modal product info
        document.getElementById('modalProductLabel').textContent = product.title;
        const price = parseFloat(product.sale_price || product.price) || 0;
        document.getElementById('modalProductPrice').textContent = fmtCur(price);
        document.getElementById('modalProductStock').textContent = `Stock: ${fmt(product.stock)}`;
        document.getElementById('modalProductUnit').textContent  = product.primary_unit || 'Unit';
        document.getElementById('modalProductSku').innerHTML     =
            `<span class="pos-meta-chip"><i class="bi bi-upc-scan"></i> ${product.sku}</span>`;
        document.getElementById('modalProductBarcode').innerHTML =
            `<span class="pos-meta-chip pos-meta-chip--blue"><i class="bi bi-barcode"></i> ${product.barcode}</span>`;

        // Thumb
        const thumbWrap = document.getElementById('modalThumbWrap');
        thumbWrap.innerHTML = product.thumbnail
            ? `<img src="${product.thumbnail}" alt="">`
            : `<i class="bi bi-box-seam"></i>`;

        // Unit mode availability
        const hasUnits = product.units && product.units.length > 0;
        const unitRadio = document.getElementById('measureUnit');
        unitRadio.disabled = !hasUnits;

        const savedPref = getSavedUnitPreference(productId);
        if (savedPref && hasUnits) {
            unitRadio.checked = true;
            switchToUnitMode();
        } else {
            document.getElementById('measureQuantity').checked = true;
            switchToQuantityMode();
        }

        if (currentMeasurementType === 'quantity') {
            modalQty.value          = previousQty;
            pricePerUnitInput.value = (price * parseInt(modalQty.value || 1)).toFixed(2);
        } else {
            modalUnit.value         = previousQty;
            pricePerUnitInput.value = (price * parseFloat(modalUnit.value || 1)).toFixed(2);
        }

        const prevText = document.getElementById('previousQtyText');
        prevText.textContent = cartItem
            ? `In cart: ${fmtQty(previousQty, cartItem.is_unit_mode)} ${cartItem.unit_short_name || ''}`
            : '';
        prevText.className = cartItem ? 'pos-prev-qty pos-prev-qty--incart' : 'pos-prev-qty';

        removeFromCartBtn.style.display = cartItem ? 'inline-flex' : 'none';
        confirmAddBtn.textContent = cartItem ? ' Update Cart' : ' Add to Cart';
        confirmAddBtn.innerHTML   = cartItem
            ? '<i class="bi bi-cart-check-fill"></i> Update Cart'
            : '<i class="bi bi-cart-plus-fill"></i> Add to Cart';

        originalPricePerUnit = price;
        isPriceInputActive   = false;
        updateModalTotal();
        updateAmountDisplay();
        quantityModal.show();
    } catch (e) {
        console.error(e);
        showToast('Error loading product', 'error');
    }
}

function setupQuantityModal() {
    const qModal = document.getElementById('quantityModal');

    qModal.addEventListener('shown.bs.modal', () => {
        setTimeout(() => {
            if (currentMeasurementType === 'quantity') { modalQty.focus(); modalQty.select(); }
            else if (selectedUnit) { modalUnit.focus(); modalUnit.select(); }
            else unitSelect.focus();
        }, 80);
    });

    qModal.addEventListener('hidden.bs.modal', () => {
        setTimeout(() => { input.focus(); }, 80);
    });

    document.getElementById('measureQuantity').addEventListener('change', function () { if (this.checked) { switchToQuantityMode(); updateModalTotal(); } });
    document.getElementById('measureUnit').addEventListener('change', function () { if (this.checked) { switchToUnitMode(); updateModalTotal(); } });

    unitSelect.addEventListener('change', function () {
        selectedUnit = availableUnits.find(u => u.id == this.value) || null;
        if (selectedUnit) {
            modalUnit.disabled = false;
            document.getElementById('decreaseUnit').disabled = false;
            document.getElementById('increaseUnit').disabled = false;
            pricePerUnitInput.disabled = false;
            updatePriceUnitLabel(); updateModalLabels(); updateModalTotal(); updateAmountDisplay();
            checkSavedUnitPreference();
        } else {
            modalUnit.disabled = pricePerUnitInput.disabled = true;
            document.getElementById('decreaseUnit').disabled = document.getElementById('increaseUnit').disabled = true;
            updateModalLabels(); updateModalTotal(); updateAmountDisplay();
        }
    });

    modalQty.addEventListener('input', function () {
        const q = parseInt(this.value) || 1;
        if (!isPriceInputActive) pricePerUnitInput.value = (q * originalPricePerUnit).toFixed(2);
        updateModalTotal(); updateAmountDisplay();
    });

    modalUnit.addEventListener('input', function () {
        if (!selectedUnit) return;
        const a = parseFloat(this.value) || 1;
        if (!isPriceInputActive) pricePerUnitInput.value = (a * originalPricePerUnit).toFixed(2);
        updateModalTotal(); updateAmountDisplay();
    });

    pricePerUnitInput.addEventListener('focus', () => isPriceInputActive = true);
    pricePerUnitInput.addEventListener('blur', () => {
        isPriceInputActive = false;
        if ((parseFloat(pricePerUnitInput.value) || 0) === 0 && currentProduct) {
            const price = parseFloat(currentProduct.sale_price || currentProduct.price) || 0;
            pricePerUnitInput.value = price.toFixed(2);
            if (currentMeasurementType === 'quantity') modalQty.value = 1;
            else if (selectedUnit) modalUnit.value = 1;
            updateModalTotal(); updateAmountDisplay();
        }
    });

    pricePerUnitInput.addEventListener('input', function () {
        if (!selectedUnit && currentMeasurementType !== 'quantity') return;
        const total = parseFloat(this.value) || 0;
        if (originalPricePerUnit > 0 && total > 0) {
            const eq = total / originalPricePerUnit;
            if (currentMeasurementType === 'quantity') modalQty.value = Math.round(eq);
            else modalUnit.value = eq.toFixed(3);
            updateModalTotal(); updateAmountDisplay();
        }
    });

    document.getElementById('increaseQty').addEventListener('click', () => {
        modalQty.value = (parseInt(modalQty.value) || 1) + 1;
        pricePerUnitInput.value = (parseInt(modalQty.value) * originalPricePerUnit).toFixed(2);
        updateModalTotal(); updateAmountDisplay(); modalQty.focus(); modalQty.select();
    });
    document.getElementById('decreaseQty').addEventListener('click', () => {
        const v = parseInt(modalQty.value) || 1;
        if (v > 1) { modalQty.value = v - 1; pricePerUnitInput.value = (parseInt(modalQty.value) * originalPricePerUnit).toFixed(2); }
        updateModalTotal(); updateAmountDisplay(); modalQty.focus(); modalQty.select();
    });
    document.getElementById('increaseUnit').addEventListener('click', () => {
        if (!selectedUnit) return;
        modalUnit.value = (parseFloat(modalUnit.value) + getUnitStep()).toFixed(3);
        pricePerUnitInput.value = (parseFloat(modalUnit.value) * originalPricePerUnit).toFixed(2);
        updateModalTotal(); updateAmountDisplay(); modalUnit.focus(); modalUnit.select();
    });
    document.getElementById('decreaseUnit').addEventListener('click', () => {
        if (!selectedUnit) return;
        const c = parseFloat(modalUnit.value) || 1;
        if (c > getUnitStep()) { modalUnit.value = (c - getUnitStep()).toFixed(3); pricePerUnitInput.value = (parseFloat(modalUnit.value) * originalPricePerUnit).toFixed(2); }
        updateModalTotal(); updateAmountDisplay(); modalUnit.focus(); modalUnit.select();
    });

    document.querySelectorAll('.pos-qb:not(.pos-qb--unit)').forEach(btn => {
        btn.addEventListener('click', function () {
            if (currentMeasurementType === 'quantity') {
                modalQty.value = this.dataset.value;
                pricePerUnitInput.value = (parseInt(modalQty.value) * originalPricePerUnit).toFixed(2);
                modalQty.focus(); modalQty.select();
            } else if (selectedUnit) {
                modalUnit.value = this.dataset.value;
                pricePerUnitInput.value = (parseFloat(modalUnit.value) * originalPricePerUnit).toFixed(2);
                modalUnit.focus(); modalUnit.select();
            } else { showToast('Select a unit first', 'warning'); }
            updateModalTotal(); updateAmountDisplay();
        });
    });

    document.querySelectorAll('.pos-qb--unit').forEach(btn => {
        btn.addEventListener('click', function () {
            if (!selectedUnit) { showToast('Select a unit first', 'warning'); return; }
            modalUnit.value = this.dataset.value;
            pricePerUnitInput.value = (parseFloat(modalUnit.value) * originalPricePerUnit).toFixed(2);
            updateModalTotal(); updateAmountDisplay(); modalUnit.focus(); modalUnit.select();
        });
    });

    document.getElementById('rememberUnitPreference').addEventListener('change', function () {
        if (this.checked && currentProduct && selectedUnit) saveUnitPreference();
    });

    modalQty.addEventListener('keydown',          e => { if (e.key === 'Enter') { e.preventDefault(); confirmAddBtn.click(); } });
    modalUnit.addEventListener('keydown',         e => { if (e.key === 'Enter') { e.preventDefault(); confirmAddBtn.click(); } });
    pricePerUnitInput.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); confirmAddBtn.click(); } });
}

function updateModalLabels() {
    document.getElementById('totalPriceLabel').textContent =
        (currentMeasurementType === 'unit' && selectedUnit) ? `Total Price (per ${selectedUnit.short_name})` : 'Total Price';
}
function updatePriceUnitLabel() {
    document.getElementById('unitDisplay') && (document.getElementById('unitDisplay').textContent = selectedUnit ? selectedUnit.short_name : 'unit');
    updateModalLabels();
}
function updateAmountDisplay() {
    const ad = document.getElementById('amountDisplay');
    const ud = document.getElementById('unitDisplay');
    if (ad) ad.textContent = currentMeasurementType === 'quantity' ? fmtQty(parseInt(modalQty.value) || 1) : fmt(parseFloat(modalUnit.value) || 1, 2);
    if (ud) ud.textContent = currentMeasurementType === 'quantity' ? 'unit(s)' : (selectedUnit ? selectedUnit.short_name : 'unit');
}

function switchToQuantityMode() {
    currentMeasurementType = 'quantity';
    document.getElementById('measurementLabel').textContent       = 'Enter Quantity';
    document.getElementById('quantityInputSection').style.display = '';
    document.getElementById('unitInputSection').style.display     = 'none';
    document.getElementById('unitSelection').style.display        = 'none';
    document.getElementById('unitQuickButtons').style.display     = 'none';
    document.getElementById('priceInputSection').style.display    = 'none';
    document.getElementById('quantityQuickButtons').style.display = '';
    modalQty.disabled = document.getElementById('decreaseQty').disabled = document.getElementById('increaseQty').disabled = false;
    if (currentProduct) {
        const price = parseFloat(currentProduct.sale_price || currentProduct.price) || 0;
        pricePerUnitInput.value = (price * (parseInt(modalQty.value) || 1)).toFixed(2);
        originalPricePerUnit    = price;
        document.getElementById('originalPriceText').textContent = `Per unit: ${fmtCur(price)}`;
    }
    updateModalLabels(); updateModalTotal(); updateAmountDisplay();
}

function switchToUnitMode() {
    currentMeasurementType = 'unit';
    document.getElementById('measurementLabel').textContent       = 'Enter Amount';
    document.getElementById('quantityInputSection').style.display = 'none';
    document.getElementById('unitInputSection').style.display     = '';
    document.getElementById('unitSelection').style.display        = '';
    document.getElementById('unitQuickButtons').style.display     = '';
    document.getElementById('priceInputSection').style.display    = '';
    document.getElementById('quantityQuickButtons').style.display = 'none';
    modalUnit.disabled = pricePerUnitInput.disabled = true;
    document.getElementById('decreaseUnit').disabled = document.getElementById('increaseUnit').disabled = true;
    loadProductUnits();
    if (currentProduct) {
        const price = parseFloat(currentProduct.sale_price || currentProduct.price) || 0;
        pricePerUnitInput.value = (price * (parseFloat(modalUnit.value) || 1)).toFixed(2);
        originalPricePerUnit    = price;
        document.getElementById('originalPriceText').textContent =
            selectedUnit ? `Per ${selectedUnit.short_name}: ${fmtCur(price)}` : `Per unit: ${fmtCur(price)}`;
    }
    updateModalLabels(); updateModalTotal(); updateAmountDisplay();
}

function getUnitStep() {
    if (!selectedUnit) return 0.001;
    const sn = selectedUnit.short_name.toLowerCase();
    if (sn.includes('kg') || sn.includes('l')) return 0.001;
    if (sn.includes('g')) return 1;
    return 0.001;
}

async function loadProductUnits() {
    if (!currentProduct) return;
    try {
        const res = await axios.get(
            `{{ route('api.product.units', ['product' => '__ID__']) }}`.replace('__ID__', currentProduct.id)
        );
        availableUnits = res.data.units || [];
        unitSelect.innerHTML = '<option value="">-- Select unit --</option>';
        if (availableUnits.length > 0) {
            const saved = getSavedUnitPreference(currentProduct.id);
            availableUnits.forEach(unit => {
                const opt = document.createElement('option');
                opt.value = unit.id;
                opt.textContent = `${unit.name} (${unit.short_name})`;
                if (saved && saved.unitId == unit.id) { opt.selected = true; selectedUnit = unit; }
                else if (unit.is_default && !selectedUnit) { opt.selected = true; selectedUnit = unit; }
                unitSelect.appendChild(opt);
            });
            if (selectedUnit) {
                modalUnit.disabled = pricePerUnitInput.disabled =
                document.getElementById('decreaseUnit').disabled = document.getElementById('increaseUnit').disabled = false;
            }
            updatePriceUnitLabel(); updateAmountDisplay();
            if (saved) document.getElementById('rememberUnitPreference').checked = true;
        } else {
            selectedUnit = { id: 1, name: currentProduct.primary_unit || 'Unit', short_name: currentProduct.primary_unit || 'unit', conversion_factor: 1, is_default: true };
            const opt = document.createElement('option');
            opt.value = selectedUnit.id; opt.textContent = `${selectedUnit.name} (${selectedUnit.short_name})`; opt.selected = true;
            unitSelect.appendChild(opt);
            modalUnit.disabled = pricePerUnitInput.disabled =
            document.getElementById('decreaseUnit').disabled = document.getElementById('increaseUnit').disabled = false;
            updatePriceUnitLabel();
        }
    } catch (e) {
        selectedUnit = { id: 1, name: currentProduct.primary_unit || 'Unit', short_name: currentProduct.primary_unit || 'unit', conversion_factor: 1, is_default: true };
        updatePriceUnitLabel();
    }
}

function updateModalTotal() {
    if (!currentProduct) return;
    const unitPrice = parseFloat(pricePerUnitInput.value) || 0;
    document.getElementById('totalPriceDisplay').textContent = `Total: ${fmtCur(unitPrice)}`;
    const amount = currentMeasurementType === 'quantity' ? parseInt(modalQty.value) || 1 : parseFloat(modalUnit.value) || 1;
    const calc   = originalPricePerUnit * amount;
    const calcEl = document.getElementById('calculatedPriceDisplay');
    if (calcEl) calcEl.textContent = `${fmtCur(originalPricePerUnit)} × ${fmtQty(amount, currentMeasurementType === 'unit')} = ${fmtCur(calc)}`;
}

function checkSavedUnitPreference() {
    if (!currentProduct || !selectedUnit) return;
    const prefs = JSON.parse(localStorage.getItem('unitPreferences') || '{}');
    if (prefs[currentProduct.id]?.unitId === selectedUnit.id) {
        document.getElementById('rememberUnitPreference').checked = true;
    }
}
function saveUnitPreference() {
    if (!currentProduct || !selectedUnit) return;
    const prefs = JSON.parse(localStorage.getItem('unitPreferences') || '{}');
    prefs[currentProduct.id] = { unitId: selectedUnit.id, unitName: selectedUnit.name, shortName: selectedUnit.short_name, timestamp: Date.now() };
    localStorage.setItem('unitPreferences', JSON.stringify(prefs));
    showToast('Unit preference saved', 'success');
}
function getSavedUnitPreference(productId) {
    return JSON.parse(localStorage.getItem('unitPreferences') || '{}')[productId];
}
function loadUnitPreferences() {}

// ══════════════════════════════════════════════
// CART OPERATIONS
// ══════════════════════════════════════════════
confirmAddBtn.addEventListener('click', addOrUpdateProductInCart);
removeFromCartBtn.addEventListener('click', removeCurrentProductFromCart);
document.getElementById('clearCart').addEventListener('click', clearCart);
document.getElementById('holdOrderBtn').addEventListener('click', holdOrder);
document.getElementById('loadHeldBtn').addEventListener('click', loadHeldOrders);
document.getElementById('completeOrder').addEventListener('click', completeOrder);
applyDiscountBtn.addEventListener('click', applyOrderDiscount);

document.getElementById('quickCustomerBtn').addEventListener('click', () => quickCustomerModal.show());
document.getElementById('saveQuickCustomerBtn').addEventListener('click', saveQuickCustomer);

document.getElementById('applyItemDiscountBtn').addEventListener('click', function () {
    if (currentItemIndex === null) return;
    const val  = parseFloat(document.getElementById('itemDiscountValue').value) || 0;
    const type = document.getElementById('itemDiscountType').value;
    if (type === 'percent' && val > 100) { showToast('Cannot exceed 100%', 'warning'); return; }
    cart[currentItemIndex].discount_type  = type;
    cart[currentItemIndex].discount_value = val;
    updateCart(); renderAll();
    bootstrap.Modal.getInstance(document.getElementById('itemDiscountModal')).hide();
    showToast('Discount applied!', 'success');
});

discountValue.addEventListener('input', function () {
    const val = parseFloat(this.value) || 0;
    if (discountType.value === 'percent' && val > 100) { this.value = 100; orderDiscountValue = 100; }
    else orderDiscountValue = val;
});
discountValue.addEventListener('click', function () { this.focus(); this.select(); });

function addOrUpdateProductInCart() {
    if (!currentProduct) return;
    if (currentProduct.stock <= 0) { showToast(`${currentProduct.title} is out of stock`, 'error'); quantityModal.hide(); return; }
    if (currentMeasurementType === 'unit' && !selectedUnit) { showToast('Please select a unit', 'warning'); unitSelect.focus(); return; }

    const isUnitMode = currentMeasurementType === 'unit';
    const totalPrice = parseFloat(pricePerUnitInput.value) || 0;
    let quantity, unitId, unitName, unitShortName;

    if (isUnitMode) {
        quantity      = parseFloat(modalUnit.value) || 0.001;
        if (quantity <= 0) { showToast('Amount must be > 0', 'warning'); return; }
        if (quantity > currentProduct.stock) { showToast(`Only ${fmt(currentProduct.stock, 3)} ${selectedUnit.short_name} available`, 'warning'); return; }
        unitId        = selectedUnit.id;
        unitName      = selectedUnit.name;
        unitShortName = selectedUnit.short_name;
    } else {
        quantity = parseInt(modalQty.value) || 1;
        if (quantity < 1) { showToast('Qty must be at least 1', 'warning'); return; }
        if (quantity > currentProduct.stock) { showToast(`Only ${fmt(currentProduct.stock)} units available`, 'warning'); return; }
        unitId        = currentProduct.primary_unit_id || 1;
        unitName      = currentProduct.primary_unit || 'Unit';
        unitShortName = unitName;
    }

    const p         = currentProduct;
    const unitPrice = originalPricePerUnit;

    if (!allSearchedProducts.some(sp => sp.id === p.id)) allSearchedProducts.unshift({...p});

    const existing = cart.find(i => i.product_id === p.id);
    const cartData = {
        product_id: p.id, title: p.title, price: totalPrice,
        unit_price: unitPrice, qty: quantity, unit_name: unitName,
        unit_short_name: unitShortName, unit_id: parseInt(unitId),
        sku: p.sku, barcode: p.barcode, thumbnail: p.thumbnail,
        discount_type: 'percent', discount_value: 0, discounted_price: unitPrice,
        is_unit_mode: isUnitMode, original_unit: isUnitMode ? selectedUnit : null,
        price_per_unit: unitPrice,
    };
    if (existing) Object.assign(existing, cartData);
    else cart.push(cartData);

    productQuantityCache[p.id] = quantity;
    quantityModal.hide();
    updateCart();
    renderAll();
    showCartNotification(cartData, existing ? 'update' : 'add');
    currentProduct = null;
}

function removeCurrentProductFromCart() {
    if (!currentProduct) return;
    Swal.fire({
        title: 'Remove from Cart?',
        text: `Remove ${currentProduct.title}?`,
        icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, remove',
    }).then(res => {
        if (res.isConfirmed) {
            const id = currentProduct.id;
            cart = cart.filter(i => i.product_id != id);
            delete productQuantityCache[id];
            quantityModal.hide();
            updateCart(); renderAll();
            showToast('Removed from cart', 'success');
        }
    });
}

// ══════════════════════════════════════════════
// UPDATE CART UI
// ══════════════════════════════════════════════
function updateCart() {
    cartCountEl.textContent = cart.length;

    if (cart.length === 0) {
        cartList.innerHTML = '';
        cartList.appendChild(emptyCartRow);
        emptyCartRow.style.display = 'flex';
        subtotalEl.textContent = discountEl.textContent = grandTotalEl.textContent = fmtCur(0);
        document.getElementById('taxAmount').textContent = fmtCur(0);
        document.getElementById('discountRow').style.display = 'none';
        renderAll();
        return;
    }

    emptyCartRow.style.display = 'none';
    cartList.innerHTML = '';
    let subtotal = 0;

    cart.forEach((item, i) => {
        const unitPrice = item.price_per_unit || (item.price / item.qty);
        let discountedUnit = unitPrice;
        if (item.discount_value > 0) {
            discountedUnit = item.discount_type === 'percent'
                ? unitPrice * (1 - item.discount_value / 100)
                : unitPrice - (item.discount_value / item.qty);
            discountedUnit = Math.max(0, discountedUnit);
            item.discounted_price = discountedUnit;
        } else {
            item.discounted_price = unitPrice;
        }
        const total       = discountedUnit * item.qty;
        subtotal         += total;
        const displayUnit = item.unit_short_name || item.unit_name || 'Unit';
        const hasDisc     = item.discount_value > 0;

        const el = document.createElement('div');
        el.className = 'pos-cart-item';
        el.innerHTML = `
            <div class="pci-thumb">
                ${item.thumbnail
                    ? `<img src="${item.thumbnail}" alt="">`
                    : `<i class="bi bi-bag-fill"></i>`}
                ${item.is_unit_mode ? `<span class="pci-unit-dot" title="Unit mode"><i class="bi bi-scale"></i></span>` : ''}
            </div>
            <div class="pci-info">
                <div class="pci-name">${item.title}</div>
                <div class="pci-meta">
                    <span class="pci-sku">${item.sku}</span>
                    ${hasDisc ? `<span class="pci-disc">-${fmt(item.discount_value, 2)}${item.discount_type === 'percent' ? '%' : '₦'}</span>` : ''}
                </div>
            </div>
            <div class="pci-mid">
                <button class="pci-qty-btn qty-btn-cart"
                    data-product='${JSON.stringify(item).replace(/'/g,"&apos;")}' data-product-id="${item.product_id}">
                    ${fmtQty(item.qty, item.is_unit_mode)} <span class="pci-unit-lbl">${displayUnit}</span>
                </button>
                <div class="pci-unit-price">${fmtCur(discountedUnit)} each</div>
            </div>
            <div class="pci-right">
                <div class="pci-total">${fmtCur(total)}</div>
                <div class="pci-actions">
                    <button class="pci-action-btn pci-action-btn--disc item-discount-btn" data-product-id="${item.product_id}" title="Discount">
                        <i class="bi bi-percent"></i>
                    </button>
                    <button class="pci-action-btn pci-action-btn--del remove-cart-item-btn" data-index="${i}" title="Remove">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
            </div>
        `;

        el.querySelector('.remove-cart-item-btn').addEventListener('click', function () { removeCartItem(parseInt(this.dataset.index)); });
        el.querySelector('.item-discount-btn').addEventListener('click', function () {
            const pid = this.dataset.productId;
            currentItemIndex = cart.findIndex(ci => ci.product_id === pid);
            if (currentItemIndex === -1) return;
            const ci = cart[currentItemIndex];
            document.getElementById('itemName').textContent        = ci.title;
            document.getElementById('itemDiscountValue').value     = ci.discount_value || 0;
            document.getElementById('itemDiscountType').value      = ci.discount_type || 'percent';
            new bootstrap.Modal(document.getElementById('itemDiscountModal')).show();
        });
        el.querySelector('.qty-btn-cart').addEventListener('click', function () { openQuantityModal(this); });

        cartList.appendChild(el);
    });

    const taxRate             = {{ config('pos.tax_rate', 0) }};
    const taxAmount           = taxRate > 0 ? (subtotal * taxRate) / 100 : 0;
    let   orderDiscountAmount = 0;
    if (orderDiscountValue > 0) {
        orderDiscountAmount = orderDiscountType === 'percent'
            ? (subtotal * orderDiscountValue) / 100
            : orderDiscountValue;
        orderDiscountAmount = Math.min(orderDiscountAmount, subtotal);
    }
    const grandTotal = subtotal + taxAmount - orderDiscountAmount;

    subtotalEl.textContent = fmtCur(subtotal);
    document.getElementById('taxAmount').textContent = fmtCur(taxAmount);
    discountEl.textContent = `-${fmtCur(orderDiscountAmount)}`;
    grandTotalEl.textContent = fmtCur(grandTotal);
    document.getElementById('discountRow').style.display = orderDiscountValue > 0 ? 'flex' : 'none';
    window.currentDiscount = { type: orderDiscountType, value: orderDiscountValue, amount: orderDiscountAmount };
}

function removeCartItem(index) {
    const item = cart[index];
    cart.splice(index, 1);
    delete productQuantityCache[item.product_id];
    updateCart(); renderAll();
    showToast('Item removed', 'success');
}

function applyOrderDiscount() {
    orderDiscountValue = parseFloat(discountValue.value) || 0;
    orderDiscountType  = discountType.value;
    if (orderDiscountType === 'percent' && orderDiscountValue > 100) {
        showToast('Discount cannot exceed 100%', 'warning');
        orderDiscountValue = 100; discountValue.value = 100;
    }
    updateCart();
    showToast('Discount applied!', 'success');
}

function clearCart() {
    if (cart.length === 0) { showToast('Cart is already empty', 'info'); return; }
    Swal.fire({ title: 'Clear Cart?', text: 'Remove all items?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, clear' })
        .then(res => {
            if (res.isConfirmed) { cart = []; productQuantityCache = {}; updateCart(); renderAll(); showToast('Cart cleared', 'success'); }
        });
}

// ══════════════════════════════════════════════
// HOLD / LOAD ORDERS
// ══════════════════════════════════════════════
function holdOrder() {
    if (cart.length === 0) { showToast('Cart is empty', 'info'); return; }
    const held = JSON.parse(localStorage.getItem('heldOrders') || '[]');
    held.push({
        id: Date.now(), cart: JSON.parse(JSON.stringify(cart)),
        customer: customerSelect.value,
        allSearchedProducts: JSON.parse(JSON.stringify(allSearchedProducts)),
        productQuantityCache: JSON.parse(JSON.stringify(productQuantityCache)),
        discount: { type: orderDiscountType, value: orderDiscountValue },
        time: new Date().toLocaleString(), timestamp: Date.now(),
    });
    localStorage.setItem('heldOrders', JSON.stringify(held));
    cart = []; productQuantityCache = {}; orderDiscountValue = 0; discountValue.value = '0';
    updateCart(); renderAll();
    showToast('Order held!', 'success');
}

function loadHeldOrders() {
    const held   = JSON.parse(localStorage.getItem('heldOrders') || '[]');
    const list   = document.getElementById('heldOrdersList');
    const noOrds = document.getElementById('noHeldOrders');
    if (held.length === 0) { list.innerHTML = ''; noOrds.style.display = 'flex'; }
    else {
        noOrds.style.display = 'none';
        let html = '';
        held.sort((a,b) => b.timestamp - a.timestamp).forEach(order => {
            const items = order.cart.length;
            const total = order.cart.reduce((s,it) => s + (it.price || 0), 0);
            html += `
            <div class="pos-held-order">
                <div class="pho-info">
                    <div class="pho-time">${order.time}</div>
                    <div class="pho-meta">${items} item${items !== 1 ? 's' : ''} &bull; ${fmtCur(total)}</div>
                </div>
                <div class="pho-actions">
                    <button class="pos-btn pos-btn--primary-sm load-order-btn" data-order-id="${order.id}">Load</button>
                    <button class="pos-btn pos-btn--ghost-danger-sm remove-order-btn" data-order-id="${order.id}"><i class="bi bi-trash3"></i></button>
                </div>
            </div>`;
        });
        list.innerHTML = html;

        document.querySelectorAll('.load-order-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const oid = this.dataset.orderId;
                const all = JSON.parse(localStorage.getItem('heldOrders') || '[]');
                const ord = all.find(o => o.id == oid);
                if (!ord) return;
                if (cart.length > 0) {
                    Swal.fire({ title: 'Replace Cart?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, replace' })
                        .then(r => r.isConfirmed && loadOrderFromHeld(ord));
                } else { loadOrderFromHeld(ord); }
            });
        });
        document.querySelectorAll('.remove-order-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                const oid = this.dataset.orderId;
                Swal.fire({ title: 'Remove Order?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes' })
                    .then(r => {
                        if (r.isConfirmed) {
                            const remaining = JSON.parse(localStorage.getItem('heldOrders') || '[]').filter(o => o.id != oid);
                            localStorage.setItem('heldOrders', JSON.stringify(remaining));
                            loadHeldOrders();
                        }
                    });
            });
        });
    }
    new bootstrap.Modal(document.getElementById('loadOrderModal')).show();
}

function loadOrderFromHeld(order) {
    cart                 = JSON.parse(JSON.stringify(order.cart));
    allSearchedProducts  = order.allSearchedProducts ? JSON.parse(JSON.stringify(order.allSearchedProducts)) : allSearchedProducts;
    productQuantityCache = order.productQuantityCache ? JSON.parse(JSON.stringify(order.productQuantityCache)) : {};
    if (order.customer) customerSelect.value = order.customer;
    if (order.discount) {
        orderDiscountType = order.discount.type; orderDiscountValue = order.discount.value;
        discountType.value = order.discount.type; discountValue.value = order.discount.value;
    }
    updateCart(); renderAll();
    bootstrap.Modal.getInstance(document.getElementById('loadOrderModal')).hide();
    showToast('Order loaded!', 'success');
}

// ══════════════════════════════════════════════
// COMPLETE ORDER
// ══════════════════════════════════════════════
async function completeOrder() {
    if (cart.length === 0) { showToast('Cart is empty', 'warning'); return; }
    if (isProcessingOrder) { showToast('Already processing…', 'info'); return; }

    const payment    = document.querySelector('input[name="payment"]:checked').value;
    const customerId = customerSelect.value || null;
    const isOffline  = document.getElementById('offlineModeToggle').checked || !navigator.onLine;

    if (isOffline) {
        const offlineOrders = JSON.parse(localStorage.getItem('offlineOrders') || '[]');
        const oid           = 'OFFLINE-' + Date.now();
        offlineOrders.push({
            id: oid, items: cart.map(item => ({
                product_id: item.product_id, qty: parseFloat(item.qty),
                unit_id: parseInt(item.unit_id || 1), sale_price: parseFloat(item.unit_price || item.price),
                discount_type: item.discount_type || null, discount_value: item.discount_value || 0,
                is_unit_mode: item.is_unit_mode || false, unit_name: item.unit_name || null,
            })),
            payment_method: payment, customer_id: customerId,
            discount_type: orderDiscountType, discount_value: orderDiscountValue,
            discount_amount: window.currentDiscount?.amount || 0,
            tax_rate: {{ config('pos.tax_rate', 0) }},
            timestamp: Date.now(), time: new Date().toLocaleString(),
        });
        localStorage.setItem('offlineOrders', JSON.stringify(offlineOrders));
        cart = []; productQuantityCache = {}; orderDiscountValue = 0; discountValue.value = '0';
        updateCart(); renderAll();
        Swal.fire({ title: 'Saved Offline!', text: `Order #${oid} saved locally. Will sync when online.`, icon: 'warning' });
        return;
    }

    isProcessingOrder = true;
    const btn = document.getElementById('completeOrder');
    btn.disabled = true;
    btn.innerHTML = '<span class="pos-spinner-sm-inline"></span> Processing…';

    const items = cart.map(item => ({
        product_id: item.product_id, qty: parseFloat(item.qty),
        unit_id: parseInt(item.unit_id || 1), sale_price: parseFloat(item.unit_price || item.price / item.qty),
        discount_type: item.discount_type || null, discount_value: item.discount_value || 0,
        is_unit_mode: item.is_unit_mode || false, unit_name: item.unit_name || null,
    }));
    const discount = window.currentDiscount || { type: 'percent', value: 0, amount: 0 };

    Swal.fire({ title: 'Processing…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const res = await axios.post('{{ route("pos.order.save") }}', {
            items, payment_method: payment, customer_id: customerId,
            discount_type: discount.type, discount_value: discount.value, discount_amount: discount.amount,
            _token: '{{ csrf_token() }}',
        });
        Swal.close();
        if (res.data.success) {
            if (thankYouAudio) { thankYouAudio.currentTime = 0; thankYouAudio.play().catch(() => {}); }
            Swal.fire({
                title: 'Order Complete!',
                html: `<div class="text-center">
                    <div style="font-size:3rem;color:#22c55e;">✓</div>
                    <h4 style="font-weight:700">Order #${res.data.order_id}</h4>
                    <p style="font-size:1.5rem;font-weight:800;color:#16a34a;">${fmtCur(res.data.total)}</p>
                </div>`,
                icon: 'success', showCancelButton: true,
                confirmButtonText: 'Print Receipt', cancelButtonText: 'New Order',
                buttonsStyling: false,
                customClass: { confirmButton: 'pos-btn pos-btn--primary me-2', cancelButton: 'pos-btn pos-btn--ghost' },
            }).then(r => {
                if (r.isConfirmed) {
                    printWindow = window.open(`/pos/receipt/${res.data.order_id}`, '_blank');
                    if (printWindow) startMonitoringPrintWindow();
                    else resetAfterOrder();
                } else { resetAfterOrder(); }
            });
        } else {
            Swal.fire('Error', res.data.message || 'Failed to process', 'error');
        }
    } catch (e) {
        Swal.close();
        let msg = 'Failed to complete order.';
        if (e.response?.data?.errors) msg = Object.values(e.response.data.errors).flat().join('<br>');
        else if (e.response?.data?.message) msg = e.response.data.message;
        if (!navigator.onLine || e.response?.status === 0) {
            Swal.fire({ title: 'Offline?', text: 'Save order for later sync?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Save Offline' })
                .then(r => { if (r.isConfirmed) { document.getElementById('offlineModeToggle').checked = true; isProcessingOrder = false; completeOrder(); } });
        } else { Swal.fire('Error', msg, 'error'); }
    } finally {
        isProcessingOrder = false;
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-printer-fill"></i> Complete & Print';
    }
}

function startMonitoringPrintWindow() {
    if (printCheckInterval) clearInterval(printCheckInterval);
    printCheckInterval = setInterval(() => {
        if (printWindow && printWindow.closed) { clearInterval(printCheckInterval); printCheckInterval = null; resetAfterOrder(); }
    }, 500);
}

function resetAfterOrder() {
    cart = []; allSearchedProducts = []; productQuantityCache = [];
    orderDiscountValue = 0; discountValue.value = '0';
    updateCart();
    loadFeaturedProducts();
    input.value = ''; searchClearBtn.style.display = 'none'; input.focus();
    showToast('Ready for new order', 'info');
}

// ══════════════════════════════════════════════
// CUSTOMER
// ══════════════════════════════════════════════
function initializeCustomerSearch() {
    const origOptions = Array.from(customerSelect.options);
    const searchEl    = document.getElementById('customerSearchInput');
    searchEl.addEventListener('input', function () {
        const term = this.value.toLowerCase().trim();
        customerSelect.innerHTML = '';
        const filtered = term.length === 0
            ? origOptions
            : origOptions.filter(o => o.value === '' || o.text.toLowerCase().includes(term));
        filtered.forEach(o => customerSelect.appendChild(o.cloneNode(true)));
        document.getElementById('customerCount').textContent = filtered.length - 1;
    });
    document.addEventListener('keydown', e => {
        if (e.ctrlKey && e.key === 'f') { e.preventDefault(); searchEl.focus(); searchEl.select(); }
    });
}

function initializeQuickCustomerModal() {
    document.getElementById('quickCustomerForm').addEventListener('submit', e => { e.preventDefault(); saveQuickCustomer(); });
    document.getElementById('quickCustomerModal').addEventListener('hidden.bs.modal', () => {
        document.getElementById('quickCustomerForm').reset();
    });
}

async function saveQuickCustomer() {
    const first = document.getElementById('firstName').value.trim();
    const last  = document.getElementById('lastName').value.trim();
    const phone = document.getElementById('phoneNumber').value.trim();
    const email = document.getElementById('email').value.trim();
    if (!first || !last) { showToast('Name is required', 'warning'); return; }

    const btn = document.getElementById('saveQuickCustomerBtn');
    btn.innerHTML = '<span class="pos-spinner-sm-inline"></span> Saving…';
    btn.disabled  = true;

    try {
        const res = await axios.post('{{ route("customers.quick") }}', { first_name: first, last_name: last, phone_number: phone, email, _token: '{{ csrf_token() }}' });
        if (res.data.success) {
            const opt = document.createElement('option');
            opt.value = res.data.customer.id;
            opt.textContent = `${first} ${last}${phone ? ` – ${phone}` : ''}`;
            customerSelect.appendChild(opt);
            customerSelect.value = res.data.customer.id;
            document.getElementById('customerCount').textContent = parseInt(document.getElementById('customerCount').textContent) + 1;
            quickCustomerModal.hide();
            showToast('Customer added!', 'success');
        } else { showToast(res.data.message || 'Failed', 'error'); }
    } catch (e) {
        const msg = e.response?.data?.message || Object.values(e.response?.data?.errors || {}).flat().join(', ') || 'Failed';
        showToast(msg, 'error');
    } finally {
        btn.innerHTML = 'Save Customer'; btn.disabled = false;
    }
}

// ══════════════════════════════════════════════
// OFFLINE / CONNECTIVITY
// ══════════════════════════════════════════════
function updateConnectionStatus(online) {
    const el  = document.getElementById('connectionStatus');
    const tog = document.getElementById('offlineModeToggle');
    el.dataset.online = online ? 'true' : 'false';
    el.querySelector('.pos-status-label').textContent = online ? 'Online' : 'Offline';
    if (!online) tog.checked = true;
    else tog.checked = false;
}

updateConnectionStatus(navigator.onLine);
window.addEventListener('online',  () => { updateConnectionStatus(true);  showToast('Back online!', 'success'); syncOfflineOrders(); });
window.addEventListener('offline', () => { updateConnectionStatus(false); showToast('You are offline', 'warning'); });

async function syncOfflineOrders() {
    const offline = JSON.parse(localStorage.getItem('offlineOrders') || '[]');
    if (offline.length === 0) return;
    let synced = 0, failed = 0, lastId = null;
    for (const order of [...offline]) {
        try {
            const res = await axios.post('{{ route("pos.order.save") }}', { ...order, _token: '{{ csrf_token() }}' });
            if (res.data.success) {
                synced++; lastId = res.data.order_id;
                const remaining = JSON.parse(localStorage.getItem('offlineOrders') || '[]').filter(o => o.id !== order.id);
                localStorage.setItem('offlineOrders', JSON.stringify(remaining));
            } else { failed++; }
        } catch { failed++; }
    }
    if (synced > 0) {
        Swal.fire({
            title: `${synced} Order${synced > 1 ? 's' : ''} Synced!`,
            icon: 'success', showCancelButton: !!lastId,
            confirmButtonText: 'Print Last Receipt', cancelButtonText: 'Dismiss',
        }).then(r => { if (r.isConfirmed && lastId) { printWindow = window.open(`/pos/receipt/${lastId}`, '_blank'); if (printWindow) startMonitoringPrintWindow(); } });
    }
    if (failed > 0) showToast(`${failed} order(s) failed to sync`, 'error', 4000);
}

// ══════════════════════════════════════════════
// KEYBOARD SHORTCUTS
// ══════════════════════════════════════════════
document.addEventListener('keydown', function (e) {
    if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) return;
    switch (e.key) {
        case 'F1': e.preventDefault(); input.focus(); input.select(); break;
        case 'F2': e.preventDefault(); clearCart(); break;
        case 'F3': e.preventDefault(); completeOrder(); break;
        case 'F4': e.preventDefault(); holdOrder(); break;
        case 'F5': e.preventDefault(); loadHeldOrders(); break;
        case 'F6': e.preventDefault(); applyViewMode(currentViewMode === 'grid' ? 'list' : 'grid'); break;
        case 'Escape': if (cart.length > 0) clearCart(); break;
    }
});

// ══════════════════════════════════════════════
// HELPERS
// ══════════════════════════════════════════════
function showToast(message, type = 'success', duration = 2200) {
    Swal.mixin({
        toast: true, position: 'bottom-end', showConfirmButton: false,
        timer: duration, timerProgressBar: true,
        didOpen: t => { t.onmouseenter = Swal.stopTimer; t.onmouseleave = Swal.resumeTimer; },
    }).fire({ icon: type, title: message });
}

function debounce(fn, delay) {
    let t;
    return function (...a) { clearTimeout(t); t = setTimeout(() => fn.apply(this, a), delay); };
}

// ══════════════════════════════════════════════
// BOOT
// ══════════════════════════════════════════════
initializeApp();
updateCart();

});
</script>

<style>
/* ════════════════════════════════════════════════════════════
   CSS CUSTOM PROPERTIES
════════════════════════════════════════════════════════════ */
:root {
    --pos-bg:          #f0f2f5;
    --pos-surface:     #ffffff;
    --pos-surface-2:   #f8fafc;
    --pos-border:      #e2e8f0;
    --pos-border-2:    #cbd5e1;
    --pos-text:        #0f172a;
    --pos-text-2:      #475569;
    --pos-text-3:      #94a3b8;
    --pos-primary:     #2563eb;
    --pos-primary-d:   #1d4ed8;
    --pos-primary-l:   #dbeafe;
    --pos-success:     #16a34a;
    --pos-success-l:   #dcfce7;
    --pos-warn:        #d97706;
    --pos-warn-l:      #fef3c7;
    --pos-danger:      #dc2626;
    --pos-danger-l:    #fee2e2;
    --pos-info:        #0891b2;
    --pos-info-l:      #cffafe;
    --pos-accent:      #7c3aed;
    --pos-radius:      12px;
    --pos-radius-sm:   8px;
    --pos-shadow:      0 1px 3px rgba(0,0,0,.08), 0 4px 16px rgba(0,0,0,.06);
    --pos-shadow-lg:   0 8px 30px rgba(0,0,0,.12);
    --topbar-h:        64px;
    --font-display:    'DM Sans', 'Segoe UI', system-ui, sans-serif;
}

/* ════════════════════════════════════════════════════════════
   SHELL
════════════════════════════════════════════════════════════ */
.pos-shell {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 60px); /* subtract master layout header if any */
    min-height: 600px;
    background: var(--pos-bg);
    font-family: var(--font-display);
}

/* ════════════════════════════════════════════════════════════
   TOP BAR
════════════════════════════════════════════════════════════ */
.pos-topbar {
    height: var(--topbar-h);
    background: var(--pos-surface);
    border-bottom: 1px solid var(--pos-border);
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 0 20px;
    flex-shrink: 0;
    box-shadow: 0 1px 0 var(--pos-border);
}
.pos-topbar__left { display: flex; align-items: center; gap: 16px; flex-shrink: 0; }
.pos-logo { display: flex; align-items: center; gap: 8px; font-weight: 800; font-size: 1.1rem; color: var(--pos-primary); }
.pos-logo i { font-size: 1.4rem; }
.pos-topbar__breadcrumb { display: flex; align-items: center; gap: 6px; font-size: .8rem; color: var(--pos-text-3); }
.pos-topbar__breadcrumb a { color: var(--pos-primary); text-decoration: none; }
.pos-topbar__breadcrumb a:hover { text-decoration: underline; }
.pos-topbar__center { flex: 1; max-width: 640px; margin: 0 auto; }
.pos-topbar__right { display: flex; align-items: center; gap: 12px; flex-shrink: 0; }

.pos-search-wrap {
    position: relative;
    display: flex;
    align-items: center;
}
.pos-search-icon {
    position: absolute; left: 14px; font-size: 1.1rem; color: var(--pos-text-3); pointer-events: none; z-index: 1;
}
.pos-search-input {
    width: 100%;
    height: 44px;
    padding: 0 44px 0 42px;
    border: 2px solid var(--pos-border);
    border-radius: 22px;
    font-size: .95rem;
    font-family: var(--font-display);
    background: var(--pos-surface-2);
    color: var(--pos-text);
    transition: border-color .2s, box-shadow .2s, background .2s;
    outline: none;
}
.pos-search-input:focus {
    border-color: var(--pos-primary);
    background: #fff;
    box-shadow: 0 0 0 4px rgba(37,99,235,.1);
}
.pos-search-clear {
    position: absolute; right: 42px; display: flex; align-items: center;
    cursor: pointer; color: var(--pos-text-3); font-size: 1rem;
    transition: color .15s;
}
.pos-search-clear:hover { color: var(--pos-danger); }
.pos-search-loader { position: absolute; right: 12px; display: flex; align-items: center; }

/* Connection status */
.pos-status-pill {
    display: flex; align-items: center; gap: 6px;
    padding: 4px 10px; border-radius: 20px;
    font-size: .75rem; font-weight: 600;
    background: var(--pos-success-l); color: var(--pos-success);
    transition: all .3s;
}
.pos-status-pill[data-online="false"] { background: var(--pos-danger-l); color: var(--pos-danger); }
.pos-status-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: currentColor;
    animation: statusPulse 2s infinite;
}
@keyframes statusPulse { 0%,100%{opacity:1} 50%{opacity:.4} }

/* Offline toggle */
.pos-offline-toggle {
    display: flex; align-items: center; gap: 7px; cursor: pointer; user-select: none;
    font-size: .78rem; color: var(--pos-text-2);
}
.pos-offline-toggle input { display: none; }
.pos-toggle-track {
    width: 34px; height: 18px; border-radius: 9px;
    background: var(--pos-border-2); position: relative; transition: background .2s;
}
.pos-offline-toggle input:checked + .pos-toggle-track { background: var(--pos-warn); }
.pos-toggle-thumb {
    position: absolute; top: 2px; left: 2px;
    width: 14px; height: 14px; border-radius: 50%;
    background: #fff; transition: left .2s;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.pos-offline-toggle input:checked + .pos-toggle-track .pos-toggle-thumb { left: 18px; }
.pos-toggle-label { font-size: .75rem; }

/* Kbd shortcuts */
.pos-topbar__shortcuts { font-size: .72rem; color: var(--pos-text-3); white-space: nowrap; }
.kbd-badge {
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--pos-surface-2); border: 1px solid var(--pos-border);
    border-radius: 4px; padding: 1px 5px; font-size: .7rem; font-weight: 700;
    font-family: monospace; color: var(--pos-text-2);
}

/* ════════════════════════════════════════════════════════════
   BODY — TWO COLUMNS
════════════════════════════════════════════════════════════ */
.pos-body {
    flex: 1;
    display: grid;
    grid-template-columns: 1fr 370px;
    gap: 0;
    overflow: hidden;
}

/* ════════════════════════════════════════════════════════════
   PRODUCTS PANEL (LEFT)
════════════════════════════════════════════════════════════ */
.pos-products-panel {
    display: flex; flex-direction: column;
    background: var(--pos-bg);
    border-right: 1px solid var(--pos-border);
    overflow: hidden; position: relative;
}

/* Toolbar */
.pos-toolbar {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 16px;
    background: var(--pos-surface);
    border-bottom: 1px solid var(--pos-border);
    flex-shrink: 0;
    flex-wrap: wrap;
}

/* View toggle */
.pos-view-toggle { display: flex; border: 1px solid var(--pos-border); border-radius: var(--pos-radius-sm); overflow: hidden; flex-shrink: 0; }
.pos-vt-radio { display: none; }
.pos-vt-btn {
    display: flex; align-items: center; gap: 5px;
    padding: 6px 14px; font-size: .8rem; font-weight: 600;
    cursor: pointer; background: transparent; color: var(--pos-text-2);
    transition: all .15s; white-space: nowrap;
}
.pos-vt-btn:hover { background: var(--pos-surface-2); }
.pos-vt-radio:checked + .pos-vt-btn { background: var(--pos-primary); color: #fff; }

/* Category chips */
.pos-cat-scroll {
    display: flex; align-items: center; gap: 6px;
    overflow-x: auto; flex: 1;
    scrollbar-width: none;
    padding: 2px 0;
}
.pos-cat-scroll::-webkit-scrollbar { display: none; }
.pos-cat-chip {
    display: flex; align-items: center;
    padding: 4px 14px; border-radius: 20px; border: 1.5px solid var(--pos-border);
    font-size: .78rem; font-weight: 600; cursor: pointer;
    background: var(--pos-surface); color: var(--pos-text-2);
    transition: all .15s; white-space: nowrap; flex-shrink: 0;
}
.pos-cat-chip:hover { border-color: var(--pos-primary); color: var(--pos-primary); }
.pos-cat-chip.active { background: var(--pos-primary); border-color: var(--pos-primary); color: #fff; }

/* Toolbar actions */
.pos-toolbar__actions { display: flex; gap: 6px; flex-shrink: 0; }

/* Loading overlay */
.pos-loading-overlay {
    position: absolute; inset: 0; background: rgba(255,255,255,.85);
    display: flex; align-items: center; justify-content: center;
    z-index: 20; backdrop-filter: blur(2px);
}
.pos-loading-inner { text-align: center; }
.pos-loading-inner p { margin-top: 12px; font-size: .9rem; color: var(--pos-text-2); font-weight: 500; }

/* ════════════════════════════════════════════════════════════
   GRID VIEW — SUPERMARKET STYLE
════════════════════════════════════════════════════════════ */
.pos-grid-panel { flex: 1; overflow-y: auto; padding: 14px 16px; position: relative; }
.pos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 12px;
}

.pos-grid-card {
    background: var(--pos-surface);
    border: 2px solid var(--pos-border);
    border-radius: var(--pos-radius);
    cursor: pointer;
    transition: transform .18s cubic-bezier(.34,1.56,.64,1), border-color .15s, box-shadow .15s;
    position: relative;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-height: 190px;
    user-select: none;
}
.pos-grid-card:hover:not(.is-out) {
    transform: translateY(-4px) scale(1.01);
    border-color: var(--pos-primary);
    box-shadow: 0 8px 24px rgba(37,99,235,.18);
}
.pos-grid-card:active:not(.is-out) { transform: scale(.98); }
.pos-grid-card:focus-visible { outline: 3px solid var(--pos-primary); outline-offset: 2px; }

.pos-grid-card.in-cart {
    border-color: var(--pos-success);
    background: linear-gradient(180deg, #f0fdf4 0%, #fff 60%);
}
.pos-grid-card.in-cart:hover { border-color: #15803d; box-shadow: 0 8px 24px rgba(22,163,74,.2); }

.pos-grid-card.is-out {
    opacity: .5; cursor: default; filter: grayscale(.5);
}

/* In-cart badge */
.pgc-in-cart-badge {
    position: absolute; top: 6px; left: 6px;
    background: var(--pos-success); color: #fff;
    font-size: 10px; font-weight: 700; padding: 2px 7px;
    border-radius: 20px; display: flex; align-items: center; gap: 3px;
    z-index: 2; box-shadow: 0 2px 6px rgba(22,163,74,.35);
}

/* Stock badge */
.pgc-stock {
    position: absolute; top: 6px; right: 6px;
    font-size: 10px; font-weight: 700; padding: 2px 6px;
    border-radius: 20px; z-index: 2;
}
.pgc-stock--high { background: var(--pos-success-l); color: var(--pos-success); }
.pgc-stock--low  { background: var(--pos-warn-l); color: var(--pos-warn); }
.pgc-stock--out  { background: var(--pos-danger-l); color: var(--pos-danger); }
.pgc-stock--inline { position: static; display: inline-flex; }

/* Thumb */
.pgc-thumb {
    width: 100%; height: 110px;
    background: var(--pos-surface-2);
    display: flex; align-items: center; justify-content: center;
    font-size: 2.5rem; color: var(--pos-text-3);
    overflow: hidden;
    flex-shrink: 0;
    border-bottom: 1px solid var(--pos-border);
}
.pgc-thumb img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform .3s ease;
}
.pos-grid-card:hover .pgc-thumb img { transform: scale(1.06); }

/* Info block */
.pgc-info {
    padding: 8px 8px 6px;
    display: flex; flex-direction: column; gap: 2px;
    flex: 1;
}
.pgc-name {
    font-size: 12px; font-weight: 700; color: var(--pos-text);
    line-height: 1.3; max-height: 2.6em;
    overflow: hidden; display: -webkit-box;
    -webkit-line-clamp: 2; -webkit-box-orient: vertical;
}
.pgc-price { font-size: 13px; font-weight: 800; color: var(--pos-success); margin-top: 2px; }
.pgc-sku   { font-size: 10px; color: var(--pos-text-3); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.pgc-pref  { font-size: 10px; color: var(--pos-warn); }

/* Add overlay on hover */
.pgc-add-overlay {
    position: absolute; bottom: 0; left: 0; right: 0;
    background: var(--pos-primary); color: #fff;
    display: flex; align-items: center; justify-content: center; gap: 5px;
    padding: 7px; font-size: 12px; font-weight: 700;
    transform: translateY(100%);
    transition: transform .2s ease;
}
.pos-grid-card:hover .pgc-add-overlay { transform: translateY(0); }
.pgc-out-label {
    position: absolute; bottom: 0; left: 0; right: 0;
    background: rgba(0,0,0,.55); color: #fff;
    padding: 5px; text-align: center;
    font-size: 11px; font-weight: 700;
}

/* No cat message */
.pos-no-cat { grid-column: 1/-1; text-align: center; padding: 48px; color: var(--pos-text-3); }
.pos-no-cat i { font-size: 2.5rem; display: block; margin-bottom: 12px; }
.pos-no-cat p { font-size: .9rem; }

/* ════════════════════════════════════════════════════════════
   LIST VIEW
════════════════════════════════════════════════════════════ */
.pos-list-panel { flex: 1; overflow-y: auto; }
.pos-list-table { width: 100%; border-collapse: collapse; font-size: .87rem; }
.pos-list-table thead th {
    background: var(--pos-surface); position: sticky; top: 0; z-index: 5;
    padding: 10px 14px; font-weight: 700; font-size: .78rem; text-transform: uppercase;
    letter-spacing: .04em; color: var(--pos-text-2); border-bottom: 2px solid var(--pos-border);
}
.pos-list-table tbody tr { border-bottom: 1px solid var(--pos-border); transition: background .12s; }
.pos-list-table tbody tr:hover { background: var(--pos-surface-2); }
.pos-list-table td { padding: 10px 14px; vertical-align: middle; }
.list-row-incart { background: #f0fdf4 !important; }

.pos-list-product { display: flex; align-items: center; gap: 10px; }
.pos-list-thumb {
    width: 48px; height: 48px; border-radius: 8px;
    object-fit: cover; flex-shrink: 0;
}
.pos-list-thumb--placeholder {
    display: flex; align-items: center; justify-content: center;
    background: var(--pos-surface-2); color: var(--pos-text-3); font-size: 1.2rem;
}
.pos-list-meta { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 3px; }
.pos-meta-chip {
    display: inline-flex; align-items: center; gap: 3px;
    padding: 2px 6px; border-radius: 4px;
    font-size: .7rem; background: var(--pos-surface-2); color: var(--pos-text-2);
    border: 1px solid var(--pos-border);
}
.pos-meta-chip--blue { background: var(--pos-info-l); color: var(--pos-info); border-color: transparent; }
.pos-meta-chip--gold { background: var(--pos-warn-l); color: var(--pos-warn); border-color: transparent; }
.pos-unit-badge { padding: 3px 8px; border-radius: 6px; background: var(--pos-primary-l); color: var(--pos-primary); font-size: .75rem; font-weight: 600; }

/* ════════════════════════════════════════════════════════════
   EMPTY STATES
════════════════════════════════════════════════════════════ */
.pos-empty {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; padding: 48px 24px; text-align: center;
    color: var(--pos-text-3);
}
.pos-empty h5 { font-weight: 700; color: var(--pos-text-2); margin: 12px 0 4px; }
.pos-empty p  { font-size: .88rem; margin: 0; }
.pos-empty__icon {
    width: 72px; height: 72px; border-radius: 50%;
    background: var(--pos-surface-2); display: flex; align-items: center;
    justify-content: center; font-size: 1.8rem; color: var(--pos-text-3);
}
.pos-empty--table td { padding: 0; }
.pos-empty--cart { min-height: 180px; }
.pos-empty--cart .pos-empty__icon--cart { font-size: 2rem; }

/* ════════════════════════════════════════════════════════════
   ORDER PANEL (RIGHT)
════════════════════════════════════════════════════════════ */
.pos-order-panel {
    display: flex; flex-direction: column;
    background: var(--pos-surface);
    overflow: hidden;
    padding: 0;
}

/* Customer */
.pos-customer-row {
    display: flex; align-items: center; gap: 8px;
    padding: 12px 16px 0;
    flex-shrink: 0;
}
.pos-customer-select-wrap {
    flex: 1; position: relative; display: flex; align-items: center;
}
.pos-customer-icon {
    position: absolute; left: 10px; font-size: 1.1rem; color: var(--pos-text-3); z-index: 1; pointer-events: none;
}
.pos-customer-select {
    width: 100%; height: 40px; padding: 0 10px 0 34px;
    border: 1.5px solid var(--pos-border); border-radius: var(--pos-radius-sm);
    font-family: var(--font-display); font-size: .85rem; color: var(--pos-text);
    background: var(--pos-surface-2); outline: none;
    transition: border-color .15s, box-shadow .15s;
    appearance: none; cursor: pointer;
}
.pos-customer-select:focus { border-color: var(--pos-primary); box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
.pos-customer-search-row {
    display: flex; align-items: center; gap: 8px;
    padding: 6px 16px 10px;
    border-bottom: 1px solid var(--pos-border);
    flex-shrink: 0;
}
.pos-customer-count { font-size: .72rem; color: var(--pos-text-3); white-space: nowrap; }

/* Cart header */
.pos-cart-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 16px 8px;
    flex-shrink: 0;
}
.pos-cart-header h6 {
    display: flex; align-items: center; gap: 8px;
    font-size: .95rem; font-weight: 700; color: var(--pos-text); margin: 0;
}
.pos-cart-count {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 20px; height: 20px; padding: 0 5px;
    background: var(--pos-primary); color: #fff;
    border-radius: 20px; font-size: .7rem; font-weight: 800;
}
.pos-cart-actions { display: flex; gap: 5px; }

/* Cart list */
.pos-cart-list {
    flex: 1; overflow-y: auto;
    padding: 4px 0;
    border-bottom: 1px solid var(--pos-border);
}
.pos-cart-list::-webkit-scrollbar { width: 4px; }
.pos-cart-list::-webkit-scrollbar-track { background: transparent; }
.pos-cart-list::-webkit-scrollbar-thumb { background: var(--pos-border-2); border-radius: 2px; }

/* Cart item */
.pos-cart-item {
    display: flex; align-items: center; gap: 8px;
    padding: 8px 14px;
    border-bottom: 1px solid var(--pos-border);
    transition: background .12s;
}
.pos-cart-item:hover { background: var(--pos-surface-2); }
.pci-thumb {
    width: 40px; height: 40px; border-radius: 8px; flex-shrink: 0;
    background: var(--pos-surface-2); overflow: hidden;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1rem; color: var(--pos-text-3);
    position: relative;
}
.pci-thumb img { width: 100%; height: 100%; object-fit: cover; }
.pci-unit-dot {
    position: absolute; bottom: -2px; right: -2px;
    background: var(--pos-success); color: #fff;
    border-radius: 50%; width: 14px; height: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 8px;
}
.pci-info { flex: 1; min-width: 0; }
.pci-name { font-size: .82rem; font-weight: 700; color: var(--pos-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pci-meta { display: flex; align-items: center; gap: 5px; margin-top: 2px; }
.pci-sku  { font-size: .7rem; color: var(--pos-text-3); }
.pci-disc { font-size: .7rem; color: var(--pos-warn); font-weight: 600; background: var(--pos-warn-l); padding: 1px 5px; border-radius: 4px; }
.pci-mid  { display: flex; flex-direction: column; align-items: center; gap: 3px; }
.pci-qty-btn {
    background: var(--pos-primary-l); color: var(--pos-primary);
    border: none; border-radius: 8px; padding: 4px 8px;
    font-size: .78rem; font-weight: 700; cursor: pointer;
    transition: all .15s; white-space: nowrap;
    display: flex; align-items: center; gap: 3px;
}
.pci-qty-btn:hover { background: var(--pos-primary); color: #fff; transform: scale(1.05); }
.pci-unit-lbl { font-size: .68rem; font-weight: 600; opacity: .8; }
.pci-unit-price { font-size: .68rem; color: var(--pos-text-3); }
.pci-right { display: flex; flex-direction: column; align-items: flex-end; gap: 4px; flex-shrink: 0; }
.pci-total { font-size: .9rem; font-weight: 800; color: var(--pos-text); }
.pci-actions { display: flex; gap: 3px; }
.pci-action-btn {
    width: 26px; height: 26px; border-radius: 6px; border: none;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    font-size: .75rem; transition: all .15s;
}
.pci-action-btn--disc { background: var(--pos-warn-l); color: var(--pos-warn); }
.pci-action-btn--disc:hover { background: var(--pos-warn); color: #fff; transform: rotate(15deg); }
.pci-action-btn--del { background: var(--pos-danger-l); color: var(--pos-danger); }
.pci-action-btn--del:hover { background: var(--pos-danger); color: #fff; }

/* Discount row */
.pos-discount-row {
    display: flex; align-items: center; justify-content: space-between;
    padding: 8px 16px;
    flex-shrink: 0;
    border-bottom: 1px solid var(--pos-border);
}
.pos-discount-label { font-size: .82rem; font-weight: 600; color: var(--pos-text-2); }
.pos-discount-controls { display: flex; align-items: center; gap: 4px; }
.pos-apply-disc {
    width: 32px; height: 32px; border-radius: 8px; border: none;
    background: var(--pos-primary); color: #fff; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: .9rem; transition: background .15s;
}
.pos-apply-disc:hover { background: var(--pos-primary-d); }

/* Totals */
.pos-totals { padding: 8px 16px; flex-shrink: 0; border-bottom: 1px solid var(--pos-border); }
.pos-totals__row {
    display: flex; justify-content: space-between; align-items: center;
    padding: 3px 0; font-size: .83rem; color: var(--pos-text-2);
}
.pos-totals__row--disc .pos-totals__disc { color: var(--pos-danger); font-weight: 700; }
.pos-totals__grand {
    display: flex; justify-content: space-between; align-items: center;
    margin-top: 6px; padding-top: 8px; border-top: 2px solid var(--pos-border);
    font-size: 1.25rem; font-weight: 900; color: var(--pos-success);
}

/* Payment */
.pos-payment-row {
    display: flex; gap: 8px; padding: 10px 16px;
    flex-shrink: 0; border-bottom: 1px solid var(--pos-border);
}
.pos-payment-opt { flex: 1; cursor: pointer; }
.pos-payment-opt input { display: none; }
.pos-payment-card {
    display: flex; flex-direction: column; align-items: center; gap: 4px;
    padding: 8px 4px; border-radius: var(--pos-radius-sm);
    border: 2px solid var(--pos-border); background: var(--pos-surface-2);
    font-size: .75rem; font-weight: 700; color: var(--pos-text-2);
    transition: all .15s; cursor: pointer;
}
.pos-payment-card i { font-size: 1.2rem; }
.pos-payment-opt input:checked + .pos-payment-card {
    border-color: var(--pos-primary); background: var(--pos-primary-l); color: var(--pos-primary);
    box-shadow: 0 2px 8px rgba(37,99,235,.2);
}
.pos-payment-opt:hover .pos-payment-card { border-color: var(--pos-primary); }

/* Complete button */
.pos-complete-btn {
    margin: 10px 16px 16px;
    height: 52px; border-radius: var(--pos-radius);
    border: none; background: linear-gradient(135deg, #16a34a 0%, #22c55e 100%);
    color: #fff; font-size: 1rem; font-weight: 800;
    cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
    transition: all .2s; letter-spacing: .02em;
    box-shadow: 0 4px 14px rgba(22,163,74,.4);
    flex-shrink: 0;
}
.pos-complete-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(22,163,74,.5); }
.pos-complete-btn:active { transform: scale(.98); }
.pos-complete-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; }

/* ════════════════════════════════════════════════════════════
   CART NOTIFICATION (RICH SLIDE-IN)
════════════════════════════════════════════════════════════ */
.cart-notif {
    position: fixed; top: calc(var(--topbar-h) + 12px); right: 380px;
    width: 300px; background: var(--pos-surface);
    border-radius: var(--pos-radius); box-shadow: var(--pos-shadow-lg);
    border: 1.5px solid var(--pos-border);
    overflow: hidden; z-index: 9999;
    display: none;
    transform: translateX(120%) scale(.9);
}
.cart-notif.is-visible {
    display: flex;
    animation: cartNotifIn .35s cubic-bezier(.34,1.56,.64,1) forwards;
}
.cart-notif.is-removing {
    animation: cartNotifOut .25s ease-in forwards;
}
@keyframes cartNotifIn {
    from { transform: translateX(120%) scale(.9); opacity: 0; }
    to   { transform: translateX(0)    scale(1);  opacity: 1; }
}
@keyframes cartNotifOut {
    from { transform: translateX(0)   scale(1);  opacity: 1; }
    to   { transform: translateX(120%) scale(.9); opacity: 0; }
}

.cart-notif__thumb {
    width: 64px; min-width: 64px; height: 100%;
    background: var(--pos-primary-l);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; color: var(--pos-primary);
    padding: 12px 0;
}
.cart-notif__thumb--img { padding: 0; background: #000; }
.cart-notif__thumb img { width: 64px; height: 100%; object-fit: cover; }
.cart-notif__body { flex: 1; padding: 10px 10px 18px; min-width: 0; }
.cart-notif__name { font-weight: 800; font-size: .88rem; color: var(--pos-text); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 3px; }
.cart-notif__meta {
    display: flex; align-items: center; gap: 5px; flex-wrap: wrap;
    font-size: .72rem; color: var(--pos-text-3); margin-bottom: 4px;
}
.cn-qty { background: var(--pos-primary-l); color: var(--pos-primary); border-radius: 4px; padding: 1px 5px; font-weight: 700; }
.cn-sep { color: var(--pos-border-2); }
.cn-sku { font-family: monospace; }
.cn-unit-icon { color: var(--pos-success); }
.cart-notif__price { font-size: 1.1rem; font-weight: 900; color: var(--pos-success); }
.cart-notif__close {
    position: absolute; top: 7px; right: 7px;
    width: 20px; height: 20px; border-radius: 50%;
    background: var(--pos-surface-2); border: 1px solid var(--pos-border);
    display: flex; align-items: center; justify-content: center;
    font-size: .75rem; cursor: pointer; color: var(--pos-text-3);
    transition: all .15s;
}
.cart-notif__close:hover { background: var(--pos-danger-l); color: var(--pos-danger); }
/* Progress bar */
.cart-notif__bar {
    position: absolute; bottom: 0; left: 0; height: 3px;
    background: linear-gradient(90deg, var(--pos-primary) 0%, var(--pos-success) 100%);
    width: 100%;
    animation: cartNotifBar 3.2s linear forwards;
}
@keyframes cartNotifBar { from { width: 100%; } to { width: 0%; } }
/* action tint */
.cart-notif[data-action="add"]    .cart-notif__thumb { background: var(--pos-primary-l); color: var(--pos-primary); }
.cart-notif[data-action="update"] .cart-notif__thumb { background: var(--pos-warn-l); color: var(--pos-warn); }

/* ════════════════════════════════════════════════════════════
   BUTTONS
════════════════════════════════════════════════════════════ */
.pos-btn {
    display: inline-flex; align-items: center; justify-content: center; gap: 6px;
    padding: 8px 16px; border-radius: var(--pos-radius-sm); border: none;
    font-family: var(--font-display); font-size: .85rem; font-weight: 700;
    cursor: pointer; transition: all .15s; text-decoration: none;
}
.pos-btn--primary { background: var(--pos-primary); color: #fff; }
.pos-btn--primary:hover { background: var(--pos-primary-d); transform: translateY(-1px); }
.pos-btn--primary-sm { background: var(--pos-primary); color: #fff; padding: 5px 12px; font-size: .78rem; border-radius: 6px; }
.pos-btn--ghost { background: var(--pos-surface-2); color: var(--pos-text-2); border: 1px solid var(--pos-border); }
.pos-btn--ghost:hover { background: var(--pos-border); }
.pos-btn--ghost-sm { background: transparent; color: var(--pos-text-3); border: 1px solid var(--pos-border); padding: 4px 8px; font-size: .75rem; border-radius: 6px; }
.pos-btn--ghost-sm:hover { background: var(--pos-danger-l); color: var(--pos-danger); border-color: var(--pos-danger); }
.pos-btn--ghost-danger { background: var(--pos-danger-l); color: var(--pos-danger); border: 1px solid transparent; }
.pos-btn--ghost-danger:hover { background: var(--pos-danger); color: #fff; }
.pos-btn--ghost-danger-sm { background: var(--pos-danger-l); color: var(--pos-danger); padding: 5px 10px; font-size: .78rem; border-radius: 6px; border: none; cursor: pointer; font-family: var(--font-display); font-weight: 700; display: inline-flex; align-items: center; transition: all .15s; }
.pos-btn--ghost-danger-sm:hover { background: var(--pos-danger); color: #fff; }
.pos-btn--warn { background: var(--pos-warn-l); color: var(--pos-warn); }
.pos-btn--warn:hover { background: var(--pos-warn); color: #fff; }
.pos-btn--cart-added { background: var(--pos-success-l); color: var(--pos-success); padding: 5px 12px; font-size: .78rem; border-radius: 6px; border: none; cursor: pointer; font-family: var(--font-display); font-weight: 700; display: inline-flex; align-items: center; gap: 5px; transition: all .15s; }
.pos-btn--cart-added:hover { background: var(--pos-success); color: #fff; }
.pos-btn--disabled { background: var(--pos-surface-2); color: var(--pos-text-3); cursor: not-allowed; padding: 5px 12px; font-size: .78rem; border-radius: 6px; border: none; font-family: var(--font-display); font-weight: 600; }
.pos-icon-btn {
    width: 34px; height: 34px; border-radius: var(--pos-radius-sm); border: 1.5px solid var(--pos-border);
    background: var(--pos-surface); color: var(--pos-text-2); cursor: pointer;
    display: flex; align-items: center; justify-content: center; font-size: .9rem;
    transition: all .15s; flex-shrink: 0;
}
.pos-icon-btn:hover { border-color: var(--pos-primary); color: var(--pos-primary); background: var(--pos-primary-l); }
.pos-icon-btn--accent { background: var(--pos-primary-l); border-color: var(--pos-primary); color: var(--pos-primary); }
.pos-pill-btn {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 10px; border-radius: 20px; border: none;
    font-size: .75rem; font-weight: 700; cursor: pointer;
    font-family: var(--font-display); transition: all .15s;
}
.pos-pill-btn--warn { background: var(--pos-warn-l); color: var(--pos-warn); }
.pos-pill-btn--warn:hover { background: var(--pos-warn); color: #fff; }
.pos-pill-btn--info { background: var(--pos-info-l); color: var(--pos-info); }
.pos-pill-btn--info:hover { background: var(--pos-info); color: #fff; }
.pos-pill-btn--danger { background: var(--pos-danger-l); color: var(--pos-danger); }
.pos-pill-btn--danger:hover { background: var(--pos-danger); color: #fff; }

/* ════════════════════════════════════════════════════════════
   INPUTS (shared)
════════════════════════════════════════════════════════════ */
.pos-input, .pos-select {
    width: 100%; padding: 8px 12px; border-radius: var(--pos-radius-sm);
    border: 1.5px solid var(--pos-border); font-family: var(--font-display);
    font-size: .88rem; color: var(--pos-text); background: var(--pos-surface-2);
    outline: none; transition: border-color .15s, box-shadow .15s;
}
.pos-input:focus, .pos-select:focus {
    border-color: var(--pos-primary); background: #fff;
    box-shadow: 0 0 0 3px rgba(37,99,235,.1);
}
.pos-input-sm {
    height: 32px; padding: 0 8px; border-radius: 6px;
    border: 1.5px solid var(--pos-border); font-family: var(--font-display);
    font-size: .82rem; color: var(--pos-text); background: var(--pos-surface-2);
    outline: none; transition: border-color .15s;
}
.pos-input-sm:focus { border-color: var(--pos-primary); background: #fff; }
.pos-input-sm--num { width: 70px; text-align: right; }
.pos-input-sm--sel { width: 56px; }

/* ════════════════════════════════════════════════════════════
   MODAL
════════════════════════════════════════════════════════════ */
.pos-modal { border: none; border-radius: 16px; overflow: hidden; box-shadow: var(--pos-shadow-lg); }
.pos-modal__header {
    display: flex; align-items: center; gap: 14px;
    padding: 16px 20px; background: var(--pos-surface);
    border-bottom: 1px solid var(--pos-border);
}
.pos-modal__header--warn { background: var(--pos-warn-l); }
.pos-modal__header h5 { margin: 0; font-size: 1rem; font-weight: 800; flex: 1; }
.pos-modal__close {
    width: 32px; height: 32px; border-radius: 8px; border: none;
    background: var(--pos-surface-2); color: var(--pos-text-2); cursor: pointer;
    display: flex; align-items: center; justify-content: center; font-size: .9rem;
    flex-shrink: 0; transition: all .15s;
}
.pos-modal__close:hover { background: var(--pos-danger-l); color: var(--pos-danger); }
.pos-modal__product-thumb {
    width: 56px; height: 56px; border-radius: 12px;
    background: var(--pos-primary-l); display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; color: var(--pos-primary); overflow: hidden; flex-shrink: 0;
}
.pos-modal__product-thumb img { width: 100%; height: 100%; object-fit: cover; }
.pos-modal__product-info { flex: 1; min-width: 0; }
.pos-modal__product-info h5 { margin: 0 0 4px; font-size: 1rem; font-weight: 800; }
.pos-modal__badges { display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 4px; }
.pos-badge {
    padding: 2px 8px; border-radius: 6px; font-size: .75rem; font-weight: 700;
}
.pos-badge--price { background: var(--pos-success-l); color: var(--pos-success); }
.pos-badge--stock { background: var(--pos-warn-l); color: var(--pos-warn); }
.pos-badge--unit  { background: var(--pos-primary-l); color: var(--pos-primary); }
.pos-modal__ids { display: flex; gap: 6px; flex-wrap: wrap; }
.pos-modal__body { padding: 16px 20px; max-height: 70vh; overflow-y: auto; }
.pos-modal__footer {
    display: flex; align-items: center; justify-content: flex-end; gap: 8px;
    padding: 12px 20px; background: var(--pos-surface-2);
    border-top: 1px solid var(--pos-border);
}

/* Mode toggle in modal */
.pos-mode-toggle {
    display: flex; border: 1.5px solid var(--pos-border); border-radius: var(--pos-radius-sm);
    overflow: hidden; margin-bottom: 14px;
}
.pos-mode-opt { flex: 1; cursor: pointer; }
.pos-mode-opt input { display: none; }
.pos-mode-opt span {
    display: flex; align-items: center; justify-content: center; gap: 5px;
    padding: 8px; font-size: .82rem; font-weight: 600; color: var(--pos-text-2);
    transition: all .15s; cursor: pointer;
}
.pos-mode-opt input:checked + span { background: var(--pos-primary); color: #fff; }
.pos-mode-opt:hover span { background: var(--pos-surface-2); }

/* Qty display in modal */
.pos-qty-display { text-align: center; margin-bottom: 12px; }
.pos-prev-qty { font-size: .75rem; color: var(--pos-text-3); margin-bottom: 4px; min-height: 16px; display: block; }
.pos-prev-qty--incart { color: var(--pos-success); font-weight: 600; }
.pos-qty-display__label { font-size: .85rem; font-weight: 700; color: var(--pos-text-2); margin-bottom: 10px; }
.pos-numpad-input-row { display: flex; align-items: center; gap: 8px; justify-content: center; }
.pos-stepper-btn {
    width: 44px; height: 56px; border-radius: 10px;
    border: 1.5px solid var(--pos-border); background: var(--pos-surface-2);
    color: var(--pos-text-2); font-size: 1.2rem; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    transition: all .15s;
}
.pos-stepper-btn:hover:not(:disabled) { background: var(--pos-primary-l); border-color: var(--pos-primary); color: var(--pos-primary); }
.pos-stepper-btn:disabled { opacity: .4; cursor: not-allowed; }
.pos-qty-input {
    width: 120px; height: 56px; text-align: center;
    font-size: 2rem; font-weight: 800; font-family: var(--font-display);
    border: 2px solid var(--pos-border); border-radius: 10px;
    color: var(--pos-text); background: var(--pos-surface);
    outline: none; transition: border-color .15s, box-shadow .15s;
}
.pos-qty-input:focus { border-color: var(--pos-primary); box-shadow: 0 0 0 3px rgba(37,99,235,.1); }
.pos-total-preview { font-size: 1rem; font-weight: 800; color: var(--pos-success); margin-top: 8px; }

/* Price input in modal */
.pos-price-input-wrap { margin-bottom: 12px; }
.pos-field-label { font-size: .8rem; font-weight: 700; color: var(--pos-text-2); margin-bottom: 5px; display: block; }
.pos-required { color: var(--pos-danger); }
.pos-price-input-row { display: flex; align-items: center; gap: 6px; }
.pos-price-symbol {
    display: flex; align-items: center; justify-content: center;
    width: 36px; height: 44px; background: var(--pos-primary); color: #fff;
    border-radius: 8px 0 0 8px; font-weight: 800; flex-shrink: 0;
}
.pos-price-input {
    flex: 1; height: 44px; padding: 0 12px;
    border: 1.5px solid var(--pos-border); border-left: none; border-radius: 0 8px 8px 0;
    font-family: var(--font-display); font-size: 1.1rem; font-weight: 700;
    text-align: center; color: var(--pos-text); background: var(--pos-surface);
    outline: none; transition: border-color .15s;
}
.pos-price-input:focus { border-color: var(--pos-primary); }
.pos-price-input:disabled { background: var(--pos-surface-2); opacity: .6; }
.pos-price-hint { font-size: .72rem; color: var(--pos-text-3); display: block; margin-top: 4px; }
.pos-calc-price { font-size: .8rem; font-weight: 700; color: var(--pos-success); display: block; margin-top: 4px; background: var(--pos-success-l); padding: 3px 8px; border-radius: 6px; }

/* Quick buttons */
.pos-quick-btns { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 8px; }
.pos-qb {
    flex: 1; min-width: 56px; height: 40px; border-radius: 8px;
    border: 1.5px solid var(--pos-border); background: var(--pos-surface-2);
    color: var(--pos-text-2); font-size: .85rem; font-weight: 700;
    cursor: pointer; transition: all .15s; font-family: var(--font-display);
}
.pos-qb:hover { border-color: var(--pos-primary); background: var(--pos-primary-l); color: var(--pos-primary); }
.pos-qb--unit { border-color: var(--pos-success); color: var(--pos-success); background: var(--pos-success-l); }
.pos-qb--unit:hover { background: var(--pos-success); color: #fff; }

/* Unit select wrap */
.pos-unit-select-wrap { margin-bottom: 12px; }
.pos-check-label { display: flex; align-items: center; gap: 6px; font-size: .78rem; color: var(--pos-text-2); margin-top: 6px; cursor: pointer; }

/* Held orders in modal */
.pos-held-order {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 10px 0; border-bottom: 1px solid var(--pos-border);
}
.pos-held-order:last-child { border-bottom: none; }
.pho-info { flex: 1; }
.pho-time { font-size: .85rem; font-weight: 700; color: var(--pos-text); }
.pho-meta { font-size: .75rem; color: var(--pos-text-3); margin-top: 2px; }
.pho-actions { display: flex; gap: 5px; }

/* Customer quick form */
.pos-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.pos-field { display: flex; flex-direction: column; gap: 5px; }
.pos-field label { font-size: .78rem; font-weight: 700; color: var(--pos-text-2); }
.pos-field--full { grid-column: 1 / -1; }

/* Discount item name */
.pos-disc-item-name { font-size: .9rem; font-weight: 700; color: var(--pos-text); text-align: center; margin-bottom: 12px; }

/* Alert */
.pos-alert { padding: 10px 14px; border-radius: 8px; font-size: .83rem; margin-bottom: 12px; }
.pos-alert--info { background: var(--pos-info-l); color: var(--pos-info); }

/* Spinners */
.pos-spinner {
    width: 36px; height: 36px; border-radius: 50%;
    border: 3px solid var(--pos-border); border-top-color: var(--pos-primary);
    animation: spin .7s linear infinite;
}
.pos-spinner-sm {
    width: 20px; height: 20px; border-radius: 50%;
    border: 2px solid var(--pos-border-2); border-top-color: var(--pos-primary);
    animation: spin .6s linear infinite; display: inline-flex;
}
.pos-spinner-sm-inline {
    display: inline-block; width: 16px; height: 16px; border-radius: 50%;
    border: 2px solid rgba(255,255,255,.3); border-top-color: #fff;
    animation: spin .6s linear infinite; vertical-align: middle;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ════════════════════════════════════════════════════════════
   SCROLLBARS
════════════════════════════════════════════════════════════ */
.pos-grid-panel::-webkit-scrollbar,
.pos-list-panel::-webkit-scrollbar,
.pos-modal__body::-webkit-scrollbar { width: 5px; }
.pos-grid-panel::-webkit-scrollbar-track,
.pos-list-panel::-webkit-scrollbar-track,
.pos-modal__body::-webkit-scrollbar-track { background: transparent; }
.pos-grid-panel::-webkit-scrollbar-thumb,
.pos-list-panel::-webkit-scrollbar-thumb,
.pos-modal__body::-webkit-scrollbar-thumb { background: var(--pos-border-2); border-radius: 3px; }

/* ════════════════════════════════════════════════════════════
   RESPONSIVE
════════════════════════════════════════════════════════════ */
@media (max-width: 1100px) {
    .pos-body { grid-template-columns: 1fr 320px; }
    .pos-grid { grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); }
}

@media (max-width: 768px) {
    .pos-body { grid-template-columns: 1fr; grid-template-rows: 1fr 1fr; }
    .pos-topbar__breadcrumb, .pos-topbar__shortcuts { display: none; }
    .pos-grid { grid-template-columns: repeat(3, 1fr); }
    .cart-notif { right: 12px; width: 260px; top: calc(var(--topbar-h) + 8px); }
    .pgc-thumb { height: 80px; }
    .pos-grid-card { min-height: 150px; }
}

@media (max-width: 480px) {
    .pos-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
    .pos-shell { height: auto; }
    .pos-body { overflow: visible; }
    .pos-products-panel, .pos-order-panel { height: auto; overflow: visible; }
    .pos-grid-panel { height: 400px; overflow-y: scroll; }
    .pos-cart-list { height: 260px; }
}

@media print {
    .pos-shell { display: none; }
}
</style>
@endsection
