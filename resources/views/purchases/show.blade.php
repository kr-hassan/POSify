@extends('layouts.app')

@section('title', 'Purchase Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Purchase Details</h2>
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
                                    <th>Cost Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->purchaseItems as $item)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->cost_price, 2) }}</td>
                                    <td>${{ number_format($item->quantity * $item->cost_price, 2) }}</td>
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
                    <h5 class="mb-0">Purchase Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Date:</strong> {{ $purchase->purchase_date->format('M d, Y') }}</p>
                    <p><strong>Supplier:</strong> {{ $purchase->supplier->name }}</p>
                    <hr>
                    <p><strong>Total:</strong> ${{ number_format($purchase->total_amount, 2) }}</p>
                    <p><strong>Paid:</strong> ${{ number_format($purchase->paid_amount, 2) }}</p>
                    @if($purchase->due_amount > 0)
                    <p><strong>Due:</strong> <span class="text-warning">${{ number_format($purchase->due_amount, 2) }}</span></p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection






