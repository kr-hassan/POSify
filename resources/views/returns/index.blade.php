@extends('layouts.app')

@section('title', 'Returns')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Product Returns</h2>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('returns.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="processed" {{ request('status') == 'processed' ? 'selected' : '' }}>Processed</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Return Type</label>
                    <select name="return_type" class="form-select">
                        <option value="">All</option>
                        <option value="refund" {{ request('return_type') == 'refund' ? 'selected' : '' }}>Refund</option>
                        <option value="exchange" {{ request('return_type') == 'exchange' ? 'selected' : '' }}>Exchange</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('returns.index') }}" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Returns Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Return No</th>
                            <th>Invoice No</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Total Refund</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $return)
                        <tr>
                            <td>{{ $return->return_no }}</td>
                            <td>
                                <a href="{{ route('sales.show', $return->sale) }}">
                                    {{ $return->sale->invoice_no }}
                                </a>
                            </td>
                            <td>{{ $return->customer->name ?? 'Walk-in' }}</td>
                            <td>
                                @if($return->return_type === 'refund')
                                    <span class="badge bg-warning">Refund</span>
                                @else
                                    <span class="badge bg-info">Exchange</span>
                                @endif
                            </td>
                            <td>${{ number_format($return->total_refund, 2) }}</td>
                            <td>
                                @if($return->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($return->status === 'approved')
                                    <span class="badge bg-info">Approved</span>
                                @elseif($return->status === 'processed')
                                    <span class="badge bg-success">Processed</span>
                                @elseif($return->status === 'rejected')
                                    <span class="badge bg-danger">Rejected</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($return->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $return->return_date->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('returns.show', $return) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center">No returns found</td>
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






