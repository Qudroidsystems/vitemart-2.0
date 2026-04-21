@extends('layouts.master')

@section('title', 'Order #' . ($order->invoice_number ?? substr($order->id, 0, 8)))

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <div>
                            <h4 class="mb-sm-0">
                                Order #<span class="text-primary fw-bold">{{ $order->invoice_number ?? $order->id }}</span>
                            </h4>
                            <p class="text-muted mb-0">
                                <i class="bi bi-calendar3"></i> Placed on {{ $order->created_at ? $order->created_at->format('F d, Y h:i A') : 'N/A' }}
                            </p>
                        </div>
                        <div class="d-flex gap-2">
                            <button onclick="emailInvoice('{{ $order->id }}')" class="btn btn-info">
                                <i class="bi bi-envelope"></i> Email Invoice
                            </button>
                            <a href="{{ route('orders.invoice', $order->id) }}" target="_blank" class="btn btn-primary">
                                <i class="bi bi-file-pdf"></i> PDF Invoice
                            </a>
                            <a href="{{ route('orders.packing-slip', $order->id) }}" target="_blank" class="btn btn-secondary">
                                <i class="bi bi-printer"></i> Print Packing Slip
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Left Column -->
                <div class="col-xl-4">
                    <!-- Customer Information Card -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-person-circle"></i> Customer Information</h5>
                        </div>
                        <div class="card-body">
                            @if($order->customer)
                                <div class="d-flex align-items-center mb-3">
                                    <div class="avatar-lg me-3">
                                        <div class="avatar-title bg-primary-subtle rounded-circle fs-3">
                                            {{ Str::substr($order->customer->first_name ?? 'C', 0, 1) }}
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="mb-1">{{ $order->customer->first_name ?? 'N/A' }} {{ $order->customer->last_name ?? '' }}</h5>
                                        <p class="text-muted mb-0">
                                            <i class="bi bi-envelope"></i> {{ $order->customer->email ?? 'N/A' }}
                                        </p>
                                        @if($order->customer->phone_number ?? false)
                                            <p class="text-muted mb-0">
                                                <i class="bi bi-phone"></i> {{ $order->customer->phone_number }}
                                            </p>
                                        @endif
                                        @if($order->customer->home_address ?? false)
                                            <p class="text-muted mb-0">
                                                <i class="bi bi-geo-alt"></i> {{ $order->customer->home_address }}
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-3">
                                    <i class="bi bi-person-walking fs-1 d-block mb-2 text-muted"></i>
                                    <h6 class="mb-0">Walk-in Customer</h6>
                                    <small class="text-muted">No customer account linked to this order</small>
                                </div>
                            @endif

                            <!-- Sales Person Info (separate section) -->
                            <hr>
                            <div class="mt-2">
                                <small class="text-muted">Processed by:</small>
                                <div class="d-flex align-items-center mt-1">
                                    <div class="avatar-sm me-2">
                                        <div class="avatar-title bg-secondary-subtle rounded-circle">
                                            <i class="bi bi-person-badge"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <strong>{{ $order->user ? ($order->user->first_name . ' ' . $order->user->last_name) : 'System' }}</strong>
                                        <br><small class="text-muted">{{ $order->user->email ?? '' }}</small>
                                    </div>
                                </div>
                            </div>

                            @if($order->shippingAddress)
                                <hr>
                                <div class="mt-3">
                                    <strong><i class="bi bi-geo-alt"></i> Shipping Address:</strong><br>
                                    {{ $order->shippingAddress->address ?? $order->shippingAddress->street ?? 'N/A' }}<br>
                                    @if($order->shippingAddress->city){{ $order->shippingAddress->city }}, @endif
                                    @if($order->shippingAddress->state){{ $order->shippingAddress->state }}, @endif
                                    @if($order->shippingAddress->country){{ $order->shippingAddress->country }} @endif
                                    @if($order->shippingAddress->zip_code)<br>Zip: {{ $order->shippingAddress->zip_code }}@endif
                                </div>
                            @endif

                            @if($order->billingAddress && (!$order->billing_address_same_as_shipping))
                                <hr>
                                <div class="mt-3">
                                    <strong><i class="bi bi-credit-card"></i> Billing Address:</strong><br>
                                    {{ $order->billingAddress->address ?? $order->billingAddress->street ?? 'N/A' }}<br>
                                    @if($order->billingAddress->city){{ $order->billingAddress->city }}, @endif
                                    @if($order->billingAddress->country){{ $order->billingAddress->country }}@endif
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Order Summary Card -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-info-circle"></i> Order Summary</h5>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Order Type:</span>
                                <span class="fw-bold">
                                    @if($order->customer)
                                        <i class="bi bi-person-check text-success"></i> Registered Customer
                                    @else
                                        <i class="bi bi-person-walking text-warning"></i> Walk-in
                                    @endif
                                </span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Payment Method:</span>
                                <span class="fw-bold">{{ ucfirst($order->payment_method ?? 'N/A') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Payment Status:</span>
                                <span>
                                    <span class="badge {{ $order->payment_status == 'paid' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($order->payment_status ?? 'Pending') }}
                                    </span>
                                </span>
                            </div>
                            @if($order->paid_at)
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Paid At:</span>
                                    <span>{{ $order->paid_at->format('M d, Y h:i A') }}</span>
                                </div>
                            @endif
                            @if($order->commission_amount)
                                <hr>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Commission:</span>
                                    <span class="text-success fw-bold">${{ number_format($order->commission_amount, 2) }} ({{ $order->commission_rate }}%)</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Refund Section -->
                    @if($order->payment_status == 'paid' && $order->refundableAmount() > 0)
                    <div class="card border-danger">
                        <div class="card-header bg-danger-subtle">
                            <h5 class="text-danger mb-0"><i class="bi bi-arrow-return-left"></i> Request Refund</h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('orders.refund', $order->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label">Amount (Max: ${{ number_format($order->refundableAmount(), 2) }})</label>
                                    <input type="number" step="0.01" name="amount" class="form-control" max="{{ $order->refundableAmount() }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Reason</label>
                                    <textarea name="reason" class="form-control" rows="3" required placeholder="Enter refund reason..."></textarea>
                                </div>
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="bi bi-arrow-return-left"></i> Process Refund
                                </button>
                            </form>
                        </div>
                    </div>
                    @endif

                    <!-- Refund History -->
                    @if($order->refunds && $order->refunds->count())
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-clock-history"></i> Refund History</h5>
                        </div>
                        <div class="card-body p-0">
                            @foreach($order->refunds as $refund)
                            <div class="p-3 border-bottom">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong class="text-danger">-${{ number_format($refund->amount, 2) }}</strong>
                                        <p class="text-muted mb-0 small">{{ $refund->reason }}</p>
                                        <small class="text-muted">{{ $refund->created_at ? $refund->created_at->format('M d, Y h:i A') : 'N/A' }}</small>
                                    </div>
                                    <span class="badge {{ $refund->status == 'processed' ? 'bg-success' : 'bg-warning' }}">
                                        {{ ucfirst($refund->status) }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Right Column -->
                <div class="col-xl-8">
                    <!-- Status Update Card -->
                    <div class="card mb-3">
                        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div>
                                <h5 class="mb-1">Order Status</h5>
                                <span class="badge fs-6 px-3 py-2
                                    @if($order->status == 'delivered') bg-success
                                    @elseif($order->status == 'cancelled') bg-danger
                                    @elseif($order->status == 'shipped') bg-info
                                    @elseif($order->status == 'processing') bg-warning
                                    @else bg-secondary
                                    @endif">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </div>
                            @can('update orders')
                            <div>
                                <label class="form-label mb-1">Update Status</label>
                                <select class="form-select status-select" data-id="{{ $order->id }}">
                                    @foreach(['pending','processing','shipped','delivered','cancelled'] as $s)
                                        <option value="{{ $s }}" {{ $order->status == $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endcan
                        </div>
                    </div>

                    <!-- Order Items Card -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-box-seam"></i> Order Items
                                <span class="badge bg-secondary ms-1">{{ $order->items->count() }}</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table align-middle table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 40%">Product</th>
                                            <th class="text-center" style="width: 15%">Unit</th>
                                            <th class="text-center" style="width: 15%">Quantity</th>
                                            <th class="text-end" style="width: 15%">Unit Price</th>
                                            <th class="text-end" style="width: 15%">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($order->items as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($item->image)
                                                        <img src="{{ asset('storage/' . $item->image) }}" class="rounded me-3" style="width:50px;height:50px;object-fit:cover;">
                                                    @else
                                                        <div class="avatar-sm me-3 bg-light rounded d-flex align-items-center justify-content-center">
                                                            <i class="bi bi-box text-muted"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $item->title ?? 'N/A' }}</strong>
                                                        @if($item->sku)
                                                            <br><small class="text-muted">SKU: {{ $item->sku }}</small>
                                                        @endif
                                                        @if($item->selected_variation)
                                                            <br><small class="text-muted">
                                                                @php
                                                                    $variation = is_string($item->selected_variation) ? json_decode($item->selected_variation, true) : $item->selected_variation;
                                                                @endphp
                                                                @if(is_array($variation))
                                                                    @foreach($variation as $key => $val)
                                                                        <span class="badge bg-light text-dark me-1">{{ ucfirst($key) }}: {{ $val }}</span>
                                                                    @endforeach
                                                                @else
                                                                    {{ $item->selected_variation }}
                                                                @endif
                                                            </small>
                                                        @endif
                                                        @if($item->discount_value > 0)
                                                            <br><small class="text-warning">
                                                                <i class="bi bi-tag"></i>
                                                                -{{ $item->discount_value }}{{ $item->discount_type == 'percent' ? '%' : ' fixed' }} discount
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info">
                                                    {{ $item->unit_name ?? ($item->unit->name ?? 'Unit') }}
                                                </span>
                                                @if($item->is_unit_mode)
                                                    <i class="bi bi-scale text-success ms-1" title="Unit mode (weight/measurement)"></i>
                                                @endif
                                            </td>
                                            <td class="text-center fw-bold">
                                                {{ number_format($item->quantity ?? 0, $item->is_unit_mode ? 3 : 0) }}
                                            </td>
                                            <td class="text-end text-success fw-bold">
                                                ${{ number_format($item->unit_price ?? 0, 2) }}
                                            </td>
                                            <td class="text-end text-success fw-bold">
                                                ${{ number_format($item->total_price ?? ($item->unit_price * $item->quantity), 2) }}
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-5">
                                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                                No items found for this order.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Order Totals Card -->
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 offset-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td width="60%">Subtotal:</td>
                                            <td class="text-end">${{ number_format($order->items->sum('total_price'), 2) }}</td>
                                        </tr>
                                        @if($order->discount_amount > 0)
                                        <tr>
                                            <td>Discount:</td>
                                            <td class="text-end text-danger">-${{ number_format($order->discount_amount, 2) }}</td>
                                        </tr>
                                        @endif
                                        <tr>
                                            <td>Shipping Cost:</td>
                                            <td class="text-end">${{ number_format($order->shipping_cost ?? 0, 2) }}</td>
                                        </tr>
                                        <tr>
                                            <td>Tax:</td>
                                            <td class="text-end">${{ number_format($order->tax_cost ?? 0, 2) }}</td>
                                        </tr>
                                        @if($order->totalRefunded() > 0)
                                        <tr>
                                            <td class="text-danger">Refunded:</td>
                                            <td class="text-end text-danger">-${{ number_format($order->totalRefunded(), 2) }}</td>
                                        </tr>
                                        @endif
                                        <tr class="border-top">
                                            <td class="fw-bold fs-5">Total Paid:</td>
                                            <td class="text-end fw-bold fs-5 text-success">
                                                ${{ number_format($order->total_amount - $order->totalRefunded(), 2) }}
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Transactions Card -->
                    @if($order->transactions && $order->transactions->count())
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="bi bi-credit-card"></i> Payment Transactions</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Transaction ID</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($order->transactions as $transaction)
                                        <tr>
                                            <td>{{ $transaction->transaction_id ?? $transaction->id }}</td>
                                            <td class="text-success fw-bold">${{ number_format($transaction->amount, 2) }}</td>
                                            <td>{{ ucfirst($transaction->payment_method) }}</td>
                                            <td>
                                                <span class="badge {{ $transaction->status == 'success' ? 'bg-success' : 'bg-danger' }}">
                                                    {{ ucfirst($transaction->status) }}
                                                </span>
                                            </td>
                                            <td>{{ $transaction->created_at ? $transaction->created_at->format('M d, Y h:i A') : 'N/A' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Order Notes Card -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0"><i class="bi bi-chat-text"></i> Order Notes</h5>
                            @can('update orders')
                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addNoteModal">
                                <i class="bi bi-plus-circle"></i> Add Note
                            </button>
                            @endcan
                        </div>
                        <div class="card-body">
                            @if($order->notes && $order->notes->count())
                                <div class="timeline">
                                    @foreach($order->notes as $note)
                                    <div class="border-start border-primary border-3 ps-3 mb-3 pb-2">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <strong>{{ $note->user?->name ?? $note->user?->first_name ?? auth()->user()->name ?? 'System' }}</strong>
                                                <span class="text-muted ms-2 small">{{ $note->created_at ? $note->created_at->diffForHumans() : 'N/A' }}</span>
                                            </div>
                                            @if($note->is_customer_visible)
                                                <span class="badge bg-info-subtle text-info">Visible to customer</span>
                                            @endif
                                        </div>
                                        <p class="mb-0 mt-2">{{ $note->note }}</p>
                                    </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center text-muted py-4">
                                    <i class="bi bi-chat-square-text fs-1 d-block mb-2"></i>
                                    <p>No notes yet. Click "Add Note" to add one.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Note Modal -->
            <div class="modal fade" id="addNoteModal" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form action="{{ route('orders.note', $order->id) }}" method="POST">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Add Order Note</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Note</label>
                                    <textarea name="note" class="form-control" rows="4" required placeholder="Enter your note here..."></textarea>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_customer_visible" id="visibleCheck" value="1">
                                    <label class="form-check-label" for="visibleCheck">
                                        <i class="bi bi-eye"></i> Visible to customer
                                    </label>
                                    <div class="form-text">If checked, this note will be visible to the customer in their account.</div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Note</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Status update functionality
    @can('update orders')
    document.querySelectorAll('.status-select').forEach(el => {
        el.addEventListener('change', function() {
            const originalValue = this.value;
            const orderId = this.dataset.id;

            Swal.fire({
                title: 'Update Status?',
                text: 'Are you sure you want to change this order status?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'Yes, update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('/orders/' + orderId + '/status', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ status: this.value })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Updated!', 'Order status has been updated.', 'success');
                            setTimeout(function() { location.reload(); }, 1500);
                        } else {
                            Swal.fire('Error!', data.message || 'Failed to update status.', 'error');
                            this.value = originalValue;
                        }
                    })
                    .catch((error) => {
                        console.error('Error:', error);
                        Swal.fire('Error!', 'Something went wrong.', 'error');
                        this.value = originalValue;
                    });
                } else {
                    this.value = originalValue;
                }
            }.bind(this));
        });
    });
    @endcan

    // Email invoice function
    window.emailInvoice = function(orderId) {
        Swal.fire({
            title: 'Sending Invoice...',
            text: 'Please wait while we send the invoice to the customer',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
                fetch('/orders/' + orderId + '/email-invoice', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Sent!', 'Invoice has been emailed to the customer.', 'success');
                    } else {
                        Swal.fire('Error!', data.message || 'Failed to send email.', 'error');
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    Swal.fire('Error!', 'Something went wrong.', 'error');
                });
            }
        });
    };
});
</script>

<style>
/* Additional styles for better order display */
.card {
    margin-bottom: 1.5rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.avatar-lg {
    width: 4rem;
    height: 4rem;
}
.avatar-sm {
    width: 2rem;
    height: 2rem;
}
.avatar-title {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.table-light th {
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
}
.timeline {
    max-height: 400px;
    overflow-y: auto;
}
.badge {
    font-weight: 500;
    padding: 0.35em 0.65em;
}
.btn-group .btn-check:checked + .btn {
    background-color: #0d6efd;
    color: white;
    border-color: #0d6efd;
}
.status-select {
    min-width: 150px;
}
@media (max-width: 768px) {
    .table-responsive {
        font-size: 0.85rem;
    }
    .btn-group {
        flex-wrap: wrap;
    }
}
</style>
@endsection
