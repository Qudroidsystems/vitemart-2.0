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
                            <div class="position-relative mb-3">
                                <input type="text" id="barcodeInput" class="form-control form-control-lg fs-3"
                                       placeholder="Scan barcode or search by name/SKU..." autofocus autocomplete="off"
                                       aria-label="Search or scan products">
                                <i class="bi bi-upc-scan position-absolute top-50 end-0 translate-middle-y me-4 fs-2 text-muted"></i>
                            </div>
                            <!-- Toolbar: shortcuts + connection status + VIEW TOGGLE -->
                            <div class="d-flex align-items-center mb-3 flex-wrap gap-2">
                                <div class="me-auto">
                                    <small class="text-muted">Shortcuts:</small>
                                    <span class="badge bg-secondary ms-1">F1</span> Focus Search
                                    <span class="badge bg-secondary ms-2">F2</span> Clear Cart
                                    <span class="badge bg-secondary ms-2">F3</span> Complete Order
                                    <span class="badge bg-secondary ms-2">F4</span> Hold Order
                                </div>
                                <!-- VIEW TOGGLE BUTTONS -->
                                <div class="btn-group me-2" role="group" aria-label="Product view mode" id="viewToggleGroup">
                                    <input type="radio" class="btn-check" name="viewMode" id="viewModeList" value="list" checked>
                                    <label class="btn btn-outline-secondary btn-sm" for="viewModeList" title="List view">
                                        <i class="bi bi-list-ul me-1"></i> List
                                    </label>
                                    <input type="radio" class="btn-check" name="viewMode" id="viewModeGrid" value="grid">
                                    <label class="btn btn-outline-secondary btn-sm" for="viewModeGrid" title="Grid view">
                                        <i class="bi bi-grid-3x3-gap me-1"></i> Grid
                                    </label>
                                    <a href="{{ route('pos.grid') }}" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-grid-3x3-gap me-1"></i> Grid Mode
                                    </a>

                                </div>
                                <div>
                                    <span id="connectionStatus" class="badge bg-success">
                                        <i class="bi bi-wifi"></i> Online
                                    </span>
                                </div>
                            </div>
                            <!-- Category Filter Tabs (visible in both modes) -->
                            <div class="mb-3" id="categoryFilterBar" style="display:none;">
                                <div class="d-flex gap-2 flex-wrap" id="categoryTabs">
                                    <span class="badge rounded-pill category-tab active bg-primary" data-cat="all" style="cursor:pointer;font-size:.85rem;padding:.45em .9em;">All</span>
                                </div>
                            </div>
                            <div class="table-responsive flex-grow-1 position-relative" id="productsTableWrap">
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
                                <!-- LIST VIEW TABLE -->
                                <table class="table table-hover align-middle mb-0" id="listViewTable">
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
                                <!-- GRID VIEW CONTAINER (hidden by default) -->
                                <div id="gridViewContainer" style="display:none;">
                                    <div id="gridViewBody" class="pos-grid-container"></div>
                                    <div id="emptyGridRow" class="text-center py-5 text-muted" style="display:none;">
                                        <i class="bi bi-search display-1 mb-4 text-light"></i>
                                        <h5>Ready to sell</h5>
                                        <p class="mb-0">Type or scan to find products</p>
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
                                               placeholder="0" min="0" step="0.01" aria-label="Discount amount" value="0">
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
                                <div class="d-flex justify-content-between text-danger fw-bold" id="discountRow" style="display: none;">
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

<!-- ==================== MODALS ==================== -->

<!-- Quantity Modal with Unit Support -->
<div class="modal fade" id="quantityModal" tabindex="-1" role="dialog" aria-labelledby="quantityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title" id="quantityModalLabel">
                    <i class="bi bi-cart-plus me-2"></i> Adjust Quantity/Unit
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="text-center mb-4">
                    <div class="product-icon bg-light-primary rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                        <i class="bi bi-box-seam fs-3 text-primary"></i>
                    </div>
                    <h6 class="text-muted mb-1">Product:</h6>
                    <h4 class="modal-product-name text-primary fw-bold mb-0" id="modalProductLabel"></h4>
                    <div class="mt-2">
                        <span class="badge bg-info" id="modalProductPrice"></span>
                        <span class="badge bg-warning ms-2" id="modalProductStock"></span>
                        <span class="badge bg-secondary ms-2" id="modalProductUnit"></span>
                    </div>
                    <div class="mt-2">
                        <small class="text-muted d-flex justify-content-center gap-2">
                            <span id="modalProductSku"></span>
                            <span id="modalProductBarcode"></span>
                        </small>
                    </div>
                </div>
                <div class="card border-0 bg-light mb-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label fw-bold text-dark mb-0">Measurement Type</label>
                            <div class="btn-group btn-group-sm" role="group">
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
                        <div id="unitSelection" class="mt-3" style="display: none;">
                            <label class="form-label fw-semibold">Select Unit <span class="text-danger">*</span></label>
                            <select class="form-select" id="unitSelect" required>
                                <option value="">-- Please select a unit --</option>
                            </select>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="rememberUnitPreference">
                                <label class="form-check-label" for="rememberUnitPreference">
                                    Remember my preference for this product
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
                <div id="priceInputSection" class="card border-0 bg-light mb-3" style="display: none;">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label fw-bold text-dark mb-0" id="totalPriceLabel">Total Price</label>
                            <small class="text-muted" id="originalPriceText"></small>
                        </div>
                        <div class="input-group input-group-lg mb-3">
                            <span class="input-group-text bg-primary text-white fw-bold">₦</span>
                            <input type="number" id="pricePerUnit" class="form-control text-center border-secondary fs-3 fw-bold"
                                   min="0" step="0.01" placeholder="Enter price" style="height: 60px;" disabled>
                        </div>
                        <div class="text-center">
                            <small class="text-muted d-block">Price for <span id="amountDisplay">1.000</span> <span id="unitDisplay">unit</span></small>
                            <small class="text-success fw-bold d-block mt-1" id="calculatedPriceDisplay"></small>
                        </div>
                    </div>
                </div>
                <div class="card border-0 bg-light mb-4">
                    <div class="card-body text-center">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <label class="form-label fw-bold text-dark mb-0" id="measurementLabel">Enter Amount</label>
                            <small class="text-muted" id="previousQtyText"></small>
                        </div>
                        <div id="quantityInputSection">
                            <div class="input-group input-group-lg">
                                <button class="btn btn-outline-secondary" type="button" id="decreaseQty" aria-label="Decrease quantity">
                                    <i class="bi bi-dash-lg"></i>
                                </button>
                                <input type="number" id="modalQty" class="form-control text-center border-secondary fs-2 fw-bold"
                                       min="1" value="1" step="1" autofocus style="height: 60px;" aria-label="Quantity">
                                <button class="btn btn-outline-secondary" type="button" id="increaseQty" aria-label="Increase quantity">
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div id="unitInputSection" style="display: none;">
                            <div class="input-group input-group-lg">
                                <button class="btn btn-outline-secondary" type="button" id="decreaseUnit" aria-label="Decrease unit" disabled>
                                    <i class="bi bi-dash-lg"></i>
                                </button>
                                <input type="number" id="modalUnit" class="form-control text-center border-secondary fs-2 fw-bold"
                                       min="0.001" value="1" step="0.001" style="height: 60px;" aria-label="Unit amount" disabled>
                                <button class="btn btn-outline-secondary" type="button" id="increaseUnit" aria-label="Increase unit" disabled>
                                    <i class="bi bi-plus-lg"></i>
                                </button>
                            </div>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted d-block">Press Enter to confirm, ESC to cancel</small>
                            <small class="text-success fw-semibold d-block mt-1" id="totalPriceDisplay">Total: ₦0.00</small>
                        </div>
                    </div>
                </div>
                <div class="mb-4" id="quantityQuickButtons">
                    <div class="row g-2 mb-2">
                        <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 py-2 quick-btn" data-value="1">1</button></div>
                        <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 py-2 quick-btn" data-value="2">2</button></div>
                        <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 py-2 quick-btn" data-value="3">3</button></div>
                        <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 py-2 quick-btn" data-value="5">5</button></div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 py-2 quick-btn" data-value="10">10</button></div>
                        <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 py-2 quick-btn" data-value="20">20</button></div>
                        <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 py-2 quick-btn" data-value="50">50</button></div>
                        <div class="col-3"><button type="button" class="btn btn-outline-secondary w-100 py-2 quick-btn" data-value="100">100</button></div>
                    </div>
                </div>
                <div id="unitQuickButtons" style="display: none;">
                    <h6 class="text-muted mb-2">Quick Amounts</h6>
                    <div class="row g-2">
                        <div class="col-3"><button type="button" class="btn btn-outline-success w-100 py-2 unit-quick-btn" data-value="0.25">¼</button></div>
                        <div class="col-3"><button type="button" class="btn btn-outline-success w-100 py-2 unit-quick-btn" data-value="0.5">½</button></div>
                        <div class="col-3"><button type="button" class="btn btn-outline-success w-100 py-2 unit-quick-btn" data-value="0.75">¾</button></div>
                        <div class="col-3"><button type="button" class="btn btn-outline-success w-100 py-2 unit-quick-btn" data-value="1">1</button></div>
                    </div>
                    <div class="row g-2 mt-2">
                        <div class="col-3"><button type="button" class="btn btn-outline-success w-100 py-2 unit-quick-btn" data-value="2.5">2.5</button></div>
                        <div class="col-3"><button type="button" class="btn btn-outline-success w-100 py-2 unit-quick-btn" data-value="5">5</button></div>
                        <div class="col-3"><button type="button" class="btn btn-outline-success w-100 py-2 unit-quick-btn" data-value="10">10</button></div>
                        <div class="col-3"><button type="button" class="btn btn-outline-success w-100 py-2 unit-quick-btn" data-value="25">25</button></div>
                    </div>
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

<!-- Accessibility live regions -->
<div class="visually-hidden" role="status" aria-live="polite" id="cartStatus"></div>
<div class="visually-hidden" role="status" aria-live="polite" id="searchStatus"></div>

<!-- Toast Container -->
<div id="toastContainer" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>

<script src="{{ asset('theme/layouts/assets/libs/chart.js/chart.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/libs/axios/axios.min.js') }}"></script>
<script src="{{ asset('theme/layouts/assets/libs/sweetalert2/sweetalert2.min.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ============================================
    // INITIALIZATION
    // ============================================
    const input             = document.getElementById('barcodeInput');
    const resultsBody       = document.getElementById('resultsBody');
    const emptySearchRow    = document.getElementById('emptySearchRow');
    const gridViewBody      = document.getElementById('gridViewBody');
    const emptyGridRow      = document.getElementById('emptyGridRow');
    const listViewTable     = document.getElementById('listViewTable');
    const gridViewContainer = document.getElementById('gridViewContainer');
    const cartBody          = document.getElementById('cartBody');
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

    // Bootstrap tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));

    // State
    let cart                    = [];
    let allSearchedProducts     = [];
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

    // View mode state — persisted in localStorage
    let currentViewMode = localStorage.getItem('posViewMode') || 'list';

    // Grid category filter state
    let activeCategoryFilter = 'all';
    let productCategories    = [];

    // Barcode scanning
    let barcodeBuffer  = '';
    let barcodeTimeout = null;

    // Unit management
    let availableUnits         = [];
    let selectedUnit           = null;
    let currentMeasurementType = 'quantity';
    let originalPricePerUnit   = 0;
    let isPriceInputActive     = false;

    input.focus();

    // ============================================
    // BARCODE SCANNING
    // ============================================
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            const scannedCode = input.value.trim();
            if (scannedCode) processBarcode(scannedCode);
            return;
        }
    });

    input.addEventListener('input', debounce(() => {
        const q = input.value.trim();
        currentSearchQuery = q;
        if (q.length >= 2 && !isLikelyBarcode(q)) {
            searchProducts(q);
        } else if (q.length === 0) {
            clearAllUnselectedItems();
            renderAll();
        } else {
            showEmptyState();
        }
    }, 300));

    async function processBarcode(barcode) {
        if (!barcode) return;
        input.value = '';
        showSearchLoading();
        try {
            const response = await axios.get('{{ route("pos.search") }}', { params: { q: barcode } });
            const products  = response.data || [];
            if (products.length > 0) {
                const product       = products[0];
                const existingIndex = allSearchedProducts.findIndex(p => p.id === product.id);
                if (existingIndex === -1) allSearchedProducts.push(product);
                else allSearchedProducts[existingIndex] = product;
                const cartItem = cart.find(i => i.product_id === product.id);
                if (cartItem) {
                    const button = document.querySelector(`[data-product-id="${product.id}"]`);
                    if (button) openQuantityModal(button);
                } else {
                    const productJson = JSON.stringify(product).replace(/'/g, "&apos;");
                    const tempButton  = document.createElement('button');
                    tempButton.dataset.product   = productJson;
                    tempButton.dataset.productId = product.id;
                    openQuantityModal(tempButton);
                }
                renderAll();
                hideSearchLoading();
                playScanSound();
            } else {
                hideSearchLoading();
                showToast('Product not found', 'error');
            }
        } catch (error) {
            hideSearchLoading();
            showToast('Error scanning barcode', 'error');
        }
    }

    function isLikelyBarcode(input) {
        if (input.length >= 8 && input.length <= 14 && /^\d+$/.test(input)) return true;
        if (input.startsWith('PROD') && input.length > 10) return true;
        return false;
    }

    function playScanSound() {
        try {
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const oscillator   = audioContext.createOscillator();
            const gainNode     = audioContext.createGain();
            oscillator.connect(gainNode);
            gainNode.connect(audioContext.destination);
            oscillator.frequency.value = 800;
            oscillator.type            = 'sine';
            gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
            gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.1);
            oscillator.start(audioContext.currentTime);
            oscillator.stop(audioContext.currentTime + 0.1);
        } catch (e) {}
    }

    // ============================================
    // FORMATTING
    // ============================================
    function formatNumber(number, decimals = 0) {
        if (typeof number !== 'number') number = parseFloat(number) || 0;
        return new Intl.NumberFormat('en-NG', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        }).format(number);
    }

    function formatCurrency(amount) {
        if (typeof amount !== 'number') amount = parseFloat(amount) || 0;
        return '₦' + formatNumber(amount, 2);
    }

    function formatQuantity(qty, isUnitMode = false) {
        if (typeof qty !== 'number') qty = parseFloat(qty) || 0;
        return isUnitMode ? formatNumber(qty, 2) : formatNumber(qty, 0);
    }

    // ============================================
    // VIEW MODE MANAGEMENT
    // ============================================

    function applyViewMode(mode) {
        currentViewMode = mode;
        localStorage.setItem('posViewMode', mode);

        if (mode === 'grid') {
            listViewTable.style.display     = 'none';
            gridViewContainer.style.display = 'block';
            document.getElementById('categoryFilterBar').style.display = 'block';
            document.getElementById('viewModeGrid').checked = true;
        } else {
            listViewTable.style.display     = '';
            gridViewContainer.style.display = 'none';
            document.getElementById('categoryFilterBar').style.display = 'none';
            document.getElementById('viewModeList').checked = true;
        }
        renderAll();
    }

    // Restore saved view on load
    applyViewMode(currentViewMode);

    // Toggle listeners
    document.getElementById('viewModeList').addEventListener('change', function () {
        if (this.checked) applyViewMode('list');
    });
    document.getElementById('viewModeGrid').addEventListener('change', function () {
        if (this.checked) applyViewMode('grid');
    });

    // ============================================
    // CATEGORY TABS (GRID MODE)
    // ============================================

    function buildCategoryTabs() {
        // Collect unique categories from allSearchedProducts
        const cats = ['all', ...new Set(allSearchedProducts.map(p => p.category || p.cat || '').filter(Boolean))];
        if (cats.length <= 1) {
            document.getElementById('categoryFilterBar').style.display = 'none';
            return;
        }
        if (currentViewMode === 'grid') {
            document.getElementById('categoryFilterBar').style.display = 'block';
        }
        const container = document.getElementById('categoryTabs');
        container.innerHTML = '';
        cats.forEach(cat => {
            const span = document.createElement('span');
            span.className = 'badge rounded-pill category-tab' + (cat === activeCategoryFilter ? ' active-cat-tab' : '');
            span.dataset.cat   = cat;
            span.textContent   = cat === 'all' ? 'All' : cat.charAt(0).toUpperCase() + cat.slice(1);
            span.style.cssText = 'cursor:pointer;font-size:.85rem;padding:.45em .9em;';
            if (cat === activeCategoryFilter) {
                span.classList.add('bg-primary');
            } else {
                span.classList.add('bg-secondary');
                span.classList.add('bg-opacity-50');
            }
            span.addEventListener('click', function () {
                activeCategoryFilter = this.dataset.cat;
                buildCategoryTabs();
                renderGridView();
            });
            container.appendChild(span);
        });
    }

    // ============================================
    // RENDER DISPATCHER
    // ============================================

    function renderAll() {
        if (currentViewMode === 'grid') {
            buildCategoryTabs();
            renderGridView();
        } else {
            renderListView();
        }
    }

    // ============================================
    // LIST VIEW (original logic, unchanged)
    // ============================================

    function renderListView() {
        if (allSearchedProducts.length === 0) {
            resultsBody.innerHTML = emptySearchRow.outerHTML;
            return;
        }
        resultsBody.innerHTML = '';
        emptySearchRow.style.display = 'none';
        [...allSearchedProducts].reverse().forEach(product => renderProductRow(product));
    }

    function renderProductRow(product) {
        const price          = product.sale_price || product.price;
        const unit           = product.primary_unit || 'Unit';
        const cartItem       = cart.find(i => i.product_id === product.id);
        const addedQty       = cartItem ? cartItem.qty : 0;
        const cachedQty      = productQuantityCache[product.id] || 1;
        const displayQty     = addedQty > 0 ? formatQuantity(addedQty, cartItem?.is_unit_mode) : (cachedQty > 1 ? `(${formatQuantity(cachedQty)})` : '');
        const isOutOfStock   = product.stock <= 0;
        const btnClass       = addedQty > 0 ? 'btn-success' : (isOutOfStock ? 'btn-secondary' : 'btn-outline-primary');
        const btnText        = addedQty > 0 ? `Added ${formatQuantity(addedQty, cartItem?.is_unit_mode)}` : (isOutOfStock ? 'Out of Stock' : `Set Qty ${displayQty}`);
        const btnDisabled    = isOutOfStock ? 'disabled' : '';
        const savedPref      = getSavedUnitPreference(product.id);
        const unitBadgeExtra = savedPref ? ` <i class="bi bi-star-fill text-warning" title="Preferred unit: ${savedPref.shortName}"></i>` : '';

        const row = document.createElement('tr');
        row.dataset.productId = product.id;
        row.className = addedQty > 0 ? 'selected-product-row table-success' : '';
        row.innerHTML = `
            <td>
                <div class="d-flex align-items-center">
                    ${product.thumbnail
                        ? `<img src="${product.thumbnail}" width="50" class="rounded me-3" alt="">`
                        : '<div class="bg-light rounded me-3 d-flex align-items-center justify-content-center" style="width:50px;height:50px;"><i class="bi bi-image"></i></div>'}
                    <div>
                        <strong>${product.title}</strong><br>
                        <small class="text-muted d-flex align-items-center flex-wrap gap-2">
                            <span class="badge bg-secondary"><i class="bi bi-upc-scan me-1"></i>SKU: ${product.sku}</span>
                            <span class="badge bg-info text-dark"><i class="bi bi-barcode me-1"></i>Barcode: ${product.barcode}</span>
                        </small>
                    </div>
                </div>
            </td>
            <td class="text-center">
                <span class="badge bg-${product.stock > 10 ? 'success' : product.stock > 0 ? 'warning' : 'danger'}">
                    ${formatNumber(product.stock, 0)}
                </span>
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
            <td class="text-end fw-bold">${formatCurrency(price)}</td>
            <td class="text-center">
                <span class="badge bg-primary">${unit}${unitBadgeExtra}</span>
            </td>
        `;
        resultsBody.appendChild(row);
        row.querySelector('.qty-btn').addEventListener('click', function () {
            if (!this.disabled) openQuantityModal(this);
        });
        row.querySelector('.remove-from-search-btn').addEventListener('click', function () {
            removeProductFromSearchAndCart(this.dataset.productId);
        });
    }

    // ============================================
    // GRID VIEW
    // ============================================

    function renderGridView() {
        const filtered = allSearchedProducts.filter(p => {
            if (activeCategoryFilter === 'all') return true;
            return (p.category || p.cat || '') === activeCategoryFilter;
        });

        gridViewBody.innerHTML = '';

        if (allSearchedProducts.length === 0) {
            emptyGridRow.style.display = 'block';
            return;
        }
        emptyGridRow.style.display = 'none';

        if (filtered.length === 0) {
            gridViewBody.innerHTML = `
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-filter display-4 mb-3 text-light"></i>
                    <p>No products in this category</p>
                </div>`;
            return;
        }

        [...filtered].reverse().forEach(product => {
            const price      = product.sale_price || product.price;
            const cartItem   = cart.find(i => i.product_id === product.id);
            const addedQty   = cartItem ? cartItem.qty : 0;
            const isOut      = product.stock <= 0;
            const savedPref  = getSavedUnitPreference(product.id);
            const stockBg    = product.stock > 10 ? 'success' : product.stock > 0 ? 'warning' : 'danger';

            const card = document.createElement('div');
            card.className   = 'pos-grid-card' + (isOut ? ' pos-grid-outstock' : '') + (addedQty > 0 ? ' pos-grid-in-cart' : '');
            card.dataset.productId = product.id;
            card.setAttribute('role', 'button');
            card.setAttribute('tabindex', isOut ? '-1' : '0');
            card.setAttribute('aria-label', `${product.title}, ${formatCurrency(price)}${isOut ? ', out of stock' : ''}`);
            card.innerHTML = `
                ${addedQty > 0 ? `<div class="pos-grid-cart-badge"><i class="bi bi-check2 me-1"></i>${formatQuantity(addedQty, cartItem?.is_unit_mode)}</div>` : ''}
                <span class="pos-grid-stock-badge badge bg-${stockBg}">${product.stock > 0 ? formatNumber(product.stock) : 'Out'}</span>
                <div class="pos-grid-thumb">
                    ${product.thumbnail
                        ? `<img src="${product.thumbnail}" alt="${product.title}">`
                        : `<i class="bi bi-box-seam"></i>`}
                </div>
                <div class="pos-grid-name" title="${product.title}">${product.title}</div>
                <div class="pos-grid-price">${formatCurrency(price)}</div>
                <div class="pos-grid-sku">${product.sku}</div>
                ${savedPref ? `<div class="pos-grid-unit-pref"><i class="bi bi-star-fill text-warning"></i> ${savedPref.shortName}</div>` : ''}
            `;

            if (!isOut) {
                const handleAdd = () => {
                    const btn = document.createElement('button');
                    btn.dataset.product   = JSON.stringify(product).replace(/'/g, "&apos;");
                    btn.dataset.productId = product.id;
                    openQuantityModal(btn);
                };
                card.addEventListener('click', handleAdd);
                card.addEventListener('keydown', e => { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); handleAdd(); } });
            }

            // Right-click / long-press: remove from grid
            card.addEventListener('contextmenu', function (e) {
                e.preventDefault();
                removeProductFromSearchAndCart(product.id);
            });

            gridViewBody.appendChild(card);
        });
    }

    // ============================================
    // INIT
    // ============================================
    function initializeApp() {
        quantityModal      = new bootstrap.Modal(document.getElementById('quantityModal'));
        quickCustomerModal = new bootstrap.Modal(document.getElementById('quickCustomerModal'));

        thankYouAudio         = new Audio('/audio/thank-you-sweet-man-235977.mp3');
        thankYouAudio.preload = 'auto';

        setupQuantityModal();
        initializeCustomerSearch();
        initializeQuickCustomerModal();
        loadUnitPreferences();

        document.addEventListener('click', function (e) {
            if (
                !e.target.closest('.modal') &&
                !e.target.closest('#customerSelect') &&
                !e.target.closest('#discountValue') &&
                !e.target.closest('.pos-grid-card') &&
                !e.target.closest('#viewToggleGroup') &&
                !e.target.closest('#categoryTabs') &&
                e.target.id !== 'barcodeInput'
            ) {
                input.focus();
                input.select();
            }
        });
    }

    // ============================================
    // QUANTITY MODAL
    // ============================================
    function setupQuantityModal() {
        const quantityModalElement = document.getElementById('quantityModal');

        quantityModalElement.addEventListener('show.bs.modal', function () {
            switchToQuantityMode();
        });

        quantityModalElement.addEventListener('shown.bs.modal', () => {
            setTimeout(() => {
                if (currentMeasurementType === 'quantity') {
                    modalQty.focus(); modalQty.select();
                } else {
                    if (availableUnits.length > 0 && !selectedUnit) unitSelect.focus();
                    else if (selectedUnit) { modalUnit.focus(); modalUnit.select(); }
                }
            }, 100);
        });

        quantityModalElement.addEventListener('hidden.bs.modal', () => {
            setTimeout(() => { input.focus(); input.select(); }, 100);
        });

        document.getElementById('measureQuantity').addEventListener('click', function () {
            if (this.checked) switchToQuantityMode();
        });
        document.getElementById('measureUnit').addEventListener('click', function () {
            if (this.checked) switchToUnitMode();
        });

        document.querySelectorAll('input[name="measurementType"]').forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.value === 'quantity') switchToQuantityMode();
                else switchToUnitMode();
                updateModalTotal();
            });
        });

        unitSelect.addEventListener('change', function () {
            selectedUnit = availableUnits.find(u => u.id == this.value);
            if (selectedUnit) {
                modalUnit.disabled = false;
                document.getElementById('decreaseUnit').disabled = false;
                document.getElementById('increaseUnit').disabled = false;
                pricePerUnitInput.disabled = false;
                modalUnit.focus(); modalUnit.select();
                updatePriceUnitLabel();
                updateModalLabels();
                updateModalTotal();
                updateAmountDisplay();
                checkSavedUnitPreference();
            } else {
                modalUnit.disabled = true;
                document.getElementById('decreaseUnit').disabled = true;
                document.getElementById('increaseUnit').disabled = true;
                pricePerUnitInput.disabled = true;
                modalUnit.value = 1;
                pricePerUnitInput.value = '';
                updateModalLabels();
                updateModalTotal();
                updateAmountDisplay();
            }
        });

        // Two-way binding
        modalQty.addEventListener('input', function () {
            const quantity   = parseInt(this.value) || 1;
            const totalPrice = quantity * originalPricePerUnit;
            if (!isPriceInputActive) pricePerUnitInput.value = totalPrice.toFixed(2);
            updateModalTotal(); updateAmountDisplay();
        });
        modalQty.addEventListener('change', updateModalTotal);

        modalUnit.addEventListener('input', function () {
            if (selectedUnit) {
                const unitAmount = parseFloat(this.value) || 1;
                const totalPrice = unitAmount * originalPricePerUnit;
                if (!isPriceInputActive) pricePerUnitInput.value = totalPrice.toFixed(2);
                updateModalTotal(); updateAmountDisplay();
            }
        });
        modalUnit.addEventListener('change', updateModalTotal);

        pricePerUnitInput.addEventListener('focus', () => { isPriceInputActive = true; });
        pricePerUnitInput.addEventListener('blur',  () => { isPriceInputActive = false; });

        pricePerUnitInput.addEventListener('input', function () {
            if (selectedUnit) {
                const totalPrice = parseFloat(this.value) || 0;
                if (originalPricePerUnit > 0 && totalPrice > 0) {
                    const equivalentAmount = totalPrice / originalPricePerUnit;
                    if (currentMeasurementType === 'quantity') modalQty.value  = Math.round(equivalentAmount);
                    else modalUnit.value = equivalentAmount.toFixed(3);
                    updateModalTotal(); updateAmountDisplay();
                }
            }
        });

        pricePerUnitInput.addEventListener('blur', function () {
            const totalPrice = parseFloat(this.value) || 0;
            if (totalPrice === 0 && currentProduct) {
                this.value = originalPricePerUnit.toFixed(2);
                if (currentMeasurementType === 'quantity') modalQty.value = 1;
                else if (selectedUnit) modalUnit.value = 1;
                updateModalTotal(); updateAmountDisplay();
                showToast('Price reset to original', 'info', 1000);
            }
        });

        document.getElementById('increaseQty').addEventListener('click', () => {
            modalQty.value = (parseInt(modalQty.value) || 1) + 1;
            pricePerUnitInput.value = (parseInt(modalQty.value) * originalPricePerUnit).toFixed(2);
            updateModalTotal(); updateAmountDisplay();
            modalQty.focus(); modalQty.select();
        });
        document.getElementById('decreaseQty').addEventListener('click', () => {
            const val = parseInt(modalQty.value) || 1;
            if (val > 1) {
                modalQty.value = val - 1;
                pricePerUnitInput.value = (parseInt(modalQty.value) * originalPricePerUnit).toFixed(2);
                updateModalTotal(); updateAmountDisplay();
                modalQty.focus(); modalQty.select();
            }
        });
        document.getElementById('increaseUnit').addEventListener('click', () => {
            if (selectedUnit) {
                modalUnit.value = (parseFloat(modalUnit.value) + getUnitStep()).toFixed(3);
                pricePerUnitInput.value = (parseFloat(modalUnit.value) * originalPricePerUnit).toFixed(2);
                updateModalTotal(); updateAmountDisplay();
                modalUnit.focus(); modalUnit.select();
            }
        });
        document.getElementById('decreaseUnit').addEventListener('click', () => {
            if (selectedUnit) {
                const current = parseFloat(modalUnit.value) || 1;
                if (current > getUnitStep()) {
                    modalUnit.value = (current - getUnitStep()).toFixed(3);
                    pricePerUnitInput.value = (parseFloat(modalUnit.value) * originalPricePerUnit).toFixed(2);
                    updateModalTotal(); updateAmountDisplay();
                    modalUnit.focus(); modalUnit.select();
                }
            }
        });

        document.querySelectorAll('.quick-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                if (currentMeasurementType === 'quantity') {
                    modalQty.value = this.dataset.value;
                    pricePerUnitInput.value = (parseInt(modalQty.value) * originalPricePerUnit).toFixed(2);
                    modalQty.focus(); modalQty.select();
                } else if (selectedUnit) {
                    modalUnit.value = this.dataset.value;
                    pricePerUnitInput.value = (parseFloat(modalUnit.value) * originalPricePerUnit).toFixed(2);
                    modalUnit.focus(); modalUnit.select();
                } else {
                    showToast('Please select a unit first', 'warning');
                }
                updateModalTotal(); updateAmountDisplay();
            });
        });

        document.querySelectorAll('.unit-quick-btn').forEach(btn => {
            btn.addEventListener('click', function () {
                if (selectedUnit) {
                    modalUnit.value = this.dataset.value;
                    pricePerUnitInput.value = (parseFloat(modalUnit.value) * originalPricePerUnit).toFixed(2);
                    updateModalTotal(); updateAmountDisplay();
                    modalUnit.focus(); modalUnit.select();
                } else {
                    showToast('Please select a unit first', 'warning');
                }
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
        const totalPriceLabel = document.getElementById('totalPriceLabel');
        totalPriceLabel.textContent = (currentMeasurementType === 'unit' && selectedUnit)
            ? `Total Price (per ${selectedUnit.short_name})`
            : 'Total Price';
    }

    function updatePriceUnitLabel() {
        const priceUnitLabel = document.getElementById('priceUnitLabel');
        if (priceUnitLabel) {
            priceUnitLabel.textContent = selectedUnit ? selectedUnit.short_name : 'Unit';
            priceUnitLabel.className   = selectedUnit ? 'text-primary fw-bold' : '';
        }
        document.getElementById('unitDisplay').textContent = selectedUnit ? selectedUnit.short_name : 'unit';
        updateModalLabels();
    }

    function updateAmountDisplay() {
        if (currentMeasurementType === 'quantity') {
            document.getElementById('amountDisplay').textContent = formatQuantity(parseInt(modalQty.value) || 1);
            document.getElementById('unitDisplay').textContent   = 'unit(s)';
        } else {
            document.getElementById('amountDisplay').textContent = formatNumber(parseFloat(modalUnit.value) || 1, 2);
            document.getElementById('unitDisplay').textContent   = selectedUnit ? selectedUnit.short_name : 'unit';
        }
    }

    function switchToQuantityMode() {
        currentMeasurementType = 'quantity';
        document.getElementById('measurementLabel').textContent       = 'Enter Quantity';
        document.getElementById('quantityInputSection').style.display = 'block';
        document.getElementById('unitInputSection').style.display     = 'none';
        document.getElementById('unitSelection').style.display        = 'none';
        document.getElementById('unitQuickButtons').style.display     = 'none';
        document.getElementById('priceInputSection').style.display    = 'none';
        document.getElementById('quantityQuickButtons').style.display = 'block';
        modalQty.disabled = false;
        document.getElementById('decreaseQty').disabled = false;
        document.getElementById('increaseQty').disabled = false;
        if (currentProduct) {
            const price    = currentProduct.sale_price || currentProduct.price;
            const quantity = parseInt(modalQty.value) || 1;
            pricePerUnitInput.value  = (price * quantity).toFixed(2);
            originalPricePerUnit     = price;
            document.getElementById('originalPriceText').textContent = `Per unit: ${formatCurrency(price)}`;
            document.getElementById('originalPriceText').className   = 'text-muted fw-semibold';
        }
        updateModalLabels(); updateModalTotal(); updateAmountDisplay();
    }

    function switchToUnitMode() {
        currentMeasurementType = 'unit';
        document.getElementById('measurementLabel').textContent       = 'Enter Amount';
        document.getElementById('quantityInputSection').style.display = 'none';
        document.getElementById('unitInputSection').style.display     = 'block';
        document.getElementById('unitSelection').style.display        = 'block';
        document.getElementById('unitQuickButtons').style.display     = 'block';
        document.getElementById('priceInputSection').style.display    = 'block';
        document.getElementById('quantityQuickButtons').style.display = 'none';
        loadProductUnits();
        modalUnit.disabled = true;
        document.getElementById('decreaseUnit').disabled = true;
        document.getElementById('increaseUnit').disabled = true;
        pricePerUnitInput.disabled = true;
        if (currentProduct) {
            const price      = currentProduct.sale_price || currentProduct.price;
            const unitAmount = parseFloat(modalUnit.value) || 1;
            pricePerUnitInput.value  = (price * unitAmount).toFixed(2);
            originalPricePerUnit     = price;
            const label = selectedUnit ? `Per ${selectedUnit.short_name}: ${formatCurrency(price)}` : `Per unit: ${formatCurrency(price)}`;
            document.getElementById('originalPriceText').textContent = label;
            document.getElementById('originalPriceText').className   = 'text-muted fw-semibold';
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
        if (!currentProduct || !currentProduct.id) return;
        try {
            const response = await axios.get(
                `{{ route('api.product.units', ['product' => '__ID__']) }}`.replace('__ID__', currentProduct.id)
            );
            availableUnits = response.data.units || [];
            unitSelect.innerHTML = '<option value="">-- Please select a unit --</option>';
            if (availableUnits.length > 0) {
                const savedPreference = getSavedUnitPreference(currentProduct.id);
                availableUnits.forEach(unit => {
                    const option       = document.createElement('option');
                    option.value       = unit.id;
                    option.textContent = `${unit.name} (${unit.short_name})`;
                    if (savedPreference && savedPreference.unitId == unit.id) {
                        option.selected = true; selectedUnit = unit;
                    } else if (unit.is_default && !selectedUnit) {
                        option.selected = true; selectedUnit = unit;
                    }
                    unitSelect.appendChild(option);
                });
                if (selectedUnit) {
                    modalUnit.disabled = false;
                    document.getElementById('decreaseUnit').disabled = false;
                    document.getElementById('increaseUnit').disabled = false;
                    pricePerUnitInput.disabled = false;
                }
                updatePriceUnitLabel(); updateAmountDisplay();
                if (savedPreference) document.getElementById('rememberUnitPreference').checked = true;
            } else {
                selectedUnit = { id: 1, name: currentProduct.primary_unit || 'Unit', short_name: currentProduct.primary_unit || 'unit', conversion_factor: 1, is_default: true };
                const option       = document.createElement('option');
                option.value       = selectedUnit.id;
                option.textContent = `${selectedUnit.name} (${selectedUnit.short_name})`;
                option.selected    = true;
                unitSelect.appendChild(option);
                modalUnit.disabled = false;
                document.getElementById('decreaseUnit').disabled = false;
                document.getElementById('increaseUnit').disabled = false;
                pricePerUnitInput.disabled = false;
                updatePriceUnitLabel();
            }
        } catch (error) {
            selectedUnit = { id: 1, name: currentProduct.primary_unit || 'Unit', short_name: currentProduct.primary_unit || 'unit', conversion_factor: 1, is_default: true };
            updatePriceUnitLabel();
        }
    }

    function checkSavedUnitPreference() {
        if (!currentProduct || !selectedUnit) return;
        const preferences       = JSON.parse(localStorage.getItem('unitPreferences') || '{}');
        const productPreference = preferences[currentProduct.id];
        if (productPreference && productPreference.unitId === selectedUnit.id) {
            document.getElementById('rememberUnitPreference').checked = true;
        }
    }

    function saveUnitPreference() {
        if (!currentProduct || !selectedUnit) return;
        const preferences = JSON.parse(localStorage.getItem('unitPreferences') || '{}');
        preferences[currentProduct.id] = { unitId: selectedUnit.id, unitName: selectedUnit.name, shortName: selectedUnit.short_name, timestamp: Date.now() };
        localStorage.setItem('unitPreferences', JSON.stringify(preferences));
        showToast('Unit preference saved for this product', 'success');
    }

    function getSavedUnitPreference(productId) {
        const preferences = JSON.parse(localStorage.getItem('unitPreferences') || '{}');
        return preferences[productId];
    }

    function loadUnitPreferences() { /* loaded on demand */ }

    // ============================================
    // EVENT LISTENERS
    // ============================================
    confirmAddBtn.addEventListener('click', addOrUpdateProductInCart);
    removeFromCartBtn.addEventListener('click', removeCurrentProductFromCart);
    document.getElementById('clearCart').addEventListener('click', clearCart);
    document.getElementById('holdOrderBtn').addEventListener('click', holdOrder);
    document.getElementById('loadHeldBtn').addEventListener('click', loadHeldOrders);
    document.getElementById('completeOrder').addEventListener('click', completeOrder);
    applyDiscountBtn.addEventListener('click', applyOrderDiscount);

    document.getElementById('quickCustomerBtn').addEventListener('click', function () {
        quickCustomerModal.show();
    });
    document.getElementById('saveQuickCustomerBtn').addEventListener('click', saveQuickCustomer);

    discountType.addEventListener('change', function () {
        orderDiscountType = this.value;
        if (orderDiscountType === 'percent' && orderDiscountValue > 100) {
            discountValue.value = 100; orderDiscountValue = 100;
        }
    });

    discountValue.addEventListener('input', function () {
        const val = parseFloat(this.value) || 0;
        if (orderDiscountType === 'percent' && val > 100) {
            this.value = 100; orderDiscountValue = 100;
        } else { orderDiscountValue = val; }
    });

    discountValue.addEventListener('click', function () { this.focus(); this.select(); });
    discountType.addEventListener('click',  function () { discountValue.focus(); discountValue.select(); });

    document.getElementById('applyItemDiscountBtn').addEventListener('click', function () {
        if (currentItemIndex === null) return;
        const value = parseFloat(document.getElementById('itemDiscountValue').value) || 0;
        const type  = document.getElementById('itemDiscountType').value;
        if (type === 'percent' && value > 100) { showToast('Percentage cannot exceed 100%', 'warning'); return; }
        cart[currentItemIndex].discount_type  = type;
        cart[currentItemIndex].discount_value = value;
        updateCart(); renderAll();
        bootstrap.Modal.getInstance(document.getElementById('itemDiscountModal')).hide();
        showToast('Item discount applied!', 'success');
    });

    // ============================================
    // AUDIO
    // ============================================
    function playThankYouSound() {
        if (thankYouAudio) {
            thankYouAudio.currentTime = 0;
            thankYouAudio.play().catch(() => showToast('Thank you for your purchase!', 'success', 3000));
        } else {
            showToast('Thank you for your purchase!', 'success', 3000);
        }
    }

    // ============================================
    // CUSTOMER FUNCTIONALITY
    // ============================================
    function initializeCustomerSearch() {
        const customerSelectElement = document.getElementById('customerSelect');
        const originalOptions       = Array.from(customerSelectElement.options);
        const customerContainer     = customerSelectElement.closest('.mb-4');

        if (customerContainer) {
            const searchBox = document.createElement('div');
            searchBox.className = 'mb-2';
            searchBox.innerHTML = `<input type="text" id="customerSearchInput" class="form-control form-control-sm" placeholder="Search customers... (Ctrl+F)">`;
            const label = customerContainer.querySelector('label');
            if (label) customerContainer.insertBefore(searchBox, label.nextElementSibling);

            const customerSearchInput = document.getElementById('customerSearchInput');
            customerSearchInput.addEventListener('input', function () {
                const searchTerm = this.value.toLowerCase().trim();
                if (searchTerm.length === 0) {
                    customerSelectElement.innerHTML = '';
                    originalOptions.forEach(option => customerSelectElement.appendChild(option.cloneNode(true)));
                    updateCustomerCount(originalOptions.length - 1);
                    return;
                }
                const filtered = originalOptions.filter(o => o.value === '' || o.text.toLowerCase().includes(searchTerm));
                customerSelectElement.innerHTML = '';
                filtered.forEach(o => customerSelectElement.appendChild(o.cloneNode(true)));
                updateCustomerCount(filtered.length - 1);
            });

            document.addEventListener('keydown', function (e) {
                if (e.ctrlKey && e.key === 'f') {
                    e.preventDefault();
                    customerSearchInput.focus(); customerSearchInput.select();
                    showToast('Customer search focused', 'info', 1000);
                }
            });
        }
    }

    function initializeQuickCustomerModal() {
        document.getElementById('quickCustomerForm').addEventListener('submit', function (e) {
            e.preventDefault(); saveQuickCustomer();
        });
        document.getElementById('quickCustomerModal').addEventListener('hidden.bs.modal', function () {
            document.getElementById('quickCustomerForm').reset();
        });
    }

    async function saveQuickCustomer() {
        const firstName   = document.getElementById('firstName').value.trim();
        const lastName    = document.getElementById('lastName').value.trim();
        const phoneNumber = document.getElementById('phoneNumber').value.trim();
        const email       = document.getElementById('email').value.trim();

        if (!firstName || !lastName) { showToast('First name and last name are required', 'warning'); return; }

        const saveBtn    = document.getElementById('saveQuickCustomerBtn');
        const origText   = saveBtn.innerHTML;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Saving...';
        saveBtn.disabled  = true;

        try {
            const response = await axios.post('{{ route("customers.quick") }}', {
                first_name: firstName, last_name: lastName,
                phone_number: phoneNumber, email: email,
                _token: '{{ csrf_token() }}'
            });
            if (response.data.success) {
                const customerSelectElement = document.getElementById('customerSelect');
                const option       = document.createElement('option');
                option.value       = response.data.customer.id;
                option.textContent = `${firstName} ${lastName}${phoneNumber ? ` - ${phoneNumber}` : ''}`;
                customerSelectElement.appendChild(option);
                customerSelectElement.value = response.data.customer.id;
                document.getElementById('customerCount').textContent = parseInt(document.getElementById('customerCount').textContent) + 1;
                quickCustomerModal.hide();
                document.getElementById('quickCustomerForm').reset();
                showToast('Customer added successfully', 'success');
            } else {
                showToast(response.data.message || 'Failed to add customer', 'error');
            }
        } catch (error) {
            let errorMessage = 'Failed to add customer';
            if (error.response?.data?.message)  errorMessage = error.response.data.message;
            else if (error.response?.data?.errors) errorMessage = Object.values(error.response.data.errors).flat().join(', ');
            showToast(errorMessage, 'error');
        } finally {
            saveBtn.innerHTML = origText;
            saveBtn.disabled  = false;
        }
    }

    function updateCustomerCount(count) {
        document.getElementById('customerCount').textContent = count;
    }

    // ============================================
    // SEARCH
    // ============================================
    function showEmptyState() {
        searchLoading.classList.add('d-none');
        if (allSearchedProducts.length > 0) renderAll();
        else {
            if (currentViewMode === 'list') {
                emptySearchRow.style.display = '';
                resultsBody.innerHTML = emptySearchRow.outerHTML;
            } else {
                gridViewBody.innerHTML = '';
                emptyGridRow.style.display = 'block';
            }
        }
    }

    function showSearchLoading() { searchLoading.classList.remove('d-none'); emptySearchRow.style.display = 'none'; }
    function hideSearchLoading() { searchLoading.classList.add('d-none'); }

    function searchProducts(query) {
        if (!query) return;
        showSearchLoading();
        axios.get('{{ route("pos.search") }}', { params: { q: query } })
            .then(res => {
                hideSearchLoading();
                const newProducts = res.data || [];
                newProducts.forEach(np => {
                    const idx = allSearchedProducts.findIndex(p => p.id === np.id);
                    if (idx === -1) allSearchedProducts.push(np);
                    else allSearchedProducts[idx] = np;
                });
                renderAll();
            })
            .catch(err => {
                hideSearchLoading();
                showToast('Failed to search products', 'error');
            });
    }

    function clearAllUnselectedItems() {
        const unselected = allSearchedProducts.filter(p => !cart.some(i => i.product_id === p.id));
        if (unselected.length === 0) return;
        allSearchedProducts = allSearchedProducts.filter(p => cart.some(i => i.product_id === p.id));
        if (allSearchedProducts.length === 0) {
            if (currentViewMode === 'list') resultsBody.innerHTML = emptySearchRow.outerHTML;
            else { gridViewBody.innerHTML = ''; emptyGridRow.style.display = 'block'; }
        } else {
            renderAll();
        }
        showToast(`Cleared ${unselected.length} unselected items`, 'info', 1500);
    }

    function removeProductFromSearchAndCart(productId) {
        allSearchedProducts = allSearchedProducts.filter(p => p.id != productId);
        const wasInCart     = cart.some(i => i.product_id == productId);
        if (wasInCart) {
            cart = cart.filter(i => i.product_id != productId);
            delete productQuantityCache[productId];
            updateCart();
        }
        if (allSearchedProducts.length === 0) {
            if (currentViewMode === 'list') resultsBody.innerHTML = emptySearchRow.outerHTML;
            else { gridViewBody.innerHTML = ''; emptyGridRow.style.display = 'block'; }
        } else {
            renderAll();
        }
        showToast('Product removed from list', 'success');
    }

    function openQuantityModal(button) {
        try {
            const product   = JSON.parse(button.dataset.product);
            const productId = button.dataset.productId;
            currentProduct  = product;

            const productIndex = allSearchedProducts.findIndex(p => p.id === productId);
            if (productIndex !== -1) {
                const [moved] = allSearchedProducts.splice(productIndex, 1);
                allSearchedProducts.push(moved);
            }

            const cartItem    = cart.find(i => i.product_id === productId);
            const cachedQty   = productQuantityCache[productId] || 1;
            const previousQty = cartItem ? cartItem.qty : cachedQty;

            document.getElementById('modalProductLabel').textContent = product.title;
            const price = product.sale_price || product.price;
            document.getElementById('modalProductPrice').textContent = `${formatCurrency(price)}`;
            document.getElementById('modalProductStock').textContent = `Stock: ${formatNumber(product.stock, 0)}`;
            document.getElementById('modalProductUnit').textContent  = product.primary_unit || 'Unit';
            document.getElementById('modalProductSku').innerHTML     = `<span class="badge bg-secondary"><i class="bi bi-upc-scan me-1"></i>SKU: ${product.sku}</span>`;
            document.getElementById('modalProductBarcode').innerHTML = `<span class="badge bg-info text-dark"><i class="bi bi-barcode me-1"></i>Barcode: ${product.barcode}</span>`;

            if (product.units && product.units.length > 0) {
                document.getElementById('measureUnit').disabled = false;
                document.getElementById('measureUnit').parentElement.classList.remove('disabled');
            } else {
                document.getElementById('measureUnit').disabled = true;
                document.getElementById('measureUnit').parentElement.classList.add('disabled');
            }

            const savedPreference = getSavedUnitPreference(productId);
            if (savedPreference) {
                document.getElementById('measureUnit').checked = true;
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

            document.getElementById('previousQtyText').textContent = cartItem
                ? `In cart: ${formatQuantity(previousQty, cartItem.is_unit_mode)} ${cartItem.is_unit_mode ? cartItem.unit_short_name || '' : ''}`
                : `Previous: ${formatQuantity(previousQty)}`;
            document.getElementById('previousQtyText').className = cartItem ? 'text-success fw-semibold' : 'text-muted';

            removeFromCartBtn.style.display = cartItem ? 'inline-block' : 'none';
            originalPricePerUnit = price;
            isPriceInputActive   = false;

            updateModalTotal(); updateAmountDisplay();
            quantityModal.show();
        } catch (e) {
            console.error('Error opening quantity modal:', e);
            showToast('Error loading product details', 'error');
        }
    }

    function updateModalTotal() {
        if (!currentProduct) return;
        const unitPrice = parseFloat(pricePerUnitInput.value) || 0;
        document.getElementById('totalPriceDisplay').textContent = `Total: ${formatCurrency(unitPrice)}`;
        const amount          = currentMeasurementType === 'quantity' ? parseInt(modalQty.value) || 1 : parseFloat(modalUnit.value) || 1;
        const calculatedTotal = originalPricePerUnit * amount;
        document.getElementById('calculatedPriceDisplay').textContent =
            `Original price: ${formatCurrency(originalPricePerUnit)} × ${formatQuantity(amount, currentMeasurementType === 'unit')} = ${formatCurrency(calculatedTotal)}`;
    }

    function addOrUpdateProductInCart() {
        if (!currentProduct) return;
        if (currentProduct.stock <= 0) {
            showToast(`${currentProduct.title} is out of stock`, 'error');
            quantityModal.hide(); return;
        }
        if (currentMeasurementType === 'unit' && !selectedUnit) {
            showToast('Please select a unit first', 'warning');
            unitSelect.focus(); return;
        }

        let quantity      = 0;
        let unitId        = currentProduct.primary_unit_id || 1;
        let unitName      = currentProduct.primary_unit || 'Unit';
        let unitShortName = unitName;
        const isUnitMode  = currentMeasurementType === 'unit';
        const totalPrice  = parseFloat(pricePerUnitInput.value) || 0;

        if (isUnitMode) {
            const unitAmount = parseFloat(modalUnit.value) || 0.001;
            if (unitAmount <= 0) { showToast('Unit amount must be greater than 0', 'warning'); return; }
            if (unitAmount > currentProduct.stock) {
                showToast(`Only ${formatNumber(currentProduct.stock, 3)} ${selectedUnit.short_name} available`, 'warning');
                modalUnit.value = currentProduct.stock.toFixed(3);
                updateModalTotal(); return;
            }
            quantity      = unitAmount;
            unitId        = selectedUnit.id;
            unitName      = selectedUnit.name;
            unitShortName = selectedUnit.short_name;
        } else {
            quantity = parseInt(modalQty.value) || 1;
            if (quantity < 1) { showToast('Quantity must be at least 1', 'warning'); return; }
            if (quantity > currentProduct.stock) {
                showToast(`Only ${formatNumber(currentProduct.stock, 0)} units available`, 'warning');
                modalQty.value = currentProduct.stock;
                updateModalTotal(); return;
            }
        }

        const p         = currentProduct;
        const unitPrice = originalPricePerUnit;

        if (!allSearchedProducts.some(sp => sp.id === p.id)) allSearchedProducts.push({...p});

        const existing = cart.find(i => i.product_id === p.id);
        const cartData = {
            product_id:      p.id,
            title:           p.title,
            price:           totalPrice,
            unit_price:      unitPrice,
            qty:             quantity,
            unit_name:       unitName,
            unit_short_name: unitShortName,
            unit_id:         parseInt(unitId),
            sku:             p.sku,
            barcode:         p.barcode,
            thumbnail:       p.thumbnail,
            discount_type:   'percent',
            discount_value:  0,
            discounted_price: unitPrice,
            is_unit_mode:    isUnitMode,
            original_unit:   isUnitMode ? selectedUnit : null,
            price_per_unit:  unitPrice,
        };

        if (existing) Object.assign(existing, cartData);
        else cart.push(cartData);

        productQuantityCache[p.id] = quantity;
        quantityModal.hide();
        updateCart();
        renderAll();
        currentProduct = null;
        showToast('Product added to cart', 'success');
    }

    function removeCurrentProductFromCart() {
        if (!currentProduct) return;
        Swal.fire({
            title: 'Remove from Cart?',
            text:  `Remove ${currentProduct.title} from cart?`,
            icon:  'warning',
            showCancelButton:  true,
            confirmButtonText: 'Yes, remove',
        }).then(res => {
            if (res.isConfirmed) {
                const id = currentProduct.id;
                cart = cart.filter(i => i.product_id != id);
                delete productQuantityCache[id];
                quantityModal.hide();
                updateCart(); renderAll();
                showToast('Product removed from cart', 'success');
            }
        });
    }

    // ============================================
    // CART
    // ============================================
    function updateCart() {
        if (cart.length === 0) {
            emptyCartRow.style.display = '';
            cartBody.innerHTML = '';
            subtotalEl.textContent                            = formatCurrency(0);
            discountEl.textContent                            = formatCurrency(0);
            grandTotalEl.textContent                          = formatCurrency(0);
            document.getElementById('taxAmount').textContent = formatCurrency(0);
            renderAll();
            return;
        }

        emptyCartRow.style.display = 'none';
        cartBody.innerHTML = '';
        let subtotal = 0;

        cart.forEach((item, i) => {
            let unitPrice         = item.price_per_unit || (item.price / item.qty);
            let discountedUnitPrice = unitPrice;

            if (item.discount_value > 0) {
                discountedUnitPrice = item.discount_type === 'percent'
                    ? unitPrice * (1 - item.discount_value / 100)
                    : unitPrice - (item.discount_value / item.qty);
                if (discountedUnitPrice < 0) discountedUnitPrice = 0;
                item.discounted_price = discountedUnitPrice;
            } else {
                item.discounted_price = unitPrice;
            }

            const total      = discountedUnitPrice * item.qty;
            subtotal        += total;
            const displayUnit = item.unit_short_name || item.unit_name || 'Unit';

            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="align-middle">${i + 1}</td>
                <td class="align-middle">
                    <div class="d-flex align-items-center">
                        ${item.thumbnail
                            ? `<img src="${item.thumbnail}" width="40" class="rounded me-2" alt="">`
                            : '<div class="bg-light rounded me-2 d-flex align-items-center justify-content-center" style="width:40px;height:40px;"><i class="bi bi-image"></i></div>'}
                        <div>
                            <strong>${item.title}</strong><br>
                            <small class="text-muted d-flex align-items-center flex-wrap gap-1">
                                <span class="badge bg-secondary"><i class="bi bi-upc-scan me-1"></i>${item.sku}</span>
                                <span class="badge bg-info text-dark"><i class="bi bi-barcode me-1"></i>${item.barcode || 'N/A'}</span>
                            </small>
                            ${item.discount_value > 0 ? `<small class="text-warning d-block">-${formatNumber(item.discount_value, 2)}${item.discount_type === 'percent' ? '%' : '₦'} discount</small>` : ''}
                        </div>
                    </div>
                </td>
                <td class="text-center align-middle">
                    <button class="btn btn-sm ${item.is_unit_mode ? 'btn-success' : 'btn-primary'} qty-btn-cart"
                            data-product='${JSON.stringify(item).replace(/'/g, "&apos;")}'
                            data-product-id="${item.product_id}">
                        ${formatQuantity(item.qty, true)} ${displayUnit}
                        ${item.is_unit_mode ? '<i class="bi bi-scale ms-1"></i>' : ''}
                    </button>
                </td>
                <td class="text-end align-middle">${formatCurrency(discountedUnitPrice)}</td>
                <td class="text-end align-middle fw-bold">${formatCurrency(total)}</td>
                <td class="text-center align-middle">
                    <button class="btn btn-sm btn-outline-warning rounded-circle item-discount-btn p-0"
                            data-product-id="${item.product_id}" title="Apply item discount"
                            style="width: 28px; height: 28px;">
                        <i class="bi bi-percent"></i>
                    </button>
                </td>
                <td class="text-center align-items-center">
                    <button class="btn btn-sm btn-danger rounded-circle remove-cart-item-btn p-0"
                            data-index="${i}" title="Remove item"
                            style="width: 28px; height: 28px;">
                        <i class="bi bi-x"></i>
                    </button>
                </td>
            `;
            cartBody.appendChild(row);

            row.querySelector('.remove-cart-item-btn').addEventListener('click', function () {
                removeCartItem(parseInt(this.dataset.index));
            });
            row.querySelector('.item-discount-btn').addEventListener('click', function () {
                const pid = this.dataset.productId;
                currentItemIndex = cart.findIndex(item => item.product_id === pid);
                if (currentItemIndex === -1) return;
                const ci = cart[currentItemIndex];
                document.getElementById('itemName').textContent        = ci.title;
                document.getElementById('itemDiscountValue').value     = ci.discount_value || 0;
                document.getElementById('itemDiscountType').value      = ci.discount_type || 'percent';
                new bootstrap.Modal(document.getElementById('itemDiscountModal')).show();
            });
            row.querySelector('.qty-btn-cart').addEventListener('click', function () {
                openQuantityModal(this);
            });
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

        subtotalEl.textContent                              = formatCurrency(subtotal);
        document.getElementById('taxAmount').textContent   = formatCurrency(taxAmount);
        discountEl.textContent                             = `-${formatCurrency(orderDiscountAmount)}`;
        grandTotalEl.textContent                           = formatCurrency(grandTotal);
        document.getElementById('discountRow').style.display = orderDiscountValue > 0 ? 'flex' : 'none';

        window.currentDiscount = { type: orderDiscountType, value: orderDiscountValue, amount: orderDiscountAmount };
    }

    function removeCartItem(index) {
        const item = cart[index];
        cart.splice(index, 1);
        delete productQuantityCache[item.product_id];
        updateCart(); renderAll();
        showToast('Item removed from cart', 'success');
    }

    function applyOrderDiscount() {
        orderDiscountValue = parseFloat(discountValue.value) || 0;
        orderDiscountType  = discountType.value;
        if (orderDiscountType === 'percent' && orderDiscountValue > 100) {
            showToast('Percentage discount cannot exceed 100%', 'warning');
            orderDiscountValue = 100; discountValue.value = 100;
        }
        document.getElementById('discountRow').style.display = orderDiscountValue > 0 ? 'flex' : 'none';
        updateCart();
        showToast('Order discount applied!', 'success');
    }

    function clearCart() {
        if (cart.length === 0) { showToast('Cart is already empty', 'info'); return; }
        Swal.fire({
            title: 'Clear Cart?',
            text:  'This will remove all items from your cart.',
            icon:  'warning',
            showCancelButton:  true,
            confirmButtonText: 'Yes, clear cart',
        }).then(res => {
            if (res.isConfirmed) {
                cart = []; productQuantityCache = {};
                updateCart(); renderAll();
                showToast('Cart cleared', 'success');
            }
        });
    }

    function holdOrder() {
        if (cart.length === 0) { showToast('Cart is empty', 'info'); return; }
        const heldOrders = JSON.parse(localStorage.getItem('heldOrders') || '[]');
        heldOrders.push({
            id: Date.now(),
            cart: JSON.parse(JSON.stringify(cart)),
            customer: customerSelect.value,
            allSearchedProducts: JSON.parse(JSON.stringify(allSearchedProducts)),
            productQuantityCache: JSON.parse(JSON.stringify(productQuantityCache)),
            discount: { type: orderDiscountType, value: orderDiscountValue },
            time: new Date().toLocaleString(),
            timestamp: Date.now(),
        });
        localStorage.setItem('heldOrders', JSON.stringify(heldOrders));
        cart = []; productQuantityCache = {}; orderDiscountValue = 0; discountValue.value = '0';
        updateCart(); renderAll();
        showToast('Order held successfully!', 'success');
    }

    function loadHeldOrders() {
        const heldOrders = JSON.parse(localStorage.getItem('heldOrders') || '[]');
        const list       = document.getElementById('heldOrdersList');
        const noOrders   = document.getElementById('noHeldOrders');

        if (heldOrders.length === 0) {
            list.innerHTML = ''; noOrders.style.display = 'block';
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
                    return sum + price;
                }, 0);
                html += `
                    <div class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${order.time}</h6>
                                <p class="mb-1 text-muted">${items} item${items > 1 ? 's' : ''} - ${formatCurrency(total)}</p>
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

            document.querySelectorAll('.load-order-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const orderId   = this.dataset.orderId;
                    const allOrders = JSON.parse(localStorage.getItem('heldOrders') || '[]');
                    const order     = allOrders.find(o => o.id == orderId);
                    if (order) {
                        if (cart.length > 0) {
                            Swal.fire({
                                title: 'Replace Current Cart?',
                                text:  'Loading this order will replace your current cart.',
                                icon:  'warning',
                                showCancelButton:  true,
                                confirmButtonText: 'Yes, replace',
                            }).then(res => res.isConfirmed && loadOrderFromHeld(order));
                        } else {
                            loadOrderFromHeld(order);
                        }
                    }
                });
            });

            document.querySelectorAll('.remove-order-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const orderId = this.dataset.orderId;
                    Swal.fire({ title: 'Remove Order?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Yes, remove' })
                        .then(res => {
                            if (res.isConfirmed) {
                                let orders = JSON.parse(localStorage.getItem('heldOrders') || '[]').filter(o => o.id != orderId);
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
        cart                 = JSON.parse(JSON.stringify(order.cart));
        allSearchedProducts  = order.allSearchedProducts ? JSON.parse(JSON.stringify(order.allSearchedProducts)) : allSearchedProducts;
        productQuantityCache = order.productQuantityCache ? JSON.parse(JSON.stringify(order.productQuantityCache)) : {};
        if (order.customer) customerSelect.value = order.customer;
        if (order.discount) {
            orderDiscountType   = order.discount.type;
            orderDiscountValue  = order.discount.value;
            discountType.value  = order.discount.type;
            discountValue.value = order.discount.value;
        }
        updateCart(); renderAll();
        bootstrap.Modal.getInstance(document.getElementById('loadOrderModal')).hide();
        showToast('Order loaded successfully!', 'success');
    }

    // ============================================
    // COMPLETE ORDER
    // ============================================
    async function completeOrder() {
        if (cart.length === 0) { showToast('Cart is empty', 'warning'); return; }

        if (isProcessingOrder) {
            showToast('Order is already being processed, please wait…', 'info');
            return;
        }

        const payment    = document.querySelector('input[name="payment"]:checked').value;
        const customerId = customerSelect.value || null;
        const isOfflineMode = document.getElementById('offlineModeToggle').checked || !navigator.onLine;

        if (isOfflineMode) {
            const offlineOrders  = JSON.parse(localStorage.getItem('offlineOrders') || '[]');
            const offlineOrderId = 'OFFLINE-' + Date.now();
            const offlineOrder   = {
                id: offlineOrderId,
                items: cart.map(item => ({
                    product_id:    item.product_id,
                    qty:           parseFloat(item.qty),
                    unit_id:       parseInt(item.unit_id || 1),
                    sale_price:    parseFloat(item.unit_price || item.price),
                    discount_type: item.discount_type || null,
                    discount_value: item.discount_value || 0,
                    is_unit_mode:  item.is_unit_mode || false,
                    unit_name:     item.unit_name || null,
                })),
                payment_method:  payment,
                customer_id:     customerId,
                discount_type:   orderDiscountType,
                discount_value:  orderDiscountValue,
                discount_amount: window.currentDiscount?.amount || 0,
                tax_rate:        {{ config('pos.tax_rate', 0) }},
                timestamp:       Date.now(),
                time:            new Date().toLocaleString(),
            };
            offlineOrders.push(offlineOrder);
            localStorage.setItem('offlineOrders', JSON.stringify(offlineOrders));
            cart = []; productQuantityCache = {}; orderDiscountValue = 0; discountValue.value = '0';
            updateCart(); renderAll();
            Swal.fire({
                title: 'Order Saved Offline!',
                html:  `<div class="text-center">
                            <i class="bi bi-wifi-off text-warning display-1 mb-3"></i>
                            <h4>Order #${offlineOrderId}</h4>
                            <p class="mb-1">Saved locally — will sync &amp; print when you're back online.</p>
                            <small class="text-muted">The receipt will be available once synced.</small>
                        </div>`,
                icon:              'warning',
                confirmButtonText: 'OK',
            });
            return;
        }

        isProcessingOrder = true;
        const completeBtn = document.getElementById('completeOrder');
        completeBtn.disabled = true;
        completeBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Processing…';

        const items    = cart.map(item => ({
            product_id:    item.product_id,
            qty:           parseFloat(item.qty),
            unit_id:       parseInt(item.unit_id || 1),
            sale_price:    parseFloat(item.unit_price || item.price / item.qty),
            discount_type: item.discount_type || null,
            discount_value: item.discount_value || 0,
            is_unit_mode:  item.is_unit_mode || false,
            unit_name:     item.unit_name || null,
        }));
        const discount = window.currentDiscount || { type: 'percent', value: 0, amount: 0 };

        Swal.fire({ title: 'Processing Order…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        try {
            const response = await axios.post('{{ route("pos.order.save") }}', {
                items,
                payment_method:  payment,
                customer_id:     customerId,
                discount_type:   discount.type,
                discount_value:  discount.value,
                discount_amount: discount.amount,
                _token:          '{{ csrf_token() }}',
            });

            Swal.close();

            if (response.data.success) {
                Swal.fire({
                    title: 'Success!',
                    html:  `<div class="text-center">
                                <i class="bi bi-check-circle text-success display-1 mb-3"></i>
                                <h4>Order #${response.data.order_id} Completed!</h4>
                                <p class="fs-3">Total: ${formatCurrency(response.data.total)}</p>
                            </div>`,
                    icon:              'success',
                    showCancelButton:  true,
                    confirmButtonText: 'Print Receipt',
                    cancelButtonText:  'New Order',
                    buttonsStyling:    false,
                    customClass: {
                        confirmButton: 'btn btn-success btn-lg me-2',
                        cancelButton:  'btn btn-outline-secondary btn-lg',
                    },
                }).then(r => {
                    if (r.isConfirmed) {
                        playThankYouSound();
                        printWindow = window.open(`/pos/receipt/${response.data.order_id}`, '_blank');
                        if (printWindow) startMonitoringPrintWindow();
                        else resetAfterOrder();
                    } else {
                        resetAfterOrder();
                    }
                });
            } else {
                Swal.fire('Error', response.data.message || 'Failed to process order', 'error');
            }
        } catch (error) {
            Swal.close();
            let msg = 'Failed to complete order.';
            if (error.response?.data?.errors)  msg = Object.values(error.response.data.errors).flat().join('<br>');
            else if (error.response?.data?.message) msg = error.response.data.message;

            if (!navigator.onLine || error.response?.status === 0) {
                Swal.fire({
                    title:             'Connection Error',
                    html:              `You seem to be offline. Would you like to save this order for later sync?`,
                    icon:              'warning',
                    showCancelButton:  true,
                    confirmButtonText: 'Save Offline',
                    cancelButtonText:  'Dismiss',
                }).then(result => {
                    if (result.isConfirmed) {
                        document.getElementById('offlineModeToggle').checked = true;
                        updateConnectionStatus(false);
                        isProcessingOrder = false;
                        completeOrder();
                    }
                });
            } else {
                Swal.fire('Error', msg, 'error');
            }
        } finally {
            isProcessingOrder     = false;
            completeBtn.disabled  = false;
            completeBtn.innerHTML = '<i class="bi bi-printer me-2"></i> Complete & Print';
        }
    }

    function startMonitoringPrintWindow() {
        if (printCheckInterval) clearInterval(printCheckInterval);
        printCheckInterval = setInterval(function () {
            if (printWindow && printWindow.closed) {
                clearInterval(printCheckInterval);
                printCheckInterval = null;
                resetAfterOrder();
            }
        }, 500);
    }

    function resetAfterOrder() {
        cart = []; allSearchedProducts = []; productQuantityCache = {};
        orderDiscountValue = 0; discountValue.value = '0';
        updateCart(); renderAll();
        input.value = ''; input.focus(); input.select();
        showToast('New order started', 'info');
    }

    // ============================================
    // OFFLINE MODE
    // ============================================
    function updateConnectionStatus(isOnline) {
        const statusElement = document.getElementById('connectionStatus');
        const offlineToggle = document.getElementById('offlineModeToggle');
        if (isOnline) {
            statusElement.className = 'badge bg-success';
            statusElement.innerHTML = '<i class="bi bi-wifi"></i> Online';
            offlineToggle.checked   = false;
        } else {
            statusElement.className = 'badge bg-danger';
            statusElement.innerHTML = '<i class="bi bi-wifi-off"></i> Offline';
            offlineToggle.checked   = true;
        }
    }

    updateConnectionStatus(navigator.onLine);
    window.addEventListener('online',  () => { updateConnectionStatus(true);  showToast('You are back online!', 'success'); syncOfflineOrders(); });
    window.addEventListener('offline', () => { updateConnectionStatus(false); showToast('You are offline. Orders will be saved locally.', 'warning'); });

    document.getElementById('offlineModeToggle').addEventListener('change', function () {
        showToast(this.checked ? 'Offline mode enabled' : 'Online mode enabled', this.checked ? 'warning' : 'success');
    });

    async function syncOfflineOrders() {
        const offlineOrders = JSON.parse(localStorage.getItem('offlineOrders') || '[]');
        if (offlineOrders.length === 0) return;

        let syncedCount      = 0;
        let failedCount      = 0;
        let lastSyncedOrderId = null;

        for (const order of [...offlineOrders]) {
            try {
                const response = await axios.post('{{ route("pos.order.save") }}', {
                    ...order, _token: '{{ csrf_token() }}',
                });
                if (response.data.success) {
                    syncedCount++;
                    lastSyncedOrderId = response.data.order_id;
                    const remaining = JSON.parse(localStorage.getItem('offlineOrders') || '[]').filter(o => o.id !== order.id);
                    localStorage.setItem('offlineOrders', JSON.stringify(remaining));
                } else { failedCount++; }
            } catch (error) { failedCount++; }
        }

        if (syncedCount > 0) {
            Swal.fire({
                title:             `${syncedCount} Order${syncedCount > 1 ? 's' : ''} Synced!`,
                text:              'Your offline orders have been submitted successfully.',
                icon:              'success',
                showCancelButton:  lastSyncedOrderId !== null,
                confirmButtonText: 'Print Last Receipt',
                cancelButtonText:  'Dismiss',
            }).then(r => {
                if (r.isConfirmed && lastSyncedOrderId) {
                    printWindow = window.open(`/pos/receipt/${lastSyncedOrderId}`, '_blank');
                    if (printWindow) startMonitoringPrintWindow();
                }
            });
        }
        if (failedCount > 0) showToast(`${failedCount} order(s) failed to sync — will retry next time`, 'error', 4000);
    }

    // ============================================
    // KEYBOARD SHORTCUTS
    // ============================================
    document.addEventListener('keydown', function (e) {
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.isContentEditable) return;
        switch (e.key) {
            case 'F1': e.preventDefault(); input.focus(); input.select(); showToast('Search box focused', 'info', 1000); break;
            case 'F2': e.preventDefault(); clearCart(); break;
            case 'F3': e.preventDefault(); completeOrder(); break;
            case 'F4': e.preventDefault(); holdOrder(); break;
            case 'F5': e.preventDefault(); loadHeldOrders(); break;
            case 'F6': e.preventDefault(); applyViewMode(currentViewMode === 'grid' ? 'list' : 'grid'); showToast(`Switched to ${currentViewMode} view`, 'info', 1000); break;
            case 'Escape': if (cart.length > 0) clearCart(); break;
        }
    });

    // ============================================
    // HELPERS
    // ============================================
    function showToast(message, type = 'success', duration = 2000) {
        const toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false,
            timer: duration, timerProgressBar: true,
            didOpen: (t) => { t.onmouseenter = Swal.stopTimer; t.onmouseleave = Swal.resumeTimer; },
        });
        toast.fire({ icon: type, title: message });
    }

    function debounce(func, delay) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    }

    // ============================================
    // BOOT
    // ============================================
    initializeApp();
    updateCart();
});
</script>

<style>
/* ===================================================
   CUSTOMER SEARCH
=================================================== */
.customer-search-container { cursor: pointer; position: relative; transition: all 0.2s ease; }
.customer-search-container:hover { background-color: rgba(0,123,255,.05); border-radius: .375rem; }
#customerSearchInput { border: 1px solid #ced4da; border-radius: .375rem; padding: .375rem .75rem; font-size: .875rem; transition: all .2s ease; width: 100%; }
#customerSearchInput:focus { border-color: #86b7fe; box-shadow: 0 0 0 .25rem rgba(13,110,253,.25); outline: none; background-color: #fff; }

/* ===================================================
   LIST VIEW (original styles)
=================================================== */
.selected-product-row { background-color: rgba(25,135,84,.1) !important; border-left: 4px solid #198754; transition: all .3s ease; }
.badge.bg-secondary, .badge.bg-info { font-size: .7rem; padding: .25rem .5rem; border-radius: .25rem; display: inline-flex; align-items: center; gap: .25rem; }
.badge .bi-upc-scan, .badge .bi-barcode { font-size: .8rem; }
.text-muted .badge { margin: .1rem; }
#cartBody .badge { font-size: .65rem; padding: .2rem .4rem; }
td .d-flex.gap-1, td .d-flex.gap-2 { margin-top: .25rem; }

/* ===================================================
   GRID VIEW — CARD STYLES
=================================================== */
.pos-grid-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 10px;
    padding: 4px 2px;
}

.pos-grid-card {
    border: 1.5px solid #dee2e6;
    border-radius: 10px;
    padding: 10px 8px 8px;
    cursor: pointer;
    transition: transform .15s, border-color .15s, background .15s, box-shadow .15s;
    background: #fff;
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    gap: 5px;
    min-height: 145px;
    user-select: none;
}
.pos-grid-card:hover {
    border-color: #0d6efd;
    background: #f0f4ff;
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(13,110,253,.15);
}
.pos-grid-card:focus-visible {
    outline: 2px solid #0d6efd;
    outline-offset: 2px;
}
.pos-grid-card:active { transform: translateY(0); }

.pos-grid-card.pos-grid-in-cart {
    border-color: #198754;
    background: #f0fdf4;
}
.pos-grid-card.pos-grid-in-cart:hover {
    border-color: #146c43;
    background: #dcfce7;
    box-shadow: 0 4px 12px rgba(25,135,84,.2);
}

.pos-grid-card.pos-grid-outstock {
    opacity: .45;
    cursor: not-allowed;
    filter: grayscale(.6);
}
.pos-grid-card.pos-grid-outstock:hover {
    transform: none;
    box-shadow: none;
    border-color: #dee2e6;
    background: #fff;
}

/* Cart quantity badge (top-left) */
.pos-grid-cart-badge {
    position: absolute;
    top: 5px;
    left: 5px;
    background: #198754;
    color: #fff;
    font-size: 10px;
    font-weight: 600;
    padding: 2px 6px;
    border-radius: 20px;
    line-height: 1.4;
    display: flex;
    align-items: center;
    gap: 2px;
}

/* Stock badge (top-right) */
.pos-grid-stock-badge {
    position: absolute;
    top: 5px;
    right: 5px;
    font-size: 10px;
    padding: 2px 5px;
    border-radius: 20px;
    font-weight: 600;
}

/* Product thumbnail */
.pos-grid-thumb {
    width: 64px;
    height: 64px;
    border-radius: 10px;
    background: #f3f4f6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 26px;
    overflow: hidden;
    flex-shrink: 0;
    margin-top: 6px;
}
.pos-grid-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 10px;
}

/* Product info */
.pos-grid-name {
    font-size: 12px;
    font-weight: 600;
    color: #1a1a2e;
    line-height: 1.3;
    max-height: 2.6em;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    width: 100%;
}
.pos-grid-price {
    font-size: 13px;
    font-weight: 700;
    color: #198754;
}
.pos-grid-sku {
    font-size: 10px;
    color: #9ca3af;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 100%;
}
.pos-grid-unit-pref {
    font-size: 10px;
    color: #6c757d;
}

/* Category tabs */
.category-tab { transition: all .2s ease; }
.category-tab:hover { transform: translateY(-1px); }

/* ===================================================
   SHARED / MODAL STYLES
=================================================== */
.unit-quick-btn { border: 1px solid #198754 !important; color: #198754 !important; transition: all .2s ease; }
.unit-quick-btn:hover { background-color: #198754 !important; color: white !important; transform: translateY(-2px); }
#priceInputSection .input-group-lg { box-shadow: 0 2px 5px rgba(0,0,0,.1); }
#priceInputSection .input-group-text { min-width: 50px; font-weight: bold; }
#priceInputSection input { background-color: #fff; }
#priceInputSection input:focus { background-color: #fff; box-shadow: 0 0 0 .25rem rgba(13,110,253,.25); }
#priceInputSection input:disabled { background-color: #e9ecef; opacity: .7; cursor: not-allowed; }
#calculatedPriceDisplay { font-size: 1.1rem; min-height: 1.5rem; display: block; padding: .25rem; background: rgba(25,135,84,.1); border-radius: .25rem; }
#unitSelect { background-color: #f8f9fa; border: 1px solid #ced4da; font-weight: 500; }
#unitSelect:focus { background-color: #fff; border-color: #86b7fe; }
#modalUnit:disabled, #pricePerUnit:disabled, #decreaseUnit:disabled, #increaseUnit:disabled { opacity: .6; cursor: not-allowed; }
.btn-group .btn-check:checked + .btn { background-color: #0d6efd; color: white; border-color: #0d6efd; z-index: 2; }
#originalPriceText { font-size: .85rem; }
#pricePerUnit:focus, #modalUnit:focus { transform: scale(1.02); transition: transform .2s ease; }
#totalPriceLabel { font-size: 1.1rem; color: #0d6efd; }
#totalPriceLabel::after { content: ''; display: block; width: 30px; height: 2px; background: linear-gradient(to right,#0d6efd,#20c997); margin-top: 2px; border-radius: 1px; }
#modalQty:focus, #barcodeInput:focus, #discountValue:focus, #modalUnit:focus, #pricePerUnit:focus { box-shadow: 0 0 0 .25rem rgba(13,110,253,.25); border-color: #86b7fe; transform: scale(1.02); transition: all .2s ease; }

/* ===================================================
   CART TABLE
=================================================== */
.table-sm { font-size: .875rem; }
.table-sm th { font-weight: 600; padding: .5rem; }
.table-sm td { padding: .5rem; vertical-align: middle !important; }
.table-sm thead th:nth-child(1) { width: 5%; }
.table-sm thead th:nth-child(2) { width: 30%; }
.table-sm thead th:nth-child(3) { width: 15%; }
.table-sm thead th:nth-child(4) { width: 15%; }
.table-sm thead th:nth-child(5) { width: 15%; }
.table-sm thead th:nth-child(6) { width: 10%; }
.table-sm thead th:nth-child(7) { width: 10%; }
#cartBody td.text-end { text-align: right !important; padding-right: .75rem !important; }
.qty-btn-cart { min-width: 80px; padding: .25rem .5rem !important; white-space: nowrap; transition: all .2s ease; }
.qty-btn-cart:hover { transform: translateY(-1px); box-shadow: 0 2px 5px rgba(0,0,0,.1); }
.item-discount-btn, .remove-cart-item-btn { width: 28px !important; height: 28px !important; padding: 0 !important; display: inline-flex !important; align-items: center !important; justify-content: center !important; }
#cartBody img { max-width: 40px; max-height: 40px; object-fit: cover; }
.text-warning { color: #ffc107 !important; font-weight: 500; }
.item-discount-btn { transition: all .3s ease; }
.item-discount-btn:hover { background: #ffc107; color: #000; transform: rotate(15deg) scale(1.1); }
.btn-danger.rounded-circle { transition: all .3s ease; }
.btn-danger.rounded-circle:hover { transform: scale(1.1); box-shadow: 0 2px 5px rgba(220,53,69,.3); }
#cartBody tr { transition: background-color .2s ease; }
#cartBody tr:hover { background: rgba(0,0,0,.02); }

/* ===================================================
   MISC UI
=================================================== */
#connectionStatus { transition: all .3s ease; animation: pulse 2s infinite; }
#connectionStatus.bg-danger { animation: blink 1.5s infinite; }
.bi-star-fill { font-size: .8em; vertical-align: text-top; }
.btn-success .bi-scale { animation: scalePulse 2s infinite; }
#searchLoading { backdrop-filter: blur(2px); animation: fadeIn .3s ease; }
.customer-select-dropdown { background: #f8f9fa; border: 1px solid #ced4da; border-radius: .375rem; transition: all .2s ease; }
.customer-select-dropdown:focus { background: #fff; border-color: #86b7fe; box-shadow: 0 0 0 .25rem rgba(13,110,253,.25); }
#discountValue:focus, #discountType:focus { border-color: #86b7fe; box-shadow: 0 0 0 .25rem rgba(13,110,253,.25); outline: none; }
.modal-body::-webkit-scrollbar { width: 6px; }
.modal-body::-webkit-scrollbar-track { background: #f1f1f1; }
.modal-body::-webkit-scrollbar-thumb { background: #888; border-radius: 3px; }
.modal-body::-webkit-scrollbar-thumb:hover { background: #555; }
.tooltip { font-size: .875rem; }
.form-control:focus, .form-select:focus { box-shadow: 0 0 0 .25rem rgba(13,110,253,.25); border-color: #86b7fe; }
.btn-group .btn:focus { z-index: 3; }
:focus-visible { outline: 2px solid #0d6efd; outline-offset: 2px; }

/* ===================================================
   SCROLLBAR
=================================================== */
.table-responsive::-webkit-scrollbar { width: 8px; height: 8px; }
.table-responsive::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
.table-responsive::-webkit-scrollbar-thumb { background: #888; border-radius: 4px; }
.table-responsive::-webkit-scrollbar-thumb:hover { background: #555; }

/* ===================================================
   ANIMATIONS
=================================================== */
@keyframes pulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.05); } }
@keyframes slideIn { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes blink { 0%,100% { opacity: 1; } 50% { opacity: .7; } }
@keyframes scalePulse { 0%,100% { transform: scale(1); } 50% { transform: scale(1.2); } }
.modal.fade .modal-content { transform: scale(.95); transition: transform .3s ease-out, opacity .3s ease-out; opacity: 0; }
.modal.show .modal-content { transform: scale(1); opacity: 1; }
.badge { transition: all .3s ease; }
.badge:hover { transform: scale(1.05); }
.btn-check:checked + .btn-outline-success { background: #198754; color: white; border-color: #198754; transform: translateY(-2px); }
.btn-check:checked + .btn-outline-primary { background: #0d6efd; color: white; border-color: #0d6efd; transform: translateY(-2px); }
.btn-check:checked + .btn-outline-info { background: #0dcaf0; color: white; border-color: #0dcaf0; transform: translateY(-2px); }
.spinner-border.text-primary { border-color: rgba(13,110,253,.25); border-right-color: #0d6efd; }

/* ===================================================
   RESPONSIVE
=================================================== */
@media (max-width: 768px) {
    .pos-grid-container { grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 8px; }
    .pos-grid-thumb { width: 50px; height: 50px; font-size: 22px; }
    .pos-grid-card { min-height: 125px; padding: 8px 6px 6px; }
    .pos-grid-name { font-size: 11px; }
    .pos-grid-price { font-size: 12px; }
    .badge.bg-secondary, .badge.bg-info { font-size: .65rem; padding: .15rem .35rem; }
    .badge .bi-upc-scan, .badge .bi-barcode { font-size: .7rem; }
    #priceInputSection input { font-size: 1.5rem !important; height: 50px !important; }
    #calculatedPriceDisplay { font-size: 1rem; }
    .table-responsive { font-size: .9rem; }
    .btn-group { flex-wrap: wrap; }
    .d-flex.align-items-center.mb-3 { flex-wrap: wrap; }
}
@media (max-width: 576px) {
    .pos-grid-container { grid-template-columns: repeat(3, 1fr); }
    .modal-dialog { margin: .5rem; }
    #modalQty, #modalUnit { font-size: 1.5rem !important; height: 50px !important; }
    .shortcuts span.badge { font-size: .7rem; padding: .25em .4em; }
    .quick-btn, .unit-quick-btn { padding: .25rem !important; font-size: .8rem; }
    .btn-group.w-100 { flex-wrap: wrap; }
    .btn-group.w-100 .btn { font-size: .9rem; padding: .5rem; }
}
@media print {
    .no-print { display: none !important; }
    .modal, .modal-backdrop { display: none !important; }
}
</style>
@endsection
