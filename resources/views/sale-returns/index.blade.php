@extends('layouts.app')

@section('title', 'Product Returns')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Product Returns</h2>
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
                    <label class="form-label">Return No</label>
                    <input type="text" name="return_no" class="form-control" placeholder="Search return no..." value="{{ request('return_no') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label><br>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('sale-returns.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Return No</th>
                            <th>Date</th>
                            <th>Sale Invoice</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Processed By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $return)
                        <tr>
                            <td>{{ $return->return_no }}</td>
                            <td>{{ $return->return_date->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('sales.show', $return->sale) }}">{{ $return->sale->invoice_no }}</a>
                            </td>
                            <td>{{ $return->customer->name ?? 'Walk-in' }}</td>
                            <td>${{ number_format($return->total_amount, 2) }}</td>
                            <td>{{ $return->user->name }}</td>
                            <td>
                                <a href="{{ route('sale-returns.show', $return) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No returns found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $returns->links() }}
        </div>
    </div>
</div>
@endsection


