@php
    $shopSettings = \App\Models\Setting::getShopSettings();
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt {{ $payment->receipt_no }}</title>
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
        .amount {
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            margin: 15px 0;
            padding: 10px;
            border: 2px solid #000;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
            border-top: 1px dashed #000;
            padding-top: 10px;
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
        <h3 style="margin-top: 10px; margin-bottom: 5px;">PAYMENT RECEIPT</h3>
        <p style="margin-top: 0;">Receipt No: {{ $payment->receipt_no }}</p>
    </div>
    
    <div class="info">
        <p><strong>Date:</strong> {{ $payment->payment_date->format('Y-m-d H:i') }}</p>
        <p><strong>Customer:</strong> {{ $payment->customer->name }}</p>
        <p><strong>Received By:</strong> {{ $payment->user->name }}</p>
        <p><strong>Payment Method:</strong> {{ ucfirst($payment->payment_method) }}</p>
        @if($payment->note)
        <p><strong>Note:</strong> {{ $payment->note }}</p>
        @endif
    </div>
    
    <div class="amount">
        Amount Received<br>
        ${{ number_format($payment->amount, 2) }}
    </div>
    
    <div class="info">
        <p><strong>Customer Balance After Payment:</strong></p>
        <p style="font-size: 14px;">
            @if($payment->customer->balance > 0)
                <span style="color: red;">-${{ number_format($payment->customer->balance, 2) }}</span>
            @else
                <span style="color: green;">$0.00 (Paid)</span>
            @endif
        </p>
    </div>
    
    <div class="footer">
        <p>{{ $shopSettings['footer_message'] ?? 'Thank you for your business!' }}</p>
        <p>This is a computer-generated receipt.</p>
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
            window.print();
        };
    </script>
</body>
</html>



