<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with(['customer', 'user']);
        
        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('payment_date', [$request->from_date, $request->to_date]);
        } elseif ($request->has('from_date')) {
            $query->where('payment_date', '>=', $request->from_date);
        } elseif ($request->has('to_date')) {
            $query->where('payment_date', '<=', $request->to_date);
        }
        
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }
        
        if ($request->has('receipt_no')) {
            $query->where('receipt_no', 'like', "%{$request->receipt_no}%");
        }
        
        $payments = $query->latest()->paginate(20);
        
        return view('payments.index', compact('payments'));
    }

    public function receipt(Payment $payment)
    {
        $payment->load(['customer', 'user']);
        return view('payments.receipt', compact('payment'));
    }
}


