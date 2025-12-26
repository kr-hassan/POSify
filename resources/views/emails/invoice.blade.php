<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $sale->invoice_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .shop-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .shop-info {
            font-size: 12px;
            color: #666;
            line-height: 1.8;
        }
        .invoice-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .invoice-header h2 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            padding: 5px 0;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        .info-value {
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .text-right {
            text-align: right;
        }
        .totals {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #ddd;
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            font-size: 14px;
        }
        .total-row.final {
            font-size: 18px;
            font-weight: bold;
            color: #007bff;
            border-top: 2px solid #007bff;
            padding-top: 15px;
            margin-top: 10px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 12px;
            color: #666;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="shop-name">{{ $shopSettings['shop_name'] ?? 'Our Store' }}</div>
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
        </div>

        <div class="invoice-header">
            <h2>Invoice: {{ $sale->invoice_no }}</h2>
            <div class="info-row">
                <span class="info-label">Date:</span>
                <span class="info-value">{{ $sale->sale_date->format('F d, Y h:i A') }}</span>
            </div>
            @if($sale->customer)
            <div class="info-row">
                <span class="info-label">Customer:</span>
                <span class="info-value">{{ $sale->customer->name }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="info-label">Cashier:</span>
                <span class="info-value">{{ $sale->user->name }}</span>
            </div>
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

        <div class="totals">
            <div class="total-row">
                <span>Subtotal:</span>
                <span>${{ number_format($sale->total_amount + $sale->discount - $sale->tax, 2) }}</span>
            </div>
            @if($sale->discount > 0)
            <div class="total-row">
                <span>Discount:</span>
                <span>-${{ number_format($sale->discount, 2) }}</span>
            </div>
            @endif
            @if($sale->tax > 0)
            <div class="total-row">
                <span>Tax:</span>
                <span>${{ number_format($sale->tax, 2) }}</span>
            </div>
            @endif
            <div class="total-row final">
                <span>Total:</span>
                <span>${{ number_format($sale->total_amount, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Paid:</span>
                <span>${{ number_format($sale->paid_amount, 2) }}</span>
            </div>
            @if($sale->due_amount > 0)
            <div class="total-row">
                <span>Due:</span>
                <span>${{ number_format($sale->due_amount, 2) }}</span>
            </div>
            @endif
            <div class="total-row">
                <span>Change:</span>
                <span>${{ number_format($sale->paid_amount - $sale->total_amount, 2) }}</span>
            </div>
            <div class="total-row">
                <span>Payment Method:</span>
                <span>{{ ucfirst($sale->payment_method) }}</span>
            </div>
        </div>

        <div class="footer">
            <p>{{ $shopSettings['footer_message'] ?? 'Thank you for your business!' }}</p>
            <p style="margin-top: 15px; font-size: 11px; color: #999;">
                This is an automated email. Please do not reply to this message.
            </p>
        </div>
    </div>
</body>
</html>


