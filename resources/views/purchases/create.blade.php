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
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 25%;">Product *</th>
                                        <th style="width: 8%;">Qty *</th>
                                        <th style="width: 10%;">Cost Price *</th>
                                        <th style="width: 12%;">Batch Number</th>
                                        <th style="width: 12%;">Mfg. Date</th>
                                        <th style="width: 12%;">Expiry Date</th>
                                        <th style="width: 8%;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsContainer">
                                    <tr class="item-row">
                                        <td>
                                            <select name="items[0][product_id]" class="form-select product-select" required>
                                                <option value="">Select Product</option>
                                                @foreach($products as $product)
                                                    <option value="{{ $product->id }}">{{ $product->name }} (Stock: {{ $product->stock }})</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][quantity]" class="form-control" placeholder="Qty" min="1" required>
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][cost_price]" class="form-control" placeholder="Cost" step="0.01" min="0" required>
                                        </td>
                                        <td>
                                            <input type="text" name="items[0][batch_number]" class="form-control" placeholder="Batch #">
                                        </td>
                                        <td>
                                            <input type="date" name="items[0][manufacturing_date]" class="form-control">
                                        </td>
                                        <td>
                                            <input type="date" name="items[0][expiry_date]" class="form-control expiry-date">
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger remove-item">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
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
        <tr class="item-row">
            <td>
                <select name="items[${itemIndex}][product_id]" class="form-select product-select" required>
                    <option value="">Select Product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }} (Stock: {{ $product->stock }})</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][quantity]" class="form-control" placeholder="Qty" min="1" required>
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][cost_price]" class="form-control" placeholder="Cost" step="0.01" min="0" required>
            </td>
            <td>
                <input type="text" name="items[${itemIndex}][batch_number]" class="form-control" placeholder="Batch #">
            </td>
            <td>
                <input type="date" name="items[${itemIndex}][manufacturing_date]" class="form-control">
            </td>
            <td>
                <input type="date" name="items[${itemIndex}][expiry_date]" class="form-control expiry-date">
            </td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-item">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
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

// Auto-calculate expiry date based on manufacturing date and product shelf life (if available)
$(document).on('change', 'input[name*="[manufacturing_date]"]', function() {
    const mfgDate = $(this).val();
    const expiryInput = $(this).closest('tr').find('.expiry-date');
    
    if (mfgDate) {
        // You can add logic here to auto-calculate expiry based on product shelf_life_days
        // For now, just set a default 1 year expiry
        const date = new Date(mfgDate);
        date.setFullYear(date.getFullYear() + 1);
        const expiryDate = date.toISOString().split('T')[0];
        
        // Only set if expiry date is empty
        if (!expiryInput.val()) {
            expiryInput.val(expiryDate);
        }
    }
});
</script>
@endpush
@endsection








