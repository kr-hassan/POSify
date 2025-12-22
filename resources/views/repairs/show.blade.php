@extends('layouts.app')

@section('title', 'Repair Claim Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Repair Claim - {{ $repair->claim_no }}</h2>
        <a href="{{ route('repairs.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Repairs
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Claim Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Claim No:</strong> {{ $repair->claim_no }}</p>
                    <p><strong>Product:</strong> {{ $repair->warranty->product->name }}</p>
                    <p><strong>Customer:</strong> {{ $repair->warranty->customer->name ?? 'Walk-in' }}</p>
                    <p><strong>Issue Description:</strong></p>
                    <p class="bg-light p-3 rounded">{{ $repair->issue_description }}</p>
                    <hr>
                    <p><strong>Claim Date:</strong> {{ $repair->claim_date->format('M d, Y') }}</p>
                    <p><strong>Received Date:</strong> {{ $repair->received_date ? $repair->received_date->format('M d, Y') : 'Not received yet' }}</p>
                    <p><strong>Completed Date:</strong> {{ $repair->resolved_date ? $repair->resolved_date->format('M d, Y') : 'Not completed yet' }}</p>
                    <p><strong>Returned Date:</strong> {{ $repair->returned_date ? $repair->returned_date->format('M d, Y') : 'Not returned yet' }}</p>
                    <p><strong>Status:</strong> 
                        @if($repair->status === 'pending')
                            <span class="badge bg-warning">Pending</span>
                        @elseif($repair->status === 'in_progress')
                            <span class="badge bg-info">In Progress</span>
                        @elseif($repair->status === 'completed')
                            <span class="badge bg-success">Completed</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($repair->status) }}</span>
                        @endif
                    </p>
                    @if($repair->technician_notes)
                    <p><strong>Technician Notes:</strong></p>
                    <p class="bg-light p-3 rounded">{{ $repair->technician_notes }}</p>
                    @endif
                    @if($repair->resolution_notes)
                    <p><strong>Resolution Notes:</strong></p>
                    <p class="bg-light p-3 rounded">{{ $repair->resolution_notes }}</p>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            @if($repair->status === 'pending')
            @can('repair.process')
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Mark as Received</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('repairs.mark-received', $repair) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Received Date *</label>
                            <input type="date" name="received_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Technician Notes</label>
                            <textarea name="technician_notes" class="form-control" rows="3" placeholder="Initial inspection notes..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-info">
                            <i class="bi bi-inbox"></i> Mark as Received
                        </button>
                    </form>
                </div>
            </div>
            @endcan
            @endif

            @if($repair->status === 'in_progress')
            @can('repair.complete')
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Mark as Completed</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('repairs.mark-completed', $repair) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Resolved Date *</label>
                            <input type="date" name="resolved_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Resolution Notes</label>
                            <textarea name="resolution_notes" class="form-control" rows="3" placeholder="What was repaired..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Technician Notes</label>
                            <textarea name="technician_notes" class="form-control" rows="3" placeholder="Final notes..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Mark as Completed
                        </button>
                    </form>
                </div>
            </div>
            @endcan
            @endif

            @if($repair->status === 'completed' && !$repair->returned_date)
            @canany(['repair.complete', 'repair.process'])
            <div class="card mb-3 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-box-arrow-right"></i> Return Product to Customer
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <i class="bi bi-info-circle"></i> <strong>Repair completed!</strong> Now you can return the product to the customer.
                    </div>
                    <form method="POST" action="{{ route('repairs.mark-returned', $repair) }}" id="returnForm">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Returned Date *</label>
                            <input type="date" name="returned_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success btn-lg">
                                <i class="bi bi-box-arrow-right"></i> Mark as Returned & Print Receipt
                            </button>
                            @if(!auth()->user()->can('repair.complete'))
                            <small class="text-muted align-self-center">
                                <i class="bi bi-lock"></i> Requires repair.complete permission
                            </small>
                            @endif
                        </div>
                        <small class="d-block text-muted mt-2">
                            <i class="bi bi-printer"></i> A receipt will be printed automatically for the customer.
                        </small>
                    </form>
                </div>
            </div>
            @endcanany
            @elseif($repair->status === 'completed' && $repair->returned_date)
            <div class="card mb-3 border-success">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-check-circle"></i> Product Returned to Customer
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i> <strong>Product returned!</strong> This product was returned to the customer on {{ $repair->returned_date->format('M d, Y') }}.
                    </div>
                    <a href="{{ route('repairs.return-receipt', $repair) }}" class="btn btn-info" target="_blank">
                        <i class="bi bi-printer"></i> Print Return Receipt
                    </a>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Warranty Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Warranty No:</strong> {{ $repair->warranty->warranty_no }}</p>
                    <p><strong>Start Date:</strong> {{ $repair->warranty->start_date->format('M d, Y') }}</p>
                    <p><strong>End Date:</strong> {{ $repair->warranty->end_date->format('M d, Y') }}</p>
                    <p><strong>Status:</strong> 
                        @if($repair->warranty->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Expired</span>
                        @endif
                    </p>
                    <hr>
                    <p><strong>Sale Invoice:</strong> 
                        <a href="{{ route('sales.show', $repair->warranty->sale) }}">
                            {{ $repair->warranty->sale->invoice_no }}
                        </a>
                    </p>
                    <p><strong>Created By:</strong> {{ $repair->user->name }}</p>
                    <p><strong>Created At:</strong> {{ $repair->created_at->format('M d, Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
@if($repair->status === 'completed' && !$repair->returned_date)
<script>
$(document).ready(function() {
    $('#returnForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = form.find('button[type="submit"]');
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
                    alert('Product returned successfully! Receipt opened in new window for printing.');
                    
                    // Reload page to update status
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    alert('Error: ' + response.message);
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.message || 'Error returning product';
                alert(error);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
@endif
@endpush
@endsection

