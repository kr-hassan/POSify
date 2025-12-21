<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Customer;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Today's statistics
        $todaySales = Sale::whereDate('sale_date', today())->sum('total_amount');
        $todayPurchases = Purchase::whereDate('purchase_date', today())->sum('total_amount');
        $todayExpenses = Expense::whereDate('expense_date', today())->sum('amount');
        
        // This month's statistics
        $monthSales = Sale::whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year)
            ->sum('total_amount');
        $monthPurchases = Purchase::whereMonth('purchase_date', now()->month)
            ->whereYear('purchase_date', now()->year)
            ->sum('total_amount');
        $monthExpenses = Expense::whereMonth('expense_date', now()->month)
            ->whereYear('expense_date', now()->year)
            ->sum('amount');
        
        // Low stock products
        $lowStockProducts = Product::whereColumn('stock', '<=', 'alert_quantity')
            ->where('is_active', true)
            ->with('category')
            ->limit(10)
            ->get();
        
        // Recent sales
        $recentSales = Sale::with(['customer', 'user'])
            ->latest()
            ->limit(10)
            ->get() ?? collect([]);
        
        // Top selling products
        try {
            $topProducts = DB::table('sale_items')
                ->join('products', 'sale_items.product_id', '=', 'products.id')
                ->select('products.name', DB::raw('SUM(sale_items.quantity) as total_quantity'), DB::raw('SUM(sale_items.total) as total_revenue'))
                ->groupBy('products.id', 'products.name')
                ->orderBy('total_quantity', 'desc')
                ->limit(10)
                ->get();
        } catch (\Exception $e) {
            $topProducts = collect([]);
        }
        
        return view('dashboard', compact(
            'todaySales',
            'todayPurchases',
            'todayExpenses',
            'monthSales',
            'monthPurchases',
            'monthExpenses',
            'lowStockProducts',
            'recentSales',
            'topProducts'
        ));
    }
}

