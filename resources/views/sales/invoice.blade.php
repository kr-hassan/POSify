@php
    $shopSettings = \App\Models\Setting::getShopSettings();
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $sale->invoice_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            width: 80mm;
            margin: 0;
            padding: 10px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }
        .shop-info {
            font-size: 10px;
            margin-top: 5px;
            line-height: 1.4;
        }
        .info {
            margin-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        th, td {
            padding: 5px;
            text-align: left;
        }
        th {
            border-bottom: 1px solid #000;
        }
        .text-right {
            text-align: right;
        }
        .total {
            border-top: 1px dashed #000;
            padding-top: 5px;
            margin-top: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
        }
        .advertisement {
            text-align: center;
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px dashed #ccc;
            font-size: 9px;
            color: #666;
            line-height: 1.4;
        }
        .advertisement strong {
            color: #333;
        }
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>{{ $shopSettings['shop_name'] }}</h2>
        <div class="shop-info">
            @if($shopSettings['shop_address'])
                <div>{{ $shopSettings['shop_address'] }}</div>
            @endif
            @if($shopSettings['shop_phone'])
                <div>Phone: {{ $shopSettings['shop_phone'] }}</div>
            @endif
            @if($shopSettings['shop_email'])
                <div>Email: {{ $shopSettings['shop_email'] }}</div>
            @endif
        </div>
        <p style="margin-top: 10px; margin-bottom: 0;">Invoice: {{ $sale->invoice_no }}</p>
    </div>
    
    <div class="info">
        <p><strong>Date:</strong> {{ $sale->sale_date->format('Y-m-d H:i') }}</p>
        @if($sale->customer)
        <p><strong>Customer:</strong> {{ $sale->customer->name }}</p>
        @endif
        <p><strong>Cashier:</strong> {{ $sale->user->name }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($sale->saleItems as $item)
            <tr>
                <td>{{ $item->product->name }}</td>
                <td class="text-right">{{ $item->quantity }}</td>
                <td class="text-right">${{ number_format($item->price, 2) }}</td>
                <td class="text-right">${{ number_format($item->total, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <div class="total">
        <p class="text-right">Subtotal: ${{ number_format($sale->total_amount + $sale->discount - $sale->tax, 2) }}</p>
        @if($sale->discount > 0)
        <p class="text-right">Discount: -${{ number_format($sale->discount, 2) }}</p>
        @endif
        @if($sale->tax > 0)
        <p class="text-right">Tax: ${{ number_format($sale->tax, 2) }}</p>
        @endif
        <p class="text-right"><strong>Total: ${{ number_format($sale->total_amount, 2) }}</strong></p>
        <p class="text-right">Paid: ${{ number_format($sale->paid_amount, 2) }}</p>
        @if($sale->due_amount > 0)
        <p class="text-right">Due: -${{ number_format($sale->due_amount, 2) }}</p>
        @endif
        <p class="text-right">Change: ${{ number_format($sale->paid_amount - $sale->total_amount, 2) }}</p>
        <p class="text-right">Payment: {{ ucfirst($sale->payment_method) }}</p>
    </div>
    
    <div class="footer">
        <p>{{ $shopSettings['footer_message'] ?? 'Thank you for your business!' }}</p>
    </div>
    
    @if(($shopSettings['show_advertisement'] ?? '0') == '1' && ($shopSettings['software_company_name'] ?? ''))
    <div class="advertisement">
        @if($shopSettings['software_company_tagline'] ?? '')
            <div>{{ $shopSettings['software_company_tagline'] }}</div>
        @endif
        <div>
            <strong>{{ $shopSettings['software_company_name'] }}</strong>
            @if($shopSettings['software_company_website'] ?? '')
                <br>{{ $shopSettings['software_company_website'] }}
            @endif
        </div>
    </div>
    @endif
    
    <script>
        window.onload = function() {
            // Auto-print receipt/invoice every time, regardless of due amount
            setTimeout(function() {
                window.print();
            }, 250);
        };
    </script>
</body>
</html>


