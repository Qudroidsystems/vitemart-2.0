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
// ============================================
// POS APPLICATION MODULES
// ============================================
class POSCart {
    constructor() {
        this.cart = [];
        this.cache = {};
        this.selectedProducts = [];
        this.discount = { type: 'percent', value: 0, amount: 0 };
        this.taxRate = {{ config('pos.tax_rate', 0) }};
        this.init();
    }

    init() {
        this.loadState();
    }

    addItem(product, quantity, discount = { type: 'percent', value: 0 }) {
        const existing = this.cart.find(item => item.product_id === product.id);

        if (existing) {
            existing.qty = quantity;
            existing.discount_type = discount.type;
            existing.discount_value = discount.value;
        } else {
            this.cart.push({
                product_id: product.id,
                title: product.title,
                price: parseFloat(product.sale_price || product.price),
                qty: quantity,
                unit_name: product.primary_unit || 'Unit',
                unit_id: parseInt(product.primary_unit_id || 1),
                sku: product.sku,
                thumbnail: product.thumbnail,
                discount_type: discount.type,
                discount_value: discount.value,
                stock: product.stock,
                maxStock: product.stock
            });
        }

        this.cache[product.id] = quantity;
        this.selectedProducts.push({...product});
        this.saveState();
        return existing ? 'updated' : 'added';
    }

    updateItem(index, updates) {
        if (this.cart[index]) {
            this.cart[index] = { ...this.cart[index], ...updates };
            this.saveState();
            return true;
        }
        return false;
    }

    removeItem(productId) {
        const index = this.cart.findIndex(item => item.product_id === productId);
        if (index !== -1) {
            this.cart.splice(index, 1);
            delete this.cache[productId];
            this.selectedProducts = this.selectedProducts.filter(p => p.id !== productId);
            this.saveState();
            return true;
        }
        return false;
    }

    clear() {
        this.cart = [];
        this.cache = {};
        this.selectedProducts = [];
        this.discount = { type: 'percent', value: 0, amount: 0 };
        localStorage.removeItem('posCartState');
        sessionStorage.removeItem('posCartState');
    }

    getTotals() {
        let subtotal = 0;
        let tax = 0;

        this.cart.forEach(item => {
            let unitPrice = item.price;
            if (item.discount_value > 0) {
                unitPrice = item.discount_type === 'percent'
                    ? item.price * (1 - item.discount_value / 100)
                    : item.price - item.discount_value;
                if (unitPrice < 0) unitPrice = 0;
            }
            subtotal += item.qty * unitPrice;
        });

        tax = subtotal * (this.taxRate / 100);

        let orderDiscount = 0;
        if (this.discount.value > 0) {
            orderDiscount = this.discount.type === 'percent'
                ? (subtotal * this.discount.value) / 100
                : this.discount.value;
            orderDiscount = Math.min(orderDiscount, subtotal);
        }

        this.discount.amount = orderDiscount;
        const grandTotal = subtotal + tax - orderDiscount;

        return {
            subtotal: subtotal.toFixed(2),
            tax: tax.toFixed(2),
            discount: orderDiscount.toFixed(2),
            grandTotal: grandTotal.toFixed(2),
            itemCount: this.cart.length
        };
    }

    saveState() {
        const state = {
            cart: this.cart,
            cache: this.cache,
            selectedProducts: this.selectedProducts,
            discount: this.discount,
            timestamp: Date.now()
        };
        localStorage.setItem('posCartState', JSON.stringify(state));
        sessionStorage.setItem('posCartState', JSON.stringify(state));
    }

    loadState() {
        const state = JSON.parse(localStorage.getItem('posCartState') || sessionStorage.getItem('posCartState') || '{}');
        const maxAge = 2 * 60 * 60 * 1000; // 2 hours

        if (state.timestamp && (Date.now() - state.timestamp) < maxAge) {
            this.cart = state.cart || [];
            this.cache = state.cache || {};
            this.selectedProducts = state.selectedProducts || [];
            this.discount = state.discount || { type: 'percent', value: 0, amount: 0 };
            return true;
        }
        return false;
    }

    validate() {
        const errors = [];

        if (this.cart.length === 0) {
            errors.push('Cart is empty');
            return errors;
        }

        this.cart.forEach((item, index) => {
            if (item.qty <= 0) {
                errors.push(`Item ${index + 1}: Quantity must be positive`);
            }
            if (item.qty > item.maxStock) {
                errors.push(`Item ${index + 1}: Exceeds available stock (${item.maxStock} available)`);
            }
            if (!item.product_id) {
                errors.push(`Item ${index + 1}: Product ID missing`);
            }
            if (item.discount_value < 0) {
                errors.push(`Item ${index + 1}: Discount cannot be negative`);
            }
        });

        return errors;
    }

    getOrderData(customerId, paymentMethod) {
        const totals = this.getTotals();

        return {
            items: this.cart.map(item => ({
                product_id: item.product_id,
                qty: parseInt(item.qty),
                unit_id: parseInt(item.unit_id || 1),
                sale_price: parseFloat(item.price),
                discount_type: item.discount_type || null,
                discount_value: item.discount_value || 0
            })),
            customer_id: customerId,
            payment_method: paymentMethod,
            discount_type: this.discount.type,
            discount_value: this.discount.value,
            discount_amount: parseFloat(totals.discount),
            subtotal: parseFloat(totals.subtotal),
            tax: parseFloat(totals.tax),
            grand_total: parseFloat(totals.grandTotal),
            _token: '{{ csrf_token() }}'
        };
    }
}

class POSSearch {
    constructor() {
        this.currentResults = [];
        this.currentQuery = '';
        this.isSearching = false;
        this.lastSearchTime = 0;
        this.debounceDelay = 300;
        this.minSearchLength = 2;
    }

    async search(query) {
        if (query.length < this.minSearchLength || this.isSearching) {
            return [];
        }

        this.currentQuery = query;
        this.isSearching = true;
        this.lastSearchTime = Date.now();

        try {
            const response = await axios.get('{{ route("pos.search") }}', {
                params: { q: query, t: Date.now() },
                timeout: 5000
            });

            this.currentResults = response.data || [];
            return this.currentResults;
        } catch (error) {
            console.error('Search error:', error);
            this.showToast('Search failed. Please check your connection.', 'error');
            return [];
        } finally {
            this.isSearching = false;
        }
    }

    debounceSearch(func, delay) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func.apply(this, args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, delay);
        };
    }

    highlightMatches(text, query) {
        if (!query || query.length < 2) return text;

        const regex = new RegExp(`(${query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return text.replace(regex, '<mark class="bg-warning">$1</mark>');
    }
}

class POSUI {
    constructor() {
        this.modals = {};
        this.toasts = [];
        this.init();
    }

    init() {
        this.initModals();
        this.initTooltips();
        this.initEventListeners();
    }

    initModals() {
        this.modals.quantity = new bootstrap.Modal(document.getElementById('quantityModal'));
        this.modals.itemDiscount = new bootstrap.Modal(document.getElementById('itemDiscountModal'));
        this.modals.loadOrder = new bootstrap.Modal(document.getElementById('loadOrderModal'));
        this.modals.quickCustomer = new bootstrap.Modal(document.getElementById('quickCustomerModal'));
        this.modals.offlineOrders = new bootstrap.Modal(document.getElementById('offlineOrdersModal'));
    }

    initTooltips() {
        const tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltips.map(tooltip => new bootstrap.Tooltip(tooltip));
    }

    initEventListeners() {
        // Keyboard shortcuts
        document.addEventListener('keydown', this.handleKeyboardShortcuts.bind(this));

        // Network status
        window.addEventListener('online', this.updateNetworkStatus.bind(this));
        window.addEventListener('offline', this.updateNetworkStatus.bind(this));

        // Before unload warning
        window.addEventListener('beforeunload', this.handleBeforeUnload.bind(this));
    }

    handleKeyboardShortcuts(e) {
        // Ignore if user is typing in input/textarea
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) {
            return;
        }

        switch(e.key) {
            case 'F1':
                e.preventDefault();
                document.getElementById('barcodeInput').focus();
                this.showToast('Search input focused', 'info');
                break;
            case 'F2':
                e.preventDefault();
                if (confirm('Clear cart?')) {
                    document.getElementById('clearCart').click();
                }
                break;
            case 'F3':
                e.preventDefault();
                document.getElementById('completeOrder').click();
                break;
            case 'F4':
                e.preventDefault();
                document.getElementById('holdOrderBtn').click();
                break;
            case 'Escape':
                const modal = bootstrap.Modal.getInstance(document.querySelector('.modal.show'));
                if (modal) modal.hide();
                break;
        }
    }

    updateNetworkStatus() {
        const statusEl = document.getElementById('connectionStatus');
        const isOnline = navigator.onLine;

        if (isOnline) {
            statusEl.className = 'badge bg-success';
            statusEl.innerHTML = '<i class="bi bi-wifi"></i> Online';
            this.showToast('Back online', 'success');
        } else {
            statusEl.className = 'badge bg-danger';
            statusEl.innerHTML = '<i class="bi bi-wifi-off"></i> Offline';
            this.showToast('You are offline. Orders will be saved locally.', 'warning');
        }

        return isOnline;
    }

    handleBeforeUnload(e) {
        const cart = window.posApp?.cart?.cart || [];
        if (cart.length > 0) {
            e.preventDefault();
            e.returnValue = 'You have unsaved items in your cart. Are you sure you want to leave?';
            return e.returnValue;
        }
    }

    showToast(message, type = 'success', duration = 3000) {
        const toastId = 'toast-' + Date.now();
        const toastHtml = `
            <div id="${toastId}" class="toast align-items-center text-bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;

        document.getElementById('toastContainer').insertAdjacentHTML('beforeend', toastHtml);
        const toastEl = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastEl, { delay: duration });
        toast.show();

        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });
    }

    showLoading(show = true, elementId = 'searchLoading') {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.toggle('d-none', !show);
        }
    }

    updateAccessibilityStatus(message, role = 'status') {
        const statusEl = document.getElementById(role === 'status' ? 'cartStatus' : 'searchStatus');
        if (statusEl) {
            statusEl.textContent = message;
            setTimeout(() => statusEl.textContent = '', 2000);
        }
    }
}

class OfflineManager {
    constructor() {
        this.orders = JSON.parse(localStorage.getItem('offlineOrders') || '[]');
        this.syncing = false;
        this.init();
    }

    init() {
        // Auto-sync when coming online
        window.addEventListener('online', () => {
            this.attemptSync();
        });
    }

    saveOrder(orderData) {
        const order = {
            ...orderData,
            id: 'offline_' + Date.now(),
            timestamp: Date.now(),
            synced: false,
            attempts: 0
        };

        this.orders.push(order);
        localStorage.setItem('offlineOrders', JSON.stringify(this.orders));

        this.showOfflineNotification();
        return order.id;
    }

    async attemptSync() {
        if (this.syncing || !navigator.onLine || this.orders.length === 0) {
            return;
        }

        this.syncing = true;
        const unsynced = this.orders.filter(o => !o.synced);

        for (const order of unsynced) {
            if (order.attempts >= 3) {
                console.warn('Max sync attempts reached for order:', order.id);
                continue;
            }

            try {
                const response = await axios.post('{{ route("pos.order.save") }}', order.data);

                if (response.data.success) {
                    order.synced = true;
                    order.synced_at = Date.now();
                    order.remote_id = response.data.order_id;
                }

                order.attempts++;
            } catch (error) {
                console.error('Sync failed for order:', order.id, error);
                order.attempts++;
                break; // Stop on first failure
            }

            // Update localStorage after each attempt
            localStorage.setItem('offlineOrders', JSON.stringify(this.orders));
        }

        this.syncing = false;
        this.cleanupSyncedOrders();
    }

    cleanupSyncedOrders() {
        const oldCount = this.orders.length;
        this.orders = this.orders.filter(o => !o.synced || Date.now() - o.synced_at < 7 * 24 * 60 * 60 * 1000);

        if (this.orders.length !== oldCount) {
            localStorage.setItem('offlineOrders', JSON.stringify(this.orders));
        }
    }

    showOfflineNotification() {
        if (!navigator.onLine) {
            const toast = document.createElement('div');
            toast.className = 'toast align-items-center text-bg-warning';
            toast.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-wifi-off me-2"></i>
                        Order saved offline. It will sync when you're back online.
                    </div>
                </div>
            `;

            document.getElementById('toastContainer').appendChild(toast);
            const bsToast = new bootstrap.Toast(toast, { delay: 5000 });
            bsToast.show();

            setTimeout(() => toast.remove(), 5000);
        }
    }

    getPendingCount() {
        return this.orders.filter(o => !o.synced).length;
    }
}

// ============================================
// MAIN APPLICATION
// ============================================
document.addEventListener('DOMContentLoaded', function () {
    // Initialize modules
    window.posApp = {
        cart: new POSCart(),
        search: new POSSearch(),
        ui: new POSUI(),
        offline: new OfflineManager()
    };

    const { cart, search, ui, offline } = window.posApp;

    // DOM Elements
    const elements = {
        input: document.getElementById('barcodeInput'),
        resultsBody: document.getElementById('resultsBody'),
        emptySearchRow: document.getElementById('emptySearchRow'),
        cartBody: document.getElementById('cartBody'),
        emptyCartRow: document.getElementById('emptyCartRow'),
        subtotalEl: document.getElementById('subtotal'),
        taxEl: document.getElementById('taxAmount'),
        discountEl: document.getElementById('discountAmount'),
        grandTotalEl: document.getElementById('grandTotal'),
        searchLoading: document.getElementById('searchLoading'),
        quantityModal: document.getElementById('quantityModal'),
        modalQty: document.getElementById('modalQty'),
        confirmAddBtn: document.getElementById('confirmAddBtn'),
        removeFromCartBtn: document.getElementById('removeFromCartBtn'),
        customerSelect: document.getElementById('customerSelect'),
        discountValue: document.getElementById('discountValue'),
        discountType: document.getElementById('discountType'),
        applyDiscountBtn: document.getElementById('applyDiscountBtn'),
        clearCartBtn: document.getElementById('clearCart'),
        holdOrderBtn: document.getElementById('holdOrderBtn'),
        loadHeldBtn: document.getElementById('loadHeldBtn'),
        completeOrderBtn: document.getElementById('completeOrder'),
        quickCustomerBtn: document.getElementById('quickCustomerBtn'),
        saveQuickCustomerBtn: document.getElementById('saveQuickCustomerBtn'),
        offlineModeToggle: document.getElementById('offlineModeToggle'),
        syncOfflineOrdersBtn: document.getElementById('syncOfflineOrdersBtn')
    };

    // State
    let currentProduct = null;
    let currentItemIndex = null;
    let lastSearchResults = [];
    let isOfflineMode = false;

    // Initialize
    init();

    function init() {
        updateNetworkStatus();
        renderCart();
        setupEventListeners();
        lazyLoadImages();
        autoSaveCart();

        // Check for offline orders
        if (offline.getPendingCount() > 0) {
            ui.showToast(`You have ${offline.getPendingCount()} pending offline orders`, 'info');
        }
    }

    function updateNetworkStatus() {
        const isOnline = ui.updateNetworkStatus();
        if (!isOnline) {
            elements.offlineModeToggle.checked = true;
            isOfflineMode = true;
        }
    }

    function setupEventListeners() {
        // Search input
        elements.input.addEventListener('input', search.debounceSearch(handleSearch, search.debounceDelay));
        elements.input.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                const query = elements.input.value.trim();
                if (query) handleSearch(query);
            }
        });

        // Customer selection
        elements.customerSelect.addEventListener('change', () => {
            cart.saveState();
        });

        // Quantity modal
        quantityModal.addEventListener('shown.bs.modal', () => {
            setTimeout(() => {
                elements.modalQty.focus();
                elements.modalQty.select();
            }, 100);
        });

        quantityModal.addEventListener('hidden.bs.modal', () => {
            setTimeout(() => {
                elements.input.focus();
                elements.input.select();
            }, 100);
        });

        elements.modalQty.addEventListener('input', updateModalTotal);
        elements.modalQty.addEventListener('change', updateModalTotal);

        document.getElementById('increaseQty').addEventListener('click', () => {
            elements.modalQty.value = (parseInt(elements.modalQty.value) || 1) + 1;
            updateModalTotal();
            elements.modalQty.focus();
            elements.modalQty.select();
        });

        document.getElementById('decreaseQty').addEventListener('click', () => {
            const val = parseInt(elements.modalQty.value) || 1;
            if (val > 1) {
                elements.modalQty.value = val - 1;
                updateModalTotal();
                elements.modalQty.focus();
                elements.modalQty.select();
            }
        });

        // Quick quantity buttons
        document.querySelectorAll('[data-qty]').forEach(btn => {
            btn.addEventListener('click', () => {
                elements.modalQty.value = btn.dataset.qty;
                updateModalTotal();
                elements.modalQty.focus();
                elements.modalQty.select();
            });
        });

        elements.modalQty.addEventListener('keydown', e => {
            if (e.key === 'Enter') {
                e.preventDefault();
                elements.confirmAddBtn.click();
            }
        });

        // Cart operations
        elements.confirmAddBtn.addEventListener('click', addOrUpdateProductInCart);
        elements.removeFromCartBtn.addEventListener('click', removeCurrentProductFromCart);
        elements.clearCartBtn.addEventListener('click', clearCart);
        elements.holdOrderBtn.addEventListener('click', holdOrder);
        elements.loadHeldBtn.addEventListener('click', loadHeldOrders);
        elements.completeOrderBtn.addEventListener('click', completeOrder);

        // Discounts
        elements.applyDiscountBtn.addEventListener('click', applyOrderDiscount);
        elements.discountValue.addEventListener('input', validateDiscount);
        elements.discountType.addEventListener('change', () => {
            cart.discount.type = elements.discountType.value;
            validateDiscount();
        });

        // Item discount
        document.getElementById('applyItemDiscountBtn').addEventListener('click', applyItemDiscount);

        // Quick customer
        elements.quickCustomerBtn.addEventListener('click', () => {
            ui.modals.quickCustomer.show();
        });

        elements.saveQuickCustomerBtn.addEventListener('click', saveQuickCustomer);

        // Offline mode
        elements.offlineModeToggle.addEventListener('change', toggleOfflineMode);
        elements.syncOfflineOrdersBtn.addEventListener('click', syncOfflineOrders);

        // Global click handlers
        document.addEventListener('click', handleGlobalClicks);

        // Refocus search input when clicking outside
        document.addEventListener('click', e => {
            const isModalOpen = document.querySelector('.modal.show');
            const isModalElement = e.target.closest('.modal');
            const isBackdrop = e.target.classList.contains('modal-backdrop');
            const isSearch = e.target === elements.input || elements.input.contains(e.target);
            const isCustomer = e.target === elements.customerSelect || elements.customerSelect.contains(e.target);

            if (!isModalOpen && !isModalElement && !isBackdrop && !isSearch && !isCustomer) {
                setTimeout(() => {
                    elements.input.focus();
                    elements.input.select();
                }, 50);
            }
        });
    }

    function handleGlobalClicks(e) {
        // Quantity buttons in product table
        if (e.target.classList.contains('qty-btn') || e.target.closest('.qty-btn')) {
            const btn = e.target.classList.contains('qty-btn') ? e.target : e.target.closest('.qty-btn');
            if (!btn.disabled) openQuantityModal(btn);
        }

        // Remove from table button
        if (e.target.classList.contains('remove-from-table-btn') || e.target.closest('.remove-from-table-btn')) {
            const btn = e.target.classList.contains('remove-from-table-btn') ? e.target : e.target.closest('.remove-from-table-btn');
            removeProductFromSelection(btn.dataset.productId);
        }

        // Quantity buttons in cart
        if (e.target.classList.contains('qty-btn-cart') || e.target.closest('.qty-btn-cart')) {
            const btn = e.target.classList.contains('qty-btn-cart') ? e.target : e.target.closest('.qty-btn-cart');
            openQuantityModal(btn);
        }

        // Item discount buttons
        if (e.target.classList.contains('item-discount-btn') || e.target.closest('.item-discount-btn')) {
            const btn = e.target.classList.contains('item-discount-btn') ? e.target : e.target.closest('.item-discount-btn');
            currentItemIndex = cart.cart.findIndex(item => item.product_id === btn.dataset.productId);
            if (currentItemIndex === -1) return;

            const item = cart.cart[currentItemIndex];
            document.getElementById('itemName').textContent = item.title;
            document.getElementById('itemDiscountValue').value = item.discount_value || 0;
            document.getElementById('itemDiscountType').value = item.discount_type || 'percent';
            ui.modals.itemDiscount.show();
        }

        // Load held order buttons
        if (e.target.classList.contains('load-order-btn') || e.target.closest('.load-order-btn')) {
            const btn = e.target.classList.contains('load-order-btn') ? e.target : e.target.closest('.load-order-btn');
            const orderId = btn.dataset.orderId;
            const heldOrders = JSON.parse(localStorage.getItem('heldOrders') || '[]');
            const order = heldOrders.find(o => o.id == orderId);
            if (order) {
                if (cart.cart.length > 0) {
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
        }

        // Remove held order buttons
        if (e.target.classList.contains('remove-order-btn') || e.target.closest('.remove-order-btn')) {
            const btn = e.target.classList.contains('remove-order-btn') ? e.target : e.target.closest('.remove-order-btn');
            const orderId = btn.dataset.orderId;
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
                    elements.loadHeldBtn.click();
                    ui.showToast('Order removed', 'success');
                }
            });
        }
    }

    async function handleSearch(query) {
        if (!query || query.length < search.minSearchLength) {
            showEmptySearchState();
            return;
        }

        ui.showLoading(true);
        ui.updateAccessibilityStatus(`Searching for "${query}"`, 'search');

        try {
            const results = await search.search(query);
            lastSearchResults = results;

            if (results.length === 0) {
                showEmptySearchState();
                ui.updateAccessibilityStatus(`No results found for "${query}"`, 'search');
            } else {
                renderSearchResults(results);
                ui.updateAccessibilityStatus(`Found ${results.length} results for "${query}"`, 'search');
            }
        } finally {
            ui.showLoading(false);
        }
    }

    function renderSearchResults(products) {
        elements.resultsBody.innerHTML = '';

        if (products.length === 0) {
            elements.emptySearchRow.style.display = '';
            elements.resultsBody.appendChild(elements.emptySearchRow);
            return;
        }

        elements.emptySearchRow.style.display = 'none';

        products.forEach(product => {
            const isSelected = cart.selectedProducts.some(p => p.id === product.id);
            const cartItem = cart.cart.find(item => item.product_id === product.id);
            const addedQty = cartItem ? cartItem.qty : 0;
            const cachedQty = cart.cache[product.id] || 1;
            const displayQty = addedQty > 0 ? addedQty : (cachedQty > 1 ? `(${cachedQty})` : '');
            const isOutOfStock = product.stock <= 0;

            const btnClass = addedQty > 0
                ? 'btn-success'
                : (isOutOfStock ? 'btn-secondary' : 'btn-outline-primary');
            const btnText = addedQty > 0
                ? `Added ${addedQty}`
                : (isOutOfStock ? 'Out of Stock' : `Set Qty ${displayQty}`);
            const btnDisabled = isOutOfStock ? 'disabled' : '';

            const highlightedTitle = search.highlightMatches(product.title, search.currentQuery);
            const highlightedSku = search.highlightMatches(product.sku, search.currentQuery);

            const row = document.createElement('tr');
            row.dataset.productId = product.id;
            row.className = isSelected ? 'selected-product-row table-success' : '';
            row.innerHTML = `
                <td>
                    <div class="d-flex align-items-center">
                        ${product.thumbnail ?
                            `<img data-src="${product.thumbnail}" width="50" height="50" class="rounded me-3" alt="${product.title}" loading="lazy">` :
                            '<div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width:50px;height:50px;"><i class="bi bi-image text-muted"></i></div>'
                        }
                        <div>
                            <strong>${highlightedTitle}</strong><br>
                            <small class="text-muted">SKU: ${highlightedSku}</small>
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <span class="badge bg-${product.stock > 10 ? 'success' : product.stock > 0 ? 'warning' : 'danger'}">
                        ${product.stock}
                    </span>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm ${btnClass} qty-btn"
                            data-product='${JSON.stringify(product).replace(/'/g, "&apos;")}'
                            data-product-id="${product.id}"
                            ${btnDisabled}
                            aria-label="${isOutOfStock ? 'Out of stock' : `Set quantity for ${product.title}`}">
                        ${btnText}
                    </button>
                    ${isSelected ?
                        `<button class="btn btn-sm btn-danger ms-2 remove-from-table-btn"
                                data-product-id="${product.id}"
                                aria-label="Remove ${product.title} from selection">
                            <i class="bi bi-trash"></i> Remove
                        </button>` : ''
                    }
                </td>
                <td class="text-end fw-bold">₦${parseFloat(product.sale_price || product.price).toFixed(2)}</td>
                <td class="text-center"><span class="badge bg-info">${product.primary_unit || 'Unit'}</span></td>
            `;
            elements.resultsBody.appendChild(row);
        });

        lazyLoadImages();
    }

    function showEmptySearchState() {
        ui.showLoading(false);
        elements.resultsBody.innerHTML = '';
        elements.emptySearchRow.style.display = '';
        elements.resultsBody.appendChild(elements.emptySearchRow);
    }

    function openQuantityModal(button) {
        try {
            const product = JSON.parse(button.dataset.product);
            const productId = button.dataset.productId;
            currentProduct = product;

            const cartItem = cart.cart.find(item => item.product_id === productId);
            const cachedQty = cart.cache[productId] || 1;
            const previousQty = cartItem ? cartItem.qty : cachedQty;

            // Update modal content
            document.getElementById('modalProductLabel').textContent = product.title;
            const price = product.sale_price || product.price;
            document.getElementById('modalProductPrice').textContent = `₦${parseFloat(price).toFixed(2)}`;
            document.getElementById('modalProductStock').textContent = `Stock: ${product.stock}`;
            document.getElementById('modalProductUnit').textContent = product.primary_unit || 'Unit';

            elements.modalQty.value = previousQty;
            document.getElementById('previousQtyText').textContent =
                cartItem ? `In cart: ${previousQty}` : `Previous: ${previousQty}`;
            document.getElementById('previousQtyText').className =
                cartItem ? 'text-success fw-semibold' : 'text-muted';

            elements.removeFromCartBtn.style.display = cartItem ? 'inline-block' : 'none';

            updateModalTotal();
            ui.modals.quantity.show();
        } catch (error) {
            console.error('Error opening quantity modal:', error);
            ui.showToast('Error loading product details', 'error');
        }
    }

    function updateModalTotal() {
        if (!currentProduct) return;
        const qty = parseInt(elements.modalQty.value) || 1;
        const price = currentProduct.sale_price || currentProduct.price;
        const total = qty * price;
        document.getElementById('totalPriceDisplay').textContent = `Total: ₦${total.toFixed(2)}`;
    }

    function addOrUpdateProductInCart() {
        if (!currentProduct) return;

        // Validate stock
        if (currentProduct.stock <= 0) {
            ui.showToast(`${currentProduct.title} is out of stock`, 'error');
            ui.modals.quantity.hide();
            return;
        }

        const qty = parseInt(elements.modalQty.value) || 1;
        if (qty < 1) {
            ui.showToast('Quantity must be at least 1', 'warning');
            return;
        }

        if (qty > currentProduct.stock) {
            ui.showToast(`Only ${currentProduct.stock} units available`, 'warning');
            elements.modalQty.value = currentProduct.stock;
            updateModalTotal();
            return;
        }

        const action = cart.addItem(currentProduct, qty);
        ui.modals.quantity.hide();

        renderCart();
        renderSearchResults(lastSearchResults);

        elements.input.value = '';
        currentProduct = null;

        ui.showToast(`Product ${action === 'updated' ? 'updated' : 'added'} to cart`, 'success');
        ui.updateAccessibilityStatus(`Added ${qty} of ${currentProduct?.title} to cart. Total items: ${cart.cart.length}`);
    }

    function removeProductFromSelection(productId) {
        const removed = cart.removeItem(productId);
        if (removed) {
            renderCart();
            renderSearchResults(lastSearchResults);
            ui.showToast('Product removed from cart', 'success');
        }
    }

    function removeCurrentProductFromCart() {
        if (!currentProduct) return;

        Swal.fire({
            title: 'Remove from Cart?',
            text: `Remove ${currentProduct.title} from cart?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, remove'
        }).then(result => {
            if (result.isConfirmed) {
                cart.removeItem(currentProduct.id);
                ui.modals.quantity.hide();
                renderCart();
                renderSearchResults(lastSearchResults);
                ui.showToast('Product removed from cart', 'success');
            }
        });
    }

    function renderCart() {
        if (cart.cart.length === 0) {
            elements.emptyCartRow.style.display = '';
            elements.cartBody.innerHTML = '';
            updateTotals();
            return;
        }

        elements.emptyCartRow.style.display = 'none';
        elements.cartBody.innerHTML = '';

        cart.cart.forEach((item, index) => {
            const unitPrice = item.discount_value > 0 ?
                (item.discount_type === 'percent' ?
                    item.price * (1 - item.discount_value / 100) :
                    item.price - item.discount_value) :
                item.price;

            const total = item.qty * unitPrice;
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${index + 1}</td>
                <td>
                    <div class="d-flex align-items-center">
                        ${item.thumbnail ?
                            `<img src="${item.thumbnail}" width="40" height="40" class="rounded me-2" alt="${item.title}" loading="lazy">` :
                            '<div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width:40px;height:40px;"><i class="bi bi-image text-muted"></i></div>'
                        }
                        <div>
                            <strong>${item.title}</strong><br>
                            <small class="text-muted">${item.sku ? 'SKU: ' + item.sku : ''}</small>
                            ${item.discount_value > 0 ?
                                `<small class="text-warning">-${item.discount_value}${item.discount_type === 'percent' ? '%' : '₦'}</small>` :
                                ''
                            }
                        </div>
                    </div>
                </td>
                <td class="text-center fw-bold">
                    <button class="btn btn-sm btn-primary qty-btn-cart"
                            data-product='${JSON.stringify(item).replace(/'/g, "&apos;")}'
                            data-product-id="${item.product_id}"
                            aria-label="Change quantity for ${item.title}, current quantity ${item.qty}">
                        ${item.qty}
                    </button>
                </td>
                <td class="text-end">₦${unitPrice.toFixed(2)}</td>
                <td class="text-end fw-bold">₦${total.toFixed(2)}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-warning rounded-circle item-discount-btn"
                            data-product-id="${item.product_id}"
                            aria-label="Apply discount to ${item.title}">
                        <i class="bi bi-percent"></i>
                    </button>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-danger rounded-circle"
                            onclick="removeCartItem(${index})"
                            aria-label="Remove ${item.title} from cart">
                        <i class="bi bi-x"></i>
                    </button>
                </td>
            `;
            elements.cartBody.appendChild(row);
        });

        updateTotals();
    }

    function updateTotals() {
        const totals = cart.getTotals();
        elements.subtotalEl.textContent = `₦${totals.subtotal}`;
        elements.taxEl.textContent = `₦${totals.tax}`;
        elements.discountEl.textContent = `-₦${totals.discount}`;
        elements.grandTotalEl.textContent = `₦${totals.grandTotal}`;

        // Update accessibility status
        ui.updateAccessibilityStatus(
            `Cart updated. ${totals.itemCount} items. Total: ₦${totals.grandTotal}`
        );
    }

    window.removeCartItem = function(index) {
        const product = cart.cart[index];
        if (product) {
            cart.removeItem(product.product_id);
            renderCart();
            renderSearchResults(lastSearchResults);
            ui.showToast(`${product.title} removed from cart`, 'success');
        }
    };

    function clearCart() {
        if (cart.cart.length === 0) {
            ui.showToast('Cart is already empty', 'info');
            return;
        }

        Swal.fire({
            title: 'Clear Cart?',
            text: 'This will remove all items from your cart.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, clear cart'
        }).then(result => {
            if (result.isConfirmed) {
                cart.clear();
                renderCart();
                renderSearchResults(lastSearchResults);
                ui.showToast('Cart cleared', 'success');
            }
        });
    }

    function holdOrder() {
        if (cart.cart.length === 0) {
            ui.showToast('Cart is empty', 'info');
            return;
        }

        const heldOrders = JSON.parse(localStorage.getItem('heldOrders') || '[]');
        heldOrders.push({
            id: Date.now(),
            cart: [...cart.cart],
            customer: elements.customerSelect.value,
            selectedProducts: [...cart.selectedProducts],
            cache: {...cart.cache},
            discount: {...cart.discount},
            time: new Date().toLocaleString(),
            timestamp: Date.now()
        });

        localStorage.setItem('heldOrders', JSON.stringify(heldOrders));
        cart.clear();
        renderCart();
        renderSearchResults(lastSearchResults);

        ui.showToast('Order held successfully', 'success');
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
                    const price = item.discount_value > 0 ?
                        (item.discount_type === 'percent' ?
                            item.price * (1 - item.discount_value / 100) :
                            item.price - item.discount_value) :
                        item.price;
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
                                <button class="btn btn-sm btn-outline-primary load-order-btn"
                                        data-order-id="${order.id}">
                                    Load
                                </button>
                                <button class="btn btn-sm btn-outline-danger remove-order-btn"
                                        data-order-id="${order.id}">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });

            html += '</div>';
            list.innerHTML = html;
        }

        ui.modals.loadOrder.show();
    }

    function loadOrderFromHeld(order) {
        cart.cart = [...order.cart];
        cart.selectedProducts = [...order.selectedProducts];
        cart.cache = {...order.cache};
        cart.discount = {...order.discount};

        if (order.customer) {
            elements.customerSelect.value = order.customer;
        }

        renderCart();
        renderSearchResults(lastSearchResults);
        ui.modals.loadOrder.hide();

        ui.showToast('Order loaded successfully', 'success');
    }

    function validateDiscount() {
        const value = parseFloat(elements.discountValue.value) || 0;

        if (elements.discountType.value === 'percent' && value > 100) {
            elements.discountValue.value = 100;
            cart.discount.value = 100;
        } else {
            cart.discount.value = value;
        }
    }

    function applyOrderDiscount() {
        cart.discount.type = elements.discountType.value;
        cart.discount.value = parseFloat(elements.discountValue.value) || 0;

        updateTotals();
        cart.saveState();

        ui.showToast(
            `Discount applied: ${cart.discount.value}${cart.discount.type === 'percent' ? '%' : '₦'}`,
            'success'
        );
    }

    function applyItemDiscount() {
        if (currentItemIndex === null || !cart.cart[currentItemIndex]) return;

        const value = parseFloat(document.getElementById('itemDiscountValue').value) || 0;
        const type = document.getElementById('itemDiscountType').value;

        if (type === 'percent' && value > 100) {
            ui.showToast('Percentage cannot exceed 100%', 'warning');
            return;
        }

        cart.updateItem(currentItemIndex, {
            discount_type: type,
            discount_value: value
        });

        ui.modals.itemDiscount.hide();
        renderCart();

        ui.showToast(
            `Item discount applied: ${value}${type === 'percent' ? '%' : '₦'}`,
            'success'
        );
    }

    async function completeOrder() {
        // Validate cart
        const errors = cart.validate();
        if (errors.length > 0) {
            Swal.fire({
                title: 'Cart Issues',
                html: errors.join('<br>'),
                icon: 'error'
            });
            return;
        }

        // Get payment method
        const paymentMethod = document.querySelector('input[name="payment"]:checked').value;
        const customerId = elements.customerSelect.value || null;

        // Prepare order data
        const orderData = cart.getOrderData(customerId, paymentMethod);

        // Check offline mode
        if (isOfflineMode || !navigator.onLine) {
            saveOrderOffline(orderData);
            return;
        }

        // Process online order
        await processOnlineOrder(orderData);
    }

    async function processOnlineOrder(orderData) {
        Swal.fire({
            title: 'Processing Order...',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        try {
            const response = await axios.post('{{ route("pos.order.save") }}', orderData);

            Swal.close();

            if (response.data.success) {
                await showSuccessModal(response.data);
                resetAfterOrder();
            } else {
                Swal.fire('Error', response.data.message || 'Failed to process order', 'error');
            }
        } catch (error) {
            Swal.close();

            let errorMessage = 'Failed to complete order.';
            if (error.response?.data?.errors) {
                errorMessage = Object.values(error.response.data.errors).flat().join('<br>');
            } else if (error.response?.data?.message) {
                errorMessage = error.response.data.message;
            }

            Swal.fire('Error', errorMessage, 'error');

            // Offer offline save
            if (!navigator.onLine) {
                saveOrderOffline(orderData);
            }
        }
    }

    function saveOrderOffline(orderData) {
        Swal.fire({
            title: 'Save Order Offline?',
            text: 'You are offline. The order will be saved locally and synced when you are back online.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Save Offline',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (result.isConfirmed) {
                const orderId = offline.saveOrder(orderData);
                resetAfterOrder();

                Swal.fire({
                    title: 'Saved Offline!',
                    html: `Order saved locally. It will sync when you're back online.<br><br>
                           <small>Order ID: ${orderId}</small>`,
                    icon: 'success'
                });
            }
        });
    }

    async function showSuccessModal(responseData) {
        const result = await Swal.fire({
            title: 'Success!',
            html: `
                <div class="text-center">
                    <i class="bi bi-check-circle text-success display-1 mb-3"></i>
                    <h4>Order #${responseData.order_id} Completed!</h4>
                    <p class="fs-3">Total: ₦${parseFloat(responseData.total).toFixed(2)}</p>
                    <div class="mt-4">
                        <p class="text-muted">Payment Method: ${responseData.payment_method}</p>
                    </div>
                </div>
            `,
            icon: 'success',
            showCancelButton: true,
            confirmButtonText: 'Print Receipt',
            cancelButtonText: 'New Order',
            buttonsStyling: false,
            customClass: {
                confirmButton: 'btn btn-success btn-lg me-2',
                cancelButton: 'btn btn-outline-secondary btn-lg'
            }
        });

        if (result.isConfirmed) {
            printReceipt(responseData.order_id);
        }
    }

    function resetAfterOrder() {
        cart.clear();
        elements.customerSelect.value = '';
        elements.discountValue.value = '0';
        cart.discount = { type: 'percent', value: 0, amount: 0 };
        updateTotals();
        renderSearchResults([]);

        elements.input.value = '';
        elements.input.focus();
        elements.input.select();
    }

    function printReceipt(orderId) {
        const printWindow = window.open(`/pos/receipt/${orderId}?print=true`, '_blank', 'noopener,noreferrer');

        if (printWindow) {
            printWindow.onload = function() {
                printWindow.focus();

                // Add print-specific styles
                const style = printWindow.document.createElement('style');
                style.textContent = `
                    @media print {
                        body { font-size: 12pt; line-height: 1.4; }
                        .no-print { display: none !important; }
                        .receipt-header { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
                        .receipt-footer { margin-top: 20px; border-top: 1px dashed #000; padding-top: 10px; font-size: 10pt; }
                        .receipt-item { border-bottom: 1px dashed #ccc; padding: 5px 0; }
                        .text-right { text-align: right; }
                        .text-center { text-align: center; }
                        .fw-bold { font-weight: bold; }
                        .mt-3 { margin-top: 15px; }
                        .mb-3 { margin-bottom: 15px; }
                        .pt-3 { padding-top: 15px; }
                    }
                    @page { margin: 0.5cm; }
                `;
                printWindow.document.head.appendChild(style);

                // Auto-print after a short delay
                setTimeout(() => {
                    printWindow.print();
                    printWindow.onafterprint = () => {
                        setTimeout(() => printWindow.close(), 500);
                    };
                }, 500);
            };
        }
    }

    async function saveQuickCustomer() {
        const firstName = document.getElementById('firstName').value.trim();
        const lastName = document.getElementById('lastName').value.trim();
        const phoneNumber = document.getElementById('phoneNumber').value.trim();
        const email = document.getElementById('email').value.trim();

        if (!firstName || !lastName) {
            ui.showToast('First name and last name are required', 'warning');
            return;
        }

        try {
            const response = await axios.post('{{ route("customers.quick") }}', {
                first_name: firstName,
                last_name: lastName,
                phone_number: phoneNumber,
                email: email,
                _token: '{{ csrf_token() }}'
            });

            if (response.data.success) {
                // Add to dropdown
                const option = document.createElement('option');
                option.value = response.data.customer.id;
                option.textContent = `${firstName} ${lastName}${phoneNumber ? ` - ${phoneNumber}` : ''}`;
                elements.customerSelect.appendChild(option);

                // Select the new customer
                elements.customerSelect.value = response.data.customer.id;

                // Update count
                const count = parseInt(document.getElementById('customerCount').textContent) + 1;
                document.getElementById('customerCount').textContent = count;

                // Close modal and reset form
                ui.modals.quickCustomer.hide();
                document.getElementById('quickCustomerForm').reset();

                ui.showToast('Customer added successfully', 'success');
            }
        } catch (error) {
            ui.showToast('Failed to add customer', 'error');
        }
    }

    function toggleOfflineMode() {
        isOfflineMode = elements.offlineModeToggle.checked;

        if (isOfflineMode) {
            ui.showToast('Offline mode enabled. Orders will be saved locally.', 'warning');
        } else {
            ui.showToast('Online mode enabled', 'success');
        }
    }

    function syncOfflineOrders() {
        ui.showToast('Syncing offline orders...', 'info');
        offline.attemptSync().then(() => {
            ui.showToast('Sync completed', 'success');
        });
    }

    function lazyLoadImages() {
        const images = document.querySelectorAll('img[data-src]');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        });
        images.forEach(img => observer.observe(img));
    }

    function autoSaveCart() {
        setInterval(() => {
            if (cart.cart.length > 0) {
                cart.saveState();
            }
        }, 30000); // Save every 30 seconds
    }
});
</script>

<style>
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
