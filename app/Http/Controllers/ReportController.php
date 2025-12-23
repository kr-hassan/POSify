<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function sales(Request $request)
    {
        $query = Sale::with(['user', 'customer', 'saleItems.product']);
        
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();
        
        $query->whereBetween('sale_date', [$fromDate, $toDate]);
        
        $sales = $query->get();
        $totalSales = $sales->sum('total_amount');
        $totalPaid = $sales->sum('paid_amount');
        $totalDue = $sales->sum('due_amount');
        
        if ($request->has('export')) {
            if ($request->export === 'pdf') {
                $pdf = Pdf::loadView('reports.sales-pdf', compact('sales', 'fromDate', 'toDate', 'totalSales', 'totalPaid', 'totalDue'));
                return $pdf->download('sales-report-' . $fromDate . '-to-' . $toDate . '.pdf');
            } elseif ($request->export === 'excel') {
                // Excel export logic here
                return redirect()->back()->with('info', 'Excel export feature coming soon.');
            }
        }
        
        return view('reports.sales', compact('sales', 'fromDate', 'toDate', 'totalSales', 'totalPaid', 'totalDue'));
    }

    public function products(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();
        
        $products = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [$fromDate, $toDate])
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                DB::raw('SUM(sale_items.quantity) as total_quantity'),
                DB::raw('SUM(sale_items.total) as total_revenue'),
                DB::raw('AVG(sale_items.price) as avg_price')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderBy('total_quantity', 'desc')
            ->get();
        
        return view('reports.products', compact('products', 'fromDate', 'toDate'));
    }

    public function stock()
    {
        $products = Product::with('category')
            ->orderBy('stock', 'asc')
            ->get();
        
        $lowStock = $products->filter(fn($p) => $p->isLowStock());
        
        return view('reports.stock', compact('products', 'lowStock'));
    }

    public function profit(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();
        
        $sales = DB::table('sale_items')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->whereBetween('sales.sale_date', [$fromDate, $toDate])
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(sale_items.quantity) as quantity_sold'),
                DB::raw('SUM(sale_items.total) as revenue'),
                DB::raw('SUM(sale_items.quantity * products.cost_price) as cost'),
                DB::raw('SUM(sale_items.total) - SUM(sale_items.quantity * products.cost_price) as profit')
            )
            ->groupBy('products.id', 'products.name')
            ->get();
        
        $totalRevenue = $sales->sum('revenue');
        $totalCost = $sales->sum('cost');
        $totalProfit = $sales->sum('profit');
        
        return view('reports.profit', compact('sales', 'fromDate', 'toDate', 'totalRevenue', 'totalCost', 'totalProfit'));
    }

    public function customerDue()
    {
        $customers = Customer::where('balance', '>', 0)
            ->orderBy('balance', 'desc')
            ->get();
        
        $totalDue = $customers->sum('balance');
        
        return view('reports.customer-due', compact('customers', 'totalDue'));
    }
}




