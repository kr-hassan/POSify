@extends('layouts.app')

@section('title', 'Sale Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Sale Details - {{ $sale->invoice_no }}</h2>
        <div>
            @php
                $totalReturned = $sale->total_returned;
                $remainingAmount = $sale->total_amount - $totalReturned;
            @endphp
            @if($remainingAmount > 0)
            <a href="{{ route('sale-returns.create', $sale) }}" class="btn btn-warning">
                <i class="bi bi-arrow-return-left"></i> Return Products
            </a>
            @endif
            <a href="{{ route('sales.invoice', $sale) }}" class="btn btn-info" target="_blank">
                <i class="bi bi-printer"></i> Print Invoice
            </a>
        </div>
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
                                    <td>
                                        {{ $item->product->name }}
                                        @if($item->warranty)
                                            <br><small>
                                                <a href="{{ route('warranties.show', $item->warranty) }}" class="text-decoration-none">
                                                    <span class="badge bg-success">
                                                        <i class="bi bi-shield-check"></i> Warranty: {{ $item->warranty->warranty_no }}
                                                    </span>
                                                </a>
                                            </small>
                                        @elseif($item->product->warranty_period_months > 0)
                                            <br><small>
                                                <span class="badge bg-warning">
                                                    Warranty period: {{ $item->product->warranty_period_months }} months (auto-created on sale)
                                                </span>
                                            </small>
                                        @endif
                                    </td>
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
                    @php
                        $totalReturned = $sale->total_returned;
                    @endphp
                    @if($totalReturned > 0)
                    <p><strong>Returned:</strong> <span class="text-warning">-${{ number_format($totalReturned, 2) }}</span></p>
                    <p><strong>Net Total:</strong> ${{ number_format($sale->total_amount - $totalReturned, 2) }}</p>
                    @endif
                    <p><strong>Paid:</strong> ${{ number_format($sale->paid_amount, 2) }}</p>
                    @if($sale->due_amount > 0)
                    <p><strong>Due:</strong> <span class="text-danger">-${{ number_format($sale->due_amount, 2) }}</span></p>
                    @endif
                    <p><strong>Payment Method:</strong> {{ ucfirst($sale->payment_method) }}</p>
                    
                    @if($sale->saleReturns->count() > 0)
                    <hr>
                    <h6>Return History</h6>
                    @foreach($sale->saleReturns as $return)
                    <p class="mb-1">
                        <a href="{{ route('sale-returns.show', $return) }}" class="text-decoration-none">
                            {{ $return->return_no }} - ${{ number_format($return->total_amount, 2) }}
                        </a>
                        <small class="text-muted">({{ $return->return_date->format('M d, Y') }})</small>
                    </p>
                    @endforeach
                    @endif
                    
                    @php
                        $warranties = $sale->saleItems()->whereHas('warranty')->with('warranty')->get();
                    @endphp
                    @if($warranties->count() > 0)
                    <hr>
                    <h6>Warranties</h6>
                    @foreach($warranties as $item)
                        @if($item->warranty)
                        <p class="mb-1">
                            <a href="{{ route('warranties.show', $item->warranty) }}" class="text-decoration-none">
                                <i class="bi bi-shield-check"></i> {{ $item->warranty->warranty_no }} - {{ $item->product->name }}
                            </a>
                            <br>
                            <small class="text-muted">
                                Valid until: {{ $item->warranty->end_date->format('M d, Y') }} 
                                @if($item->warranty->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-danger">Expired</span>
                                @endif
                            </small>
                        </p>
                        @endif
                    @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


