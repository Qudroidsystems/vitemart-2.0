@extends('layouts.master')
@section('title', $pagetitle ?? 'POS Grid')
@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">
                            <i class="bi bi-grid-3x3-gap-fill me-2 text-primary"></i>Grid POS
                        </h4>
                        <div class="page-title-right d-flex align-items-center gap-3">
                            <a href="{{ route('pos.index') }}" class="btn btn-sm btn-outline-secondary">
                                <i class="bi bi-layout-text-sidebar-reverse me-1"></i>Standard POS
                            </a>
                            <span id="connectionStatus" class="badge bg-success">
                                <i class="bi bi-wifi"></i> Online
                            </span>
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="offlineModeToggle">
                                <label class="form-check-label small" for="offlineModeToggle">Offline</label>
                            </div>
                            <div class="pos-clock" id="posClock">--:--</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-0 pos-grid-body">

                <!-- LEFT: Products Panel -->
                <div class="col-xxl-8 col-xl-8 col-lg-7 pos-products-col">

                    <!-- Search Bar -->
                    <div class="card mb-2 border-0 shadow-sm">
                        <div class="card-body py-2 px-3">
                            <div class="pos-search-wrap position-relative">
                                <input type="text" id="barcodeInput"
                                       class="form-control form-control-lg fs-5 pos-search-input"
                                       placeholder="Scan barcode or search by name / SKU…"
                                       autofocus autocomplete="off"
                                       aria-label="Search or scan products">
                                <i class="bi bi-upc-scan pos-search-icon"></i>
                                <span id="searchSpinner" class="pos-search-spinner d-none">
                                    <span class="spinner-border spinner-border-sm text-primary"></span>
                                </span>
                            </div>
                            <div class="pos-shortcuts-row mt-1">
                                <small class="text-muted me-1">Shortcuts:</small>
                                <span class="badge bg-secondary">F1</span> Search &nbsp;
                                <span class="badge bg-secondary">F2</span> Clear &nbsp;
                                <span class="badge bg-secondary">F3</span> Charge &nbsp;
                                <span class="badge bg-secondary">F4</span> Hold &nbsp;
                                <span class="badge bg-secondary">F6</span> Standard POS
                            </div>
                        </div>
                    </div>

                    <!-- Category Pills + Grid -->
                    <div class="card border-0 shadow-sm pos-products-card">
                        <div class="card-body p-2 d-flex flex-column pos-products-card-body">

                            <!-- Category pills -->
                            <div class="pos-cat-bar mb-2" id="catBar">
                                <button class="pos-cat-pill active" data-cat="all">
                                    <i class="bi bi-grid-fill me-1"></i>All
                                </button>
                            </div>

                            <!-- Loading overlay -->
                            <div id="gridLoadingOverlay" class="pos-grid-loading">
                                <div class="spinner-border text-primary" style="width:3rem;height:3rem;" role="status"></div>
                                <p class="mt-3 text-primary fw-semibold">Loading products…</p>
                            </div>

                            <!-- Product grid -->
                            <div class="pos-product-grid" id="productGrid"></div>

                            <!-- Empty state -->
                            <div id="emptyState" class="pos-empty-state d-none">
                                <i class="bi bi-search"></i>
                                <h5>No products found</h5>
                                <p>Try a different search or category</p>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- RIGHT: Order Panel -->
                <div class="col-xxl-4 col-xl-4 col-lg-5 pos-order-col">
                    <div class="card border-0 shadow-sm h-100 pos-order-card">
                        <div class="card-body d-flex flex-column p-0 pos-order-body">

                            <!-- Customer -->
                            <div class="pos-customer-row px-3 pt-3 pb-2 border-bottom">
                                <div class="pos-customer-select-wrap">
                                    <i class="bi bi-person-circle pos-customer-icon"></i>
                                    <select id="customerSelect" class="pos-customer-select">
                                        <option value="">Walk-in Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">
                                                {{ $customer->first_name }} {{ $customer->last_name }}
                                                @if($customer->phone_number) · {{ $customer->phone_number }}@endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button class="pos-icon-btn" id="quickCustomerBtn" title="Add Customer"><i class="bi bi-person-plus-fill"></i></button>
                                <button class="pos-icon-btn warning" id="holdOrderBtn" title="Hold (F4)"><i class="bi bi-pause-circle-fill"></i></button>
                                <button class="pos-icon-btn info" id="loadHeldBtn" title="Load Held"><i class="bi bi-folder-symlink-fill"></i></button>
                                <button class="pos-icon-btn danger" id="clearCart" title="Clear (F2)"><i class="bi bi-trash3-fill"></i></button>
                            </div>

                            <!-- Cart -->
                            <div class="pos-cart-area px-0" id="cartArea">
                                <div id="emptyCartState" class="pos-cart-empty">
                                    <div class="pos-cart-empty-icon"><i class="bi bi-cart4"></i></div>
                                    <p>Cart is empty</p>
                                    <small>Tap a product to add it</small>
                                </div>
                                <div id="cartItemsWrap" class="d-none">
                                    <table class="pos-cart-table">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-end">Price</th>
                                                <th class="text-end">Total</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="cartBody"></tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Discount -->
                            <div class="pos-discount-row px-3 py-2 border-top">
                                <span class="pos-discount-label">Discount</span>
                                <div class="pos-discount-inputs">
                                    <input type="number" id="discountValue" class="pos-discount-input" placeholder="0" min="0" step="0.01" value="0">
                                    <select id="discountType" class="pos-discount-select">
                                        <option value="percent" selected>%</option>
                                        <option value="fixed">₦</option>
                                    </select>
                                    <button class="pos-discount-apply" id="applyDiscountBtn" title="Apply"><i class="bi bi-check-lg"></i></button>
                                </div>
                                <div id="discountApplied" class="pos-discount-applied d-none">
                                    <i class="bi bi-tag-fill me-1"></i><span id="discountAmountLabel">-₦0.00</span>
                                </div>
                            </div>

                            <!-- Totals -->
                            <div class="pos-totals px-3 py-2 border-top">
                                <div class="pos-total-row"><span>Subtotal</span><span id="subtotal">₦0.00</span></div>
                                <div class="pos-total-row"><span>Tax ({{ config('pos.tax_rate', 0) }}%)</span><span id="taxAmount">₦0.00</span></div>
                                <div class="pos-total-row grand"><span>Total</span><span id="grandTotal">₦0.00</span></div>
                            </div>

                            <!-- Payment -->
                            <div class="pos-payment-group px-3 py-2 border-top">
                                <label class="pos-payment-opt">
                                    <input type="radio" name="payment" value="cash" checked>
                                    <span><i class="bi bi-cash-coin"></i> Cash</span>
                                </label>
                                <label class="pos-payment-opt">
                                    <input type="radio" name="payment" value="card">
                                    <span><i class="bi bi-credit-card-fill"></i> Card</span>
                                </label>
                                <label class="pos-payment-opt">
                                    <input type="radio" name="payment" value="transfer">
                                    <span><i class="bi bi-bank2"></i> Transfer</span>
                                </label>
                            </div>

                            <!-- Charge Button -->
                            <div class="px-3 pb-3 pt-2">
                                <button class="pos-charge-btn w-100" id="completeOrder">
                                    <i class="bi bi-printer-fill me-2"></i>
                                    <span>Charge</span>
                                    <span class="pos-charge-total" id="chargeBtnTotal">₦0.00</span>
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QUANTITY MODAL -->
<div class="modal fade" id="quantityModal" tabindex="-1" aria-labelledby="quantityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content pos-modal-content border-0 shadow-xl">
            <div class="modal-header pos-modal-header border-0">
                <div class="d-flex align-items-center gap-3 flex-grow-1">
                    <div class="pos-modal-product-thumb" id="modalThumb">
                        <i class="bi bi-box-seam"></i>
                    </div>
                    <div>
                        <h5 class="modal-title mb-0 fw-bold" id="modalProductLabel">Product</h5>
                        <div class="d-flex gap-2 mt-1 flex-wrap">
                            <span class="badge bg-success" id="modalProductPrice"></span>
                            <span class="badge bg-warning text-dark" id="modalProductStock"></span>
                            <span class="badge bg-secondary" id="modalProductUnit"></span>
                        </div>
                        <div class="mt-1">
                            <small class="text-muted d-flex gap-2" id="modalProductMeta"></small>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body px-4 pb-0">
                <!-- Measurement Toggle -->
                <div class="pos-measure-toggle mb-3">
                    <label class="pos-measure-opt">
                        <input type="radio" name="measurementType" id="measureQuantity" value="quantity" checked>
                        <span><i class="bi bi-123 me-1"></i>Quantity</span>
                    </label>
                    <label class="pos-measure-opt">
                        <input type="radio" name="measurementType" id="measureUnit" value="unit">
                        <span><i class="bi bi-scale me-1"></i>By Unit</span>
                    </label>
                </div>

                <!-- Unit Selection -->
                <div id="unitSelectionWrap" class="mb-3 d-none">
                    <label class="form-label small fw-semibold text-muted">Select Unit</label>
                    <select class="form-select form-select-sm" id="unitSelect">
                        <option value="">-- Select unit --</option>
                    </select>
                    <div class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" id="rememberUnitPreference">
                        <label class="form-check-label small" for="rememberUnitPreference">Remember for this product</label>
                    </div>
                </div>

                <!-- Price Input (unit mode) -->
                <div id="priceInputSection" class="pos-price-box mb-3 d-none">
                    <label class="form-label small fw-semibold mb-1">
                        Total Price
                        <small class="text-muted fw-normal ms-2" id="originalPriceText"></small>
                    </label>
                    <div class="input-group input-group-lg">
                        <span class="input-group-text fw-bold bg-primary text-white">₦</span>
                        <input type="number" id="pricePerUnit" class="form-control text-center fw-bold fs-4"
                               min="0" step="0.01" placeholder="Enter price" disabled>
                    </div>
                    <small class="text-muted d-block mt-1">
                        For <span id="amountDisplay">1.000</span> <span id="unitDisplay">unit</span>
                        <span class="text-success fw-bold ms-2" id="calculatedPriceDisplay"></span>
                    </small>
                </div>

                <!-- Quantity Input -->
                <div id="quantityInputSection">
                    <label class="form-label small fw-semibold text-muted mb-1" id="measurementLabel">
                        Quantity
                        <small class="ms-2" id="previousQtyText"></small>
                    </label>
                    <div class="pos-qty-stepper">
                        <button type="button" id="decreaseQty" class="pos-step-btn"><i class="bi bi-dash-lg"></i></button>
                        <input type="number" id="modalQty" class="pos-qty-input" min="1" value="1" step="1" autofocus>
                        <button type="button" id="increaseQty" class="pos-step-btn"><i class="bi bi-plus-lg"></i></button>
                    </div>
                </div>

                <!-- Unit Input -->
                <div id="unitInputSection" class="d-none">
                    <label class="form-label small fw-semibold text-muted mb-1">Amount</label>
                    <div class="pos-qty-stepper">
                        <button type="button" id="decreaseUnit" class="pos-step-btn" disabled><i class="bi bi-dash-lg"></i></button>
                        <input type="number" id="modalUnit" class="pos-qty-input" min="0.001" value="1" step="0.001" disabled>
                        <button type="button" id="increaseUnit" class="pos-step-btn" disabled><i class="bi bi-plus-lg"></i></button>
                    </div>
                </div>

                <div class="text-center mt-2 mb-3">
                    <span class="fw-bold text-success fs-5" id="totalPriceDisplay">Total: ₦0.00</span>
                </div>

                <!-- Quick Buttons -->
                <div id="quantityQuickButtons" class="pos-quick-btns mb-3">
                    <button class="pos-quick-btn quick-btn" data-value="1">1</button>
                    <button class="pos-quick-btn quick-btn" data-value="2">2</button>
                    <button class="pos-quick-btn quick-btn" data-value="3">3</button>
                    <button class="pos-quick-btn quick-btn" data-value="5">5</button>
                    <button class="pos-quick-btn quick-btn" data-value="10">10</button>
                    <button class="pos-quick-btn quick-btn" data-value="20">20</button>
                    <button class="pos-quick-btn quick-btn" data-value="50">50</button>
                    <button class="pos-quick-btn quick-btn" data-value="100">100</button>
                </div>
                <div id="unitQuickButtons" class="pos-quick-btns mb-3 d-none">
                    <button class="pos-quick-btn unit-quick-btn" data-value="0.25">¼</button>
                    <button class="pos-quick-btn unit-quick-btn" data-value="0.5">½</button>
                    <button class="pos-quick-btn unit-quick-btn" data-value="0.75">¾</button>
                    <button class="pos-quick-btn unit-quick-btn" data-value="1">1</button>
                    <button class="pos-quick-btn unit-quick-btn" data-value="2.5">2.5</button>
                    <button class="pos-quick-btn unit-quick-btn" data-value="5">5</button>
                    <button class="pos-quick-btn unit-quick-btn" data-value="10">10</button>
                    <button class="pos-quick-btn unit-quick-btn" data-value="25">25</button>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light rounded-bottom gap-2 px-4 pb-4 pt-3">
                <button type="button" class="btn btn-outline-danger px-3" id="removeFromCartBtn" style="display:none;">
                    <i class="bi bi-trash me-1"></i>Remove
                </button>
                <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-primary px-4 ms-auto" id="confirmAddBtn">
                    <i class="bi bi-cart-plus me-1"></i>Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Per-Item Discount Modal -->
<div class="modal fade" id="itemDiscountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h6 class="modal-title text-dark"><i class="bi bi-percent me-2"></i>Item Discount</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="fw-semibold text-center mb-3" id="itemName"></p>
                <div class="input-group">
                    <input type="number" id="itemDiscountValue" class="form-control" placeholder="0" min="0" step="0.01">
                    <select id="itemDiscountType" class="form-select" style="max-width:80px;">
                        <option value="percent" selected>%</option>
                        <option value="fixed">₦</option>
                    </select>
                </div>
                <small class="text-muted">Set 0 to remove discount</small>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-warning btn-sm" id="applyItemDiscountBtn">Apply</button>
            </div>
        </div>
    </div>
</div>

<!-- Quick Customer Modal -->
<div class="modal fade" id="quickCustomerModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Quick Add Customer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-6"><label class="form-label small">First Name *</label><input type="text" class="form-control" id="firstName" required></div>
                    <div class="col-6"><label class="form-label small">Last Name *</label><input type="text" class="form-control" id="lastName" required></div>
                    <div class="col-12"><label class="form-label small">Phone</label><input type="tel" class="form-control" id="phoneNumber"></div>
                    <div class="col-12"><label class="form-label small">Email</label><input type="email" class="form-control" id="email"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="saveQuickCustomerBtn">Save Customer</button>
            </div>
        </div>
    </div>
</div>

<!-- Load Order Modal -->
<div class="modal fade" id="loadOrderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Load Held Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="heldOrdersList"></div>
                <div id="noHeldOrders" class="text-center py-4 text-muted">
                    <i class="bi bi-inbox fs-1 mb-2 d-block"></i>No held orders
                </div>
            </div>
        </div>
    </div>
</div>

<div class="visually-hidden" role="status" aria-live="polite" id="cartStatus"></div>

<script src="{{ asset('theme/layouts/assets/libs/axios/axios.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── PRODUCT REGISTRY ─────────────────────────────────────
    const productRegistry = new Map();
    function registerProduct(p) { productRegistry.set(String(p.id), p); }
    function getProduct(id)     { return productRegistry.get(String(id)); }

    // ── SEARCH CACHE + ABORT ─────────────────────────────────
    const searchCache = new Map();   // query → array of products
    let   searchAbort = null;        // current AbortController

    // ── CLOCK ────────────────────────────────────────────────
    (function tickClock() {
        const el = document.getElementById('posClock');
        if (el) el.textContent = new Date().toLocaleTimeString('en-NG', { hour:'2-digit', minute:'2-digit' });
        setTimeout(tickClock, 30000);
    })();

    // ── DOM REFS ─────────────────────────────────────────────
    const input              = document.getElementById('barcodeInput');
    const productGrid        = document.getElementById('productGrid');
    const emptyState         = document.getElementById('emptyState');
    const gridLoadingOverlay = document.getElementById('gridLoadingOverlay');
    const cartBody           = document.getElementById('cartBody');
    const emptyCartState     = document.getElementById('emptyCartState');
    const cartItemsWrap      = document.getElementById('cartItemsWrap');
    const subtotalEl         = document.getElementById('subtotal');
    const taxAmountEl        = document.getElementById('taxAmount');
    const grandTotalEl       = document.getElementById('grandTotal');
    const chargeBtnTotal     = document.getElementById('chargeBtnTotal');
    const discountValueEl    = document.getElementById('discountValue');
    const discountTypeEl     = document.getElementById('discountType');
    const modalQty           = document.getElementById('modalQty');
    const modalUnit          = document.getElementById('modalUnit');
    const confirmAddBtn      = document.getElementById('confirmAddBtn');
    const removeFromCartBtn  = document.getElementById('removeFromCartBtn');
    const customerSelect     = document.getElementById('customerSelect');
    const pricePerUnitInput  = document.getElementById('pricePerUnit');
    const unitSelect         = document.getElementById('unitSelect');
    const catBar             = document.getElementById('catBar');

    // ── STATE ────────────────────────────────────────────────
    let cart                   = [];
    let currentItemIndex       = null;
    let orderDiscountType      = 'percent';
    let orderDiscountValue     = 0;
    let isProcessingOrder      = false;
    let quantityModal          = null;
    let quickCustomerModal     = null;
    let thankYouAudio          = null;
    let activeCategoryFilter   = 'all';
    let availableUnits         = [];
    let selectedUnit           = null;
    let currentProduct         = null;
    let currentMeasurementType = 'quantity';
    let originalPricePerUnit   = 0;
    let isPriceInputActive     = false;
    let productQuantityCache   = {};

    input.focus();

    // ── BOOT ─────────────────────────────────────────────────
    function init() {
        quantityModal      = new bootstrap.Modal(document.getElementById('quantityModal'));
        quickCustomerModal = new bootstrap.Modal(document.getElementById('quickCustomerModal'));
        thankYouAudio      = new Audio('/audio/thank-you-sweet-man-235977.mp3');
        thankYouAudio.preload = 'auto';

        setupQuantityModal();
        setupItemDiscountModal();
        initQuickCustomer();
        setupOfflineMode();
        setupKeyboardShortcuts();

        document.addEventListener('click', function (e) {
            if (!e.target.closest('.modal') &&
                !e.target.closest('#customerSelect') &&
                !e.target.closest('#discountValue') &&
                !e.target.closest('#discountType') &&
                !e.target.closest('.pos-prod-card') &&
                !e.target.closest('.pos-cat-bar') &&
                e.target.id !== 'barcodeInput') {
                input.focus();
            }
        });

        loadInitialProducts();
        renderCartAndTotals();
    }

    // ── LOAD INITIAL PRODUCTS ─────────────────────────────────
    async function loadInitialProducts() {
        gridLoadingOverlay.style.display = 'flex';
        try {
            const res = await axios.get('{{ route("pos.initial-products") }}');
            const products = res.data.products || [];
            products.forEach(p => registerProduct(p));
        } catch (e) {
            // silently continue — registry stays empty, search still works
        } finally {
            gridLoadingOverlay.style.display = 'none';
            buildCategoryPills();
            renderGrid();
        }
    }

    // ── BARCODE / SEARCH INPUT ────────────────────────────────
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const val = input.value.trim();
            if (val) processBarcode(val);
        }
    });

    input.addEventListener('input', debounce(function () {
        const q = input.value.trim();
        if (q.length >= 2 && !isLikelyBarcode(q)) {
            searchProducts(q);
        } else if (q.length === 0) {
            // Cancel any in-flight search
            if (searchAbort) { searchAbort.abort(); searchAbort = null; }
            buildCategoryPills();
            renderGrid();
        }
    }, 400));

    async function processBarcode(barcode) {
        input.value = '';
        showSpinner(true);
        try {
            const res = await axios.get('{{ route("pos.search") }}', { params: { q: barcode } });
            const products = res.data || [];
            if (products.length > 0) {
                registerProduct(products[0]);
                renderGrid();
                openQuantityModal(String(products[0].id));
                playScanSound();
            } else {
                showToast('Product not found', 'error');
            }
        } catch (e) {
            showToast('Scan error', 'error');
        } finally {
            showSpinner(false);
        }
    }

    function isLikelyBarcode(v) {
        return (v.length >= 8 && v.length <= 14 && /^\d+$/.test(v)) ||
               (v.startsWith('PROD') && v.length > 10);
    }

    function playScanSound() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator(), g = ctx.createGain();
            osc.connect(g); g.connect(ctx.destination);
            osc.frequency.value = 800; osc.type = 'sine';
            g.gain.setValueAtTime(0.3, ctx.currentTime);
            g.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.1);
            osc.start(ctx.currentTime); osc.stop(ctx.currentTime + 0.1);
        } catch (e) {}
    }

    // ── SEARCH (with cache + abort) ───────────────────────────
    async function searchProducts(q) {
        // Abort previous request
        if (searchAbort) { searchAbort.abort(); }
        searchAbort = new AbortController();

        // Serve from cache instantly — no spinner
        if (searchCache.has(q)) {
            const cached = searchCache.get(q);
            cached.forEach(p => registerProduct(p));
            buildCategoryPills();
            renderGrid(cached.map(p => String(p.id)));
            searchAbort = null;
            return;
        }

        showSpinner(true);
        try {
            const res = await axios.get('{{ route("pos.search") }}', {
                params: { q },
                signal: searchAbort.signal,
            });
            const results = res.data || [];

            // Store in cache (cap at 200 entries)
            searchCache.set(q, results);
            if (searchCache.size > 200) {
                searchCache.delete(searchCache.keys().next().value);
            }

            results.forEach(p => registerProduct(p));
            buildCategoryPills();
            renderGrid(results.map(p => String(p.id)));
        } catch (e) {
            if (!axios.isCancel(e)) showToast('Search failed', 'error');
        } finally {
            showSpinner(false);
            searchAbort = null;
        }
    }

    // ── CATEGORY PILLS ────────────────────────────────────────
    function buildCategoryPills() {
        const allProds = Array.from(productRegistry.values());
        const cats = [...new Set(allProds.map(p => p.category || '').filter(Boolean))];

        catBar.querySelectorAll('.pos-cat-pill:not([data-cat="all"])').forEach(el => el.remove());

        cats.forEach(cat => {
            const btn = document.createElement('button');
            btn.className = 'pos-cat-pill' + (cat === activeCategoryFilter ? ' active' : '');
            btn.dataset.cat = cat;
            btn.textContent = cat.charAt(0).toUpperCase() + cat.slice(1);
            catBar.appendChild(btn);
        });

        catBar.querySelectorAll('.pos-cat-pill').forEach(pill => {
            pill.classList.toggle('active', pill.dataset.cat === activeCategoryFilter);
            pill.onclick = () => {
                activeCategoryFilter = pill.dataset.cat;
                catBar.querySelectorAll('.pos-cat-pill').forEach(p => p.classList.remove('active'));
                pill.classList.add('active');
                renderGrid();
            };
        });
    }

    // ── RENDER GRID (diff-based — no full rebuild) ────────────
    function renderGrid(limitToIds = null) {
        let products = Array.from(productRegistry.values());

        if (limitToIds !== null) {
            products = products.filter(p => limitToIds.includes(String(p.id)));
        }
        if (activeCategoryFilter !== 'all') {
            products = products.filter(p => (p.category || '') === activeCategoryFilter);
        }

        products.sort((a, b) => {
            const aCart = cart.some(i => String(i.product_id) === String(a.id));
            const bCart = cart.some(i => String(i.product_id) === String(b.id));
            if (aCart && !bCart) return -1;
            if (!aCart && bCart) return 1;
            return (a.title || '').localeCompare(b.title || '');
        });

        if (products.length === 0) {
            emptyState.classList.remove('d-none');
            productGrid.innerHTML = '';
            return;
        }
        emptyState.classList.add('d-none');

        // Remove cards no longer in the filtered list
        const currentIds = new Set(products.map(p => String(p.id)));
        productGrid.querySelectorAll('.pos-prod-card').forEach(card => {
            if (!currentIds.has(card.dataset.productId)) card.remove();
        });

        // Update existing or insert new cards
        products.forEach(p => {
            const pid      = String(p.id);
            const cartItem = cart.find(i => String(i.product_id) === pid);
            const inCart   = !!cartItem;
            const existing = productGrid.querySelector(`[data-product-id="${pid}"]`);

            if (existing) {
                // Patch only the in-cart state — avoid full re-render
                existing.classList.toggle('in-cart', inCart);

                const badge = existing.querySelector('.prod-cart-badge');
                if (inCart && !badge) {
                    const b = document.createElement('div');
                    b.className = 'prod-cart-badge';
                    b.innerHTML = `<i class="bi bi-check2 me-1"></i>${formatQty(cartItem.qty, cartItem.is_unit_mode)}`;
                    existing.prepend(b);
                } else if (!inCart && badge) {
                    badge.remove();
                } else if (inCart && badge) {
                    badge.innerHTML = `<i class="bi bi-check2 me-1"></i>${formatQty(cartItem.qty, cartItem.is_unit_mode)}`;
                }

                const bar = existing.querySelector('.prod-in-cart-bar');
                if (inCart && !bar) {
                    const b = document.createElement('div');
                    b.className = 'prod-in-cart-bar';
                    b.textContent = 'In Cart';
                    existing.appendChild(b);
                } else if (!inCart && bar) {
                    bar.remove();
                }

                // Re-order in DOM to match sorted position
                productGrid.appendChild(existing);
                return;
            }

            // Build new card
            const price      = parseFloat(p.sale_price || p.price) || 0;
            const outOfStock = parseFloat(p.stock) <= 0;
            const stockClass = parseFloat(p.stock) > 10 ? 'good' : parseFloat(p.stock) > 0 ? 'low' : 'out';
            const savedPref  = getSavedUnitPref(p.id);

            const card = document.createElement('div');
            card.className = `pos-prod-card${inCart ? ' in-cart' : ''}${outOfStock ? ' out-of-stock' : ''}`;
            card.dataset.productId = pid;
            card.setAttribute('role', 'button');
            card.setAttribute('tabindex', outOfStock ? '-1' : '0');

            card.innerHTML = `
                ${inCart ? `<div class="prod-cart-badge"><i class="bi bi-check2 me-1"></i>${formatQty(cartItem.qty, cartItem.is_unit_mode)}</div>` : ''}
                <div class="prod-stock-pip ${stockClass}" title="Stock: ${formatNum(p.stock)}"></div>
                <div class="prod-img-wrap">
                    ${p.thumbnail
                        ? `<img src="${escHtml(p.thumbnail)}" alt="${escHtml(p.title)}" loading="lazy">`
                        : `<div class="prod-img-placeholder"><i class="bi bi-box-seam"></i></div>`}
                    ${outOfStock ? `<div class="prod-out-overlay"><span>Out of Stock</span></div>` : ''}
                </div>
                <div class="prod-info">
                    <div class="prod-name" title="${escHtml(p.title)}">${escHtml(p.title)}</div>
                    <div class="prod-price">${formatCurrency(price)}</div>
                    <div class="prod-meta">${escHtml(p.primary_unit || 'Unit')} · ${escHtml(p.sku || '')}</div>
                    ${savedPref ? `<div class="prod-saved-unit"><i class="bi bi-star-fill text-warning"></i> ${escHtml(savedPref.shortName)}</div>` : ''}
                </div>
                ${inCart ? `<div class="prod-in-cart-bar">In Cart</div>` : ''}
            `;

            if (!outOfStock) {
                card.addEventListener('click', () => openQuantityModal(pid));
                card.addEventListener('keydown', e => {
                    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); openQuantityModal(pid); }
                });
            }

            card.addEventListener('contextmenu', e => {
                e.preventDefault();
                if (inCart) {
                    cart = cart.filter(i => String(i.product_id) !== pid);
                    delete productQuantityCache[p.id];
                    renderCartAndTotals();
                    renderGrid(limitToIds);
                    showToast('Removed from cart', 'info');
                }
            });

            productGrid.appendChild(card);
        });
    }

    // ── OPEN QUANTITY MODAL ───────────────────────────────────
    function openQuantityModal(productId) {
        const p = getProduct(productId);
        if (!p) { showToast('Product data not found', 'error'); return; }

        currentProduct         = p;
        availableUnits         = [];
        selectedUnit           = null;
        isPriceInputActive     = false;

        const price    = parseFloat(p.sale_price || p.price) || 0;
        const cartItem = cart.find(i => String(i.product_id) === String(p.id));
        const prevQty  = cartItem ? cartItem.qty : (productQuantityCache[p.id] || 1);

        document.getElementById('modalProductLabel').textContent = p.title;
        document.getElementById('modalProductPrice').textContent = formatCurrency(price);
        document.getElementById('modalProductStock').textContent = `Stock: ${formatNum(p.stock, 0)}`;
        document.getElementById('modalProductUnit').textContent  = p.primary_unit || 'Unit';
        document.getElementById('modalProductMeta').innerHTML    =
            `<span class="badge bg-secondary"><i class="bi bi-upc-scan me-1"></i>SKU: ${escHtml(p.sku||'')}</span>
             <span class="badge bg-info text-dark"><i class="bi bi-barcode me-1"></i>${escHtml(p.barcode||'')}</span>`;

        const thumb = document.getElementById('modalThumb');
        thumb.innerHTML = p.thumbnail
            ? `<img src="${escHtml(p.thumbnail)}" alt="${escHtml(p.title)}" style="width:100%;height:100%;object-fit:cover;border-radius:10px;">`
            : '<i class="bi bi-box-seam"></i>';

        const hasUnits = Array.isArray(p.units) && p.units.length > 0;
        const measureUnitRadio = document.getElementById('measureUnit');
        measureUnitRadio.disabled = !hasUnits;
        measureUnitRadio.closest('.pos-measure-opt').style.opacity = hasUnits ? '1' : '0.4';

        const savedPref = getSavedUnitPref(p.id);
        if (savedPref && hasUnits) {
            document.getElementById('measureUnit').checked = true;
            switchToUnitMode();
        } else {
            document.getElementById('measureQuantity').checked = true;
            switchToQuantityMode();
        }

        if (currentMeasurementType === 'quantity') {
            modalQty.value          = prevQty;
            pricePerUnitInput.value = (price * (parseInt(prevQty) || 1)).toFixed(2);
        } else {
            modalUnit.value         = prevQty;
            pricePerUnitInput.value = (price * (parseFloat(prevQty) || 1)).toFixed(2);
        }

        const prevLabel = document.getElementById('previousQtyText');
        if (cartItem) {
            prevLabel.textContent = `In cart: ${formatQty(prevQty, cartItem.is_unit_mode)}${cartItem.unit_short_name ? ' ' + cartItem.unit_short_name : ''}`;
            prevLabel.className = 'text-success fw-semibold ms-2';
        } else {
            prevLabel.textContent = `Prev: ${formatQty(prevQty)}`;
            prevLabel.className = 'text-muted ms-2';
        }

        removeFromCartBtn.style.display = cartItem ? 'inline-block' : 'none';
        confirmAddBtn.innerHTML = cartItem
            ? '<i class="bi bi-cart-check me-1"></i>Update Cart'
            : '<i class="bi bi-cart-plus me-1"></i>Add to Cart';

        originalPricePerUnit = price;
        updateModalTotal();
        updateAmountDisplay();
        quantityModal.show();
    }

    // ── QUANTITY MODAL SETUP ──────────────────────────────────
    function setupQuantityModal() {
        const qmEl = document.getElementById('quantityModal');

        qmEl.addEventListener('shown.bs.modal', () => {
            setTimeout(() => {
                if (currentMeasurementType === 'quantity') { modalQty.focus(); modalQty.select(); }
                else if (selectedUnit) { modalUnit.focus(); modalUnit.select(); }
                else unitSelect.focus();
            }, 100);
        });
        qmEl.addEventListener('hidden.bs.modal', () => setTimeout(() => input.focus(), 100));

        document.getElementById('measureQuantity').addEventListener('change', function () {
            if (this.checked) { switchToQuantityMode(); updateModalTotal(); }
        });
        document.getElementById('measureUnit').addEventListener('change', function () {
            if (this.checked) { switchToUnitMode(); updateModalTotal(); }
        });

        unitSelect.addEventListener('change', function () {
            selectedUnit = availableUnits.find(u => String(u.id) === this.value) || null;
            const enabled = !!selectedUnit;
            modalUnit.disabled = !enabled;
            document.getElementById('decreaseUnit').disabled = !enabled;
            document.getElementById('increaseUnit').disabled = !enabled;
            pricePerUnitInput.disabled = !enabled;
            if (enabled) {
                modalUnit.focus(); modalUnit.select();
                updateUnitLabel(); updateModalTotal(); updateAmountDisplay();
            } else {
                modalUnit.value = 1; pricePerUnitInput.value = '';
                updateModalTotal(); updateAmountDisplay();
            }
        });

        modalQty.addEventListener('input', () => {
            if (!isPriceInputActive) pricePerUnitInput.value = ((parseInt(modalQty.value)||1) * originalPricePerUnit).toFixed(2);
            updateModalTotal(); updateAmountDisplay();
        });
        modalUnit.addEventListener('input', () => {
            if (selectedUnit && !isPriceInputActive) pricePerUnitInput.value = ((parseFloat(modalUnit.value)||1) * originalPricePerUnit).toFixed(2);
            updateModalTotal(); updateAmountDisplay();
        });

        pricePerUnitInput.addEventListener('focus', () => isPriceInputActive = true);
        pricePerUnitInput.addEventListener('blur',  () => {
            isPriceInputActive = false;
            if ((parseFloat(pricePerUnitInput.value)||0) === 0 && currentProduct) {
                pricePerUnitInput.value = originalPricePerUnit.toFixed(2);
                if (currentMeasurementType === 'quantity') modalQty.value = 1;
                else if (selectedUnit) modalUnit.value = 1;
                updateModalTotal(); updateAmountDisplay();
            }
        });
        pricePerUnitInput.addEventListener('input', () => {
            if (!originalPricePerUnit) return;
            const total = parseFloat(pricePerUnitInput.value) || 0;
            if (total > 0) {
                const equiv = total / originalPricePerUnit;
                if (currentMeasurementType === 'quantity') modalQty.value = Math.round(equiv);
                else modalUnit.value = equiv.toFixed(3);
                updateModalTotal(); updateAmountDisplay();
            }
        });

        document.getElementById('increaseQty').addEventListener('click', () => {
            modalQty.value = (parseInt(modalQty.value)||1) + 1;
            pricePerUnitInput.value = (parseInt(modalQty.value) * originalPricePerUnit).toFixed(2);
            updateModalTotal(); updateAmountDisplay(); modalQty.focus(); modalQty.select();
        });
        document.getElementById('decreaseQty').addEventListener('click', () => {
            const v = parseInt(modalQty.value)||1;
            if (v > 1) { modalQty.value = v - 1; pricePerUnitInput.value = (parseInt(modalQty.value) * originalPricePerUnit).toFixed(2); updateModalTotal(); updateAmountDisplay(); modalQty.focus(); modalQty.select(); }
        });
        document.getElementById('increaseUnit').addEventListener('click', () => {
            if (!selectedUnit) return;
            modalUnit.value = (parseFloat(modalUnit.value) + getUnitStep()).toFixed(3);
            pricePerUnitInput.value = (parseFloat(modalUnit.value) * originalPricePerUnit).toFixed(2);
            updateModalTotal(); updateAmountDisplay(); modalUnit.focus(); modalUnit.select();
        });
        document.getElementById('decreaseUnit').addEventListener('click', () => {
            if (!selectedUnit) return;
            const c = parseFloat(modalUnit.value)||1;
            if (c > getUnitStep()) { modalUnit.value = (c - getUnitStep()).toFixed(3); pricePerUnitInput.value = (parseFloat(modalUnit.value) * originalPricePerUnit).toFixed(2); updateModalTotal(); updateAmountDisplay(); modalUnit.focus(); modalUnit.select(); }
        });

        document.querySelectorAll('.quick-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                if (currentMeasurementType === 'quantity') {
                    modalQty.value = this.dataset.value;
                    pricePerUnitInput.value = (parseInt(this.dataset.value) * originalPricePerUnit).toFixed(2);
                    modalQty.focus(); modalQty.select();
                } else if (selectedUnit) {
                    modalUnit.value = this.dataset.value;
                    pricePerUnitInput.value = (parseFloat(this.dataset.value) * originalPricePerUnit).toFixed(2);
                    modalUnit.focus(); modalUnit.select();
                } else { showToast('Select a unit first', 'warning'); return; }
                updateModalTotal(); updateAmountDisplay();
            });
        });
        document.querySelectorAll('.unit-quick-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                if (!selectedUnit) { showToast('Select a unit first', 'warning'); return; }
                modalUnit.value = this.dataset.value;
                pricePerUnitInput.value = (parseFloat(this.dataset.value) * originalPricePerUnit).toFixed(2);
                updateModalTotal(); updateAmountDisplay(); modalUnit.focus(); modalUnit.select();
            });
        });

        document.getElementById('rememberUnitPreference').addEventListener('change', function () {
            if (this.checked && currentProduct && selectedUnit) saveUnitPref();
        });

        [modalQty, modalUnit, pricePerUnitInput].forEach(el => {
            el.addEventListener('keydown', e => { if (e.key === 'Enter') { e.preventDefault(); confirmAddBtn.click(); } });
        });

        confirmAddBtn.addEventListener('click', addOrUpdateCart);
        removeFromCartBtn.addEventListener('click', removeCurrentFromCart);
    }

    // ── MODAL HELPERS ─────────────────────────────────────────
    function switchToQuantityMode() {
        currentMeasurementType = 'quantity';
        document.getElementById('measurementLabel').childNodes[0].textContent = 'Quantity';
        document.getElementById('quantityInputSection').style.display = 'block';
        document.getElementById('unitInputSection').classList.add('d-none');
        document.getElementById('unitSelectionWrap').classList.add('d-none');
        document.getElementById('unitQuickButtons').classList.add('d-none');
        document.getElementById('priceInputSection').classList.add('d-none');
        document.getElementById('quantityQuickButtons').classList.remove('d-none');
        modalQty.disabled = false;
        document.getElementById('decreaseQty').disabled = false;
        document.getElementById('increaseQty').disabled = false;
        if (currentProduct) {
            originalPricePerUnit = parseFloat(currentProduct.sale_price || currentProduct.price) || 0;
            pricePerUnitInput.value = (originalPricePerUnit * (parseInt(modalQty.value)||1)).toFixed(2);
            document.getElementById('originalPriceText').textContent = `Per unit: ${formatCurrency(originalPricePerUnit)}`;
        }
        updateModalTotal(); updateAmountDisplay();
    }

    function switchToUnitMode() {
        currentMeasurementType = 'unit';
        document.getElementById('measurementLabel').childNodes[0].textContent = 'Amount';
        document.getElementById('quantityInputSection').style.display = 'none';
        document.getElementById('unitInputSection').classList.remove('d-none');
        document.getElementById('unitSelectionWrap').classList.remove('d-none');
        document.getElementById('unitQuickButtons').classList.remove('d-none');
        document.getElementById('priceInputSection').classList.remove('d-none');
        document.getElementById('quantityQuickButtons').classList.add('d-none');
        modalUnit.disabled = true;
        document.getElementById('decreaseUnit').disabled = true;
        document.getElementById('increaseUnit').disabled = true;
        pricePerUnitInput.disabled = true;
        if (currentProduct) {
            originalPricePerUnit = parseFloat(currentProduct.sale_price || currentProduct.price) || 0;
            pricePerUnitInput.value = (originalPricePerUnit * (parseFloat(modalUnit.value)||1)).toFixed(2);
            document.getElementById('originalPriceText').textContent = `Per unit: ${formatCurrency(originalPricePerUnit)}`;
        }
        loadProductUnits();
        updateModalTotal(); updateAmountDisplay();
    }

    function updateUnitLabel() {
        document.getElementById('unitDisplay').textContent = selectedUnit ? selectedUnit.short_name : 'unit';
    }
    function updateAmountDisplay() {
        if (currentMeasurementType === 'quantity') {
            document.getElementById('amountDisplay').textContent = formatNum(parseInt(modalQty.value)||1);
            document.getElementById('unitDisplay').textContent   = 'unit(s)';
        } else {
            document.getElementById('amountDisplay').textContent = formatNum(parseFloat(modalUnit.value)||1, 2);
            document.getElementById('unitDisplay').textContent   = selectedUnit ? selectedUnit.short_name : 'unit';
        }
    }
    function updateModalTotal() {
        if (!currentProduct) return;
        const total = parseFloat(pricePerUnitInput.value) || 0;
        document.getElementById('totalPriceDisplay').textContent = `Total: ${formatCurrency(total)}`;
        const amount = currentMeasurementType === 'quantity' ? (parseInt(modalQty.value)||1) : (parseFloat(modalUnit.value)||1);
        const calc   = originalPricePerUnit * amount;
        document.getElementById('calculatedPriceDisplay').textContent =
            `${formatCurrency(originalPricePerUnit)} × ${formatNum(amount, currentMeasurementType==='unit'?2:0)} = ${formatCurrency(calc)}`;
    }
    function getUnitStep() {
        if (!selectedUnit) return 0.001;
        const sn = (selectedUnit.short_name || '').toLowerCase();
        if (sn.includes('g') && !sn.includes('kg')) return 1;
        return 0.001;
    }

    async function loadProductUnits() {
        if (!currentProduct) return;
        try {
            const res = await axios.get(
                '{{ route("api.product.units", ["product" => "__ID__"]) }}'.replace('__ID__', currentProduct.id)
            );
            availableUnits = res.data.units || [];
            unitSelect.innerHTML = '<option value="">-- Select unit --</option>';
            const savedPref = getSavedUnitPref(currentProduct.id);
            if (availableUnits.length > 0) {
                availableUnits.forEach(u => {
                    const opt = new Option(`${u.name} (${u.short_name})`, String(u.id));
                    if (savedPref && String(savedPref.unitId) === String(u.id)) { opt.selected = true; selectedUnit = u; }
                    else if (u.is_default && !selectedUnit) { opt.selected = true; selectedUnit = u; }
                    unitSelect.appendChild(opt);
                });
            } else {
                selectedUnit = { id: 1, name: currentProduct.primary_unit||'Unit', short_name: currentProduct.primary_unit||'unit', conversion_factor:1, is_default:true };
                unitSelect.appendChild(new Option(`${selectedUnit.name} (${selectedUnit.short_name})`, '1', true, true));
            }
            if (selectedUnit) {
                modalUnit.disabled = pricePerUnitInput.disabled =
                document.getElementById('decreaseUnit').disabled =
                document.getElementById('increaseUnit').disabled = false;
            }
            if (savedPref) document.getElementById('rememberUnitPreference').checked = true;
            updateUnitLabel(); updateAmountDisplay();
        } catch (e) {
            selectedUnit = { id:1, name: currentProduct.primary_unit||'Unit', short_name: currentProduct.primary_unit||'unit' };
            updateUnitLabel();
        }
    }

    function saveUnitPref() {
        if (!currentProduct || !selectedUnit) return;
        const prefs = JSON.parse(localStorage.getItem('unitPreferences')||'{}');
        prefs[currentProduct.id] = { unitId: selectedUnit.id, unitName: selectedUnit.name, shortName: selectedUnit.short_name, timestamp: Date.now() };
        localStorage.setItem('unitPreferences', JSON.stringify(prefs));
        showToast('Unit preference saved', 'success');
    }
    function getSavedUnitPref(id) {
        return (JSON.parse(localStorage.getItem('unitPreferences')||'{}')||{})[id];
    }

    // ── ADD / UPDATE CART ─────────────────────────────────────
    function addOrUpdateCart() {
        if (!currentProduct) return;
        if (parseFloat(currentProduct.stock) <= 0) { showToast(`${currentProduct.title} is out of stock`, 'error'); quantityModal.hide(); return; }
        if (currentMeasurementType === 'unit' && !selectedUnit) { showToast('Select a unit first', 'warning'); unitSelect.focus(); return; }

        const isUnitMode = currentMeasurementType === 'unit';
        let quantity     = isUnitMode ? (parseFloat(modalUnit.value)||0.001) : (parseInt(modalQty.value)||1);
        const totalPrice = parseFloat(pricePerUnitInput.value) || 0;
        let unitId       = currentProduct.primary_unit_id || 1;
        let unitName     = currentProduct.primary_unit || 'Unit';
        let unitShortName = unitName;

        if (isUnitMode) {
            if (quantity <= 0) { showToast('Amount must be > 0', 'warning'); return; }
            if (quantity > parseFloat(currentProduct.stock)) {
                showToast(`Only ${formatNum(currentProduct.stock, 3)} ${selectedUnit.short_name} available`, 'warning');
                modalUnit.value = parseFloat(currentProduct.stock).toFixed(3); updateModalTotal(); return;
            }
            unitId = selectedUnit.id; unitName = selectedUnit.name; unitShortName = selectedUnit.short_name;
        } else {
            if (quantity < 1) { showToast('Qty must be ≥ 1', 'warning'); return; }
            if (quantity > parseFloat(currentProduct.stock)) {
                showToast(`Only ${formatNum(currentProduct.stock)} units available`, 'warning');
                modalQty.value = Math.floor(currentProduct.stock); updateModalTotal(); return;
            }
        }

        const data = {
            product_id: currentProduct.id, title: currentProduct.title,
            price: totalPrice, unit_price: originalPricePerUnit, qty: quantity,
            unit_name: unitName, unit_short_name: unitShortName, unit_id: parseInt(unitId),
            sku: currentProduct.sku, barcode: currentProduct.barcode, thumbnail: currentProduct.thumbnail,
            discount_type: 'percent', discount_value: 0, discounted_price: originalPricePerUnit,
            is_unit_mode: isUnitMode, price_per_unit: originalPricePerUnit,
        };

        const existing = cart.find(i => String(i.product_id) === String(currentProduct.id));
        if (existing) Object.assign(existing, data);
        else cart.push(data);

        productQuantityCache[currentProduct.id] = quantity;
        quantityModal.hide();
        renderCartAndTotals();
        renderGrid();
        currentProduct = null;
        showToast('Added to cart', 'success');
    }

    function removeCurrentFromCart() {
        if (!currentProduct) return;
        Swal.fire({ title:'Remove?', text:`Remove ${currentProduct.title} from cart?`, icon:'warning', showCancelButton:true, confirmButtonText:'Yes, remove' })
            .then(r => {
                if (r.isConfirmed) {
                    const id = currentProduct.id;
                    cart = cart.filter(i => String(i.product_id) !== String(id));
                    delete productQuantityCache[id];
                    quantityModal.hide(); renderCartAndTotals(); renderGrid();
                    showToast('Removed from cart', 'success');
                }
            });
    }

    // ── CART RENDER + TOTALS ──────────────────────────────────
    function renderCartAndTotals() {
        if (cart.length === 0) {
            emptyCartState.classList.remove('d-none');
            cartItemsWrap.classList.add('d-none');
            cartBody.innerHTML = '';
            subtotalEl.textContent = formatCurrency(0);
            taxAmountEl.textContent = formatCurrency(0);
            grandTotalEl.textContent = formatCurrency(0);
            // FIX: Reset charge button total when cart is empty
            if (chargeBtnTotal) chargeBtnTotal.textContent = formatCurrency(0);
            document.getElementById('discountApplied').classList.add('d-none');
            window.currentDiscount = { type:'percent', value:0, amount:0 };
            return;
        }

        emptyCartState.classList.add('d-none');
        cartItemsWrap.classList.remove('d-none');
        cartBody.innerHTML = '';
        let subtotal = 0;

        cart.forEach((item, i) => {
            const unitPrice  = parseFloat(item.price_per_unit) || (parseFloat(item.price) / parseFloat(item.qty));
            let discPrice    = unitPrice;
            if (item.discount_value > 0) {
                discPrice = item.discount_type === 'percent'
                    ? unitPrice * (1 - item.discount_value / 100)
                    : unitPrice - (item.discount_value / item.qty);
                discPrice = Math.max(0, discPrice);
            }
            item.discounted_price = discPrice;
            const lineTotal   = discPrice * parseFloat(item.qty);
            subtotal         += lineTotal;
            const displayUnit = item.unit_short_name || item.unit_name || 'Unit';

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div class="cart-item-name">${escHtml(item.title)}</div>
                    ${item.discount_value > 0 ? `<span class="cart-disc-badge">-${formatNum(item.discount_value,2)}${item.discount_type==='percent'?'%':'₦'}</span>` : ''}
                </td>
                <td class="text-center">
                    <button class="cart-qty-btn qty-btn-cart" data-product-id="${escHtml(String(item.product_id))}">
                        ${formatQty(item.qty, item.is_unit_mode)} ${escHtml(displayUnit)}
                    </button>
                </td>
                <td class="text-end">${formatCurrency(discPrice)}</td>
                <td class="text-end fw-bold">${formatCurrency(lineTotal)}</td>
                <td>
                    <div class="d-flex gap-1 justify-content-end">
                        <button class="cart-action-btn disc item-discount-btn" data-product-id="${escHtml(String(item.product_id))}" title="Discount"><i class="bi bi-percent"></i></button>
                        <button class="cart-action-btn del remove-cart-item-btn" data-index="${i}" title="Remove"><i class="bi bi-x-lg"></i></button>
                    </div>
                </td>
            `;
            cartBody.appendChild(tr);

            tr.querySelector('.qty-btn-cart').addEventListener('click', function () {
                openQuantityModal(this.dataset.productId);
            });
            tr.querySelector('.item-discount-btn').addEventListener('click', function () {
                const pid = this.dataset.productId;
                currentItemIndex = cart.findIndex(x => String(x.product_id) === String(pid));
                if (currentItemIndex === -1) return;
                const ci = cart[currentItemIndex];
                document.getElementById('itemName').textContent      = ci.title;
                document.getElementById('itemDiscountValue').value   = ci.discount_value || 0;
                document.getElementById('itemDiscountType').value    = ci.discount_type || 'percent';
                new bootstrap.Modal(document.getElementById('itemDiscountModal')).show();
            });
            tr.querySelector('.remove-cart-item-btn').addEventListener('click', function () {
                const idx = parseInt(this.dataset.index);
                const pid = cart[idx]?.product_id;
                cart.splice(idx, 1);
                if (pid) delete productQuantityCache[pid];
                renderCartAndTotals(); renderGrid();
                showToast('Removed', 'success');
            });
        });

        const taxRate = {{ config('pos.tax_rate', 0) }};
        const taxAmt  = taxRate > 0 ? (subtotal * taxRate) / 100 : 0;
        let orderDisc = 0;
        if (orderDiscountValue > 0) {
            orderDisc = orderDiscountType === 'percent' ? (subtotal * orderDiscountValue) / 100 : orderDiscountValue;
            orderDisc = Math.min(orderDisc, subtotal);
        }
        const grand = Math.max(0, subtotal + taxAmt - orderDisc);

        subtotalEl.textContent     = formatCurrency(subtotal);
        taxAmountEl.textContent    = formatCurrency(taxAmt);
        grandTotalEl.textContent   = formatCurrency(grand);
        // FIX: Update charge button total
        if (chargeBtnTotal) chargeBtnTotal.textContent = formatCurrency(grand);

        if (orderDiscountValue > 0) {
            document.getElementById('discountApplied').classList.remove('d-none');
            document.getElementById('discountAmountLabel').textContent = `-${formatCurrency(orderDisc)}`;
        } else {
            document.getElementById('discountApplied').classList.add('d-none');
        }
        window.currentDiscount = { type: orderDiscountType, value: orderDiscountValue, amount: orderDisc };
    }

    // ── ITEM DISCOUNT MODAL ───────────────────────────────────
    function setupItemDiscountModal() {
        document.getElementById('applyItemDiscountBtn').addEventListener('click', function () {
            if (currentItemIndex === null || currentItemIndex < 0) return;
            const val  = parseFloat(document.getElementById('itemDiscountValue').value) || 0;
            const type = document.getElementById('itemDiscountType').value;
            if (type === 'percent' && val > 100) { showToast('Max 100%', 'warning'); return; }
            cart[currentItemIndex].discount_type  = type;
            cart[currentItemIndex].discount_value = val;
            renderCartAndTotals(); renderGrid();
            bootstrap.Modal.getInstance(document.getElementById('itemDiscountModal')).hide();
            showToast('Item discount applied', 'success');
        });
    }

    // ── ORDER DISCOUNT ────────────────────────────────────────
    document.getElementById('applyDiscountBtn').addEventListener('click', () => {
        orderDiscountValue = parseFloat(discountValueEl.value) || 0;
        orderDiscountType  = discountTypeEl.value;
        if (orderDiscountType === 'percent' && orderDiscountValue > 100) {
            orderDiscountValue = 100; discountValueEl.value = 100; showToast('Max 100%', 'warning'); return;
        }
        renderCartAndTotals();
        showToast('Discount applied', 'success');
    });

    // ── CLEAR / HOLD / LOAD ───────────────────────────────────
    document.getElementById('clearCart').addEventListener('click', () => {
        if (cart.length === 0) { showToast('Cart is already empty', 'info'); return; }
        Swal.fire({ title:'Clear Cart?', text:'Remove all items?', icon:'warning', showCancelButton:true, confirmButtonText:'Yes, clear' })
            .then(r => { if (r.isConfirmed) { cart = []; productQuantityCache = {}; renderCartAndTotals(); renderGrid(); showToast('Cart cleared','success'); } });
    });

    document.getElementById('holdOrderBtn').addEventListener('click', () => {
        if (cart.length === 0) { showToast('Cart is empty','info'); return; }
        const held = JSON.parse(localStorage.getItem('heldOrders')||'[]');
        held.push({
            id: Date.now(), cart: JSON.parse(JSON.stringify(cart)), customer: customerSelect.value,
            registrySnapshot: Array.from(productRegistry.entries()),
            productQuantityCache: {...productQuantityCache},
            discount: { type: orderDiscountType, value: orderDiscountValue },
            time: new Date().toLocaleString(), timestamp: Date.now(),
        });
        localStorage.setItem('heldOrders', JSON.stringify(held));
        cart = []; productQuantityCache = {}; orderDiscountValue = 0; discountValueEl.value = '0';
        renderCartAndTotals(); renderGrid();
        showToast('Order held!', 'success');
    });

    document.getElementById('loadHeldBtn').addEventListener('click', () => {
        const held   = JSON.parse(localStorage.getItem('heldOrders')||'[]');
        const list   = document.getElementById('heldOrdersList');
        const noOrds = document.getElementById('noHeldOrders');
        if (held.length === 0) { list.innerHTML=''; noOrds.style.display='block'; }
        else {
            noOrds.style.display='none';
            list.innerHTML = '<div class="list-group">' + held.sort((a,b)=>b.timestamp-a.timestamp).map(o => {
                const total = o.cart.reduce((s,it) => s + (parseFloat(it.price)||0), 0);
                return `<div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <div><strong>${o.time}</strong><br><small>${o.cart.length} item(s) — ${formatCurrency(total)}</small></div>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary load-order-btn" data-order-id="${o.id}">Load</button>
                        <button class="btn btn-sm btn-outline-danger remove-order-btn" data-order-id="${o.id}"><i class="bi bi-trash"></i></button>
                    </div></div>`;
            }).join('') + '</div>';

            list.querySelectorAll('.load-order-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const order = JSON.parse(localStorage.getItem('heldOrders')||'[]').find(o=>o.id==btn.dataset.orderId);
                    if (!order) return;
                    const doLoad = () => {
                        cart = JSON.parse(JSON.stringify(order.cart));
                        productQuantityCache = order.productQuantityCache || {};
                        if (order.registrySnapshot) order.registrySnapshot.forEach(([k,v]) => productRegistry.set(k, v));
                        if (order.customer) customerSelect.value = order.customer;
                        if (order.discount) { orderDiscountType=order.discount.type; orderDiscountValue=order.discount.value; discountTypeEl.value=order.discount.type; discountValueEl.value=order.discount.value; }
                        renderCartAndTotals(); renderGrid(); buildCategoryPills();
                        bootstrap.Modal.getInstance(document.getElementById('loadOrderModal')).hide();
                        showToast('Order loaded!','success');
                    };
                    if (cart.length > 0) {
                        Swal.fire({title:'Replace Cart?',icon:'warning',showCancelButton:true,confirmButtonText:'Replace'}).then(r=>{ if(r.isConfirmed) doLoad(); });
                    } else doLoad();
                });
            });
            list.querySelectorAll('.remove-order-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    Swal.fire({title:'Remove order?',icon:'warning',showCancelButton:true,confirmButtonText:'Yes'}).then(r=>{
                        if(r.isConfirmed){
                            localStorage.setItem('heldOrders', JSON.stringify(JSON.parse(localStorage.getItem('heldOrders')||'[]').filter(o=>o.id!=btn.dataset.orderId)));
                            btn.closest('.list-group-item').remove();
                            showToast('Order removed','success');
                        }
                    });
                });
            });
        }
        new bootstrap.Modal(document.getElementById('loadOrderModal')).show();
    });

    // ── COMPLETE ORDER ────────────────────────────────────────
    document.getElementById('completeOrder').addEventListener('click', completeOrder);

    async function completeOrder() {
        if (cart.length === 0) { showToast('Cart is empty','warning'); return; }
        if (isProcessingOrder) { showToast('Already processing…','info'); return; }

        const payment    = document.querySelector('input[name="payment"]:checked').value;
        const customerId = customerSelect.value || null;
        const offline    = document.getElementById('offlineModeToggle').checked || !navigator.onLine;

        if (offline) {
            const offlineOrders = JSON.parse(localStorage.getItem('offlineOrders')||'[]');
            const oid = 'OFFLINE-' + Date.now();
            offlineOrders.push({
                id:oid, items: cart.map(it => ({
                    product_id: it.product_id, qty: parseFloat(it.qty), unit_id: parseInt(it.unit_id||1),
                    sale_price: parseFloat(it.unit_price||it.price), discount_type: it.discount_type||null,
                    discount_value: it.discount_value||0, is_unit_mode: it.is_unit_mode||false, unit_name: it.unit_name||null,
                })),
                payment_method:payment, customer_id:customerId, discount_type:orderDiscountType,
                discount_value:orderDiscountValue, discount_amount: window.currentDiscount?.amount||0,
                tax_rate: {{ config('pos.tax_rate', 0) }}, timestamp:Date.now(), time:new Date().toLocaleString(),
            });
            localStorage.setItem('offlineOrders', JSON.stringify(offlineOrders));
            resetAfterOrder(false);
            Swal.fire({title:'Saved Offline!',html:`<div class="text-center"><i class="bi bi-wifi-off text-warning display-1 mb-3"></i><h4>Order #${oid}</h4><p>Will sync when online.</p></div>`,icon:'warning',confirmButtonText:'OK'});
            return;
        }

        isProcessingOrder = true;
        const btn = document.getElementById('completeOrder');
        btn.disabled=true; btn.innerHTML='<span class="spinner-border spinner-border-sm me-2"></span>Processing…';

        const items = cart.map(it => ({
            product_id:it.product_id, qty:parseFloat(it.qty), unit_id:parseInt(it.unit_id||1),
            sale_price:parseFloat(it.unit_price||it.price/it.qty), discount_type:it.discount_type||null,
            discount_value:it.discount_value||0, is_unit_mode:it.is_unit_mode||false, unit_name:it.unit_name||null,
        }));
        const disc = window.currentDiscount || {type:'percent',value:0,amount:0};

        Swal.fire({title:'Processing…',allowOutsideClick:false,didOpen:()=>Swal.showLoading()});
        try {
            const res = await axios.post('{{ route("pos.order.save") }}', {
                items, payment_method:payment, customer_id:customerId,
                discount_type:disc.type, discount_value:disc.value, discount_amount:disc.amount,
                _token:'{{ csrf_token() }}',
            });
            Swal.close();
            if (res.data.success) {
                if (thankYouAudio) { thankYouAudio.currentTime=0; thankYouAudio.play().catch(()=>{}); }
                Swal.fire({
                    title:'Order Complete!',
                    html:`<div class="text-center"><i class="bi bi-check-circle text-success display-1 mb-3"></i><h4>Order #${res.data.order_id}</h4><p class="fs-4">${formatCurrency(res.data.total)}</p></div>`,
                    icon:'success', showCancelButton:true, confirmButtonText:'Print Receipt', cancelButtonText:'New Order',
                    buttonsStyling:false, customClass:{confirmButton:'btn btn-success btn-lg me-2',cancelButton:'btn btn-outline-secondary btn-lg'},
                }).then(r => {
                    if (r.isConfirmed) {
                        const pw = window.open(`/pos/receipt/${res.data.order_id}`, '_blank');
                        if (pw) { const iv=setInterval(()=>{ if(pw.closed){clearInterval(iv);resetAfterOrder();}},500); }
                        else resetAfterOrder();
                    } else resetAfterOrder();
                });
            } else { Swal.fire('Error', res.data.message||'Failed', 'error'); isProcessingOrder=false; btn.disabled=false; btn.innerHTML='<i class="bi bi-printer-fill me-2"></i><span>Charge</span><span class="pos-charge-total">' + (chargeBtnTotal ? chargeBtnTotal.textContent : '₦0.00') + '</span>'; }
        } catch (e) {
            Swal.close();
            let msg = 'Failed to complete order.';
            if (e.response?.data?.errors) msg = Object.values(e.response.data.errors).flat().join('<br>');
            else if (e.response?.data?.message) msg = e.response.data.message;
            Swal.fire('Error', msg, 'error');
            isProcessingOrder=false; btn.disabled=false;
            btn.innerHTML='<i class="bi bi-printer-fill me-2"></i><span>Charge</span><span class="pos-charge-total">' + (chargeBtnTotal ? chargeBtnTotal.textContent : '₦0.00') + '</span>';
        }
    }

    // ── RESET AFTER ORDER ─────────────────────────────────────
    function resetAfterOrder(focusInput = true) {
        cart = [];
        productQuantityCache = {};
        orderDiscountValue   = 0;
        orderDiscountType    = 'percent';
        discountValueEl.value = '0';
        discountTypeEl.value = 'percent';

        // Clear search cache so stock counts stay fresh
        searchCache.clear();

        // Force complete UI refresh
        renderCartAndTotals();
        renderGrid();

        // Explicitly reset the charge button total (double-check)
        if (chargeBtnTotal) {
            chargeBtnTotal.textContent = formatCurrency(0);
        }

        // Reset the complete order button HTML to ensure it's clean
        const chargeBtn = document.getElementById('completeOrder');
        if (chargeBtn && !chargeBtn.disabled) {
            chargeBtn.innerHTML = '<i class="bi bi-printer-fill me-2"></i><span>Charge</span><span class="pos-charge-total" id="chargeBtnTotal">₦0.00</span>';
        }

        input.value = '';
        if (focusInput) input.focus();
        showToast('New order started', 'info');
    }

    // ── QUICK CUSTOMER ────────────────────────────────────────
    function initQuickCustomer() {
        document.getElementById('quickCustomerBtn').addEventListener('click', () => quickCustomerModal.show());
        document.getElementById('saveQuickCustomerBtn').addEventListener('click', async () => {
            const fn = document.getElementById('firstName').value.trim();
            const ln = document.getElementById('lastName').value.trim();
            const ph = document.getElementById('phoneNumber').value.trim();
            const em = document.getElementById('email').value.trim();
            if (!fn||!ln) { showToast('Name required','warning'); return; }
            const btn = document.getElementById('saveQuickCustomerBtn');
            btn.innerHTML='<span class="spinner-border spinner-border-sm me-1"></span>Saving…'; btn.disabled=true;
            try {
                const res = await axios.post('{{ route("customers.quick") }}', { first_name:fn, last_name:ln, phone_number:ph, email:em, _token:'{{ csrf_token() }}' });
                if (res.data.success) {
                    const opt = new Option(`${fn} ${ln}${ph?' - '+ph:''}`, res.data.customer.id);
                    customerSelect.appendChild(opt); customerSelect.value = res.data.customer.id;
                    quickCustomerModal.hide();
                    ['firstName','lastName','phoneNumber','email'].forEach(id => { const el=document.getElementById(id); if(el) el.value=''; });
                    showToast('Customer added','success');
                } else { showToast(res.data.message||'Failed','error'); }
            } catch(e) { showToast(e.response?.data?.message||'Failed to add customer','error'); }
            finally { btn.innerHTML='Save Customer'; btn.disabled=false; }
        });
    }

    // ── OFFLINE MODE ──────────────────────────────────────────
    function setupOfflineMode() {
        function updateConn(online) {
            const el = document.getElementById('connectionStatus');
            el.className = 'badge ' + (online ? 'bg-success' : 'bg-danger');
            el.innerHTML = online ? '<i class="bi bi-wifi"></i> Online' : '<i class="bi bi-wifi-off"></i> Offline';
            document.getElementById('offlineModeToggle').checked = !online;
        }
        updateConn(navigator.onLine);
        window.addEventListener('online',  () => { updateConn(true);  showToast('Back online!','success'); syncOfflineOrders(); });
        window.addEventListener('offline', () => { updateConn(false); showToast("You're offline",'warning'); });
    }

    async function syncOfflineOrders() {
        const orders = JSON.parse(localStorage.getItem('offlineOrders')||'[]');
        if (!orders.length) return;
        let synced=0, failed=0, lastId=null;
        for (const o of [...orders]) {
            try {
                const r = await axios.post('{{ route("pos.order.save") }}', {...o, _token:'{{ csrf_token() }}'});
                if (r.data.success) {
                    synced++; lastId=r.data.order_id;
                    localStorage.setItem('offlineOrders', JSON.stringify(JSON.parse(localStorage.getItem('offlineOrders')||'[]').filter(x=>x.id!==o.id)));
                } else failed++;
            } catch { failed++; }
        }
        if (synced>0) Swal.fire({title:`${synced} Order${synced>1?'s':''} Synced!`,text:'Offline orders submitted.',icon:'success',showCancelButton:!!lastId,confirmButtonText:'Print Last Receipt',cancelButtonText:'Dismiss'}).then(r=>{if(r.isConfirmed&&lastId) window.open(`/pos/receipt/${lastId}`,'_blank');});
        if (failed>0) showToast(`${failed} order(s) failed to sync`,'error',4000);
    }

    // ── KEYBOARD SHORTCUTS ────────────────────────────────────
    function setupKeyboardShortcuts() {
        document.addEventListener('keydown', function (e) {
            if (e.target.tagName==='INPUT'||e.target.tagName==='TEXTAREA'||e.target.isContentEditable) return;
            switch (e.key) {
                case 'F1': e.preventDefault(); input.focus(); input.select(); break;
                case 'F2': e.preventDefault(); document.getElementById('clearCart').click(); break;
                case 'F3': e.preventDefault(); completeOrder(); break;
                case 'F4': e.preventDefault(); document.getElementById('holdOrderBtn').click(); break;
                case 'F5': e.preventDefault(); document.getElementById('loadHeldBtn').click(); break;
                case 'F6': e.preventDefault(); window.location.href='{{ route("pos.index") }}'; break;
                case 'Escape': if(cart.length>0) document.getElementById('clearCart').click(); break;
            }
        });
    }

    // ── UTILITIES ─────────────────────────────────────────────
    function showSpinner(show) { document.getElementById('searchSpinner').classList.toggle('d-none', !show); }

    function formatNum(n, dec=0) {
        return new Intl.NumberFormat('en-NG',{minimumFractionDigits:dec,maximumFractionDigits:dec}).format(parseFloat(n)||0);
    }
    function formatCurrency(n) { return '₦' + formatNum(n,2); }
    function formatQty(qty, isUnit=false) { return formatNum(qty, isUnit?2:0); }

    function escHtml(str) {
        return String(str||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }

    function showToast(message, type='success', duration=2000) {
        Swal.mixin({toast:true,position:'top-end',showConfirmButton:false,timer:duration,timerProgressBar:true}).fire({icon:type,title:message});
    }
    function debounce(fn, delay) {
        let t;
        return function(...args){ clearTimeout(t); t=setTimeout(()=>fn.apply(this,args),delay); };
    }

    // ── START ─────────────────────────────────────────────────
    init();
});
</script>

<style>
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap');

:root {
    --pg-accent:      #2563eb;
    --pg-green:       #16a34a;
    --pg-green-light: #dcfce7;
    --pg-amber:       #d97706;
    --pg-red:         #dc2626;
    --pg-border:      #e1e4e8;
    --pg-surface:     #ffffff;
    --pg-price-font:  'DM Mono', monospace;
    --pg-radius:      14px;
    --pg-radius-sm:   8px;
}

.pos-grid-body { height: calc(100vh - 152px); min-height: 500px; }
.pos-products-col, .pos-order-col { display: flex; flex-direction: column; height: 100%; overflow: hidden; }
.pos-products-col .card.mb-2 { flex-shrink: 0; }
.pos-products-card { flex: 1; overflow: hidden; }
.pos-products-card-body { height: 100%; overflow: hidden; }
.pos-order-card { overflow: hidden; }
.pos-order-body  { overflow: hidden; }

.pos-search-wrap { position: relative; }
.pos-search-input { padding-right: 80px !important; font-family: 'DM Sans', sans-serif; border-radius: var(--pg-radius-sm) !important; }
.pos-search-input:focus { border-color: var(--pg-accent) !important; box-shadow: 0 0 0 3px rgba(37,99,235,.15) !important; }
.pos-search-icon { position: absolute; right: 44px; top: 50%; transform: translateY(-50%); font-size: 1.3rem; color: #9ca3af; pointer-events: none; }
.pos-search-spinner { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); }
.pos-shortcuts-row { display: flex; align-items: center; flex-wrap: wrap; gap: 4px; font-size: .75rem; color: #6b7280; }
.pos-shortcuts-row .badge { font-size: .62rem; }

.pos-clock { font-family: var(--pg-price-font); font-size: .82rem; color: #6b7280; }

.pos-cat-bar { display: flex; align-items: center; gap: 8px; flex-wrap: nowrap; overflow-x: auto; scrollbar-width: none; flex-shrink: 0; }
.pos-cat-bar::-webkit-scrollbar { display: none; }
.pos-cat-pill { flex-shrink: 0; padding: 5px 14px; border-radius: 20px; font-size: .78rem; font-weight: 600; cursor: pointer; border: 1.5px solid var(--pg-border); background: var(--pg-surface); color: #6b7280; transition: all .15s; white-space: nowrap; }
.pos-cat-pill:hover { border-color: var(--pg-accent); color: var(--pg-accent); }
.pos-cat-pill.active { background: var(--pg-accent); color: #fff; border-color: var(--pg-accent); box-shadow: 0 2px 8px rgba(37,99,235,.25); }

.pos-grid-loading { position: absolute; inset: 0; background: rgba(255,255,255,.9); display: flex; flex-direction: column; align-items: center; justify-content: center; z-index: 10; border-radius: var(--pg-radius); }

.pos-product-grid { flex: 1; overflow-y: auto; display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 12px; padding: 4px 2px; align-content: start; }
.pos-product-grid::-webkit-scrollbar { width: 5px; }
.pos-product-grid::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }

.pos-prod-card { background: var(--pg-surface); border: 1.5px solid var(--pg-border); border-radius: var(--pg-radius); cursor: pointer; position: relative; display: flex; flex-direction: column; align-items: center; padding: 12px 10px 0; gap: 6px; transition: transform .12s, border-color .15s, box-shadow .15s; user-select: none; overflow: hidden; min-height: 185px; }
.pos-prod-card:hover:not(.out-of-stock) { border-color: var(--pg-accent); box-shadow: 0 6px 18px rgba(37,99,235,.14); transform: translateY(-3px); }
.pos-prod-card:active:not(.out-of-stock) { transform: translateY(0); }
.pos-prod-card:focus-visible { outline: 2px solid var(--pg-accent); outline-offset: 2px; }
.pos-prod-card.in-cart { border-color: var(--pg-green); background: #f0fdf4; }
.pos-prod-card.in-cart:hover { border-color: #15803d; box-shadow: 0 6px 18px rgba(22,163,74,.18); }
.pos-prod-card.out-of-stock { opacity: .45; cursor: not-allowed; filter: grayscale(.7); }
.pos-prod-card.out-of-stock:hover { transform: none; box-shadow: none; border-color: var(--pg-border); }

.prod-cart-badge { position: absolute; top: 6px; left: 6px; z-index: 2; background: var(--pg-green); color: #fff; font-size: 10px; font-weight: 700; padding: 2px 7px; border-radius: 20px; display: flex; align-items: center; gap: 2px; }
.prod-stock-pip { position: absolute; top: 8px; right: 8px; width: 8px; height: 8px; border-radius: 50%; }
.prod-stock-pip.good { background: #22c55e; }
.prod-stock-pip.low  { background: #f59e0b; }
.prod-stock-pip.out  { background: #ef4444; }

.prod-img-wrap { width: 80px; height: 80px; border-radius: 10px; background: #f3f4f6; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; flex-shrink: 0; margin-top: 4px; }
.prod-img-wrap img { width: 100%; height: 100%; object-fit: cover; }
.prod-img-placeholder { font-size: 32px; color: #d1d5db; }
.prod-out-overlay { position: absolute; inset: 0; background: rgba(0,0,0,.45); display: flex; align-items: center; justify-content: center; border-radius: 10px; }
.prod-out-overlay span { color: #fff; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px; }

.prod-info { width: 100%; text-align: center; padding-bottom: 4px; }
.prod-name { font-size: 12px; font-weight: 600; color: #111827; line-height: 1.3; max-height: 2.6em; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; margin-bottom: 3px; }
.prod-price { font-family: var(--pg-price-font); font-size: 13px; font-weight: 700; color: var(--pg-green); }
.prod-meta  { font-size: 10px; color: #9ca3af; margin-top: 2px; }
.prod-saved-unit { font-size: 10px; color: #6b7280; }
.prod-in-cart-bar { width: calc(100% + 20px); margin: auto -10px -0px; background: var(--pg-green); color: #fff; font-size: 10px; font-weight: 700; text-align: center; padding: 3px 0; letter-spacing: .5px; text-transform: uppercase; margin-top: auto; }

.pos-empty-state { flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: center; color: #9ca3af; padding: 40px; }
.pos-empty-state i { font-size: 2.8rem; margin-bottom: 12px; color: #d1d5db; }
.pos-empty-state h5 { font-size: .95rem; font-weight: 600; color: #6b7280; }

.pos-customer-row { display: flex; align-items: center; gap: 6px; }
.pos-customer-select-wrap { flex: 1; position: relative; display: flex; align-items: center; }
.pos-customer-icon { position: absolute; left: 10px; font-size: 1rem; color: #9ca3af; pointer-events: none; z-index: 2; }
.pos-customer-select { width: 100%; padding: 7px 10px 7px 32px; border: 1px solid var(--pg-border); border-radius: var(--pg-radius-sm); font-size: .78rem; font-family: 'DM Sans', sans-serif; appearance: none; background: #f9fafb; color: #374151; cursor: pointer; outline: none; transition: border-color .2s; }
.pos-customer-select:focus { border-color: var(--pg-accent); background: #fff; }
.pos-icon-btn { width: 32px; height: 32px; flex-shrink: 0; border: 1px solid var(--pg-border); border-radius: var(--pg-radius-sm); background: #f9fafb; color: #6b7280; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: .85rem; transition: all .15s; }
.pos-icon-btn:hover         { background: var(--pg-accent); color: #fff; border-color: var(--pg-accent); }
.pos-icon-btn.warning:hover { background: #f59e0b; color: #fff; border-color: #f59e0b; }
.pos-icon-btn.info:hover    { background: #0ea5e9; color: #fff; border-color: #0ea5e9; }
.pos-icon-btn.danger:hover  { background: var(--pg-red); color: #fff; border-color: var(--pg-red); }

.pos-cart-area { flex: 1; overflow-y: auto; min-height: 0; }
.pos-cart-area::-webkit-scrollbar { width: 4px; }
.pos-cart-area::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 2px; }
.pos-cart-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; color: #9ca3af; gap: 6px; padding: 32px; }
.pos-cart-empty-icon { font-size: 3rem; color: #e5e7eb; }
.pos-cart-empty p { font-size: .88rem; font-weight: 600; color: #6b7280; margin: 0; }

.pos-cart-table { width: 100%; border-collapse: collapse; font-size: .78rem; }
.pos-cart-table thead tr { background: #f9fafb; border-bottom: 2px solid var(--pg-border); }
.pos-cart-table thead th { padding: 6px 8px; font-weight: 600; color: #6b7280; text-transform: uppercase; font-size: .65rem; letter-spacing: .5px; }
.pos-cart-table tbody tr { border-bottom: 1px solid #f3f4f6; transition: background .1s; }
.pos-cart-table tbody tr:hover { background: #fafafa; }
.pos-cart-table td { padding: 6px 8px; vertical-align: middle; }
.cart-item-name { font-weight: 600; color: #111827; font-size: .78rem; line-height: 1.3; }
.cart-disc-badge { display: inline-block; font-size: 9px; background: #fef3c7; color: #92400e; border-radius: 4px; padding: 1px 5px; margin-top: 2px; font-weight: 600; }
.cart-qty-btn { background: #eff6ff; border: 1px solid #dbeafe; border-radius: 5px; color: var(--pg-accent); font-size: .72rem; font-weight: 600; padding: 2px 6px; cursor: pointer; white-space: nowrap; transition: all .12s; }
.cart-qty-btn:hover { background: var(--pg-accent); color: #fff; border-color: var(--pg-accent); }
.cart-action-btn { width: 24px; height: 24px; border-radius: 5px; cursor: pointer; border: 1px solid var(--pg-border); background: #f9fafb; color: #9ca3af; display: flex; align-items: center; justify-content: center; font-size: .68rem; transition: all .12s; }
.cart-action-btn.disc:hover { background: #fef3c7; border-color: #f59e0b; color: #d97706; }
.cart-action-btn.del:hover  { background: #fee2e2; border-color: #ef4444; color: #dc2626; }

.pos-discount-row { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; background: #fafafa; }
.pos-discount-label { font-size: .75rem; font-weight: 600; color: #374151; white-space: nowrap; }
.pos-discount-inputs { display: flex; gap: 4px; align-items: center; flex: 1; }
.pos-discount-input { flex: 1; height: 30px; border: 1px solid var(--pg-border); border-radius: 6px; padding: 0 6px; font-size: .78rem; font-family: var(--pg-price-font); text-align: right; outline: none; background: #fff; transition: border-color .15s; }
.pos-discount-input:focus { border-color: var(--pg-accent); }
.pos-discount-select { height: 30px; border: 1px solid var(--pg-border); border-radius: 6px; padding: 0 4px; font-size: .75rem; background: #fff; outline: none; cursor: pointer; }
.pos-discount-apply { height: 30px; width: 30px; border: 1px solid var(--pg-accent); border-radius: 6px; background: var(--pg-accent); color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: .85rem; transition: background .15s; }
.pos-discount-apply:hover { background: #1d4ed8; }
.pos-discount-applied { font-size: .74rem; font-weight: 700; color: var(--pg-red); white-space: nowrap; }

.pos-totals {}
.pos-total-row { display: flex; justify-content: space-between; align-items: center; font-size: .8rem; color: #6b7280; padding: 2px 0; }
.pos-total-row span:last-child { font-family: var(--pg-price-font); }
.pos-total-row.grand { margin-top: 4px; padding-top: 6px; border-top: 2px solid #111827; font-size: .95rem; font-weight: 700; color: #111827; }
.pos-total-row.grand span:last-child { font-size: 1.05rem; color: var(--pg-green); }

.pos-payment-group { display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px; }
.pos-payment-opt { cursor: pointer; }
.pos-payment-opt input { display: none; }
.pos-payment-opt span { display: flex; align-items: center; justify-content: center; gap: 4px; padding: 7px 4px; border-radius: var(--pg-radius-sm); border: 1.5px solid var(--pg-border); font-size: .73rem; font-weight: 600; color: #6b7280; background: #f9fafb; transition: all .15s; }
.pos-payment-opt:hover span { border-color: var(--pg-accent); color: var(--pg-accent); background: #eff6ff; }
.pos-payment-opt input:checked + span { border-color: var(--pg-green); background: var(--pg-green-light); color: var(--pg-green); }

.pos-charge-btn { border: none; border-radius: var(--pg-radius); background: linear-gradient(135deg, #16a34a, #15803d); color: #fff; font-family: 'DM Sans', sans-serif; font-weight: 700; font-size: .95rem; cursor: pointer; height: 50px; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 12px rgba(22,163,74,.3); transition: all .15s; }
.pos-charge-btn:hover:not(:disabled) { background: linear-gradient(135deg, #15803d, #166534); box-shadow: 0 6px 16px rgba(22,163,74,.4); transform: translateY(-1px); }
.pos-charge-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; }
.pos-charge-total { font-family: var(--pg-price-font); background: rgba(255,255,255,.2); border-radius: 6px; padding: 2px 10px; font-size: .9rem; }

.pos-modal-content { border-radius: 16px; overflow: hidden; }
.pos-modal-header { background: linear-gradient(135deg, #1e40af, #2563eb); color: #fff; padding: 14px 18px; }
.pos-modal-header .btn-close { filter: brightness(0) invert(1); opacity: .8; }
.pos-modal-product-thumb { width: 46px; height: 46px; border-radius: 10px; background: rgba(255,255,255,.2); flex-shrink: 0; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: rgba(255,255,255,.8); overflow: hidden; }
.pos-measure-toggle { display: flex; gap: 8px; }
.pos-measure-opt { flex: 1; cursor: pointer; }
.pos-measure-opt input { display: none; }
.pos-measure-opt span { display: flex; align-items: center; justify-content: center; gap: 5px; padding: 7px; border-radius: 8px; border: 1.5px solid var(--pg-border); font-size: .8rem; font-weight: 600; color: #6b7280; background: #f9fafb; transition: all .15s; }
.pos-measure-opt input:checked + span { border-color: var(--pg-accent); background: #eff6ff; color: var(--pg-accent); }
.pos-qty-stepper { display: flex; align-items: center; gap: 8px; }
.pos-step-btn { width: 42px; height: 48px; border: 1.5px solid var(--pg-border); border-radius: 10px; background: #f9fafb; color: #374151; font-size: 1rem; cursor: pointer; transition: all .12s; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
.pos-step-btn:hover:not(:disabled) { background: var(--pg-accent); color: #fff; border-color: var(--pg-accent); }
.pos-step-btn:disabled { opacity: .4; cursor: not-allowed; }
.pos-qty-input { flex: 1; height: 48px; border: 1.5px solid var(--pg-border); border-radius: 10px; text-align: center; font-size: 1.5rem; font-weight: 700; font-family: var(--pg-price-font); color: #111827; background: #fff; outline: none; transition: border-color .15s; }
.pos-qty-input:focus { border-color: var(--pg-accent); box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
.pos-price-box { background: #f9fafb; border-radius: 10px; padding: 10px 12px; }
.pos-quick-btns { display: grid; grid-template-columns: repeat(4,1fr); gap: 6px; }
.pos-quick-btn { padding: 7px 4px; border-radius: 7px; border: 1.5px solid var(--pg-border); background: #fff; color: #374151; font-size: .82rem; font-weight: 600; cursor: pointer; transition: all .12s; font-family: 'DM Sans', sans-serif; }
.pos-quick-btn:hover { background: var(--pg-accent); color: #fff; border-color: var(--pg-accent); }
.unit-quick-btn { border-color: #dcfce7; color: var(--pg-green); }
.unit-quick-btn:hover { background: var(--pg-green); border-color: var(--pg-green); color: #fff; }

@media (max-width: 991px) {
    .pos-grid-body { height: auto; }
    .pos-products-col, .pos-order-col { height: auto; overflow: visible; }
    .pos-products-card { flex: none; }
    .pos-products-card-body { height: auto; }
    .pos-product-grid { max-height: 55vh; }
    .pos-cart-area { max-height: 300px; }
    .pos-product-grid { grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); }
}
@media (max-width: 767px) {
    .pos-product-grid { grid-template-columns: repeat(3,1fr); }
}
</style>
@endsection
