@extends('layouts.app')

@section('title', 'Warranty Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Warranty Details - {{ $warranty->warranty_no }}</h2>
        <div>
            @if($warranty->is_active)
            <a href="{{ route('warranty-claims.create', $warranty) }}" class="btn btn-warning">
                <i class="bi bi-exclamation-triangle"></i> Create Claim
            </a>
            @endif
            <a href="{{ route('warranties.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Warranty Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Warranty No:</strong> {{ $warranty->warranty_no }}</p>
                            <p><strong>Product:</strong> {{ $warranty->product->name }}</p>
                            <p><strong>Customer:</strong> {{ $warranty->customer->name ?? 'Walk-in' }}</p>
                            <p><strong>Sale Invoice:</strong> 
                                <a href="{{ route('sales.show', $warranty->sale) }}">{{ $warranty->sale->invoice_no }}</a>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Start Date:</strong> {{ $warranty->start_date->format('M d, Y') }}</p>
                            <p><strong>End Date:</strong> {{ $warranty->end_date->format('M d, Y') }}</p>
                            <p><strong>Warranty Period:</strong> {{ $warranty->warranty_period_months }} months</p>
                            <p><strong>Status:</strong> 
                                @if($warranty->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($warranty->status === 'expired')
                                    <span class="badge bg-danger">Expired</span>
                                @elseif($warranty->status === 'claimed')
                                    <span class="badge bg-warning">Claimed</span>
                                @else
                                    <span class="badge bg-secondary">Void</span>
                                @endif
                            </p>
                            @if($warranty->is_active)
                            <p><strong>Days Remaining:</strong> 
                                <span class="text-success">{{ $warranty->days_remaining }} days</span>
                            </p>
                            @else
                            <p><strong>Status:</strong> <span class="text-danger">Expired</span></p>
                            @endif
                        </div>
                    </div>
                    
                    @if($warranty->notes)
                    <hr>
                    <p><strong>Notes:</strong></p>
                    <p>{{ $warranty->notes }}</p>
                    @endif

                    @if(auth()->user()->hasRole(['admin', 'manager']))
                    <hr>
                    <form method="POST" action="{{ route('warranties.update-status', $warranty) }}" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label">Update Status</label>
                                <select name="status" class="form-select">
                                    <option value="active" {{ $warranty->status === 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="expired" {{ $warranty->status === 'expired' ? 'selected' : '' }}>Expired</option>
                                    <option value="void" {{ $warranty->status === 'void' ? 'selected' : '' }}>Void</option>
                                    <option value="claimed" {{ $warranty->status === 'claimed' ? 'selected' : '' }}>Claimed</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">&nbsp;</label><br>
                                <button type="submit" class="btn btn-sm btn-primary">Update Status</button>
                            </div>
                        </div>
                    </form>
                    @endif
                </div>
            </div>

            @if($warranty->warrantyClaims->count() > 0)
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Warranty Claims</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Claim No</th>
                                    <th>Type</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($warranty->warrantyClaims as $claim)
                                <tr>
                                    <td>{{ $claim->claim_no }}</td>
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
                                        <a href="{{ route('warranty-claims.show', $claim) }}" class="btn btn-sm btn-info">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="card-body">
                    @if($warranty->is_active)
                    <a href="{{ route('warranty-claims.create', $warranty) }}" class="btn btn-warning w-100 mb-2">
                        <i class="bi bi-exclamation-triangle"></i> Create Claim
                    </a>
                    @endif
                    <a href="{{ route('sales.show', $warranty->sale) }}" class="btn btn-info w-100 mb-2">
                        <i class="bi bi-receipt"></i> View Sale
                    </a>
                    <a href="{{ route('warranties.index') }}" class="btn btn-secondary w-100">
                        <i class="bi bi-list"></i> All Warranties
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
