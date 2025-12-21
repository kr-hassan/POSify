<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Http\Requests\StoreSaleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['user', 'customer', 'saleItems.product']);
        
        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('sale_date', [$request->from_date, $request->to_date]);
        } elseif ($request->has('from_date')) {
            $query->where('sale_date', '>=', $request->from_date);
        } elseif ($request->has('to_date')) {
            $query->where('sale_date', '<=', $request->to_date);
        }
        
        if ($request->has('invoice_no')) {
            $query->where('invoice_no', 'like', "%{$request->invoice_no}%");
        }
        
        $sales = $query->latest()->paginate(20);
        return view('sales.index', compact('sales'));
    }

    public function store(StoreSaleRequest $request)
    {
        DB::beginTransaction();
        
        try {
            // Generate invoice number
            $invoiceNo = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            
            // Calculate totals
            $subtotal = 0;
            $tax = 0;
            
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Check stock availability
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}. Available: {$product->stock}");
                }
                
                $itemTotal = $item['quantity'] * $item['price'];
                $subtotal += $itemTotal;
                
                // Calculate tax if applicable
                if ($product->tax_percent > 0) {
                    $tax += ($itemTotal * $product->tax_percent) / 100;
                }
            }
            
            // Apply discount
            $discount = 0;
            if ($request->discount) {
                if ($request->discount_type === 'percent') {
                    $discount = ($subtotal * $request->discount) / 100;
                } else {
                    $discount = $request->discount;
                }
            }
            
            $totalAmount = $subtotal + $tax - $discount;
            $dueAmount = $totalAmount - $request->paid_amount;
            
            // If no customer selected (walk-in), payment must be full
            if (!$request->customer_id && $dueAmount > 0) {
                throw new \Exception("Walk-in customers must pay in full. Please select a customer for partial payment or pay the full amount.");
            }
            
            // Ensure paid amount is not less than total for walk-in customers
            if (!$request->customer_id && $request->paid_amount < $totalAmount) {
                throw new \Exception("Walk-in customers must pay the full amount of $" . number_format($totalAmount, 2));
            }
            
            // Create sale
            $sale = Sale::create([
                'invoice_no' => $invoiceNo,
                'user_id' => auth()->id(),
                'customer_id' => $request->customer_id,
                'total_amount' => $totalAmount,
                'discount' => $discount,
                'tax' => $tax,
                'paid_amount' => $request->customer_id ? $request->paid_amount : $totalAmount, // Force full payment for walk-in
                'due_amount' => $request->customer_id ? max(0, $dueAmount) : 0, // No due for walk-in
                'payment_method' => $request->payment_method,
                'sale_date' => now()->toDateString(),
            ]);
            
            // Create sale items and update stock
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $itemTotal = $item['quantity'] * $item['price'];
                
                $sale->saleItems()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $itemTotal,
                ]);
                
                // Reduce stock
                $product->decrement('stock', $item['quantity']);
            }
            
            // Update customer balance if due amount exists
            if ($dueAmount > 0 && $request->customer_id) {
                $customer = \App\Models\Customer::find($request->customer_id);
                if ($customer) {
                    // Only add to balance if there's actually a due amount
                    $customer->increment('balance', $dueAmount);
                }
            } elseif ($request->customer_id && $dueAmount <= 0) {
                // If fully paid, ensure balance doesn't go negative
                $customer = \App\Models\Customer::find($request->customer_id);
                if ($customer && $customer->balance < 0) {
                    $customer->update(['balance' => 0]);
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully',
                'sale_id' => $sale->id,
                'invoice_no' => $invoiceNo,
                'invoice_url' => route('sales.invoice', $sale),
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function show(Sale $sale)
    {
        $sale->load(['user', 'customer', 'saleItems.product.category']);
        return view('sales.show', compact('sale'));
    }

    public function invoice(Sale $sale)
    {
        $sale->load(['user', 'customer', 'saleItems.product.category']);
        return view('sales.invoice', compact('sale'));
    }

    public function destroy(Sale $sale)
    {
        DB::beginTransaction();
        
        try {
            // Restore stock
            foreach ($sale->saleItems as $item) {
                $item->product->increment('stock', $item->quantity);
            }
            
            // Update customer balance if needed
            if ($sale->due_amount > 0 && $sale->customer_id) {
                $sale->customer->decrement('balance', $sale->due_amount);
            }
            
            $sale->delete();
            
            DB::commit();
            
            return redirect()->route('sales.index')->with('success', 'Sale deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('sales.index')->with('error', 'Error deleting sale: ' . $e->getMessage());
        }
    }
}

