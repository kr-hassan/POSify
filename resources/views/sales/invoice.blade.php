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
        <h2>POS SYSTEM</h2>
        <p>Invoice: {{ $sale->invoice_no }}</p>
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
        <p>Thank you for your business!</p>
    </div>
    
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


