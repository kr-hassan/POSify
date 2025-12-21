@extends('layouts.app')

@section('title', 'Sales')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Sales</h2>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form method="GET" class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Invoice No</label>
                    <input type="text" name="invoice_no" class="form-control" placeholder="Search invoice..." value="{{ request('invoice_no') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label><br>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('sales.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Payment</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sales as $sale)
                        <tr>
                            <td>{{ $sale->invoice_no }}</td>
                            <td>{{ $sale->sale_date->format('M d, Y') }}</td>
                            <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                            <td>${{ number_format($sale->total_amount, 2) }}</td>
                            <td>${{ number_format($sale->paid_amount, 2) }}</td>
                            <td>
                                @if($sale->due_amount > 0)
                                    <span class="badge bg-danger">-${{ number_format($sale->due_amount, 2) }}</span>
                                @else
                                    <span class="badge bg-success">Paid</span>
                                @endif
                            </td>
                            <td>{{ ucfirst($sale->payment_method) }}</td>
                            <td>
                                <a href="{{ route('sales.invoice', $sale) }}" class="btn btn-sm btn-info" target="_blank">
                                    <i class="bi bi-printer"></i> Invoice
                                </a>
                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No sales found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $sales->links() }}
        </div>
    </div>
</div>
@endsection


