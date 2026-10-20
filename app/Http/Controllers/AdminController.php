<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        $productCount = Product::count();
        $categoryCount = Category::count();

        $customerCount = User::count();
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total_amount');

        $products = Product::with('categories')->latest()->take(5)->get();
        $recentOrders = Order::with('user')->latest()->take(5)->get();
        $lowStockProducts = Product::where('stock_quantity', '<', 5)->orderBy('stock_quantity', 'asc')->take(5)->get();

        return view('admin.dashboard', compact('productCount', 'categoryCount', 'customerCount', 'totalRevenue', 'products', 'recentOrders', 'lowStockProducts'));
    }
}
