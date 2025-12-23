@extends('layouts.app')

@section('title', 'Supplier Returns')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Supplier Returns</h2>
        <a href="{{ route('supplier-returns.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create Return
        </a>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="GET" class="row g-3 mb-3">
                <div class="col-md-2">
                    <input type="text" name="return_no" class="form-control" placeholder="Return No..." value="{{ request('return_no') }}">
                </div>
                <div class="col-md-2">
                    <select name="supplier_id" class="form-select">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                {{ $supplier->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}" placeholder="From Date">
                </div>
                <div class="col-md-2">
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}" placeholder="To Date">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Return No</th>
                            <th>Supplier</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $return)
                        <tr>
                            <td>
                                <a href="{{ route('supplier-returns.show', $return) }}" class="text-decoration-none">
                                    <strong>{{ $return->return_no }}</strong>
                                </a>
                            </td>
                            <td>{{ $return->supplier->name }}</td>
                            <td>{{ $return->return_date->format('M d, Y') }}</td>
                            <td>
                                <span class="badge bg-info">{{ $return->items->count() }} items</span>
                            </td>
                            <td>${{ number_format($return->total_amount, 2) }}</td>
                            <td>
                                @if($return->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($return->status === 'approved')
                                    <span class="badge bg-info">Approved</span>
                                @elseif($return->status === 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                                @if($return->is_settled)
                                    <br>
                                    @if($return->settlement_type === 'refund')
                                        <span class="badge bg-success mt-1">Refunded</span>
                                    @elseif($return->settlement_type === 'replacement')
                                        <span class="badge bg-info mt-1">Replaced</span>
                                    @endif
                                @endif
                            </td>
                            <td>{{ $return->user->name }}</td>
                            <td>
                                <a href="{{ route('supplier-returns.show', $return) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No supplier returns found</td>
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

