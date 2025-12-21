<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\WarrantyController;
use App\Http\Controllers\WarrantyClaimController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile routes (from Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // POS
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/', [PosController::class, 'index'])->name('index');
        Route::get('/search', [PosController::class, 'searchProduct'])->name('search');
        Route::get('/product/{id}', [PosController::class, 'getProduct'])->name('product');
    });
    
    // Categories
    Route::resource('categories', CategoryController::class);
    
    // Products
    Route::resource('products', ProductController::class);
    
    // Customers
    Route::resource('customers', CustomerController::class);
    Route::post('customers/{customer}/add-payment', [CustomerController::class, 'addPayment'])->name('customers.add-payment');
    Route::post('customers/{customer}/recalculate-balance', [CustomerController::class, 'recalculateBalance'])->name('customers.recalculate-balance');
    
    // Payments
    Route::prefix('payments')->name('payments.')->group(function () {
        Route::get('/', [PaymentController::class, 'index'])->name('index');
        Route::get('/{payment}/receipt', [PaymentController::class, 'receipt'])->name('receipt');
    });
    
    // Suppliers
    Route::resource('suppliers', SupplierController::class);
    
    // Sales
    Route::prefix('sales')->name('sales.')->group(function () {
        Route::get('/', [SaleController::class, 'index'])->name('index');
        Route::post('/', [SaleController::class, 'store'])->name('store');
        Route::get('/{sale}', [SaleController::class, 'show'])->name('show');
        Route::get('/{sale}/invoice', [SaleController::class, 'invoice'])->name('invoice');
        Route::delete('/{sale}', [SaleController::class, 'destroy'])->name('destroy');
    });
    
    // Sale Returns
    Route::prefix('sale-returns')->name('sale-returns.')->group(function () {
        Route::get('/', [SaleReturnController::class, 'index'])->name('index');
        Route::get('/create/{sale}', [SaleReturnController::class, 'create'])->name('create');
        Route::post('/{sale}', [SaleReturnController::class, 'store'])->name('store');
        Route::get('/{saleReturn}', [SaleReturnController::class, 'show'])->name('show');
    });
    
    // Warranties
    Route::prefix('warranties')->name('warranties.')->group(function () {
        Route::get('/', [WarrantyController::class, 'index'])->name('index');
        Route::get('/create/{sale}', [WarrantyController::class, 'create'])->name('create');
        Route::post('/{sale}', [WarrantyController::class, 'store'])->name('store');
        Route::get('/{warranty}', [WarrantyController::class, 'show'])->name('show');
        Route::patch('/{warranty}/status', [WarrantyController::class, 'updateStatus'])->name('update-status');
    });
    
    // Warranty Claims
    Route::prefix('warranty-claims')->name('warranty-claims.')->group(function () {
        Route::get('/', [WarrantyClaimController::class, 'index'])->name('index');
        Route::get('/create/{warranty}', [WarrantyClaimController::class, 'create'])->name('create');
        Route::post('/{warranty}', [WarrantyClaimController::class, 'store'])->name('store');
        Route::get('/{warrantyClaim}', [WarrantyClaimController::class, 'show'])->name('show');
        Route::get('/{warrantyClaim}/receipt', [WarrantyClaimController::class, 'receipt'])->name('receipt');
        Route::patch('/{warrantyClaim}/status', [WarrantyClaimController::class, 'updateStatus'])->name('update-status');
    });
    
    // Purchases
    Route::resource('purchases', PurchaseController::class)->except(['edit', 'update']);
    
    // Expenses
    Route::resource('expenses', ExpenseController::class);
    Route::resource('expense-categories', ExpenseCategoryController::class)->except(['show']);
    
    // Reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
        Route::get('/products', [ReportController::class, 'products'])->name('products');
        Route::get('/stock', [ReportController::class, 'stock'])->name('stock');
        Route::get('/profit', [ReportController::class, 'profit'])->name('profit');
        Route::get('/customer-due', [ReportController::class, 'customerDue'])->name('customer-due');
    });
    
    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
