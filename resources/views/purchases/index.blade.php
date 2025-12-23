@extends('layouts.app')

@section('title', 'Purchases')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Purchases</h2>
        <a href="{{ route('purchases.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Purchase
        </a>
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
                    <label class="form-label">Supplier</label>
                    <select name="supplier_id" class="form-select">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label><br>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('purchases.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Supplier</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                        <tr>
                            <td>{{ $purchase->purchase_date->format('M d, Y') }}</td>
                            <td>{{ $purchase->supplier->name }}</td>
                            <td>${{ number_format($purchase->total_amount, 2) }}</td>
                            <td>${{ number_format($purchase->paid_amount, 2) }}</td>
                            <td>
                                @if($purchase->due_amount > 0)
                                    <span class="badge bg-warning">${{ number_format($purchase->due_amount, 2) }}</span>
                                @else
                                    <span class="badge bg-success">Paid</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">No purchases found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $purchases->links() }}
        </div>
    </div>
</div>
@endsection




