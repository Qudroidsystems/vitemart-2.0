<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $order->invoice_number ?? substr($order->id, 0, 10) }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', 'Segoe UI', Arial, sans-serif;
            margin: 0;
            padding: 40px;
            color: #333;
            line-height: 1.6;
            background: #fff;
        }
        .invoice-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 20px;
        }
        .logo {
            max-height: 80px;
            margin-bottom: 10px;
        }
        h1 {
            margin: 0;
            color: #0d6efd;
            font-size: 28px;
        }
        .company-info {
            color: #666;
            font-size: 12px;
            margin-top: 5px;
        }
        .info-grid {
            display: table;
            width: 100%;
            margin: 30px 0;
        }
        .info-row {
            display: table-row;
        }
        .info-cell {
            display: table-cell;
            padding: 8px 0;
            width: 50%;
            vertical-align: top;
        }
        .info-cell strong {
            display: inline-block;
            width: 100px;
            font-weight: 600;
        }
        .text-right {
            text-align: right;
        }
        .invoice-title {
            font-size: 32px;
            font-weight: bold;
            color: #0d6efd;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        .text-right {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 60px;
            text-align: center;
            color: #666;
            font-size: 12px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .badge {
            padding: 6px 12px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
        }
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        .status-unpaid {
            background: #f8d7da;
            color: #721c24;
        }
        .amount {
            font-weight: bold;
            color: #28a745;
        }
        .address-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 10px;
        }
        .discount-text {
            color: #dc3545;
        }
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            @php
                $storeSettings = \App\Models\StoreSetting::getSettings();
                $storeName = $storeSettings->store_name ?? config('app.name', 'Store Name');
                $storeAddress = $storeSettings->address ?? '';
                $storePhone = $storeSettings->phone ?? '';
                $storeEmail = $storeSettings->email ?? '';
                $storeWebsite = $storeSettings->website ?? '';
                $currencySymbol = $storeSettings->currency_symbol ?? '₦';
            @endphp

            @if($storeSettings && $storeSettings->logo)
                <img src="{{ public_path('storage/' . $storeSettings->logo) }}" class="logo" alt="Logo" style="max-height:80px;">
            @else
                <h1>{{ $storeName }}</h1>
            @endif
            <div class="company-info">
                {{ $storeAddress }}<br>
                {{ $storePhone ? "Tel: $storePhone | " : '' }}{{ $storeEmail ? "Email: $storeEmail | " : '' }}{{ $storeWebsite ? "Web: $storeWebsite" : '' }}
            </div>
        </div>

        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell">
                    <strong>Invoice #:</strong> {{ $order->invoice_number ?? substr($order->id, 0, 10) }}<br>
                    <strong>Order Date:</strong> {{ $order->created_at ? $order->created_at->format('d M Y h:i A') : 'N/A' }}<br>
                    <strong>Payment Method:</strong> {{ ucfirst(str_replace('_', ' ', $order->payment_method ?? 'N/A')) }}
                </div>
                <div class="info-cell text-right">
                    <div class="badge
                        @if($order->payment_status == 'paid') status-paid
                        @elseif($order->payment_status == 'unpaid') status-unpaid
                        @else status-pending
                        @endif">
                        {{ ucfirst($order->payment_status ?? 'Pending') }}
                    </div>
                    <div class="invoice-title">INVOICE</div>
                </div>
            </div>
        </div>

        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell">
                    <strong>Bill To:</strong>
                    <div class="address-box">
                        @php
                            if ($order->customer) {
                                $customerName = $order->customer->first_name . ' ' . $order->customer->last_name;
                                $customerEmail = $order->customer->email ?? 'N/A';
                                $customerPhone = $order->customer->phone_number ?? 'N/A';
                                $customerAddress = $order->customer->home_address ?? $order->customer->office_address ?? '';
                            } else {
                                $customerName = $order->user ? ($order->user->first_name . ' ' . $order->user->last_name) : 'Walk-in Customer';
                                $customerEmail = $order->user->email ?? 'N/A';
                                $customerPhone = $order->user->phone_number ?? 'N/A';
                                $customerAddress = '';
                            }
                        @endphp
                        <strong>{{ $customerName }}</strong><br>
                        @if($customerAddress)
                            {{ $customerAddress }}<br>
                        @endif
                        @if($customerEmail && $customerEmail != 'N/A')
                            Email: {{ $customerEmail }}<br>
                        @endif
                        @if($customerPhone && $customerPhone != 'N/A')
                            Phone: {{ $customerPhone }}
                        @endif
                        @if(!$customerAddress && $customerEmail == 'N/A' && $customerPhone == 'N/A')
                            Walk-in Customer
                        @endif
                    </div>
                </div>
                <div class="info-cell">
                    <strong>Ship To:</strong>
                    <div class="address-box">
                        @if($order->shippingAddress)
                            {{ $order->shippingAddress->name ?? $customerName }}<br>
                            {{ $order->shippingAddress->address ?? $order->shippingAddress->street ?? 'N/A' }}<br>
                            @if($order->shippingAddress->city){{ $order->shippingAddress->city }}, @endif
                            @if($order->shippingAddress->state){{ $order->shippingAddress->state }}, @endif
                            @if($order->shippingAddress->country){{ $order->shippingAddress->country }} @endif
                            @if($order->shippingAddress->phone_number)<br>Phone: {{ $order->shippingAddress->phone_number }}@endif
                        @else
                            Same as billing address
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Unit</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        {{ $item->title ?? 'N/A' }}
                        @if($item->discount_amount > 0)
                            <br><small class="discount-text">Discount: -{{ $currencySymbol }}{{ number_format($item->discount_amount, 2) }}</small>
                        @endif
                    </td>
                    <td>
                        @if($item->unit_name)
                            {{ $item->unit_name }}
                        @elseif($item->unit)
                            {{ $item->unit->name }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($item->quantity ?? 0, $item->is_unit_mode ? 3 : 0) }}</td>
                    <td class="text-right">
                        @if($item->discount_amount > 0)
                            <del>{{ $currencySymbol }}{{ number_format($item->unit_price ?? 0, 2) }}</del><br>
                            <span class="text-success">{{ $currencySymbol }}{{ number_format($item->discounted_price, 2) }}</span>
                        @else
                            {{ $currencySymbol }}{{ number_format($item->unit_price ?? 0, 2) }}
                        @endif
                    </td>
                    <td class="text-right">
                        @if($item->discount_amount > 0)
                            <del>{{ $currencySymbol }}{{ number_format($item->total_price ?? ($item->unit_price * $item->quantity), 2) }}</del><br>
                            <span class="text-success">{{ $currencySymbol }}{{ number_format(($item->discounted_price * $item->quantity), 2) }}</span>
                        @else
                            {{ $currencySymbol }}{{ number_format($item->total_price ?? ($item->unit_price * $item->quantity), 2) }}
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table style="width: 50%; margin-left: auto;">
            @php
                $subtotal = $order->subtotal ?? $order->items->sum('total_price');
                $discount = $order->discount_amount ?? 0;
            @endphp

            @if($subtotal > 0)
            <tr>
                <td style="border: none;"><strong>Subtotal</strong></td>
                <td style="border: none;" class="text-right">{{ $currencySymbol }}{{ number_format($subtotal, 2) }}</td>
            </tr>
            @endif

            @if($discount > 0)
            <tr>
                <td style="border: none;"><strong>Discount</strong></td>
                <td style="border: none;" class="text-right discount-text">-{{ $currencySymbol }}{{ number_format($discount, 2) }}</td>
            </tr>
            @endif

            @if($order->shipping_cost > 0)
            <tr>
                <td style="border: none;"><strong>Shipping</strong></td>
                <td style="border: none;" class="text-right">{{ $currencySymbol }}{{ number_format($order->shipping_cost, 2) }}</td>
            </tr>
            @endif

            @if($order->tax_cost > 0)
            <tr>
                <td style="border: none;"><strong>Tax</strong></td>
                <td style="border: none;" class="text-right">{{ $currencySymbol }}{{ number_format($order->tax_cost, 2) }}</td>
            </tr>
            @endif

            @if($order->totalRefunded() > 0)
            <tr>
                <td style="border: none;"><strong class="text-danger">Refunded</strong></td>
                <td style="border: none;" class="text-right text-danger">-{{ $currencySymbol }}{{ number_format($order->totalRefunded(), 2) }}</td>
            </tr>
            @endif

            <tr class="total-row">
                <td style="border: none;"><strong>Grand Total</strong></td>
                <td style="border: none;" class="text-right"><strong class="amount">{{ $currencySymbol }}{{ number_format($order->total_amount - $order->totalRefunded(), 2) }}</strong></td>
            </tr>
        </table>

        <div class="footer">
            <p>{{ $storeSettings->footer_note ?? 'Thank you for your business!' }}</p>
            <p>For any inquiries, please contact our support team.</p>
        </div>
    </div>
</body>
</html>
