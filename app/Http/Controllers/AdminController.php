<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Category;

class AdminController extends Controller
{
    public function dashboard()
    {
        $productCount = Product::count();
        $categoryCount = Category::count();
        
        $customerCount = \App\Models\User::count();
        $totalRevenue = \App\Models\Order::where('status', '!=', 'cancelled')->sum('total_amount');
        
        $products = Product::with('categories')->latest()->take(5)->get();
        $recentOrders = \App\Models\Order::with('user')->latest()->take(5)->get();
        
        return view('admin.dashboard', compact('productCount', 'categoryCount', 'customerCount', 'totalRevenue', 'products', 'recentOrders'));
    }
}
