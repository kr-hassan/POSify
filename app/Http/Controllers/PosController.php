<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function index()
    {
        $customers = Customer::all();
        return view('pos.index', compact('customers'));
    }

    public function searchProduct(Request $request)
    {
        $search = $request->get('search');
        
        $products = Product::where('is_active', true)
            ->where(function($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('barcode', 'like', "%{$search}%");
            })
            ->with('category')
            ->limit(20)
            ->get();
        
        return response()->json($products);
    }

    public function getProduct($id)
    {
        $product = Product::with('category')->findOrFail($id);
        
        if (!$product->is_active) {
            return response()->json(['error' => 'Product is not active'], 400);
        }
        
        return response()->json($product);
    }
}




