<?php

namespace App\Http\Controllers;

use App\Models\Warranty;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WarrantyController extends Controller
{
    public function index(Request $request)
    {
        $query = Warranty::with(['sale', 'customer', 'product']);

        // Smart search - searches across multiple fields
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('warranty_no', 'like', "{$searchTerm}%")
                  ->orWhere('warranty_no', 'like', "%{$searchTerm}%")
                  ->orWhereHas('sale', function($sq) use ($searchTerm) {
                      $sq->where('invoice_no', 'like', "{$searchTerm}%")
                         ->orWhere('invoice_no', 'like', "%{$searchTerm}%");
                  })
                  ->orWhereHas('product', function($pq) use ($searchTerm) {
                      $pq->where('name', 'like', "%{$searchTerm}%")
                         ->orWhere('sku', 'like', "{$searchTerm}%")
                         ->orWhere('barcode', 'like', "{$searchTerm}%");
                  })
                  ->orWhereHas('customer', function($cq) use ($searchTerm) {
                      $cq->where('name', 'like', "%{$searchTerm}%")
                         ->orWhere('phone', 'like', "%{$searchTerm}%")
                         ->orWhere('email', 'like', "%{$searchTerm}%");
                  });
            });
        }

        // Individual field filters (for advanced search)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('from_date') && $request->filled('to_date')) {
            $query->whereBetween('start_date', [$request->from_date, $request->to_date]);
        } elseif ($request->filled('from_date')) {
            $query->where('start_date', '>=', $request->from_date);
        } elseif ($request->filled('to_date')) {
            $query->where('start_date', '<=', $request->to_date);
        }

        if ($request->filled('warranty_no')) {
            $query->where('warranty_no', 'like', "{$request->warranty_no}%");
        }

        if ($request->filled('invoice_no')) {
            $query->whereHas('sale', function($q) use ($request) {
                $q->where('invoice_no', 'like', "{$request->invoice_no}%");
            });
        }

        if ($request->filled('product_name')) {
            $query->whereHas('product', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->product_name}%");
            });
        }

        if ($request->filled('customer_name')) {
            $query->whereHas('customer', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->customer_name}%");
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Auto-update expired warranties (only run once per day to optimize)
        $lastUpdate = cache()->get('warranties_expired_update');
        if (!$lastUpdate || $lastUpdate < Carbon::today()) {
            Warranty::where('status', 'active')
                ->where('end_date', '<', Carbon::today())
                ->update(['status' => 'expired']);
            cache()->put('warranties_expired_update', Carbon::today(), now()->addDay());
        }

        // Use index-friendly ordering
        $warranties = $query->orderBy('id', 'desc')->paginate(50);

        return view('warranties.index', compact('warranties'));
    }

    public function create(Sale $sale)
    {
        $sale->load(['saleItems.product', 'customer']);
        return view('warranties.create', compact('sale'));
    }

    public function store(Request $request, Sale $sale)
    {
        $request->validate([
            'sale_item_id' => 'required|exists:sale_items,id',
            'warranty_period_months' => 'required|integer|min:1|max:120',
            'start_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $saleItem = SaleItem::findOrFail($request->sale_item_id);
            
            // Check if warranty already exists for this sale item
            if (Warranty::where('sale_item_id', $request->sale_item_id)->exists()) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Warranty already exists for this item.');
            }

            $startDate = Carbon::parse($request->start_date);
            $warrantyMonths = (int) $request->warranty_period_months;
            $endDate = $startDate->copy()->addMonths($warrantyMonths);
            
            // Calculate warranty period in days (actual difference)
            $warrantyDays = $startDate->diffInDays($endDate);

            // Generate warranty number
            $warrantyNo = 'WAR-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            $warranty = Warranty::create([
                'warranty_no' => $warrantyNo,
                'sale_id' => $sale->id,
                'sale_item_id' => $request->sale_item_id,
                'customer_id' => $sale->customer_id,
                'product_id' => $saleItem->product_id,
                'start_date' => $startDate,
                'end_date' => $endDate,
                'warranty_period_months' => $warrantyMonths,
                'warranty_period_days' => $warrantyDays,
                'status' => 'active',
                'notes' => $request->notes,
            ]);

            DB::commit();

            return redirect()->route('warranties.show', $warranty)
                ->with('success', 'Warranty created successfully. Warranty No: ' . $warrantyNo);

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating warranty: ' . $e->getMessage());
        }
    }

    public function show(Warranty $warranty)
    {
        $warranty->load(['sale', 'saleItem.product', 'customer', 'warrantyClaims.user']);
        return view('warranties.show', compact('warranty'));
    }

    public function updateStatus(Request $request, Warranty $warranty)
    {
        $request->validate([
            'status' => 'required|in:active,expired,void,claimed',
        ]);

        $warranty->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Warranty status updated successfully.');
    }
}
