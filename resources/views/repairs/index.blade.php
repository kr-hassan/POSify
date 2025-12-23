@extends('layouts.app')

@section('title', 'Repair Claims')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Repair Claims</h2>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('repairs.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
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
                <div class="col-md-3">
                    <label class="form-label">Claim No</label>
                    <input type="text" name="claim_no" class="form-control" value="{{ request('claim_no') }}" placeholder="Search claim no...">
                </div>
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('repairs.index') }}" class="btn btn-secondary">Clear</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Claims Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Claim No</th>
                            <th>Product</th>
                            <th>Customer</th>
                            <th>Claim Date</th>
                            <th>Received Date</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($claims as $claim)
                        <tr>
                            <td>{{ $claim->claim_no }}</td>
                            <td>{{ $claim->warranty->product->name }}</td>
                            <td>{{ $claim->warranty->customer->name ?? 'Walk-in' }}</td>
                            <td>{{ $claim->claim_date->format('M d, Y') }}</td>
                            <td>{{ $claim->received_date ? $claim->received_date->format('M d, Y') : '-' }}</td>
                            <td>
                                @if($claim->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($claim->status === 'in_progress')
                                    <span class="badge bg-info">In Progress</span>
                                @elseif($claim->status === 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @else
                                    <span class="badge bg-secondary">{{ ucfirst($claim->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('repairs.show', $claim) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center">No repair claims found</td>
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


