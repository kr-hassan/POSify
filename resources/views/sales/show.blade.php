@extends('layouts.app')

@section('title', 'Sale Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Sale Details - {{ $sale->invoice_no }}</h2>
        <a href="{{ route('sales.invoice', $sale) }}" class="btn btn-info" target="_blank">
            <i class="bi bi-printer"></i> Print Invoice
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->saleItems as $item)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->price, 2) }}</td>
                                    <td>${{ number_format($item->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Sale Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Date:</strong> {{ $sale->sale_date->format('M d, Y') }}</p>
                    <p><strong>Cashier:</strong> {{ $sale->user->name }}</p>
                    <p><strong>Customer:</strong> {{ $sale->customer->name ?? 'Walk-in' }}</p>
                    <hr>
                    <p><strong>Subtotal:</strong> ${{ number_format($sale->total_amount + $sale->discount - $sale->tax, 2) }}</p>
                    @if($sale->discount > 0)
                    <p><strong>Discount:</strong> -${{ number_format($sale->discount, 2) }}</p>
                    @endif
                    @if($sale->tax > 0)
                    <p><strong>Tax:</strong> ${{ number_format($sale->tax, 2) }}</p>
                    @endif
                    <p><strong>Total:</strong> ${{ number_format($sale->total_amount, 2) }}</p>
                    <p><strong>Paid:</strong> ${{ number_format($sale->paid_amount, 2) }}</p>
                    @if($sale->due_amount > 0)
                    <p><strong>Due:</strong> <span class="text-danger">-${{ number_format($sale->due_amount, 2) }}</span></p>
                    @endif
                    <p><strong>Payment Method:</strong> {{ ucfirst($sale->payment_method) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


