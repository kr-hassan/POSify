@extends('layouts.app')

@section('title', 'Point of Sale')

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Product Search & Selection -->
        <div class="col-md-8">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Product Search</h5>
                    <button class="btn btn-sm btn-outline-secondary" type="button" id="toggleFilters">
                        <i class="bi bi-funnel"></i> Filters
                    </button>
                </div>
                <div class="card-body">
                    <!-- Advanced Search Filters (Collapsible) -->
                    <div id="searchFilters" class="mb-3" style="display: none;">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label small">Category</label>
                                <select class="form-select form-select-sm" id="filterCategory">
                                    <option value="">All Categories</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Stock</label>
                                <select class="form-select form-select-sm" id="filterStock">
                                    <option value="">All</option>
                                    <option value="1">In Stock Only</option>
                                    <option value="0">Out of Stock</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Min Price</label>
                                <input type="number" class="form-control form-control-sm" id="filterMinPrice" placeholder="0.00" step="0.01">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small">Max Price</label>
                                <input type="number" class="form-control form-control-sm" id="filterMaxPrice" placeholder="9999.99" step="0.01">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small">Sort By</label>
                                <select class="form-select form-select-sm" id="filterSort">
                                    <option value="name">Name (A-Z)</option>
                                    <option value="price_asc">Price (Low to High)</option>
                                    <option value="price_desc">Price (High to Low)</option>
                                    <option value="stock">Stock (High to Low)</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Search Input with Autocomplete -->
                    <div class="input-group mb-3 position-relative">
                        <input type="text" class="form-control form-control-lg" id="productSearch" 
                               placeholder="Type to search (name, SKU, barcode) or scan barcode..." 
                               autofocus autocomplete="off">
                        <button class="btn btn-primary" type="button" id="searchBtn">
                            <i class="bi bi-search"></i> Search
                        </button>
                        <!-- Autocomplete Dropdown -->
                        <div id="autocompleteResults" class="list-group position-absolute w-100" style="z-index: 1000; display: none; top: 100%; max-height: 300px; overflow-y: auto;"></div>
                    </div>
                    
                    <!-- Loading Indicator -->
                    <div id="searchLoading" class="text-center" style="display: none;">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    
                    <!-- Search Results -->
                    <div id="searchResults" class="row g-2"></div>
                </div>
            </div>
        </div>
        
        <!-- Cart & Payment -->
        <div class="col-md-4">
            <div class="card sticky-top" style="top: 20px;">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Cart <span id="cartCount" class="badge bg-light text-dark">0</span></h5>
                    <small class="text-white-50">Press F2 to focus search</small>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">Customer</label>
                        <select class="form-select" id="customerSelect">
                            <option value="">Walk-in Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" data-email="{{ $customer->email ?? '' }}">
                                    {{ $customer->name }} @if($customer->phone)({{ $customer->phone }})@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <!-- Email field for walk-in customers only -->
                    <div class="mb-3" id="emailFieldContainer" style="display: none;">
                        <label class="form-label">Email <small class="text-muted">(Optional - for receipt)</small></label>
                        <input type="email" class="form-control" id="customerEmail" placeholder="customer@example.com">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="sendEmailReceipt" value="1">
                            <label class="form-check-label" for="sendEmailReceipt">
                                <i class="bi bi-envelope"></i> Send receipt via email
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1">Enter email and check the box to send receipt</small>
                    </div>
                    
                    <!-- Email info for registered customers -->
                    <div class="mb-3" id="registeredCustomerEmailInfo" style="display: none;">
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-envelope-check"></i> <strong>Receipt will be sent automatically</strong>
                            <div class="small mt-1" id="customerEmailDisplay"></div>
                        </div>
                    </div>
                    
                    <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                        <table class="table table-sm table-hover" id="cartTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Item</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody id="cartBody">
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Cart is empty</td>
                                </tr>
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
                            <i class="bi bi-check-circle"></i> Complete Sale (Enter)
                        </button>
                        <button class="btn btn-secondary w-100 mt-2" id="clearCartBtn">
                            <i class="bi bi-x-circle"></i> Clear Cart (Esc)
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
let searchTimeout;
let autocompleteTimeout;
let categories = [];

$(document).ready(function() {
    // Load categories
    loadCategories();
    
    // Check initial state - if walk-in customer is selected, show email field
    if (!$('#customerSelect').val()) {
        $('#emailFieldContainer').show();
    }
    
    // Toggle filters
    $('#toggleFilters').on('click', function() {
        $('#searchFilters').slideToggle();
    });
    
    // Search with debouncing (300ms delay)
    $('#productSearch').on('input', function() {
        const search = $(this).val().trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        clearTimeout(autocompleteTimeout);
        
        if (search.length === 0) {
            $('#searchResults').html('');
            $('#autocompleteResults').hide().html('');
            return;
        }
        
        // Quick autocomplete for 2+ characters
        if (search.length >= 2) {
            autocompleteTimeout = setTimeout(() => {
                quickSearch(search);
            }, 150);
        }
        
        // Full search after 300ms of no typing
        searchTimeout = setTimeout(() => {
            searchProducts();
        }, 300);
    });
    
    // Search on Enter or button click
    $('#productSearch').on('keypress', function(e) {
        if (e.which === 13) {
        e.preventDefault();
            clearTimeout(searchTimeout);
            searchProducts();
        }
    });
    
    $('#searchBtn').on('click', function() {
        clearTimeout(searchTimeout);
        searchProducts();
    });
    
    // Filter changes trigger search
    $('#filterCategory, #filterStock, #filterMinPrice, #filterMaxPrice, #filterSort').on('change input', function() {
        if ($('#productSearch').val().trim()) {
            searchProducts();
        }
    });
    
    // Keyboard shortcuts
    $(document).on('keydown', function(e) {
        // F2 - Focus search
        if (e.key === 'F2') {
            e.preventDefault();
            $('#productSearch').focus().select();
        }
        // Enter - Complete sale (when not in input fields)
        if (e.key === 'Enter' && !$(e.target).is('input, textarea, select') && cart.length > 0) {
            e.preventDefault();
            completeSale();
        }
        // Esc - Clear cart
        if (e.key === 'Escape' && !$(e.target).is('input, textarea')) {
            if (confirm('Clear cart?')) {
                clearCartSilent();
            }
        }
    });
    
    // Calculate totals when discount or paid amount changes
    $('#discount, #discountType, #paidAmount').on('input change', debounce(calculateTotals, 100));
    
    // When customer selection changes, update paid amount requirement and email field
    $('#customerSelect').on('change', function() {
        const customerId = $(this).val();
        const selectedOption = $(this).find('option:selected');
        const customerEmail = selectedOption.data('email');
        
        calculateTotals();
        const total = parseFloat($('#total').text().replace('$', ''));
        if (!customerId && $('#paidAmount').val() < total) {
            $('#paidAmount').val(total.toFixed(2));
        }
        
        // Show/hide email field based on customer type
        if (!customerId) {
            // Walk-in customer - show email field with checkbox
            $('#emailFieldContainer').slideDown();
            $('#registeredCustomerEmailInfo').slideUp();
            $('#customerEmail').val('');
            $('#sendEmailReceipt').prop('checked', false);
        } else if (customerEmail) {
            // Registered customer with email - show info, hide email field
            // Email will be sent automatically
            $('#emailFieldContainer').slideUp();
            $('#registeredCustomerEmailInfo').slideDown();
            $('#customerEmailDisplay').text('Email: ' + customerEmail);
            $('#customerEmail').val('');
            $('#sendEmailReceipt').prop('checked', false);
        } else {
            // Registered customer without email - show email field (optional)
            $('#emailFieldContainer').slideDown();
            $('#registeredCustomerEmailInfo').slideUp();
            $('#customerEmail').val('');
            $('#sendEmailReceipt').prop('checked', false);
        }
    });
    
    // Auto-check email checkbox when email is entered for walk-in customers
    $('#customerEmail').on('input', function() {
        const email = $(this).val().trim();
        const customerId = $('#customerSelect').val();
        if (!customerId && email) {
            // Walk-in customer with email - auto-check the box
            $('#sendEmailReceipt').prop('checked', true);
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
    
    // Click outside to close autocomplete
    $(document).on('click', function(e) {
        if (!$(e.target).closest('#productSearch, #autocompleteResults').length) {
            $('#autocompleteResults').hide();
        }
    });
    
    // Select from autocomplete
    $(document).on('click', '.autocomplete-item', function() {
        const product = $(this).data('product');
        addToCart(product);
        $('#productSearch').val('').focus();
        $('#autocompleteResults').hide();
    });
});

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Load categories
function loadCategories() {
    $.ajax({
        url: '{{ route("pos.categories") }}',
        method: 'GET',
        success: function(data) {
            categories = data;
            let html = '<option value="">All Categories</option>';
            data.forEach(function(cat) {
                html += `<option value="${cat.id}">${cat.name}</option>`;
            });
            $('#filterCategory').html(html);
        }
    });
}

// Quick autocomplete search
function quickSearch(search) {
    $.ajax({
        url: '{{ route("pos.quick-search") }}',
        method: 'GET',
        data: { q: search },
        success: function(products) {
            if (products.length > 0) {
                let html = '';
                products.forEach(function(product) {
                    const stockClass = product.stock > 0 ? 'text-success' : 'text-danger';
                    html += `
                        <a href="#" class="list-group-item list-group-item-action autocomplete-item" data-product='${JSON.stringify(product)}'>
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1">${product.name}</h6>
                                <small class="${stockClass}">$${parseFloat(product.sell_price).toFixed(2)}</small>
                            </div>
                            <small class="text-muted">${product.sku} | Stock: ${product.stock}</small>
                        </a>
                    `;
                });
                $('#autocompleteResults').html(html).show();
            } else {
                $('#autocompleteResults').hide();
            }
        }
    });
}

// Full product search
function searchProducts() {
    const search = $('#productSearch').val().trim();
    if (!search) {
        $('#searchResults').html('');
        return;
    }
    
    $('#searchLoading').show();
    $('#searchResults').html('');
    
    const filters = {
        search: search,
        category_id: $('#filterCategory').val() || null,
        in_stock_only: $('#filterStock').val() === '1',
        min_price: $('#filterMinPrice').val() || null,
        max_price: $('#filterMaxPrice').val() || null,
        sort_by: $('#filterSort').val() || 'name'
    };
    
    $.ajax({
        url: '{{ route("pos.search") }}',
        method: 'GET',
        data: filters,
        success: function(products) {
            $('#searchLoading').hide();
            let html = '';
            if (products.length > 0) {
                products.forEach(function(product) {
                    const stockClass = product.stock > 0 ? (product.stock <= product.alert_quantity ? 'text-warning' : 'text-success') : 'text-danger';
                    const stockText = product.stock > 0 ? product.stock : 'Out of Stock';
                    html += `
                        <div class="col-md-3 col-sm-4 col-6">
                            <div class="card product-card h-100 ${product.stock <= 0 ? 'border-danger' : ''}" data-product='${JSON.stringify(product)}'>
                                <div class="card-body text-center p-2">
                                    <h6 class="card-title mb-1" style="font-size: 0.9rem;">${product.name}</h6>
                                    <p class="text-muted small mb-1">${product.sku}</p>
                                    ${product.category ? `<p class="text-muted small mb-1">${product.category.name}</p>` : ''}
                                    <p class="fw-bold mb-1">$${parseFloat(product.sell_price).toFixed(2)}</p>
                                    <p class="small mb-2 ${stockClass}">Stock: ${stockText}</p>
                                    <button class="btn btn-sm btn-primary add-to-cart w-100" ${product.stock <= 0 ? 'disabled' : ''}>
                                        ${product.stock <= 0 ? 'Out of Stock' : 'Add'}
                                    </button>
                                </div>
                            </div>
                        </div>
                    `;
                });
            } else {
                html = '<div class="col-12"><div class="alert alert-info">No products found. Try adjusting your search or filters.</div></div>';
            }
            $('#searchResults').html(html);
        },
        error: function() {
            $('#searchLoading').hide();
            $('#searchResults').html('<div class="col-12"><div class="alert alert-danger">Error searching products. Please try again.</div></div>');
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
    $('#autocompleteResults').hide();
}

function updateCartDisplay() {
    let html = '';
    if (cart.length === 0) {
        html = '<tr><td colspan="5" class="text-center text-muted">Cart is empty</td></tr>';
    } else {
    cart.forEach(function(item, index) {
        html += `
            <tr>
                    <td><small>${item.name}</small></td>
                <td>
                    <input type="number" class="form-control form-control-sm item-quantity" 
                           data-index="${index}" value="${item.quantity}" min="1" max="${item.stock}" style="width: 60px;">
                </td>
                    <td><small>$${item.price.toFixed(2)}</small></td>
                    <td><small>$${item.total.toFixed(2)}</small></td>
                <td>
                        <button class="btn btn-sm btn-danger remove-item" data-index="${index}" title="Remove">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    }
    
    $('#cartBody').html(html);
    $('#cartCount').text(cart.length);
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
        if (!confirm('Paid amount is less than total. Continue with partial payment? Due amount will be: $' + (total - paid).toFixed(2) + '\n\nReceipt will be printed automatically.')) {
            return;
        }
    }
    
    const selectedOption = $('#customerSelect').find('option:selected');
    const customerHasEmail = selectedOption.data('email');
    const walkInEmail = $('#customerEmail').val().trim();
    const sendEmailChecked = $('#sendEmailReceipt').is(':checked');
    
    // For registered customers with email: always send (automatic)
    // For walk-in customers: only send if checkbox is checked and email provided
    let shouldSendEmail = false;
    let emailToSend = null;
    
    if (customerId && customerHasEmail) {
        // Registered customer with email - send automatically
        shouldSendEmail = true;
        emailToSend = customerHasEmail;
    } else if (!customerId && sendEmailChecked && walkInEmail) {
        // Walk-in customer - send only if checkbox checked and email provided
        shouldSendEmail = true;
        emailToSend = walkInEmail;
    } else if (customerId && !customerHasEmail && sendEmailChecked && walkInEmail) {
        // Registered customer without email - send if checkbox checked and email provided
        shouldSendEmail = true;
        emailToSend = walkInEmail;
    }
    
    const saleData = {
        customer_id: customerId || null,
        customer_email: emailToSend,
        send_email: shouldSendEmail ? '1' : '0',
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
    
    $('#completeSaleBtn').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');
    
    $.ajax({
        url: '{{ route("sales.store") }}',
        method: 'POST',
        data: saleData,
        success: function(response) {
            if (response.success) {
                // Always open invoice/receipt in new window for printing
                const invoiceUrl = response.invoice_url || '/sales/' + response.sale_id + '/invoice';
                window.open(invoiceUrl, '_blank');
                
                // Show success message
                let message = 'Sale completed successfully!\nInvoice: ' + response.invoice_no;
                if (response.email_sent) {
                    message += '\n\n✓ Invoice sent to: ' + response.email_address;
                } else if (response.email_error) {
                    message += '\n\n⚠ Email failed: ' + response.email_error;
                }
                message += '\n\nReceipt opened in new window.';
                alert(message);
                
                // Clear cart automatically
                clearCartSilent();
            }
            $('#completeSaleBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Complete Sale (Enter)');
        },
        error: function(xhr) {
            $('#completeSaleBtn').prop('disabled', false).html('<i class="bi bi-check-circle"></i> Complete Sale (Enter)');
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
    $('#customerEmail').val('');
    $('#emailFieldContainer').slideUp();
    $('#registeredCustomerEmailInfo').slideUp();
    $('#sendEmailReceipt').prop('checked', false);
    $('#discount').val(0);
    $('#paidAmount').val(0);
    $('#paymentMethod').val('cash');
    calculateTotals();
}
</script>
@endpush
