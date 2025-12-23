@extends('layouts.app')

@section('title', 'Create Repair Claim')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Create Repair Claim - {{ $sale->invoice_no }}</h2>
        <a href="{{ route('sales.show', $sale) }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Sale
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Repair Claim Information</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('repairs.store-from-sale', $sale) }}">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label">Select Item with Warranty *</label>
                            <select name="sale_item_id" class="form-select" required>
                                <option value="">-- Select Item --</option>
                                @foreach($returnableItems as $item)
                                    @if($item->warranty && $item->warranty->is_active)
                                    <option value="{{ $item->id }}">
                                        {{ $item->product->name }} - 
                                        Warranty: {{ $item->warranty->warranty_no }}
                                        (Valid until: {{ $item->warranty->end_date->format('M d, Y') }})
                                    </option>
                                    @endif
                                @endforeach
                            </select>
                            <small class="text-muted">Only items with active warranties are shown</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Issue Description *</label>
                            <textarea name="issue_description" class="form-control" rows="5" 
                                      placeholder="Describe the issue with the product that needs repair..." required minlength="10"></textarea>
                            <small class="text-muted">Minimum 10 characters required</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Claim Date *</label>
                            <input type="date" name="claim_date" class="form-control" 
                                   value="{{ date('Y-m-d') }}" required>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-tools"></i> Create Repair Claim
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
                    <p><strong>Invoice No:</strong> {{ $sale->invoice_no }}</p>
                    <p><strong>Date:</strong> {{ $sale->sale_date->format('M d, Y') }}</p>
                    <p><strong>Customer:</strong> {{ $sale->customer->name ?? 'Walk-in' }}</p>
                    <hr>
                    <h6>Items with Active Warranties</h6>
                    @foreach($returnableItems as $item)
                        @if($item->warranty && $item->warranty->is_active)
                        <p class="mb-2">
                            <strong>{{ $item->product->name }}</strong><br>
                            <small>
                                Warranty: {{ $item->warranty->warranty_no }}<br>
                                Valid until: {{ $item->warranty->end_date->format('M d, Y') }}<br>
                                <span class="badge bg-success">{{ $item->warranty->days_remaining }} days remaining</span>
                            </small>
                        </p>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


