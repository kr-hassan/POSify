@extends('layouts.app')

@section('title', 'Create Warranty Claim')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Create Warranty Claim</h2>
        <a href="{{ route('warranties.show', $warranty) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Claim Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('warranties.store-claim', $warranty) }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Warranty No</label>
                            <input type="text" class="form-control" value="{{ $warranty->warranty_no }}" readonly>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Product</label>
                                <input type="text" class="form-control" value="{{ $warranty->product->name }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Customer</label>
                                <input type="text" class="form-control" value="{{ $warranty->customer->name ?? 'Walk-in' }}" readonly>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Warranty Start Date</label>
                                <input type="text" class="form-control" value="{{ $warranty->start_date->format('M d, Y') }}" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Warranty End Date</label>
                                <input type="text" class="form-control" value="{{ $warranty->end_date->format('M d, Y') }}" readonly>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Days Remaining</label>
                            <input type="text" class="form-control" value="{{ $warranty->days_remaining }} days" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Claim Date *</label>
                            <input type="date" name="claim_date" class="form-control @error('claim_date') is-invalid @enderror" value="{{ old('claim_date', date('Y-m-d')) }}" required>
                            @error('claim_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Issue Description *</label>
                            <textarea name="issue_description" class="form-control @error('issue_description') is-invalid @enderror" rows="5" required placeholder="Describe the issue with the product...">{{ old('issue_description') }}</textarea>
                            <small class="text-muted">Minimum 10 characters required</small>
                            @error('issue_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-check-circle"></i> Submit Claim
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Warranty Status</h5>
                </div>
                <div class="card-body">
                    <p><strong>Status:</strong> 
                        <span class="badge bg-success">Active</span>
                    </p>
                    <p><strong>Warranty Period:</strong> {{ $warranty->warranty_period_days }} days</p>
                    <p><strong>Remaining:</strong> {{ $warranty->days_remaining }} days</p>
                    <hr>
                    <p class="text-muted small">
                        <strong>Note:</strong> After submitting a claim, it will be reviewed and processed by an administrator.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

