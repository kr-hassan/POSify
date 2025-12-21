@extends('layouts.app')

@section('title', 'Create Warranty')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Create Warranty - Sale: {{ $sale->invoice_no }}</h2>
        <a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Sale
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Warranty Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('warranties.store', $sale) }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Select Product *</label>
                            <select name="sale_item_id" class="form-select" required id="saleItemSelect">
                                <option value="">Select a product from this sale</option>
                                @foreach($sale->saleItems as $item)
                                    @if(!$item->warranty)
                                    <option value="{{ $item->id }}" 
                                            data-product-name="{{ $item->product->name }}"
                                            data-warranty-months="{{ $item->product->warranty_period_months ?? 12 }}">
                                        {{ $item->product->name }} (Qty: {{ $item->quantity }})
                                    </option>
                                    @endif
                                @endforeach
                            </select>
                            <small class="text-muted">Only products without existing warranties are shown</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Warranty Period (Months) *</label>
                            <input type="number" name="warranty_period_months" class="form-control" 
                                   id="warrantyPeriod" value="12" min="1" max="120" required>
                            <small class="text-muted">Default warranty period from product will be used if available</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Start Date *</label>
                            <input type="date" name="start_date" class="form-control" 
                                   value="{{ $sale->sale_date->format('Y-m-d') }}" required id="startDate">
                            <small class="text-muted">
                                <i class="bi bi-info-circle"></i> Warranty starts from sale date: <strong>{{ $sale->sale_date->format('M d, Y') }}</strong>
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">End Date (Auto-calculated)</label>
                            <input type="date" class="form-control" id="endDate" readonly>
                            <small class="text-muted">End date is automatically calculated based on start date and warranty period</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes (Optional)</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Additional warranty notes..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-shield-check"></i> Create Warranty
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
                    <h6>Sale Items:</h6>
                    <ul class="list-unstyled">
                        @foreach($sale->saleItems as $item)
                        <li>
                            {{ $item->product->name }} 
                            @if($item->warranty)
                                <span class="badge bg-success">Has Warranty</span>
                            @elseif($item->product->warranty_period_months > 0)
                                <span class="badge bg-info">Warranty Available ({{ $item->product->warranty_period_months }} months)</span>
                            @else
                                <span class="badge bg-secondary">No Warranty Period</span>
                            @endif
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    function calculateEndDate() {
        const startDate = $('#startDate').val();
        const months = parseInt($('#warrantyPeriod').val()) || 12;
        
        if (startDate) {
            const date = new Date(startDate);
            date.setMonth(date.getMonth() + months);
            const endDate = date.toISOString().split('T')[0];
            $('#endDate').val(endDate);
        }
    }

    $('#saleItemSelect').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const warrantyMonths = selectedOption.data('warranty-months');
        if (warrantyMonths) {
            $('#warrantyPeriod').val(warrantyMonths);
            calculateEndDate();
        }
    });

    $('#startDate, #warrantyPeriod').on('change', calculateEndDate);
    calculateEndDate();
});
</script>
@endpush
@endsection

