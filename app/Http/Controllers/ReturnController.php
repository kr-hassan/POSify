<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\ProductReturn;
use App\Services\ReturnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * ReturnController
 * 
 * Handles product return workflow (refund/exchange).
 * 
 * Permissions:
 * - return.create: Create returns
 * - return.approve: Approve returns (Admin/Manager)
 * - refund.process: Process refunds/exchanges
 */
class ReturnController extends Controller
{
    protected $returnService;

    public function __construct(ReturnService $returnService)
    {
        $this->returnService = $returnService;
    }

    /**
     * Show form to create return from sale
     */
    public function createFromSale(Sale $sale)
    {
        // Check permission
        if (!Gate::allows('return.create')) {
            abort(403, 'You do not have permission to create returns.');
        }

        $sale->load(['saleItems.product', 'saleItems.warranty', 'customer']);
        
        // Get returnable items (items that haven't been fully returned)
        $returnableItems = $this->returnService->getReturnableItems($sale);

        if ($returnableItems->isEmpty()) {
            return redirect()->route('sales.show', $sale)
                ->with('error', 'No items available for return.');
        }

        return view('returns.create-from-sale', compact('sale', 'returnableItems'));
    }

    /**
     * Store return from sale
     */
    public function storeFromSale(Request $request, Sale $sale)
    {
        // Check permission
        if (!Gate::allows('return.create')) {
            abort(403, 'You do not have permission to create returns.');
        }

        $request->validate([
            'return_type' => 'required|in:refund,exchange',
            'return_date' => 'required|date',
            'reason' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.sale_item_id' => 'required|exists:sale_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.reason' => 'nullable|string',
        ]);

        try {
            $return = $this->returnService->createReturn($sale, [
                'return_type' => $request->return_type,
                'return_date' => $request->return_date,
                'reason' => $request->reason,
                'items' => $request->items,
            ]);

            return redirect()->route('returns.show', $return)
                ->with('success', 'Return created successfully. Waiting for approval.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating return: ' . $e->getMessage());
        }
    }

    /**
     * List all returns
     */
    public function index(Request $request)
    {
        $query = ProductReturn::with(['sale', 'customer', 'creator', 'returnItems.saleItem.product']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('return_type')) {
            $query->where('return_type', $request->return_type);
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

        return view('returns.index', compact('returns'));
    }

    /**
     * Show return details
     */
    public function show(ProductReturn $productReturn)
    {
        $productReturn->load(['sale', 'customer', 'creator', 'approver', 'returnItems.saleItem.product']);

        return view('returns.show', compact('productReturn'));
    }

    /**
     * Approve return (Admin/Manager only)
     */
    public function approve(ProductReturn $productReturn)
    {
        // Check permission
        if (!Gate::allows('return.approve')) {
            abort(403, 'You do not have permission to approve returns.');
        }

        try {
            $this->returnService->approveReturn($productReturn);

            return redirect()->back()->with('success', 'Return approved successfully.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Process return (execute refund/exchange)
     */
    public function process(ProductReturn $productReturn)
    {
        // Check permission
        if (!Gate::allows('refund.process')) {
            abort(403, 'You do not have permission to process returns.');
        }

        try {
            $this->returnService->processReturn($productReturn);

            return redirect()->back()->with('success', 'Return processed successfully. Inventory and financial adjustments have been made.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Reject return
     */
    public function reject(Request $request, ProductReturn $productReturn)
    {
        // Check permission
        if (!Gate::allows('return.approve')) {
            abort(403, 'You do not have permission to reject returns.');
        }

        $request->validate([
            'reason' => 'nullable|string',
        ]);

        try {
            $this->returnService->rejectReturn($productReturn, $request->reason);

            return redirect()->back()->with('success', 'Return rejected.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
