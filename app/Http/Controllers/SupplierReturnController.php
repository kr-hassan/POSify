<?php

namespace App\Http\Controllers;

use App\Models\SupplierReturn;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\SupplierReplacement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SupplierReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = SupplierReturn::with(['supplier', 'user', 'items.product']);

        if ($request->has('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

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
        $suppliers = Supplier::all();

        return view('supplier-returns.index', compact('returns', 'suppliers'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $products = Product::where('stock', '>', 0)->get();
        return view('supplier-returns.create', compact('suppliers', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'return_date' => 'required|date',
            'reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.cost_price' => 'required|numeric|min:0',
            'items.*.reason' => 'nullable|string|max:255',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();

        try {
            // Generate return number
            $returnNo = 'SRT-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            // Calculate total amount
            $totalAmount = 0;
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                // Check stock availability
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}. Available: {$product->stock}");
                }

                $itemTotal = $item['quantity'] * $item['cost_price'];
                $totalAmount += $itemTotal;
            }

            // Create supplier return
            $supplierReturn = SupplierReturn::create([
                'return_no' => $returnNo,
                'supplier_id' => $request->supplier_id,
                'user_id' => auth()->id(),
                'total_amount' => $totalAmount,
                'reason' => $request->reason,
                'notes' => $request->notes,
                'status' => 'pending',
                'return_date' => $request->return_date,
            ]);

            // Create return items and reduce stock
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $itemTotal = $item['quantity'] * $item['cost_price'];

                $supplierReturn->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                    'total' => $itemTotal,
                    'reason' => $item['reason'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);

                // Reduce stock immediately when return is created
                $product->decrement('stock', $item['quantity']);
            }

            DB::commit();

            return redirect()->route('supplier-returns.show', $supplierReturn)
                ->with('success', 'Supplier return created successfully. Stock has been reduced.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Error creating supplier return: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show(SupplierReturn $supplierReturn)
    {
        $supplierReturn->load(['supplier', 'user', 'items.product', 'replacements.product', 'replacements.user']);
        $products = Product::all();
        return view('supplier-returns.show', compact('supplierReturn', 'products'));
    }

    public function updateStatus(Request $request, SupplierReturn $supplierReturn)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,completed,rejected',
        ]);

        DB::beginTransaction();

        try {
            $oldStatus = $supplierReturn->status;
            $newStatus = $request->status;

            // If rejecting a completed return, restore stock
            if ($oldStatus === 'completed' && $newStatus === 'rejected') {
                foreach ($supplierReturn->items as $item) {
                    $item->product->increment('stock', $item->quantity);
                }
            }

            // If completing a return, mark processed date
            if ($newStatus === 'completed' && $oldStatus !== 'completed') {
                $supplierReturn->update([
                    'status' => $newStatus,
                    'processed_date' => now(),
                ]);
            } else {
                $supplierReturn->update(['status' => $newStatus]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Return status updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating status: ' . $e->getMessage());
        }
    }

    public function destroy(SupplierReturn $supplierReturn)
    {
        if ($supplierReturn->status === 'completed') {
            return redirect()->back()->with('error', 'Cannot delete a completed return.');
        }

        DB::beginTransaction();

        try {
            // Restore stock if return is deleted
            foreach ($supplierReturn->items as $item) {
                $item->product->increment('stock', $item->quantity);
            }

            $supplierReturn->delete();

            DB::commit();

            return redirect()->route('supplier-returns.index')
                ->with('success', 'Supplier return deleted successfully. Stock has been restored.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error deleting return: ' . $e->getMessage());
        }
    }

    public function processRefund(Request $request, SupplierReturn $supplierReturn)
    {
        $request->validate([
            'refund_amount' => 'required|numeric|min:0|max:' . $supplierReturn->total_amount,
            'refund_date' => 'required|date',
            'refund_method' => 'required|string|max:255',
            'refund_notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            $supplierReturn->update([
                'settlement_type' => 'refund',
                'refund_amount' => $request->refund_amount,
                'refund_date' => $request->refund_date,
                'refund_method' => $request->refund_method,
                'refund_notes' => $request->refund_notes,
                'is_settled' => true,
                'settled_date' => now(),
                'status' => 'completed',
            ]);

            // Update supplier balance
            // When supplier refunds us for returned damaged products:
            // - If we owe them money (positive balance), refund reduces what we owe
            // - If balance is 0 or negative, refund makes them owe us (negative balance)
            if ($supplierReturn->supplier) {
                $supplier = $supplierReturn->supplier;
                // Reduce balance by refund amount (can go negative if they owe us)
                $supplier->decrement('balance', $request->refund_amount);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Refund processed successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error processing refund: ' . $e->getMessage());
        }
    }

    public function processReplacement(Request $request, SupplierReturn $supplierReturn)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.cost_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
            'replacement_date' => 'required|date',
        ]);

        DB::beginTransaction();

        try {
            // Create replacement records and add products back to stock
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                SupplierReplacement::create([
                    'supplier_return_id' => $supplierReturn->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'cost_price' => $item['cost_price'],
                    'received_date' => $request->replacement_date,
                    'notes' => $item['notes'] ?? null,
                ]);

                // Add replacement products back to stock
                $product->increment('stock', $item['quantity']);
            }

            // Update supplier return status
            $supplierReturn->update([
                'settlement_type' => 'replacement',
                'is_settled' => true,
                'settled_date' => now(),
                'status' => 'completed',
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Replacement processed successfully. Products have been added back to stock.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error processing replacement: ' . $e->getMessage());
        }
    }
}
