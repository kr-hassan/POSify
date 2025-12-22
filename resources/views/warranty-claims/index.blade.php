@extends('layouts.app')

@section('title', 'Warranty Claims')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Warranty Claims</h2>
        <a href="{{ route('warranty-claims.index', ['show_returned' => '1']) }}" class="btn btn-success">
            <i class="bi bi-check-circle"></i> View Returned Products
        </a>
    </div>
    
    @if(request('show_returned') == '1')
    <div class="alert alert-info mb-3">
        <i class="bi bi-info-circle"></i> <strong>Showing Returned Products Only</strong> - Products that have been returned to customers after repair.
        <a href="{{ route('warranty-claims.index') }}" class="btn btn-sm btn-secondary ms-2">Back to Active Claims</a>
    </div>
    @else
    <div class="alert alert-success mb-3">
        <i class="bi bi-info-circle"></i> <strong>Showing Active Claims</strong> - Products that are pending, in progress, or completed but not yet returned.
        <a href="{{ route('warranty-claims.index', ['show_returned' => '1']) }}" class="btn btn-sm btn-success ms-2">View Returned Products</a>
    </div>
    @endif
    
    <div class="card">
        <div class="card-body">
            <form method="GET" class="row g-3 mb-3">
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Claim Type</label>
                    <select name="claim_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="repair" {{ request('claim_type') == 'repair' ? 'selected' : '' }}>Repair</option>
                        <option value="replacement" {{ request('claim_type') == 'replacement' ? 'selected' : '' }}>Replacement</option>
                        <option value="refund" {{ request('claim_type') == 'refund' ? 'selected' : '' }}>Refund (Return)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Claim No</label>
                    <input type="text" name="claim_no" class="form-control" value="{{ request('claim_no') }}" placeholder="Search claim no...">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Filters</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="include_returns" value="1" id="includeReturns" {{ request('include_returns') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="includeReturns" style="font-size: 0.85rem;">
                            Include Refund Claims
                        </label>
                    </div>
                    <div class="form-check form-switch mt-2">
                        <input class="form-check-input" type="checkbox" name="show_returned" value="1" id="showReturned" {{ request('show_returned') == '1' ? 'checked' : '' }}>
                        <label class="form-check-label" for="showReturned" style="font-size: 0.85rem;">
                            Show Returned Products
                        </label>
                    </div>
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('warranty-claims.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Claim No</th>
                            <th>Warranty</th>
                            <th>Product</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Returned</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($claims as $claim)
                        <tr>
                            <td>{{ $claim->claim_no }}</td>
                            <td>
                                <a href="{{ route('warranties.show', $claim->warranty) }}">{{ $claim->warranty->warranty_no }}</a>
                            </td>
                            <td>{{ $claim->warranty->product->name }}</td>
                            <td>{{ $claim->warranty->customer->name ?? 'Walk-in' }}</td>
                            <td>{{ ucfirst($claim->claim_type) }}</td>
                            <td>{{ $claim->claim_date->format('M d, Y') }}</td>
                            <td>
                                @if($claim->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($claim->status === 'approved')
                                    <span class="badge bg-info">Approved</span>
                                @elseif($claim->status === 'in_progress')
                                    <span class="badge bg-primary">In Progress</span>
                                @elseif($claim->status === 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </td>
                            <td>
                                @if($claim->returned_date)
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle"></i> Returned<br>
                                        <small>{{ $claim->returned_date->format('M d, Y') }}</small>
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Not Returned</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('warranty-claims.show', $claim) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> View
                                </a>
                                @if($claim->claim_type === 'repair')
                                <a href="{{ route('repairs.show', $claim) }}" class="btn btn-sm btn-primary" title="Repair Details">
                                    <i class="bi bi-tools"></i> Repair
                                </a>
                                @endif
                                <a href="{{ route('warranty-claims.receipt', $claim) }}" class="btn btn-sm btn-success" target="_blank">
                                    <i class="bi bi-printer"></i> Receipt
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center">No warranty claims found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $claims->links() }}
        </div>
    </div>
</div>
@endsection

