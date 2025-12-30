@extends('layouts.app')

@section('title', 'Customer Due Report')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Customer Due Report</h2>
    </div>
    
    <div class="card mb-3">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h5>Total Due Amount</h5>
                            <h3>-${{ number_format($totalDue, 2) }}</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-info text-white">
                        <div class="card-body">
                            <h5>Customers with Due</h5>
                            <h3>{{ $customers->count() }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Due Balance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $customer)
                        <tr>
                            <td>{{ $customer->name }}</td>
                            <td>{{ $customer->phone ?? '-' }}</td>
                            <td>{{ $customer->email ?? '-' }}</td>
                            <td>
                                <span class="badge bg-danger">
                                    -${{ number_format($customer->balance, 2) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('customers.show', $customer) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye"></i> View Details
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No customers with due amounts</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection





