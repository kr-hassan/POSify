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
        <h2>REPAIR RETURN RECEIPT</h2>
        <p>Claim No: {{ $repair->claim_no }}</p>
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
        <p>Thank you for your patience!</p>
        <p>Please keep this receipt for your records.</p>
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

