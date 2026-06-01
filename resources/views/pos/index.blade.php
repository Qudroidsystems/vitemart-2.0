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
                <!-- Left: Product Search & Grid -->
                <div class="col-xxl-8">
                    <div class="card h-100">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title mb-4">
                                <i class="bi bi-search me-2"></i>Search or Scan Products
                            </h5>

                            <!-- Search Bar -->
                            <div class="position-relative mb-3">
                                <input type="text" id="barcodeInput" class="form-control form-control-lg fs-3"
                                       placeholder="🔍 Scan barcode or search by name/SKU..." autofocus autocomplete="off"
                                       aria-label="Search or scan products">
                                <i class="bi bi-upc-scan position-absolute top-50 end-0 translate-middle-y me-4 fs-2 text-muted"></i>
                            </div>

                            <!-- Toolbar -->
                            <div class="d-flex align-items-center mb-3 flex-wrap gap-2">
                                <div class="me-auto">
                                    <small class="text-muted">
                                        <i class="bi bi-keyboard"></i> Shortcuts:
                                    </small>
                                    <span class="badge bg-secondary ms-1">F1</span> Focus
                                    <span class="badge bg-secondary ms-2">F2</span> Clear
                                    <span class="badge bg-secondary ms-2">F3</span> Checkout
                                    <span class="badge bg-secondary ms-2">F4</span> Hold
                                </div>
                                <div class="btn-group me-2" role="group" id="viewToggleGroup">
                                    <input type="radio" class="btn-check" name="viewMode" id="viewModeGrid" value="grid" checked>
                                    <label class="btn btn-outline-primary btn-sm" for="viewModeGrid">
                                        <i class="bi bi-grid-3x3-gap me-1"></i> Grid
                                    </label>
                                    <input type="radio" class="btn-check" name="viewMode" id="viewModeList" value="list">
                                    <label class="btn btn-outline-secondary btn-sm" for="viewModeList">
                                        <i class="bi bi-list-ul me-1"></i> List
                                    </label>
                                </div>
                                <div>
                                    <span id="connectionStatus" class="badge bg-success">
                                        <i class="bi bi-wifi"></i> Online
                                    </span>
                                </div>
                            </div>

                            <!-- Category Filter -->
                            <div class="mb-3" id="categoryFilterBar">
                                <div class="d-flex gap-2 flex-wrap" id="categoryTabs">
                                    <span class="category-tab active" data-cat="all">
                                        <i class="bi bi-grid"></i> All
                                    </span>
                                </div>
                            </div>

                            <!-- Products Container -->
                            <div class="products-container flex-grow-1 position-relative">
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

                                <!-- GRID VIEW -->
                                <div id="gridViewContainer">
                                    <div id="gridViewBody" class="pos-grid-container"></div>
                                    <div id="emptyGridRow" class="text-center py-5 text-muted" style="display:none;">
                                        <i class="bi bi-box-seam display-1 mb-4 text-light"></i>
                                        <h5>No products found</h5>
                                        <p class="mb-0">Try a different search term</p>
                                    </div>
                                </div>

                                <!-- LIST VIEW -->
                                <div id="listViewContainer" style="display:none;">
                                    <div class="table-responsive">
                                        <table class="table table-hover align-middle mb-0">
                                            <thead class="table-light sticky-top">
                                                <tr>
                                                    <th>Product</th>
                                                    <th class="text-center">Stock</th>
                                                    <th class="text-center">Actions</th>
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
                    </div>
                </div>

                <!-- Right: Order Summary -->
                <div class="col-xxl-4">
                    <div class="card h-100">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-cart-fill me-2"></i>Order Summary
                            </h5>
                            <div>
                                <button class="btn btn-sm btn-light me-2" id="holdOrderBtn" title="Hold order">
                                    <i class="bi bi-pause-circle"></i>
                                </button>
                                <button class="btn btn-sm btn-light me-2" id="loadHeldBtn" title="Load held orders">
                                    <i class="bi bi-folder-symlink"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" id="clearCart" title="Clear cart">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <!-- Customer Selection -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">
                                    <i class="bi bi-person"></i> Customer
                                    <a href="javascript:void(0)" class="text-decoration-none ms-2" id="quickCustomerBtn">
                                        <i class="bi bi-person-plus text-primary"></i>
                                    </a>
                                </label>
                                <select class="form-select" id="customerSelect">
                                    <option value="">👤 Walk-in Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">
                                            {{ $customer->first_name }} {{ $customer->last_name }}
                                            @if($customer->phone_number) - {{ $customer->phone_number }} @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Cart Items -->
                            <div class="flex-grow-1 mb-4">
                                <h6 class="fw-bold mb-3">
                                    <i class="bi bi-bag-check me-2"></i>Cart Items
                                    <span id="cartCount" class="badge bg-secondary ms-2">0</span>
                                </h6>
                                <div class="cart-items-container" style="max-height: 350px; overflow-y: auto;">
                                    <table class="table table-sm mb-0">
                                        <thead class="table-light sticky-top">
                                            <tr>
                                                <th>Item</th>
                                                <th class="text-center">Qty</th>
                                                <th class="text-end">Price</th>
                                                <th class="text-end">Total</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="cartBody">
                                            <tr id="emptyCartRow">
                                                <td colspan="5" class="text-center py-5 text-muted">
                                                    <i class="bi bi-cart fs-1 mb-3 d-block"></i>
                                                    No items in cart
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Order Totals -->
                            <div class="border-top pt-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal</span>
                                    <span id="subtotal" class="fw-bold">₦0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tax ({{ config('pos.tax_rate', 0) }}%)</span>
                                    <span id="taxAmount" class="text-muted">₦0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2" id="discountRow" style="display: none;">
                                    <span>Discount</span>
                                    <span id="discountAmount" class="text-danger">-₦0.00</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between fs-3 fw-bold text-success">
                                    <span>Total</span>
                                    <span id="grandTotal">₦0.00</span>
                                </div>
                            </div>

                            <!-- Order Discount -->
                            <div class="mt-3">
                                <label class="form-label fw-semibold">Order Discount</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" id="discountValue" class="form-control text-end"
                                           placeholder="0" min="0" step="0.01" value="0">
                                    <select id="discountType" class="form-select" style="width: 70px;">
                                        <option value="percent">%</option>
                                        <option value="fixed">₦</option>
                                    </select>
                                    <button class="btn btn-outline-primary" type="button" id="applyDiscountBtn">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Payment Method -->
                            <div class="mt-3">
                                <label class="form-label fw-semibold">Payment Method</label>
                                <div class="d-flex gap-2">
                                    <div class="form-check flex-fill">
                                        <input class="form-check-input" type="radio" name="payment" id="cash" value="cash" checked>
                                        <label class="form-check-label d-block text-center p-2 border rounded" for="cash">
                                            <i class="bi bi-cash-coin"></i> Cash
                                        </label>
                                    </div>
                                    <div class="form-check flex-fill">
                                        <input class="form-check-input" type="radio" name="payment" id="card" value="card">
                                        <label class="form-check-label d-block text-center p-2 border rounded" for="card">
                                            <i class="bi bi-credit-card"></i> Card
                                        </label>
                                    </div>
                                    <div class="form-check flex-fill">
                                        <input class="form-check-input" type="radio" name="payment" id="transfer" value="transfer">
                                        <label class="form-check-label d-block text-center p-2 border rounded" for="transfer">
                                            <i class="bi bi-bank"></i> Transfer
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="mt-3 d-grid gap-2">
                                <button class="btn btn-success btn-lg" id="completeOrder">
                                    <i class="bi bi-printer me-2"></i> Complete & Print
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ==================== MODALS ==================== -->

<!-- Quantity Modal -->
<div class="modal fade" id="quantityModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-cart-plus me-2"></i>
                    <span id="modalProductName"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="product-detail-card p-3 bg-light rounded">
                        <div class="row">
                            <div class="col-6 text-start">
                                <small class="text-muted">Price:</small>
                                <h6 id="modalProductPrice" class="text-primary mb-0"></h6>
                            </div>
                            <div class="col-6 text-end">
                                <small class="text-muted">Stock:</small>
                                <h6 id="modalProductStock" class="mb-0"></h6>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-12">
                                <small class="text-muted">Unit:</small>
                                <span id="modalProductUnit" class="badge bg-secondary"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Unit Type Selection -->
                <div class="btn-group w-100 mb-3" role="group">
                    <input type="radio" class="btn-check" name="measurementType" id="measureQuantity" value="quantity" checked>
                    <label class="btn btn-outline-primary" for="measureQuantity">
                        <i class="bi bi-123 me-1"></i> Quantity (Pieces)
                    </label>
                    <input type="radio" class="btn-check" name="measurementType" id="measureUnit" value="unit">
                    <label class="btn btn-outline-success" for="measureUnit">
                        <i class="bi bi-scale me-1"></i> Unit (Weight/Volume)
                    </label>
                </div>

                <!-- Unit Selection (for unit mode) -->
                <div id="unitSelection" class="mb-3" style="display: none;">
                    <label class="form-label fw-semibold">Select Unit</label>
                    <select class="form-select" id="unitSelect">
                        <option value="">-- Select unit --</option>
                    </select>
                </div>

                <!-- Quantity Input -->
                <div id="quantityInputSection">
                    <label class="form-label fw-semibold">Quantity</label>
                    <div class="input-group input-group-lg">
                        <button class="btn btn-outline-secondary" type="button" id="decreaseQty">
                            <i class="bi bi-dash-lg"></i>
                        </button>
                        <input type="number" id="modalQty" class="form-control text-center fs-3 fw-bold"
                               min="1" value="1" step="1">
                        <button class="btn btn-outline-secondary" type="button" id="increaseQty">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Unit Amount Input -->
                <div id="unitInputSection" style="display: none;">
                    <label class="form-label fw-semibold">Amount</label>
                    <div class="input-group input-group-lg">
                        <button class="btn btn-outline-secondary" type="button" id="decreaseUnit">
                            <i class="bi bi-dash-lg"></i>
                        </button>
                        <input type="number" id="modalUnit" class="form-control text-center fs-3 fw-bold"
                               min="0.001" value="1" step="0.001">
                        <button class="btn btn-outline-secondary" type="button" id="increaseUnit">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Quick Amount Buttons -->
                <div class="mt-3">
                    <div class="row g-2" id="quickButtonsContainer">
                        <!-- Quick buttons will be populated dynamically -->
                    </div>
                </div>

                <!-- Total Display -->
                <div class="alert alert-success mt-3 mb-0 text-center">
                    <strong>Total:</strong> <span id="modalTotalDisplay" class="fs-4">₦0.00</span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="removeFromCartBtn">
                    <i class="bi bi-trash me-1"></i> Remove
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-primary" id="confirmAddBtn">
                    <i class="bi bi-check-circle me-1"></i> Add to Cart
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Item Discount Modal -->
<div class="modal fade" id="itemDiscountModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title">
                    <i class="bi bi-percent me-2"></i> Item Discount
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 id="discountItemName" class="text-center mb-3"></h6>
                <div class="input-group">
                    <input type="number" id="itemDiscountValue" class="form-control" placeholder="0" min="0" step="0.01">
                    <select id="itemDiscountType" class="form-select" style="width: 80px;">
                        <option value="percent">%</option>
                        <option value="fixed">₦</option>
                    </select>
                </div>
                <small class="text-muted mt-2 d-block">Leave 0 to remove discount</small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="applyItemDiscountBtn">Apply</button>
            </div>
        </div>
    </div>
</div>

<!-- Held Orders Modal -->
<div class="modal fade" id="loadOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Held Orders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="heldOrdersList"></div>
                <div id="noHeldOrders" class="text-center py-5 text-muted">
                    <i class="bi bi-inbox fs-1 mb-3 d-block"></i>
                    <p>No held orders found</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Customer Modal -->
<div class="modal fade" id="quickCustomerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="bi bi-person-plus me-2"></i> Quick Add Customer
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickCustomerForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">First Name *</label>
                        <input type="text" class="form-control" id="firstName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Name *</label>
                        <input type="text" class="form-control" id="lastName" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phoneNumber">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" id="email">
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

<!-- Toast Notification Container -->
<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

<script src="{{ asset('theme/layouts/assets/libs/chart.js/chart.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/libs/axios/axios.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // ============================================
    // DOM Elements
    // ============================================
    const input = document.getElementById('barcodeInput');
    const gridViewBody = document.getElementById('gridViewBody');
    const emptyGridRow = document.getElementById('emptyGridRow');
    const resultsBody = document.getElementById('resultsBody');
    const emptySearchRow = document.getElementById('emptySearchRow');
    const listViewContainer = document.getElementById('listViewContainer');
    const gridViewContainer = document.getElementById('gridViewContainer');
    const cartBody = document.getElementById('cartBody');
    const emptyCartRow = document.getElementById('emptyCartRow');
    const subtotalEl = document.getElementById('subtotal');
    const discountAmountEl = document.getElementById('discountAmount');
    const grandTotalEl = document.getElementById('grandTotal');
    const taxAmountEl = document.getElementById('taxAmount');
    const cartCountEl = document.getElementById('cartCount');
    const customerSelect = document.getElementById('customerSelect');
    const discountValue = document.getElementById('discountValue');
    const discountType = document.getElementById('discountType');
    const applyDiscountBtn = document.getElementById('applyDiscountBtn');
    const completeOrderBtn = document.getElementById('completeOrder');
    const clearCartBtn = document.getElementById('clearCart');
    const holdOrderBtn = document.getElementById('holdOrderBtn');
    const loadHeldBtn = document.getElementById('loadHeldBtn');
    const quickCustomerBtn = document.getElementById('quickCustomerBtn');
    const offlineModeToggle = document.getElementById('offlineModeToggle');
    const connectionStatus = document.getElementById('connectionStatus');

    // ============================================
    // State
    // ============================================
    let cart = [];
    let allProducts = [];
    let currentProduct = null;
    let currentItemIndex = null;
    let orderDiscountType = 'percent';
    let orderDiscountValue = 0;
    let currentViewMode = localStorage.getItem('posViewMode') || 'grid';
    let activeCategory = 'all';
    let categories = new Set();
    let isProcessingOrder = false;
    let quantityModal = null;
    let heldOrders = JSON.parse(localStorage.getItem('heldOrders') || '[]');
    let offlineOrders = JSON.parse(localStorage.getItem('offlineOrders') || '[]');

    // Unit related
    let availableUnits = [];
    let selectedUnit = null;
    let currentMeasurementType = 'quantity';

    // Load initial products
    loadInitialProducts();

    // ============================================
    // Load Initial Products (10 items)
    // ============================================
    async function loadInitialProducts() {
        showLoading();
        try {
            const response = await axios.get('{{ route("pos.search") }}', { params: { q: '' } });
            allProducts = response.data || [];
            updateCategories();
            renderProducts();
            hideLoading();
        } catch (error) {
            hideLoading();
            showToast('Failed to load products', 'error');
        }
    }

    function updateCategories() {
        categories.clear();
        categories.add('all');
        allProducts.forEach(product => {
            const cat = product.category || 'Uncategorized';
            categories.add(cat);
        });
        renderCategoryTabs();
    }

    function renderCategoryTabs() {
        const container = document.getElementById('categoryTabs');
        if (!container) return;

        container.innerHTML = '';
        Array.from(categories).forEach(cat => {
            const tab = document.createElement('span');
            tab.className = `category-tab ${activeCategory === cat ? 'active' : ''}`;
            tab.setAttribute('data-cat', cat);
            tab.innerHTML = cat === 'all' ? '<i class="bi bi-grid"></i> All' :
                           `<i class="bi bi-tag"></i> ${cat.charAt(0).toUpperCase() + cat.slice(1)}`;
            tab.onclick = () => {
                activeCategory = cat;
                renderCategoryTabs();
                renderProducts();
            };
            container.appendChild(tab);
        });
    }

    function renderProducts() {
        const filtered = activeCategory === 'all'
            ? allProducts
            : allProducts.filter(p => (p.category || 'Uncategorized') === activeCategory);

        if (currentViewMode === 'grid') {
            renderGridView(filtered);
        } else {
            renderListView(filtered);
        }
    }

    function renderGridView(products) {
        gridViewContainer.style.display = 'block';
        listViewContainer.style.display = 'none';

        if (products.length === 0) {
            gridViewBody.innerHTML = '';
            emptyGridRow.style.display = 'block';
            return;
        }

        emptyGridRow.style.display = 'none';
        gridViewBody.innerHTML = '';

        products.forEach(product => {
            const cartItem = cart.find(i => i.product_id === product.id);
            const isInCart = !!cartItem;
            const stock = product.stock || 0;
            const isOutOfStock = stock <= 0;
            const price = product.sale_price || product.price;

            const card = document.createElement('div');
            card.className = `pos-grid-card ${isInCart ? 'in-cart' : ''} ${isOutOfStock ? 'out-of-stock' : ''}`;
            card.setAttribute('data-product-id', product.id);

            card.innerHTML = `
                ${isInCart ? `<div class="cart-badge">${cartItem.qty} in cart</div>` : ''}
                <div class="product-image">
                    ${product.thumbnail ?
                        `<img src="${product.thumbnail}" alt="${product.title}">` :
                        `<div class="no-image"><i class="bi bi-box-seam"></i></div>`
                    }
                </div>
                <div class="product-info">
                    <div class="product-title" title="${product.title}">${product.title}</div>
                    <div class="product-price">${formatCurrency(price)}</div>
                    <div class="product-stock ${stock <= 5 ? 'low-stock' : ''}">
                        ${stock > 0 ? `${stock} left` : 'Out of stock'}
                    </div>
                    <div class="product-unit">
                        <i class="bi bi-scale"></i> ${product.primary_unit || 'Unit'}
                    </div>
                </div>
                ${!isOutOfStock ? `<button class="add-to-cart-btn" data-product='${JSON.stringify(product).replace(/'/g, "&apos;")}'>
                    <i class="bi bi-cart-plus"></i> Add
                </button>` : '<button class="add-to-cart-btn disabled" disabled>Out of Stock</button>'}
            `;

            const addBtn = card.querySelector('.add-to-cart-btn');
            if (addBtn && !isOutOfStock) {
                addBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    openQuantityModal(addBtn);
                });
            }

            gridViewBody.appendChild(card);
        });
    }

    function renderListView(products) {
        gridViewContainer.style.display = 'none';
        listViewContainer.style.display = 'block';

        if (products.length === 0) {
            resultsBody.innerHTML = emptySearchRow.outerHTML;
            return;
        }

        resultsBody.innerHTML = '';

        products.forEach(product => {
            const cartItem = cart.find(i => i.product_id === product.id);
            const isInCart = !!cartItem;
            const stock = product.stock || 0;
            const price = product.sale_price || product.price;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <div class="d-flex align-items-center">
                        ${product.thumbnail ?
                            `<img src="${product.thumbnail}" width="40" class="rounded me-2" alt="">` :
                            `<div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                <i class="bi bi-box-seam"></i>
                            </div>`
                        }
                        <div>
                            <strong>${product.title}</strong>
                            <small class="d-block text-muted">SKU: ${product.sku || 'N/A'}</small>
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <span class="badge ${stock > 10 ? 'bg-success' : stock > 0 ? 'bg-warning' : 'bg-danger'}">
                        ${stock > 0 ? stock : 'Out'}
                    </span>
                </td>
                <td class="text-center">
                    ${!isInCart ?
                        `<button class="btn btn-sm btn-primary add-product-btn" data-product='${JSON.stringify(product).replace(/'/g, "&apos;")}'>
                            <i class="bi bi-cart-plus"></i> Add
                        </button>` :
                        `<span class="badge bg-success">Added (${cartItem.qty})</span>
                         <button class="btn btn-sm btn-outline-primary ms-1 edit-cart-btn" data-product='${JSON.stringify(product).replace(/'/g, "&apos;")}'>
                            <i class="bi bi-pencil"></i>
                         </button>`
                    }
                </td>
                <td class="text-end fw-bold">${formatCurrency(price)}</td>
                <td class="text-center">
                    <span class="badge bg-secondary">${product.primary_unit || 'Unit'}</span>
                </td>
            `;

            const addBtn = row.querySelector('.add-product-btn');
            if (addBtn) addBtn.addEventListener('click', (e) => openQuantityModal(addBtn));

            const editBtn = row.querySelector('.edit-cart-btn');
            if (editBtn) editBtn.addEventListener('click', (e) => openQuantityModal(editBtn));

            resultsBody.appendChild(row);
        });
    }

    // ============================================
    // Search Functionality
    // ============================================
    input.addEventListener('input', debounce(async (e) => {
        const query = e.target.value.trim();
        if (query.length >= 2) {
            await searchProducts(query);
        } else if (query.length === 0) {
            await loadInitialProducts();
        }
    }, 400));

    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            const query = input.value.trim();
            if (query) searchProducts(query);
        }
    });

    async function searchProducts(query) {
        showLoading();
        try {
            const response = await axios.get('{{ route("pos.search") }}', { params: { q: query } });
            allProducts = response.data || [];
            updateCategories();
            renderProducts();
            hideLoading();
            if (allProducts.length === 0) {
                showToast('No products found', 'info');
            }
        } catch (error) {
            hideLoading();
            showToast('Search failed', 'error');
        }
    }

    // ============================================
    // Quantity Modal
    // ============================================
    function openQuantityModal(button) {
        const productData = button.dataset.product;
        if (!productData) return;

        currentProduct = JSON.parse(productData);
        document.getElementById('modalProductName').innerHTML = `<i class="bi bi-box"></i> ${currentProduct.title}`;
        document.getElementById('modalProductPrice').innerHTML = formatCurrency(currentProduct.sale_price || currentProduct.price);
        document.getElementById('modalProductStock').innerHTML = `${currentProduct.stock || 0} units`;
        document.getElementById('modalProductUnit').innerHTML = currentProduct.primary_unit || 'Unit';

        const cartItem = cart.find(i => i.product_id === currentProduct.id);
        const removeBtn = document.getElementById('removeFromCartBtn');
        removeBtn.style.display = cartItem ? 'inline-block' : 'none';

        // Reset to quantity mode
        document.getElementById('measureQuantity').checked = true;
        switchToQuantityMode();

        // Load units if available
        if (currentProduct.units && currentProduct.units.length > 0) {
            document.getElementById('measureUnit').disabled = false;
            loadProductUnits();
        } else {
            document.getElementById('measureUnit').disabled = true;
        }

        quantityModal = new bootstrap.Modal(document.getElementById('quantityModal'));
        quantityModal.show();

        setTimeout(() => {
            document.getElementById('modalQty').focus();
            document.getElementById('modalQty').select();
        }, 100);
    }

    function switchToQuantityMode() {
        currentMeasurementType = 'quantity';
        document.getElementById('quantityInputSection').style.display = 'block';
        document.getElementById('unitInputSection').style.display = 'none';
        document.getElementById('unitSelection').style.display = 'none';

        const price = currentProduct.sale_price || currentProduct.price;
        const qty = parseInt(document.getElementById('modalQty').value) || 1;
        updateModalTotal(price * qty);

        setupQuickButtons([1, 2, 3, 5, 10, 20, 50]);
    }

    function switchToUnitMode() {
        currentMeasurementType = 'unit';
        document.getElementById('quantityInputSection').style.display = 'none';
        document.getElementById('unitInputSection').style.display = 'block';
        document.getElementById('unitSelection').style.display = 'block';

        setupQuickButtons([0.25, 0.5, 0.75, 1, 2, 5]);
    }

    async function loadProductUnits() {
        try {
            const response = await axios.get(`/pos/product-units/${currentProduct.id}`);
            availableUnits = response.data.units || [];
            const unitSelect = document.getElementById('unitSelect');
            unitSelect.innerHTML = '<option value="">-- Select unit --</option>';

            availableUnits.forEach(unit => {
                const option = document.createElement('option');
                option.value = unit.id;
                option.textContent = `${unit.name} (${unit.short_name})`;
                if (unit.is_default) option.selected = true;
                unitSelect.appendChild(option);
            });

            selectedUnit = availableUnits.find(u => u.is_default) || availableUnits[0];
            if (selectedUnit) {
                const price = currentProduct.sale_price || currentProduct.price;
                const amount = parseFloat(document.getElementById('modalUnit').value) || 1;
                updateModalTotal(price * amount);
            }
        } catch (error) {
            console.error('Failed to load units:', error);
        }
    }

    function setupQuickButtons(values) {
        const container = document.getElementById('quickButtonsContainer');
        container.innerHTML = '';

        values.forEach(val => {
            const col = document.createElement('div');
            col.className = 'col';
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn btn-outline-secondary w-100';
            btn.textContent = val % 1 === 0 ? val : val.toString();
            btn.onclick = () => {
                if (currentMeasurementType === 'quantity') {
                    document.getElementById('modalQty').value = val;
                    const price = currentProduct.sale_price || currentProduct.price;
                    updateModalTotal(price * val);
                } else {
                    document.getElementById('modalUnit').value = val;
                    const price = currentProduct.sale_price || currentProduct.price;
                    updateModalTotal(price * val);
                }
            };
            col.appendChild(btn);
            container.appendChild(col);
        });
    }

    function updateModalTotal(total) {
        document.getElementById('modalTotalDisplay').innerHTML = formatCurrency(total);
    }

    // Event listeners for modal
    document.getElementById('measureQuantity').addEventListener('change', switchToQuantityMode);
    document.getElementById('measureUnit').addEventListener('change', switchToUnitMode);
    document.getElementById('unitSelect').addEventListener('change', (e) => {
        selectedUnit = availableUnits.find(u => u.id == e.target.value);
    });

    document.getElementById('decreaseQty').addEventListener('click', () => {
        const input = document.getElementById('modalQty');
        let val = parseInt(input.value) || 1;
        if (val > 1) input.value = val - 1;
        const price = currentProduct.sale_price || currentProduct.price;
        updateModalTotal(price * parseInt(input.value));
    });

    document.getElementById('increaseQty').addEventListener('click', () => {
        const input = document.getElementById('modalQty');
        let val = parseInt(input.value) || 1;
        input.value = val + 1;
        const price = currentProduct.sale_price || currentProduct.price;
        updateModalTotal(price * parseInt(input.value));
    });

    document.getElementById('decreaseUnit').addEventListener('click', () => {
        const input = document.getElementById('modalUnit');
        let val = parseFloat(input.value) || 1;
        if (val > 0.001) input.value = (val - 0.001).toFixed(3);
        const price = currentProduct.sale_price || currentProduct.price;
        updateModalTotal(price * parseFloat(input.value));
    });

    document.getElementById('increaseUnit').addEventListener('click', () => {
        const input = document.getElementById('modalUnit');
        let val = parseFloat(input.value) || 1;
        input.value = (val + 0.001).toFixed(3);
        const price = currentProduct.sale_price || currentProduct.price;
        updateModalTotal(price * parseFloat(input.value));
    });

    document.getElementById('modalQty').addEventListener('input', () => {
        const price = currentProduct.sale_price || currentProduct.price;
        updateModalTotal(price * (parseInt(document.getElementById('modalQty').value) || 1));
    });

    document.getElementById('modalUnit').addEventListener('input', () => {
        const price = currentProduct.sale_price || currentProduct.price;
        updateModalTotal(price * (parseFloat(document.getElementById('modalUnit').value) || 1));
    });

    document.getElementById('confirmAddBtn').addEventListener('click', () => {
        addToCart();
        quantityModal.hide();
    });

    document.getElementById('removeFromCartBtn').addEventListener('click', () => {
        removeFromCart(currentProduct.id);
        quantityModal.hide();
    });

    // ============================================
    // Cart Functions
    // ============================================
    function addToCart() {
        if (!currentProduct) return;

        let quantity = 0;
        let unitId = currentProduct.primary_unit_id || 1;
        let unitName = currentProduct.primary_unit || 'Unit';
        let unitShortName = unitName;
        let isUnitMode = currentMeasurementType === 'unit';

        if (isUnitMode) {
            quantity = parseFloat(document.getElementById('modalUnit').value) || 0.001;
            if (selectedUnit) {
                unitId = selectedUnit.id;
                unitName = selectedUnit.name;
                unitShortName = selectedUnit.short_name;
            }
        } else {
            quantity = parseInt(document.getElementById('modalQty').value) || 1;
        }

        if (quantity <= 0) {
            showToast('Invalid quantity', 'warning');
            return;
        }

        const stock = currentProduct.stock || 0;
        if (quantity > stock) {
            showToast(`Only ${stock} units available`, 'warning');
            return;
        }

        const price = currentProduct.sale_price || currentProduct.price;
        const totalPrice = price * quantity;

        const existingItem = cart.find(i => i.product_id === currentProduct.id);

        if (existingItem) {
            existingItem.qty = quantity;
            existingItem.total = totalPrice;
            existingItem.is_unit_mode = isUnitMode;
            existingItem.unit_id = unitId;
            existingItem.unit_name = unitName;
            existingItem.unit_short_name = unitShortName;
            showNotification('updated', currentProduct.title, quantity, unitShortName);
        } else {
            cart.push({
                product_id: currentProduct.id,
                title: currentProduct.title,
                sku: currentProduct.sku,
                thumbnail: currentProduct.thumbnail,
                price: price,
                qty: quantity,
                total: totalPrice,
                unit_id: unitId,
                unit_name: unitName,
                unit_short_name: unitShortName,
                is_unit_mode: isUnitMode,
                discount_type: null,
                discount_value: 0,
                discounted_price: price
            });
            showNotification('added', currentProduct.title, quantity, unitShortName);
        }

        updateCartDisplay();
        renderProducts();
        playAddSound();
    }

    function removeFromCart(productId) {
        const item = cart.find(i => i.product_id === productId);
        if (item) {
            cart = cart.filter(i => i.product_id !== productId);
            updateCartDisplay();
            renderProducts();
            showNotification('removed', item.title);
            playRemoveSound();
        }
    }

    function updateCartDisplay() {
        if (cart.length === 0) {
            emptyCartRow.style.display = '';
            cartBody.innerHTML = emptyCartRow.outerHTML;
            cartCountEl.textContent = '0';
            subtotalEl.textContent = formatCurrency(0);
            taxAmountEl.textContent = formatCurrency(0);
            grandTotalEl.textContent = formatCurrency(0);
            return;
        }

        emptyCartRow.style.display = 'none';
        cartBody.innerHTML = '';

        let subtotal = 0;

        cart.forEach((item, index) => {
            const discountedPrice = item.discounted_price || item.price;
            const total = discountedPrice * item.qty;
            subtotal += total;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <div class="d-flex align-items-center">
                        ${item.thumbnail ?
                            `<img src="${item.thumbnail}" width="35" class="rounded me-2" alt="">` :
                            `<div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width:35px;height:35px;">
                                <i class="bi bi-box"></i>
                            </div>`
                        }
                        <div>
                            <strong>${item.title}</strong>
                            ${item.discount_value > 0 ?
                                `<small class="text-danger d-block">-${item.discount_value}% OFF</small>` : ''}
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-secondary qty-decr" data-index="${index}">
                        <i class="bi bi-dash"></i>
                    </button>
                    <span class="mx-2">${item.qty}</span>
                    <button class="btn btn-sm btn-outline-secondary qty-incr" data-index="${index}">
                        <i class="bi bi-plus"></i>
                    </button>
                    <div><small class="text-muted">${item.unit_short_name || item.unit_name || 'unit'}</small></div>
                </td>
                <td class="text-end">${formatCurrency(discountedPrice)}</td>
                <td class="text-end fw-bold">${formatCurrency(total)}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-warning item-discount" data-index="${index}" title="Apply discount">
                        <i class="bi bi-percent"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger ms-1 remove-item" data-index="${index}" title="Remove">
                        <i class="bi bi-x"></i>
                    </button>
                </td>
            `;

            cartBody.appendChild(row);

            row.querySelector('.qty-decr').addEventListener('click', () => updateItemQuantity(index, -1));
            row.querySelector('.qty-incr').addEventListener('click', () => updateItemQuantity(index, 1));
            row.querySelector('.item-discount').addEventListener('click', () => openItemDiscountModal(index));
            row.querySelector('.remove-item').addEventListener('click', () => removeFromCart(item.product_id));
        });

        const taxRate = {{ config('pos.tax_rate', 0) }};
        const taxAmount = (subtotal * taxRate) / 100;

        let discountAmount = 0;
        if (orderDiscountValue > 0) {
            discountAmount = orderDiscountType === 'percent'
                ? (subtotal * orderDiscountValue) / 100
                : orderDiscountValue;
        }

        const grandTotal = subtotal + taxAmount - discountAmount;

        subtotalEl.textContent = formatCurrency(subtotal);
        taxAmountEl.textContent = formatCurrency(taxAmount);
        discountAmountEl.textContent = `-${formatCurrency(discountAmount)}`;
        grandTotalEl.textContent = formatCurrency(grandTotal);
        cartCountEl.textContent = cart.length;

        document.getElementById('discountRow').style.display = discountAmount > 0 ? 'flex' : 'none';
    }

    function updateItemQuantity(index, delta) {
        const item = cart[index];
        if (!item) return;

        let newQty = item.qty + delta;
        if (newQty < 1) {
            removeFromCart(item.product_id);
            return;
        }

        item.qty = newQty;
        item.total = (item.discounted_price || item.price) * newQty;
        updateCartDisplay();
        renderProducts();
    }

    function openItemDiscountModal(index) {
        currentItemIndex = index;
        const item = cart[index];
        document.getElementById('discountItemName').textContent = item.title;
        document.getElementById('itemDiscountValue').value = item.discount_value || 0;
        document.getElementById('itemDiscountType').value = item.discount_type || 'percent';

        const modal = new bootstrap.Modal(document.getElementById('itemDiscountModal'));
        modal.show();
    }

    document.getElementById('applyItemDiscountBtn').addEventListener('click', () => {
        if (currentItemIndex === null) return;

        const item = cart[currentItemIndex];
        const discountValue = parseFloat(document.getElementById('itemDiscountValue').value) || 0;
        const discountType = document.getElementById('itemDiscountType').value;

        if (discountValue > 0) {
            if (discountType === 'percent' && discountValue > 100) {
                showToast('Percentage cannot exceed 100%', 'warning');
                return;
            }

            let discountedPrice = item.price;
            if (discountType === 'percent') {
                discountedPrice = item.price * (1 - discountValue / 100);
            } else {
                discountedPrice = item.price - (discountValue / item.qty);
                if (discountedPrice < 0) discountedPrice = 0;
            }

            item.discount_type = discountType;
            item.discount_value = discountValue;
            item.discounted_price = discountedPrice;
            item.total = discountedPrice * item.qty;
        } else {
            delete item.discount_type;
            delete item.discount_value;
            item.discounted_price = item.price;
            item.total = item.price * item.qty;
        }

        updateCartDisplay();
        bootstrap.Modal.getInstance(document.getElementById('itemDiscountModal')).hide();
        showToast('Discount applied', 'success');
    });

    // ============================================
    // Order Functions
    // ============================================
    function clearCart() {
        if (cart.length === 0) return;

        Swal.fire({
            title: 'Clear Cart?',
            text: 'This will remove all items from your cart.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, clear',
            cancelButtonText: 'Cancel'
        }).then(result => {
            if (result.isConfirmed) {
                cart = [];
                orderDiscountValue = 0;
                discountValue.value = '0';
                updateCartDisplay();
                renderProducts();
                showToast('Cart cleared', 'success');
            }
        });
    }

    function holdOrder() {
        if (cart.length === 0) {
            showToast('Cart is empty', 'warning');
            return;
        }

        heldOrders.push({
            id: Date.now(),
            cart: JSON.parse(JSON.stringify(cart)),
            customer: customerSelect.value,
            discount: { type: orderDiscountType, value: orderDiscountValue },
            timestamp: new Date().toLocaleString(),
            date: Date.now()
        });

        localStorage.setItem('heldOrders', JSON.stringify(heldOrders));

        cart = [];
        orderDiscountValue = 0;
        discountValue.value = '0';
        updateCartDisplay();
        renderProducts();
        showToast('Order held successfully', 'success');
    }

    function loadHeldOrders() {
        if (heldOrders.length === 0) {
            Swal.fire('No Held Orders', 'No orders are currently on hold.', 'info');
            return;
        }

        const listContainer = document.getElementById('heldOrdersList');
        const noOrdersDiv = document.getElementById('noHeldOrders');

        if (heldOrders.length === 0) {
            listContainer.innerHTML = '';
            noOrdersDiv.style.display = 'block';
        } else {
            noOrdersDiv.style.display = 'none';
            listContainer.innerHTML = `
                <div class="list-group">
                    ${heldOrders.sort((a,b) => b.date - a.date).map(order => `
                        <div class="list-group-item list-group-item-action">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-1">${order.timestamp}</h6>
                                    <p class="mb-1 text-muted">${order.cart.length} items</p>
                                </div>
                                <div>
                                    <button class="btn btn-sm btn-primary load-order" data-id="${order.id}">Load</button>
                                    <button class="btn btn-sm btn-danger delete-order" data-id="${order.id}">Delete</button>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;

            document.querySelectorAll('.load-order').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const orderId = parseInt(btn.dataset.id);
                    const order = heldOrders.find(o => o.id === orderId);
                    if (order) {
                        cart = JSON.parse(JSON.stringify(order.cart));
                        if (order.customer) customerSelect.value = order.customer;
                        if (order.discount) {
                            orderDiscountType = order.discount.type;
                            orderDiscountValue = order.discount.value;
                            discountType.value = order.discount.type;
                            discountValue.value = order.discount.value;
                        }
                        updateCartDisplay();
                        renderProducts();
                        bootstrap.Modal.getInstance(document.getElementById('loadOrderModal')).hide();
                        showToast('Order loaded', 'success');
                    }
                });
            });

            document.querySelectorAll('.delete-order').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const orderId = parseInt(btn.dataset.id);
                    heldOrders = heldOrders.filter(o => o.id !== orderId);
                    localStorage.setItem('heldOrders', JSON.stringify(heldOrders));
                    loadHeldOrders();
                    showToast('Order deleted', 'success');
                });
            });
        }

        const modal = new bootstrap.Modal(document.getElementById('loadOrderModal'));
        modal.show();
    }

    function applyOrderDiscount() {
        orderDiscountValue = parseFloat(discountValue.value) || 0;
        orderDiscountType = discountType.value;

        if (orderDiscountType === 'percent' && orderDiscountValue > 100) {
            showToast('Percentage cannot exceed 100%', 'warning');
            orderDiscountValue = 100;
            discountValue.value = 100;
        }

        updateCartDisplay();
        showToast('Discount applied', 'success');
    }

    async function completeOrder() {
        if (cart.length === 0) {
            showToast('Cart is empty', 'warning');
            return;
        }

        if (isProcessingOrder) {
            showToast('Order is being processed', 'info');
            return;
        }

        const paymentMethod = document.querySelector('input[name="payment"]:checked').value;
        const customerId = customerSelect.value || null;
        const isOffline = offlineModeToggle.checked || !navigator.onLine;

        if (isOffline) {
            saveOrderOffline(paymentMethod, customerId);
            return;
        }

        isProcessingOrder = true;
        completeOrderBtn.disabled = true;
        completeOrderBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing...';

        const items = cart.map(item => ({
            product_id: item.product_id,
            qty: item.qty,
            unit_id: item.unit_id || 1,
            sale_price: item.discounted_price || item.price,
            discount_type: item.discount_type || null,
            discount_value: item.discount_value || 0,
            is_unit_mode: item.is_unit_mode || false,
            unit_name: item.unit_name || null
        }));

        const discount = {
            type: orderDiscountType,
            value: orderDiscountValue
        };

        try {
            const response = await axios.post('{{ route("pos.order.save") }}', {
                items: items,
                payment_method: paymentMethod,
                customer_id: customerId,
                discount_type: discount.type,
                discount_value: discount.value,
                _token: '{{ csrf_token() }}'
            });

            if (response.data.success) {
                playSuccessSound();
                showReceiptDialog(response.data.order_id, response.data.total);
                resetAfterOrder();
            } else {
                throw new Error(response.data.message || 'Order failed');
            }
        } catch (error) {
            let message = 'Failed to process order';
            if (error.response?.data?.message) message = error.response.data.message;
            Swal.fire('Error', message, 'error');
        } finally {
            isProcessingOrder = false;
            completeOrderBtn.disabled = false;
            completeOrderBtn.innerHTML = '<i class="bi bi-printer me-2"></i> Complete & Print';
        }
    }

    function saveOrderOffline(paymentMethod, customerId) {
        offlineOrders.push({
            id: 'OFF-' + Date.now(),
            items: cart.map(item => ({...item})),
            payment_method: paymentMethod,
            customer_id: customerId,
            discount: { type: orderDiscountType, value: orderDiscountValue },
            timestamp: new Date().toLocaleString(),
            date: Date.now()
        });

        localStorage.setItem('offlineOrders', JSON.stringify(offlineOrders));

        Swal.fire({
            title: 'Order Saved Offline',
            html: '<i class="bi bi-wifi-off text-warning display-4 d-block mb-3"></i>' +
                  '<p>Your order has been saved locally and will sync when you\'re back online.</p>',
            icon: 'warning',
            confirmButtonText: 'OK'
        });

        resetAfterOrder();
    }

    function resetAfterOrder() {
        cart = [];
        orderDiscountValue = 0;
        discountValue.value = '0';
        updateCartDisplay();
        renderProducts();
        input.focus();
    }

    function showReceiptDialog(orderId, total) {
        Swal.fire({
            title: 'Order Completed!',
            html: `
                <div class="text-center">
                    <i class="bi bi-check-circle-fill text-success display-3 mb-3"></i>
                    <h4>Order #${orderId}</h4>
                    <p class="fs-3 text-primary">${formatCurrency(total)}</p>
                    <button class="btn btn-success mt-2" id="printReceiptBtn">
                        <i class="bi bi-printer me-2"></i> Print Receipt
                    </button>
                </div>
            `,
            showConfirmButton: false,
            showCloseButton: true,
            didOpen: () => {
                document.getElementById('printReceiptBtn').addEventListener('click', () => {
                    window.open(`/pos/receipt/${orderId}`, '_blank');
                });
            }
        });
    }

    // ============================================
    // Customer Functions
    // ============================================
    quickCustomerBtn.addEventListener('click', () => {
        const modal = new bootstrap.Modal(document.getElementById('quickCustomerModal'));
        modal.show();
    });

    document.getElementById('saveQuickCustomerBtn').addEventListener('click', async () => {
        const firstName = document.getElementById('firstName').value.trim();
        const lastName = document.getElementById('lastName').value.trim();
        const phoneNumber = document.getElementById('phoneNumber').value.trim();
        const email = document.getElementById('email').value.trim();

        if (!firstName || !lastName) {
            showToast('First name and last name are required', 'warning');
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
                const option = document.createElement('option');
                option.value = response.data.customer.id;
                option.textContent = `${firstName} ${lastName}${phoneNumber ? ` - ${phoneNumber}` : ''}`;
                customerSelect.appendChild(option);
                customerSelect.value = response.data.customer.id;

                bootstrap.Modal.getInstance(document.getElementById('quickCustomerModal')).hide();
                document.getElementById('quickCustomerForm').reset();
                showToast('Customer added successfully', 'success');
            }
        } catch (error) {
            showToast('Failed to add customer', 'error');
        }
    });

    // ============================================
    // Helper Functions
    // ============================================
    function formatCurrency(amount) {
        return '₦' + new Intl.NumberFormat('en-NG', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        }).format(amount);
    }

    function showToast(message, type = 'success', duration = 3000) {
        const container = document.querySelector('.toast-container');
        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'warning'} border-0`;
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'assertive');
        toast.setAttribute('aria-atomic', 'true');

        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-${type === 'success' ? 'check-circle' : type === 'error' ? 'x-circle' : 'info-circle'} me-2"></i>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        container.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast, { delay: duration });
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => toast.remove());
    }

    function showNotification(action, productName, quantity = null, unit = null) {
        const messages = {
            added: `✅ Added ${quantity} × ${productName}${unit ? ` (${unit})` : ''} to cart`,
            updated: `📝 Updated ${productName} to ${quantity}${unit ? ` ${unit}` : ''}`,
            removed: `🗑️ Removed ${productName} from cart`
        };

        showToast(messages[action] || action, 'success', 2000);
    }

    function playAddSound() {
        try {
            const audio = new Audio();
            // Use Web Audio API for beep
            const context = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = context.createOscillator();
            const gain = context.createGain();
            oscillator.connect(gain);
            gain.connect(context.destination);
            oscillator.frequency.value = 880;
            oscillator.type = 'sine';
            gain.gain.setValueAtTime(0.1, context.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.00001, context.currentTime + 0.1);
            oscillator.start(context.currentTime);
            oscillator.stop(context.currentTime + 0.1);
        } catch(e) {}
    }

    function playRemoveSound() {
        try {
            const context = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator = context.createOscillator();
            const gain = context.createGain();
            oscillator.connect(gain);
            gain.connect(context.destination);
            oscillator.frequency.value = 440;
            oscillator.type = 'sine';
            gain.gain.setValueAtTime(0.1, context.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.00001, context.currentTime + 0.1);
            oscillator.start(context.currentTime);
            oscillator.stop(context.currentTime + 0.1);
        } catch(e) {}
    }

    function playSuccessSound() {
        try {
            const context = new (window.AudioContext || window.webkitAudioContext)();
            const frequencies = [523.25, 659.25, 783.99];
            frequencies.forEach((freq, i) => {
                const oscillator = context.createOscillator();
                const gain = context.createGain();
                oscillator.connect(gain);
                gain.connect(context.destination);
                oscillator.frequency.value = freq;
                oscillator.type = 'sine';
                gain.gain.setValueAtTime(0.1, context.currentTime + i * 0.1);
                gain.gain.exponentialRampToValueAtTime(0.00001, context.currentTime + i * 0.1 + 0.1);
                oscillator.start(context.currentTime + i * 0.1);
                oscillator.stop(context.currentTime + i * 0.1 + 0.1);
            });
        } catch(e) {}
    }

    function showLoading() {
        document.getElementById('searchLoading').classList.remove('d-none');
    }

    function hideLoading() {
        document.getElementById('searchLoading').classList.add('d-none');
    }

    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // ============================================
    // View Mode Toggle
    // ============================================
    document.getElementById('viewModeGrid').addEventListener('change', function() {
        if (this.checked) {
            currentViewMode = 'grid';
            localStorage.setItem('posViewMode', 'grid');
            renderProducts();
        }
    });

    document.getElementById('viewModeList').addEventListener('change', function() {
        if (this.checked) {
            currentViewMode = 'list';
            localStorage.setItem('posViewMode', 'list');
            renderProducts();
        }
    });

    // ============================================
    // Event Listeners
    // ============================================
    clearCartBtn.addEventListener('click', clearCart);
    holdOrderBtn.addEventListener('click', holdOrder);
    loadHeldBtn.addEventListener('click', loadHeldOrders);
    applyDiscountBtn.addEventListener('click', applyOrderDiscount);
    completeOrderBtn.addEventListener('click', completeOrder);

    // Keyboard shortcuts
    document.addEventListener('keydown', (e) => {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;

        switch(e.key) {
            case 'F1': e.preventDefault(); input.focus(); input.select(); break;
            case 'F2': e.preventDefault(); clearCart(); break;
            case 'F3': e.preventDefault(); completeOrder(); break;
            case 'F4': e.preventDefault(); holdOrder(); break;
            case 'F5': e.preventDefault(); loadHeldOrders(); break;
        }
    });

    // Online/Offline handling
    window.addEventListener('online', () => {
        connectionStatus.className = 'badge bg-success';
        connectionStatus.innerHTML = '<i class="bi bi-wifi"></i> Online';
        offlineModeToggle.checked = false;
        showToast('Back online!', 'success');
        syncOfflineOrders();
    });

    window.addEventListener('offline', () => {
        connectionStatus.className = 'badge bg-danger';
        connectionStatus.innerHTML = '<i class="bi bi-wifi-off"></i> Offline';
        offlineModeToggle.checked = true;
        showToast('You are offline. Orders will be saved locally.', 'warning');
    });

    async function syncOfflineOrders() {
        if (offlineOrders.length === 0) return;

        let synced = 0;
        for (const order of offlineOrders) {
            try {
                await axios.post('{{ route("pos.order.save") }}', {
                    items: order.items.map(i => ({
                        product_id: i.product_id,
                        qty: i.qty,
                        unit_id: i.unit_id || 1,
                        sale_price: i.discounted_price || i.price,
                        discount_type: i.discount_type || null,
                        discount_value: i.discount_value || 0,
                        is_unit_mode: i.is_unit_mode || false
                    })),
                    payment_method: order.payment_method,
                    customer_id: order.customer_id,
                    discount_type: order.discount?.type || 'percent',
                    discount_value: order.discount?.value || 0,
                    _token: '{{ csrf_token() }}'
                });
                synced++;
            } catch(e) {}
        }

        if (synced > 0) {
            offlineOrders = [];
            localStorage.setItem('offlineOrders', JSON.stringify(offlineOrders));
            showToast(`${synced} offline order(s) synced`, 'success');
        }
    }

    // Initialize
    updateCartDisplay();

});
</script>

<style>
/* ===================================================
   MAIN POS STYLES
=================================================== */

/* Category Tabs */
.category-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: #f8f9fa;
    border-radius: 30px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 14px;
    font-weight: 500;
    color: #6c757d;
    border: 1px solid #dee2e6;
}

.category-tab:hover {
    background: #e9ecef;
    transform: translateY(-2px);
}

.category-tab.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
    box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
}

/* Grid View Container */
.pos-grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 20px;
    padding: 10px;
    max-height: 500px;
    overflow-y: auto;
}

/* Grid Card */
.pos-grid-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    position: relative;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    border: 1px solid #eef2f6;
}

.pos-grid-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
}

.pos-grid-card.in-cart {
    border: 2px solid #28a745;
    background: #f0fff4;
}

.pos-grid-card.out-of-stock {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Cart Badge */
.pos-grid-card .cart-badge {
    position: absolute;
    top: 10px;
    left: 10px;
    background: #28a745;
    color: white;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: bold;
    z-index: 1;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

/* Product Image */
.pos-grid-card .product-image {
    width: 100%;
    height: 160px;
    background: #f8f9fa;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
}

.pos-grid-card .product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.pos-grid-card:hover .product-image img {
    transform: scale(1.05);
}

.pos-grid-card .product-image .no-image {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    color: #adb5bd;
    background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
}

/* Product Info */
.pos-grid-card .product-info {
    padding: 12px;
}

.pos-grid-card .product-title {
    font-size: 14px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 6px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.pos-grid-card .product-price {
    font-size: 18px;
    font-weight: bold;
    color: #28a745;
    margin-bottom: 4px;
}

.pos-grid-card .product-stock {
    font-size: 11px;
    color: #6c757d;
    margin-bottom: 4px;
}

.pos-grid-card .product-stock.low-stock {
    color: #ffc107;
    font-weight: 600;
}

.pos-grid-card .product-unit {
    font-size: 11px;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Add to Cart Button */
.pos-grid-card .add-to-cart-btn {
    width: calc(100% - 24px);
    margin: 0 12px 12px 12px;
    padding: 8px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    border-radius: 25px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.pos-grid-card .add-to-cart-btn:hover {
    transform: scale(1.02);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.pos-grid-card .add-to-cart-btn.disabled {
    background: #6c757d;
    cursor: not-allowed;
    opacity: 0.6;
}

/* Cart Table */
.cart-items-container {
    border-radius: 12px;
    background: #f8f9fa;
    padding: 5px;
}

/* Toast Animations */
.toast {
    animation: slideInRight 0.3s ease;
}

@keyframes slideInRight {
    from {
        transform: translateX(100%);
        opacity: 0;
    }
    to {
        transform: translateX(0);
        opacity: 1;
    }
}

/* Modal Enhancements */
.modal-content {
    border-radius: 20px;
    border: none;
}

.modal-header {
    border-radius: 20px 20px 0 0;
}

/* Quantity Input */
#modalQty, #modalUnit {
    font-size: 24px;
    font-weight: bold;
}

/* Responsive */
@media (max-width: 768px) {
    .pos-grid-container {
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 12px;
    }

    .pos-grid-card .product-image {
        height: 120px;
    }

    .pos-grid-card .product-title {
        font-size: 12px;
    }

    .pos-grid-card .product-price {
        font-size: 14px;
    }

    .category-tab {
        padding: 6px 12px;
        font-size: 12px;
    }
}

/* Scrollbar Styling */
.products-container::-webkit-scrollbar,
.cart-items-container::-webkit-scrollbar {
    width: 6px;
}

.products-container::-webkit-scrollbar-track,
.cart-items-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.products-container::-webkit-scrollbar-thumb,
.cart-items-container::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 10px;
}

.products-container::-webkit-scrollbar-thumb:hover,
.cart-items-container::-webkit-scrollbar-thumb:hover {
    background: #555;
}

/* Loading Overlay */
#searchLoading {
    background: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(4px);
}

/* Payment Method Labels */
.form-check-label {
    transition: all 0.3s ease;
}

.form-check-input:checked + .form-check-label {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-color: transparent;
}

/* Button Hover Effects */
.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

/* Cart Item Buttons */
.qty-decr, .qty-incr, .item-discount, .remove-item {
    transition: all 0.2s ease;
}

.qty-decr:hover, .qty-incr:hover {
    transform: scale(1.1);
}

.item-discount:hover {
    background: #ffc107 !important;
    color: #000 !important;
}

.remove-item:hover {
    background: #dc3545 !important;
    color: white !important;
}

/* Status Badge Animation */
#connectionStatus {
    transition: all 0.3s ease;
}

#connectionStatus.bg-danger {
    animation: pulse 1.5s infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}
</style>
@endsection
