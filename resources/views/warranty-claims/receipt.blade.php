@php
    $shopSettings = \App\Models\Setting::getShopSettings();
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Warranty Claim Receipt {{ $warrantyClaim->claim_no }}</title>
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
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 8px;
            margin: 10px 0;
            font-size: 10px;
            text-align: center;
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
        <h3 style="margin-top: 10px; margin-bottom: 5px;">WARRANTY CLAIM RECEIPT</h3>
        <p style="margin-top: 0;">Claim No: {{ $warrantyClaim->claim_no }}</p>
    </div>
    
    <div class="info">
        <p><strong>Date:</strong> {{ $warrantyClaim->claim_date->format('Y-m-d H:i') }}</p>
        <p><strong>Customer:</strong> {{ $warrantyClaim->warranty->customer->name ?? 'Walk-in' }}</p>
        <p><strong>Product:</strong> {{ $warrantyClaim->warranty->product->name }}</p>
        <p><strong>Warranty No:</strong> {{ $warrantyClaim->warranty->warranty_no }}</p>
        <p><strong>Sale Invoice:</strong> {{ $warrantyClaim->warranty->sale->invoice_no }}</p>
        <p><strong>Claim Type:</strong> {{ ucfirst($warrantyClaim->claim_type) }}</p>
        <p><strong>Status:</strong> {{ ucfirst($warrantyClaim->status) }}</p>
    </div>
    
    <div class="info">
        <p><strong>Issue Description:</strong></p>
        <p style="font-size: 11px;">{{ $warrantyClaim->issue_description }}</p>
    </div>
    
    <div class="warning">
        <strong>⚠️ IMPORTANT</strong><br>
        Please keep this receipt safe.<br>
        You will need to present this receipt<br>
        when collecting your product after<br>
        repair/replacement.
    </div>
    
    <div class="info">
        <p><strong>Received By:</strong> {{ $warrantyClaim->user->name }}</p>
        <p><strong>Warranty Period:</strong> {{ $warrantyClaim->warranty->warranty_period_months }} months</p>
        <p><strong>Warranty Valid Until:</strong> {{ $warrantyClaim->warranty->end_date->format('M d, Y') }}</p>
    </div>
    
    <div class="footer">
        <p>{{ $shopSettings['footer_message'] ?? 'Thank you for your business!' }}</p>
        <p>We will contact you when your product is ready.</p>
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
            // Auto-print receipt when opened
            setTimeout(function() {
                window.print();
            }, 250);
        };
    </script>
</body>
</html>



