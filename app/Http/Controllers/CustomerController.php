<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();
        
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        $customers = $query->latest()->paginate(20);
        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(StoreCustomerRequest $request)
    {
        Customer::create($request->validated());
        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function show(Customer $customer)
    {
        $customer->load(['sales.saleItems.product', 'payments.user']);
        
        // Calculate total due from all sales
        $totalDue = $customer->sales()->sum('due_amount');
        
        // Recalculate balance to ensure accuracy
        $calculatedBalance = $customer->sales()->sum('due_amount');
        
        // If balance doesn't match, show a warning
        $balanceMismatch = abs($customer->balance - $calculatedBalance) > 0.01;
        
        return view('customers.show', compact('customer', 'totalDue', 'calculatedBalance', 'balanceMismatch'));
    }
    
    public function recalculateBalance(Customer $customer)
    {
        // Recalculate balance from all sales
        $calculatedBalance = $customer->sales()->sum('due_amount');
        $customer->update(['balance' => $calculatedBalance]);
        
        return redirect()->route('customers.show', $customer)->with('success', 'Customer balance recalculated successfully.');
    }
    
    public function addPayment(Request $request, Customer $customer)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,card,mobile',
            'note' => 'nullable|string',
        ]);
        
        DB::beginTransaction();
        
        try {
            $amount = $request->amount;
            
            // Don't allow payment more than balance
            if ($amount > $customer->balance) {
                return redirect()->back()->with('error', 'Payment amount cannot exceed customer balance.');
            }
            
            // Generate receipt number
            $receiptNo = 'RCP-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            
            // Create payment record
            $payment = Payment::create([
                'receipt_no' => $receiptNo,
                'customer_id' => $customer->id,
                'amount' => $amount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'note' => $request->note,
                'user_id' => auth()->id(),
            ]);
            
            // Reduce customer balance
            $customer->decrement('balance', $amount);
            
            // Update related sales' due amounts
            $remainingPayment = $amount;
            $sales = $customer->sales()->where('due_amount', '>', 0)->orderBy('sale_date')->get();
            
            foreach ($sales as $sale) {
                if ($remainingPayment <= 0) break;
                
                if ($sale->due_amount > 0) {
                    $paymentToSale = min($remainingPayment, $sale->due_amount);
                    $sale->increment('paid_amount', $paymentToSale);
                    $sale->decrement('due_amount', $paymentToSale);
                    $remainingPayment -= $paymentToSale;
                }
            }
            
            DB::commit();
            
            // Return JSON response for AJAX
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Payment recorded successfully',
                    'payment_id' => $payment->id,
                    'receipt_url' => route('payments.receipt', $payment),
                ]);
            }
            
            // Fallback for non-AJAX requests
            return redirect()->route('payments.receipt', $payment)
                ->with('success', 'Payment recorded successfully. Receipt will be printed automatically.');
        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }
            
            return redirect()->back()->with('error', 'Error recording payment: ' . $e->getMessage());
        }
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        $customer->update($request->validated());
        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->sales()->count() > 0) {
            return redirect()->route('customers.index')->with('error', 'Cannot delete customer with sales history.');
        }
        
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}

