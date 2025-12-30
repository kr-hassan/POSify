<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\Product;
use App\Http\Requests\StorePurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with(['supplier', 'purchaseItems.product']);
        
        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('purchase_date', [$request->from_date, $request->to_date]);
        } elseif ($request->has('from_date')) {
            $query->where('purchase_date', '>=', $request->from_date);
        } elseif ($request->has('to_date')) {
            $query->where('purchase_date', '<=', $request->to_date);
        }
        
        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        
        $purchases = $query->latest()->paginate(20);
        $suppliers = Supplier::all();
        
        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $products = Product::with('category')->get();
        return view('purchases.create', compact('suppliers', 'products'));
    }

    public function store(StorePurchaseRequest $request)
    {
        DB::beginTransaction();
        
        try {
            // Calculate total
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $totalAmount += $item['quantity'] * $item['cost_price'];
            }
            
            $dueAmount = $totalAmount - ($request->paid_amount ?? 0);
            
            // Create purchase
            $purchase = Purchase::create([
                'supplier_id' => $request->supplier_id,
                'total_amount' => $totalAmount,
                'paid_amount' => $request->paid_amount ?? 0,
                'due_amount' => max(0, $dueAmount),
                'purchase_date' => $request->purchase_date,
            ]);
            
            // Create purchase items and update stock
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                $purchase->purchaseItems()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                ]);
                
                // Update product cost price and increase stock
                $product->update([
                    'cost_price' => $item['cost_price'],
                ]);
                $product->increment('stock', $item['quantity']);
            }
            
            // Update supplier balance if due amount exists
            if ($dueAmount > 0) {
                $supplier = Supplier::find($request->supplier_id);
                if ($supplier) {
                    $supplier->increment('balance', $dueAmount);
                }
            }
            
            DB::commit();
            
            return redirect()->route('purchases.index')->with('success', 'Purchase created successfully.');
            
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error creating purchase: ' . $e->getMessage());
        }
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['supplier', 'purchaseItems.product.category']);
        return view('purchases.show', compact('purchase'));
    }

    public function destroy(Purchase $purchase)
    {
        DB::beginTransaction();
        
        try {
            // Restore stock
            foreach ($purchase->purchaseItems as $item) {
                $item->product->decrement('stock', $item->quantity);
            }
            
            // Update supplier balance if needed
            if ($purchase->due_amount > 0) {
                $purchase->supplier->decrement('balance', $purchase->due_amount);
            }
            
            $purchase->delete();
            
            DB::commit();
            
            return redirect()->route('purchases.index')->with('success', 'Purchase deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('purchases.index')->with('error', 'Error deleting purchase: ' . $e->getMessage());
        }
    }
}






