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
                                       placeholder="Scan barcode or search..." autofocus autocomplete="off">
                                <i class="bi bi-upc-scan position-absolute top-50 end-0 translate-middle-y me-4 fs-2 text-muted"></i>
                            </div>

                            <div class="table-responsive flex-grow-1 position-relative">
                                <div id="searchLoading" class="position-absolute top-0 start-0 w-100 h-100 bg-white bg-opacity-75 d-none" style="z-index: 10;">
                                    <div class="d-flex justify-content-center align-items-center h-100">
                                        <div class="text-center">
                                            <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;"></div>
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
                                <button class="btn btn-sm btn-warning me-2" id="holdOrderBtn">Hold</button>
                                <button class="btn btn-sm btn-info me-2" id="loadHeldBtn">Load Held</button>
                                <button class="btn btn-sm btn-danger" id="clearCart">Clear</button>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <!-- Customer Selection -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold">Customer</label>
                                <select class="form-select form-select-lg" id="customerSelect">
                                    <option value="">Walk-in Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">
                                            {{ $customer->first_name }} {{ $customer->last_name }} @if($customer->phone_number) - {{ $customer->phone_number }} @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Loyalty Points Section -->
                            <div class="mb-4" id="loyaltySection" style="display:none;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold">Loyalty Points</span>
                                    <span id="customerPoints" class="text-primary fw-bold">0 points</span>
                                </div>
                                <div class="input-group input-group-sm">
                                    <input type="number" id="redeemPoints" class="form-control" min="0" placeholder="Redeem points">
                                    <button class="btn btn-success" id="applyRedeemBtn">Apply</button>
                                </div>
                                <small class="text-muted mt-1" id="redeemInfo">100 points = ₦100 discount</small>
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

                            <!-- Order Discount -->
                            <div class="border-top pt-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-semibold">Order Discount</span>
                                    <div class="input-group input-group-sm" style="width: 180px;">
                                        <input type="number" id="discountValue" class="form-control text-end" placeholder="0" min="0" step="0.01">
                                        <select id="discountType" class="form-select">
                                            <option value="fixed">₦</option>
                                            <option value="percent" selected>%</option>
                                        </select>
                                        <button class="btn btn-outline-primary" id="applyDiscountBtn">
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
                                <hr class="my-3">
                                <div class="d-flex justify-content-between fs-3 fw-bold text-success">
                                    <span>Grand Total</span>
                                    <span id="grandTotal">₦0.00</span>
                                </div>
                            </div>

                            <div class="mt-4">
                                <label class="form-label fw-bold">Payment Method</label>
                                <div class="btn-group w-100 mb-4" role="group">
                                    <input type="radio" class="btn-check" name="payment" id="cash" value="cash" checked>
                                    <label class="btn btn-outline-success w-100 py-3" for="cash">Cash</label>
                                    <input type="radio" class="btn-check" name="payment" id="card" value="card">
                                    <label class="btn btn-outline-primary w-100 py-3" for="card">Card</label>
                                    <input type="radio" class="btn-check" name="payment" id="transfer" value="transfer">
                                    <label class="btn btn-outline-info w-100 py-3" for="transfer">Transfer</label>
                                </div>
                            </div>

                            <button class="btn btn-success btn-lg w-100 py-4 fs-2" id="completeOrder">
                                <i class="bi bi-printer me-2"></i> Complete & Print
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quantity Modal -->
<div class="modal fade" id="quantityModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title">
                    <i class="bi bi-cart-plus me-2"></i> Adjust Quantity
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
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
                            <button class="btn btn-outline-secondary" type="button" id="decreaseQty">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                            <input type="number" id="modalQty" class="form-control text-center border-secondary fs-2 fw-bold"
                                   min="1" value="1" autofocus style="height: 60px;">
                            <button class="btn btn-outline-secondary" type="button" id="increaseQty">
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
<div class="modal fade" id="itemDiscountModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="bi bi-percent me-2"></i> Apply Item Discount
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <h6 id="itemName" class="text-center mb-3"></h6>
                <div class="input-group mb-3">
                    <input type="number" id="itemDiscountValue" class="form-control" placeholder="0" min="0" step="0.01">
                    <select id="itemDiscountType" class="form-select">
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

<!-- Load Held Order Modal -->
<div class="modal fade" id="loadOrderModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Load Held Order</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// MAIN POS SCRIPT
document.addEventListener('DOMContentLoaded', function () {
    // Elements
    const input = document.getElementById('barcodeInput');
    const resultsBody = document.getElementById('resultsBody');
    const emptySearchRow = document.getElementById('emptySearchRow');
    const cartBody = document.getElementById('cartBody');
    const emptyCartRow = document.getElementById('emptyCartRow');
    const subtotalEl = document.getElementById('subtotal');
    const discountEl = document.getElementById('discountAmount');
    const grandTotalEl = document.getElementById('grandTotal');
    const searchLoading = document.getElementById('searchLoading');
    const quantityModal = document.getElementById('quantityModal');
    const modalQty = document.getElementById('modalQty');
    const confirmAddBtn = document.getElementById('confirmAddBtn');
    const removeFromCartBtn = document.getElementById('removeFromCartBtn');
    const customerSelect = document.getElementById('customerSelect');
    const modalProductLabel = document.getElementById('modalProductLabel');
    const modalProductPrice = document.getElementById('modalProductPrice');
    const modalProductStock = document.getElementById('modalProductStock');
    const modalProductUnit = document.getElementById('modalProductUnit');
    const previousQtyText = document.getElementById('previousQtyText');
    const totalPriceDisplay = document.getElementById('totalPriceDisplay');
    const loyaltySection = document.getElementById('loyaltySection');
    const customerPoints = document.getElementById('customerPoints');
    const redeemPoints = document.getElementById('redeemPoints');
    const applyRedeemBtn = document.getElementById('applyRedeemBtn');
    const redeemInfo = document.getElementById('redeemInfo');

    // State
    let cart = [];
    let selectedProducts = [];
    let currentSearchQuery = '';
    let currentProduct = null;
    let currentItemIndex = null;
    let productQuantityCache = {};
    let lastSearchResults = [];
    let discountType = 'percent';
    let discountValue = 0;
    let customerCurrentPoints = 0;
    const redeemRate = 100; // 100 points = ₦100

    input.focus();

    // Loyalty: Fetch points when customer selected
    customerSelect.addEventListener('change', function () {
        const customerId = this.value;
        if (customerId) {
            axios.get(`/pos/customer-points/${customerId}`)
                .then(res => {
                    if (res.data.success) {
                        customerCurrentPoints = res.data.points;
                        customerPoints.textContent = `${customerCurrentPoints} points (₦${(customerCurrentPoints / redeemRate).toFixed(2)})`;
                        redeemInfo.textContent = `${redeemRate} points = ₦100 discount`;
                        loyaltySection.style.display = 'block';
                    }
                })
                .catch(() => {
                    loyaltySection.style.display = 'none';
                });
        } else {
            loyaltySection.style.display = 'none';
            customerCurrentPoints = 0;
        }
    });

    // Loyalty: Apply redemption
    applyRedeemBtn.addEventListener('click', function () {
        const points = parseInt(redeemPoints.value) || 0;
        if (points <= 0) {
            redeemPoints.value = '';
            updateCart();
            return;
        }
        if (points > customerCurrentPoints) {
            Swal.fire('Insufficient Points', `You only have ${customerCurrentPoints} points`, 'warning');
            return;
        }
        if (points % redeemRate !== 0) {
            Swal.fire('Invalid Amount', `Points must be multiple of ${redeemRate}`, 'warning');
            return;
        }

        discountValue = points / redeemRate;
        discountType = 'fixed';
        updateCart();

        Swal.fire('Points Redeemed!', `${points} points = ₦${discountValue} discount`, 'success');
    });

    // Input search
    input.addEventListener('input', debounce(() => {
        const q = input.value.trim();
        currentSearchQuery = q;
        if (q.length >= 2) {
            searchProducts(q);
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

    // Click handlers
    document.addEventListener('click', function(e) {
        // Quantity button
        if (e.target.classList.contains('qty-btn') || e.target.closest('.qty-btn')) {
            const btn = e.target.classList.contains('qty-btn') ? e.target : e.target.closest('.qty-btn');
            if (!btn.disabled) openQuantityModal(btn);
        }

        // Remove from search results
        if (e.target.classList.contains('remove-from-table-btn') || e.target.closest('.remove-from-table-btn')) {
            const btn = e.target.classList.contains('remove-from-table-btn') ? e.target : e.target.closest('.remove-from-table-btn');
            removeProductFromSelection(btn.dataset.productId);
        }

        // Cart quantity button
        if (e.target.classList.contains('qty-btn-cart') || e.target.closest('.qty-btn-cart')) {
            const btn = e.target.classList.contains('qty-btn-cart') ? e.target : e.target.closest('.qty-btn-cart');
            openQuantityModal(btn);
        }

        // Per-item discount button
        if (e.target.classList.contains('item-discount-btn') || e.target.closest('.item-discount-btn')) {
            const btn = e.target.classList.contains('item-discount-btn') ? e.target : e.target.closest('.item-discount-btn');
            currentItemIndex = cart.findIndex(item => item.product_id == btn.dataset.productId);
            if (currentItemIndex !== -1) {
                const item = cart[currentItemIndex];
                document.getElementById('itemName').textContent = item.title;
                document.getElementById('itemDiscountValue').value = item.discount_value || 0;
                document.getElementById('itemDiscountType').value = item.discount_type || 'percent';
                new bootstrap.Modal(document.getElementById('itemDiscountModal')).show();
            }
        }

        // Load held order
        if (e.target.classList.contains('load-order-btn') || e.target.closest('.load-order-btn')) {
            const btn = e.target.classList.contains('load-order-btn') ? e.target : e.target.closest('.load-order-btn');
            const orderId = btn.dataset.orderId;
            const heldOrders = JSON.parse(localStorage.getItem('heldOrders') || '[]');
            const order = heldOrders.find(o => o.id == orderId);
            if (order) {
                if (cart.length > 0) {
                    Swal.fire({
                        title: 'Replace Current Cart?',
                        text: 'Loading this order will replace your current cart.',
                        icon: 'warning',
                        showCancelButton: true
                    }).then(res => res.isConfirmed && loadOrderFromHeld(order));
                } else {
                    loadOrderFromHeld(order);
                }
            }
        }

        // Delete held order
        if (e.target.classList.contains('remove-order-btn') || e.target.closest('.remove-order-btn')) {
            const btn = e.target.classList.contains('remove-order-btn') ? e.target : e.target.closest('.remove-order-btn');
            const orderId = btn.dataset.orderId;
            Swal.fire({
                title: 'Remove Held Order?',
                icon: 'warning',
                showCancelButton: true
            }).then(res => {
                if (res.isConfirmed) {
                    let orders = JSON.parse(localStorage.getItem('heldOrders') || '[]');
                    orders = orders.filter(o => o.id != orderId);
                    localStorage.setItem('heldOrders', JSON.stringify(orders));
                    loadHeldOrders();
                    Swal.fire('Removed!', '', 'success');
                }
            });
        }
    });

    // Buttons
    removeFromCartBtn.addEventListener('click', removeCurrentProductFromCart);
    confirmAddBtn.addEventListener('click', addOrUpdateProductInCart);
    document.getElementById('clearCart').onclick = clearCart;
    document.getElementById('holdOrderBtn').onclick = holdOrder;
    document.getElementById('loadHeldBtn').onclick = loadHeldOrders;
    document.getElementById('completeOrder').onclick = completeOrder;

    // Order discount
    document.getElementById('discountType').addEventListener('change', function() {
        discountType = this.value;
    });

    document.getElementById('applyDiscountBtn').addEventListener('click', function() {
        discountValue = parseFloat(document.getElementById('discountValue').value) || 0;
        if (discountType === 'percent' && discountValue > 100) {
            discountValue = 100;
            document.getElementById('discountValue').value = 100;
        }
        updateCart();
    });

    // Per-item discount apply
    document.getElementById('applyItemDiscountBtn').addEventListener('click', function() {
        if (currentItemIndex === null) return;

        const value = parseFloat(document.getElementById('itemDiscountValue').value) || 0;
        const type = document.getElementById('itemDiscountType').value;

        if (type === 'percent' && value > 100) {
            Swal.fire('Invalid', 'Percentage cannot exceed 100%', 'warning');
            return;
        }

        cart[currentItemIndex].discount_type = type;
        cart[currentItemIndex].discount_value = value;

        updateCart();
        bootstrap.Modal.getInstance(document.getElementById('itemDiscountModal')).hide();
    });

    // Functions
    function showEmptySearchState() {
        searchLoading.classList.add('d-none');
        if (selectedProducts.length > 0) {
            renderAllProducts();
            emptySearchRow.style.display = 'none';
        } else {
            emptySearchRow.style.display = '';
        }
    }

    function showSearchLoading() {
        searchLoading.classList.remove('d-none');
        emptySearchRow.style.display = 'none';
    }

    function hideSearchLoading() {
        searchLoading.classList.add('d-none');
    }

    function renderAllProducts() {
        resultsBody.innerHTML = '';
        selectedProducts.forEach(p => renderProductRow(p, true));
        lastSearchResults.forEach(p => {
            if (!selectedProducts.some(sp => sp.id === p.id)) renderProductRow(p, false);
        });
        if (!resultsBody.querySelector('tr')) {
            resultsBody.innerHTML = emptySearchRow.outerHTML;
        }
    }

    function renderProductRow(product, isSelected) {
        const price = product.sale_price || product.price;
        const unit = product.primary_unit || 'Unit';
        const cartItem = cart.find(i => i.product_id === product.id);
        const addedQty = cartItem ? cartItem.qty : 0;
        const cachedQty = productQuantityCache[product.id] || 1;
        const displayQty = addedQty > 0 ? addedQty : (cachedQty > 1 ? `(${cachedQty})` : '');

        const isOutOfStock = product.stock <= 0;
        const btnClass = addedQty > 0 ? 'btn-success' : (isOutOfStock ? 'btn-secondary' : 'btn-outline-primary');
        const btnText = addedQty > 0 ? `Added ${addedQty}` : (isOutOfStock ? 'Out of Stock' : `Set Qty ${displayQty}`);
        const btnDisabled = isOutOfStock ? 'disabled' : '';

        const row = document.createElement('tr');
        row.dataset.productId = product.id;
        row.className = isSelected ? 'table-success' : '';
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
                ${isSelected ? `<button class="btn btn-sm btn-danger ms-2 remove-from-table-btn" data-product-id="${product.id}"><i class="bi bi-trash"></i></button>` : ''}
            </td>
            <td class="text-end fw-bold">₦${parseFloat(price).toFixed(2)}</td>
            <td class="text-center"><span class="badge bg-info">${unit}</span></td>
        `;
        resultsBody.appendChild(row);
        emptySearchRow.style.display = 'none';
    }

    function searchProducts(query) {
        showSearchLoading();
        axios.get('{{ route("pos.search") }}', { params: { q: query } })
            .then(res => {
                hideSearchLoading();
                lastSearchResults = res.data;
                renderAllProducts();
            })
            .catch(() => {
                hideSearchLoading();
                Swal.fire('Error', 'Failed to search products', 'error');
            });
    }

    function openQuantityModal(button) {
        const p = JSON.parse(button.dataset.product);
        currentProduct = p;

        const cartItem = cart.find(i => i.product_id === p.id);
        const previousQty = cartItem ? cartItem.qty : (productQuantityCache[p.id] || 1);

        modalProductLabel.textContent = p.title;
        modalProductPrice.textContent = `₦${(p.sale_price || p.price).toFixed(2)}`;
        modalProductStock.textContent = `Stock: ${p.stock}`;
        modalProductUnit.textContent = p.primary_unit || 'Unit';

        modalQty.value = previousQty;
        previousQtyText.textContent = cartItem ? `In cart: ${previousQty}` : `Previous: ${previousQty}`;
        previousQtyText.className = cartItem ? 'text-success fw-semibold' : 'text-muted';

        updateTotalPrice();
        removeFromCartBtn.style.display = cartItem ? 'inline-block' : 'none';

        new bootstrap.Modal(quantityModal).show();
    }

    function updateTotalPrice() {
        if (!currentProduct) return;
        const qty = parseInt(modalQty.value) || 1;
        const price = currentProduct.sale_price || currentProduct.price;
        totalPriceDisplay.textContent = `Total: ₦${(qty * price).toFixed(2)}`;
    }

    function addOrUpdateProductInCart() {
        if (!currentProduct) return;

        if (currentProduct.stock <= 0) {
            Swal.fire('Out of Stock', 'This product is unavailable', 'error');
            return;
        }

        const qty = parseInt(modalQty.value) || 1;
        if (qty < 1) return;

        const p = currentProduct;
        const price = p.sale_price || p.price;
        const unitId = p.primary_unit_id || 1;

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
        new bootstrap.Modal(quantityModal).hide();

        updateCart();
        input.value = '';
        lastSearchResults = [];
        renderAllProducts();
        currentProduct = null;

        Swal.fire({ title: existing ? 'Updated' : 'Added', icon: 'success', timer: 800, showConfirmButton: false });
    }

    function removeCurrentProductFromCart() {
        if (!currentProduct) return;
        Swal.fire({
            title: 'Remove from Cart?',
            icon: 'warning',
            showCancelButton: true
        }).then(res => {
            if (res.isConfirmed) {
                cart = cart.filter(i => i.product_id != currentProduct.id);
                delete productQuantityCache[currentProduct.id];
                removeFromSelectedProducts(currentProduct.id);
                new bootstrap.Modal(quantityModal).hide();
                updateCart();
                renderAllProducts();
            }
        });
    }

    function removeProductFromSelection(id) {
        cart = cart.filter(i => i.product_id != id);
        selectedProducts = selectedProducts.filter(p => p.id != id);
        delete productQuantityCache[id];
        updateCart();
        renderAllProducts();
    }

    function updateCart() {
        if (cart.length === 0) {
            emptyCartRow.style.display = '';
            cartBody.innerHTML = '';
            subtotalEl.textContent = '₦0.00';
            discountEl.textContent = '-₦0.00';
            grandTotalEl.textContent = '₦0.00';
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
                            <small class="text-muted">${item.sku || ''}</small>
                            ${item.discount_value > 0 ? `<small class="text-warning"> -${item.discount_value}${item.discount_type === 'percent' ? '%' : '₦'}</small>` : ''}
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
                    <button class="btn btn-sm btn-outline-warning rounded-circle item-discount-btn" data-product-id="${item.product_id}">
                        <i class="bi bi-percent"></i>
                    </button>
                </td>
                <td class="text-center">
                    <button class="btn btn-sm btn-danger rounded-circle" onclick="removeCartItem(${i})">
                        <i class="bi bi-x"></i>
                    </button>
                </td>
            `;
            cartBody.appendChild(row);
        });

        let orderDiscountAmount = 0;
        if (discountValue > 0) {
            if (discountType === 'percent') {
                orderDiscountAmount = (subtotal * discountValue) / 100;
            } else {
                orderDiscountAmount = discountValue;
            }
            orderDiscountAmount = Math.min(orderDiscountAmount, subtotal);
        }

        const grandTotal = subtotal - orderDiscountAmount;

        subtotalEl.textContent = `₦${subtotal.toFixed(2)}`;
        discountEl.textContent = `-₦${orderDiscountAmount.toFixed(2)}`;
        grandTotalEl.textContent = `₦${grandTotal.toFixed(2)}`;
    }

    window.removeCartItem = function(index) {
        const id = cart[index].product_id;
        cart.splice(index, 1);
        delete productQuantityCache[id];
        if (!cart.some(item => item.product_id === id)) {
            selectedProducts = selectedProducts.filter(p => p.id != id);
        }
        updateCart();
        renderAllProducts();
    };

    function clearCart() {
        if (cart.length === 0) return;
        Swal.fire({
            title: 'Clear Cart?',
            icon: 'warning',
            showCancelButton: true
        }).then(res => {
            if (res.isConfirmed) {
                cart = [];
                selectedProducts = [];
                productQuantityCache = {};
                discountValue = 0;
                updateCart();
                renderAllProducts();
                Swal.fire('Cleared!', '', 'success');
            }
        });
    }

    function holdOrder() {
        if (cart.length === 0) return Swal.fire('Empty', 'Nothing to hold', 'info');
        const heldOrders = JSON.parse(localStorage.getItem('heldOrders') || '[]');
        heldOrders.push({
            id: Date.now(),
            cart: JSON.parse(JSON.stringify(cart)),
            customer: customerSelect.value,
            selectedProducts: JSON.parse(JSON.stringify(selectedProducts)),
            productQuantityCache: {...productQuantityCache},
            discountType,
            discountValue,
            time: new Date().toLocaleString(),
            timestamp: Date.now()
        });
        localStorage.setItem('heldOrders', JSON.stringify(heldOrders));
        Swal.fire('Held!', 'Order saved', 'success');
        clearCart();
    }

    function loadHeldOrders() {
        const heldOrders = JSON.parse(localStorage.getItem('heldOrders') || '[]');
        const list = document.getElementById('heldOrdersList');
        const no = document.getElementById('noHeldOrders');

        if (heldOrders.length === 0) {
            no.style.display = 'block';
            list.innerHTML = '';
        } else {
            no.style.display = 'none';
            let html = '<div class="list-group">';
            heldOrders.sort((a,b) => b.timestamp - a.timestamp).forEach(o => {
                const items = o.cart.length;
                html += `
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${o.time}</strong>
                                <p class="mb-0 text-muted">${items} item${items > 1 ? 's' : ''}</p>
                            </div>
                            <div>
                                <button class="btn btn-sm btn-primary load-order-btn" data-order-id="${o.id}">Load</button>
                                <button class="btn btn-sm btn-danger remove-order-btn" data-order-id="${o.id}"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>`;
            });
            html += '</div>';
            list.innerHTML = html;
        }
        new bootstrap.Modal(document.getElementById('loadOrderModal')).show();
    }

    function loadOrderFromHeld(order) {
        cart = order.cart;
        selectedProducts = order.selectedProducts || [];
        productQuantityCache = order.productQuantityCache || {};
        discountType = order.discountType || 'percent';
        discountValue = order.discountValue || 0;
        if (order.customer) customerSelect.value = order.customer;
        updateCart();
        renderAllProducts();
        new bootstrap.Modal(document.getElementById('loadOrderModal')).hide();
        Swal.fire('Loaded!', '', 'success');
    }

    function completeOrder() {
        if (cart.length === 0) return Swal.fire('Empty Cart', 'Add items first', 'warning');

        const payment = document.querySelector('input[name="payment"]:checked').value;
        const customerId = customerSelect.value || null;
        const redeemPointsValue = parseInt(redeemPoints.value) || 0;

        const items = cart.map(item => ({
            product_id: item.product_id,
            qty: item.qty,
            unit_id: item.unit_id,
            sale_price: item.price,
            discount_type: item.discount_type,
            discount_value: item.discount_value
        }));

        Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        axios.post('{{ route("pos.order.save") }}', {
            items,
            payment_method: payment,
            customer_id: customerId,
            discount_type: discountType,
            discount_value: discountValue,
            redeem_points: redeemPointsValue,
            _token: '{{ csrf_token() }}'
        })
        .then(res => {
            Swal.close();
            if (res.data.success) {
                Swal.fire({
                    title: 'Success!',
                    html: `<h4>Order #${res.data.order_id}</h4><p>Total: ₦${res.data.total.toFixed(2)}</p>`,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Print Receipt',
                    cancelButtonText: 'New Sale'
                }).then(r => {
                    if (r.isConfirmed) window.open(`/pos/receipt/${res.data.order_id}`, '_blank');
                    cart = []; selectedProducts = []; productQuantityCache = {};
                    discountValue = 0; redeemPoints.value = '';
                    loyaltySection.style.display = 'none';
                    updateCart(); renderAllProducts();
                    input.focus();
                });
            }
        })
        .catch(err => {
            Swal.close();
            const msg = err.response?.data?.message || 'Failed to complete order';
            Swal.fire('Error', msg, 'error');
        });
    }

    function debounce(func, delay) {
        let timeout;
        return (...args) => {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), delay);
        };
    }

    updateCart();
});
</script>

<style>
.qty-btn:disabled, .qty-btn-cart:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}
.selected-product-row {
    background-color: rgba(25,135,84,0.1) !important;
    border-left: 4px solid #198754;
}
.item-discount-btn {
    width: 32px;
    height: 32px;
    padding: 0;
}
</style>
@endsection

