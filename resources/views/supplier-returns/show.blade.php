@extends('layouts.app')

@section('title', 'Supplier Return Details')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Supplier Return - {{ $supplierReturn->return_no }}</h2>
        <div>
            <a href="{{ route('supplier-returns.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Return Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Return No:</strong> {{ $supplierReturn->return_no }}</p>
                            <p><strong>Supplier:</strong> {{ $supplierReturn->supplier->name }}</p>
                            <p><strong>Return Date:</strong> {{ $supplierReturn->return_date->format('M d, Y') }}</p>
                            @if($supplierReturn->processed_date)
                                <p><strong>Processed Date:</strong> {{ $supplierReturn->processed_date->format('M d, Y') }}</p>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <p><strong>Status:</strong> 
                                @if($supplierReturn->status === 'pending')
                                    <span class="badge bg-warning">Pending</span>
                                @elseif($supplierReturn->status === 'approved')
                                    <span class="badge bg-info">Approved</span>
                                @elseif($supplierReturn->status === 'completed')
                                    <span class="badge bg-success">Completed</span>
                                @else
                                    <span class="badge bg-danger">Rejected</span>
                                @endif
                            </p>
                            <p><strong>Total Amount:</strong> ${{ number_format($supplierReturn->total_amount, 2) }}</p>
                            <p><strong>Created By:</strong> {{ $supplierReturn->user->name }}</p>
                            @if($supplierReturn->reason)
                                <p><strong>Reason:</strong> {{ $supplierReturn->reason }}</p>
                            @endif
                            <p><strong>Settlement:</strong> 
                                @if($supplierReturn->is_settled)
                                    @if($supplierReturn->settlement_type === 'refund')
                                        <span class="badge bg-success">Refunded</span>
                                    @elseif($supplierReturn->settlement_type === 'replacement')
                                        <span class="badge bg-info">Replaced</span>
                                    @else
                                        <span class="badge bg-secondary">Settled</span>
                                    @endif
                                @else
                                    <span class="badge bg-warning">Pending Settlement</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    @if($supplierReturn->notes)
                        <div class="mt-3">
                            <p><strong>Notes:</strong></p>
                            <p>{{ $supplierReturn->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">Returned Items</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Cost Price</th>
                                    <th>Total</th>
                                    <th>Reason</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($supplierReturn->items as $item)
                                <tr>
                                    <td>{{ $item->product->name }}</td>
                                    <td>{{ $item->quantity }}</td>
                                    <td>${{ number_format($item->cost_price, 2) }}</td>
                                    <td>${{ number_format($item->total, 2) }}</td>
                                    <td>
                                        @if($item->reason)
                                            <span class="badge bg-secondary">{{ ucfirst($item->reason) }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $item->notes ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th>${{ number_format($supplierReturn->total_amount, 2) }}</th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            @if($supplierReturn->is_settled && $supplierReturn->settlement_type === 'refund')
            <div class="card mb-3">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-cash-coin"></i> Refund Information</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Refund Amount:</strong> ${{ number_format($supplierReturn->refund_amount, 2) }}</p>
                            <p><strong>Refund Date:</strong> {{ $supplierReturn->refund_date->format('M d, Y') }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Refund Method:</strong> {{ ucfirst(str_replace('_', ' ', $supplierReturn->refund_method)) }}</p>
                            <p><strong>Settled Date:</strong> {{ $supplierReturn->settled_date->format('M d, Y') }}</p>
                        </div>
                    </div>
                    <div class="alert alert-info mt-3">
                        <i class="bi bi-info-circle"></i> <strong>Balance Effect:</strong> 
                        This refund of ${{ number_format($supplierReturn->refund_amount, 2) }} has been applied to the supplier's balance. 
                        @if($supplierReturn->supplier->balance < 0)
                            The supplier now owes you ${{ number_format(abs($supplierReturn->supplier->balance), 2) }}.
                        @elseif($supplierReturn->supplier->balance > 0)
                            You still owe the supplier ${{ number_format($supplierReturn->supplier->balance, 2) }}.
                        @else
                            The balance is now settled (zero).
                        @endif
                    </div>
                    @if($supplierReturn->refund_notes)
                        <div class="mt-3">
                            <p><strong>Notes:</strong></p>
                            <p>{{ $supplierReturn->refund_notes }}</p>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            @if($supplierReturn->is_settled && $supplierReturn->settlement_type === 'replacement')
            <div class="card mb-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="bi bi-arrow-repeat"></i> Replacement Products</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Cost Price</th>
                                    <th>Replacement Date</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($supplierReturn->replacements as $replacement)
                                <tr>
                                    <td>{{ $replacement->product->name }}</td>
                                    <td>{{ $replacement->quantity }}</td>
                                    <td>${{ number_format($replacement->cost_price, 2) }}</td>
                                    <td>{{ $replacement->received_date->format('M d, Y') }}</td>
                                    <td>{{ $replacement->notes ?? '-' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="text-muted mt-2">
                        <small><i class="bi bi-info-circle"></i> Replacement products have been added back to stock.</small>
                    </p>
                </div>
            </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="bi bi-check-circle"></i> {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="bi bi-exclamation-circle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(!$supplierReturn->isCompleted())
                        <form action="{{ route('supplier-returns.update-status', $supplierReturn) }}" method="POST" class="mb-3">
                            @csrf
                            @method('PUT')
                            <div class="mb-3">
                                <label class="form-label">Update Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="pending" {{ $supplierReturn->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="approved" {{ $supplierReturn->status === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="completed" {{ $supplierReturn->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="rejected" {{ $supplierReturn->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Update Status</button>
                        </form>
                    @endif

                    @if(!$supplierReturn->is_settled && $supplierReturn->status === 'approved')
                        <hr>
                        <h6 class="mb-3">Settlement Options</h6>
                        
                        <!-- Current Supplier Balance Info -->
                        <div class="alert alert-info mb-3" style="font-size: 0.85rem;">
                            <strong>Current Supplier Balance:</strong><br>
                            @if($supplierReturn->supplier->balance > 0)
                                <span class="badge bg-warning">You owe: ${{ number_format($supplierReturn->supplier->balance, 2) }}</span>
                            @elseif($supplierReturn->supplier->balance < 0)
                                <span class="badge bg-success">They owe you: ${{ number_format(abs($supplierReturn->supplier->balance), 2) }}</span>
                            @else
                                <span class="badge bg-secondary">Balance: $0.00</span>
                            @endif
                        </div>
                        
                        <!-- Refund Form -->
                        <div class="card mb-3" style="border: 2px solid #28a745;">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0"><i class="bi bi-cash-coin"></i> Process Refund</h6>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-warning mb-2" style="font-size: 0.75rem; padding: 0.5rem;">
                                    <i class="bi bi-info-circle"></i> <strong>Balance Effect:</strong> 
                                    This refund will <strong>reduce</strong> the supplier's balance by the refund amount.
                                    @if($supplierReturn->supplier->balance > 0)
                                        Current balance will decrease from ${{ number_format($supplierReturn->supplier->balance, 2) }}.
                                    @endif
                                </div>
                                <form action="{{ route('supplier-returns.process-refund', $supplierReturn) }}" method="POST" onsubmit="return confirm('Process refund of $' + document.getElementById('refund_amount').value + '? This will update the supplier balance.')">
                                    @csrf
                                    <div class="mb-2">
                                        <label class="form-label small">Refund Amount *</label>
                                        <input type="number" 
                                               name="refund_amount" 
                                               id="refund_amount"
                                               class="form-control form-control-sm" 
                                               step="0.01" 
                                               min="0" 
                                               max="{{ $supplierReturn->total_amount }}" 
                                               value="{{ $supplierReturn->total_amount }}" 
                                               required>
                                        <small class="text-muted">Max: ${{ number_format($supplierReturn->total_amount, 2) }}</small>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Refund Date *</label>
                                        <input type="date" 
                                               name="refund_date" 
                                               class="form-control form-control-sm" 
                                               value="{{ date('Y-m-d') }}" 
                                               required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Refund Method *</label>
                                        <select name="refund_method" class="form-select form-select-sm" required>
                                            <option value="cash">Cash</option>
                                            <option value="bank_transfer">Bank Transfer</option>
                                            <option value="credit_note">Credit Note</option>
                                            <option value="check">Check</option>
                                            <option value="other">Other</option>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label small">Notes</label>
                                        <textarea name="refund_notes" class="form-control form-control-sm" rows="2"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-success btn-sm w-100">
                                        <i class="bi bi-check-circle"></i> Process Refund
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Replacement Form -->
                        <div class="card" style="border: 2px solid #0dcaf0;">
                            <div class="card-header bg-info text-white">
                                <h6 class="mb-0"><i class="bi bi-arrow-repeat"></i> Process Replacement</h6>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('supplier-returns.process-replacement', $supplierReturn) }}" method="POST" id="replacementForm">
                                    @csrf
                                    <div class="mb-2">
                                        <label class="form-label small">Replacement Date *</label>
                                        <input type="date" 
                                               name="replacement_date" 
                                               class="form-control form-control-sm" 
                                               value="{{ date('Y-m-d') }}" 
                                               required>
                                    </div>
                                    <div id="replacementItems">
                                        <div class="replacement-item mb-2 p-2 border rounded">
                                            <div class="mb-2">
                                                <label class="form-label small">Product *</label>
                                                <select name="items[0][product_id]" class="form-select form-select-sm" required>
                                                    <option value="">Select Product</option>
                                                    @foreach($products as $product)
                                                        <option value="{{ $product->id }}" data-cost="{{ $product->cost_price }}">
                                                            {{ $product->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="row">
                                                <div class="col-6 mb-2">
                                                    <label class="form-label small">Quantity *</label>
                                                    <input type="number" name="items[0][quantity]" class="form-control form-control-sm" min="1" required>
                                                </div>
                                                <div class="col-6 mb-2">
                                                    <label class="form-label small">Cost Price *</label>
                                                    <input type="number" name="items[0][cost_price]" class="form-control form-control-sm cost-price" step="0.01" min="0" required>
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small">Notes</label>
                                                <input type="text" name="items[0][notes]" class="form-control form-control-sm">
                                            </div>
                                            <button type="button" class="btn btn-danger btn-sm remove-replacement-item">Remove</button>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-secondary btn-sm w-100 mb-2" id="addReplacementItem">
                                        <i class="bi bi-plus-circle"></i> Add Item
                                    </button>
                                    <button type="submit" class="btn btn-info btn-sm w-100" onsubmit="return confirm('Process replacement? Products will be added to stock.')">
                                        <i class="bi bi-check-circle"></i> Process Replacement
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    @if($supplierReturn->status !== 'completed' && !$supplierReturn->is_settled)
                        <hr>
                        <form action="{{ route('supplier-returns.destroy', $supplierReturn) }}" method="POST" onsubmit="return confirm('Are you sure? This will restore stock.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger w-100">
                                <i class="bi bi-trash"></i> Delete Return
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let replacementItemIndex = 1;

$('#addReplacementItem').on('click', function() {
    const html = `
        <div class="replacement-item mb-2 p-2 border rounded">
            <div class="mb-2">
                <label class="form-label small">Product *</label>
                <select name="items[${replacementItemIndex}][product_id]" class="form-select form-select-sm" required>
                    <option value="">Select Product</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}" data-cost="{{ $product->cost_price }}">
                            {{ $product->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="row">
                <div class="col-6 mb-2">
                    <label class="form-label small">Quantity *</label>
                    <input type="number" name="items[${replacementItemIndex}][quantity]" class="form-control form-control-sm" min="1" required>
                </div>
                <div class="col-6 mb-2">
                    <label class="form-label small">Cost Price *</label>
                    <input type="number" name="items[${replacementItemIndex}][cost_price]" class="form-control form-control-sm cost-price" step="0.01" min="0" required>
                </div>
            </div>
            <div class="mb-2">
                <label class="form-label small">Notes</label>
                <input type="text" name="items[${replacementItemIndex}][notes]" class="form-control form-control-sm">
            </div>
            <button type="button" class="btn btn-danger btn-sm remove-replacement-item">Remove</button>
        </div>
    `;
    $('#replacementItems').append(html);
    replacementItemIndex++;
});

$(document).on('click', '.remove-replacement-item', function() {
    if ($('.replacement-item').length > 1) {
        $(this).closest('.replacement-item').remove();
    } else {
        alert('At least one item is required');
    }
});

// Auto-fill cost price when product is selected
$(document).on('change', 'select[name*="[product_id]"]', function() {
    const $row = $(this).closest('.replacement-item');
    const $option = $(this).find('option:selected');
    const costPrice = $option.data('cost');
    
    if (costPrice) {
        $row.find('.cost-price').val(costPrice);
    }
});

$('#replacementForm').on('submit', function() {
    return confirm('Process replacement? Products will be added to stock.');
});
</script>
@endpush
@endsection

