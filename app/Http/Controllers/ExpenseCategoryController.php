<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use App\Http\Requests\StoreExpenseCategoryRequest;
use App\Http\Requests\UpdateExpenseCategoryRequest;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function index()
    {
        $categories = ExpenseCategory::withCount('expenses')->latest()->paginate(15);
        return view('expense-categories.index', compact('categories'));
    }

    public function create()
    {
        return view('expense-categories.create');
    }

    public function store(StoreExpenseCategoryRequest $request)
    {
        ExpenseCategory::create($request->validated());
        return redirect()->route('expense-categories.index')->with('success', 'Expense category created successfully.');
    }

    public function edit(ExpenseCategory $expenseCategory)
    {
        return view('expense-categories.edit', compact('expenseCategory'));
    }

    public function update(UpdateExpenseCategoryRequest $request, ExpenseCategory $expenseCategory)
    {
        $expenseCategory->update($request->validated());
        return redirect()->route('expense-categories.index')->with('success', 'Expense category updated successfully.');
    }

    public function destroy(ExpenseCategory $expenseCategory)
    {
        if ($expenseCategory->expenses()->count() > 0) {
            return redirect()->route('expense-categories.index')->with('error', 'Cannot delete category with expenses.');
        }
        
        $expenseCategory->delete();
        return redirect()->route('expense-categories.index')->with('success', 'Expense category deleted successfully.');
    }
}




