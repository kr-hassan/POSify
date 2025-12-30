@extends('layouts.app')

@section('title', 'Expenses')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Expenses</h2>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add Expense
        </a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form method="GET" class="row g-3 mb-3">
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label><br>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('expenses.index') }}" class="btn btn-secondary">Reset</a>
                </div>
            </form>
            
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Note</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $expense)
                        <tr>
                            <td>{{ $expense->expense_date->format('M d, Y') }}</td>
                            <td>{{ $expense->category->name }}</td>
                            <td>${{ number_format($expense->amount, 2) }}</td>
                            <td>{{ Str::limit($expense->note, 50) }}</td>
                            <td>
                                <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('expenses.destroy', $expense) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No expenses found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{ $expenses->links() }}
        </div>
    </div>
</div>
@endsection






