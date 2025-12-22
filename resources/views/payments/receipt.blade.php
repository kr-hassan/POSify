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
        <h2>PAYMENT RECEIPT</h2>
        <p>Receipt No: {{ $payment->receipt_no }}</p>
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
        <p>Thank you for your payment!</p>
        <p>This is a computer-generated receipt.</p>
    </div>
    
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>


