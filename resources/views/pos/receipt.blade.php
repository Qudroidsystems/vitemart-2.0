<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt - {{ $order->id }}</title>

    <!-- JsBarcode -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jsbarcode/3.12.1/JsBarcode.all.min.js"></script>

    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
            background: #f5f5f5;
            height: 100vh;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            overflow: hidden;
        }

        /* Fake Printer Slot (visible only on screen) */
        .printer-slot {
            width: 100%;
            max-width: 80mm;
            height: 25px;
            background: #222;
            border-radius: 12px 12px 0 0;
            margin: 40px auto 0;
            position: relative;
            box-shadow: 0 5px 15px rgba(0,0,0,0.4);
        }

        .printer-slot::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 5%;
            right: 5%;
            height: 12px;
            background: #111;
            border-radius: 0 0 12px 12px;
        }

        /* Receipt Paper Animation */
        .receipt-paper {
            width: 80mm;
            background: white;
            padding: 20px 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            border-radius: 0 0 8px 8px;
            animation: emerge 3.5s ease-out forwards;
            transform: translateY(-120%);
            margin-top: -20px;
        }

        @keyframes emerge {
            0% { transform: translateY(-120%); }
            60% { transform: translateY(15px); }
            100% { transform: translateY(0); }
        }

        .receipt {
            width: 100%;
            text-align: center;
        }

        /* Standard receipt styles */
        .header h1 { font-size: 18px; margin: 5px 0; font-weight: bold; }
        .motto { font-size: 11px; font-style: italic; margin: 5px 0; }
        .logo img { max-width: 60mm; max-height: 30mm; margin: 10px 0; }
        .header p { margin: 3px 0; font-size: 11px; }
        .divider { border-top: 1px dashed #000; margin: 12px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 11px; }
        table th { text-align: left; padding: 4px 0; border-bottom: 1px dashed #000; }
        table td { padding: 4px 0; }
        .text-right { text-align: right; }
        .total-row { font-weight: bold; font-size: 14px; border-top: 1px dashed #000; padding-top: 8px; margin-top: 8px; }
        .footer { margin-top: 20px; font-size: 11px; }
        .barcode { margin: 20px 0; }
        .barcode svg { width: 100%; height: 60px; }
        .barcode-text { font-size: 10px; margin-top: 5px; }

        /* === CRITICAL: Hide animation & printer on actual print === */
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
            }
            .printer-slot { display: none !important; }
            .receipt-paper {
                animation: none !important;
                transform: translateY(0) !important;
                box-shadow: none !important;
                padding: 10px;
                margin: 0;
                border-radius: 0;
            }
        }
    </style>
</head>
<body onload="window.print(); window.onafterprint = function(){ window.close(); }">

@php
    $store = \App\Models\StoreSetting::getSettings();
@endphp

<!-- Fake Printer (visible only on screen) -->
<div class="printer-slot"></div>

<!-- Animated Receipt Paper -->
<div class="receipt-paper">
    <div class="receipt">
        <!-- Store Header -->
        <div class="header">
            @if($store?->logo)
                <div class="logo">
                    <img src="{{ $store->getLogoUrlAttribute() }}" alt="Store Logo">
                </div>
            @endif
            <h1>{{ $store?->store_name ?? 'My Supermarket' }}</h1>
            @if($store?->motto)
                <p class="motto">{{ $store->motto }}</p>
            @endif
            @if($store?->address)
                <p>{{ $store->address }}</p>
            @endif
            @if($store?->phone)
                <p>Phone: {{ $store->phone }}</p>
            @endif
            @if($store?->email)
                <p>{{ $store->email }}</p>
            @endif
            @if($store?->tax_id)
                <p>Tax ID: {{ $store->tax_id }}</p>
            @endif
        </div>

        <div class="divider"></div>

        <!-- Order Info -->
        <div class="order-info" style="text-align:left;">
            <p><strong>Receipt No:</strong> {{ $order->id }}</p>
            <p><strong>Date:</strong> {{ $order->order_date->format('d M Y, h:i A') }}</p>
            <p><strong>Cashier:</strong> {{ auth()->user()->name ?? 'Admin' }}</p>
            @if($order->customer)
                <p><strong>Customer:</strong> {{ $order->customer->first_name }} {{ $order->customer->last_name }}</p>
                @if($order->customer->phone_number)
                    <p><strong>Phone:</strong> {{ $order->customer->phone_number }}</p>
                @endif
            @else
                <p><strong>Customer:</strong> Walk-in Customer</p>
            @endif
            <p><strong>Payment:</strong> {{ ucfirst($order->payment_method) }}</p>
        </div>

        <div class="divider"></div>

        <!-- Items -->
        <table>
            <thead>
                <tr>
                    <th style="text-align:left;">Item</th>
                    <th style="text-align:center;">Qty</th>
                    <th style="text-align:right;">Price</th>
                    <th style="text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td style="word-wrap:break-word;max-width:140px;">
                            {{ $item->title }}
                            @if($item->unit_name)
                                <br><small>{{ $item->quantity }} × {{ $item->unit_name }}</small>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">{{ $store?->currency_symbol ?? '$' }}{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-right">{{ $store?->currency_symbol ?? '$' }}{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="divider"></div>

        <!-- Total -->
        <table style="font-size:13px;">
            <tr class="total-row">
                <td style="text-align:left;"><strong>TOTAL</strong></td>
                <td style="text-align:right;"><strong>{{ $store?->currency_symbol ?? '$' }}{{ number_format($order->total_amount, 2) }}</strong></td>
            </tr>
        </table>

        <div class="divider"></div>

        <!-- Barcode -->
        <div class="barcode">
            <svg id="receipt-barcode"></svg>
            <div class="barcode-text">{{ $order->id }}</div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p><strong>{{ $store?->footer_note ?? 'Thank you for shopping with us!' }}</strong></p>
            <p>Goods sold are non-returnable</p>
            <p>Printed: {{ now()->format('d M Y h:i A') }}</p>
        </div>

        <div style="height:40px;"></div>
    </div>
</div>

<script>
    JsBarcode("#receipt-barcode", "{{ $order->id }}", {
        format: "CODE128",
        width: 2,
        height: 60,
        displayValue: false,
        margin: 10,
        flat: true
    });
</script>

</body>
</html>
