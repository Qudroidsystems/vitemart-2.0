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
                            <h5 class="card-title mb-4">Search or Scan Products</h5>

                            <!-- Search Bar -->
                            <div class="position-relative mb-3">
                                <input type="text" id="barcodeInput" class="form-control form-control-lg fs-3"
                                       placeholder="Scan barcode or search by name/SKU..." autofocus autocomplete="off"
                                       aria-label="Search or scan products">
                                <i class="bi bi-upc-scan position-absolute top-50 end-0 translate-middle-y me-4 fs-2 text-muted"></i>
                            </div>

                            <!-- Toolbar -->
                            <div class="d-flex align-items-center mb-3 flex-wrap gap-2">
                                <div class="me-auto">
                                    <small class="text-muted">Shortcuts:</small>
                                    <span class="badge bg-secondary ms-1">F1</span> Focus
                                    <span class="badge bg-secondary ms-2">F2</span> Clear Cart
                                    <span class="badge bg-secondary ms-2">F3</span> Complete
                                    <span class="badge bg-secondary ms-2">F4</span> Hold
                                </div>
                                <div class="btn-group me-2" role="group" id="viewToggleGroup">
                                    <input type="radio" class="btn-check" name="viewMode" id="viewModeList" value="list">
                                    <label class="btn btn-outline-secondary btn-sm" for="viewModeList" title="List view">
                                        <i class="bi bi-list-ul me-1"></i> List
                                    </label>
                                    <input type="radio" class="btn-check" name="viewMode" id="viewModeGrid" value="grid" checked>
                                    <label class="btn btn-outline-secondary btn-sm" for="viewModeGrid" title="Grid view">
                                        <i class="bi bi-grid-3x3-gap me-1"></i> Grid
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
                                    <span class="badge rounded-pill category-tab active bg-primary" data-cat="all" style="cursor:pointer;font-size:.85rem;padding:.45em .9em;">All</span>
                                </div>
                            </div>

                            <!-- Products Container -->
                            <div class="flex-grow-1 position-relative" id="productsContainer">
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

                                <!-- LIST VIEW -->
                                <div class="table-responsive" id="listViewContainer" style="display:none;">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-primary sticky-top">
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

                                <!-- GRID VIEW -->
                                <div id="gridViewContainer">
                                    <div id="gridViewBody" class="pos-grid-container"></div>
                                    <div id="emptyGridRow" class="text-center py-5 text-muted" style="display:none;">
                                        <i class="bi bi-box-seam display-1 mb-4 text-light"></i>
                                        <h5>No products found</h5>
                                        <p class="mb-0">Try adjusting your search or category filter</p>
                                    </div>
                                </div>
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
                                <button class="btn btn-sm btn-warning me-2" id="holdOrderBtn" title="Hold order">
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
                                    <a href="javascript:void(0)" class="text-decoration-none fs-6" id="quickCustomerBtn">
                                        <i class="bi bi-person-plus"></i>
                                    </a>
                                </label>
                                <select class="form-select form-select-lg" id="customerSelect">
                                    <option value="">Walk-in Customer</option>
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
                                <h6 class="fw-bold mb-3">Cart Items (<span id="cartCount">0</span>)</h6>
                                <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                                    <table class="table table-sm">
                                        <thead class="table-dark sticky-top">
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
                                    <span id="subtotal">₦0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Discount</span>
                                    <div class="input-group input-group-sm" style="width: 150px;">
                                        <input type="number" id="discountValue" class="form-control text-end" placeholder="0" min="0" step="0.01" value="0">
                                        <select id="discountType" class="form-select" style="width: auto;">
                                            <option value="percent">%</option>
                                            <option value="fixed">₦</option>
                                        </select>
                                        <button class="btn btn-outline-primary" type="button" id="applyDiscountBtn">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mb-2 text-danger" id="discountRow" style="display: none;">
                                    <span>Discount Applied</span>
                                    <span id="discountAmount">-₦0.00</span>
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

                            <!-- Payment Methods -->
                            <div class="mt-4">
                                <label class="form-label fw-bold">Payment Method</label>
                                <div class="btn-group w-100 mb-3" role="group">
                                    <input type="radio" class="btn-check" name="payment" id="cash" value="cash" checked>
                                    <label class="btn btn-outline-success" for="cash">
                                        <i class="bi bi-cash-coin me-1"></i> Cash
                                    </label>
                                    <input type="radio" class="btn-check" name="payment" id="card" value="card">
                                    <label class="btn btn-outline-primary" for="card">
                                        <i class="bi bi-credit-card me-1"></i> Card
                                    </label>
                                    <input type="radio" class="btn-check" name="payment" id="transfer" value="transfer">
                                    <label class="btn btn-outline-info" for="transfer">
                                        <i class="bi bi-bank me-1"></i> Transfer
                                    </label>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-grid gap-2">
                                <button class="btn btn-success btn-lg py-3" id="completeOrder">
                                    <i class="bi bi-printer me-2"></i> Complete Order
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
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title">
                    <i class="bi bi-cart-plus me-2"></i> Add to Cart
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="product-icon bg-light-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px;">
                        <i class="bi bi-box-seam fs-1 text-primary"></i>
                    </div>
                    <h4 class="modal-product-name text-primary fw-bold mb-2" id="modalProductLabel"></h4>
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <span class="badge bg-info" id="modalProductPrice"></span>
                        <span class="badge bg-warning" id="modalProductStock"></span>
                        <span class="badge bg-secondary" id="modalProductUnit"></span>
                    </div>
                </div>

                <div class="card border-0 bg-light mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label fw-bold mb-0">Measurement Type</label>
                            <div class="btn-group btn-group-sm">
                                <input type="radio" class="btn-check" name="measurementType" id="measureQuantity" value="quantity" checked>
                                <label class="btn btn-outline-primary" for="measureQuantity">
                                    <i class="bi bi-123 me-1"></i> Quantity
                                </label>
                                <input type="radio" class="btn-check" name="measurementType" id="measureUnit" value="unit">
                                <label class="btn btn-outline-success" for="measureUnit">
                                    <i class="bi bi-scale me-1"></i> Unit
                                </label>
                            </div>
                        </div>

                        <div id="unitSelection" style="display: none;">
                            <label class="form-label fw-semibold">Select Unit</label>
                            <select class="form-select" id="unitSelect">
                                <option value="">-- Select unit --</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card border-0 bg-light mb-3">
                    <div class="card-body">
                        <div id="quantityInputSection">
                            <label class="form-label fw-bold">Quantity</label>
                            <div class="input-group input-group-lg">
                                <button class="btn btn-outline-secondary" type="button" id="decreaseQty">
                                    <i class="bi bi-dash-lg"></i>
                                </button>
                                <input type="number" id="modalQty" class="form-control text-center border-secondary fs-2 fw-bold" min="1" value="1" step="1">
                                <button class="btn btn-outline-secondary" type="button" id="increaseQty">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>

                        <div id="unitInputSection" style="display: none;">
                            <label class="form-label fw-bold">Amount (in selected unit)</label>
                            <div class="input-group input-group-lg">
                                <button class="btn btn-outline-secondary" type="button" id="decreaseUnit">
                                    <i class="bi bi-dash-lg"></i>
                                </button>
                                <input type="number" id="modalUnit" class="form-control text-center border-secondary fs-2 fw-bold" min="0.001" value="1" step="0.001">
                                <button class="btn btn-outline-secondary" type="button" id="increaseUnit">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mt-3 text-center">
                            <small class="text-muted d-block">Total Price</small>
                            <h4 class="text-primary fw-bold" id="totalPriceDisplay">₦0.00</h4>
                        </div>
                    </div>
                </div>

                <div class="row g-2" id="quickAmounts">
                    <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 quick-amount" data-value="1">1</button></div>
                    <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 quick-amount" data-value="2">2</button></div>
                    <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 quick-amount" data-value="5">5</button></div>
                    <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 quick-amount" data-value="10">10</button></div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light rounded-bottom">
                <button type="button" class="btn btn-danger" id="removeFromCartBtn" style="display: none;">
                    <i class="bi bi-trash me-2"></i> Remove
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmAddBtn">
                    <i class="bi bi-check-circle me-2"></i> Add to Cart
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
                <h5 class="modal-title"><i class="bi bi-percent me-2"></i> Item Discount</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 id="discountItemName" class="text-center mb-3"></h6>
                <div class="input-group">
                    <input type="number" id="itemDiscountValue" class="form-control" placeholder="0" min="0" step="0.01">
                    <select id="itemDiscountType" class="form-select">
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

<!-- Load Order Modal -->
<div class="modal fade" id="loadOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Held Orders</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="heldOrdersList"></div>
                <div id="noHeldOrders" class="text-center py-5 text-muted" style="display:none;">
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
                <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i> Add Customer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="quickCustomerForm">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name *</label>
                            <input type="text" class="form-control" id="firstName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name *</label>
                            <input type="text" class="form-control" id="lastName" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phoneNumber">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Email</label>
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

<!-- Toast Container -->
<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

<script src="{{ asset('theme/layouts/assets/libs/axios/axios.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ============================================
    // STATE MANAGEMENT
    // ============================================
    let cart = [];
    let allProducts = [];
    let currentSearchQuery = '';
    let currentCategoryFilter = 'all';
    let currentViewMode = localStorage.getItem('posViewMode') || 'grid';
    let orderDiscountType = 'percent';
    let orderDiscountValue = 0;
    let currentProduct = null;
    let currentCartIndex = null;
    let productCategories = new Set();

    // DOM Elements
    const input = document.getElementById('barcodeInput');
    const gridViewBody = document.getElementById('gridViewBody');
    const listViewBody = document.getElementById('resultsBody');
    const cartBody = document.getElementById('cartBody');
    const emptyCartRow = document.getElementById('emptyCartRow');
    const subtotalEl = document.getElementById('subtotal');
    const discountAmountEl = document.getElementById('discountAmount');
    const grandTotalEl = document.getElementById('grandTotal');
    const taxAmountEl = document.getElementById('taxAmount');
    const cartCountEl = document.getElementById('cartCount');
    const searchLoading = document.getElementById('searchLoading');
    const categoryTabs = document.getElementById('categoryTabs');
    const categoryFilterBar = document.getElementById('categoryFilterBar');

    // Modal instances
    let quantityModal = new bootstrap.Modal(document.getElementById('quantityModal'));
    let quickCustomerModal = new bootstrap.Modal(document.getElementById('quickCustomerModal'));

    // ============================================
    // INITIAL LOAD - FETCH PRODUCTS
    // ============================================
    async function loadInitialProducts() {
        showSearchLoading();
        try {
            const response = await axios.get('{{ route("pos.initial-products") }}');
            allProducts = response.data.products || [];
            buildCategoryList();
            renderAll();
            hideSearchLoading();
        } catch (error) {
            console.error('Error loading products:', error);
            hideSearchLoading();
            showToast('Failed to load products', 'error');
        }
    }

    function buildCategoryList() {
        productCategories.clear();
        allProducts.forEach(product => {
            const category = product.category || product.cat || 'Uncategorized';
            productCategories.add(category);
        });

        categoryTabs.innerHTML = '<span class="badge rounded-pill category-tab active bg-primary" data-cat="all">All</span>';

        Array.from(productCategories).sort().forEach(category => {
            const tab = document.createElement('span');
            tab.className = 'badge rounded-pill category-tab bg-secondary bg-opacity-50';
            tab.setAttribute('data-cat', category);
            tab.textContent = category;
            tab.style.cssText = 'cursor:pointer;font-size:.85rem;padding:.45em .9em;';
            tab.addEventListener('click', () => {
                document.querySelectorAll('.category-tab').forEach(t => {
                    t.classList.remove('active', 'bg-primary');
                    t.classList.add('bg-secondary', 'bg-opacity-50');
                });
                tab.classList.remove('bg-secondary', 'bg-opacity-50');
                tab.classList.add('active', 'bg-primary');
                currentCategoryFilter = tab.getAttribute('data-cat');
                renderGridView();
            });
            categoryTabs.appendChild(tab);
        });
    }

    // ============================================
    // RENDERING
    // ============================================
    function renderAll() {
        if (currentViewMode === 'grid') {
            document.getElementById('listViewContainer').style.display = 'none';
            document.getElementById('gridViewContainer').style.display = 'block';
            document.getElementById('viewModeGrid').checked = true;
            renderGridView();
        } else {
            document.getElementById('listViewContainer').style.display = 'block';
            document.getElementById('gridViewContainer').style.display = 'none';
            document.getElementById('viewModeList').checked = true;
            renderListView();
        }
    }

    function renderGridView() {
        let filteredProducts = allProducts;

        if (currentSearchQuery.length >= 2) {
            const query = currentSearchQuery.toLowerCase();
            filteredProducts = allProducts.filter(p =>
                p.title.toLowerCase().includes(query) ||
                p.sku.toLowerCase().includes(query) ||
                (p.barcode && p.barcode.toLowerCase().includes(query))
            );
        } else if (currentCategoryFilter !== 'all') {
            filteredProducts = allProducts.filter(p =>
                (p.category || p.cat || 'Uncategorized') === currentCategoryFilter
            );
        }

        if (filteredProducts.length === 0) {
            gridViewBody.innerHTML = '';
            document.getElementById('emptyGridRow').style.display = 'block';
            return;
        }

        document.getElementById('emptyGridRow').style.display = 'none';
        gridViewBody.innerHTML = '';

        filteredProducts.forEach(product => {
            const cartItem = cart.find(i => i.product_id === product.id);
            const isInCart = !!cartItem;
            const cartQty = cartItem ? cartItem.qty : 0;
            const price = product.sale_price || product.price;
            const isOutOfStock = product.stock <= 0;

            const card = document.createElement('div');
            card.className = `pos-grid-card ${isInCart ? 'in-cart' : ''} ${isOutOfStock ? 'out-of-stock' : ''}`;
            card.setAttribute('data-product-id', product.id);

            card.innerHTML = `
                ${isInCart ? `<div class="cart-badge">${formatQuantity(cartQty)}</div>` : ''}
                <div class="stock-badge ${product.stock > 10 ? 'stock-high' : product.stock > 0 ? 'stock-low' : 'stock-out'}">
                    ${product.stock > 0 ? formatNumber(product.stock) : 'Out'}
                </div>
                <div class="product-thumb">
                    ${product.thumbnail ? `<img src="${product.thumbnail}" alt="${product.title}">` : '<i class="bi bi-box-seam"></i>'}
                </div>
                <div class="product-name" title="${product.title}">${product.title}</div>
                <div class="product-price">${formatCurrency(price)}</div>
                <div class="product-sku">${product.sku}</div>
                ${!isOutOfStock ? `<button class="add-to-cart-btn" data-id="${product.id}"><i class="bi bi-cart-plus"></i> Add</button>` : '<button class="add-to-cart-btn disabled" disabled>Out of Stock</button>'}
            `;

            if (!isOutOfStock) {
                const addBtn = card.querySelector('.add-to-cart-btn');
                addBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    openQuantityModal(product);
                });

                card.addEventListener('click', (e) => {
                    if (!e.target.closest('.add-to-cart-btn')) {
                        openQuantityModal(product);
                    }
                });
            }

            gridViewBody.appendChild(card);
        });
    }

    function renderListView() {
        let filteredProducts = allProducts;

        if (currentSearchQuery.length >= 2) {
            const query = currentSearchQuery.toLowerCase();
            filteredProducts = allProducts.filter(p =>
                p.title.toLowerCase().includes(query) ||
                p.sku.toLowerCase().includes(query) ||
                (p.barcode && p.barcode.toLowerCase().includes(query))
            );
        }

        if (filteredProducts.length === 0) {
            listViewBody.innerHTML = '<tr><td colspan="5" class="text-center py-5 text-muted"><i class="bi bi-search display-1 mb-4 text-light"></i><h5>No products found</h5></td></tr>';
            return;
        }

        listViewBody.innerHTML = '';

        filteredProducts.forEach(product => {
            const cartItem = cart.find(i => i.product_id === product.id);
            const isInCart = !!cartItem;
            const price = product.sale_price || product.price;
            const unit = product.primary_unit || 'Unit';
            const isOutOfStock = product.stock <= 0;

            const row = document.createElement('tr');
            row.className = isInCart ? 'table-success' : '';
            row.innerHTML = `
                <td>
                    <div class="d-flex align-items-center">
                        ${product.thumbnail ? `<img src="${product.thumbnail}" width="40" class="rounded me-2">` : '<div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width:40px;height:40px;"><i class="bi bi-image"></i></div>'}
                        <div>
                            <strong>${product.title}</strong><br>
                            <small class="text-muted">SKU: ${product.sku}</small>
                        </div>
                    </div>
                </td>
                <td class="text-center"><span class="badge ${product.stock > 10 ? 'bg-success' : product.stock > 0 ? 'bg-warning' : 'bg-danger'}">${formatNumber(product.stock)}</span></td>
                <td class="text-center">
                    ${!isOutOfStock ? `<button class="btn btn-sm ${isInCart ? 'btn-success' : 'btn-primary'}" data-product='${JSON.stringify(product).replace(/'/g, "&apos;")}'>${isInCart ? `Added ${formatQuantity(cartItem.qty)}` : 'Add to Cart'}</button>` : '<button class="btn btn-sm btn-secondary" disabled>Out of Stock</button>'}
                </td>
                <td class="text-end fw-bold">${formatCurrency(price)}</td>
                <td class="text-center"><span class="badge bg-secondary">${unit}</span></td>
            `;

            if (!isOutOfStock) {
                const addBtn = row.querySelector('button');
                addBtn.addEventListener('click', () => openQuantityModal(product));
            }

            listViewBody.appendChild(row);
        });
    }

    function renderCart() {
        if (cart.length === 0) {
            cartBody.innerHTML = '<tr id="emptyCartRow"><td colspan="5" class="text-center py-5 text-muted"><i class="bi bi-cart fs-1 mb-3 d-block"></i>No items in cart</td></tr>';
            cartCountEl.textContent = '0';
            updateTotals();
            return;
        }

        cartBody.innerHTML = '';
        let subtotal = 0;

        cart.forEach((item, index) => {
            const unitPrice = item.unit_price || item.price;
            let discountedPrice = unitPrice;

            if (item.discount_value > 0) {
                discountedPrice = item.discount_type === 'percent'
                    ? unitPrice * (1 - item.discount_value / 100)
                    : unitPrice - (item.discount_value / item.qty);
                discountedPrice = Math.max(0, discountedPrice);
            }

            const total = discountedPrice * item.qty;
            subtotal += total;

            const row = document.createElement('tr');
            row.innerHTML = `
                <td>
                    <div class="d-flex align-items-center">
                        ${item.thumbnail ? `<img src="${item.thumbnail}" width="35" class="rounded me-2">` : '<i class="bi bi-box me-2"></i>'}
                        <div>
                            <strong class="small">${item.title}</strong>
                            ${item.discount_value > 0 ? `<br><small class="text-warning">-${formatNumber(item.discount_value)}${item.discount_type === 'percent' ? '%' : '₦'}</small>` : ''}
                        </div>
                    </div>
                </td>
                <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center gap-1">
                        <button class="btn btn-sm btn-outline-secondary qty-decr" data-index="${index}">-</button>
                        <span class="mx-1 fw-bold">${formatQuantity(item.qty, item.is_unit_mode)}</span>
                        <button class="btn btn-sm btn-outline-secondary qty-incr" data-index="${index}">+</button>
                    </div>
                    <small class="text-muted d-block">${item.unit_name || 'unit'}</small>
                </td>
                <td class="text-end small">${formatCurrency(discountedPrice)}</td>
                <td class="text-end fw-bold">${formatCurrency(total)}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-outline-warning me-1 discount-item" data-index="${index}" title="Discount"><i class="bi bi-percent"></i></button>
                    <button class="btn btn-sm btn-outline-danger remove-item" data-index="${index}" title="Remove"><i class="bi bi-x"></i></button>
                </td>
            `;

            row.querySelector('.qty-decr').addEventListener('click', () => updateItemQuantity(index, item.qty - (item.is_unit_mode ? 0.001 : 1)));
            row.querySelector('.qty-incr').addEventListener('click', () => updateItemQuantity(index, item.qty + (item.is_unit_mode ? 0.001 : 1)));
            row.querySelector('.discount-item').addEventListener('click', () => openDiscountModal(index));
            row.querySelector('.remove-item').addEventListener('click', () => removeCartItem(index));

            cartBody.appendChild(row);
        });

        cartCountEl.textContent = cart.length;
        updateTotals();
    }

    function updateTotals() {
        let subtotal = 0;
        cart.forEach(item => {
            let unitPrice = item.unit_price || item.price;
            if (item.discount_value > 0) {
                unitPrice = item.discount_type === 'percent'
                    ? unitPrice * (1 - item.discount_value / 100)
                    : unitPrice - (item.discount_value / item.qty);
                unitPrice = Math.max(0, unitPrice);
            }
            subtotal += unitPrice * item.qty;
        });

        const taxRate = {{ config('pos.tax_rate', 0) }};
        let taxAmount = (subtotal * taxRate) / 100;
        let discountAmount = 0;

        if (orderDiscountValue > 0) {
            discountAmount = orderDiscountType === 'percent'
                ? (subtotal * orderDiscountValue) / 100
                : orderDiscountValue;
            discountAmount = Math.min(discountAmount, subtotal);
        }

        const grandTotal = subtotal + taxAmount - discountAmount;

        subtotalEl.textContent = formatCurrency(subtotal);
        taxAmountEl.textContent = formatCurrency(taxAmount);
        discountAmountEl.textContent = `-${formatCurrency(discountAmount)}`;
        grandTotalEl.textContent = formatCurrency(grandTotal);
        document.getElementById('discountRow').style.display = discountAmount > 0 ? 'flex' : 'none';
    }

    // ============================================
    // CART OPERATIONS
    // ============================================
    function addToCart(product, quantity, selectedUnit = null, isUnitMode = false) {
        const existingIndex = cart.findIndex(item => item.product_id === product.id);
        const unitPrice = product.sale_price || product.price;

        const cartItem = {
            product_id: product.id,
            title: product.title,
            price: unitPrice * quantity,
            unit_price: unitPrice,
            qty: quantity,
            sku: product.sku,
            barcode: product.barcode,
            thumbnail: product.thumbnail,
            unit_name: selectedUnit ? selectedUnit.short_name : (product.primary_unit || 'Unit'),
            unit_id: selectedUnit ? selectedUnit.id : null,
            is_unit_mode: isUnitMode,
            discount_type: null,
            discount_value: 0,
            discounted_price: unitPrice
        };

        if (existingIndex !== -1) {
            cart[existingIndex].qty += quantity;
            cart[existingIndex].price = cart[existingIndex].unit_price * cart[existingIndex].qty;
        } else {
            cart.push(cartItem);
        }

        renderCart();
        renderAll();
        showAddToCartNotification(product, quantity, selectedUnit);
    }

    function updateItemQuantity(index, newQty) {
        if (newQty <= 0) {
            removeCartItem(index);
            return;
        }
        cart[index].qty = parseFloat(newQty.toFixed(3));
        cart[index].price = cart[index].unit_price * cart[index].qty;
        renderCart();
        renderAll();
    }

    function removeCartItem(index) {
        const item = cart[index];
        cart.splice(index, 1);
        renderCart();
        renderAll();
        showToast(`${item.title} removed from cart`, 'info');
    }

    function clearCart() {
        if (cart.length === 0) return;
        Swal.fire({
            title: 'Clear Cart?',
            text: 'Remove all items from cart?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, clear'
        }).then(result => {
            if (result.isConfirmed) {
                cart = [];
                orderDiscountValue = 0;
                document.getElementById('discountValue').value = '0';
                renderCart();
                renderAll();
                showToast('Cart cleared', 'success');
            }
        });
    }

    // ============================================
    // NOTIFICATIONS
    // ============================================
    function showAddToCartNotification(product, quantity, unit) {
        const price = product.sale_price || product.price;
        const total = price * quantity;
        const unitText = unit ? ` (${formatQuantity(quantity)} ${unit.short_name})` : ` (${formatQuantity(quantity)} ${product.primary_unit || 'unit'})`;

        const toast = document.createElement('div');
        toast.className = 'toast align-items-center text-white bg-success border-0 show';
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="bi bi-cart-check-fill me-2"></i>
                    <strong>Added to Cart!</strong><br>
                    ${product.title}${unitText}<br>
                    ${formatCurrency(total)}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        const container = document.getElementById('toastContainer');
        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);

        toast.querySelector('.btn-close').addEventListener('click', () => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        });
    }

    function showToast(message, type = 'success', duration = 3000) {
        const bgColor = type === 'success' ? 'bg-success' : type === 'error' ? 'bg-danger' : type === 'warning' ? 'bg-warning' : 'bg-info';
        const icon = type === 'success' ? 'bi-check-circle-fill' : type === 'error' ? 'bi-exclamation-triangle-fill' : 'bi-info-circle-fill';

        const toast = document.createElement('div');
        toast.className = `toast align-items-center text-white ${bgColor} show`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <i class="${icon} me-2"></i> ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        document.getElementById('toastContainer').appendChild(toast);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, duration);
    }

    // ============================================
    // MODAL HANDLERS
    // ============================================
    function openQuantityModal(product) {
        currentProduct = product;
        const price = product.sale_price || product.price;

        document.getElementById('modalProductLabel').textContent = product.title;
        document.getElementById('modalProductPrice').textContent = formatCurrency(price);
        document.getElementById('modalProductStock').textContent = `Stock: ${formatNumber(product.stock)}`;
        document.getElementById('modalProductUnit').textContent = product.primary_unit || 'Unit';

        // Reset to quantity mode
        document.getElementById('measureQuantity').checked = true;
        document.getElementById('measureUnit').disabled = !(product.units && product.units.length > 0);
        switchToQuantityMode();

        modalQty.value = 1;
        updateModalTotal(price);
        quantityModal.show();
    }

    function switchToQuantityMode() {
        document.getElementById('quantityInputSection').style.display = 'block';
        document.getElementById('unitInputSection').style.display = 'none';
        document.getElementById('unitSelection').style.display = 'none';
        document.getElementById('quickAmounts').style.display = 'flex';
        document.getElementById('removeFromCartBtn').style.display = 'none';
        currentMeasurementType = 'quantity';

        document.querySelectorAll('.quick-amount').forEach(btn => {
            btn.onclick = () => {
                modalQty.value = btn.dataset.value;
                updateModalTotal();
            };
        });
    }

    function switchToUnitMode() {
        document.getElementById('quantityInputSection').style.display = 'none';
        document.getElementById('unitInputSection').style.display = 'block';
        document.getElementById('unitSelection').style.display = 'block';
        document.getElementById('quickAmounts').style.display = 'none';
        document.getElementById('removeFromCartBtn').style.display = 'none';
        currentMeasurementType = 'unit';
        loadUnits();
    }

    async function loadUnits() {
        if (!currentProduct) return;
        try {
            const response = await axios.get(`/pos/product/${currentProduct.id}/units`);
            const units = response.data.units || [];
            const select = document.getElementById('unitSelect');
            select.innerHTML = '<option value="">-- Select unit --</option>';

            units.forEach(unit => {
                const option = document.createElement('option');
                option.value = unit.id;
                option.textContent = `${unit.name} (${unit.short_name})`;
                select.appendChild(option);
            });

            select.onchange = () => {
                const selectedId = select.value;
                currentSelectedUnit = units.find(u => u.id == selectedId);
                if (currentSelectedUnit) {
                    updateModalTotal();
                }
            };
        } catch (error) {
            console.error('Error loading units:', error);
        }
    }

    function updateModalTotal(price = null) {
        const unitPrice = price || (currentProduct ? (currentProduct.sale_price || currentProduct.price) : 0);
        let quantity = 1;

        if (currentMeasurementType === 'quantity') {
            quantity = parseFloat(document.getElementById('modalQty').value) || 1;
        } else {
            quantity = parseFloat(document.getElementById('modalUnit').value) || 1;
        }

        const total = unitPrice * quantity;
        document.getElementById('totalPriceDisplay').textContent = formatCurrency(total);
    }

    function openDiscountModal(cartIndex) {
        currentCartIndex = cartIndex;
        const item = cart[cartIndex];
        document.getElementById('discountItemName').textContent = item.title;
        document.getElementById('itemDiscountValue').value = item.discount_value || 0;
        document.getElementById('itemDiscountType').value = item.discount_type || 'percent';
        new bootstrap.Modal(document.getElementById('itemDiscountModal')).show();
    }

    // ============================================
    // SEARCH HANDLERS
    // ============================================
    function searchProducts(query) {
        currentSearchQuery = query;

        if (query.length >= 2) {
            showSearchLoading();
            axios.get('{{ route("pos.search") }}', { params: { q: query } })
                .then(res => {
                    const newProducts = res.data || [];
                    // Merge new products with existing
                    newProducts.forEach(np => {
                        const idx = allProducts.findIndex(p => p.id === np.id);
                        if (idx === -1) allProducts.push(np);
                        else allProducts[idx] = np;
                    });
                    buildCategoryList();
                    renderAll();
                    hideSearchLoading();
                })
                .catch(err => {
                    hideSearchLoading();
                    showToast('Search failed', 'error');
                });
        } else if (query.length === 0) {
            loadInitialProducts();
        } else {
            renderAll();
        }
    }

    input.addEventListener('input', debounce(e => {
        searchProducts(e.target.value.trim());
    }, 300));

    input.addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            const scannedCode = input.value.trim();
            if (scannedCode) {
                processBarcode(scannedCode);
            }
        }
    });

    async function processBarcode(barcode) {
        if (!barcode) return;
        input.value = '';
        showSearchLoading();
        try {
            const response = await axios.get('{{ route("pos.search") }}', { params: { q: barcode } });
            const products = response.data || [];
            if (products.length > 0) {
                const product = products[0];
                const idx = allProducts.findIndex(p => p.id === product.id);
                if (idx === -1) allProducts.unshift(product);
                else allProducts[idx] = product;
                buildCategoryList();
                renderAll();
                openQuantityModal(product);
                hideSearchLoading();
            } else {
                hideSearchLoading();
                showToast('Product not found', 'error');
            }
        } catch (error) {
            hideSearchLoading();
            showToast('Error scanning barcode', 'error');
        }
    }

    // ============================================
    // HELPER FUNCTIONS
    // ============================================
    function formatCurrency(amount) {
        return '₦' + formatNumber(amount, 2);
    }

    function formatNumber(number, decimals = 0) {
        return new Intl.NumberFormat('en-NG', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(number);
    }

    function formatQuantity(qty, isUnit = false) {
        return isUnit ? formatNumber(qty, 2) : formatNumber(qty, 0);
    }

    function debounce(func, delay) {
        let timeout;
        return function(...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    }

    function showSearchLoading() {
        searchLoading.classList.remove('d-none');
    }

    function hideSearchLoading() {
        searchLoading.classList.add('d-none');
    }

    // ============================================
    // ORDER OPERATIONS
    // ============================================
    function applyOrderDiscount() {
        orderDiscountValue = parseFloat(document.getElementById('discountValue').value) || 0;
        orderDiscountType = document.getElementById('discountType').value;

        if (orderDiscountType === 'percent' && orderDiscountValue > 100) {
            showToast('Percentage cannot exceed 100%', 'warning');
            orderDiscountValue = 100;
            document.getElementById('discountValue').value = 100;
        }

        updateTotals();
        showToast('Discount applied', 'success');
    }

    function applyItemDiscount() {
        if (currentCartIndex === null) return;
        const value = parseFloat(document.getElementById('itemDiscountValue').value) || 0;
        const type = document.getElementById('itemDiscountType').value;

        if (type === 'percent' && value > 100) {
            showToast('Percentage cannot exceed 100%', 'warning');
            return;
        }

        cart[currentCartIndex].discount_type = type;
        cart[currentCartIndex].discount_value = value;
        renderCart();
        bootstrap.Modal.getInstance(document.getElementById('itemDiscountModal')).hide();
        showToast('Discount applied to item', 'success');
    }

    function holdOrder() {
        if (cart.length === 0) {
            showToast('Cart is empty', 'warning');
            return;
        }

        const heldOrders = JSON.parse(localStorage.getItem('heldOrders') || '[]');
        heldOrders.push({
            id: Date.now(),
            cart: JSON.parse(JSON.stringify(cart)),
            customer: document.getElementById('customerSelect').value,
            discount: { type: orderDiscountType, value: orderDiscountValue },
            timestamp: new Date().toISOString()
        });
        localStorage.setItem('heldOrders', JSON.stringify(heldOrders));

        cart = [];
        orderDiscountValue = 0;
        document.getElementById('discountValue').value = '0';
        renderCart();
        renderAll();
        showToast('Order held successfully', 'success');
    }

    function loadHeldOrders() {
        const heldOrders = JSON.parse(localStorage.getItem('heldOrders') || '[]');
        const container = document.getElementById('heldOrdersList');
        const noOrders = document.getElementById('noHeldOrders');

        if (heldOrders.length === 0) {
            container.innerHTML = '';
            noOrders.style.display = 'block';
        } else {
            noOrders.style.display = 'none';
            container.innerHTML = heldOrders.map(order => `
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small>${new Date(order.timestamp).toLocaleString()}</small>
                            <div>${order.cart.length} items</div>
                        </div>
                        <button class="btn btn-sm btn-primary load-held" data-id="${order.id}">Load</button>
                    </div>
                </div>
            `).join('');

            document.querySelectorAll('.load-held').forEach(btn => {
                btn.addEventListener('click', () => {
                    const order = heldOrders.find(o => o.id == btn.dataset.id);
                    if (order) {
                        cart = order.cart;
                        if (order.discount) {
                            orderDiscountType = order.discount.type;
                            orderDiscountValue = order.discount.value;
                            document.getElementById('discountType').value = order.discount.type;
                            document.getElementById('discountValue').value = order.discount.value;
                        }
                        renderCart();
                        renderAll();
                        bootstrap.Modal.getInstance(document.getElementById('loadOrderModal')).hide();
                        showToast('Order loaded', 'success');
                    }
                });
            });
        }
        new bootstrap.Modal(document.getElementById('loadOrderModal')).show();
    }

    async function completeOrder() {
        if (cart.length === 0) {
            showToast('Cart is empty', 'warning');
            return;
        }

        const paymentMethod = document.querySelector('input[name="payment"]:checked').value;
        const customerId = document.getElementById('customerSelect').value || null;
        const isOffline = document.getElementById('offlineModeToggle').checked || !navigator.onLine;

        if (isOffline) {
            const offlineOrders = JSON.parse(localStorage.getItem('offlineOrders') || '[]');
            offlineOrders.push({
                id: 'OFFLINE-' + Date.now(),
                items: cart,
                payment_method: paymentMethod,
                customer_id: customerId,
                discount: { type: orderDiscountType, value: orderDiscountValue },
                timestamp: Date.now()
            });
            localStorage.setItem('offlineOrders', JSON.stringify(offlineOrders));
            cart = [];
            orderDiscountValue = 0;
            renderCart();
            renderAll();
            Swal.fire('Order Saved Offline', 'Will sync when online', 'warning');
            return;
        }

        Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        try {
            const response = await axios.post('{{ route("pos.order.save") }}', {
                items: cart.map(item => ({
                    product_id: item.product_id,
                    qty: item.qty,
                    unit_id: item.unit_id,
                    sale_price: item.unit_price,
                    discount_type: item.discount_type,
                    discount_value: item.discount_value
                })),
                payment_method: paymentMethod,
                customer_id: customerId,
                discount_type: orderDiscountType,
                discount_value: orderDiscountValue,
                _token: '{{ csrf_token() }}'
            });

            Swal.close();

            if (response.data.success) {
                Swal.fire({
                    title: 'Order Complete!',
                    html: `<h3>${formatCurrency(response.data.total)}</h3><p>Order #${response.data.order_id}</p>`,
                    icon: 'success',
                    confirmButtonText: 'Print Receipt'
                }).then(() => {
                    window.open(`/pos/receipt/${response.data.order_id}`, '_blank');
                });

                cart = [];
                orderDiscountValue = 0;
                document.getElementById('discountValue').value = '0';
                renderCart();
                renderAll();
                showToast('Order completed successfully', 'success');
            }
        } catch (error) {
            Swal.close();
            showToast(error.response?.data?.message || 'Order failed', 'error');
        }
    }

    // ============================================
    // EVENT LISTENERS
    // ============================================
    document.getElementById('viewModeList').addEventListener('change', () => {
        currentViewMode = 'list';
        localStorage.setItem('posViewMode', 'list');
        renderAll();
    });

    document.getElementById('viewModeGrid').addEventListener('change', () => {
        currentViewMode = 'grid';
        localStorage.setItem('posViewMode', 'grid');
        renderAll();
    });

    document.getElementById('clearCart').addEventListener('click', clearCart);
    document.getElementById('holdOrderBtn').addEventListener('click', holdOrder);
    document.getElementById('loadHeldBtn').addEventListener('click', loadHeldOrders);
    document.getElementById('completeOrder').addEventListener('click', completeOrder);
    document.getElementById('applyDiscountBtn').addEventListener('click', applyOrderDiscount);
    document.getElementById('applyItemDiscountBtn').addEventListener('click', applyItemDiscount);
    document.getElementById('confirmAddBtn').addEventListener('click', () => {
        if (!currentProduct) return;

        let quantity = 1;
        let selectedUnit = null;
        let isUnitMode = false;

        if (currentMeasurementType === 'quantity') {
            quantity = parseFloat(document.getElementById('modalQty').value) || 1;
        } else {
            quantity = parseFloat(document.getElementById('modalUnit').value) || 1;
            isUnitMode = true;
            const unitSelect = document.getElementById('unitSelect');
            const unitId = unitSelect.value;
            if (unitId) {
                selectedUnit = { id: unitId, short_name: unitSelect.options[unitSelect.selectedIndex]?.text.split('(')[1]?.replace(')', '') || 'unit' };
            }
        }

        if (quantity <= 0) {
            showToast('Quantity must be greater than 0', 'warning');
            return;
        }

        if (quantity > currentProduct.stock) {
            showToast(`Only ${formatNumber(currentProduct.stock)} available`, 'warning');
            return;
        }

        addToCart(currentProduct, quantity, selectedUnit, isUnitMode);
        quantityModal.hide();
    });

    document.getElementById('quickCustomerBtn').addEventListener('click', () => quickCustomerModal.show());
    document.getElementById('saveQuickCustomerBtn').addEventListener('click', async () => {
        const firstName = document.getElementById('firstName').value.trim();
        const lastName = document.getElementById('lastName').value.trim();

        if (!firstName || !lastName) {
            showToast('First and last name required', 'warning');
            return;
        }

        try {
            const response = await axios.post('{{ route("customers.quick") }}', {
                first_name: firstName,
                last_name: lastName,
                phone_number: document.getElementById('phoneNumber').value.trim(),
                email: document.getElementById('email').value.trim(),
                _token: '{{ csrf_token() }}'
            });

            if (response.data.success) {
                const select = document.getElementById('customerSelect');
                const option = document.createElement('option');
                option.value = response.data.customer.id;
                option.textContent = `${firstName} ${lastName}`;
                select.appendChild(option);
                select.value = response.data.customer.id;
                quickCustomerModal.hide();
                document.getElementById('quickCustomerForm').reset();
                showToast('Customer added', 'success');
            }
        } catch (error) {
            showToast('Failed to add customer', 'error');
        }
    });

    // Modal toggle handlers
    document.getElementById('measureQuantity').addEventListener('click', switchToQuantityMode);
    document.getElementById('measureUnit').addEventListener('click', switchToUnitMode);

    document.getElementById('decreaseQty').addEventListener('click', () => {
        const qty = parseFloat(document.getElementById('modalQty').value) || 1;
        if (qty > 1) {
            document.getElementById('modalQty').value = qty - 1;
            updateModalTotal();
        }
    });

    document.getElementById('increaseQty').addEventListener('click', () => {
        const qty = parseFloat(document.getElementById('modalQty').value) || 1;
        document.getElementById('modalQty').value = qty + 1;
        updateModalTotal();
    });

    document.getElementById('decreaseUnit').addEventListener('click', () => {
        const unit = parseFloat(document.getElementById('modalUnit').value) || 1;
        if (unit > 0.001) {
            document.getElementById('modalUnit').value = (unit - 0.001).toFixed(3);
            updateModalTotal();
        }
    });

    document.getElementById('increaseUnit').addEventListener('click', () => {
        const unit = parseFloat(document.getElementById('modalUnit').value) || 1;
        document.getElementById('modalUnit').value = (unit + 0.001).toFixed(3);
        updateModalTotal();
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', e => {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') return;
        switch(e.key) {
            case 'F1': e.preventDefault(); input.focus(); break;
            case 'F2': e.preventDefault(); clearCart(); break;
            case 'F3': e.preventDefault(); completeOrder(); break;
            case 'F4': e.preventDefault(); holdOrder(); break;
        }
    });

    // Online/Offline handling
    window.addEventListener('online', () => {
        document.getElementById('connectionStatus').className = 'badge bg-success';
        document.getElementById('connectionStatus').innerHTML = '<i class="bi bi-wifi"></i> Online';
        showToast('Back online', 'success');
    });

    window.addEventListener('offline', () => {
        document.getElementById('connectionStatus').className = 'badge bg-danger';
        document.getElementById('connectionStatus').innerHTML = '<i class="bi bi-wifi-off"></i> Offline';
        showToast('Offline mode - orders saved locally', 'warning');
    });

    let currentMeasurementType = 'quantity';
    let currentSelectedUnit = null;

    // Bootstrap
    loadInitialProducts();
    renderCart();
});
</script>

<style>
/* Grid View Styles */
.pos-grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 15px;
    padding: 5px;
    max-height: 500px;
    overflow-y: auto;
}

.pos-grid-card {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 12px 8px;
    cursor: pointer;
    transition: all 0.2s ease;
    background: white;
    position: relative;
    text-align: center;
}

.pos-grid-card:hover {
    border-color: #0d6efd;
    transform: translateY(-3px);
    box-shadow: 0 6px 15px rgba(0,0,0,0.1);
}

.pos-grid-card.in-cart {
    border-color: #198754;
    background: #f0fff4;
}

.pos-grid-card.out-of-stock {
    opacity: 0.5;
    cursor: not-allowed;
}

.pos-grid-card .cart-badge {
    position: absolute;
    top: 8px;
    left: 8px;
    background: #198754;
    color: white;
    border-radius: 20px;
    padding: 2px 8px;
    font-size: 11px;
    font-weight: bold;
}

.pos-grid-card .stock-badge {
    position: absolute;
    top: 8px;
    right: 8px;
    border-radius: 20px;
    padding: 2px 8px;
    font-size: 10px;
    font-weight: bold;
}

.stock-high { background: #198754; color: white; }
.stock-low { background: #ffc107; color: #000; }
.stock-out { background: #dc3545; color: white; }

.pos-grid-card .product-thumb {
    width: 80px;
    height: 80px;
    margin: 0 auto 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 10px;
    overflow: hidden;
}

.pos-grid-card .product-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.pos-grid-card .product-thumb i {
    font-size: 40px;
    color: #adb5bd;
}

.pos-grid-card .product-name {
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 5px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.pos-grid-card .product-price {
    font-size: 14px;
    font-weight: bold;
    color: #198754;
    margin-bottom: 5px;
}

.pos-grid-card .product-sku {
    font-size: 10px;
    color: #6c757d;
    margin-bottom: 8px;
}

.pos-grid-card .add-to-cart-btn {
    width: 100%;
    padding: 5px;
    border: none;
    border-radius: 6px;
    background: #0d6efd;
    color: white;
    font-size: 12px;
    transition: all 0.2s;
}

.pos-grid-card .add-to-cart-btn:hover {
    background: #0b5ed7;
    transform: scale(1.02);
}

.pos-grid-card .add-to-cart-btn.disabled {
    background: #6c757d;
    cursor: not-allowed;
}

/* Category Tabs */
.category-tab {
    transition: all 0.2s ease;
}

.category-tab:hover {
    transform: translateY(-2px);
}

/* Cart Table */
.table-sm td {
    vertical-align: middle;
}

.qty-decr, .qty-incr {
    width: 28px;
    height: 28px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

/* Toast Notifications */
.toast-container {
    z-index: 9999;
}

.toast {
    min-width: 250px;
    backdrop-filter: blur(10px);
}

/* Loading Overlay */
#searchLoading {
    backdrop-filter: blur(2px);
}

/* Responsive */
@media (max-width: 768px) {
    .pos-grid-container {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 10px;
    }

    .pos-grid-card .product-thumb {
        width: 60px;
        height: 60px;
    }

    .pos-grid-card .product-name {
        font-size: 11px;
    }
}
</style>
@endsection
