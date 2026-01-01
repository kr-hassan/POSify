@extends('layouts.app')

@section('title', 'Product Return')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Product Return - Sale: {{ $sale->invoice_no }}</h2>
        <a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Sale
        </a>
    </div>

    @if($totalReturned > 0)
    <div class="alert alert-info">
        <strong>Already Returned:</strong> ${{ number_format($totalReturned, 2) }} | 
        <strong>Remaining:</strong> ${{ number_format($remainingAmount, 2) }}
    </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Select Products to Return</h5>
                </div>
                <div class="card-body">
                    <form id="returnForm" method="POST" action="{{ route('sale-returns.store', $sale) }}">
                        @csrf
                        
                        <div class="table-responsive mb-3">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Sold Qty</th>
                                        <th>Return Qty</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody id="returnItems">
                                    @foreach($sale->saleItems as $item)
                                    @php
                                        $returnedQty = $sale->saleReturns()
                                            ->join('sale_return_items', 'sale_returns.id', '=', 'sale_return_items.sale_return_id')
                                            ->where('sale_return_items.product_id', $item->product_id)
                                            ->sum('sale_return_items.quantity');
                                        $availableQty = $item->quantity - $returnedQty;
                                    @endphp
                                    @if($availableQty > 0)
                                    <tr>
                                        <td>{{ $item->product->name }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>
                                            <input type="number" 
                                                   class="form-control form-control-sm return-qty" 
                                                   name="items[{{ $item->product_id }}][quantity]" 
                                                   data-price="{{ $item->price }}"
                                                   data-product-id="{{ $item->product_id }}"
                                                   min="0" 
                                                   max="{{ $availableQty }}" 
                                                   value="0"
                                                   onchange="calculateReturnTotal()">
                                            <small class="text-muted">Max: {{ $availableQty }}</small>
                                        </td>
                                        <td>${{ number_format($item->price, 2) }}</td>
                                        <td class="item-total">$0.00</td>
                                        <input type="hidden" name="items[{{ $item->product_id }}][product_id]" value="{{ $item->product_id }}">
                                        <input type="hidden" name="items[{{ $item->product_id }}][price]" value="{{ $item->price }}">
                                    </tr>
                                    @endif
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-right">Total Return Amount:</th>
                                        <th id="totalReturnAmount">$0.00</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Return Date *</label>
                            <input type="date" name="return_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reason (Optional)</label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="Enter reason for return..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-warning" id="submitBtn" disabled>
                            <i class="bi bi-arrow-return-left"></i> Process Return
                        </button>
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
                    <p><strong>Invoice:</strong> {{ $sale->invoice_no }}</p>
                    <p><strong>Date:</strong> {{ $sale->sale_date->format('M d, Y') }}</p>
                    <p><strong>Customer:</strong> {{ $sale->customer->name ?? 'Walk-in' }}</p>
                    <hr>
                    <p><strong>Sale Total:</strong> ${{ number_format($sale->total_amount, 2) }}</p>
                    <p><strong>Already Returned:</strong> ${{ number_format($totalReturned, 2) }}</p>
                    <p><strong>Remaining:</strong> ${{ number_format($remainingAmount, 2) }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function calculateReturnTotal() {
    let total = 0;
    let hasItems = false;

    $('.return-qty').each(function() {
        const qty = parseFloat($(this).val()) || 0;
        const price = parseFloat($(this).data('price'));
        const itemTotal = qty * price;
        
        if (qty > 0) {
            hasItems = true;
        }

        total += itemTotal;
        $(this).closest('tr').find('.item-total').text('$' + itemTotal.toFixed(2));
    });

    $('#totalReturnAmount').text('$' + total.toFixed(2));
    
    const remainingAmount = {{ $remainingAmount }};
    if (total > remainingAmount) {
        $('#totalReturnAmount').addClass('text-danger');
        $('#submitBtn').prop('disabled', true);
        alert('Return amount cannot exceed remaining sale amount of $' + remainingAmount.toFixed(2));
    } else if (hasItems && total > 0) {
        $('#totalReturnAmount').removeClass('text-danger');
        $('#submitBtn').prop('disabled', false);
    } else {
        $('#totalReturnAmount').removeClass('text-danger');
        $('#submitBtn').prop('disabled', true);
    }
}

$('#returnForm').on('submit', function(e) {
    const total = parseFloat($('#totalReturnAmount').text().replace('$', ''));
    const remaining = {{ $remainingAmount }};
    
    if (total > remaining) {
        e.preventDefault();
        alert('Return amount cannot exceed remaining sale amount.');
        return false;
    }
    
    if (total <= 0) {
        e.preventDefault();
        alert('Please select at least one product to return.');
        return false;
    }
    
    if (!confirm('Are you sure you want to process this return? Stock will be restored and customer balance will be adjusted.')) {
        e.preventDefault();
        return false;
    }
});
</script>
@endpush
@endsection







