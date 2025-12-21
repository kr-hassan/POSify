@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Dashboard</h2>
        <a href="{{ route('pos.index') }}" class="btn btn-success btn-lg">
            <i class="bi bi-cart"></i> Start POS
        </a>
    </div>
    
    <div class="alert alert-info mb-4">
        <h5><i class="bi bi-info-circle"></i> Quick Start Guide</h5>
        <p><strong>Welcome to POS System!</strong> Here's how to get started:</p>
        <ol>
            <li><strong>Add Categories:</strong> Go to <a href="{{ route('categories.index') }}">Categories</a> → Click "Add Category" button</li>
            <li><strong>Add Products:</strong> Go to <a href="{{ route('products.index') }}">Products</a> → Click "Add Product" button</li>
            <li><strong>Start Selling:</strong> Go to <a href="{{ route('pos.index') }}">POS</a> → Search products → Add to cart → Complete sale</li>
        </ol>
        <p class="mb-0"><small>💡 <strong>Tip:</strong> Use the sidebar menu on the left to navigate. All "Add" buttons are in the top-right corner of each page.</small></p>
    </div>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Today's Sales</h5>
                    <h3 class="mb-0">${{ number_format($todaySales, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info">
                <div class="card-body">
                    <h5 class="card-title">Today's Purchases</h5>
                    <h3 class="mb-0">${{ number_format($todayPurchases, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Today's Expenses</h5>
                    <h3 class="mb-0">${{ number_format($todayExpenses, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success">
                <div class="card-body">
                    <h5 class="card-title">This Month Sales</h5>
                    <h3 class="mb-0">${{ number_format($monthSales, 2) }}</h3>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Low Stock Products -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Low Stock Products</h5>
                </div>
                <div class="card-body">
                    @if($lowStockProducts->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Stock</th>
                                        <th>Alert Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lowStockProducts as $product)
                                    <tr>
                                        <td>{{ $product->name }}</td>
                                        <td><span class="badge bg-danger">{{ $product->stock }}</span></td>
                                        <td>{{ $product->alert_quantity }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted mb-0">No low stock products</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Recent Sales -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Sales</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Customer</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSales as $sale)
                                <tr>
                                    <td>{{ $sale->invoice_no }}</td>
                                    <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                                    <td>${{ number_format($sale->total_amount, 2) }}</td>
                                    <td>{{ $sale->sale_date->format('M d, Y') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No recent sales</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
