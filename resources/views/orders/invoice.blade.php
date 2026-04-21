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
        .status-pending {
            background: #fff3cd;
            color: #856404;
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
        @media print {
            body {
                padding: 0;
                margin: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="header">
            @if(file_exists(public_path('img/logo.png')))
                <img src="{{ public_path('img/logo.png') }}" class="logo" alt="Logo">
            @else
                <h1>{{ config('app.name', 'Store Name') }}</h1>
            @endif
            <div class="company-info">
                {{ config('app.url') }} | support@yourstore.com | +1 234 567 890
            </div>
        </div>

        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell">
                    <strong>Invoice #:</strong> {{ $order->invoice_number ?? substr($order->id, 0, 10) }}<br>
                    <strong>Order Date:</strong> {{ $order->created_at ? $order->created_at->format('d M Y') : 'N/A' }}<br>
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
                            $billingName = $order->billingAddress ? ($order->billingAddress->name ?? 'N/A') : ($order->customer ? $order->customer->first_name . ' ' . $order->customer->last_name : ($order->user ? $order->user->first_name . ' ' . $order->user->last_name : 'N/A'));
                            $billingStreet = $order->billingAddress ? ($order->billingAddress->street ?? $order->billingAddress->address ?? 'N/A') : 'N/A';
                            $billingCity = $order->billingAddress ? ($order->billingAddress->city ?? 'N/A') : 'N/A';
                            $billingCountry = $order->billingAddress ? ($order->billingAddress->country ?? 'N/A') : 'N/A';
                        @endphp
                        {{ $billingName }}<br>
                        {{ $billingStreet }}<br>
                        {{ $billingCity }}, {{ $billingCountry }}<br>
                        <strong>Phone:</strong> {{ $order->billingAddress->phone_number ?? ($order->customer->phone_number ?? 'N/A') }}
                    </div>
                </div>
                <div class="info-cell">
                    <strong>Ship To:</strong>
                    <div class="address-box">
                        @php
                            $shippingName = $order->shippingAddress ? ($order->shippingAddress->name ?? 'N/A') : ($order->customer ? $order->customer->first_name . ' ' . $order->customer->last_name : ($order->user ? $order->user->first_name . ' ' . $order->user->last_name : 'N/A'));
                            $shippingStreet = $order->shippingAddress ? ($order->shippingAddress->street ?? $order->shippingAddress->address ?? 'N/A') : 'N/A';
                            $shippingCity = $order->shippingAddress ? ($order->shippingAddress->city ?? 'N/A') : 'N/A';
                            $shippingCountry = $order->shippingAddress ? ($order->shippingAddress->country ?? 'N/A') : 'N/A';
                        @endphp
                        {{ $shippingName }}<br>
                        {{ $shippingStreet }}<br>
                        {{ $shippingCity }}, {{ $shippingCountry }}<br>
                        <strong>Phone:</strong> {{ $order->shippingAddress->phone_number ?? ($order->customer->phone_number ?? 'N/A') }}
                    </div>
                </div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Product</th>
                    <th>Variation</th>
                    <th class="text-right">Qty</th>
                    <th class="text-right">Unit Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->title ?? 'N/A' }}</td>
                    <td>
                        @if($item->selected_variation)
                            @php
                                $attrs = is_string($item->selected_variation) ? json_decode($item->selected_variation, true) : $item->selected_variation;
                            @endphp
                            @if(is_array($attrs))
                                @foreach($attrs as $key => $val)
                                    <strong>{{ ucfirst($key) }}:</strong> {{ $val }}<br>
                                @endforeach
                            @else
                                {{ $item->selected_variation }}
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($item->quantity ?? 0) }}</td>
                    <td class="text-right">${{ number_format($item->unit_price ?? 0, 2) }}</td>
                    <td class="text-right">${{ number_format($item->total_price ?? ($item->unit_price * $item->quantity), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <table style="width: 50%; margin-left: auto;">
            <tr>
                <td style="border: none;"><strong>Subtotal</strong></td>
                <td style="border: none;" class="text-right">${{ number_format($order->items->sum('total_price'), 2) }}</td>
            </tr>
            <tr>
                <td style="border: none;"><strong>Shipping</strong></td>
                <td style="border: none;" class="text-right">${{ number_format($order->shipping_cost ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td style="border: none;"><strong>Tax</strong></td>
                <td style="border: none;" class="text-right">${{ number_format($order->tax_cost ?? 0, 2) }}</td>
            </tr>
            @if($order->totalRefunded() > 0)
            <tr>
                <td style="border: none;"><strong class="text-danger">Refunded</strong></td>
                <td style="border: none;" class="text-right text-danger">-${{ number_format($order->totalRefunded(), 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td style="border: none;"><strong>Grand Total</strong></td>
                <td style="border: none;" class="text-right"><strong class="amount">${{ number_format($order->total_amount - $order->totalRefunded(), 2) }}</strong></td>
            </tr>
        </table>

        <div class="footer">
            <p>Thank you for your business!</p>
            <p>For any inquiries, please contact our support team.</p>
        </div>
    </div>
</body>
</html>
