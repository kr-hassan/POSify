<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Check if Spatie Permission is installed
        if (!class_exists(\Spatie\Permission\Models\Permission::class)) {
            $this->command->error('Spatie Permission package is not installed. Please run: composer require spatie/laravel-permission');
            return;
        }
        
        $Permission = \Spatie\Permission\Models\Permission::class;
        $Role = \Spatie\Permission\Models\Role::class;
        
        // Create permissions
        $permissions = [
            'view pos',
            'create sale',
            'view sale',
            'edit sale',
            'delete sale',
            'view product',
            'create product',
            'edit product',
            'delete product',
            'view category',
            'create category',
            'edit category',
            'delete category',
            'view customer',
            'create customer',
            'edit customer',
            'delete customer',
            'view supplier',
            'create supplier',
            'edit supplier',
            'delete supplier',
            'view purchase',
            'create purchase',
            'delete purchase',
            'view expense',
            'create expense',
            'edit expense',
            'delete expense',
            'view report',
            // Repair permissions
            'repair.create',
            'repair.process',
            'repair.complete',
            // Return permissions
            'return.create',
            'return.approve',
            'refund.process',
        ];
        
        foreach ($permissions as $permission) {
            $Permission::firstOrCreate(['name' => $permission]);
        }
        
        // Create roles
        $adminRole = $Role::firstOrCreate(['name' => 'Admin']);
        $managerRole = $Role::firstOrCreate(['name' => 'Manager']);
        $cashierRole = $Role::firstOrCreate(['name' => 'Cashier']);
        
        // Assign all permissions to admin
        $adminRole->givePermissionTo($Permission::all());
        
        // Assign permissions to manager
        $managerRole->givePermissionTo([
            'view pos', 'create sale', 'view sale', 'edit sale',
            'view product', 'create product', 'edit product', 'delete product',
            'view category', 'create category', 'edit category', 'delete category',
            'view customer', 'create customer', 'edit customer', 'delete customer',
            'view supplier', 'create supplier', 'edit supplier', 'delete supplier',
            'view purchase', 'create purchase', 'delete purchase',
            'view expense', 'create expense', 'edit expense', 'delete expense',
            'view report',
            // Repair permissions
            'repair.create', 'repair.process', 'repair.complete',
            // Return permissions
            'return.create', 'return.approve', 'refund.process',
        ]);
        
        // Assign permissions to cashier
        $cashierRole->givePermissionTo([
            'view pos', 'create sale', 'view sale',
            'view product', 'view category', 'view customer',
            // Cashiers can create repair claims and returns, but not process them
            'repair.create',
            'return.create',
        ]);
        
        // Create users (only if they don't exist)
        $admin = User::firstOrCreate(
            ['email' => 'admin@pos.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
            ]
        );
        if (!$admin->hasRole('Admin')) {
            $admin->assignRole('Admin');
        }
        
        $manager = User::firstOrCreate(
            ['email' => 'manager@pos.com'],
            [
                'name' => 'Manager User',
                'password' => Hash::make('password'),
            ]
        );
        if (!$manager->hasRole('Manager')) {
            $manager->assignRole('Manager');
        }
        
        $cashier = User::firstOrCreate(
            ['email' => 'cashier@pos.com'],
            [
                'name' => 'Cashier User',
                'password' => Hash::make('password'),
            ]
        );
        if (!$cashier->hasRole('Cashier')) {
            $cashier->assignRole('Cashier');
        }
        
        // Create categories
        $categories = [
            ['name' => 'Electronics'],
            ['name' => 'Clothing'],
            ['name' => 'Food & Beverages'],
            ['name' => 'Books'],
            ['name' => 'Home & Garden'],
        ];
        
        foreach ($categories as $category) {
            Category::firstOrCreate(['name' => $category['name']], $category);
        }
        
        // Create products
        $products = [
            ['name' => 'Laptop', 'sku' => 'LAP001', 'barcode' => '1234567890123', 'category_id' => 1, 'cost_price' => 800, 'sell_price' => 1200, 'stock' => 10, 'alert_quantity' => 2],
            ['name' => 'T-Shirt', 'sku' => 'TSH001', 'barcode' => '1234567890124', 'category_id' => 2, 'cost_price' => 10, 'sell_price' => 25, 'stock' => 50, 'alert_quantity' => 10],
            ['name' => 'Coffee', 'sku' => 'COF001', 'barcode' => '1234567890125', 'category_id' => 3, 'cost_price' => 5, 'sell_price' => 10, 'stock' => 100, 'alert_quantity' => 20],
            ['name' => 'Novel', 'sku' => 'BOK001', 'barcode' => '1234567890126', 'category_id' => 4, 'cost_price' => 8, 'sell_price' => 15, 'stock' => 30, 'alert_quantity' => 5],
        ];
        
        foreach ($products as $product) {
            Product::firstOrCreate(['sku' => $product['sku']], $product);
        }
        
        // Create customers
        $customers = [
            ['name' => 'John Doe', 'phone' => '1234567890', 'email' => 'john@example.com'],
            ['name' => 'Jane Smith', 'phone' => '0987654321', 'email' => 'jane@example.com'],
        ];
        
        foreach ($customers as $customer) {
            Customer::firstOrCreate(['email' => $customer['email']], $customer);
        }
        
        // Create suppliers
        $suppliers = [
            ['name' => 'ABC Suppliers', 'phone' => '1111111111'],
            ['name' => 'XYZ Distributors', 'phone' => '2222222222'],
        ];
        
        foreach ($suppliers as $supplier) {
            Supplier::firstOrCreate(['name' => $supplier['name']], $supplier);
        }
        
        // Create expense categories
        $expenseCategories = [
            ['name' => 'Rent'],
            ['name' => 'Utilities'],
            ['name' => 'Salaries'],
            ['name' => 'Marketing'],
        ];
        
        foreach ($expenseCategories as $category) {
            ExpenseCategory::firstOrCreate(['name' => $category['name']], $category);
        }
    }
}
