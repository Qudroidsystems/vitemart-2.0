@extends('layouts.master')

@section('title', $pagetitle ?? 'Point of Sale')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Point of Sale (POS)</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                                <li class="breadcrumb-item active">POS</li>
                            </ol>
                        </div>
                        <div class="mt-2 mt-sm-0">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="offlineModeToggle">
                                <label class="form-check-label" for="offlineModeToggle">Offline Mode</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Left: Product Search -->
                <div class="col-xxl-8">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-4">Search or Scan Products</h5>

                            <div class="position-relative mb-4">
                                <input type="text" id="barcodeInput" class="form-control form-control-lg fs-3"
                                       placeholder="Scan barcode or search..." autofocus autocomplete="off"
                                       aria-label="Search or scan products">
                                <i class="bi bi-upc-scan position-absolute top-50 end-0 translate-middle-y me-4 fs-2 text-muted"></i>
                            </div>

                            <div class="d-flex align-items-center mb-3">
                                <div class="me-3">
                                    <small class="text-muted">Shortcuts:</small>
                                    <span class="badge bg-secondary ms-1">F1</span> Focus Search
                                    <span class="badge bg-secondary ms-2">F2</span> Clear Cart
                                    <span class="badge bg-secondary ms-2">F3</span> Complete Order
                                    <span class="badge bg-secondary ms-2">F4</span> Hold Order
                                </div>
                                <div class="ms-auto">
                                    <span id="connectionStatus" class="badge bg-success">
                                        <i class="bi bi-wifi"></i> Online
                                    </span>
                                </div>
                            </div>

                            <div class="table-responsive flex-grow-1 position-relative">
                                <!-- Loading Overlay -->
                                <div id="searchLoading" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none" style="z-index: 10;">
                                    <div class="d-flex justify-content-center align-items-center h-100">
                                        <div class="text-center">
                                            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <h5 class="text-primary">Searching products...</h5>
                                        </div>
                                    </div>
                                </div>

                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-primary sticky-top" style="z-index: 1;">
                                        <tr>
                                            <th>Product</th>
                                            <th class="text-center">Stock</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Price</th>
                                            <th class="text-center">Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody id="resultsBody">
                                        <tr id="emptySearchRow">
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="bi bi-search display-1 mb-4 text-light"></i>
                                                <h5>Ready to sell</h5>
                                                <p class="mb-0">Type or scan to find products</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right: Order Summary -->
                <div class="col-xxl-4">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">Order Summary</h5>
                            <div>
                                <button class="btn btn-sm btn-warning me-2" id="holdOrderBtn" title="Hold order for later">
                                    <i class="bi bi-pause-circle"></i> Hold
                                </button>
                                <button class="btn btn-sm btn-info me-2" id="loadHeldBtn" title="Load held orders">
                                    <i class="bi bi-folder-symlink"></i> Load
                                </button>
                                <button class="btn btn-sm btn-danger" id="clearCart" title="Clear cart">
                                    <i class="bi bi-trash"></i> Clear
                                </button>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <!-- Customer Selection -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold d-flex justify-content-between align-items-center">
                                    <span>Customer</span>
                                    <a href="javascript:void(0)" class="text-decoration-none fs-6"
                                       data-bs-toggle="tooltip" data-bs-title="Quick customer management"
                                       id="quickCustomerBtn">
                                        <i class="bi bi-person-plus"></i>
                                    </a>
                                </label>
                                <div class="position-relative">
                                    <select class="form-select form-select-lg customer-select-dropdown"
                                            id="customerSelect"
                                            style="padding-right: 40px; z-index: 1000;">
                                        <option value="">Walk-in Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">
                                                {{ $customer->first_name }} {{ $customer->last_name }}
                                                @if($customer->phone_number)
                                                    - {{ $customer->phone_number }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="position-absolute top-50 end-0 translate-middle-y me-3">
                                        <i class="bi bi-chevron-down text-muted"></i>
                                    </div>
                                </div>
                                <small class="text-muted mt-1 d-block">
                                    <span id="customerCount">{{ count($customers) }}</span> customers available
                                </small>
                            </div>

                            <div class="flex-grow-1 mb-4">
                                <h6 class="fw-bold mb-3">Cart Items</h6>
                                <div class="table-responsive" style="max-height: 350px;">
                                    <table class="table table-sm">
                                        <thead class="table-dark sticky-top">
                                            <tr>
                                                <th>#</th>
                                                <th>Item</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-end">Price</th>
                                                <th class="text-end">Total</th>
                                                <th>Disc</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="cartBody">
                                            <tr id="emptyCartRow">
                                                <td colspan="7" class="text-center py-5 text-muted">
                                                    <i class="bi bi-cart fs-1 mb-3"></i>
                                                    No items in cart
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Order-Level Discount -->
                            <div class="border-top pt-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold">Order Discount</span>
                                    <div class="input-group input-group-sm" style="width: 180px;">
                                        <input type="number" id="discountValue" class="form-control text-end"
                                               placeholder="0" min="0" step="0.01" aria-label="Discount amount">
                                        <select id="discountType" class="form-select" aria-label="Discount type">
                                            <option value="fixed">₦</option>
                                            <option value="percent" selected>%</option>
                                        </select>
                                        <button class="btn btn-outline-primary" type="button" id="applyDiscountBtn"
                                                title="Apply discount">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between text-danger fw-bold">
                                    <span>Discount Applied</span>
                                    <span id="discountAmount">-₦0.00</span>
                                </div>
                            </div>

                            <div class="border-top pt-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal</span>
                                    <span id="subtotal">₦0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tax ({{ config('pos.tax_rate', 0) }}%)</span>
                                    <span id="taxAmount">₦0.00</span>
                                </div>
                                <hr class="my-3">
                                <div class="d-flex justify-content-between fs-3 fw-bold text-success">
                                    <span>Grand Total</span>
                                    <span id="grandTotal">₦0.00</span>
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="form-label fw-bold">Payment Method</label>
                                <div class="btn-group w-100 mb-4" role="group" aria-label="Payment methods">
                                    <input type="radio" class="btn-check" name="payment" id="cash" value="cash" checked>
                                    <label class="btn btn-outline-success w-100 py-3" for="cash">
                                        <i class="bi bi-cash-coin me-1"></i> Cash
                                    </label>
                                    <input type="radio" class="btn-check" name="payment" id="card" value="card">
                                    <label class="btn btn-outline-primary w-100 py-3" for="card">
                                        <i class="bi bi-credit-card me-1"></i> Card
                                    </label>
                                    <input type="radio" class="btn-check" name="payment" id="transfer" value="transfer">
                                    <label class="btn btn-outline-info w-100 py-3" for="transfer">
                                        <i class="bi bi-bank me-1"></i> Transfer
                                    </label>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button class="btn btn-success btn-lg py-3 fs-2" id="completeOrder">
                                    <i class="bi bi-printer me-2"></i> Complete & Print
                                </button>
                                <button class="btn btn-outline-secondary" id="quickInvoiceBtn" style="display: none;">
                                    <i class="bi bi-receipt me-1"></i> Generate Invoice
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quantity Modal -->
<div class="modal fade" id="quantityModal" tabindex="-1" role="dialog" aria-labelledby="quantityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title" id="quantityModalLabel">
                    <i class="bi bi-cart-plus me-2"></i> Adjust Quantity
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="product-icon bg-light-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-box-seam fs-3 text-primary"></i>
                    </div>
                    <h6 class="text-muted mb-1">Product Name:</h6>
                    <h4 class="modal-product-name text-primary fw-bold mb-0" id="modalProductLabel"></h4>
                    <div class="mt-2">
                        <span class="badge bg-info" id="modalProductPrice"></span>
                        <span class="badge bg-warning ms-2" id="modalProductStock"></span>
                        <span class="badge bg-secondary ms-2" id="modalProductUnit"></span>
                    </div>
                </div>

                <div class="card border-0 bg-light mb-4">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label fw-bold text-dark mb-0">Enter Quantity</label>
                            <small class="text-muted" id="previousQtyText"></small>
                        </div>
                        <div class="input-group input-group-lg">
                            <button class="btn btn-outline-secondary" type="button" id="decreaseQty" aria-label="Decrease quantity">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                            <input type="number" id="modalQty" class="form-control text-center border-secondary fs-2 fw-bold"
                                   min="1" value="1" autofocus style="height: 60px;" aria-label="Quantity">
                            <button class="btn btn-outline-secondary" type="button" id="increaseQty" aria-label="Increase quantity">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted d-block">Press Enter to confirm, ESC to cancel</small>
                            <small class="text-success fw-semibold d-block mt-1" id="totalPriceDisplay"></small>
                        </div>
                    </div>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 py-2" data-qty="1">1</button></div>
                    <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 py-2" data-qty="2">2</button></div>
                    <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 py-2" data-qty="3">3</button></div>
                    <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 py-2" data-qty="5">5</button></div>
                </div>

                <div class="row g-2">
                    <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 py-2" data-qty="10">10</button></div>
                    <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 py-2" data-qty="20">20</button></div>
                    <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 py-2" data-qty="50">50</button></div>
                    <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 py-2" data-qty="100">100</button></div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light rounded-bottom">
                <button type="button" class="btn btn-danger px-4" id="removeFromCartBtn">
                    <i class="bi bi-trash me-2"></i> Remove
                </button>
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-2"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary px-4" id="confirmAddBtn">
                    <i class="bi bi-check-circle me-2"></i> Update Cart
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Per-Item Discount Modal -->
<div class="modal fade" id="itemDiscountModal" tabindex="-1" role="dialog" aria-labelledby="itemDiscountModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="itemDiscountModalLabel">
                    <i class="bi bi-percent me-2"></i> Apply Item Discount
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <h6 id="itemName" class="text-center mb-3"></h6>
                <div class="input-group mb-3">
                    <input type="number" id="itemDiscountValue" class="form-control" placeholder="0" min="0" step="0.01" aria-label="Discount amount">
                    <select id="itemDiscountType" class="form-select" aria-label="Discount type">
                        <option value="percent" selected>%</option>
                        <option value="fixed">₦</option>
                    </select>
                </div>
                <small class="text-muted">Leave 0 to remove discount</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="applyItemDiscountBtn">Apply</button>
            </div>
        </div>
    </div>
</div>

<!-- Load Order Modal -->
<div class="modal fade" id="loadOrderModal" tabindex="-1" role="dialog" aria-labelledby="loadOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="loadOrderModalLabel">Load Held Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="heldOrdersList"></div>
                <div id="noHeldOrders" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 mb-3"></i>
                    <p class="mb-0">No held orders found</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Customer Modal -->
<div class="modal fade" id="quickCustomerModal" tabindex="-1" role="dialog" aria-labelledby="quickCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="quickCustomerModalLabel">
                    <i class="bi bi-person-plus me-2"></i> Quick Add Customer
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="quickCustomerForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="firstName" class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="firstName" required>
                        </div>
                        <div class="col-md-6">
                            <label for="lastName" class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="lastName" required>
                        </div>
                        <div class="col-md-12">
                            <label for="phoneNumber" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phoneNumber">
                        </div>
                        <div class="col-md-12">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveQuickCustomerBtn">Save Customer</button>
            </div>
        </div>
    </div>
</div>

<!-- Offline Orders Modal -->
<div class="modal fade" id="offlineOrdersModal" tabindex="-1" role="dialog" aria-labelledby="offlineOrdersModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title text-white" id="offlineOrdersModalLabel">
                    <i class="bi bi-wifi-off me-2"></i> Offline Orders
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle me-2"></i>
                    These orders were created while offline. They will be synced automatically when you're back online.
                </div>
                <div id="offlineOrdersList">
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-warning" id="syncOfflineOrdersBtn" style="display: none;">
                    <i class="bi bi-cloud-arrow-up me-1"></i> Sync Now
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Hidden elements for accessibility -->
<div class="visually-hidden" role="status" aria-live="polite" id="cartStatus"></div>
<div class="visually-hidden" role="status" aria-live="polite" id="searchStatus"></div>

<!-- Toast Container -->
<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// MAIN POS SCRIPT - WITH AUDIO FEATURE
document.addEventListener('DOMContentLoaded', function () {
    // ============================================
    // INITIALIZATION
    // ============================================
    const input = document.getElementById('barcodeInput');
    const resultsBody = document.getElementById('resultsBody');
    const emptySearchRow = document.getElementById('emptySearchRow');
    const cartBody = document.getElementById('cartBody');
    const emptyCartRow = document.getElementById('emptyCartRow');
    const subtotalEl = document.getElementById('subtotal');
    const discountEl = document.getElementById('discountAmount');
    const grandTotalEl = document.getElementById('grandTotal');
    const searchLoading = document.getElementById('searchLoading');
    const modalQty = document.getElementById('modalQty');
    const confirmAddBtn = document.getElementById('confirmAddBtn');
    const removeFromCartBtn = document.getElementById('removeFromCartBtn');
    const customerSelect = document.getElementById('customerSelect');
    const discountValue = document.getElementById('discountValue');
    const discountType = document.getElementById('discountType');
    const applyDiscountBtn = document.getElementById('applyDiscountBtn');

    // Initialize Bootstrap tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // State management
    let cart = [];
    let allSearchedProducts = [];
    let currentSearchQuery = '';
    let productQuantityCache = {};
    let currentProduct = null;
    let currentItemIndex = null;
    let orderDiscountType = 'percent';
    let orderDiscountValue = 0;
    let printWindow = null;
    let printCheckInterval = null;
    let quantityModal = null;

    // Audio element for thank you sound
    let thankYouAudio = null;

    input.focus();

    // ============================================
    // INITIALIZE MODALS AND AUDIO
    // ============================================
    function initializeApp() {
        // Initialize quantity modal
        quantityModal = new bootstrap.Modal(document.getElementById('quantityModal'));

        // Create audio element
        thankYouAudio = new Audio('/audio/thank-you-sweet-man-235977.mp3');
        thankYouAudio.preload = 'auto';

        // Set up quantity modal events
        setupQuantityModal();

        // Initialize customer search
        initializeCustomerSearch();
    }

    function setupQuantityModal() {
        document.getElementById('quantityModal').addEventListener('show.bs.modal', updateModalTotal);

        document.getElementById('quantityModal').addEventListener('shown.bs.modal', () => {
            setTimeout(() => {
                modalQty.focus();
                modalQty.select();
            }, 100);
        });

        document.getElementById('quantityModal').addEventListener('hidden.bs.modal', () => {
            setTimeout(() => {
                input.focus();
                input.select();
            }, 100);
        });

        modalQty.addEventListener('input', updateModalTotal);
        modalQty.addEventListener('change', updateModalTotal);

        document.getElementById('increaseQty').addEventListener('click', () => {
            modalQty.value = (parseInt(modalQty.value) || 1) + 1;
            updateModalTotal();
            modalQty.focus();
            modalQty.select();
        });

        document.getElementById('decreaseQty').addEventListener('click', () => {
            const val = parseInt(modalQty.value) || 1;
            if (val > 1) {
                modalQty.value = val - 1;
                updateModalTotal();
                modalQty.focus();
                modalQty.select();
            }
        });

        // Quick quantity buttons
        document.querySelectorAll('[data-qty]').forEach(btn => {
            btn.addEventListener('click', () => {
                modalQty.value = btn.dataset.qty;
                updateModalTotal();
                modalQty.focus();
                modalQty.select();
            });
        });

        modalQty.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                confirmAddBtn.click();
            }
        });
    }

    // ============================================
    // EVENT LISTENERS
    // ============================================
    input.addEventListener('input', debounce(() => {
        const q = input.value.trim();
        currentSearchQuery = q;

        if (q.length >= 2) {
            searchProducts(q);
        } else if (q.length === 0) {
            clearAllUnselectedItems();
            renderAllSearchedProducts();
        } else {
            showEmptySearchState();
        }
    }, 300));

    input.addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            const q = input.value.trim();
            if (q) searchProducts(q);
        }
    });

    // Button click handlers
    confirmAddBtn.addEventListener('click', addOrUpdateProductInCart);
    removeFromCartBtn.addEventListener('click', removeCurrentProductFromCart);
    document.getElementById('clearCart').addEventListener('click', clearCart);
    document.getElementById('holdOrderBtn').addEventListener('click', holdOrder);
    document.getElementById('loadHeldBtn').addEventListener('click', loadHeldOrders);
    document.getElementById('completeOrder').addEventListener('click', completeOrder);
    applyDiscountBtn.addEventListener('click', applyOrderDiscount);

    discountType.addEventListener('change', function() {
        orderDiscountType = this.value;
        if (orderDiscountType === 'percent' && orderDiscountValue > 100) {
            discountValue.value = 100;
            orderDiscountValue = 100;
        }
    });

    discountValue.addEventListener('input', function() {
        const val = parseFloat(this.value) || 0;
        if (orderDiscountType === 'percent' && val > 100) {
            this.value = 100;
            orderDiscountValue = 100;
        } else {
            orderDiscountValue = val;
        }
    });

    // Discount field focus
    discountValue.addEventListener('click', function() {
        this.focus();
        this.select();
    });

    discountType.addEventListener('click', function() {
        discountValue.focus();
        discountValue.select();
    });

    // Item discount apply
    document.getElementById('applyItemDiscountBtn').addEventListener('click', function() {
        if (currentItemIndex === null) return;

        const value = parseFloat(document.getElementById('itemDiscountValue').value) || 0;
        const type = document.getElementById('itemDiscountType').value;

        if (type === 'percent' && value > 100) {
            showToast('Percentage cannot exceed 100%', 'warning');
            return;
        }

        cart[currentItemIndex].discount_type = type;
        cart[currentItemIndex].discount_value = value;

        updateCart();
        renderAllSearchedProducts();
        bootstrap.Modal.getInstance(document.getElementById('itemDiscountModal')).hide();

        showToast('Item discount applied!', 'success');
    });

    // ============================================
    // AUDIO FUNCTIONS
    // ============================================
    function playThankYouSound() {
        if (thankYouAudio) {
            // Reset audio to start
            thankYouAudio.currentTime = 0;

            // Play the audio
            thankYouAudio.play().catch(error => {
                console.log('Audio play failed:', error);
                // Show fallback notification
                showToast('Thank you for your purchase!', 'success', 3000);
            });
        } else {
            // Fallback if audio fails to load
            showToast('Thank you for your purchase!', 'success', 3000);
        }
    }

    // ============================================
    // CUSTOMER SEARCH FUNCTIONALITY
    // ============================================
    function initializeCustomerSearch() {
        const customerSelectElement = document.getElementById('customerSelect');
        const originalOptions = Array.from(customerSelectElement.options);

        // Create customer search container
        const customerContainer = customerSelectElement.closest('.mb-4');
        if (customerContainer) {
            const customerSearchContainer = document.createElement('div');
            customerSearchContainer.className = 'mb-2';
            customerSearchContainer.innerHTML = `
                <input type="text"
                       id="customerSearchInput"
                       class="form-control form-control-sm"
                       placeholder="Search customers... (Ctrl+F)">
            `;

            // Insert after the label but before the dropdown
            const label = customerContainer.querySelector('label');
            if (label) {
                customerContainer.insertBefore(customerSearchContainer, label.nextElementSibling);
            }

            const customerSearchInput = document.getElementById('customerSearchInput');

            customerSearchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();

                if (searchTerm.length === 0) {
                    // Restore all options
                    customerSelectElement.innerHTML = '';
                    originalOptions.forEach(option => {
                        customerSelectElement.appendChild(option.cloneNode(true));
                    });
                    updateCustomerCount(originalOptions.length - 1);
                    return;
                }

                // Filter options
                const filteredOptions = originalOptions.filter(option => {
                    if (option.value === '') return true; // Always show "Walk-in Customer"
                    return option.text.toLowerCase().includes(searchTerm);
                });

                // Update dropdown
                customerSelectElement.innerHTML = '';
                filteredOptions.forEach(option => {
                    customerSelectElement.appendChild(option.cloneNode(true));
                });

                // Show count
                const visibleCount = filteredOptions.length - 1; // Exclude "Walk-in Customer"
                updateCustomerCount(visibleCount);
            });

            // Keyboard shortcut to focus customer search
            document.addEventListener('keydown', function(e) {
                if (e.ctrlKey && e.key === 'f') {
                    e.preventDefault();
                    if (customerSearchInput) {
                        customerSearchInput.focus();
                        customerSearchInput.select();
                        showToast('Customer search focused', 'info', 1000);
                    }
                }
            });
        }
    }

    function updateCustomerCount(count) {
        document.getElementById('customerCount').textContent = count;
    }

    // ============================================
    // FUNCTIONS
    // ============================================
    function showEmptySearchState() {
        searchLoading.classList.add('d-none');
        if (allSearchedProducts.length > 0) {
            renderAllSearchedProducts();
        } else {
            emptySearchRow.style.display = '';
            resultsBody.innerHTML = emptySearchRow.outerHTML;
        }
    }

    function showSearchLoading() {
        searchLoading.classList.remove('d-none');
        emptySearchRow.style.display = 'none';
    }

    function hideSearchLoading() {
        searchLoading.classList.add('d-none');
    }

    function renderAllSearchedProducts() {
        if (allSearchedProducts.length === 0) {
            resultsBody.innerHTML = emptySearchRow.outerHTML;
            return;
        }

        resultsBody.innerHTML = '';
        emptySearchRow.style.display = 'none';

        // Render products in reverse order (latest on top)
        const sortedProducts = [...allSearchedProducts].reverse();

        sortedProducts.forEach(product => {
            renderProductRow(product);
        });
    }

    function renderProductRow(product) {
        const price = product.sale_price || product.price;
        const unit = product.primary_unit || 'Unit';
        const cartItem = cart.find(i => i.product_id === product.id);
        const addedQty = cartItem ? cartItem.qty : 0;
        const cachedQty = productQuantityCache[product.id] || 1;
        const displayQty = addedQty > 0 ? addedQty : (cachedQty > 1 ? `(${cachedQty})` : '');

        const isOutOfStock = product.stock <= 0;
        const btnClass = addedQty > 0
            ? 'btn-success'
            : (isOutOfStock ? 'btn-secondary' : 'btn-outline-primary');
        const btnText = addedQty > 0
            ? `Added ${addedQty}`
            : (isOutOfStock ? 'Out of Stock' : `Set Qty ${displayQty}`);
        const btnDisabled = isOutOfStock ? 'disabled' : '';

        const row = document.createElement('tr');
        row.dataset.productId = product.id;
        row.className = addedQty > 0 ? 'selected-product-row table-success' : '';
        row.innerHTML = `
            <td>
                <div class="d-flex align-items-center">
                    ${product.thumbnail ? `<img src="${product.thumbnail}" width="50" class="rounded me-3">` : '<div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width:50px;height:50px;"><i class="bi bi-image"></i></div>'}
                    <div>
                        <strong>${product.title}</strong><br>
                        <small class="text-muted">SKU: ${product.sku}</small>
                    </div>
                </div>
            </td>
            <td class="text-center">
                <span class="badge bg-${product.stock > 10 ? 'success' : product.stock > 0 ? 'warning' : 'danger'}">${product.stock}</span>
            </td>
            <td class="text-center">
                <button class="btn btn-sm ${btnClass} qty-btn"
                        data-product='${JSON.stringify(product).replace(/'/g, "&apos;")}'
                        data-product-id="${product.id}"
                        ${btnDisabled}>
                    ${btnText}
                </button>
                <button class="btn btn-sm btn-danger ms-2 remove-from-search-btn"
                        data-product-id="${product.id}"
                        title="Remove from list">
                    <i class="bi bi-x-circle"></i> Cancel
                </button>
            </td>
            <td class="text-end fw-bold">₦${parseFloat(price).toFixed(2)}</td>
            <td class="text-center"><span class="badge bg-info">${unit}</span></td>
        `;
        resultsBody.appendChild(row);

        // Add event listeners for the buttons in this row
        const qtyBtn = row.querySelector('.qty-btn');
        const cancelBtn = row.querySelector('.remove-from-search-btn');

        qtyBtn.addEventListener('click', function() {
            if (!this.disabled) openQuantityModal(this);
        });

        cancelBtn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            removeProductFromSearchAndCart(productId);
        });
    }

    function searchProducts(query) {
        if (!query) return;
        showSearchLoading();

        axios.get('{{ route("pos.search") }}', {
            params: { q: query }
        })
        .then(res => {
            hideSearchLoading();

            // Merge new results with existing ones (avoid duplicates)
            const newProducts = res.data || [];
            newProducts.forEach(newProduct => {
                // Check if product already exists in our list
                const existingIndex = allSearchedProducts.findIndex(p => p.id === newProduct.id);

                if (existingIndex === -1) {
                    // Add new product to the END of array
                    allSearchedProducts.push(newProduct);
                } else {
                    // Update existing product (in case stock changed)
                    allSearchedProducts[existingIndex] = newProduct;
                }
            });

            renderAllSearchedProducts();
        })
        .catch(err => {
            hideSearchLoading();
            console.error('Search error:', err);
            showToast('Failed to search products', 'error');
        });
    }

    function clearAllUnselectedItems() {
        // Check if there are any unselected items
        const unselectedProducts = allSearchedProducts.filter(product => {
            const isInCart = cart.some(item => item.product_id === product.id);
            return !isInCart;
        });

        if (unselectedProducts.length === 0) {
            return; // No unselected items to clear
        }

        // Remove all items that are NOT in cart
        allSearchedProducts = allSearchedProducts.filter(product =>
            cart.some(item => item.product_id === product.id)
        );

        // Update display
        if (allSearchedProducts.length === 0) {
            resultsBody.innerHTML = emptySearchRow.outerHTML;
            showToast('All unselected items cleared', 'info', 1500);
        } else {
            renderAllSearchedProducts();
            showToast(`Cleared ${unselectedProducts.length} unselected items`, 'info', 1500);
        }
    }

    function removeProductFromSearchAndCart(productId) {
        // Remove from search table
        allSearchedProducts = allSearchedProducts.filter(p => p.id != productId);

        // Remove from cart if present
        const wasInCart = cart.some(item => item.product_id == productId);
        if (wasInCart) {
            cart = cart.filter(i => i.product_id != productId);
            delete productQuantityCache[productId];
            updateCart();
        }

        // Update display
        if (allSearchedProducts.length === 0) {
            resultsBody.innerHTML = emptySearchRow.outerHTML;
        } else {
            renderAllSearchedProducts();
        }

        showToast('Product removed from list', 'success');
    }

    function openQuantityModal(button) {
        try {
            const product = JSON.parse(button.dataset.product);
            const productId = button.dataset.productId;
            currentProduct = product;

            // Move this product to the end of the array (latest)
            const productIndex = allSearchedProducts.findIndex(p => p.id === productId);
            if (productIndex !== -1) {
                const [movedProduct] = allSearchedProducts.splice(productIndex, 1);
                allSearchedProducts.push(movedProduct);
            }

            const cartItem = cart.find(i => i.product_id === productId);
            const cachedQty = productQuantityCache[productId] || 1;
            const previousQty = cartItem ? cartItem.qty : cachedQty;

            // Update modal content
            document.getElementById('modalProductLabel').textContent = product.title;
            const price = product.sale_price || product.price;
            document.getElementById('modalProductPrice').textContent = `₦${parseFloat(price).toFixed(2)}`;
            document.getElementById('modalProductStock').textContent = `Stock: ${product.stock}`;
            document.getElementById('modalProductUnit').textContent = product.primary_unit || 'Unit';

            modalQty.value = previousQty;
            document.getElementById('previousQtyText').textContent =
                cartItem ? `In cart: ${previousQty}` : `Previous: ${previousQty}`;
            document.getElementById('previousQtyText').className =
                cartItem ? 'text-success fw-semibold' : 'text-muted';

            removeFromCartBtn.style.display = cartItem ? 'inline-block' : 'none';

            updateModalTotal();
            quantityModal.show();
        } catch (e) {
            console.error('Error opening quantity modal:', e);
            showToast('Error loading product details', 'error');
        }
    }

    function updateModalTotal() {
        if (!currentProduct) return;
        const qty = parseInt(modalQty.value) || 1;
        const price = currentProduct.sale_price || currentProduct.price;
        const total = qty * price;
        document.getElementById('totalPriceDisplay').textContent = `Total: ₦${total.toFixed(2)}`;
    }

    function addOrUpdateProductInCart() {
        if (!currentProduct) return;

        // Check stock
        if (currentProduct.stock <= 0) {
            showToast(`${currentProduct.title} is out of stock`, 'error');
            quantityModal.hide();
            return;
        }

        const qty = parseInt(modalQty.value) || 1;
        if (qty < 1) {
            showToast('Quantity must be at least 1', 'warning');
            return;
        }

        if (qty > currentProduct.stock) {
            showToast(`Only ${currentProduct.stock} units available`, 'warning');
            modalQty.value = currentProduct.stock;
            updateModalTotal();
            return;
        }

        const p = currentProduct;
        const price = p.sale_price || p.price;
        let unitId = p.primary_unit_id || 1;

        // Make sure product is in searched list (add if not)
        if (!allSearchedProducts.some(sp => sp.id === p.id)) {
            allSearchedProducts.push({...p});
        }

        // Update or add to cart
        const existing = cart.find(i => i.product_id === p.id);
        if (existing) {
            existing.qty = qty;
        } else {
            cart.push({
                product_id: p.id,
                title: p.title,
                price: parseFloat(price),
                qty: qty,
                unit_name: p.primary_unit || 'Unit',
                unit_id: parseInt(unitId),
                sku: p.sku,
                thumbnail: p.thumbnail,
                discount_type: 'percent',
                discount_value: 0,
                discounted_price: parseFloat(price)
            });
        }

        productQuantityCache[p.id] = qty;
        quantityModal.hide();

        updateCart();
        renderAllSearchedProducts();
        currentProduct = null;

        showToast('Product added to cart', 'success');
    }

    function removeCurrentProductFromCart() {
        if (!currentProduct) return;

        Swal.fire({
            title: 'Remove from Cart?',
            text: `Remove ${currentProduct.title} from cart?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, remove'
        }).then(res => {
            if (res.isConfirmed) {
                const id = currentProduct.id;
                cart = cart.filter(i => i.product_id != id);
                delete productQuantityCache[id];
                quantityModal.hide();
                updateCart();
                renderAllSearchedProducts();
                showToast('Product removed from cart', 'success');
            }
        });
    }

    function updateCart() {
        if (cart.length === 0) {
            emptyCartRow.style.display = '';
            cartBody.innerHTML = '';
            subtotalEl.textContent = '₦0.00';
            discountEl.textContent = '-₦0.00';
            grandTotalEl.textContent = '₦0.00';
            renderAllSearchedProducts();
            return;
        }

        emptyCartRow.style.display = 'none';
        cartBody.innerHTML = '';

        let subtotal = 0;

        cart.forEach((item, i) => {
            let unitPrice = item.price;
            if (item.discount_value > 0) {
                unitPrice = item.discount_type === 'percent'
                    ? item.price * (1 - item.discount_value / 100)
                    : item.price - item.discount_value;
                if (unitPrice < 0) unitPrice = 0;
            }
            item.discounted_price = unitPrice;

            const total = item.qty * unitPrice;
            subtotal += total;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${i+1}</td>
                <td>
                    <div class="d-flex align-items-center">
                        ${item.thumbnail ? `<img src="${item.thumbnail}" width="40" class="rounded me-2">` : '<div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width:40px;height:40px;"><i class="bi bi-image"></i></div>'}
                        <div>
                            <strong>${item.title}</strong><br>
                            <small class="text-muted">${item.sku ? 'SKU: ' + item.sku : ''}</small>
                            ${item.discount_value > 0 ? `<small class="text-warning">-${item.discount_value}${item.discount_type === 'percent' ? '%' : '₦'}</small>` : ''}
                        </div>
                    </div>
                </td>
                <td class="text-center fw-bold">
                    <button class="btn btn-sm btn-primary qty-btn-cart"
                            data-product='${JSON.stringify(item).replace(/'/g, "&apos;")}'
                            data-product-id="${item.product_id}">
                        ${item.qty}
                    </button>
                </td>
                <td class="text-end">₦${unitPrice.toFixed(2)}</td>
                <td class="text-end fw-bold">₦${total.toFixed(2)}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-warning rounded-circle item-discount-btn"
                            data-product-id="${item.product_id}">
                        <i class="bi bi-percent"></i>
                    </button>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-danger rounded-circle remove-cart-item-btn"
                            data-index="${i}">
                        <i class="bi bi-x"></i>
                    </button>
                </td>
            `;
            cartBody.appendChild(row);

            // Add event listeners
            const removeBtn = row.querySelector('.remove-cart-item-btn');
            removeBtn.addEventListener('click', function() {
                const index = parseInt(this.dataset.index);
                removeCartItem(index);
            });

            const discountBtn = row.querySelector('.item-discount-btn');
            discountBtn.addEventListener('click', function() {
                const productId = this.dataset.productId;
                currentItemIndex = cart.findIndex(item => item.product_id === productId);
                if (currentItemIndex === -1) return;

                const item = cart[currentItemIndex];
                document.getElementById('itemName').textContent = item.title;
                document.getElementById('itemDiscountValue').value = item.discount_value || 0;
                document.getElementById('itemDiscountType').value = item.discount_type || 'percent';

                new bootstrap.Modal(document.getElementById('itemDiscountModal')).show();
            });

            const qtyBtn = row.querySelector('.qty-btn-cart');
            qtyBtn.addEventListener('click', function() {
                openQuantityModal(this);
            });
        });

        let orderDiscountAmount = 0;
        if (orderDiscountValue > 0) {
            if (orderDiscountType === 'percent') {
                orderDiscountAmount = (subtotal * orderDiscountValue) / 100;
            } else {
                orderDiscountAmount = orderDiscountValue;
            }
            orderDiscountAmount = Math.min(orderDiscountAmount, subtotal);
        }

        const grandTotal = subtotal - orderDiscountAmount;

        subtotalEl.textContent = `₦${subtotal.toFixed(2)}`;
        discountEl.textContent = `-₦${orderDiscountAmount.toFixed(2)}`;
        grandTotalEl.textContent = `₦${grandTotal.toFixed(2)}`;

        window.currentDiscount = {
            type: orderDiscountType,
            value: orderDiscountValue,
            amount: orderDiscountAmount
        };
    }

    function removeCartItem(index) {
        const item = cart[index];
        cart.splice(index, 1);

        delete productQuantityCache[item.product_id];

        updateCart();
        renderAllSearchedProducts();
        showToast('Item removed from cart', 'success');
    }

    function applyOrderDiscount() {
        orderDiscountValue = parseFloat(discountValue.value) || 0;
        orderDiscountType = discountType.value;

        if (orderDiscountType === 'percent' && orderDiscountValue > 100) {
            showToast('Percentage discount cannot exceed 100%', 'warning');
            orderDiscountValue = 100;
            discountValue.value = 100;
        }

        updateCart();
        showToast('Order discount applied!', 'success');
    }

    function clearCart() {
        if (cart.length === 0) {
            showToast('Cart is already empty', 'info');
            return;
        }

        Swal.fire({
            title: 'Clear Cart?',
            text: 'This will remove all items from your cart.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, clear cart'
        }).then(res => {
            if (res.isConfirmed) {
                cart = [];
                productQuantityCache = {};
                updateCart();
                renderAllSearchedProducts();
                showToast('Cart cleared', 'success');
            }
        });
    }

    function holdOrder() {
        if (cart.length === 0) {
            showToast('Cart is empty', 'info');
            return;
        }

        const heldOrders = JSON.parse(localStorage.getItem('heldOrders') || '[]');
        heldOrders.push({
            id: Date.now(),
            cart: JSON.parse(JSON.stringify(cart)),
            customer: customerSelect.value,
            allSearchedProducts: JSON.parse(JSON.stringify(allSearchedProducts)),
            productQuantityCache: JSON.parse(JSON.stringify(productQuantityCache)),
            discount: {
                type: orderDiscountType,
                value: orderDiscountValue
            },
            time: new Date().toLocaleString(),
            timestamp: Date.now()
        });

        localStorage.setItem('heldOrders', JSON.stringify(heldOrders));

        cart = [];
        productQuantityCache = {};
        orderDiscountValue = 0;
        discountValue.value = '0';

        updateCart();
        renderAllSearchedProducts();

        showToast('Order held successfully!', 'success');
    }

    function loadHeldOrders() {
        const heldOrders = JSON.parse(localStorage.getItem('heldOrders') || '[]');
        const list = document.getElementById('heldOrdersList');
        const noOrders = document.getElementById('noHeldOrders');

        if (heldOrders.length === 0) {
            list.innerHTML = '';
            noOrders.style.display = 'block';
        } else {
            noOrders.style.display = 'none';
            let html = '<div class="list-group">';

            heldOrders.sort((a, b) => b.timestamp - a.timestamp).forEach(order => {
                const items = order.cart.length;
                const total = order.cart.reduce((sum, item) => {
                    let price = item.price;
                    if (item.discount_value > 0) {
                        price = item.discount_type === 'percent'
                            ? item.price * (1 - item.discount_value / 100)
                            : item.price - item.discount_value;
                    }
                    return sum + (item.qty * price);
                }, 0);

                html += `
                    <div class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${order.time}</h6>
                                <p class="mb-1 text-muted">${items} item${items > 1 ? 's' : ''} - ₦${total.toFixed(2)}</p>
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary load-order-btn" data-order-id="${order.id}">Load</button>
                                <button class="btn btn-sm btn-outline-danger remove-order-btn" data-order-id="${order.id}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            list.innerHTML = html;

            // Add event listeners for the loaded orders
            document.querySelectorAll('.load-order-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const orderId = this.dataset.orderId;
                    const heldOrders = JSON.parse(localStorage.getItem('heldOrders') || '[]');
                    const order = heldOrders.find(o => o.id == orderId);
                    if (order) {
                        if (cart.length > 0) {
                            Swal.fire({
                                title: 'Replace Current Cart?',
                                text: 'Loading this order will replace your current cart.',
                                icon: 'warning',
                                showCancelButton: true,
                                confirmButtonText: 'Yes, replace'
                            }).then(res => res.isConfirmed && loadOrderFromHeld(order));
                        } else {
                            loadOrderFromHeld(order);
                        }
                    }
                });
            });

            document.querySelectorAll('.remove-order-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const orderId = this.dataset.orderId;
                    Swal.fire({
                        title: 'Remove Order?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, remove'
                    }).then(res => {
                        if (res.isConfirmed) {
                            let orders = JSON.parse(localStorage.getItem('heldOrders') || '[]');
                            orders = orders.filter(o => o.id != orderId);
                            localStorage.setItem('heldOrders', JSON.stringify(orders));
                            loadHeldOrders();
                            showToast('Order removed', 'success');
                        }
                    });
                });
            });
        }

        new bootstrap.Modal(document.getElementById('loadOrderModal')).show();
    }

    function loadOrderFromHeld(order) {
        cart = JSON.parse(JSON.stringify(order.cart));
        allSearchedProducts = order.allSearchedProducts ? JSON.parse(JSON.stringify(order.allSearchedProducts)) : allSearchedProducts;
        productQuantityCache = order.productQuantityCache ? JSON.parse(JSON.stringify(order.productQuantityCache)) : {};

        if (order.customer) {
            customerSelect.value = order.customer;
        }

        if (order.discount) {
            orderDiscountType = order.discount.type;
            orderDiscountValue = order.discount.value;
            discountType.value = order.discount.type;
            discountValue.value = order.discount.value;
        }

        updateCart();
        renderAllSearchedProducts();

        bootstrap.Modal.getInstance(document.getElementById('loadOrderModal')).hide();
        showToast('Order loaded successfully!', 'success');
    }

    function completeOrder() {
        if (cart.length === 0) {
            showToast('Cart is empty', 'warning');
            return;
        }

        const payment = document.querySelector('input[name="payment"]:checked').value;
        const customerId = customerSelect.value || null;

        const items = cart.map(item => ({
            product_id: item.product_id,
            qty: parseInt(item.qty),
            unit_id: parseInt(item.unit_id || 1),
            sale_price: parseFloat(item.price),
            discount_type: item.discount_type || null,
            discount_value: item.discount_value || 0
        }));

        const discount = window.currentDiscount || { type: 'percent', value: 0, amount: 0 };

        Swal.fire({
            title: 'Processing Order...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        axios.post('{{ route("pos.order.save") }}', {
            items,
            payment_method: payment,
            customer_id: customerId,
            discount_type: discount.type,
            discount_value: discount.value,
            discount_amount: discount.amount,
            _token: '{{ csrf_token() }}'
        })
        .then(res => {
            Swal.close();
            if (res.data.success) {
                Swal.fire({
                    title: 'Success!',
                    html: `<div class="text-center">
                        <i class="bi bi-check-circle text-success display-1 mb-3"></i>
                        <h4>Order #${res.data.order_id} Completed!</h4>
                        <p class="fs-3">Total: ₦${parseFloat(res.data.total).toFixed(2)}</p>
                    </div>`,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Print Receipt',
                    cancelButtonText: 'New Order',
                    buttonsStyling: false,
                    customClass: {
                        confirmButton: 'btn btn-success btn-lg me-2',
                        cancelButton: 'btn btn-outline-secondary btn-lg'
                    }
                }).then(r => {
                    if (r.isConfirmed) {
                        // Play thank you sound
                        playThankYouSound();

                        // Open print window
                        printWindow = window.open(`/pos/receipt/${res.data.order_id}`, '_blank');
                        if (printWindow) {
                            startMonitoringPrintWindow();
                        }
                    } else {
                        resetAfterOrder();
                    }
                });
            } else {
                Swal.fire('Error', res.data.message || 'Failed to process order', 'error');
            }
        })
        .catch(err => {
            Swal.close();
            let msg = 'Failed to complete order.';
            if (err.response && err.response.data.errors) {
                msg = Object.values(err.response.data.errors).flat().join('<br>');
            } else if (err.response && err.response.data.message) {
                msg = err.response.data.message;
            }
            Swal.fire('Error', msg, 'error');
        });
    }

    function startMonitoringPrintWindow() {
        if (printCheckInterval) {
            clearInterval(printCheckInterval);
        }

        printCheckInterval = setInterval(function() {
            if (printWindow && printWindow.closed) {
                clearInterval(printCheckInterval);
                printCheckInterval = null;
                resetAfterOrder();
            }
        }, 500);
    }

    function resetAfterOrder() {
        // Clear cart and search table
        cart = [];
        allSearchedProducts = [];
        productQuantityCache = {};
        orderDiscountValue = 0;
        discountValue.value = '0';

        updateCart();
        renderAllSearchedProducts();

        input.value = '';
        input.focus();
        input.select();

        showToast('New order started', 'info');
    }

    function showToast(message, type = 'success', duration = 2000) {
        const toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: duration,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        toast.fire({
            icon: type,
            title: message
        });
    }

    function debounce(func, delay) {
        let timeout;
        return function executedFunction(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    }

    // Initialize the application
    initializeApp();
    updateCart();
});
</script>

<style>

    /* Customer Search Container */
.customer-search-container {
    cursor: pointer;
    position: relative;
    transition: all 0.2s ease;
}

.customer-search-container:hover {
    background-color: rgba(0, 123, 255, 0.05);
    border-radius: 0.375rem;
}

#customerSearchInput {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    transition: all 0.2s ease;
    width: 100%;
}

#customerSearchInput:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    outline: none;
    background-color: #fff;
}

/* Shortcut hint */
#customerSearchInput::placeholder {
    color: #6c757d;
    font-size: 0.8rem;
}

/* Focus state for customer section */
.customer-search-container:focus-within {
    background-color: rgba(0, 123, 255, 0.1);
}

/* Discount field focus styling */
#discountValue:focus, #discountType:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    outline: none;
}

/* Search product table */
.selected-product-row {
    background-color: rgba(25, 135, 84, 0.1) !important;
    border-left: 4px solid #198754;
    transition: all 0.3s ease;
}

/* Remove button styling */
.remove-from-search-btn {
    transition: all 0.2s ease;
}

.remove-from-search-btn:hover {
    transform: scale(1.05);
    background-color: #dc3545 !important;
    color: white !important;
}

/* Keyboard shortcut hint */
.keyboard-shortcut {
    font-size: 0.75rem;
    color: #6c757d;
    margin-left: 0.5rem;
    font-style: italic;
}

/* Blinking cursor effect for focus */
.focused-field {
    animation: blink 1s infinite;
    border-color: #0d6efd !important;
}

@keyframes blink {
    0%, 100% { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25); }
    50% { box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.5); }
}
    /* Customer Search Styling */
#customerSearchInput {
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    transition: all 0.2s ease;
}

#customerSearchInput:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    outline: none;
}

/* Discount field focus styling */
#discountValue:focus, #discountType:focus {
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    outline: none;
}

/* Highlight latest added product */
tr.selected-product-row {
    background-color: rgba(25, 135, 84, 0.1) !important;
    border-left: 4px solid #198754;
    transition: all 0.3s ease;
}

/* Remove button styling */
.remove-from-search-btn {
    transition: all 0.2s ease;
}

.remove-from-search-btn:hover {
    transform: scale(1.05);
    background-color: #dc3545 !important;
    color: white !important;
}
/* Enhanced animations and transitions */
.modal.fade .modal-content {
    transform: scale(0.95);
    transition: transform 0.3s ease-out, opacity 0.3s ease-out;
    opacity: 0;
}
.modal.show .modal-content {
    transform: scale(1);
    opacity: 1;
}

.product-icon {
    transition: all 0.3s ease;
}
.modal.show .product-icon {
    animation: pulse 0.6s ease;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); }
    100% { transform: scale(1); }
}

@keyframes slideIn {
    from { transform: translateY(-20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* Input focus effects */
#modalQty:focus,
#barcodeInput:focus,
#discountValue:focus {
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    border-color: #86b7fe;
    transform: scale(1.02);
}

/* Button hover effects */
.btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
    transform: translateY(-2px);
    transition: all 0.2s ease;
}

.qty-btn, .qty-btn-cart {
    min-width: 120px;
    transition: all 0.2s ease;
}
.qty-btn-cart {
    min-width: 60px;
    font-weight: bold;
}
.qty-btn-cart:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.qty-btn:disabled {
    cursor: not-allowed;
    opacity: 0.65;
    pointer-events: none;
}

/* Loading overlay */
#searchLoading {
    backdrop-filter: blur(2px);
}

/* List group animations */
.list-group-item {
    transition: background-color 0.2s ease;
}
.list-group-item:hover {
    background: #f8f9fa;
}

/* Selected product row */
.selected-product-row {
    background-color: rgba(25, 135, 84, 0.1) !important;
    border-left: 4px solid #198754;
    animation: slideIn 0.3s ease;
}

/* Badge styling */
.badge {
    font-size: 0.75em;
    padding: 0.35em 0.65em;
    transition: transform 0.2s ease;
}
.badge:hover {
    transform: scale(1.05);
}

/* Table headers */
.table-primary th {
    background: linear-gradient(135deg, #0d6efd, #0a58ca);
    color: white;
    position: relative;
}
.table-primary th::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: rgba(255, 255, 255, 0.3);
}

.table-dark th {
    background: #212529;
    color: white;
}

/* Payment method buttons */
.btn-check:checked + .btn-outline-success {
    background: #198754;
    color: white;
    border-color: #198754;
}
.btn-check:checked + .btn-outline-primary {
    background: #0d6efd;
    color: white;
    border-color: #0d6efd;
}
.btn-check:checked + .btn-outline-info {
    background: #0dcaf0;
    color: white;
    border-color: #0dcaf0;
}

/* Cart table hover */
#cartBody tr {
    transition: background-color 0.2s ease;
}
#cartBody tr:hover {
    background: rgba(0, 0, 0, 0.02);
}

/* Item discount button */
.item-discount-btn {
    font-size: 0.8rem;
    transition: all 0.2s ease;
}
.item-discount-btn:hover {
    background: #ffc107;
    color: #000;
    transform: rotate(15deg);
}

/* Remove button */
.btn-danger.rounded-circle {
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
    transition: all 0.2s ease;
}
.btn-danger.rounded-circle:hover {
    transform: scale(1.1);
}

/* Customer select dropdown */
.customer-select-dropdown {
    background: #f8f9fa;
    border: 1px solid #ced4da;
    border-radius: 0.375rem;
    transition: all 0.2s ease;
}
.customer-select-dropdown:focus {
    background: #fff;
    border-color: #86b7fe;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

/* Toast animations */
.toast {
    animation: slideIn 0.3s ease;
}

/* Offline mode indicator */
#connectionStatus {
    transition: all 0.3s ease;
    animation: pulse 2s infinite;
}

/* Responsive adjustments */
@media (max-width: 576px) {
    .modal-dialog {
        margin: 0.5rem;
    }
    #modalQty {
        font-size: 1.5rem !important;
        height: 50px !important;
    }
    .shortcuts {
        font-size: 0.8rem;
    }
}

@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.9rem;
    }
    .btn-group {
        flex-wrap: wrap;
    }
}

/* Highlight search matches */
mark.bg-warning {
    padding: 0.1em 0.2em;
    border-radius: 0.2em;
    animation: highlight 1s ease;
}

@keyframes highlight {
    from { background-color: #ff6b6b; }
    to { background-color: #ffc107; }
}

/* Scrollbar styling */
.table-responsive::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.table-responsive::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

.table-responsive::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Focus outline for accessibility */
:focus-visible {
    outline: 2px solid #0d6efd;
    outline-offset: 2px;
}

/* Print styles */
@media print {
    .no-print {
        display: none !important;
    }
}
</style>
@endsection
