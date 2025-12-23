@extends('layouts.app')

@section('title', 'Add Purchase')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Add New Purchase</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('purchases.store') }}" method="POST" id="purchaseForm">
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
                                <label class="form-label">Purchase Date *</label>
                                <input type="date" name="purchase_date" class="form-control @error('purchase_date') is-invalid @enderror" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                                @error('purchase_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Paid Amount</label>
                                <input type="number" name="paid_amount" class="form-control @error('paid_amount') is-invalid @enderror" value="{{ old('paid_amount', 0) }}" step="0.01" min="0">
                                @error('paid_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <h6 class="mb-3">Purchase Items</h6>
                        <div id="itemsContainer">
                            <div class="row mb-2 item-row">
                                <div class="col-md-5">
                                    <select name="items[0][product_id]" class="form-select product-select" required>
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} (Stock: {{ $product->stock }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" name="items[0][quantity]" class="form-control" placeholder="Qty" min="1" required>
                                </div>
                                <div class="col-md-2">
                                    <input type="number" name="items[0][cost_price]" class="form-control" placeholder="Cost Price" step="0.01" min="0" required>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-danger remove-item">Remove</button>
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" class="btn btn-secondary mb-3" id="addItemBtn">
                            <i class="bi bi-plus-circle"></i> Add Item
                        </button>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Save Purchase</button>
                            <a href="{{ route('purchases.index') }}" class="btn btn-secondary">Cancel</a>
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
            <div class="col-md-5">
                <select name="items[${itemIndex}][product_id]" class="form-select product-select" required>
                    <option value="">Select Product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} (Stock: {{ $product->stock }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control" placeholder="Qty" min="1" required>
            </div>
            <div class="col-md-2">
                <input type="number" name="items[${itemIndex}][cost_price]" class="form-control" placeholder="Cost Price" step="0.01" min="0" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger remove-item">Remove</button>
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
</script>
@endpush
@endsection




