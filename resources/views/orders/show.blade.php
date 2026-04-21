<!-- Order Items -->
<div class="card">
    <div class="card-header">
        <h5>Items ({{ $order->items->count() }})</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->items as $item)
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                @if($item->image)
                                    <img src="{{ asset('storage/' . $item->image) }}" class="rounded me-3" style="width:50px;height:50px;object-fit:cover;">
                                @endif
                                <div>
                                    <strong>{{ $item->title ?? 'N/A' }}</strong>
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
                                </div>
                            </div>
                        </td>
                        <td>{{ $item->quantity ?? 0 }}</td>
                        <td class="text-success fw-bold">${{ number_format($item->unit_price ?? 0, 2) }}</td>
                        <td class="text-success fw-bold">${{ number_format($item->total_price ?? ($item->unit_price * $item->quantity), 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">
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
