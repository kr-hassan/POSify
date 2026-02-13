@extends('layouts.app')

@section('title', 'User-Wise Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people"></i> User-Wise Report</h2>
        <div>
            <a href="{{ route('reports.users', array_merge(request()->all(), ['export' => 'pdf'])) }}" class="btn btn-danger">
                <i class="bi bi-file-pdf"></i> Export PDF
            </a>
        </div>
    </div>
    
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Filter by User (Optional)</label>
                    <select name="user_id" class="form-select">
                        <option value="">All Users</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ $selectedUserId == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label><br>
                    <button type="submit" class="btn btn-primary">Generate Report</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6>Total Sales</h6>
                    <h3>${{ number_format($grandTotalSales, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6>Total Paid</h6>
                    <h3>${{ number_format($grandTotalPaid, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h6>Total Due</h6>
                    <h3>${{ number_format($grandTotalDue, 2) }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6>Total Sales Count</h6>
                    <h3>{{ number_format($grandTotalSalesCount) }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- User Statistics Table -->
    <div class="card mb-3">
        <div class="card-header">
            <h5 class="mb-0">User Performance Summary</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Email</th>
                            <th>Total Sales</th>
                            <th>Sales Count</th>
                            <th>Avg Sale</th>
                            <th>Total Paid</th>
                            <th>Total Due</th>
                            <th>Payments Collected</th>
                            <th>Returns Processed</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($userStats as $stat)
                        <tr>
                            <td>
                                <strong>{{ $stat['user']->name }}</strong>
                                @if($stat['user']->roles->count() > 0)
                                    <br><small class="text-muted">
                                        @foreach($stat['user']->roles as $role)
                                            <span class="badge bg-secondary">{{ $role->name }}</span>
                                        @endforeach
                                    </small>
                                @endif
                            </td>
                            <td>{{ $stat['user']->email }}</td>
                            <td>
                                <strong class="text-primary">${{ number_format($stat['total_sales'], 2) }}</strong>
                            </td>
                            <td>{{ number_format($stat['sales_count']) }}</td>
                            <td>
                                @if($stat['sales_count'] > 0)
                                    ${{ number_format($stat['avg_sale'], 2) }}
                                @else
                                    $0.00
                                @endif
                            </td>
                            <td class="text-success">${{ number_format($stat['total_paid'], 2) }}</td>
                            <td>
                                @if($stat['total_due'] > 0)
                                    <span class="text-danger">${{ number_format($stat['total_due'], 2) }}</span>
                                @else
                                    <span class="text-success">$0.00</span>
                                @endif
                            </td>
                            <td>
                                <span class="text-info">${{ number_format($stat['payments_collected'], 2) }}</span>
                                <br><small class="text-muted">({{ $stat['payments_count'] }} payments)</small>
                            </td>
                            <td>
                                <span class="text-warning">{{ $stat['returns_processed'] }}</span>
                                @if($stat['returns_processed'] > 0)
                                    <br><small class="text-muted">${{ number_format($stat['returns_amount'], 2) }}</small>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('reports.users', array_merge(request()->all(), ['user_id' => $stat['user']->id])) }}" 
                                   class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> View Details
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">No data found for the selected period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="table-info">
                            <th colspan="2">Grand Total</th>
                            <th>${{ number_format($grandTotalSales, 2) }}</th>
                            <th>{{ number_format($grandTotalSalesCount) }}</th>
                            <th>
                                @if($grandTotalSalesCount > 0)
                                    ${{ number_format($grandTotalSales / $grandTotalSalesCount, 2) }}
                                @else
                                    $0.00
                                @endif
                            </th>
                            <th>${{ number_format($grandTotalPaid, 2) }}</th>
                            <th>${{ number_format($grandTotalDue, 2) }}</th>
                            <th>${{ number_format($grandTotalPayments, 2) }}</th>
                            <th>${{ number_format($grandTotalReturns, 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    <!-- Detailed Sales for Selected User -->
    @if($selectedUserId && $detailedSales)
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Detailed Sales for {{ $users->find($selectedUserId)->name }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Invoice No</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Due</th>
                            <th>Payment Method</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($detailedSales as $sale)
                        <tr>
                            <td>{{ $sale->invoice_no }}</td>
                            <td>{{ $sale->sale_date->format('M d, Y') }}</td>
                            <td>{{ $sale->customer->name ?? 'Walk-in' }}</td>
                            <td>{{ $sale->saleItems->count() }} items</td>
                            <td>${{ number_format($sale->total_amount, 2) }}</td>
                            <td>${{ number_format($sale->paid_amount, 2) }}</td>
                            <td>
                                @if($sale->due_amount > 0)
                                    <span class="text-danger">${{ number_format($sale->due_amount, 2) }}</span>
                                @else
                                    <span class="text-success">$0.00</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ ucfirst($sale->payment_method) }}</span>
                            </td>
                            <td>
                                <a href="{{ route('sales.show', $sale) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection



