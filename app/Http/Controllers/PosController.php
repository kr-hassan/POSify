<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index()
    {
        // Only load active customers with minimal data for dropdown
        $customers = Customer::select('id', 'name', 'phone')
            ->orderBy('name')
            ->get();
        return view('pos.index', compact('customers'));
    }

    public function searchProduct(Request $request)
    {
        $search = trim($request->get('search', ''));
        $categoryId = $request->get('category_id');
        $inStockOnly = $request->get('in_stock_only', false);
        $minPrice = $request->get('min_price');
        $maxPrice = $request->get('max_price');
        $sortBy = $request->get('sort_by', 'name'); // name, price_asc, price_desc, stock
        
        if (empty($search) && !$categoryId) {
            return response()->json([]);
        }
        
        $query = Product::select('id', 'name', 'sku', 'barcode', 'sell_price', 'stock', 'tax_percent', 'category_id', 'is_active')
            ->where('is_active', true);
        
        // Search in name, SKU, or barcode
        if (!empty($search)) {
            // If search looks like exact barcode/SKU match, prioritize exact match
            if (strlen($search) >= 3) {
                $query->where(function($q) use ($search) {
                    $q->where('barcode', '=', $search) // Exact barcode match first
                      ->orWhere('sku', '=', $search) // Exact SKU match
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%");
                });
            } else {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%");
                });
            }
        }
        
        // Category filter
        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }
        
        // Stock filter
        if ($inStockOnly) {
            $query->where('stock', '>', 0);
        }
        
        // Price range filter
        if ($minPrice !== null) {
            $query->where('sell_price', '>=', $minPrice);
        }
        if ($maxPrice !== null) {
            $query->where('sell_price', '<=', $maxPrice);
        }
        
        // Sorting
        switch ($sortBy) {
            case 'price_asc':
                $query->orderBy('sell_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('sell_price', 'desc');
                break;
            case 'stock':
                $query->orderBy('stock', 'desc');
                break;
            default:
                $query->orderBy('name', 'asc');
        }
        
        $products = $query->with('category:id,name')
            ->limit(50) // Increased limit
            ->get();
        
        return response()->json($products);
    }
    
    public function getCategories()
    {
        $categories = \App\Models\Category::select('id', 'name')
            ->has('products')
            ->orderBy('name')
            ->get();
        return response()->json($categories);
    }
    
    public function quickSearch(Request $request)
    {
        $search = trim($request->get('q', ''));
        
        if (strlen($search) < 2) {
            return response()->json([]);
        }
        
        // Quick autocomplete search - returns top 10 matches
        $products = Product::select('id', 'name', 'sku', 'barcode', 'sell_price', 'stock')
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%");
            })
            ->orderByRaw("CASE 
                WHEN barcode = ? THEN 1 
                WHEN sku = ? THEN 2 
                WHEN name LIKE ? THEN 3 
                ELSE 4 END", [$search, $search, $search . '%'])
            ->limit(10)
            ->get();
        
        return response()->json($products);
    }

    public function getProduct($id)
    {
        $product = Product::with(['category', 'batches'])->findOrFail($id);
        
        if (!$product->is_active) {
            return response()->json(['error' => 'Product is not active'], 400);
        }
        
        // Add medical information (batches with expiry dates)
        $product->load('available_batches');
        
        return response()->json($product);
    }
}




