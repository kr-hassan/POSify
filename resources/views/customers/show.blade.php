@extends('layouts.app')

@section('title', 'Customer Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Customer: {{ $customer->name }}</h2>
        <div>
            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit
            </a>
            <a href="{{ route('customers.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Customer Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $customer->name }}</p>
                    <p><strong>Phone:</strong> {{ $customer->phone ?? '-' }}</p>
                    <p><strong>Email:</strong> {{ $customer->email ?? '-' }}</p>
                    <p><strong>Address:</strong> {{ $customer->address ?? '-' }}</p>
                    <hr>
                    <h4>
                        @if($customer->balance > 0)
                            <span class="badge bg-danger fs-6">
                                Due Balance: -${{ number_format($customer->balance, 2) }}
                            </span>
                        @else
                            <span class="badge bg-success fs-6">
                                Balance: ${{ number_format(abs($customer->balance), 2) }}
                            </span>
                        @endif
                    </h4>
                    @if(isset($balanceMismatch) && $balanceMismatch)
                    <div class="alert alert-warning mt-2">
                        <small>Balance mismatch detected. Calculated: ${{ number_format($calculatedBalance, 2) }}</small>
                        <form action="{{ route('customers.recalculate-balance', $customer) }}" method="POST" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning">Recalculate Balance</button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
            
            @if($customer->balance > 0)
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Record Payment</h5>
                </div>
                <div class="card-body">
                    <form id="paymentForm" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Payment Amount *</label>
                            <input type="number" name="amount" id="paymentAmount" class="form-control" step="0.01" min="0.01" max="{{ $customer->balance }}" value="{{ $customer->balance }}" required>
                            <small class="text-muted">Due Amount: ${{ number_format($customer->balance, 2) }}</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Method *</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="mobile">Mobile</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Payment Date *</label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Note</label>
                            <textarea name="note" class="form-control" rows="2" placeholder="Optional note..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100" id="submitPaymentBtn">
                            <i class="bi bi-cash-coin"></i> Record Payment & Print Receipt
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
        
        <div class="col-md-8">
            <ul class="nav nav-tabs mb-3" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#sales" type="button">
                        Sales History
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#payments" type="button">
                        Payment Receipts ({{ $customer->payments->count() }})
                    </button>
                </li>
            </ul>
            
            <div class="tab-content">
                <div class="tab-pane fade show active" id="sales">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Invoice No</th>
                                            <th>Date</th>
                                            <th>Total</th>
                                            <th>Paid</th>
                                            <th>Due</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($customer->sales as $sale)
                                        <tr>
                                            <td>{{ $sale->invoice_no }}</td>
                                            <td>{{ $sale->sale_date->format('M d, Y') }}</td>
                                            <td>${{ number_format($sale->total_amount, 2) }}</td>
                                            <td>${{ number_format($sale->paid_amount, 2) }}</td>
                                            <td>
                                                @if($sale->due_amount > 0)
                                                    <span class="badge bg-danger">-${{ number_format($sale->due_amount, 2) }}</span>
                                                @else
                                                    <span class="badge bg-success">Paid</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('sales.invoice', $sale) }}" class="btn btn-sm btn-info" target="_blank">
                                                    <i class="bi bi-printer"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No sales found</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="tab-pane fade" id="payments">
                    <div class="card">
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Receipt No</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Payment Method</th>
                                            <th>Received By</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($customer->payments as $payment)
                                        <tr>
                                            <td>{{ $payment->receipt_no }}</td>
                                            <td>{{ $payment->payment_date->format('M d, Y') }}</td>
                                            <td>${{ number_format($payment->amount, 2) }}</td>
                                            <td>{{ ucfirst($payment->payment_method) }}</td>
                                            <td>{{ $payment->user->name }}</td>
                                            <td>
                                                <a href="{{ route('payments.receipt', $payment) }}" class="btn btn-sm btn-info" target="_blank">
                                                    <i class="bi bi-printer"></i> Receipt
                                                </a>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center">No payment receipts found</td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#submitPaymentBtn');
        const originalText = submitBtn.html();
        
        // Disable button
        submitBtn.prop('disabled', true).html('<i class="bi bi-hourglass-split"></i> Processing...');
        
        $.ajax({
            url: '{{ route("customers.add-payment", $customer) }}',
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
                    alert('Payment recorded successfully! Receipt opened in new window.');
                    
                    // Reload page to update balance and payment history
                    setTimeout(function() {
                        window.location.reload();
                    }, 1000);
                } else {
                    alert('Error: ' + response.message);
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr) {
                const error = xhr.responseJSON?.message || 'Error recording payment';
                alert(error);
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });
});
</script>
@endpush
@endsection

