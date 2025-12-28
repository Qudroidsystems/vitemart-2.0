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
                                <button class="btn btn-sm btn-warning me-2" id="holdOrderBtn">Hold</button>
                                <button class="btn btn-sm btn-info me-2" id="loadHeldBtn">Load Held</button>
                                <button class="btn btn-sm btn-danger" id="clearCart">Clear</button>
                            </div>
                        </div>
                        <div class="card-body d-flex flex-column">
                            <!-- Customer Selection -->
                            <div class="mb-4">
                                <label class="form-label fw-semibold d-flex justify-content-between align-items-center">
                                    <span>Customer</span>
                                    <a href="javascript:void(0)" class="text-decoration-none fs-6"
                                       data-bs-toggle="tooltip" data-bs-title="Quick customer management">
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
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody id="cartBody">
                                            <tr id="emptyCartRow">
                                                <td colspan="6" class="text-center py-5 text-muted">
                                                    <i class="bi bi-cart fs-1 mb-3"></i>
                                                    No items in cart
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="border-top pt-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal</span>
                                    <span id="subtotal">₦0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2 text-danger">
                                    <span>Discount</span>
                                    <span id="discountAmount">-₦0.00</span>
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

<!-- Load Order Modal -->
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

    // Initialize Bootstrap tooltips
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    // State management
    let cart = [];
    let selectedProducts = [];
    let currentSearchQuery = '';
    let loadOrderModalInstance = new bootstrap.Modal(document.getElementById('loadOrderModal'));
    let currentProduct = null;
    let quantityModalInstance = null;
    let productQuantityCache = {};
    let lastSearchResults = [];

    input.focus();

    // ============================================
    // EVENT LISTENERS
    // ============================================
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

    customerSelect.addEventListener('touchstart', function() {
        if (window.innerWidth < 768) this.style.fontSize = '16px';
    });

    // Quantity Modal Events
    quantityModal.addEventListener('show.bs.modal', updateTotalPrice);
    quantityModal.addEventListener('shown.bs.modal', () => {
        setTimeout(() => { modalQty.focus(); modalQty.select(); }, 100);
    });
    quantityModal.addEventListener('hidden.bs.modal', () => {
        setTimeout(() => { input.focus(); input.select(); }, 100);
    });

    modalQty.addEventListener('input', updateTotalPrice);
    modalQty.addEventListener('change', updateTotalPrice);

    document.getElementById('increaseQty').addEventListener('click', () => {
        modalQty.value = (parseInt(modalQty.value) || 1) + 1;
        updateTotalPrice();
        modalQty.focus(); modalQty.select();
    });

    document.getElementById('decreaseQty').addEventListener('click', () => {
        const val = parseInt(modalQty.value) || 1;
        if (val > 1) {
            modalQty.value = val - 1;
            updateTotalPrice();
            modalQty.focus(); modalQty.select();
        }
    });

    document.querySelectorAll('[data-qty]').forEach(btn => {
        btn.addEventListener('click', () => {
            modalQty.value = btn.dataset.qty;
            updateTotalPrice();
            modalQty.focus(); modalQty.select();
        });
    });

    modalQty.addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            confirmAddBtn.click();
        }
    });

    document.getElementById('loadOrderModal').addEventListener('hidden.bs.modal', () => {
        setTimeout(() => { input.focus(); input.select(); }, 100);
    });

    // Refocus search input on page click (except modals)
    document.addEventListener('click', e => {
        const isModalOpen = document.querySelector('.modal.show');
        const isModalElement = e.target.closest('.modal');
        const isBackdrop = e.target.classList.contains('modal-backdrop');
        const isSearch = e.target === input || input.contains(e.target);
        const isCustomer = e.target === customerSelect || customerSelect.contains(e.target);

        if (!isModalOpen && !isModalElement && !isBackdrop && !isSearch && !isCustomer) {
            setTimeout(() => input.focus(), 50);
        }
    });

    // Click handlers for buttons in search results and cart
    document.addEventListener('click', function(e) {
        // Qty button in search results
        if (e.target.classList.contains('qty-btn') || e.target.closest('.qty-btn')) {
            const btn = e.target.classList.contains('qty-btn') ? e.target : e.target.closest('.qty-btn');
            openQuantityModal(btn);
        }

        // Remove from selection in search results
        if (e.target.classList.contains('remove-from-table-btn') || e.target.closest('.remove-from-table-btn')) {
            const btn = e.target.classList.contains('remove-from-table-btn') ? e.target : e.target.closest('.remove-from-table-btn');
            removeProductFromSelection(btn.dataset.productId);
        }

        // Qty button in cart (new class)
        if (e.target.classList.contains('qty-btn-cart') || e.target.closest('.qty-btn-cart')) {
            const btn = e.target.classList.contains('qty-btn-cart') ? e.target : e.target.closest('.qty-btn-cart');
            openQuantityModal(btn);
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
                        showCancelButton: true,
                        confirmButtonText: 'Yes, replace'
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
                title: 'Remove Order?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, remove'
            }).then(res => {
                if (res.isConfirmed) {
                    let orders = JSON.parse(localStorage.getItem('heldOrders') || '[]');
                    orders = orders.filter(o => o.id != orderId);
                    localStorage.setItem('heldOrders', JSON.stringify(orders));
                    document.getElementById('loadHeldBtn').click();
                    Swal.fire('Removed!', '', 'success');
                }
            });
        }
    });

    removeFromCartBtn.addEventListener('click', removeCurrentProductFromCart);
    confirmAddBtn.addEventListener('click', addOrUpdateProductInCart);
    document.getElementById('clearCart').onclick = clearCart;
    document.getElementById('holdOrderBtn').onclick = holdOrder;
    document.getElementById('loadHeldBtn').onclick = loadHeldOrders;
    document.getElementById('completeOrder').onclick = completeOrder;

    // ============================================
    // FUNCTIONS
    // ============================================
    function showEmptySearchState() {
        searchLoading.classList.add('d-none');
        if (selectedProducts.length > 0) {
            renderAllProducts();
            emptySearchRow.style.display = 'none';
        } else if (!resultsBody.querySelector('tr[data-product-id]')) {
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
        resultsBody.innerHTML = selectedProducts.length || lastSearchResults.length ? '' : emptySearchRow.outerHTML;

        selectedProducts.forEach(p => renderProductRow(p, true));
        lastSearchResults.forEach(p => {
            if (!selectedProducts.some(sp => sp.id === p.id)) renderProductRow(p, false);
        });

        if (!resultsBody.querySelector('tr[data-product-id]')) {
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

        const row = document.createElement('tr');
        row.dataset.productId = product.id;
        row.className = isSelected ? 'selected-product-row table-success' : '';
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
                <button class="btn btn-sm ${addedQty > 0 ? 'btn-success' : 'btn-outline-primary'} qty-btn"
                        data-product='${JSON.stringify(product).replace(/'/g, "&apos;")}'
                        data-product-id="${product.id}">
                    ${addedQty > 0 ? `Added ${addedQty}` : `Set Qty ${displayQty}`}
                </button>
                ${isSelected ? `<button class="btn btn-sm btn-danger ms-2 remove-from-table-btn" data-product-id="${product.id}">
                    <i class="bi bi-trash"></i> Remove
                </button>` : ''}
            </td>
            <td class="text-end fw-bold">₦${parseFloat(price).toFixed(2)}</td>
            <td class="text-center"><span class="badge bg-info">${unit}</span></td>
        `;
        resultsBody.appendChild(row);
        emptySearchRow.style.display = 'none';
    }

    function searchProducts(query) {
        if (!query) return;
        showSearchLoading();
        axios.get('{{ route("pos.search") }}', { params: { q: query } })
            .then(res => {
                hideSearchLoading();
                lastSearchResults = res.data;
                renderAllProducts();
            })
            .catch(err => {
                hideSearchLoading();
                Swal.fire('Error', 'Failed to search products', 'error');
            });
    }

    function openQuantityModal(button) {
        try {
            const p = JSON.parse(button.dataset.product);
            const productId = button.dataset.productId;
            currentProduct = p;

            const cartItem = cart.find(i => i.product_id === productId);
            const cachedQty = productQuantityCache[productId] || 1;
            const previousQty = cartItem ? cartItem.qty : cachedQty;

            modalProductLabel.textContent = p.title;
            const price = p.sale_price || p.price;
            modalProductPrice.textContent = `₦${parseFloat(price).toFixed(2)}`;
            modalProductStock.textContent = `Stock: ${p.stock}`;
            modalProductUnit.textContent = p.primary_unit || 'Unit';

            modalQty.value = previousQty;
            previousQtyText.textContent = cartItem ? `In cart: ${previousQty}` : `Previous: ${previousQty}`;
            previousQtyText.className = cartItem ? 'text-success fw-semibold' : 'text-muted';

            updateTotalPrice();
            removeFromCartBtn.style.display = cartItem ? 'inline-block' : 'none';

            quantityModalInstance = new bootstrap.Modal(quantityModal);
            quantityModalInstance.show();
        } catch (e) {
            console.error(e);
        }
    }

    function updateTotalPrice() {
        if (!currentProduct) return;
        const qty = parseInt(modalQty.value) || 1;
        const price = currentProduct.sale_price || currentProduct.price;
        totalPriceDisplay.textContent = `Total: ₦${(qty * price).toFixed(2)}`;
    }

    function addToSelectedProducts(product) {
        if (!selectedProducts.some(p => p.id === product.id)) selectedProducts.push({...product});
    }

    function removeFromSelectedProducts(id) {
        selectedProducts = selectedProducts.filter(p => p.id != id);
    }

    function addOrUpdateProductInCart() {
        if (!currentProduct) return;
        const qty = parseInt(modalQty.value) || 1;
        if (qty < 1) return Swal.fire('Invalid', 'Quantity must be at least 1', 'warning');

        const p = currentProduct;
        const price = p.sale_price || p.price;
        let unitId = p.primary_unit_id || 1;

        addToSelectedProducts(p);

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
                thumbnail: p.thumbnail
            });
        }

        productQuantityCache[p.id] = qty;
        quantityModalInstance?.hide();

        updateCart();
        input.value = '';
        lastSearchResults = [];
        renderAllProducts();
        currentProduct = null;

        Swal.fire({ title: existing ? 'Updated!' : 'Added!', icon: 'success', timer: 1000, showConfirmButton: false });
    }

    function removeProductFromSelection(id) {
        cart = cart.filter(i => i.product_id != id);
        removeFromSelectedProducts(id);
        delete productQuantityCache[id];
        updateCart();
        renderAllProducts();
        Swal.fire({ title: 'Removed!', icon: 'success', timer: 1000, showConfirmButton: false });
    }

    function removeCurrentProductFromCart() {
        if (!currentProduct) return;
        Swal.fire({
            title: 'Remove from Cart?',
            icon: 'warning',
            showCancelButton: true
        }).then(res => {
            if (res.isConfirmed) {
                const id = currentProduct.id;
                cart = cart.filter(i => i.product_id != id);
                removeFromSelectedProducts(id);
                delete productQuantityCache[id];
                quantityModalInstance?.hide();
                updateCart();
                renderAllProducts();
                Swal.fire('Removed!', '', 'success');
            }
        });
    }

    function updateCart() {
        if (cart.length === 0) {
            emptyCartRow.style.display = '';
            cartBody.innerHTML = '';
            subtotalEl.textContent = '₦0.00';
            grandTotalEl.textContent = '₦0.00';
            selectedProducts = [];
            renderAllProducts();
            return;
        }

        emptyCartRow.style.display = 'none';
        cartBody.innerHTML = '';
        let subtotal = 0;

        cart.forEach((item, i) => {
            const total = item.qty * item.price;
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
                <td class="text-end">₦${item.price.toFixed(2)}</td>
                <td class="text-end fw-bold">₦${total.toFixed(2)}</td>
                <td class="text-center">
                    <button class="btn btn-sm btn-danger rounded-circle" onclick="removeCartItem(${i})">
                        <i class="bi bi-x"></i>
                    </button>
                </td>
            `;
            cartBody.appendChild(row);
        });

        subtotalEl.textContent = `₦${subtotal.toFixed(2)}`;
        grandTotalEl.textContent = `₦${subtotal.toFixed(2)}`;
    }

    window.removeCartItem = (i) => {
        const id = cart[i].product_id;
        delete productQuantityCache[id];
        cart.splice(i, 1);
        if (!cart.some(item => item.product_id === id)) removeFromSelectedProducts(id);
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
            cart: cart,
            customer: customerSelect.value,
            selectedProducts: selectedProducts,
            productQuantityCache: productQuantityCache,
            time: new Date().toLocaleString(),
            timestamp: Date.now()
        });
        localStorage.setItem('heldOrders', JSON.stringify(heldOrders));
        Swal.fire('Order Held!', '', 'success');
        cart = []; selectedProducts = []; productQuantityCache = {};
        updateCart(); renderAllProducts();
    }

    function loadHeldOrders() {
        const heldOrders = JSON.parse(localStorage.getItem('heldOrders') || '[]');
        const list = document.getElementById('heldOrdersList');
        const no = document.getElementById('noHeldOrders');
        if (heldOrders.length === 0) {
            list.innerHTML = '';
            no.style.display = 'block';
        } else {
            no.style.display = 'none';
            let html = '<div class="list-group">';
            heldOrders.sort((a,b) => b.timestamp - a.timestamp).forEach(o => {
                const items = o.cart.length;
                const cust = o.customer ? ` - ${o.customer}` : '';
                html += `
                    <div class="list-group-item list-group-item-action">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="mb-1">${o.time}${cust}</h6>
                                <p class="mb-1 text-muted">${items} item${items>1?'s':''}</p>
                            </div>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary load-order-btn" data-order-id="${o.id}">Load</button>
                                <button class="btn btn-sm btn-outline-danger remove-order-btn" data-order-id="${o.id}"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>`;
            });
            html += '</div>';
            list.innerHTML = html;
        }
        loadOrderModalInstance.show();
    }

    function loadOrderFromHeld(order) {
        cart = JSON.parse(JSON.stringify(order.cart));
        selectedProducts = order.selectedProducts ? JSON.parse(JSON.stringify(order.selectedProducts)) : [];
        productQuantityCache = order.productQuantityCache ? JSON.parse(JSON.stringify(order.productQuantityCache)) : {};
        if (order.customer) customerSelect.value = order.customer;
        updateCart();
        renderAllProducts();
        loadOrderModalInstance.hide();
        Swal.fire('Loaded!', '', 'success');
    }

    function completeOrder() {
        if (cart.length === 0) return Swal.fire('Empty', 'Add items first', 'warning');
        const payment = document.querySelector('input[name="payment"]:checked').value;
        const customerId = customerSelect.value || null;

        const items = cart.map(item => ({
            product_id: item.product_id,
            qty: parseInt(item.qty),
            unit_id: parseInt(item.unit_id || 1),
            sale_price: parseFloat(item.price)
        }));

        Swal.fire({ title: 'Processing...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

        axios.post('{{ route("pos.order.save") }}', {
            items, payment_method: payment, customer_id: customerId, _token: '{{ csrf_token() }}'
        })
        .then(res => {
            Swal.close();
            if (res.data.success) {
                Swal.fire({
                    title: 'Success!',
                    html: `<div class="text-center"><i class="bi bi-check-circle text-success display-1 mb-3"></i><h4>Order #${res.data.order_id} Completed!</h4><p class="fs-3">Total: ₦${parseFloat(res.data.total).toFixed(2)}</p></div>`,
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Print Receipt',
                    cancelButtonText: 'New Order'
                }).then(r => {
                    if (r.isConfirmed) window.open(`/pos/receipt/${res.data.order_id}`, '_blank');
                    cart = []; selectedProducts = []; productQuantityCache = {};
                    updateCart(); renderAllProducts();
                    input.focus(); input.select();
                });
            } else {
                Swal.fire('Error', res.data.message || 'Failed', 'error');
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

    function debounce(func, delay) {
        let t;
        return (...args) => {
            clearTimeout(t);
            t = setTimeout(() => func.apply(this, args), delay);
        };
    }

    updateCart();
});
</script>

<style>
/* All your existing styles remain unchanged - omitted here for brevity, but keep them exactly as in your original code */
.modal.fade .modal-content { transform: scale(0.95); transition: transform 0.3s ease-out; }
.modal.show .modal-content { transform: scale(1); }
.product-icon { transition: all 0.3s ease; }
.modal.show .product-icon { animation: pulse 0.6s ease; }
@keyframes pulse { 0%{transform:scale(1)} 50%{transform:scale(1.1)} 100%{transform:scale(1)} }
#modalQty:focus { box-shadow: 0 0 0 0.25rem rgba(13,110,253,0.25); border-color:#86b7fe; transform:scale(1.02); }
.btn-outline-secondary:hover { background:#6c757d; color:white; transform:translateY(-2px); }
.selected-product-row { background-color:rgba(25,135,84,0.1)!important; border-left:4px solid #198754; }
.qty-btn, .qty-btn-cart { min-width:120px; }
.qty-btn-cart { min-width:60px; font-weight:bold; }
.qty-btn-cart:hover { transform:translateY(-1px); box-shadow:0 2px 5px rgba(0,0,0,0.1); }
#searchLoading { backdrop-filter:blur(2px); }
.list-group-item:hover { background:#f8f9fa; }
@media (max-width:576px) { .modal-dialog{margin:0.5rem;} #modalQty{font-size:1.5rem!important;height:50px!important;} }
.btn-danger.rounded-circle { width:30px;height:30px;display:flex;align-items:center;justify-content:center;padding:0; }
.customer-select-dropdown { background:#f8f9fa; border:1px solid #ced4da; border-radius:0.375rem; transition:all 0.2s ease; }
.customer-select-dropdown:focus { background:#fff; border-color:#86b7fe; box-shadow:0 0 0 0.25rem rgba(13,110,253,0.25); }
#totalPriceDisplay { font-size:1.1rem; font-weight:bold; }
.btn-outline-secondary { width:30px;height:30px;display:flex;align-items:center;justify-content:center;padding:0; }
.badge { font-size:0.75em; padding:0.35em 0.65em; }
.table-primary th { background:#0d6efd; color:white; }
.table-dark th { background:#212529; color:white; }
.btn-check:checked + .btn-outline-success { background:#198754; color:white; border-color:#198754; }
.btn-check:checked + .btn-outline-primary { background:#0d6efd; color:white; border-color:#0d6efd; }
.btn-check:checked + .btn-outline-info { background:#0dcaf0; color:white; border-color:#0dcaf0; }
#cartBody tr:hover { background:rgba(0,0,0,0.02); }
</style>
@endsection
