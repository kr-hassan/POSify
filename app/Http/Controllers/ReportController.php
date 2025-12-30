<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Purchase;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Expense;
use App\Models\User;
use App\Models\Payment;
use App\Models\ProductReturn;
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

    public function users(Request $request)
    {
        $fromDate = $request->from_date ?? now()->startOfMonth()->toDateString();
        $toDate = $request->to_date ?? now()->toDateString();
        $selectedUserId = $request->user_id;

        // Get all users
        $users = User::all();

        // Get user-wise statistics
        $userStats = [];
        
        foreach ($users as $user) {
            // Sales statistics
            $salesQuery = Sale::where('user_id', $user->id)
                ->whereBetween('sale_date', [$fromDate, $toDate]);
            
            $totalSales = $salesQuery->sum('total_amount');
            $salesCount = $salesQuery->count();
            $totalPaid = $salesQuery->sum('paid_amount');
            $totalDue = $salesQuery->sum('due_amount');
            $avgSale = $salesCount > 0 ? $totalSales / $salesCount : 0;

            // Payments collected
            $paymentsQuery = Payment::where('user_id', $user->id)
                ->whereBetween('payment_date', [$fromDate, $toDate]);
            
            $paymentsCollected = $paymentsQuery->sum('amount');
            $paymentsCount = $paymentsQuery->count();

            // Returns processed (ProductReturn uses created_by, not user_id)
            $returnsQuery = ProductReturn::where('created_by', $user->id)
                ->whereBetween('return_date', [$fromDate, $toDate]);
            
            $returnsProcessed = $returnsQuery->count();
            $returnsAmount = $returnsQuery->sum('total_refund');

            $userStats[] = [
                'user' => $user,
                'total_sales' => $totalSales,
                'sales_count' => $salesCount,
                'total_paid' => $totalPaid,
                'total_due' => $totalDue,
                'avg_sale' => $avgSale,
                'payments_collected' => $paymentsCollected,
                'payments_count' => $paymentsCount,
                'returns_processed' => $returnsProcessed,
                'returns_amount' => $returnsAmount,
            ];
        }

        // Sort by total sales descending
        usort($userStats, function($a, $b) {
            return $b['total_sales'] <=> $a['total_sales'];
        });

        // Calculate totals
        $grandTotalSales = collect($userStats)->sum('total_sales');
        $grandTotalPaid = collect($userStats)->sum('total_paid');
        $grandTotalDue = collect($userStats)->sum('total_due');
        $grandTotalPayments = collect($userStats)->sum('payments_collected');
        $grandTotalReturns = collect($userStats)->sum('returns_amount');
        $grandTotalSalesCount = collect($userStats)->sum('sales_count');

        // If specific user selected, get detailed sales
        $detailedSales = null;
        if ($selectedUserId) {
            $detailedSales = Sale::with(['customer', 'saleItems.product'])
                ->where('user_id', $selectedUserId)
                ->whereBetween('sale_date', [$fromDate, $toDate])
                ->latest()
                ->get();
        }

        return view('reports.users', compact(
            'userStats',
            'users',
            'fromDate',
            'toDate',
            'selectedUserId',
            'grandTotalSales',
            'grandTotalPaid',
            'grandTotalDue',
            'grandTotalPayments',
            'grandTotalReturns',
            'grandTotalSalesCount',
            'detailedSales'
        ));
    }
}






