@extends('layouts.app')

@section('title', 'Create Supplier Return')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Return Damaged Products to Supplier</h5>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('supplier-returns.store') }}" method="POST" id="returnForm">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Supplier *</label>
                                <select name="supplier_id" class="form-select @error('supplier_id') is-invalid @enderror" required>
                                    <option value="">Select Supplier</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('supplier_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Return Date *</label>
                                <input type="date" name="return_date" class="form-control @error('return_date') is-invalid @enderror" value="{{ old('return_date', date('Y-m-d')) }}" required>
                                @error('return_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">General Reason</label>
                                <input type="text" name="reason" class="form-control @error('reason') is-invalid @enderror" value="{{ old('reason') }}" placeholder="e.g., Damaged goods">
                                @error('reason')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control @error('notes') is-invalid @enderror" rows="2" placeholder="Additional notes...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <h6 class="mb-3">Return Items (Damaged Products)</h6>
                        <div id="itemsContainer">
                            <div class="row mb-2 item-row">
                                <div class="col-md-4">
                                    <label class="form-label small">Product *</label>
                                    <select name="items[0][product_id]" class="form-select product-select" required>
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-stock="{{ $product->stock }}" data-cost="{{ $product->cost_price }}">
                                                {{ $product->name }} (Stock: {{ $product->stock }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label small">Qty *</label>
                                    <input type="number" name="items[0][quantity]" class="form-control quantity-input" placeholder="Qty" min="1" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Cost Price *</label>
                                    <input type="number" name="items[0][cost_price]" class="form-control cost-price-input" placeholder="Cost" step="0.01" min="0" required>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Reason</label>
                                    <select name="items[0][reason]" class="form-select">
                                        <option value="">Select Reason</option>
                                        <option value="damaged">Damaged</option>
                                        <option value="defective">Defective</option>
                                        <option value="expired">Expired</option>
                                        <option value="wrong_item">Wrong Item</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small">Item Notes</label>
                                    <input type="text" name="items[0][notes]" class="form-control" placeholder="Item notes...">
                                </div>
                                <div class="col-md-1">
                                    <label class="form-label small">&nbsp;</label>
                                    <button type="button" class="btn btn-danger btn-sm remove-item w-100">Remove</button>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-secondary mb-3" id="addItemBtn">
                            <i class="bi bi-plus-circle"></i> Add Item
                        </button>
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i> <strong>Note:</strong> Stock will be reduced immediately when this return is created.
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Create Return</button>
                            <a href="{{ route('supplier-returns.index') }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let itemIndex = 1;

$('#addItemBtn').on('click', function() {
    const html = `
        <div class="row mb-2 item-row">
            <div class="col-md-4">
                <label class="form-label small">Product *</label>
                <select name="items[${itemIndex}][product_id]" class="form-select product-select" required>
                    <option value="">Select Product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-stock="{{ $product->stock }}" data-cost="{{ $product->cost_price }}">
                            {{ $product->name }} (Stock: {{ $product->stock }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label class="form-label small">Qty *</label>
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control quantity-input" placeholder="Qty" min="1" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Cost Price *</label>
                <input type="number" name="items[${itemIndex}][cost_price]" class="form-control cost-price-input" placeholder="Cost" step="0.01" min="0" required>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Reason</label>
                <select name="items[${itemIndex}][reason]" class="form-select">
                    <option value="">Select Reason</option>
                    <option value="damaged">Damaged</option>
                    <option value="defective">Defective</option>
                    <option value="expired">Expired</option>
                    <option value="wrong_item">Wrong Item</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Item Notes</label>
                <input type="text" name="items[${itemIndex}][notes]" class="form-control" placeholder="Item notes...">
            </div>
            <div class="col-md-1">
                <label class="form-label small">&nbsp;</label>
                <button type="button" class="btn btn-danger btn-sm remove-item w-100">Remove</button>
            </div>
        </div>
    `;
    $('#itemsContainer').append(html);
    itemIndex++;
});

$(document).on('click', '.remove-item', function() {
    if ($('.item-row').length > 1) {
        $(this).closest('.item-row').remove();
    } else {
        alert('At least one item is required');
    }
});

// Auto-fill cost price when product is selected
$(document).on('change', '.product-select', function() {
    const $row = $(this).closest('.item-row');
    const $option = $(this).find('option:selected');
    const costPrice = $option.data('cost');
    const stock = $option.data('stock');
    
    if (costPrice) {
        $row.find('.cost-price-input').val(costPrice);
    }
    
    // Set max quantity to available stock
    if (stock) {
        $row.find('.quantity-input').attr('max', stock);
    }
});
</script>
@endpush
@endsection

