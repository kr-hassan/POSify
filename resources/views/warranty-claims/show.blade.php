@extends('layouts.app')

@section('title', 'Warranty Claim Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Warranty Claim - {{ $warrantyClaim->claim_no }}</h2>
        <div>
            <a href="{{ route('warranty-claims.receipt', $warrantyClaim) }}" class="btn btn-info" target="_blank">
                <i class="bi bi-printer"></i> Print Receipt
            </a>
            <a href="{{ route('warranty-claims.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Claim Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Claim No:</strong> {{ $warrantyClaim->claim_no }}</p>
                            <p><strong>Warranty:</strong> 
                                <a href="{{ route('warranties.show', $warrantyClaim->warranty) }}">
                                    {{ $warrantyClaim->warranty->warranty_no }}
                                </a>
                            </p>
                            <p><strong>Product:</strong> {{ $warrantyClaim->warranty->product->name }}</p>
                            <p><strong>Customer:</strong> {{ $warrantyClaim->warranty->customer->name ?? 'Walk-in' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Claim Type:</strong> {{ ucfirst($warrantyClaim->claim_type) }}</p>
                            <p><strong>Claim Date:</strong> {{ $warrantyClaim->claim_date->format('M d, Y') }}</p>
                            <p><strong>Status:</strong> 
                                @if($warrantyClaim->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($warrantyClaim->status === 'approved')
                                    <span class="badge bg-info">Approved</span>
                                @elseif($warrantyClaim->status === 'in_progress')
                                    <span class="badge bg-primary">In Progress</span>
                                @elseif($warrantyClaim->status === 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </p>
                            @if($warrantyClaim->resolved_date)
                            <p><strong>Resolved Date:</strong> {{ $warrantyClaim->resolved_date->format('M d, Y') }}</p>
                            @endif
                            <p><strong>Created By:</strong> {{ $warrantyClaim->user->name }}</p>
                        </div>
                    </div>
                    
                    <hr>
                    <p><strong>Issue Description:</strong></p>
                    <p>{{ $warrantyClaim->issue_description }}</p>
                    
                    @if($warrantyClaim->resolution_notes)
                    <hr>
                    <p><strong>Resolution Notes:</strong></p>
                    <p>{{ $warrantyClaim->resolution_notes }}</p>
                    @endif

                    @if(auth()->user()->hasRole(['admin', 'manager']))
                    <hr>
                    <h6>Update Claim Status</h6>
                    <form method="POST" action="{{ route('warranty-claims.update-status', $warrantyClaim) }}">
                        @csrf
                        @method('PATCH')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="pending" {{ $warrantyClaim->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ $warrantyClaim->status === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="in_progress" {{ $warrantyClaim->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                                    <option value="completed" {{ $warrantyClaim->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="rejected" {{ $warrantyClaim->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Resolution Notes</label>
                                <textarea name="resolution_notes" class="form-control" rows="3" 
                                          placeholder="Enter resolution notes...">{{ $warrantyClaim->resolution_notes }}</textarea>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Warranty Details</h5>
                </div>
                <div class="card-body">
                    <p><strong>Warranty No:</strong> 
                        <a href="{{ route('warranties.show', $warrantyClaim->warranty) }}">
                            {{ $warrantyClaim->warranty->warranty_no }}
                        </a>
                    </p>
                    <p><strong>Start Date:</strong> {{ $warrantyClaim->warranty->start_date->format('M d, Y') }}</p>
                    <p><strong>End Date:</strong> {{ $warrantyClaim->warranty->end_date->format('M d, Y') }}</p>
                    <p><strong>Warranty Status:</strong> 
                        @if($warrantyClaim->warranty->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Expired</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

