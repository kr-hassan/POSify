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
        <h2>WARRANTY CLAIM RECEIPT</h2>
        <p>Claim No: {{ $warrantyClaim->claim_no }}</p>
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
        <p>Thank you for your patience!</p>
        <p>We will contact you when your product is ready.</p>
        <p>This is a computer-generated receipt.</p>
    </div>
    
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


