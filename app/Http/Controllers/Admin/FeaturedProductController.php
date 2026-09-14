<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class FeaturedProductController extends Controller
{
    public function index()
    {
        $featuredProducts = Product::where('is_featured', true)
                                   ->orderBy('created_at', 'desc')
                                   ->get();
                                   
        return view('admin.featured_products.index', compact('featuredProducts'));
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        if (empty($query)) return response()->json([]);

        $products = Product::where('name', 'like', "%{$query}%")
                           ->orWhere('sku', 'like', "%{$query}%")
                           ->select('id', 'name', 'sku', 'price')
                           ->take(10)
                           ->get();
                           
        return response()->json($products);
    }

    public function add(Request $request)
    {
        $request->validate(['product_id' => 'required|exists:products,id']);
        
        $product = Product::findOrFail($request->product_id);
        $product->update(['is_featured' => true]);
        
        return back()->with('success', 'Product added to Hot Deals successfully.');
    }

    public function remove(Product $product)
    {
        $product->update(['is_featured' => false]);
        return back()->with('success', 'Product removed from Hot Deals.');
    }
}
