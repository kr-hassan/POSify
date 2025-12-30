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
use App\Http\Controllers\SupplierReturnController;
use App\Http\Controllers\SaleReturnController;
use App\Http\Controllers\WarrantyController;
use App\Http\Controllers\WarrantyClaimController;
use App\Http\Controllers\RepairController;
use App\Http\Controllers\ReturnController;
use App\Http\Controllers\UserController;
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
        Route::get('/quick-search', [PosController::class, 'quickSearch'])->name('quick-search');
        Route::get('/categories', [PosController::class, 'getCategories'])->name('categories');
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
    
    // Repairs (Warranty-based repair workflow)
    Route::prefix('repairs')->name('repairs.')->group(function () {
        Route::get('/', [RepairController::class, 'index'])->name('index');
        Route::get('/create-from-sale/{sale}', [RepairController::class, 'createFromSale'])->name('create-from-sale');
        Route::post('/create-from-sale/{sale}', [RepairController::class, 'storeFromSale'])->name('store-from-sale');
        Route::get('/{repair}', [RepairController::class, 'show'])->name('show');
        Route::get('/{repair}/return-receipt', [RepairController::class, 'returnReceipt'])->name('return-receipt');
        Route::post('/{repair}/mark-received', [RepairController::class, 'markAsReceived'])->name('mark-received');
        Route::post('/{repair}/mark-completed', [RepairController::class, 'markAsCompleted'])->name('mark-completed');
        Route::post('/{repair}/mark-returned', [RepairController::class, 'markAsReturned'])->name('mark-returned');
    });
    
    // Returns (Product return workflow - refund/exchange)
    Route::prefix('returns')->name('returns.')->group(function () {
        Route::get('/', [ReturnController::class, 'index'])->name('index');
        Route::get('/create-from-sale/{sale}', [ReturnController::class, 'createFromSale'])->name('create-from-sale');
        Route::post('/create-from-sale/{sale}', [ReturnController::class, 'storeFromSale'])->name('store-from-sale');
        Route::get('/{productReturn}', [ReturnController::class, 'show'])->name('show');
        Route::post('/{productReturn}/approve', [ReturnController::class, 'approve'])->name('approve');
        Route::post('/{productReturn}/process', [ReturnController::class, 'process'])->name('process');
        Route::post('/{productReturn}/reject', [ReturnController::class, 'reject'])->name('reject');
    });
    
    // Purchases
    Route::resource('purchases', PurchaseController::class)->except(['edit', 'update']);
    
    // Supplier Returns
    Route::prefix('supplier-returns')->name('supplier-returns.')->group(function () {
        Route::get('/', [SupplierReturnController::class, 'index'])->name('index');
        Route::get('/create', [SupplierReturnController::class, 'create'])->name('create');
        Route::post('/', [SupplierReturnController::class, 'store'])->name('store');
        Route::get('/{supplierReturn}', [SupplierReturnController::class, 'show'])->name('show');
        Route::put('/{supplierReturn}/update-status', [SupplierReturnController::class, 'updateStatus'])->name('update-status');
        Route::post('/{supplierReturn}/process-refund', [SupplierReturnController::class, 'processRefund'])->name('process-refund');
        Route::post('/{supplierReturn}/process-replacement', [SupplierReturnController::class, 'processReplacement'])->name('process-replacement');
        Route::delete('/{supplierReturn}', [SupplierReturnController::class, 'destroy'])->name('destroy');
    });
    
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
        Route::get('/users', [ReportController::class, 'users'])->name('users');
    });
    
    // Settings
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
    
    // Users Management
    Route::resource('users', UserController::class);
    Route::put('/users/{user}/roles', [UserController::class, 'updateRoles'])->name('users.update-roles');
});

require __DIR__.'/auth.php';
