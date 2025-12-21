@extends('layouts.app')

@section('title', 'Point of Sale')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Product Search & Selection -->
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control form-control-lg" id="productSearch" placeholder="Search by name, SKU, or barcode..." autofocus>
                        <button class="btn btn-primary" type="button" id="searchBtn">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                    <div id="searchResults" class="row g-2"></div>
                </div>
            </div>
        </div>
        
        <!-- Cart & Payment -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Cart</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Customer</label>
                        <select class="form-select" id="customerSelect">
                            <option value="">Walk-in Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div class="table-responsive" style="max-height: 300px;">
                        <table class="table table-sm" id="cartTable">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="cartBody">
                                <!-- Cart items will be added here via AJAX -->
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span id="subtotal">$0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Tax:</span>
                            <span id="tax">$0.00</span>
                        </div>
                        <div class="mb-2">
                            <label class="form-label">Discount</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="discount" value="0" step="0.01" min="0">
                                <select class="form-select" id="discountType" style="max-width: 100px;">
                                    <option value="flat">$</option>
                                    <option value="percent">%</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Total:</strong>
                            <strong id="total">$0.00</strong>
                        </div>
                        
                        <div class="mb-2">
                            <label class="form-label">Payment Method</label>
                            <select class="form-select" id="paymentMethod">
                                <option value="cash">Cash</option>
                                <option value="card">Card</option>
                                <option value="mobile">Mobile</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Paid Amount</label>
                            <input type="number" class="form-control" id="paidAmount" value="0" step="0.01" min="0">
                            <small class="text-muted" id="paidAmountHelp">For walk-in customers, full payment is required</small>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <span>Change:</span>
                            <span id="change">$0.00</span>
                        </div>
                        
                        <button class="btn btn-success btn-lg w-100" id="completeSaleBtn">
                            <i class="bi bi-check-circle"></i> Complete Sale
                        </button>
                        <button class="btn btn-secondary w-100 mt-2" id="clearCartBtn">
                            <i class="bi bi-x-circle"></i> Clear Cart
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let cart = [];

$(document).ready(function() {
    // Search products
    $('#searchBtn, #productSearch').on('click keypress', function(e) {
        if (e.type === 'keypress' && e.which !== 13) return;
        e.preventDefault();
        searchProducts();
    });
    
    // Calculate totals when discount or paid amount changes
    $('#discount, #discountType, #paidAmount').on('input change', calculateTotals);
    
    // When customer selection changes, update paid amount requirement
    $('#customerSelect').on('change', function() {
        calculateTotals();
        const total = parseFloat($('#total').text().replace('$', ''));
        if (!$(this).val() && $('#paidAmount').val() < total) {
            $('#paidAmount').val(total.toFixed(2));
        }
    });
    
    // Complete sale
    $('#completeSaleBtn').on('click', completeSale);
    
    // Clear cart
    $('#clearCartBtn').on('click', clearCart);
    
    // Remove item from cart
    $(document).on('click', '.remove-item', function() {
        const index = $(this).data('index');
        cart.splice(index, 1);
        updateCartDisplay();
    });
    
    // Update quantity
    $(document).on('change', '.item-quantity', function() {
        const index = $(this).data('index');
        const newQty = parseInt($(this).val());
        if (newQty > 0 && newQty <= cart[index].stock) {
            cart[index].quantity = newQty;
            cart[index].total = cart[index].quantity * cart[index].price;
            updateCartDisplay();
        } else {
            alert('Invalid quantity or insufficient stock');
            $(this).val(cart[index].quantity);
        }
    });
});

function searchProducts() {
    const search = $('#productSearch').val();
    if (!search) return;
    
    $.ajax({
        url: '{{ route("pos.search") }}',
        method: 'GET',
        data: { search: search },
        success: function(products) {
            let html = '';
            if (products.length > 0) {
                products.forEach(function(product) {
                    html += `
                        <div class="col-md-3">
                            <div class="card product-card" data-product='${JSON.stringify(product)}'>
                                <div class="card-body text-center">
                                    <h6>${product.name}</h6>
                                    <p class="text-muted small">${product.sku}</p>
                                    <p class="fw-bold">$${parseFloat(product.sell_price).toFixed(2)}</p>
                                    <p class="small">Stock: ${product.stock}</p>
                                    <button class="btn btn-sm btn-primary add-to-cart">Add</button>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                html = '<div class="col-12"><p class="text-muted">No products found</p></div>';
            }
            $('#searchResults').html(html);
        }
    });
}

$(document).on('click', '.add-to-cart', function() {
    const product = $(this).closest('.product-card').data('product');
    addToCart(product);
});

function addToCart(product) {
    if (product.stock <= 0) {
        alert('Product out of stock');
        return;
    }
    
    const existingIndex = cart.findIndex(item => item.id === product.id);
    if (existingIndex >= 0) {
        if (cart[existingIndex].quantity < product.stock) {
            cart[existingIndex].quantity++;
            cart[existingIndex].total = cart[existingIndex].quantity * cart[existingIndex].price;
        } else {
            alert('Insufficient stock');
            return;
        }
    } else {
        cart.push({
            id: product.id,
            name: product.name,
            sku: product.sku,
            price: parseFloat(product.sell_price),
            quantity: 1,
            total: parseFloat(product.sell_price),
            stock: product.stock,
            tax_percent: parseFloat(product.tax_percent || 0)
        });
    }
    
    updateCartDisplay();
    $('#productSearch').val('').focus();
}

function updateCartDisplay() {
    let html = '';
    cart.forEach(function(item, index) {
        html += `
            <tr>
                <td>${item.name}</td>
                <td>
                    <input type="number" class="form-control form-control-sm item-quantity" 
                           data-index="${index}" value="${item.quantity}" min="1" max="${item.stock}" style="width: 60px;">
                </td>
                <td>$${item.price.toFixed(2)}</td>
                <td>$${item.total.toFixed(2)}</td>
                <td>
                    <button class="btn btn-sm btn-danger remove-item" data-index="${index}">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    $('#cartBody').html(html);
    calculateTotals();
}

function calculateTotals() {
    let subtotal = 0;
    let tax = 0;
    
    cart.forEach(function(item) {
        subtotal += item.total;
        if (item.tax_percent > 0) {
            tax += (item.total * item.tax_percent) / 100;
        }
    });
    
    const discount = parseFloat($('#discount').val()) || 0;
    const discountType = $('#discountType').val();
    let discountAmount = 0;
    
    if (discountType === 'percent') {
        discountAmount = (subtotal * discount) / 100;
    } else {
        discountAmount = discount;
    }
    
    const total = subtotal + tax - discountAmount;
    const paid = parseFloat($('#paidAmount').val()) || 0;
    const change = paid - total;
    const customerId = $('#customerSelect').val();
    
    $('#subtotal').text('$' + subtotal.toFixed(2));
    $('#tax').text('$' + tax.toFixed(2));
    $('#total').text('$' + total.toFixed(2));
    $('#change').text('$' + (change > 0 ? change.toFixed(2) : '0.00'));
    
    // Auto-fill paid amount for walk-in customers
    if (!customerId && paid < total) {
        $('#paidAmount').val(total.toFixed(2));
    }
    
    // Show warning if walk-in customer and paid less than total
    if (!customerId && paid < total) {
        $('#paidAmount').addClass('is-invalid');
        if ($('#walkInWarning').length === 0) {
            $('#paidAmount').after('<div class="invalid-feedback" id="walkInWarning">Walk-in customers must pay in full</div>');
        }
    } else {
        $('#paidAmount').removeClass('is-invalid');
        $('#walkInWarning').remove();
    }
}

function completeSale() {
    if (cart.length === 0) {
        alert('Cart is empty');
        return;
    }
    
    const total = parseFloat($('#total').text().replace('$', ''));
    const paid = parseFloat($('#paidAmount').val());
    const customerId = $('#customerSelect').val();
    
    // If no customer selected, must pay in full
    if (!customerId && paid < total) {
        alert('Walk-in customers must pay in full. Please select a customer for partial payment or pay the full amount of $' + total.toFixed(2));
        $('#paidAmount').val(total.toFixed(2));
        $('#paidAmount').focus();
        return;
    }
    
    // If customer selected but paid less, confirm partial payment
    if (customerId && paid < total) {
        if (!confirm('Paid amount is less than total. Continue with partial payment? Due amount will be: -$' + (total - paid).toFixed(2) + '\n\nReceipt will be printed automatically.')) {
            return;
        }
    }
    
    const saleData = {
        customer_id: $('#customerSelect').val() || null,
        items: cart.map(item => ({
            product_id: item.id,
            quantity: item.quantity,
            price: item.price
        })),
        discount: parseFloat($('#discount').val()) || 0,
        discount_type: $('#discountType').val(),
        paid_amount: paid,
        payment_method: $('#paymentMethod').val(),
        _token: '{{ csrf_token() }}'
    };
    
    $.ajax({
        url: '{{ route("sales.store") }}',
        method: 'POST',
        data: saleData,
        success: function(response) {
            if (response.success) {
                // Always open invoice/receipt in new window for printing (regardless of due amount)
                const invoiceUrl = response.invoice_url || '/sales/' + response.sale_id + '/invoice';
                window.open(invoiceUrl, '_blank');
                
                // Show success message
                const message = 'Sale completed successfully!\nInvoice: ' + response.invoice_no + '\n\nReceipt opened in new window and will print automatically.';
                alert(message);
                
                // Clear cart automatically
                clearCartSilent();
            }
        },
        error: function(xhr) {
            const error = xhr.responseJSON?.message || 'Error completing sale';
            alert(error);
        }
    });
}

function clearCart() {
    if (confirm('Clear cart?')) {
        clearCartSilent();
    }
}

function clearCartSilent() {
    cart = [];
    updateCartDisplay();
    $('#customerSelect').val('');
    $('#discount').val(0);
    $('#paidAmount').val(0);
    $('#paymentMethod').val('cash');
    calculateTotals();
}
</script>
@endpush

