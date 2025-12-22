<?php

namespace App\Http\Controllers;

use App\Models\Warranty;
use App\Models\WarrantyClaim;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WarrantyClaimController extends Controller
{
    public function create(Warranty $warranty)
    {
        $warranty->load(['sale', 'product', 'customer']);
        
        if (!$warranty->is_active) {
            return redirect()->route('warranties.show', $warranty)
                ->with('error', 'Cannot create claim for expired or void warranty.');
        }

        return view('warranty-claims.create', compact('warranty'));
    }

    public function store(Request $request, Warranty $warranty)
    {
        $request->validate([
            'claim_type' => 'required|in:repair,replacement,refund',
            'issue_description' => 'required|string|min:10',
            'claim_date' => 'required|date',
        ]);

        if (!$warranty->is_active) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Cannot create claim for expired or void warranty.');
        }

        DB::beginTransaction();

        try {
            // Generate claim number
            $claimNo = 'CLM-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            $claim = WarrantyClaim::create([
                'claim_no' => $claimNo,
                'warranty_id' => $warranty->id,
                'user_id' => auth()->id(),
                'claim_type' => $request->claim_type,
                'issue_description' => $request->issue_description,
                'status' => 'pending',
                'claim_date' => $request->claim_date,
            ]);

            // Update warranty status to claimed if first claim
            if ($warranty->warrantyClaims()->count() == 1) {
                $warranty->update(['status' => 'claimed']);
            }

            DB::commit();

            // Return JSON response for AJAX or redirect for form submission
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Warranty claim created successfully',
                    'claim_id' => $claim->id,
                    'receipt_url' => route('warranty-claims.receipt', $claim),
                ]);
            }

            // Redirect to receipt page which will auto-print
            return redirect()->route('warranty-claims.receipt', $claim)
                ->with('success', 'Warranty claim created successfully. Receipt will be printed automatically.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 400);
            }
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating claim: ' . $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $query = WarrantyClaim::with(['warranty.product', 'warranty.customer', 'user']);

        // Filter by claim type
        if ($request->has('claim_type') && $request->claim_type !== '') {
            // If specific type is selected, show only that type
            $query->where('claim_type', $request->claim_type);
        } else {
            // By default, exclude refund (return) type claims unless explicitly requested
            if (!$request->has('include_returns') || $request->include_returns != '1') {
                $query->where('claim_type', '!=', 'refund');
            }
        }

        // Filter by returned status
        if ($request->has('show_returned') && $request->show_returned == '1') {
            // Show only returned products
            $query->whereNotNull('returned_date');
        } else {
            // By default, exclude returned products (show only active/not returned)
            $query->whereNull('returned_date');
        }

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

        return view('warranty-claims.index', compact('claims'));
    }

    public function show(WarrantyClaim $warrantyClaim)
    {
        $warrantyClaim->load(['warranty.sale', 'warranty.product', 'warranty.customer', 'user']);
        return view('warranty-claims.show', compact('warrantyClaim'));
    }

    public function receipt(WarrantyClaim $warrantyClaim)
    {
        $warrantyClaim->load(['warranty.sale', 'warranty.product', 'warranty.customer', 'user']);
        return view('warranty-claims.receipt', compact('warrantyClaim'));
    }

    public function updateStatus(Request $request, WarrantyClaim $warrantyClaim)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected,in_progress,completed',
            'resolution_notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $warrantyClaim->update([
                'status' => $request->status,
                'resolution_notes' => $request->resolution_notes,
                'resolved_date' => $request->status === 'completed' ? Carbon::today() : null,
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Claim status updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating claim: ' . $e->getMessage());
        }
    }
}

