<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;

class HomeController extends Controller
{
    public function index()
    {
        $banners = \App\Models\Banner::with('media')
            ->where('is_active', true)
            ->orderBy('order', 'asc')
            ->get()
            ->groupBy('type');

        // Categories for sidebar
        $allCategories = \App\Models\Category::withCount('products')->orderBy('name')->get();

        // Featured Products / Hot Deals
        $featuredProducts = Product::where('status', 'published')
            ->where('is_featured', true)
            ->with('thumbnail', 'categories')
            ->latest()
            ->take(8)
            ->get();

        // Fetch active Homepage Builder Sections
        $builderSections = \App\Models\HomepageSection::where('is_active', true)->orderBy('order')->get();
        
        // Populate products for each section based on type and data
        foreach ($builderSections as $section) {
            $data = $section->data ?? [];
            $limit = $data['limit'] ?? 8;
            
            if ($section->type === 'category_grid' && !empty($data['category_id'])) {
                $section->products = Product::where('status', 'published')
                    ->whereHas('categories', function($q) use ($data) {
                        $q->where('categories.id', $data['category_id']);
                    })
                    ->with('thumbnail', 'categories')
                    ->latest()
                    ->take($limit)
                    ->get();
            } 
            elseif ($section->type === 'custom_products') {
                $section->products = Product::where('status', 'published')
                    ->with('thumbnail', 'categories')
                    ->latest()
                    ->take($limit)
                    ->get();
            }
            elseif ($section->type === 'flash_sale') {
                $section->products = Product::where('status', 'published')
                    ->whereNotNull('compare_at_price')
                    ->whereColumn('compare_at_price', '>', 'price')
                    ->with('thumbnail', 'categories')
                    ->orderByRaw('((compare_at_price - price) / compare_at_price) DESC')
                    ->take(8)
                    ->get();
            } else {
                $section->products = collect();
            }
        }

        return view('welcome', compact('banners', 'allCategories', 'featuredProducts', 'builderSections'));
    }
}
