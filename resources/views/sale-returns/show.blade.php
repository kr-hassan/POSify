@extends('layouts.app')

@section('title', 'Return Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Return Details - {{ $saleReturn->return_no }}</h2>
        <a href="{{ route('sale-returns.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Returned Items</h5>
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
                                @foreach($saleReturn->saleReturnItems as $item)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->price, 2) }}</td>
                                    <td>${{ number_format($item->total, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right">Total:</th>
                                    <th>${{ number_format($saleReturn->total_amount, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Return Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Return No:</strong> {{ $saleReturn->return_no }}</p>
                    <p><strong>Date:</strong> {{ $saleReturn->return_date->format('M d, Y') }}</p>
                    <p><strong>Sale Invoice:</strong> 
                        <a href="{{ route('sales.show', $saleReturn->sale) }}">{{ $saleReturn->sale->invoice_no }}</a>
                    </p>
                    <p><strong>Customer:</strong> {{ $saleReturn->customer->name ?? 'Walk-in' }}</p>
                    <p><strong>Processed By:</strong> {{ $saleReturn->user->name }}</p>
                    <hr>
                    <p><strong>Total Amount:</strong> ${{ number_format($saleReturn->total_amount, 2) }}</p>
                    @if($saleReturn->reason)
                    <hr>
                    <p><strong>Reason:</strong></p>
                    <p>{{ $saleReturn->reason }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

