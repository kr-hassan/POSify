@extends('layouts.app')

@section('title', 'Create Warranty Claim')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Create Warranty Claim - {{ $warranty->warranty_no }}</h2>
        <a href="{{ route('warranties.show', $warranty) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Warranty
        </a>
    </div>

    @if(!$warranty->is_active)
    <div class="alert alert-danger">
        <strong>Warning:</strong> This warranty is not active. Claims can only be created for active warranties.
    </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Claim Information</h5>
                </div>
                <div class="card-body">
                    <form id="claimForm" method="POST" action="{{ route('warranty-claims.store', $warranty) }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Claim Type *</label>
                            <select name="claim_type" class="form-select" required>
                                <option value="repair">Repair</option>
                                <option value="replacement">Replacement</option>
                                <option value="refund">Refund</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Issue Description *</label>
                            <textarea name="issue_description" class="form-control" rows="5" 
                                      placeholder="Describe the issue with the product..." required minlength="10"></textarea>
                            <small class="text-muted">Minimum 10 characters required</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Claim Date *</label>
                            <input type="date" name="claim_date" class="form-control" 
                                   value="{{ date('Y-m-d') }}" required>
                        </div>

                        <button type="submit" class="btn btn-warning" id="submitClaimBtn" {{ !$warranty->is_active ? 'disabled' : '' }}>
                            <i class="bi bi-exclamation-triangle"></i> Submit Claim & Print Receipt
                        </button>
                        <small class="d-block text-muted mt-2">
                            <i class="bi bi-info-circle"></i> A receipt will be printed automatically for the customer to keep.
                        </small>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Warranty Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Warranty No:</strong> {{ $warranty->warranty_no }}</p>
                    <p><strong>Product:</strong> {{ $warranty->product->name }}</p>
                    <p><strong>Customer:</strong> {{ $warranty->customer->name ?? 'Walk-in' }}</p>
                    <hr>
                    <p><strong>Start Date:</strong> {{ $warranty->start_date->format('M d, Y') }}</p>
                    <p><strong>End Date:</strong> {{ $warranty->end_date->format('M d, Y') }}</p>
                    <p><strong>Status:</strong> 
                        @if($warranty->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Expired</span>
                        @endif
                    </p>
                    @if($warranty->is_active)
                    <p><strong>Days Remaining:</strong> {{ $warranty->days_remaining }} days</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#claimForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#submitClaimBtn');
        const originalText = submitBtn.html();
        
        // Disable button
        submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Processing...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            success: function(response) {
                if (response.success) {
                    // Open receipt in new window
                    window.open(response.receipt_url, '_blank');
                    
                    // Show success message
                    alert('Warranty claim created successfully! Receipt opened in new window for printing.\n\nPlease give this receipt to the customer - they will need it to collect the product after repair/replacement.');
                    
                    // Redirect to warranty details
                    setTimeout(function() {
                        window.location.href = '{{ route("warranties.show", $warranty) }}';
                    }, 1000);
                } else {
                    alert('Error: ' + response.message);
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.message || 'Error creating warranty claim';
                alert(error);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
@endpush
@endsection

