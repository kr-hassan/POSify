@php
    $shopSettings = \App\Models\Setting::getShopSettings();
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Repair Return Receipt {{ $repair->claim_no }}</title>
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
        .success {
            background: #d4edda;
            border: 1px solid #28a745;
            padding: 8px;
            margin: 10px 0;
            font-size: 11px;
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
        <h3 style="margin-top: 10px; margin-bottom: 5px;">REPAIR RETURN RECEIPT</h3>
        <p style="margin-top: 0;">Claim No: {{ $repair->claim_no }}</p>
    </div>
    
    <div class="info">
        <p><strong>Return Date:</strong> {{ $repair->returned_date->format('Y-m-d H:i') }}</p>
        <p><strong>Customer:</strong> {{ $repair->warranty->customer->name ?? 'Walk-in' }}</p>
        <p><strong>Product:</strong> {{ $repair->warranty->product->name }}</p>
        <p><strong>Warranty No:</strong> {{ $repair->warranty->warranty_no }}</p>
        <p><strong>Sale Invoice:</strong> {{ $repair->warranty->sale->invoice_no }}</p>
    </div>
    
    <div class="info">
        <p><strong>Repair Details:</strong></p>
        <p style="font-size: 11px;">
            <strong>Claim Date:</strong> {{ $repair->claim_date->format('M d, Y') }}<br>
            @if($repair->received_date)
            <strong>Received:</strong> {{ $repair->received_date->format('M d, Y') }}<br>
            @endif
            @if($repair->resolved_date)
            <strong>Completed:</strong> {{ $repair->resolved_date->format('M d, Y') }}<br>
            @endif
            <strong>Returned:</strong> {{ $repair->returned_date->format('M d, Y') }}
        </p>
    </div>
    
    @if($repair->issue_description)
    <div class="info">
        <p><strong>Issue Reported:</strong></p>
        <p style="font-size: 11px;">{{ $repair->issue_description }}</p>
    </div>
    @endif
    
    @if($repair->resolution_notes)
    <div class="info">
        <p><strong>Repair Resolution:</strong></p>
        <p style="font-size: 11px;">{{ $repair->resolution_notes }}</p>
    </div>
    @endif
    
    <div class="success">
        <strong>✅ PRODUCT RETURNED</strong><br>
        Your product has been successfully<br>
        repaired and returned to you.<br>
        Please verify the product condition<br>
        before leaving the store.
    </div>
    
    <div class="info">
        <p><strong>Returned By:</strong> {{ auth()->user()->name }}</p>
        <p><strong>Warranty Status:</strong> 
            @if($repair->warranty->is_active)
                Active (Valid until {{ $repair->warranty->end_date->format('M d, Y') }})
            @else
                Expired
            @endif
        </p>
    </div>
    
    <div class="footer">
        <p>{{ $shopSettings['footer_message'] ?? 'Thank you for your business!' }}</p>
        <p>Please keep this receipt for your records.</p>
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


