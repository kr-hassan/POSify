@extends('layouts.app')

@section('title', 'Create Return')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Create Return - {{ $sale->invoice_no }}</h2>
        <a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Sale
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Return Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('returns.store-from-sale', $sale) }}" id="returnForm">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Return Type *</label>
                            <select name="return_type" class="form-select" required id="returnType">
                                <option value="refund">Refund</option>
                                <option value="exchange">Exchange</option>
                            </select>
                            <small class="text-muted">
                                <strong>Refund:</strong> Customer gets money back, warranty becomes void<br>
                                <strong>Exchange:</strong> Customer gets replacement product, new warranty created
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Return Date *</label>
                            <input type="date" name="return_date" class="form-control" 
                                   value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reason</label>
                            <textarea name="reason" class="form-control" rows="3" 
                                      placeholder="Reason for return..."></textarea>
                        </div>

                        <hr>
                        <h5>Select Items to Return</h5>
                        <div id="itemsContainer">
                            @foreach($returnableItems as $index => $item)
                            <div class="card mb-3 item-row" data-item-id="{{ $item->id }}">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            <strong>{{ $item->product->name }}</strong><br>
                                            <small class="text-muted">
                                                Sold: {{ $item->quantity }} | 
                                                Available: {{ $item->available_for_return }}
                                            </small>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Quantity</label>
                                            <input type="number" 
                                                   name="items[{{ $index }}][quantity]" 
                                                   class="form-control quantity-input" 
                                                   min="1" 
                                                   max="{{ $item->available_for_return }}"
                                                   value="0"
                                                   data-price="{{ $item->price }}"
                                                   data-max="{{ $item->available_for_return }}">
                                            <input type="hidden" name="items[{{ $index }}][sale_item_id]" value="{{ $item->id }}">
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label">Refund Amount</label>
                                            <input type="text" class="form-control refund-amount" readonly value="$0.00">
                                        </div>
                                        <div class="col-md-2">
                                            <label class="form-label">Reason</label>
                                            <textarea name="items[{{ $index }}][reason]" class="form-control" rows="2" placeholder="Item reason..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>

                        <div class="alert alert-info">
                            <strong>Total Refund:</strong> <span id="totalRefund">$0.00</span>
                        </div>

                        <button type="submit" class="btn btn-warning">
                            <i class="bi bi-arrow-return-left"></i> Create Return Request
                        </button>
                        <small class="d-block text-muted mt-2">
                            <i class="bi bi-info-circle"></i> Return requires approval before processing.
                        </small>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Sale Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Invoice No:</strong> {{ $sale->invoice_no }}</p>
                    <p><strong>Date:</strong> {{ $sale->sale_date->format('M d, Y') }}</p>
                    <p><strong>Customer:</strong> {{ $sale->customer->name ?? 'Walk-in' }}</p>
                    <p><strong>Total Amount:</strong> ${{ number_format($sale->total_amount, 2) }}</p>
                    <hr>
                    <h6>Returnable Items</h6>
                    @foreach($returnableItems as $item)
                    <p class="mb-2">
                        <strong>{{ $item->product->name }}</strong><br>
                        <small>
                            Sold: {{ $item->quantity }}<br>
                            Returned: {{ $item->returned_quantity }}<br>
                            Available: <span class="badge bg-success">{{ $item->available_for_return }}</span>
                        </small>
                    </p>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Calculate refund amounts
    function calculateRefunds() {
        let total = 0;
        $('.item-row').each(function() {
            const quantity = parseInt($(this).find('.quantity-input').val()) || 0;
            const price = parseFloat($(this).find('.quantity-input').data('price'));
            const refund = quantity * price;
            $(this).find('.refund-amount').val('$' + refund.toFixed(2));
            total += refund;
        });
        $('#totalRefund').text('$' + total.toFixed(2));
    }

    // Update on quantity change
    $(document).on('input', '.quantity-input', function() {
        const max = parseInt($(this).data('max'));
        const value = parseInt($(this).val()) || 0;
        if (value > max) {
            $(this).val(max);
            alert('Cannot return more than available quantity');
        }
        calculateRefunds();
    });

    // Initial calculation
    calculateRefunds();
});
</script>
@endpush
@endsection


