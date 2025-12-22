@extends('layouts.app')

@section('title', 'Return Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Return Details - {{ $productReturn->return_no }}</h2>
        <a href="{{ route('returns.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Returns
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Return Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Return No:</strong> {{ $productReturn->return_no }}</p>
                    <p><strong>Type:</strong> 
                        @if($productReturn->return_type === 'refund')
                            <span class="badge bg-warning">Refund</span>
                        @else
                            <span class="badge bg-info">Exchange</span>
                        @endif
                    </p>
                    <p><strong>Status:</strong> 
                        @if($productReturn->status === 'pending')
                            <span class="badge bg-warning">Pending Approval</span>
                        @elseif($productReturn->status === 'approved')
                            <span class="badge bg-info">Approved</span>
                        @elseif($productReturn->status === 'processed')
                            <span class="badge bg-success">Processed</span>
                        @elseif($productReturn->status === 'rejected')
                            <span class="badge bg-danger">Rejected</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($productReturn->status) }}</span>
                        @endif
                    </p>
                    <p><strong>Return Date:</strong> {{ $productReturn->return_date->format('M d, Y') }}</p>
                    @if($productReturn->processed_date)
                    <p><strong>Processed Date:</strong> {{ $productReturn->processed_date->format('M d, Y') }}</p>
                    @endif
                    @if($productReturn->reason)
                    <p><strong>Reason:</strong> {{ $productReturn->reason }}</p>
                    @endif
                    <hr>
                    <h6>Returned Items</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Refund Amount</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($productReturn->returnItems as $item)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->refund_amount, 2) }}</td>
                                    <td>{{ $item->reason ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2">Total Refund</th>
                                    <th>${{ number_format($productReturn->total_refund, 2) }}</th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            @can('return.approve')
            @if($productReturn->status === 'pending')
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Approve or Reject Return</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('returns.approve', $productReturn) }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Approve Return
                        </button>
                    </form>
                    <form method="POST" action="{{ route('returns.reject', $productReturn) }}" class="d-inline">
                        @csrf
                        <div class="input-group mt-2">
                            <input type="text" name="reason" class="form-control" placeholder="Rejection reason...">
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-x-circle"></i> Reject
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            @endif
            @endcan

            @can('refund.process')
            @if($productReturn->status === 'approved')
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Process Return</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <strong>Warning:</strong> Processing this return will:
                        <ul class="mb-0">
                            <li>Increase inventory stock for returned items</li>
                            <li>Adjust customer balance (refund amount)</li>
                            @if($productReturn->return_type === 'refund')
                            <li>Void warranties for returned items</li>
                            @else
                            <li>Void old warranties and create new ones for exchange items</li>
                            @endif
                        </ul>
                    </div>
                    <form method="POST" action="{{ route('returns.process', $productReturn) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cash-coin"></i> Process Return
                        </button>
                    </form>
                </div>
            </div>
            @endif
            @endcan
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Sale Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Invoice No:</strong> 
                        <a href="{{ route('sales.show', $productReturn->sale) }}">
                            {{ $productReturn->sale->invoice_no }}
                        </a>
                    </p>
                    <p><strong>Sale Date:</strong> {{ $productReturn->sale->sale_date->format('M d, Y') }}</p>
                    <p><strong>Customer:</strong> {{ $productReturn->customer->name ?? 'Walk-in' }}</p>
                    <p><strong>Sale Total:</strong> ${{ number_format($productReturn->sale->total_amount, 2) }}</p>
                    <hr>
                    <p><strong>Created By:</strong> {{ $productReturn->creator->name }}</p>
                    <p><strong>Created At:</strong> {{ $productReturn->created_at->format('M d, Y H:i') }}</p>
                    @if($productReturn->approver)
                    <p><strong>Approved By:</strong> {{ $productReturn->approver->name }}</p>
                    <p><strong>Approved At:</strong> {{ $productReturn->updated_at->format('M d, Y H:i') }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

