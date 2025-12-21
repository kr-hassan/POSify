@extends('layouts.app')

@section('title', 'Warranties')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Warranties</h2>
        <a href="{{ route('sales.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Sales
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form method="GET" class="mb-4">
                <!-- Smart Search - Main Search Box -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <label class="form-label fw-bold">
                            <i class="bi bi-search"></i> Smart Search
                            <small class="text-muted">(Search by Warranty No, Invoice No, Product Name, Customer Name, Phone, Email, SKU, Barcode)</small>
                        </label>
                        <div class="input-group">
                            <input type="text" 
                                   name="search" 
                                   class="form-control form-control-lg" 
                                   placeholder="Type warranty number, invoice, product name, customer name, phone, email, SKU, or barcode..." 
                                   value="{{ request('search') }}"
                                   autofocus>
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-search"></i> Search
                            </button>
                            @if(request()->hasAny(['search', 'status', 'from_date', 'to_date', 'warranty_no', 'invoice_no', 'product_name', 'customer_name']))
                            <a href="{{ route('warranties.index') }}" class="btn btn-secondary btn-lg">
                                <i class="bi bi-x-circle"></i> Clear
                            </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Advanced Filters (Collapsible) -->
                <div class="row">
                    <div class="col-md-12">
                        <button type="button" class="btn btn-sm btn-outline-secondary mb-3" data-bs-toggle="collapse" data-bs-target="#advancedFilters">
                            <i class="bi bi-funnel"></i> Advanced Filters
                            <i class="bi bi-chevron-down"></i>
                        </button>
                    </div>
                </div>
                
                <div class="collapse" id="advancedFilters">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                                <option value="void" {{ request('status') == 'void' ? 'selected' : '' }}>Void</option>
                                <option value="claimed" {{ request('status') == 'claimed' ? 'selected' : '' }}>Claimed</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">From Date</label>
                            <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">To Date</label>
                            <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Warranty No</label>
                            <input type="text" name="warranty_no" class="form-control" placeholder="Warranty number..." value="{{ request('warranty_no') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Invoice No</label>
                            <input type="text" name="invoice_no" class="form-control" placeholder="Invoice number..." value="{{ request('invoice_no') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Product Name</label>
                            <input type="text" name="product_name" class="form-control" placeholder="Product name..." value="{{ request('product_name') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Customer Name</label>
                            <input type="text" name="customer_name" class="form-control" placeholder="Customer name..." value="{{ request('customer_name') }}">
                        </div>
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Apply Filters
                            </button>
                            <a href="{{ route('warranties.index') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-clockwise"></i> Reset All
                            </a>
                        </div>
                    </div>
                </div>
            </form>
            
            @if(request()->hasAny(['search', 'status', 'from_date', 'to_date', 'warranty_no', 'invoice_no', 'product_name', 'customer_name']))
            <div class="alert alert-info mb-3">
                <i class="bi bi-info-circle"></i> 
                Showing filtered results. 
                <strong>{{ $warranties->total() }}</strong> warranty(ies) found.
            </div>
            @endif
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Warranty No</th>
                            <th>Invoice No</th>
                            <th>Product</th>
                            <th>Customer</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Period</th>
                            <th>Status</th>
                            <th>Days Remaining</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($warranties as $warranty)
                        <tr>
                            <td>
                                <strong>{{ $warranty->warranty_no }}</strong>
                            </td>
                            <td>
                                <a href="{{ route('sales.show', $warranty->sale) }}" class="text-decoration-none">
                                    {{ $warranty->sale->invoice_no }}
                                </a>
                            </td>
                            <td>{{ $warranty->product->name }}</td>
                            <td>{{ $warranty->customer->name ?? 'Walk-in' }}</td>
                            <td>{{ $warranty->start_date->format('M d, Y') }}</td>
                            <td>{{ $warranty->end_date->format('M d, Y') }}</td>
                            <td>{{ $warranty->warranty_period_months }} months</td>
                            <td>
                                @if($warranty->status === 'active')
                                    <span class="badge bg-success">Active</span>
                                @elseif($warranty->status === 'expired')
                                    <span class="badge bg-danger">Expired</span>
                                @elseif($warranty->status === 'claimed')
                                    <span class="badge bg-warning">Claimed</span>
                                @else
                                    <span class="badge bg-secondary">Void</span>
                                @endif
                            </td>
                            <td>
                                @if($warranty->is_active)
                                    <span class="text-success">{{ $warranty->days_remaining }} days</span>
                                @else
                                    <span class="text-danger">Expired</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('warranties.show', $warranty) }}" class="btn btn-sm btn-info">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">No warranties found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    <small class="text-muted">
                        Showing {{ $warranties->firstItem() ?? 0 }} to {{ $warranties->lastItem() ?? 0 }} 
                        of {{ $warranties->total() }} warranties
                    </small>
                </div>
                <div>
                    {{ $warranties->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Auto-focus search on page load
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput && !searchInput.value) {
            searchInput.focus();
        }
        
        // Show advanced filters if any filter is active
        @php
            $hasFilters = request()->hasAny(['status', 'from_date', 'to_date', 'warranty_no', 'invoice_no', 'product_name', 'customer_name']);
        @endphp
        const hasFilters = @json($hasFilters);
        if (hasFilters) {
            const advancedFilters = document.getElementById('advancedFilters');
            if (advancedFilters) {
                advancedFilters.classList.add('show');
            }
        }
    });
</script>
@endpush
@endsection
