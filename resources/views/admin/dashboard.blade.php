@extends('layouts.admin')

@section('header', 'Overview')

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Revenue</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($totalRevenue ?? 0, 0) }} BDT</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center text-green-600 dark:text-green-400">
                <i data-lucide="dollar-sign" class="w-6 h-6"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-green-500 flex items-center font-medium">
                <i data-lucide="arrow-up-right" class="w-4 h-4 mr-1"></i>
                Lifetime
            </span>
            <span class="text-gray-500 dark:text-gray-400 ml-2">sales</span>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Products</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $productCount ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-secondary/10 dark:bg-secondary/20 flex items-center justify-center text-secondary dark:text-secondary">
                <i data-lucide="package" class="w-6 h-6"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-secondary flex items-center font-medium">
                <i data-lucide="arrow-up-right" class="w-4 h-4 mr-1"></i>
                Active
            </span>
            <span class="text-gray-500 dark:text-gray-400 ml-2">in catalog</span>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Categories</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $categoryCount ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center text-purple-600 dark:text-purple-400">
                <i data-lucide="layers" class="w-6 h-6"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-purple-500 flex items-center font-medium">
                <i data-lucide="arrow-up-right" class="w-4 h-4 mr-1"></i>
                Organized
            </span>
            <span class="text-gray-500 dark:text-gray-400 ml-2">shop structure</span>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Customers</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $customerCount ?? 0 }}</p>
            </div>
            <div class="w-12 h-12 rounded-xl bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-orange-600 dark:text-orange-400">
                <i data-lucide="users" class="w-6 h-6"></i>
            </div>
        </div>
        <div class="mt-4 flex items-center text-sm">
            <span class="text-orange-500 flex items-center font-medium">
                <i data-lucide="arrow-up-right" class="w-4 h-4 mr-1"></i>
                Registered
            </span>
            <span class="text-gray-500 dark:text-gray-400 ml-2">on platform</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Recent Orders Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/50">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i data-lucide="shopping-bag" class="w-5 h-5 text-primary"></i> Recent Orders
            </h3>
            <a href="{{ route('admin.orders.index') }}" class="text-sm font-medium text-primary hover:text-secondary dark:hover:text-secondary transition-colors">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 font-medium">
                    <tr>
                        <th class="px-6 py-4">Order</th>
                        <th class="px-6 py-4">Customer</th>
                        <th class="px-6 py-4">Amount</th>
                        <th class="px-6 py-4 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($recentOrders ?? [] as $order)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                            #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-gray-900 dark:text-white font-medium">{{ $order->customer_name ?? ($order->user->name ?? 'Guest') }}</div>
                            <div class="text-xs text-gray-500">{{ $order->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                            {{ number_format($order->total_amount, 0) }} BDT
                        </td>
                        <td class="px-6 py-4 text-right">
                            @php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                    'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                    'shipped' => 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-400',
                                    'delivered' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                    'cancelled' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
                                ];
                                $colorClass = $statusColors[$order->status] ?? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-semibold {{ $colorClass }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                            No orders found yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Products Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-gray-50/50 dark:bg-gray-900/50">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <i data-lucide="package" class="w-5 h-5 text-primary"></i> Recent Products
            </h3>
            <a href="{{ route('admin.products.index') }}" class="text-sm font-medium text-primary hover:text-secondary dark:hover:text-secondary transition-colors">View All</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 font-medium">
                    <tr>
                        <th class="px-6 py-4">Product</th>
                        <th class="px-6 py-4">Price</th>
                        <th class="px-6 py-4 text-right">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($products ?? [] as $product)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4 flex items-center gap-3">
                            <div class="h-10 w-10 flex-shrink-0 rounded-lg bg-gray-100 dark:bg-gray-900 overflow-hidden border border-gray-200 dark:border-gray-700">
                                <img src="{{ $product->thumbnail ? $product->thumbnail->url : 'https://via.placeholder.com/150' }}" alt="" class="h-full w-full object-cover">
                            </div>
                            <div>
                                <div class="font-medium text-gray-900 dark:text-white truncate max-w-[150px]">{{ $product->name }}</div>
                                <div class="text-xs text-gray-500 truncate max-w-[150px]">{{ $product->categories->pluck('name')->implode(', ') ?: 'Uncategorized' }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                            {{ number_format($product->price, 0) }} BDT
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-semibold bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                Active
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                            No products found. Start by adding a new product.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Low Stock Alerts -->
<div class="mt-6 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-700 flex justify-between items-center bg-red-50/50 dark:bg-red-900/10">
        <h3 class="text-lg font-bold text-red-600 dark:text-red-400 flex items-center gap-2">
            <i data-lucide="alert-triangle" class="w-5 h-5"></i> Low Stock Alerts
        </h3>
        <a href="{{ route('admin.products.index') }}" class="text-sm font-medium text-red-600 hover:text-red-700 dark:hover:text-red-300 transition-colors">View Inventory</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 font-medium">
                <tr>
                    <th class="px-6 py-4">Product</th>
                    <th class="px-6 py-4">Price</th>
                    <th class="px-6 py-4 text-right">Stock</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($lowStockProducts ?? [] as $product)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-6 py-4 flex items-center gap-3">
                        <div class="h-10 w-10 flex-shrink-0 rounded-lg bg-gray-100 dark:bg-gray-900 overflow-hidden border border-gray-200 dark:border-gray-700">
                            <img src="{{ $product->thumbnail ? $product->thumbnail->url : 'https://via.placeholder.com/150' }}" alt="" class="h-full w-full object-cover">
                        </div>
                        <div>
                            <div class="font-medium text-gray-900 dark:text-white truncate max-w-[200px]">{{ $product->name }}</div>
                            <div class="text-xs text-gray-500 truncate max-w-[200px]">{{ $product->categories->pluck('name')->implode(', ') ?: 'Uncategorized' }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                        {{ number_format($product->price, 0) }} BDT
                    </td>
                    <td class="px-6 py-4 text-right">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-semibold bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">
                            {{ $product->stock }} Left
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                        All products have healthy stock levels.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
