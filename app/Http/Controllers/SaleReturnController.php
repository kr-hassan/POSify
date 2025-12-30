<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SaleReturnController extends Controller
{
    public function create(Sale $sale)
    {
        $sale->load(['saleItems.product', 'customer']);
        $totalReturned = $sale->total_returned;
        $remainingAmount = $sale->total_amount - $totalReturned;
        
        return view('sale-returns.create', compact('sale', 'totalReturned', 'remainingAmount'));
    }

    public function store(Request $request, Sale $sale)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'reason' => 'nullable|string',
            'return_date' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            // Generate return number
            $returnNo = 'RET-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            // Calculate total return amount
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['price'];
            }

            // Check if return amount exceeds sale amount
            $totalReturned = $sale->total_returned;
            $remainingAmount = $sale->total_amount - $totalReturned;
            
            if ($totalAmount > $remainingAmount) {
                throw new \Exception("Return amount cannot exceed remaining sale amount. Remaining: $" . number_format($remainingAmount, 2));
            }

            // Create sale return
            $saleReturn = SaleReturn::create([
                'return_no' => $returnNo,
                'sale_id' => $sale->id,
                'user_id' => auth()->id(),
                'customer_id' => $sale->customer_id,
                'total_amount' => $totalAmount,
                'reason' => $request->reason,
                'return_date' => $request->return_date,
            ]);

            // Create return items and restore stock
            foreach ($request->items as $item) {
                $product = \App\Models\Product::findOrFail($item['product_id']);
                $itemTotal = $item['quantity'] * $item['price'];

                $saleReturn->saleReturnItems()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $itemTotal,
                ]);

                // Restore stock
                $product->increment('stock', $item['quantity']);
            }

            // Adjust customer balance if customer exists and had due amount
            if ($sale->customer_id && $sale->due_amount > 0) {
                $customer = $sale->customer;
                // Reduce customer balance by return amount
                if ($customer->balance >= $totalAmount) {
                    $customer->decrement('balance', $totalAmount);
                } else {
                    // If balance is less than return, set to 0
                    $customer->update(['balance' => 0]);
                }

                // Adjust sale due amount
                $returnRatio = $totalAmount / $sale->total_amount;
                $dueReduction = min($sale->due_amount, $totalAmount * $returnRatio);
                if ($dueReduction > 0) {
                    $sale->decrement('due_amount', $dueReduction);
                    $sale->increment('paid_amount', $dueReduction);
                }
            }

            DB::commit();

            return redirect()->route('sales.show', $sale)
                ->with('success', 'Product return processed successfully. Return No: ' . $returnNo);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $query = SaleReturn::with(['sale', 'customer', 'user']);

        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('return_date', [$request->from_date, $request->to_date]);
        } elseif ($request->has('from_date')) {
            $query->where('return_date', '>=', $request->from_date);
        } elseif ($request->has('to_date')) {
            $query->where('return_date', '<=', $request->to_date);
        }

        if ($request->has('return_no')) {
            $query->where('return_no', 'like', "%{$request->return_no}%");
        }

        $returns = $query->latest()->paginate(20);

        return view('sale-returns.index', compact('returns'));
    }

    public function show(SaleReturn $saleReturn)
    {
        $saleReturn->load(['sale', 'customer', 'user', 'saleReturnItems.product']);
        return view('sale-returns.show', compact('saleReturn'));
    }
}





