<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Warranty;
use App\Models\WarrantyClaim;
use App\Services\RepairService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/**
 * RepairController
 * 
 * Handles repair workflow for warranty-based repairs.
 * 
 * Permissions:
 * - repair.create: Create repair claims
 * - repair.process: Process repair claims (mark as received, completed, returned)
 * - repair.complete: Complete repair workflow
 */
class RepairController extends Controller
{
    protected $repairService;

    public function __construct(RepairService $repairService)
    {
        $this->repairService = $repairService;
    }

    /**
     * Show form to create repair claim from sale
     */
    public function createFromSale(Sale $sale)
    {
        // Check permission
        if (!Gate::allows('repair.create')) {
            abort(403, 'You do not have permission to create repair claims.');
        }

        $sale->load(['saleItems.product', 'saleItems.warranty', 'customer']);
        
        // Get sale items with active warranties
        $returnableItems = $sale->saleItems->filter(function ($item) {
            return $item->warranty && $item->warranty->is_active;
        });

        if ($returnableItems->isEmpty()) {
            return redirect()->route('sales.show', $sale)
                ->with('error', 'No items with active warranties found for this sale.');
        }

        return view('repairs.create-from-sale', compact('sale', 'returnableItems'));
    }

    /**
     * Store repair claim from sale item
     */
    public function storeFromSale(Request $request, Sale $sale)
    {
        // Check permission
        if (!Gate::allows('repair.create')) {
            abort(403, 'You do not have permission to create repair claims.');
        }

        $request->validate([
            'sale_item_id' => 'required|exists:sale_items,id',
            'issue_description' => 'required|string|min:10',
            'claim_date' => 'required|date',
        ]);

        $saleItem = SaleItem::findOrFail($request->sale_item_id);
        
        // Verify sale item belongs to this sale
        if ($saleItem->sale_id !== $sale->id) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Invalid sale item selected.');
        }

        // Get warranty for this sale item
        $warranty = $saleItem->warranty;
        
        if (!$warranty) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'No warranty found for this item.');
        }

        try {
            $claim = $this->repairService->createRepairClaim($warranty, [
                'issue_description' => $request->issue_description,
                'claim_date' => $request->claim_date,
            ]);

            return redirect()->route('repairs.show', $claim)
                ->with('success', 'Repair claim created successfully.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating repair claim: ' . $e->getMessage());
        }
    }

    /**
     * List all repair claims
     */
    public function index(Request $request)
    {
        $query = WarrantyClaim::with(['warranty.sale', 'warranty.product', 'warranty.customer', 'user'])
            ->where('claim_type', 'repair');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('from_date') && $request->has('to_date')) {
            $query->whereBetween('claim_date', [$request->from_date, $request->to_date]);
        } elseif ($request->has('from_date')) {
            $query->where('claim_date', '>=', $request->from_date);
        } elseif ($request->has('to_date')) {
            $query->where('claim_date', '<=', $request->to_date);
        }

        if ($request->has('claim_no')) {
            $query->where('claim_no', 'like', "%{$request->claim_no}%");
        }

        $claims = $query->latest()->paginate(20);

        return view('repairs.index', compact('claims'));
    }

    /**
     * Show repair claim details
     */
    public function show(WarrantyClaim $repair)
    {
        // Verify it's a repair claim
        if ($repair->claim_type !== 'repair') {
            abort(404);
        }

        $repair->load(['warranty.sale', 'warranty.product', 'warranty.customer', 'user']);

        return view('repairs.show', compact('repair'));
    }

    /**
     * Mark repair claim as received (product received for repair)
     */
    public function markAsReceived(Request $request, WarrantyClaim $repair)
    {
        // Check permission
        if (!Gate::allows('repair.process')) {
            abort(403, 'You do not have permission to process repairs.');
        }

        // Verify it's a repair claim
        if ($repair->claim_type !== 'repair') {
            abort(404);
        }

        $request->validate([
            'received_date' => 'required|date',
            'technician_notes' => 'nullable|string',
        ]);

        try {
            $this->repairService->markAsReceived($repair, [
                'received_date' => $request->received_date,
                'technician_notes' => $request->technician_notes,
            ]);

            return redirect()->back()->with('success', 'Repair claim marked as received.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Mark repair claim as completed (repair finished)
     */
    public function markAsCompleted(Request $request, WarrantyClaim $repair)
    {
        // Check permission
        if (!Gate::allows('repair.complete')) {
            abort(403, 'You do not have permission to complete repairs.');
        }

        // Verify it's a repair claim
        if ($repair->claim_type !== 'repair') {
            abort(404);
        }

        $request->validate([
            'resolved_date' => 'required|date',
            'resolution_notes' => 'nullable|string',
            'technician_notes' => 'nullable|string',
        ]);

        try {
            $this->repairService->markAsCompleted($repair, [
                'resolved_date' => $request->resolved_date,
                'resolution_notes' => $request->resolution_notes,
                'technician_notes' => $request->technician_notes,
            ]);

            return redirect()->back()->with('success', 'Repair claim marked as completed.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Show return receipt for repaired product
     */
    public function returnReceipt(WarrantyClaim $repair)
    {
        // Verify it's a repair claim
        if ($repair->claim_type !== 'repair') {
            abort(404);
        }

        // Verify product has been returned
        if (!$repair->returned_date) {
            return redirect()->route('repairs.show', $repair)
                ->with('error', 'Product has not been returned yet.');
        }

        $repair->load(['warranty.sale', 'warranty.product', 'warranty.customer', 'user']);
        return view('repairs.return-receipt', compact('repair'));
    }

    /**
     * Mark repair claim as returned (product returned to customer)
     * 
     * This allows marking a completed repair as returned to the customer.
     * Users with repair.process or repair.complete permission can do this.
     */
    public function markAsReturned(Request $request, WarrantyClaim $repair)
    {
        // Check permission - allow both repair.process and repair.complete
        if (!Gate::allows('repair.complete') && !Gate::allows('repair.process')) {
            abort(403, 'You do not have permission to return repaired products to customers.');
        }

        // Verify it's a repair claim
        if ($repair->claim_type !== 'repair') {
            abort(404);
        }

        // Verify repair is completed
        if ($repair->status !== 'completed') {
            return redirect()->back()->with('error', 'Cannot return product. Repair must be completed first.');
        }

        // Verify product hasn't been returned already
        if ($repair->returned_date) {
            return redirect()->back()->with('error', 'Product has already been returned to customer.');
        }

        $request->validate([
            'returned_date' => 'required|date',
        ]);

        try {
            $this->repairService->markAsReturned($repair, [
                'returned_date' => $request->returned_date,
            ]);

            // Return JSON response for AJAX or redirect to receipt
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Product successfully marked as returned to customer.',
                    'receipt_url' => route('repairs.return-receipt', $repair),
                ]);
            }

            // Redirect to receipt page which will auto-print
            return redirect()->route('repairs.return-receipt', $repair)
                ->with('success', 'Product successfully returned. Receipt will be printed automatically.');

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }
            
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}
