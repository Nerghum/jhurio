<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductBundle;
use Illuminate\Http\Request;

class ProductBundleController extends Controller
{
    public function index()
    {
        $bundles = ProductBundle::with('products')->latest()->paginate(20);

        return view('admin.bundles.index', compact('bundles'));
    }

    public function create()
    {
        $products = Product::where('status', 'published')->get();

        return view('admin.bundles.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:fixed,percentage',
            'discount_value' => 'required|numeric|min:0',
            'products' => 'required|array|min:2',
            'products.*' => 'exists:products,id',
            'is_active' => 'boolean',
        ]);

        $bundle = ProductBundle::create($request->only([
            'title', 'description', 'discount_type', 'discount_value', 'is_active',
        ]));

        $bundle->products()->attach($request->products);

        return redirect()->route('admin.bundles.index')->with('success', 'Bundle created successfully.');
    }

    public function edit(ProductBundle $bundle)
    {
        $products = Product::where('status', 'published')->get();
        $bundle->load('products');

        return view('admin.bundles.edit', compact('bundle', 'products'));
    }

    public function update(Request $request, ProductBundle $bundle)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'discount_type' => 'required|in:fixed,percentage',
            'discount_value' => 'required|numeric|min:0',
            'products' => 'required|array|min:2',
            'products.*' => 'exists:products,id',
            'is_active' => 'boolean',
        ]);

        $bundle->update($request->only([
            'title', 'description', 'discount_type', 'discount_value', 'is_active',
        ]));

        $bundle->products()->sync($request->products);

        return redirect()->route('admin.bundles.index')->with('success', 'Bundle updated successfully.');
    }

    public function destroy(ProductBundle $bundle)
    {
        $bundle->delete();

        return redirect()->route('admin.bundles.index')->with('success', 'Bundle deleted successfully.');
    }
}
