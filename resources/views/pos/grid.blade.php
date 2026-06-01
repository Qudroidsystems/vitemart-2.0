@extends('layouts.master')
@section('title', $pagetitle ?? 'POS Grid')
@section('content')
<div class="main-content pos-grid-page">
    <div class="page-content p-0">
        <div class="pos-grid-layout">

            <!-- ══════════════════════════════════════════
                 TOP NAV BAR
            ══════════════════════════════════════════ -->
            <header class="pos-topbar">
                <div class="pos-topbar-left">
                    <a href="{{ route('pos.index') }}" class="pos-back-btn" title="Switch to Standard POS">
                        <i class="bi bi-layout-text-sidebar-reverse"></i>
                        <span>Standard POS</span>
                    </a>
                    <div class="pos-topbar-divider"></div>
                    <span class="pos-topbar-title">
                        <i class="bi bi-grid-3x3-gap-fill me-2"></i>Grid POS
                    </span>
                </div>
                <div class="pos-topbar-center">
                    <div class="pos-search-wrap">
                        <i class="bi bi-upc-scan pos-search-icon"></i>
                        <input type="text" id="barcodeInput"
                               class="pos-search-input"
                               placeholder="Scan barcode or type product name / SKU…"
                               autofocus autocomplete="off"
                               aria-label="Search or scan products">
                        <span id="searchSpinner" class="pos-search-spinner d-none">
                            <span class="spinner-border spinner-border-sm text-primary"></span>
                        </span>
                        <kbd class="pos-search-kbd">F1</kbd>
                    </div>
                </div>
                <div class="pos-topbar-right">
                    <span id="connectionStatus" class="pos-conn-badge online">
                        <i class="bi bi-wifi"></i><span>Online</span>
                    </span>
                    <div class="form-check form-switch mb-0 ms-3">
                        <input class="form-check-input" type="checkbox" id="offlineModeToggle">
                        <label class="form-check-label small text-muted" for="offlineModeToggle">Offline</label>
                    </div>
                    <div class="pos-topbar-divider"></div>
                    <div class="pos-clock" id="posClock">--:--</div>
                </div>
            </header>

            <!-- ══════════════════════════════════════════
                 MAIN AREA: LEFT PRODUCTS + RIGHT CART
            ══════════════════════════════════════════ -->
            <div class="pos-body">

                <!-- ─── LEFT: Products Panel ─── -->
                <div class="pos-products-panel">

                    <!-- Category Filter Bar -->
                    <div class="pos-cat-bar" id="catBar">
                        <button class="pos-cat-pill active" data-cat="all">
                            <i class="bi bi-grid-fill me-1"></i>All
                        </button>
                        <!-- Dynamic pills added by JS -->
                    </div>

                    <!-- Product Grid -->
                    <div class="pos-product-grid" id="productGrid">
                        <!-- Loading skeleton -->
                        @for($i=0; $i<20; $i++)
                        <div class="pos-prod-skeleton">
                            <div class="skel-img"></div>
                            <div class="skel-line long"></div>
                            <div class="skel-line short"></div>
                        </div>
                        @endfor
                    </div>

                    <!-- Empty / No results state -->
                    <div id="emptyState" class="pos-empty-state d-none">
                        <i class="bi bi-search"></i>
                        <h5>No products found</h5>
                        <p>Try a different search term or category</p>
                    </div>
                </div>

                <!-- ─── RIGHT: Order Panel ─── -->
                <div class="pos-order-panel">

                    <!-- Customer Row -->
                    <div class="pos-customer-row">
                        <div class="pos-customer-select-wrap">
                            <i class="bi bi-person-circle pos-customer-icon"></i>
                            <select id="customerSelect" class="pos-customer-select">
                                <option value="">Walk-in Customer</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}">
                                        {{ $customer->first_name }} {{ $customer->last_name }}
                                        @if($customer->phone_number) · {{ $customer->phone_number }} @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button class="pos-icon-btn" id="quickCustomerBtn" title="Add Customer">
                            <i class="bi bi-person-plus-fill"></i>
                        </button>
                        <button class="pos-icon-btn warning" id="holdOrderBtn" title="Hold Order (F4)">
                            <i class="bi bi-pause-circle-fill"></i>
                        </button>
                        <button class="pos-icon-btn info" id="loadHeldBtn" title="Load Held Order">
                            <i class="bi bi-folder-symlink-fill"></i>
                        </button>
                        <button class="pos-icon-btn danger" id="clearCart" title="Clear Cart (F2)">
                            <i class="bi bi-trash3-fill"></i>
                        </button>
                    </div>

                    <!-- Cart Items -->
                    <div class="pos-cart-area" id="cartArea">
                        <div id="emptyCartState" class="pos-cart-empty">
                            <div class="pos-cart-empty-icon">
                                <i class="bi bi-cart4"></i>
                            </div>
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

                    <!-- Discount Row -->
                    <div class="pos-discount-row">
                        <span class="pos-discount-label">Discount</span>
                        <div class="pos-discount-inputs">
                            <input type="number" id="discountValue" class="pos-discount-input"
                                   placeholder="0" min="0" step="0.01" value="0">
                            <select id="discountType" class="pos-discount-select">
                                <option value="percent" selected>%</option>
                                <option value="fixed">₦</option>
                            </select>
                            <button class="pos-discount-apply" id="applyDiscountBtn" title="Apply">
                                <i class="bi bi-check-lg"></i>
                            </button>
                        </div>
                        <div id="discountApplied" class="pos-discount-applied d-none">
                            <i class="bi bi-tag-fill me-1"></i><span id="discountAmountLabel">-₦0.00</span>
                        </div>
                    </div>

                    <!-- Totals -->
                    <div class="pos-totals">
                        <div class="pos-total-row">
                            <span>Subtotal</span><span id="subtotal">₦0.00</span>
                        </div>
                        <div class="pos-total-row">
                            <span>Tax ({{ config('pos.tax_rate', 0) }}%)</span>
                            <span id="taxAmount">₦0.00</span>
                        </div>
                        <div class="pos-total-row grand">
                            <span>Total</span><span id="grandTotal">₦0.00</span>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="pos-payment-group">
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
                    <button class="pos-charge-btn" id="completeOrder">
                        <i class="bi bi-printer-fill me-2"></i>
                        <span>Charge</span>
                        <span class="pos-charge-total" id="chargeBtnTotal">₦0.00</span>
                    </button>

                    <!-- Shortcuts -->
                    <div class="pos-shortcuts">
                        <span><kbd>F1</kbd> Search</span>
                        <span><kbd>F2</kbd> Clear</span>
                        <span><kbd>F3</kbd> Charge</span>
                        <span><kbd>F4</kbd> Hold</span>
                        <span><kbd>F6</kbd> Grid/List</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════
     QUANTITY / UNIT MODAL (same as pos.index)
══════════════════════════════════════════ -->
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

                <!-- Unit Selection (unit mode) -->
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
                        <small class="text-success fw-semibold ms-2" id="previousQtyText"></small>
                    </label>
                    <div class="pos-qty-stepper">
                        <button type="button" id="decreaseQty" class="pos-step-btn" aria-label="Decrease">
                            <i class="bi bi-dash-lg"></i>
                        </button>
                        <input type="number" id="modalQty" class="pos-qty-input"
                               min="1" value="1" step="1" autofocus aria-label="Quantity">
                        <button type="button" id="increaseQty" class="pos-step-btn" aria-label="Increase">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Unit Amount Input (unit mode) -->
                <div id="unitInputSection" class="d-none">
                    <label class="form-label small fw-semibold text-muted mb-1">Amount</label>
                    <div class="pos-qty-stepper">
                        <button type="button" id="decreaseUnit" class="pos-step-btn" disabled aria-label="Decrease">
                            <i class="bi bi-dash-lg"></i>
                        </button>
                        <input type="number" id="modalUnit" class="pos-qty-input"
                               min="0.001" value="1" step="0.001" disabled aria-label="Unit amount">
                        <button type="button" id="increaseUnit" class="pos-step-btn" disabled aria-label="Increase">
                            <i class="bi bi-plus-lg"></i>
                        </button>
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
                    <div class="col-6">
                        <label class="form-label small">First Name *</label>
                        <input type="text" class="form-control" id="firstName" required>
                    </div>
                    <div class="col-6">
                        <label class="form-label small">Last Name *</label>
                        <input type="text" class="form-control" id="lastName" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Phone</label>
                        <input type="tel" class="form-control" id="phoneNumber">
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Email</label>
                        <input type="email" class="form-control" id="email">
                    </div>
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

<!-- Offline Orders Modal -->
<div class="modal fade" id="offlineOrdersModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-white"><i class="bi bi-wifi-off me-2"></i>Offline Orders</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>These orders will sync when you're back online.</div>
                <div id="offlineOrdersList"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button class="btn btn-warning" id="syncOfflineOrdersBtn" style="display:none;"><i class="bi bi-cloud-arrow-up me-1"></i>Sync Now</button>
            </div>
        </div>
    </div>
</div>

<!-- Accessibility live regions -->
<div class="visually-hidden" role="status" aria-live="polite" id="cartStatus"></div>
<div class="visually-hidden" role="status" aria-live="polite" id="searchStatus"></div>

<script src="{{ asset('theme/layouts/assets/libs/axios/axios.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ══════════════════════════════════════
    // CLOCK
    // ══════════════════════════════════════
    function updateClock() {
        const now = new Date();
        document.getElementById('posClock').textContent =
            now.toLocaleTimeString('en-NG', { hour: '2-digit', minute: '2-digit' });
    }
    updateClock();
    setInterval(updateClock, 30000);

    // ══════════════════════════════════════
    // REFS
    // ══════════════════════════════════════
    const input             = document.getElementById('barcodeInput');
    const productGrid       = document.getElementById('productGrid');
    const emptyState        = document.getElementById('emptyState');
    const cartBody          = document.getElementById('cartBody');
    const emptyCartState    = document.getElementById('emptyCartState');
    const cartItemsWrap     = document.getElementById('cartItemsWrap');
    const subtotalEl        = document.getElementById('subtotal');
    const taxAmountEl       = document.getElementById('taxAmount');
    const grandTotalEl      = document.getElementById('grandTotal');
    const chargeBtnTotal    = document.getElementById('chargeBtnTotal');
    const discountValueEl   = document.getElementById('discountValue');
    const discountTypeEl    = document.getElementById('discountType');
    const modalQty          = document.getElementById('modalQty');
    const modalUnit         = document.getElementById('modalUnit');
    const confirmAddBtn     = document.getElementById('confirmAddBtn');
    const removeFromCartBtn = document.getElementById('removeFromCartBtn');
    const customerSelect    = document.getElementById('customerSelect');
    const pricePerUnitInput = document.getElementById('pricePerUnit');
    const unitSelect        = document.getElementById('unitSelect');
    const catBar            = document.getElementById('catBar');

    // State
    let cart                    = [];
    let allProducts             = [];           // all loaded / searched products
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
    let activeCategoryFilter    = 'all';
    let availableUnits          = [];
    let selectedUnit            = null;
    let currentMeasurementType  = 'quantity';
    let originalPricePerUnit    = 0;
    let isPriceInputActive      = false;

    input.focus();

    // ══════════════════════════════════════
    // INIT
    // ══════════════════════════════════════
    function initializeApp() {
        quantityModal      = new bootstrap.Modal(document.getElementById('quantityModal'));
        quickCustomerModal = new bootstrap.Modal(document.getElementById('quickCustomerModal'));
        thankYouAudio      = new Audio('/audio/thank-you-sweet-man-235977.mp3');
        thankYouAudio.preload = 'auto';

        setupQuantityModal();
        initQuickCustomer();

        document.addEventListener('click', function (e) {
            if (
                !e.target.closest('.modal') &&
                !e.target.closest('#customerSelect') &&
                !e.target.closest('#discountValue') &&
                !e.target.closest('.pos-prod-card') &&
                !e.target.closest('.pos-cat-bar') &&
                e.target.id !== 'barcodeInput'
            ) {
                input.focus();
            }
        });

        loadInitialProducts();
    }

    // ══════════════════════════════════════
    // INITIAL PRODUCTS
    // ══════════════════════════════════════
    async function loadInitialProducts() {
        try {
            const res = await axios.get('{{ route("pos.initial-products") }}');
            const products = res.data.products || [];
            products.forEach(p => {
                if (!allProducts.find(x => x.id === p.id)) allProducts.push(p);
            });
            buildCategoryPills();
            renderGrid();
        } catch (e) {
            renderGrid(); // show empty
        }
    }

    // ══════════════════════════════════════
    // BARCODE INPUT
    // ══════════════════════════════════════
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const val = input.value.trim();
            if (val) processBarcode(val);
        }
    });

    input.addEventListener('input', debounce(() => {
        const q = input.value.trim();
        currentSearchQuery = q;
        if (q.length >= 2 && !isLikelyBarcode(q)) {
            searchProducts(q);
        } else if (q.length === 0) {
            buildCategoryPills();
            renderGrid();
        }
    }, 300));

    async function processBarcode(barcode) {
        input.value = '';
        showSpinner(true);
        try {
            const res = await axios.get('{{ route("pos.search") }}', { params: { q: barcode } });
            const products = res.data || [];
            if (products.length > 0) {
                const p = products[0];
                mergeProduct(p);
                const btn = makeTempBtn(p);
                openQuantityModal(btn);
                renderGrid();
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
        if (v.length >= 8 && v.length <= 14 && /^\d+$/.test(v)) return true;
        if (v.startsWith('PROD') && v.length > 10) return true;
        return false;
    }

    function playScanSound() {
        try {
            const ctx  = new (window.AudioContext || window.webkitAudioContext)();
            const osc  = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain); gain.connect(ctx.destination);
            osc.frequency.value = 800; osc.type = 'sine';
            gain.gain.setValueAtTime(0.3, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.01, ctx.currentTime + 0.1);
            osc.start(ctx.currentTime); osc.stop(ctx.currentTime + 0.1);
        } catch (e) {}
    }

    // ══════════════════════════════════════
    // SEARCH
    // ══════════════════════════════════════
    function searchProducts(q) {
        showSpinner(true);
        axios.get('{{ route("pos.search") }}', { params: { q } })
            .then(res => {
                showSpinner(false);
                const newProds = res.data || [];
                newProds.forEach(p => mergeProduct(p));
                buildCategoryPills();
                renderGrid();
            })
            .catch(() => { showSpinner(false); showToast('Search failed', 'error'); });
    }

    function mergeProduct(p) {
        const idx = allProducts.findIndex(x => x.id === p.id);
        if (idx === -1) allProducts.push(p);
        else allProducts[idx] = p;
    }

    function makeTempBtn(p) {
        const btn = document.createElement('button');
        btn.dataset.product   = JSON.stringify(p).replace(/'/g, "&apos;");
        btn.dataset.productId = p.id;
        return btn;
    }

    // ══════════════════════════════════════
    // CATEGORY PILLS
    // ══════════════════════════════════════
    function buildCategoryPills() {
        const cats = ['all', ...new Set(allProducts.map(p => p.category || '').filter(Boolean))];
        // Remove old dynamic pills
        catBar.querySelectorAll('.pos-cat-pill:not([data-cat="all"])').forEach(el => el.remove());
        // Reset All pill
        const allPill = catBar.querySelector('[data-cat="all"]');
        if (allPill) allPill.className = 'pos-cat-pill' + (activeCategoryFilter === 'all' ? ' active' : '');

        cats.slice(1).forEach(cat => {
            const btn = document.createElement('button');
            btn.className = 'pos-cat-pill' + (cat === activeCategoryFilter ? ' active' : '');
            btn.dataset.cat = cat;
            btn.textContent = cat.charAt(0).toUpperCase() + cat.slice(1);
            catBar.appendChild(btn);
        });

        catBar.querySelectorAll('.pos-cat-pill').forEach(pill => {
            pill.onclick = () => {
                activeCategoryFilter = pill.dataset.cat;
                catBar.querySelectorAll('.pos-cat-pill').forEach(p => p.classList.remove('active'));
                pill.classList.add('active');
                renderGrid();
            };
        });
    }

    // ══════════════════════════════════════
    // RENDER GRID
    // ══════════════════════════════════════
    function renderGrid() {
        let products = activeCategoryFilter === 'all'
            ? allProducts
            : allProducts.filter(p => (p.category || '') === activeCategoryFilter);

        // Sort: in-cart products first, then by name
        products = [...products].sort((a, b) => {
            const aInCart = cart.some(i => i.product_id == a.id);
            const bInCart = cart.some(i => i.product_id == b.id);
            if (aInCart && !bInCart) return -1;
            if (!aInCart && bInCart) return 1;
            return (a.title || '').localeCompare(b.title || '');
        });

        productGrid.innerHTML = '';

        if (products.length === 0) {
            emptyState.classList.remove('d-none');
            return;
        }
        emptyState.classList.add('d-none');

        products.forEach(p => {
            const price    = p.sale_price || p.price;
            const cartItem = cart.find(i => i.product_id == p.id);
            const inCart   = !!cartItem;
            const outOfStock = p.stock <= 0;
            const stockClass = p.stock > 10 ? 'good' : p.stock > 0 ? 'low' : 'out';

            const card = document.createElement('div');
            card.className = `pos-prod-card${inCart ? ' in-cart' : ''}${outOfStock ? ' out-of-stock' : ''}`;
            card.setAttribute('data-product-id', p.id);
            card.setAttribute('role', 'button');
            card.setAttribute('tabindex', outOfStock ? '-1' : '0');
            card.setAttribute('aria-label', `${p.title}, ${formatCurrency(price)}${outOfStock ? ', out of stock' : ''}`);

            card.innerHTML = `
                ${inCart ? `<div class="prod-cart-badge"><i class="bi bi-check2"></i>${formatQty(cartItem.qty, cartItem.is_unit_mode)}</div>` : ''}
                <div class="prod-stock-pip ${stockClass}" title="Stock: ${formatNum(p.stock)}"></div>
                <div class="prod-img-wrap">
                    ${p.thumbnail
                        ? `<img src="${p.thumbnail}" alt="${p.title}" loading="lazy">`
                        : `<div class="prod-img-placeholder"><i class="bi bi-box-seam"></i></div>`}
                    ${outOfStock ? `<div class="prod-out-overlay"><span>Out of Stock</span></div>` : ''}
                </div>
                <div class="prod-info">
                    <div class="prod-name" title="${p.title}">${p.title}</div>
                    <div class="prod-price">${formatCurrency(price)}</div>
                    <div class="prod-meta">${p.primary_unit || 'Unit'} · ${p.sku}</div>
                </div>
                ${inCart ? `<div class="prod-in-cart-bar">In Cart</div>` : ''}
            `;

            if (!outOfStock) {
                const handleClick = () => {
                    const btn = makeTempBtn(p);
                    openQuantityModal(btn);
                };
                card.addEventListener('click', handleClick);
                card.addEventListener('keydown', e => {
                    if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); handleClick(); }
                });
            }

            productGrid.appendChild(card);
        });
    }

    // ══════════════════════════════════════
    // QUANTITY MODAL (full feature parity)
    // ══════════════════════════════════════
    function setupQuantityModal() {
        const qm = document.getElementById('quantityModal');

        qm.addEventListener('show.bs.modal', switchToQuantityMode);
        qm.addEventListener('shown.bs.modal', () => {
            setTimeout(() => {
                if (currentMeasurementType === 'quantity') { modalQty.focus(); modalQty.select(); }
                else if (selectedUnit) { modalUnit.focus(); modalUnit.select(); }
                else unitSelect.focus();
            }, 100);
        });
        qm.addEventListener('hidden.bs.modal', () => {
            setTimeout(() => { input.focus(); }, 100);
        });

        document.getElementById('measureQuantity').addEventListener('change', function() {
            if (this.checked) { switchToQuantityMode(); updateModalTotal(); }
        });
        document.getElementById('measureUnit').addEventListener('change', function() {
            if (this.checked) { switchToUnitMode(); updateModalTotal(); }
        });

        unitSelect.addEventListener('change', function() {
            selectedUnit = availableUnits.find(u => u.id == this.value);
            if (selectedUnit) {
                modalUnit.disabled = false;
                document.getElementById('decreaseUnit').disabled = false;
                document.getElementById('increaseUnit').disabled = false;
                pricePerUnitInput.disabled = false;
                modalUnit.focus(); modalUnit.select();
                updateUnitLabel(); updateModalTotal(); updateAmountDisplay();
            } else {
                modalUnit.disabled = pricePerUnitInput.disabled =
                document.getElementById('decreaseUnit').disabled =
                document.getElementById('increaseUnit').disabled = true;
                modalUnit.value = 1; pricePerUnitInput.value = '';
                updateModalTotal(); updateAmountDisplay();
            }
        });

        modalQty.addEventListener('input', () => {
            const q = parseInt(modalQty.value) || 1;
            if (!isPriceInputActive) pricePerUnitInput.value = (q * originalPricePerUnit).toFixed(2);
            updateModalTotal(); updateAmountDisplay();
        });
        modalUnit.addEventListener('input', () => {
            if (selectedUnit) {
                const a = parseFloat(modalUnit.value) || 1;
                if (!isPriceInputActive) pricePerUnitInput.value = (a * originalPricePerUnit).toFixed(2);
                updateModalTotal(); updateAmountDisplay();
            }
        });

        pricePerUnitInput.addEventListener('focus', () => isPriceInputActive = true);
        pricePerUnitInput.addEventListener('blur',  () => isPriceInputActive = false);
        pricePerUnitInput.addEventListener('input', () => {
            const total = parseFloat(pricePerUnitInput.value) || 0;
            if (originalPricePerUnit > 0 && total > 0) {
                const equiv = total / originalPricePerUnit;
                if (currentMeasurementType === 'quantity') modalQty.value = Math.round(equiv);
                else modalUnit.value = equiv.toFixed(3);
                updateModalTotal(); updateAmountDisplay();
            }
        });
        pricePerUnitInput.addEventListener('blur', () => {
            const total = parseFloat(pricePerUnitInput.value) || 0;
            if (total === 0 && currentProduct) {
                pricePerUnitInput.value = originalPricePerUnit.toFixed(2);
                if (currentMeasurementType === 'quantity') modalQty.value = 1;
                else if (selectedUnit) modalUnit.value = 1;
                updateModalTotal(); updateAmountDisplay();
            }
        });

        document.getElementById('increaseQty').addEventListener('click', () => {
            modalQty.value = (parseInt(modalQty.value)||1)+1;
            pricePerUnitInput.value = (parseInt(modalQty.value)*originalPricePerUnit).toFixed(2);
            updateModalTotal(); updateAmountDisplay(); modalQty.focus(); modalQty.select();
        });
        document.getElementById('decreaseQty').addEventListener('click', () => {
            const v = parseInt(modalQty.value)||1;
            if (v > 1) {
                modalQty.value = v-1;
                pricePerUnitInput.value = (parseInt(modalQty.value)*originalPricePerUnit).toFixed(2);
                updateModalTotal(); updateAmountDisplay(); modalQty.focus(); modalQty.select();
            }
        });
        document.getElementById('increaseUnit').addEventListener('click', () => {
            if (selectedUnit) {
                modalUnit.value = (parseFloat(modalUnit.value)+getUnitStep()).toFixed(3);
                pricePerUnitInput.value = (parseFloat(modalUnit.value)*originalPricePerUnit).toFixed(2);
                updateModalTotal(); updateAmountDisplay(); modalUnit.focus(); modalUnit.select();
            }
        });
        document.getElementById('decreaseUnit').addEventListener('click', () => {
            if (selectedUnit) {
                const c = parseFloat(modalUnit.value)||1;
                if (c > getUnitStep()) {
                    modalUnit.value = (c - getUnitStep()).toFixed(3);
                    pricePerUnitInput.value = (parseFloat(modalUnit.value)*originalPricePerUnit).toFixed(2);
                    updateModalTotal(); updateAmountDisplay(); modalUnit.focus(); modalUnit.select();
                }
            }
        });

        document.querySelectorAll('.quick-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (currentMeasurementType === 'quantity') {
                    modalQty.value = this.dataset.value;
                    pricePerUnitInput.value = (parseInt(modalQty.value)*originalPricePerUnit).toFixed(2);
                    modalQty.focus(); modalQty.select();
                } else if (selectedUnit) {
                    modalUnit.value = this.dataset.value;
                    pricePerUnitInput.value = (parseFloat(modalUnit.value)*originalPricePerUnit).toFixed(2);
                    modalUnit.focus(); modalUnit.select();
                } else { showToast('Select a unit first', 'warning'); }
                updateModalTotal(); updateAmountDisplay();
            });
        });

        document.querySelectorAll('.unit-quick-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                if (selectedUnit) {
                    modalUnit.value = this.dataset.value;
                    pricePerUnitInput.value = (parseFloat(modalUnit.value)*originalPricePerUnit).toFixed(2);
                    updateModalTotal(); updateAmountDisplay(); modalUnit.focus(); modalUnit.select();
                } else { showToast('Select a unit first', 'warning'); }
            });
        });

        document.getElementById('rememberUnitPreference').addEventListener('change', function() {
            if (this.checked && currentProduct && selectedUnit) saveUnitPref();
        });

        modalQty.addEventListener('keydown',          e => { if (e.key==='Enter') { e.preventDefault(); confirmAddBtn.click(); } });
        modalUnit.addEventListener('keydown',         e => { if (e.key==='Enter') { e.preventDefault(); confirmAddBtn.click(); } });
        pricePerUnitInput.addEventListener('keydown', e => { if (e.key==='Enter') { e.preventDefault(); confirmAddBtn.click(); } });

        confirmAddBtn.addEventListener('click', addOrUpdateCart);
        removeFromCartBtn.addEventListener('click', removeCurrentFromCart);
    }

    function switchToQuantityMode() {
        currentMeasurementType = 'quantity';
        document.getElementById('measurementLabel').textContent        = 'Quantity';
        document.getElementById('quantityInputSection').style.display  = 'block';
        document.getElementById('unitInputSection').classList.add('d-none');
        document.getElementById('unitSelectionWrap').classList.add('d-none');
        document.getElementById('unitQuickButtons').classList.add('d-none');
        document.getElementById('priceInputSection').classList.add('d-none');
        document.getElementById('quantityQuickButtons').classList.remove('d-none');
        modalQty.disabled = false;
        document.getElementById('decreaseQty').disabled = false;
        document.getElementById('increaseQty').disabled = false;
        if (currentProduct) {
            const price = currentProduct.sale_price || currentProduct.price;
            originalPricePerUnit = price;
            pricePerUnitInput.value = (price * (parseInt(modalQty.value)||1)).toFixed(2);
            document.getElementById('originalPriceText').textContent = `Per unit: ${formatCurrency(price)}`;
        }
        updateModalTotal(); updateAmountDisplay();
    }

    function switchToUnitMode() {
        currentMeasurementType = 'unit';
        document.getElementById('measurementLabel').textContent        = 'Amount';
        document.getElementById('quantityInputSection').style.display  = 'none';
        document.getElementById('unitInputSection').classList.remove('d-none');
        document.getElementById('unitSelectionWrap').classList.remove('d-none');
        document.getElementById('unitQuickButtons').classList.remove('d-none');
        document.getElementById('priceInputSection').classList.remove('d-none');
        document.getElementById('quantityQuickButtons').classList.add('d-none');
        loadProductUnits();
        modalUnit.disabled = true;
        document.getElementById('decreaseUnit').disabled = true;
        document.getElementById('increaseUnit').disabled = true;
        pricePerUnitInput.disabled = true;
        if (currentProduct) {
            const price = currentProduct.sale_price || currentProduct.price;
            originalPricePerUnit = price;
            pricePerUnitInput.value = (price * (parseFloat(modalUnit.value)||1)).toFixed(2);
            const label = selectedUnit ? `Per ${selectedUnit.short_name}: ${formatCurrency(price)}` : `Per unit: ${formatCurrency(price)}`;
            document.getElementById('originalPriceText').textContent = label;
        }
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

    function getUnitStep() {
        if (!selectedUnit) return 0.001;
        const sn = selectedUnit.short_name.toLowerCase();
        if (sn.includes('g') && !sn.includes('kg')) return 1;
        return 0.001;
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

    async function loadProductUnits() {
        if (!currentProduct) return;
        try {
            const res = await axios.get(
                `{{ route('api.product.units', ['product'=>'__ID__']) }}`.replace('__ID__', currentProduct.id)
            );
            availableUnits = res.data.units || [];
            unitSelect.innerHTML = '<option value="">-- Select unit --</option>';
            if (availableUnits.length > 0) {
                const savedPref = getSavedUnitPref(currentProduct.id);
                availableUnits.forEach(u => {
                    const opt = new Option(`${u.name} (${u.short_name})`, u.id);
                    if (savedPref && savedPref.unitId == u.id) { opt.selected = true; selectedUnit = u; }
                    else if (u.is_default && !selectedUnit) { opt.selected = true; selectedUnit = u; }
                    unitSelect.appendChild(opt);
                });
                if (selectedUnit) {
                    modalUnit.disabled = pricePerUnitInput.disabled =
                    document.getElementById('decreaseUnit').disabled =
                    document.getElementById('increaseUnit').disabled = false;
                }
                if (savedPref) document.getElementById('rememberUnitPreference').checked = true;
                updateUnitLabel(); updateAmountDisplay();
            } else {
                selectedUnit = { id:1, name: currentProduct.primary_unit||'Unit', short_name: currentProduct.primary_unit||'unit', conversion_factor:1, is_default:true };
                unitSelect.appendChild(new Option(`${selectedUnit.name} (${selectedUnit.short_name})`, selectedUnit.id, true, true));
                modalUnit.disabled = pricePerUnitInput.disabled =
                document.getElementById('decreaseUnit').disabled =
                document.getElementById('increaseUnit').disabled = false;
                updateUnitLabel();
            }
        } catch (e) {
            selectedUnit = { id:1, name: currentProduct.primary_unit||'Unit', short_name: currentProduct.primary_unit||'unit', conversion_factor:1 };
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

    function openQuantityModal(btn) {
        try {
            const product   = JSON.parse(btn.dataset.product);
            const productId = btn.dataset.productId;
            currentProduct  = product;
            availableUnits  = []; selectedUnit = null; isPriceInputActive = false;

            // Refresh to top of list
            const idx = allProducts.findIndex(p => p.id == productId);
            if (idx !== -1) {
                const [moved] = allProducts.splice(idx, 1);
                allProducts.unshift(moved);
            }

            const cartItem   = cart.find(i => i.product_id == productId);
            const prevQty    = cartItem ? cartItem.qty : (productQuantityCache[productId] || 1);
            const price      = product.sale_price || product.price;

            // Fill modal header
            document.getElementById('modalProductLabel').textContent  = product.title;
            document.getElementById('modalProductPrice').textContent  = formatCurrency(price);
            document.getElementById('modalProductStock').textContent  = `Stock: ${formatNum(product.stock, 0)}`;
            document.getElementById('modalProductUnit').textContent   = product.primary_unit || 'Unit';

            // Thumbnail
            const thumb = document.getElementById('modalThumb');
            if (product.thumbnail) {
                thumb.innerHTML = `<img src="${product.thumbnail}" alt="${product.title}" style="width:100%;height:100%;object-fit:cover;border-radius:10px;">`;
            } else {
                thumb.innerHTML = '<i class="bi bi-box-seam"></i>';
            }

            // Unit mode availability
            const hasUnits = product.units && product.units.length > 0;
            document.getElementById('measureUnit').disabled = !hasUnits;

            // Restore saved preference
            const savedPref = getSavedUnitPref(productId);
            if (savedPref && hasUnits) {
                document.getElementById('measureUnit').checked = true;
                switchToUnitMode();
            } else {
                document.getElementById('measureQuantity').checked = true;
                switchToQuantityMode();
            }

            if (currentMeasurementType === 'quantity') {
                modalQty.value = prevQty;
                pricePerUnitInput.value = (price * (parseInt(prevQty)||1)).toFixed(2);
            } else {
                modalUnit.value = prevQty;
                pricePerUnitInput.value = (price * (parseFloat(prevQty)||1)).toFixed(2);
            }

            document.getElementById('previousQtyText').textContent = cartItem
                ? `In cart: ${formatQty(prevQty, cartItem.is_unit_mode)}${cartItem.unit_short_name ? ' '+cartItem.unit_short_name : ''}`
                : `Prev: ${formatQty(prevQty)}`;
            document.getElementById('previousQtyText').className = cartItem ? 'text-success fw-semibold ms-2' : 'text-muted ms-2';

            removeFromCartBtn.style.display = cartItem ? 'inline-block' : 'none';
            confirmAddBtn.textContent = '';
            confirmAddBtn.innerHTML   = cartItem
                ? '<i class="bi bi-cart-check me-1"></i>Update Cart'
                : '<i class="bi bi-cart-plus me-1"></i>Add to Cart';

            originalPricePerUnit = price;
            updateModalTotal(); updateAmountDisplay();
            quantityModal.show();
        } catch (e) {
            console.error(e);
            showToast('Error loading product', 'error');
        }
    }

    // ══════════════════════════════════════
    // ADD / UPDATE CART
    // ══════════════════════════════════════
    function addOrUpdateCart() {
        if (!currentProduct) return;
        if (currentProduct.stock <= 0) { showToast(`${currentProduct.title} is out of stock`, 'error'); quantityModal.hide(); return; }
        if (currentMeasurementType === 'unit' && !selectedUnit) { showToast('Select a unit first', 'warning'); unitSelect.focus(); return; }

        const isUnitMode = currentMeasurementType === 'unit';
        let quantity     = isUnitMode ? (parseFloat(modalUnit.value)||0.001) : (parseInt(modalQty.value)||1);
        const totalPrice = parseFloat(pricePerUnitInput.value) || 0;
        let unitId = currentProduct.primary_unit_id || 1;
        let unitName = currentProduct.primary_unit || 'Unit';
        let unitShortName = unitName;

        if (isUnitMode) {
            if (quantity <= 0) { showToast('Amount must be > 0', 'warning'); return; }
            if (quantity > currentProduct.stock) {
                showToast(`Only ${formatNum(currentProduct.stock, 3)} ${selectedUnit.short_name} available`, 'warning');
                modalUnit.value = currentProduct.stock.toFixed(3); updateModalTotal(); return;
            }
            unitId = selectedUnit.id; unitName = selectedUnit.name; unitShortName = selectedUnit.short_name;
        } else {
            if (quantity < 1) { showToast('Qty must be ≥ 1', 'warning'); return; }
            if (quantity > currentProduct.stock) {
                showToast(`Only ${formatNum(currentProduct.stock)} units available`, 'warning');
                modalQty.value = currentProduct.stock; updateModalTotal(); return;
            }
        }

        const p = currentProduct;
        mergeProduct({...p});

        const data = {
            product_id: p.id, title: p.title,
            price: totalPrice, unit_price: originalPricePerUnit, qty: quantity,
            unit_name: unitName, unit_short_name: unitShortName, unit_id: parseInt(unitId),
            sku: p.sku, barcode: p.barcode, thumbnail: p.thumbnail,
            discount_type: 'percent', discount_value: 0, discounted_price: originalPricePerUnit,
            is_unit_mode: isUnitMode, original_unit: isUnitMode ? selectedUnit : null,
            price_per_unit: originalPricePerUnit,
        };

        const existing = cart.find(i => i.product_id == p.id);
        if (existing) Object.assign(existing, data);
        else cart.push(data);

        productQuantityCache[p.id] = quantity;
        quantityModal.hide();
        renderCartAndTotals();
        renderGrid();
        currentProduct = null;
        showToast('Added to cart', 'success');
    }

    function removeCurrentFromCart() {
        if (!currentProduct) return;
        Swal.fire({ title: 'Remove?', text: `Remove ${currentProduct.title} from cart?`, icon:'warning', showCancelButton:true, confirmButtonText:'Yes, remove' })
            .then(r => {
                if (r.isConfirmed) {
                    const id = currentProduct.id;
                    cart = cart.filter(i => i.product_id != id);
                    delete productQuantityCache[id];
                    quantityModal.hide(); renderCartAndTotals(); renderGrid();
                    showToast('Removed from cart', 'success');
                }
            });
    }

    // ══════════════════════════════════════
    // CART RENDER
    // ══════════════════════════════════════
    function renderCartAndTotals() {
        if (cart.length === 0) {
            emptyCartState.classList.remove('d-none');
            cartItemsWrap.classList.add('d-none');
            cartBody.innerHTML = '';
            subtotalEl.textContent = grandTotalEl.textContent = taxAmountEl.textContent = chargeBtnTotal.textContent = formatCurrency(0);
            document.getElementById('discountApplied').classList.add('d-none');
            return;
        }

        emptyCartState.classList.add('d-none');
        cartItemsWrap.classList.remove('d-none');
        cartBody.innerHTML = '';
        let subtotal = 0;

        cart.forEach((item, i) => {
            let unitPrice = item.price_per_unit || item.price / item.qty;
            let discPrice = unitPrice;
            if (item.discount_value > 0) {
                discPrice = item.discount_type === 'percent'
                    ? unitPrice * (1 - item.discount_value/100)
                    : unitPrice - (item.discount_value/item.qty);
                discPrice = Math.max(0, discPrice);
                item.discounted_price = discPrice;
            } else {
                item.discounted_price = unitPrice;
            }
            const total = discPrice * item.qty;
            subtotal += total;
            const displayUnit = item.unit_short_name || item.unit_name || 'Unit';

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>
                    <div class="cart-item-name">${item.title}</div>
                    ${item.discount_value > 0 ? `<span class="cart-disc-badge">-${formatNum(item.discount_value,2)}${item.discount_type==='percent'?'%':'₦'}</span>` : ''}
                </td>
                <td class="text-center">
                    <button class="cart-qty-btn qty-btn-cart"
                            data-product='${JSON.stringify(item).replace(/'/g,"&apos;")}'
                            data-product-id="${item.product_id}">
                        ${formatQty(item.qty, item.is_unit_mode)} ${displayUnit}
                    </button>
                </td>
                <td class="text-end">${formatCurrency(discPrice)}</td>
                <td class="text-end fw-bold">${formatCurrency(total)}</td>
                <td>
                    <div class="d-flex gap-1 justify-content-end">
                        <button class="cart-action-btn disc item-discount-btn" data-product-id="${item.product_id}" title="Discount">
                            <i class="bi bi-percent"></i>
                        </button>
                        <button class="cart-action-btn del remove-cart-item-btn" data-index="${i}" title="Remove">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </td>
            `;
            cartBody.appendChild(tr);

            tr.querySelector('.qty-btn-cart').addEventListener('click', function() { openQuantityModal(this); });
            tr.querySelector('.item-discount-btn').addEventListener('click', function() {
                const pid = this.dataset.productId;
                currentItemIndex = cart.findIndex(x => x.product_id === pid);
                if (currentItemIndex === -1) return;
                const ci = cart[currentItemIndex];
                document.getElementById('itemName').textContent      = ci.title;
                document.getElementById('itemDiscountValue').value   = ci.discount_value || 0;
                document.getElementById('itemDiscountType').value    = ci.discount_type || 'percent';
                new bootstrap.Modal(document.getElementById('itemDiscountModal')).show();
            });
            tr.querySelector('.remove-cart-item-btn').addEventListener('click', function() {
                const idx = parseInt(this.dataset.index);
                const it = cart[idx];
                cart.splice(idx, 1);
                delete productQuantityCache[it.product_id];
                renderCartAndTotals(); renderGrid();
                showToast('Removed', 'success');
            });
        });

        const taxRate   = {{ config('pos.tax_rate', 0) }};
        const taxAmt    = taxRate > 0 ? (subtotal * taxRate)/100 : 0;
        let orderDisc   = 0;
        if (orderDiscountValue > 0) {
            orderDisc = orderDiscountType === 'percent' ? (subtotal * orderDiscountValue)/100 : orderDiscountValue;
            orderDisc = Math.min(orderDisc, subtotal);
        }
        const grand = Math.max(0, subtotal + taxAmt - orderDisc);

        subtotalEl.textContent    = formatCurrency(subtotal);
        taxAmountEl.textContent   = formatCurrency(taxAmt);
        grandTotalEl.textContent  = formatCurrency(grand);
        chargeBtnTotal.textContent = formatCurrency(grand);

        if (orderDiscountValue > 0) {
            document.getElementById('discountApplied').classList.remove('d-none');
            document.getElementById('discountAmountLabel').textContent = `-${formatCurrency(orderDisc)}`;
        } else {
            document.getElementById('discountApplied').classList.add('d-none');
        }

        window.currentDiscount = { type: orderDiscountType, value: orderDiscountValue, amount: orderDisc };
    }

    // ══════════════════════════════════════
    // DISCOUNT
    // ══════════════════════════════════════
    document.getElementById('applyDiscountBtn').addEventListener('click', () => {
        orderDiscountValue = parseFloat(discountValueEl.value) || 0;
        orderDiscountType  = discountTypeEl.value;
        if (orderDiscountType === 'percent' && orderDiscountValue > 100) {
            orderDiscountValue = 100; discountValueEl.value = 100;
            showToast('Max 100%', 'warning'); return;
        }
        renderCartAndTotals();
        showToast('Discount applied', 'success');
    });

    document.getElementById('applyItemDiscountBtn').addEventListener('click', function() {
        if (currentItemIndex === null) return;
        const val  = parseFloat(document.getElementById('itemDiscountValue').value) || 0;
        const type = document.getElementById('itemDiscountType').value;
        if (type === 'percent' && val > 100) { showToast('Max 100%', 'warning'); return; }
        cart[currentItemIndex].discount_type  = type;
        cart[currentItemIndex].discount_value = val;
        renderCartAndTotals(); renderGrid();
        bootstrap.Modal.getInstance(document.getElementById('itemDiscountModal')).hide();
        showToast('Item discount applied', 'success');
    });

    // ══════════════════════════════════════
    // CLEAR / HOLD / LOAD
    // ══════════════════════════════════════
    document.getElementById('clearCart').addEventListener('click', () => {
        if (cart.length === 0) { showToast('Cart is empty', 'info'); return; }
        Swal.fire({ title:'Clear Cart?', text:'Remove all items?', icon:'warning', showCancelButton:true, confirmButtonText:'Yes, clear' })
            .then(r => { if (r.isConfirmed) { cart = []; productQuantityCache = {}; renderCartAndTotals(); renderGrid(); showToast('Cart cleared','success'); } });
    });

    document.getElementById('holdOrderBtn').addEventListener('click', () => {
        if (cart.length === 0) { showToast('Cart is empty','info'); return; }
        const held = JSON.parse(localStorage.getItem('heldOrders')||'[]');
        held.push({ id:Date.now(), cart:JSON.parse(JSON.stringify(cart)), customer:customerSelect.value,
            allProducts:JSON.parse(JSON.stringify(allProducts)), productQuantityCache:JSON.parse(JSON.stringify(productQuantityCache)),
            discount:{type:orderDiscountType,value:orderDiscountValue}, time:new Date().toLocaleString(), timestamp:Date.now() });
        localStorage.setItem('heldOrders', JSON.stringify(held));
        cart = []; productQuantityCache = {}; orderDiscountValue = 0; discountValueEl.value = '0';
        renderCartAndTotals(); renderGrid();
        showToast('Order held!', 'success');
    });

    document.getElementById('loadHeldBtn').addEventListener('click', () => {
        const held    = JSON.parse(localStorage.getItem('heldOrders')||'[]');
        const list    = document.getElementById('heldOrdersList');
        const noOrds  = document.getElementById('noHeldOrders');
        if (held.length === 0) { list.innerHTML=''; noOrds.style.display='block'; }
        else {
            noOrds.style.display = 'none';
            let html = '<div class="list-group">';
            held.sort((a,b)=>b.timestamp-a.timestamp).forEach(o => {
                const total = o.cart.reduce((s,it) => s + it.price, 0);
                html += `<div class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                    <div><strong>${o.time}</strong><br><small>${o.cart.length} item(s) — ${formatCurrency(total)}</small></div>
                    <div class="btn-group">
                        <button class="btn btn-sm btn-outline-primary load-order-btn" data-order-id="${o.id}">Load</button>
                        <button class="btn btn-sm btn-outline-danger remove-order-btn" data-order-id="${o.id}"><i class="bi bi-trash"></i></button>
                    </div></div>`;
            });
            html += '</div>';
            list.innerHTML = html;
            list.querySelectorAll('.load-order-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    const order = JSON.parse(localStorage.getItem('heldOrders')||'[]').find(o=>o.id==btn.dataset.orderId);
                    if (!order) return;
                    if (cart.length > 0) {
                        Swal.fire({title:'Replace Cart?',text:'This will replace your current cart.',icon:'warning',showCancelButton:true,confirmButtonText:'Replace'})
                            .then(r => { if (r.isConfirmed) doLoadOrder(order); });
                    } else doLoadOrder(order);
                });
            });
            list.querySelectorAll('.remove-order-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    Swal.fire({title:'Remove order?',icon:'warning',showCancelButton:true,confirmButtonText:'Yes'}).then(r => {
                        if (r.isConfirmed) {
                            const remaining = JSON.parse(localStorage.getItem('heldOrders')||'[]').filter(o=>o.id!=btn.dataset.orderId);
                            localStorage.setItem('heldOrders',JSON.stringify(remaining));
                            document.getElementById('loadHeldBtn').click();
                            showToast('Order removed','success');
                        }
                    });
                });
            });
        }
        new bootstrap.Modal(document.getElementById('loadOrderModal')).show();
    });

    function doLoadOrder(order) {
        cart = JSON.parse(JSON.stringify(order.cart));
        allProducts = order.allProducts ? JSON.parse(JSON.stringify(order.allProducts)) : allProducts;
        productQuantityCache = order.productQuantityCache ? JSON.parse(JSON.stringify(order.productQuantityCache)) : {};
        if (order.customer) customerSelect.value = order.customer;
        if (order.discount) { orderDiscountType = order.discount.type; orderDiscountValue = order.discount.value; discountTypeEl.value = order.discount.type; discountValueEl.value = order.discount.value; }
        renderCartAndTotals(); renderGrid(); buildCategoryPills();
        bootstrap.Modal.getInstance(document.getElementById('loadOrderModal')).hide();
        showToast('Order loaded!', 'success');
    }

    // ══════════════════════════════════════
    // COMPLETE ORDER
    // ══════════════════════════════════════
    document.getElementById('completeOrder').addEventListener('click', completeOrder);

    async function completeOrder() {
        if (cart.length === 0) { showToast('Cart is empty', 'warning'); return; }
        if (isProcessingOrder) { showToast('Already processing…','info'); return; }

        const payment    = document.querySelector('input[name="payment"]:checked').value;
        const customerId = customerSelect.value || null;
        const offline    = document.getElementById('offlineModeToggle').checked || !navigator.onLine;

        if (offline) {
            const offlineOrders = JSON.parse(localStorage.getItem('offlineOrders')||'[]');
            const oid = 'OFFLINE-' + Date.now();
            offlineOrders.push({
                id: oid, items: cart.map(i => ({
                    product_id: i.product_id, qty: parseFloat(i.qty), unit_id: parseInt(i.unit_id||1),
                    sale_price: parseFloat(i.unit_price||i.price), discount_type: i.discount_type||null,
                    discount_value: i.discount_value||0, is_unit_mode: i.is_unit_mode||false, unit_name: i.unit_name||null,
                })),
                payment_method: payment, customer_id: customerId, discount_type: orderDiscountType,
                discount_value: orderDiscountValue, discount_amount: window.currentDiscount?.amount||0,
                tax_rate: {{ config('pos.tax_rate', 0) }}, timestamp: Date.now(), time: new Date().toLocaleString(),
            });
            localStorage.setItem('offlineOrders', JSON.stringify(offlineOrders));
            cart = []; productQuantityCache = {}; orderDiscountValue = 0; discountValueEl.value = '0';
            renderCartAndTotals(); renderGrid();
            Swal.fire({title:'Saved Offline!', html:`<div class="text-center"><i class="bi bi-wifi-off text-warning display-1 mb-3"></i><h4>Order #${oid}</h4><p>Will sync when online.</p></div>`, icon:'warning', confirmButtonText:'OK'});
            return;
        }

        isProcessingOrder = true;
        const btn = document.getElementById('completeOrder');
        btn.disabled = true; btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing…';

        const items = cart.map(i => ({
            product_id: i.product_id, qty: parseFloat(i.qty), unit_id: parseInt(i.unit_id||1),
            sale_price: parseFloat(i.unit_price||i.price/i.qty), discount_type: i.discount_type||null,
            discount_value: i.discount_value||0, is_unit_mode: i.is_unit_mode||false, unit_name: i.unit_name||null,
        }));
        const disc = window.currentDiscount || { type:'percent', value:0, amount:0 };

        Swal.fire({ title:'Processing…', allowOutsideClick:false, didOpen:()=>Swal.showLoading() });

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
                    buttonsStyling:false, customClass:{ confirmButton:'btn btn-success btn-lg me-2', cancelButton:'btn btn-outline-secondary btn-lg' }
                }).then(r => {
                    if (r.isConfirmed) {
                        printWindow = window.open(`/pos/receipt/${res.data.order_id}`, '_blank');
                        if (printWindow) {
                            const interval = setInterval(() => {
                                if (printWindow.closed) { clearInterval(interval); resetAfterOrder(); }
                            }, 500);
                        } else resetAfterOrder();
                    } else resetAfterOrder();
                });
            } else {
                Swal.fire('Error', res.data.message||'Failed to process order', 'error');
            }
        } catch (err) {
            Swal.close();
            let msg = 'Failed to complete order.';
            if (err.response?.data?.errors) msg = Object.values(err.response.data.errors).flat().join('<br>');
            else if (err.response?.data?.message) msg = err.response.data.message;
            Swal.fire('Error', msg, 'error');
        } finally {
            isProcessingOrder = false;
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-printer-fill me-2"></i><span>Charge</span><span class="pos-charge-total" id="chargeBtnTotal">' + grandTotalEl.textContent + '</span>';
        }
    }

    function resetAfterOrder() {
        cart = []; allProducts = []; productQuantityCache = {};
        orderDiscountValue = 0; discountValueEl.value = '0';
        renderCartAndTotals();
        input.value = ''; input.focus();
        loadInitialProducts();
        showToast('New order started', 'info');
    }

    // ══════════════════════════════════════
    // QUICK CUSTOMER
    // ══════════════════════════════════════
    document.getElementById('quickCustomerBtn').addEventListener('click', () => quickCustomerModal.show());

    function initQuickCustomer() {
        document.getElementById('saveQuickCustomerBtn').addEventListener('click', async () => {
            const fn = document.getElementById('firstName').value.trim();
            const ln = document.getElementById('lastName').value.trim();
            const ph = document.getElementById('phoneNumber').value.trim();
            const em = document.getElementById('email').value.trim();
            if (!fn || !ln) { showToast('Name required', 'warning'); return; }
            const btn = document.getElementById('saveQuickCustomerBtn');
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving…'; btn.disabled = true;
            try {
                const res = await axios.post('{{ route("customers.quick") }}', { first_name:fn, last_name:ln, phone_number:ph, email:em, _token:'{{ csrf_token() }}' });
                if (res.data.success) {
                    const opt = new Option(`${fn} ${ln}${ph?' - '+ph:''}`, res.data.customer.id);
                    customerSelect.appendChild(opt); customerSelect.value = res.data.customer.id;
                    quickCustomerModal.hide();
                    document.getElementById('quickCustomerModal').querySelector('form')?.reset();
                    ['firstName','lastName','phoneNumber','email'].forEach(id => { const el=document.getElementById(id); if(el) el.value=''; });
                    showToast('Customer added', 'success');
                } else { showToast(res.data.message||'Failed', 'error'); }
            } catch (e) {
                let msg = 'Failed to add customer';
                if (e.response?.data?.message) msg = e.response.data.message;
                showToast(msg, 'error');
            } finally { btn.innerHTML = 'Save Customer'; btn.disabled = false; }
        });
    }

    // ══════════════════════════════════════
    // OFFLINE
    // ══════════════════════════════════════
    function updateConnStatus(online) {
        const el = document.getElementById('connectionStatus');
        el.className = 'pos-conn-badge ' + (online ? 'online' : 'offline');
        el.innerHTML = online ? '<i class="bi bi-wifi"></i><span>Online</span>' : '<i class="bi bi-wifi-off"></i><span>Offline</span>';
        document.getElementById('offlineModeToggle').checked = !online;
    }
    updateConnStatus(navigator.onLine);
    window.addEventListener('online',  () => { updateConnStatus(true);  showToast('Back online!','success'); syncOfflineOrders(); });
    window.addEventListener('offline', () => { updateConnStatus(false); showToast('You\'re offline','warning'); });

    async function syncOfflineOrders() {
        const orders = JSON.parse(localStorage.getItem('offlineOrders')||'[]');
        if (!orders.length) return;
        let synced = 0, failed = 0, lastId = null;
        for (const o of [...orders]) {
            try {
                const r = await axios.post('{{ route("pos.order.save") }}', { ...o, _token:'{{ csrf_token() }}' });
                if (r.data.success) { synced++; lastId = r.data.order_id; const rem = JSON.parse(localStorage.getItem('offlineOrders')||'[]').filter(x=>x.id!==o.id); localStorage.setItem('offlineOrders',JSON.stringify(rem)); }
                else failed++;
            } catch { failed++; }
        }
        if (synced > 0) {
            Swal.fire({ title:`${synced} Order${synced>1?'s':''} Synced!`, text:'Offline orders submitted.', icon:'success',
                showCancelButton:!!lastId, confirmButtonText:'Print Last Receipt', cancelButtonText:'Dismiss' })
                .then(r => { if (r.isConfirmed && lastId) window.open(`/pos/receipt/${lastId}`, '_blank'); });
        }
        if (failed > 0) showToast(`${failed} order(s) failed to sync`, 'error', 4000);
    }

    // ══════════════════════════════════════
    // KEYBOARD SHORTCUTS
    // ══════════════════════════════════════
    document.addEventListener('keydown', function(e) {
        if (e.target.tagName==='INPUT'||e.target.tagName==='TEXTAREA'||e.target.isContentEditable) return;
        switch(e.key) {
            case 'F1': e.preventDefault(); input.focus(); input.select(); break;
            case 'F2': e.preventDefault(); document.getElementById('clearCart').click(); break;
            case 'F3': e.preventDefault(); completeOrder(); break;
            case 'F4': e.preventDefault(); document.getElementById('holdOrderBtn').click(); break;
            case 'F5': e.preventDefault(); document.getElementById('loadHeldBtn').click(); break;
            case 'F6': e.preventDefault(); window.location.href = '{{ route("pos.index") }}'; break;
            case 'Escape': if(cart.length>0) document.getElementById('clearCart').click(); break;
        }
    });

    // ══════════════════════════════════════
    // UTILITIES
    // ══════════════════════════════════════
    function showSpinner(show) {
        document.getElementById('searchSpinner').classList.toggle('d-none', !show);
    }

    function formatNum(n, dec = 0) {
        n = parseFloat(n) || 0;
        return new Intl.NumberFormat('en-NG', { minimumFractionDigits: dec, maximumFractionDigits: dec }).format(n);
    }
    function formatCurrency(n) { return '₦' + formatNum(n, 2); }
    function formatQty(qty, isUnit = false) { return formatNum(qty, isUnit ? 2 : 0); }

    function showToast(message, type = 'success', duration = 2000) {
        Swal.mixin({ toast:true, position:'top-end', showConfirmButton:false, timer:duration, timerProgressBar:true })
            .fire({ icon:type, title:message });
    }

    function debounce(fn, delay) {
        let t;
        return function(...args) { clearTimeout(t); t = setTimeout(() => fn.apply(this,args), delay); };
    }

    // ══════════════════════════════════════
    // BOOT
    // ══════════════════════════════════════
    initializeApp();
    renderCartAndTotals();
});
</script>

<style>
/* ═══════════════════════════════════════════════════════
   POS GRID PAGE — FULL LAYOUT STYLES
   Aesthetic: Clean Supermarket / Retail Terminal
   Font: DM Sans (body) + DM Mono (numbers/prices)
═══════════════════════════════════════════════════════ */
@import url('https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap');

.pos-grid-page {
    font-family: 'DM Sans', sans-serif;
    --pos-bg:          #f4f5f7;
    --pos-surface:     #ffffff;
    --pos-border:      #e1e4e8;
    --pos-topbar-bg:   #1a1d23;
    --pos-topbar-text: #e8eaed;
    --pos-accent:      #2563eb;
    --pos-accent-dark: #1d4ed8;
    --pos-green:       #16a34a;
    --pos-green-light: #dcfce7;
    --pos-amber:       #d97706;
    --pos-red:         #dc2626;
    --pos-price-font:  'DM Mono', monospace;
    --pos-radius:      12px;
    --pos-radius-sm:   8px;
    --pos-shadow:      0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.06);
    --pos-shadow-md:   0 4px 12px rgba(0,0,0,.10), 0 2px 4px rgba(0,0,0,.06);
    background: var(--pos-bg);
    height: 100vh;
    overflow: hidden;
}

.pos-grid-layout {
    display: flex;
    flex-direction: column;
    height: 100vh;
    overflow: hidden;
}

/* ─── TOPBAR ─── */
.pos-topbar {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 0 20px;
    height: 56px;
    background: var(--pos-topbar-bg);
    flex-shrink: 0;
    z-index: 100;
}
.pos-topbar-left { display: flex; align-items: center; gap: 12px; min-width: 220px; }
.pos-topbar-right { display: flex; align-items: center; gap: 12px; min-width: 200px; justify-content: flex-end; }
.pos-topbar-center { flex: 1; }
.pos-topbar-title { color: var(--pos-topbar-text); font-weight: 600; font-size: .9rem; white-space: nowrap; }
.pos-topbar-divider { width: 1px; height: 24px; background: rgba(255,255,255,.15); }

.pos-back-btn {
    display: flex; align-items: center; gap: 6px;
    color: rgba(255,255,255,.7); text-decoration: none;
    font-size: .8rem; font-weight: 500;
    padding: 5px 10px; border-radius: 6px;
    border: 1px solid rgba(255,255,255,.15);
    transition: all .2s;
    white-space: nowrap;
}
.pos-back-btn:hover { color: #fff; background: rgba(255,255,255,.1); border-color: rgba(255,255,255,.3); }
.pos-back-btn i { font-size: .95rem; }

.pos-search-wrap {
    position: relative;
    display: flex; align-items: center;
    max-width: 600px; margin: 0 auto;
}
.pos-search-icon {
    position: absolute; left: 14px;
    color: rgba(255,255,255,.4); font-size: 1.1rem; pointer-events: none; z-index: 2;
}
.pos-search-input {
    width: 100%; height: 38px;
    background: rgba(255,255,255,.08);
    border: 1px solid rgba(255,255,255,.18);
    border-radius: 8px; color: #fff;
    padding: 0 80px 0 44px;
    font-size: .9rem; font-family: 'DM Sans', sans-serif;
    outline: none; transition: all .2s;
}
.pos-search-input::placeholder { color: rgba(255,255,255,.4); }
.pos-search-input:focus { background: rgba(255,255,255,.14); border-color: rgba(99,155,255,.6); box-shadow: 0 0 0 3px rgba(37,99,235,.3); }
.pos-search-spinner { position: absolute; right: 42px; }
.pos-search-kbd {
    position: absolute; right: 12px;
    font-size: .7rem; color: rgba(255,255,255,.4);
    background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.15);
    border-radius: 4px; padding: 1px 6px;
    font-family: 'DM Mono', monospace;
}

.pos-conn-badge {
    display: flex; align-items: center; gap: 5px;
    font-size: .75rem; font-weight: 600; padding: 4px 10px;
    border-radius: 20px; white-space: nowrap;
}
.pos-conn-badge.online  { background: rgba(22,163,74,.2); color: #4ade80; }
.pos-conn-badge.offline { background: rgba(220,38,38,.2); color: #f87171; animation: blink 1.5s infinite; }

.pos-clock { color: rgba(255,255,255,.6); font-family: 'DM Mono', monospace; font-size: .85rem; }

/* ─── BODY ─── */
.pos-body {
    display: flex; flex: 1; overflow: hidden; gap: 0;
}

/* ─── LEFT: PRODUCTS PANEL ─── */
.pos-products-panel {
    flex: 1; display: flex; flex-direction: column;
    overflow: hidden; padding: 16px 12px 16px 16px;
    gap: 12px;
}

/* CATEGORY PILLS */
.pos-cat-bar {
    display: flex; align-items: center; gap: 8px;
    flex-wrap: nowrap; overflow-x: auto;
    padding-bottom: 4px;
    scrollbar-width: none;
}
.pos-cat-bar::-webkit-scrollbar { display: none; }
.pos-cat-pill {
    flex-shrink: 0; padding: 6px 14px; border-radius: 20px;
    font-size: .8rem; font-weight: 600; cursor: pointer;
    border: 1.5px solid var(--pos-border);
    background: var(--pos-surface); color: #6b7280;
    transition: all .15s; white-space: nowrap;
}
.pos-cat-pill:hover { border-color: var(--pos-accent); color: var(--pos-accent); }
.pos-cat-pill.active {
    background: var(--pos-accent); color: #fff;
    border-color: var(--pos-accent);
    box-shadow: 0 2px 8px rgba(37,99,235,.3);
}

/* PRODUCT GRID */
.pos-product-grid {
    flex: 1; overflow-y: auto;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(135px, 1fr));
    gap: 10px;
    padding-right: 4px;
    align-content: start;
}
.pos-product-grid::-webkit-scrollbar { width: 6px; }
.pos-product-grid::-webkit-scrollbar-track { background: transparent; }
.pos-product-grid::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }

/* PRODUCT CARD */
.pos-prod-card {
    background: var(--pos-surface);
    border: 1.5px solid var(--pos-border);
    border-radius: var(--pos-radius);
    cursor: pointer;
    position: relative;
    display: flex; flex-direction: column; align-items: center;
    padding: 10px 8px 8px;
    gap: 6px;
    transition: transform .12s, border-color .12s, box-shadow .12s;
    user-select: none;
    overflow: hidden;
    min-height: 160px;
}
.pos-prod-card:hover:not(.out-of-stock) {
    border-color: var(--pos-accent);
    box-shadow: 0 4px 14px rgba(37,99,235,.16);
    transform: translateY(-2px);
}
.pos-prod-card:active:not(.out-of-stock) { transform: translateY(0); box-shadow: none; }
.pos-prod-card:focus-visible { outline: 2px solid var(--pos-accent); outline-offset: 2px; }

.pos-prod-card.in-cart {
    border-color: var(--pos-green);
    background: #f0fdf4;
}
.pos-prod-card.in-cart:hover {
    border-color: #15803d;
    box-shadow: 0 4px 14px rgba(22,163,74,.18);
}

.pos-prod-card.out-of-stock {
    opacity: .45; cursor: not-allowed;
    filter: grayscale(.7);
}
.pos-prod-card.out-of-stock:hover { transform: none; box-shadow: none; border-color: var(--pos-border); }

/* Cart badge */
.prod-cart-badge {
    position: absolute; top: 5px; left: 5px;
    background: var(--pos-green); color: #fff;
    font-size: 10px; font-weight: 700;
    padding: 2px 7px; border-radius: 20px;
    display: flex; align-items: center; gap: 2px;
    z-index: 2;
}

/* Stock pip */
.prod-stock-pip {
    position: absolute; top: 7px; right: 7px;
    width: 8px; height: 8px; border-radius: 50%;
}
.prod-stock-pip.good  { background: #22c55e; }
.prod-stock-pip.low   { background: #f59e0b; }
.prod-stock-pip.out   { background: #ef4444; }

/* Product image */
.prod-img-wrap {
    width: 68px; height: 68px;
    border-radius: 10px; overflow: hidden;
    background: #f3f4f6;
    display: flex; align-items: center; justify-content: center;
    position: relative; flex-shrink: 0;
    margin-top: 4px;
}
.prod-img-wrap img { width:100%; height:100%; object-fit:cover; }
.prod-img-placeholder { font-size: 28px; color: #d1d5db; }
.prod-out-overlay {
    position: absolute; inset: 0;
    background: rgba(0,0,0,.45); display:flex; align-items:center; justify-content:center;
    border-radius: 10px;
}
.prod-out-overlay span { color: #fff; font-size: 9px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; }

/* Product info */
.prod-info { width: 100%; text-align: center; }
.prod-name {
    font-size: 11.5px; font-weight: 600; color: #111827;
    line-height: 1.3; max-height: 2.6em; overflow: hidden;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;
    margin-bottom: 3px;
}
.prod-price {
    font-family: var(--pos-price-font); font-size: 13px; font-weight: 700;
    color: var(--pos-green);
}
.prod-meta { font-size: 9.5px; color: #9ca3af; margin-top: 2px; }

.prod-in-cart-bar {
    width: calc(100% + 16px); margin: 0 -8px -8px;
    background: var(--pos-green); color: #fff;
    font-size: 9.5px; font-weight: 700; text-align: center;
    padding: 3px 0; letter-spacing: .5px; text-transform: uppercase;
    margin-top: auto;
}

/* Skeletons */
.pos-prod-skeleton {
    border-radius: var(--pos-radius);
    background: var(--pos-surface);
    border: 1.5px solid var(--pos-border);
    padding: 12px 10px;
    display: flex; flex-direction: column; align-items: center; gap: 8px;
    min-height: 160px;
}
.skel-img { width: 68px; height: 68px; border-radius: 10px; background: #e5e7eb; animation: shimmer 1.5s infinite; }
.skel-line { height: 10px; border-radius: 5px; background: #e5e7eb; animation: shimmer 1.5s infinite; }
.skel-line.long  { width: 80%; }
.skel-line.short { width: 50%; }

/* Empty state */
.pos-empty-state {
    flex: 1; display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    color: #9ca3af; padding: 40px;
}
.pos-empty-state i { font-size: 3rem; margin-bottom: 12px; color: #d1d5db; }
.pos-empty-state h5 { font-size: 1rem; font-weight: 600; color: #6b7280; }
.pos-empty-state p { font-size: .85rem; }

/* ─── RIGHT: ORDER PANEL ─── */
.pos-order-panel {
    width: 380px; flex-shrink: 0;
    background: var(--pos-surface);
    border-left: 1px solid var(--pos-border);
    display: flex; flex-direction: column;
    overflow: hidden;
    padding: 0;
}

/* Customer Row */
.pos-customer-row {
    display: flex; align-items: center; gap: 6px;
    padding: 12px 14px;
    border-bottom: 1px solid var(--pos-border);
    flex-shrink: 0;
}
.pos-customer-select-wrap {
    flex: 1; position: relative;
    display: flex; align-items: center;
}
.pos-customer-icon {
    position: absolute; left: 10px; font-size: 1.1rem; color: #9ca3af;
    pointer-events: none; z-index: 2;
}
.pos-customer-select {
    width: 100%; padding: 7px 10px 7px 34px;
    border: 1px solid var(--pos-border); border-radius: var(--pos-radius-sm);
    font-size: .82rem; font-family: 'DM Sans', sans-serif;
    appearance: none; background: #f9fafb; color: #374151;
    cursor: pointer; outline: none;
    transition: border-color .2s;
}
.pos-customer-select:focus { border-color: var(--pos-accent); background: #fff; }

.pos-icon-btn {
    width: 34px; height: 34px; flex-shrink: 0;
    border: 1px solid var(--pos-border); border-radius: var(--pos-radius-sm);
    background: #f9fafb; color: #6b7280;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: .95rem;
    transition: all .15s;
}
.pos-icon-btn:hover       { background: var(--pos-accent); color: #fff; border-color: var(--pos-accent); }
.pos-icon-btn.warning:hover { background: #f59e0b; color: #fff; border-color: #f59e0b; }
.pos-icon-btn.info:hover    { background: #0ea5e9; color: #fff; border-color: #0ea5e9; }
.pos-icon-btn.danger:hover  { background: var(--pos-red); color: #fff; border-color: var(--pos-red); }

/* Cart Area */
.pos-cart-area {
    flex: 1; overflow-y: auto; padding: 0;
}
.pos-cart-area::-webkit-scrollbar { width: 4px; }
.pos-cart-area::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 2px; }

.pos-cart-empty {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    height: 100%; color: #9ca3af; gap: 6px; padding: 32px;
}
.pos-cart-empty-icon { font-size: 3.5rem; color: #e5e7eb; }
.pos-cart-empty p { font-size: .95rem; font-weight: 600; color: #6b7280; margin: 0; }
.pos-cart-empty small { font-size: .8rem; }

/* Cart Table */
.pos-cart-table { width: 100%; border-collapse: collapse; font-size: .82rem; }
.pos-cart-table thead tr { background: #f9fafb; border-bottom: 2px solid var(--pos-border); }
.pos-cart-table thead th { padding: 8px 10px; font-weight: 600; color: #6b7280; text-transform: uppercase; font-size: .7rem; letter-spacing: .5px; }
.pos-cart-table tbody tr { border-bottom: 1px solid #f3f4f6; transition: background .12s; }
.pos-cart-table tbody tr:hover { background: #fafafa; }
.pos-cart-table td { padding: 8px 10px; vertical-align: middle; }

.cart-item-name { font-weight: 600; color: #111827; font-size: .82rem; line-height: 1.3; }
.cart-disc-badge {
    display: inline-block; font-size: 9px; background: #fef3c7; color: #92400e;
    border-radius: 4px; padding: 1px 5px; margin-top: 2px; font-weight: 600;
}
.cart-qty-btn {
    background: #eff6ff; border: 1px solid #dbeafe; border-radius: 6px;
    color: var(--pos-accent); font-size: .78rem; font-weight: 600;
    padding: 3px 8px; cursor: pointer; white-space: nowrap;
    transition: all .12s;
}
.cart-qty-btn:hover { background: var(--pos-accent); color: #fff; border-color: var(--pos-accent); }

.cart-action-btn {
    width: 26px; height: 26px; border-radius: 6px; cursor: pointer;
    border: 1px solid var(--pos-border); background: #f9fafb; color: #9ca3af;
    display: flex; align-items: center; justify-content: center; font-size: .75rem;
    transition: all .12s;
}
.cart-action-btn.disc:hover { background: #fef3c7; border-color: #f59e0b; color: #d97706; }
.cart-action-btn.del:hover  { background: #fee2e2; border-color: #ef4444; color: #dc2626; }

/* Discount Row */
.pos-discount-row {
    padding: 10px 14px;
    border-top: 1px solid var(--pos-border);
    background: #fafafa;
    display: flex; align-items: center; gap: 8px; flex-wrap: wrap;
    flex-shrink: 0;
}
.pos-discount-label { font-size: .8rem; font-weight: 600; color: #374151; white-space: nowrap; }
.pos-discount-inputs { display: flex; gap: 4px; align-items: center; flex: 1; }
.pos-discount-input {
    flex: 1; height: 32px; border: 1px solid var(--pos-border); border-radius: 6px;
    padding: 0 8px; font-size: .82rem; font-family: var(--pos-price-font);
    text-align: right; outline: none; background: #fff;
    transition: border-color .15s;
}
.pos-discount-input:focus { border-color: var(--pos-accent); }
.pos-discount-select {
    height: 32px; border: 1px solid var(--pos-border); border-radius: 6px;
    padding: 0 4px; font-size: .8rem; background: #fff; outline: none; cursor: pointer;
}
.pos-discount-apply {
    height: 32px; width: 32px; border: 1px solid var(--pos-accent);
    border-radius: 6px; background: var(--pos-accent); color: #fff;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    font-size: .9rem; transition: background .15s;
}
.pos-discount-apply:hover { background: var(--pos-accent-dark); }
.pos-discount-applied { font-size: .78rem; font-weight: 700; color: var(--pos-red); white-space: nowrap; }

/* Totals */
.pos-totals {
    padding: 10px 14px;
    border-top: 1px solid var(--pos-border);
    flex-shrink: 0;
}
.pos-total-row {
    display: flex; justify-content: space-between; align-items: center;
    font-size: .83rem; color: #6b7280; padding: 3px 0;
}
.pos-total-row span:last-child { font-family: var(--pos-price-font); }
.pos-total-row.grand {
    margin-top: 6px; padding-top: 8px; border-top: 2px solid #111827;
    font-size: 1.05rem; font-weight: 700; color: #111827;
}
.pos-total-row.grand span:last-child { font-size: 1.15rem; color: var(--pos-green); }

/* Payment Group */
.pos-payment-group {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 6px;
    padding: 10px 14px; flex-shrink: 0;
}
.pos-payment-opt { cursor: pointer; }
.pos-payment-opt input { display: none; }
.pos-payment-opt span {
    display: flex; align-items: center; justify-content: center; gap: 5px;
    padding: 8px 4px; border-radius: var(--pos-radius-sm);
    border: 1.5px solid var(--pos-border);
    font-size: .78rem; font-weight: 600; color: #6b7280;
    background: #f9fafb; transition: all .15s;
}
.pos-payment-opt:hover span { border-color: var(--pos-accent); color: var(--pos-accent); background: #eff6ff; }
.pos-payment-opt input:checked + span {
    border-color: var(--pos-green); background: var(--pos-green-light);
    color: var(--pos-green); box-shadow: 0 1px 4px rgba(22,163,74,.2);
}

/* Charge Button */
.pos-charge-btn {
    margin: 0 14px 10px;
    width: calc(100% - 28px);
    height: 54px; border: none; border-radius: var(--pos-radius);
    background: linear-gradient(135deg, #16a34a 0%, #15803d 100%);
    color: #fff; font-family: 'DM Sans', sans-serif; font-weight: 700;
    font-size: 1rem; cursor: pointer; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center; gap: 8px;
    box-shadow: 0 4px 12px rgba(22,163,74,.35);
    transition: all .15s;
}
.pos-charge-btn:hover:not(:disabled) {
    background: linear-gradient(135deg, #15803d 0%, #166534 100%);
    box-shadow: 0 6px 16px rgba(22,163,74,.4); transform: translateY(-1px);
}
.pos-charge-btn:active { transform: translateY(0); }
.pos-charge-btn:disabled { opacity: .6; cursor: not-allowed; transform: none; }
.pos-charge-total {
    font-family: var(--pos-price-font);
    background: rgba(255,255,255,.2); border-radius: 6px;
    padding: 2px 10px; font-size: 1.05rem;
}

/* Shortcuts */
.pos-shortcuts {
    display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;
    padding: 6px 14px 10px;
    flex-shrink: 0;
}
.pos-shortcuts span { font-size: .7rem; color: #9ca3af; display: flex; align-items: center; gap: 3px; }
.pos-shortcuts kbd {
    font-size: .65rem; background: #f3f4f6; border: 1px solid #e5e7eb;
    border-radius: 3px; padding: 1px 4px;
    font-family: 'DM Mono', monospace; color: #6b7280;
}

/* ─── MODAL STYLES ─── */
.pos-modal-content { border-radius: 16px; overflow: hidden; }
.pos-modal-header {
    background: linear-gradient(135deg, #1e40af 0%, #2563eb 100%);
    color: #fff; padding: 16px 20px;
}
.pos-modal-header .btn-close { filter: brightness(0) invert(1); opacity: .8; }
.pos-modal-product-thumb {
    width: 52px; height: 52px; border-radius: 10px;
    background: rgba(255,255,255,.2); flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem; color: rgba(255,255,255,.8);
    overflow: hidden;
}

.pos-measure-toggle {
    display: flex; gap: 8px;
}
.pos-measure-opt { flex: 1; cursor: pointer; }
.pos-measure-opt input { display: none; }
.pos-measure-opt span {
    display: flex; align-items: center; justify-content: center; gap: 5px;
    padding: 8px; border-radius: 8px; border: 1.5px solid var(--pos-border);
    font-size: .82rem; font-weight: 600; color: #6b7280; background: #f9fafb;
    transition: all .15s;
}
.pos-measure-opt input:checked + span { border-color: var(--pos-accent); background: #eff6ff; color: var(--pos-accent); }
.pos-measure-opt:has(input:disabled) span { opacity: .4; cursor: not-allowed; }

.pos-qty-stepper {
    display: flex; align-items: center; gap: 8px;
}
.pos-step-btn {
    width: 44px; height: 50px; border: 1.5px solid var(--pos-border);
    border-radius: 10px; background: #f9fafb; color: #374151; font-size: 1.1rem;
    cursor: pointer; transition: all .12s; flex-shrink: 0;
    display: flex; align-items: center; justify-content: center;
}
.pos-step-btn:hover:not(:disabled) { background: var(--pos-accent); color: #fff; border-color: var(--pos-accent); }
.pos-step-btn:disabled { opacity: .4; cursor: not-allowed; }
.pos-qty-input {
    flex: 1; height: 50px; border: 1.5px solid var(--pos-border);
    border-radius: 10px; text-align: center;
    font-size: 1.6rem; font-weight: 700; font-family: var(--pos-price-font);
    color: #111827; background: #fff; outline: none;
    transition: border-color .15s;
}
.pos-qty-input:focus { border-color: var(--pos-accent); box-shadow: 0 0 0 3px rgba(37,99,235,.12); }

.pos-price-box { background: #f9fafb; border-radius: 10px; padding: 12px; }

.pos-quick-btns {
    display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px;
}
.pos-quick-btn {
    padding: 8px 4px; border-radius: 8px; border: 1.5px solid var(--pos-border);
    background: #fff; color: #374151; font-size: .85rem; font-weight: 600;
    cursor: pointer; transition: all .12s; font-family: 'DM Sans', sans-serif;
}
.pos-quick-btn:hover { background: var(--pos-accent); color: #fff; border-color: var(--pos-accent); }
.unit-quick-btn { border-color: #dcfce7; color: var(--pos-green); }
.unit-quick-btn:hover { background: var(--pos-green); border-color: var(--pos-green); color: #fff; }

/* Skeletons shimmer */
@keyframes shimmer {
    0%   { background-color: #e5e7eb; }
    50%  { background-color: #f3f4f6; }
    100% { background-color: #e5e7eb; }
}
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:.5} }

/* ─── RESPONSIVE ─── */
@media (max-width: 1024px) {
    .pos-order-panel { width: 320px; }
    .pos-product-grid { grid-template-columns: repeat(auto-fill, minmax(115px, 1fr)); }
}
@media (max-width: 768px) {
    .pos-body { flex-direction: column; }
    .pos-products-panel { flex: 0 0 55vh; }
    .pos-order-panel { width: 100%; border-left: none; border-top: 1px solid var(--pos-border); flex: 1; }
    .pos-product-grid { grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); }
    .pos-topbar-right { display: none; }
}
@media (max-width: 480px) {
    .pos-product-grid { grid-template-columns: repeat(3, 1fr); }
    .pos-topbar { padding: 0 12px; }
    .pos-back-btn span { display: none; }
}

/* Scrollbar global (products panel) */
.pos-products-panel ::-webkit-scrollbar { width: 5px; }
.pos-products-panel ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
</style>
@endsection
