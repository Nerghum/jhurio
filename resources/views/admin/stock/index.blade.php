@extends('layouts.admin')

@section('title', 'Stock & Inventory Management')

@section('content')
<div class="space-y-6" x-data="stockManager()">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                <i data-lucide="boxes" class="w-7 h-7 text-primary"></i>
                Stock & Inventory Management
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Monitor stock quantities, evaluate inventory costs, and record new stock batches with purchase prices.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button @click="openAddStockModal()" type="button" class="inline-flex items-center justify-center px-4 py-2.5 bg-primary hover:bg-primary/90 text-white rounded-xl text-sm font-semibold shadow-sm transition-all hover:shadow hover:scale-[1.01] active:scale-[0.99] gap-2">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                <span>Add New Stock</span>
            </button>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600 dark:text-emerald-400 flex-shrink-0"></i>
                <span class="text-sm font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 dark:text-emerald-400">&times;</button>
        </div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300">
            <div class="flex items-center gap-2 mb-1.5 font-semibold text-sm">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                <span>Please fix the following errors:</span>
            </div>
            <ul class="list-disc list-inside text-xs space-y-0.5">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Top KPI Summary Cards -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <!-- Total Units -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/80 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Total Units</span>
                <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                    <i data-lucide="package" class="w-4 h-4"></i>
                </span>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($totalUnits) }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Across {{ $totalProducts }} products</p>
        </div>

        <!-- Inventory Valuation (Purchase Cost) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/80 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Stock Valuation (Cost)</span>
                <span class="p-2 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                    <i data-lucide="coins" class="w-4 h-4"></i>
                </span>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">৳{{ number_format($totalValuation, 2) }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">At purchase/cost price</p>
        </div>

        <!-- Potential Retail Value -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/80 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Retail Value</span>
                <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                    <i data-lucide="banknote" class="w-4 h-4"></i>
                </span>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">৳{{ number_format($potentialRetailValue, 2) }}</p>
            <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">
                Potential ৳{{ number_format(max(0, $potentialRetailValue - $totalValuation), 2) }} profit
            </p>
        </div>

        <!-- Low Stock Alert -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-amber-100 dark:border-amber-900/40 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-amber-700 dark:text-amber-400">Low Stock (≤ 5)</span>
                <span class="p-2 rounded-xl bg-amber-50 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400">
                    <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                </span>
            </div>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $lowStockCount }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Reorder soon</p>
        </div>

        <!-- Out of Stock -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-rose-100 dark:border-rose-900/40 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-rose-700 dark:text-rose-400">Out of Stock</span>
                <span class="p-2 rounded-xl bg-rose-50 dark:bg-rose-900/30 text-rose-600 dark:text-rose-400">
                    <i data-lucide="alert-octagon" class="w-4 h-4"></i>
                </span>
            </div>
            <p class="text-2xl font-bold text-rose-600 dark:text-rose-400">{{ $outOfStockCount }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Requires immediate restock</p>
        </div>
    </div>

    <!-- Navigation Tabs & Search Toolbar -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-sm overflow-hidden">
        <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-700/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <!-- Tabs -->
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.stock.index', array_merge(request()->except('tab'), ['tab' => 'inventory'])) }}"
                   class="px-4 py-2 rounded-xl text-sm font-semibold transition-all flex items-center gap-2 {{ $tab !== 'history' ? 'bg-primary text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <i data-lucide="layers" class="w-4 h-4"></i>
                    <span>Product Stock Inventory</span>
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $tab !== 'history' ? 'bg-white/20 text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300' }}">{{ $products->total() }}</span>
                </a>
                <a href="{{ route('admin.stock.index', array_merge(request()->except('tab'), ['tab' => 'history'])) }}"
                   class="px-4 py-2 rounded-xl text-sm font-semibold transition-all flex items-center gap-2 {{ $tab === 'history' ? 'bg-primary text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}">
                    <i data-lucide="history" class="w-4 h-4"></i>
                    <span>Stock Purchase Log</span>
                    <span class="px-2 py-0.5 rounded-full text-xs {{ $tab === 'history' ? 'bg-white/20 text-white' : 'bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300' }}">{{ $recentPurchases->total() }}</span>
                </a>
            </div>

            <!-- Filter Controls -->
            <form method="GET" action="{{ route('admin.stock.index') }}" class="flex flex-wrap items-center gap-2.5">
                <input type="hidden" name="tab" value="{{ $tab }}">

                <!-- Search -->
                <div class="relative min-w-[200px]">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input type="text" name="search" value="{{ $search }}" placeholder="Search product..."
                           class="w-full pl-9 pr-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                </div>

                @if($tab !== 'history')
                    <!-- Stock Status Filter -->
                    <select name="stock_status" onchange="this.form.submit()"
                            class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Stock</option>
                        <option value="in_stock" {{ $statusFilter === 'in_stock' ? 'selected' : '' }}>In Stock (> 5)</option>
                        <option value="low_stock" {{ $statusFilter === 'low_stock' ? 'selected' : '' }}>Low Stock (1-5)</option>
                        <option value="out_of_stock" {{ $statusFilter === 'out_of_stock' ? 'selected' : '' }}>Out of Stock (0)</option>
                    </select>

                    <!-- Category Filter -->
                    <select name="category_id" onchange="this.form.submit()"
                            class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ (string)$categoryId === (string)$cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    <!-- Sort -->
                    <select name="sort" onchange="this.form.submit()"
                            class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                        <option value="name_asc" {{ $sort === 'name_asc' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="stock_asc" {{ $sort === 'stock_asc' ? 'selected' : '' }}>Stock (Lowest first)</option>
                        <option value="stock_desc" {{ $sort === 'stock_desc' ? 'selected' : '' }}>Stock (Highest first)</option>
                        <option value="valuation_desc" {{ $sort === 'valuation_desc' ? 'selected' : '' }}>Valuation (Highest first)</option>
                    </select>
                @endif

                <button type="submit" class="px-3.5 py-2 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-xl text-sm font-medium transition-colors">
                    Filter
                </button>

                @if($search || $statusFilter !== 'all' || $categoryId || $sort !== 'name_asc')
                    <a href="{{ route('admin.stock.index', ['tab' => $tab]) }}" class="px-2.5 py-2 text-rose-600 hover:text-rose-700 text-sm font-medium">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        @if($tab !== 'history')
            <!-- ═════════ TAB 1: PRODUCT STOCK INVENTORY ═════════ -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-800/50 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <th class="py-3.5 px-4">Product</th>
                            <th class="py-3.5 px-4 text-center">Stock Level</th>
                            <th class="py-3.5 px-4 text-right">Purchase Price (Cost)</th>
                            <th class="py-3.5 px-4 text-right">Selling Price</th>
                            <th class="py-3.5 px-4 text-right">Margin / Unit</th>
                            <th class="py-3.5 px-4 text-right">Stock Valuation</th>
                            <th class="py-3.5 px-4 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-sm">
                        @forelse($products as $product)
                            @php
                                $stock = (int) $product->stock_quantity;
                                $cost = (float) ($product->purchase_price ?: 0);
                                $price = (float) $product->price;
                                $margin = $price - $cost;
                                $marginPct = $price > 0 ? ($margin / $price) * 100 : 0;
                                $valuation = $stock * $cost;
                            @endphp
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-700 overflow-hidden flex-shrink-0 flex items-center justify-center border border-gray-200 dark:border-gray-600">
                                            @if($product->thumbnail)
                                                <img src="{{ $product->thumbnail->url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                            @elseif($product->image_url)
                                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                            @else
                                                <i data-lucide="image" class="w-5 h-5 text-gray-400"></i>
                                            @endif
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.products.edit', $product->id) }}" class="font-semibold text-gray-900 dark:text-white hover:text-primary transition-colors line-clamp-1">
                                                {{ $product->name }}
                                            </a>
                                            <div class="flex items-center gap-2 mt-0.5 text-xs text-gray-500 dark:text-gray-400">
                                                <span>#ID: {{ $product->id }}</span>
                                                @if($product->categories->isNotEmpty())
                                                    <span>•</span>
                                                    <span class="text-primary/90">{{ $product->categories->first()->name }}</span>
                                                @endif
                                                @if($product->variations->count() > 0)
                                                    <span>•</span>
                                                    <span class="text-amber-600 dark:text-amber-400 font-medium">{{ $product->variations->count() }} Variations</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Stock Level -->
                                <td class="py-3.5 px-4 text-center">
                                    @if($stock <= 0)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900/40 gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            0 &nbsp;(Out of stock)
                                        </span>
                                    @elseif($stock <= 5)
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-900/40 gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            {{ $stock }} &nbsp;(Low stock)
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/40 gap-1">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            {{ $stock }} in stock
                                        </span>
                                    @endif
                                </td>

                                <!-- Purchase Price -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="font-semibold text-gray-900 dark:text-white">
                                        ৳{{ number_format($cost, 2) }}
                                    </div>
                                    @if($cost <= 0)
                                        <span class="text-[10px] text-amber-600 dark:text-amber-400 font-medium">Cost not set</span>
                                    @endif
                                </td>

                                <!-- Selling Price -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="font-semibold text-gray-900 dark:text-white">
                                        ৳{{ number_format($price, 2) }}
                                    </div>
                                    @if($product->compare_at_price > $price)
                                        <div class="text-xs text-gray-400 line-through">৳{{ number_format($product->compare_at_price, 2) }}</div>
                                    @endif
                                </td>

                                <!-- Profit Margin -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="font-semibold {{ $margin >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600' }}">
                                        ৳{{ number_format($margin, 2) }}
                                    </div>
                                    <div class="text-[11px] text-gray-500">
                                        {{ number_format($marginPct, 1) }}% margin
                                    </div>
                                </td>

                                <!-- Valuation -->
                                <td class="py-3.5 px-4 text-right">
                                    <div class="font-bold text-gray-900 dark:text-white">
                                        ৳{{ number_format($valuation, 2) }}
                                    </div>
                                    <div class="text-[11px] text-gray-400">
                                        {{ $stock }} × ৳{{ number_format($cost, 2) }}
                                    </div>
                                </td>

                                <!-- Actions -->
                                <td class="py-3.5 px-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Add Stock with purchase price -->
                                        <button type="button"
                                                @click="openAddStockModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $cost }}, {{ $price }})"
                                                title="Add New Stock Batch"
                                                class="px-2.5 py-1.5 rounded-lg bg-primary/10 hover:bg-primary/20 text-primary text-xs font-semibold flex items-center gap-1 transition-colors">
                                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                            <span>Add Stock</span>
                                        </button>

                                        <!-- Quick Edit Stock/Price -->
                                        <button type="button"
                                                @click="openQuickEditModal({{ $product->id }}, '{{ addslashes($product->name) }}', {{ $stock }}, {{ $cost }}, {{ $price }})"
                                                title="Quick Adjust Stock & Cost"
                                                class="p-1.5 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                                            <i data-lucide="edit-3" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-gray-500 dark:text-gray-400">
                                    <i data-lucide="package-search" class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-2"></i>
                                    <p class="font-medium">No products match your stock filter.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-700/60">
                {{ $products->links() }}
            </div>
        @else
            <!-- ═════════ TAB 2: STOCK PURCHASE HISTORY LOG ═════════ -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-800/50 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            <th class="py-3.5 px-4">Date & Time</th>
                            <th class="py-3.5 px-4">Product</th>
                            <th class="py-3.5 px-4 text-center">Type</th>
                            <th class="py-3.5 px-4 text-right">Qty Added</th>
                            <th class="py-3.5 px-4 text-right">Purchase Price</th>
                            <th class="py-3.5 px-4 text-right">Total Batch Cost</th>
                            <th class="py-3.5 px-4">Supplier / Reference</th>
                            <th class="py-3.5 px-4">Notes</th>
                            <th class="py-3.5 px-4">Recorded By</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-sm">
                        @forelse($recentPurchases as $purchase)
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/30 transition-colors">
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $purchase->created_at->format('d M Y') }}</div>
                                    <div class="text-xs text-gray-400">{{ $purchase->created_at->format('h:i A') }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($purchase->product)
                                        <div class="font-semibold text-gray-900 dark:text-white line-clamp-1">
                                            {{ $purchase->product->name }}
                                        </div>
                                        @if($purchase->variation)
                                            <div class="text-xs text-primary">{{ $purchase->variation->name }}</div>
                                        @endif
                                    @else
                                        <span class="text-gray-400">Deleted Product</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $purchase->type === 'purchase' ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}">
                                        {{ ucfirst($purchase->type) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold {{ $purchase->quantity > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600' }}">
                                    {{ $purchase->quantity > 0 ? '+' : '' }}{{ $purchase->quantity }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-semibold text-gray-900 dark:text-white">
                                    ৳{{ number_format($purchase->purchase_price, 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-right font-bold text-gray-900 dark:text-white">
                                    ৳{{ number_format($purchase->total_cost, 2) }}
                                </td>
                                <td class="py-3.5 px-4 text-gray-600 dark:text-gray-300 text-xs">
                                    {{ $purchase->supplier ?: '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-gray-500 text-xs max-w-xs truncate" title="{{ $purchase->notes }}">
                                    {{ $purchase->notes ?: '—' }}
                                </td>
                                <td class="py-3.5 px-4 text-xs text-gray-500">
                                    {{ $purchase->creator->name ?? 'Admin' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-gray-500 dark:text-gray-400">
                                    <i data-lucide="clipboard-list" class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-2"></i>
                                    <p class="font-medium">No stock purchase logs recorded yet.</p>
                                    <p class="text-xs text-gray-400 mt-1">Use the "Add New Stock" button above to record your first stock batch.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-700/60">
                {{ $recentPurchases->links() }}
            </div>
        @endif
    </div>

    <!-- ═════════ MODAL 1: ADD NEW STOCK BATCH ═════════ -->
    <div x-show="showAddModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showAddModal = false"
             x-show="showAddModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white dark:bg-gray-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-primary/10 text-primary">
                        <i data-lucide="package-plus" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Add New Stock Batch</h3>
                        <p class="text-xs text-gray-500">Record incoming stock with purchase price</p>
                    </div>
                </div>
                <button type="button" @click="showAddModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.stock.store') }}" class="space-y-4">
                @csrf

                <!-- Product Select -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Select Product <span class="text-rose-500">*</span>
                    </label>
                    <select name="product_id" x-model="form.productId" @change="onProductSelect()" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-primary focus:border-primary">
                        <option value="">-- Choose Product --</option>
                        @foreach($allProductsList as $p)
                            <option value="{{ $p->id }}" data-cost="{{ $p->purchase_price ?: 0 }}" data-price="{{ $p->price }}" data-stock="{{ $p->stock_quantity }}">
                                {{ $p->name }} (Stock: {{ $p->stock_quantity }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3.5">
                    <!-- Quantity Added -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            New Stock Qty <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" name="quantity" x-model.number="form.quantity" min="1" required placeholder="e.g. 50"
                               class="w-full px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-primary focus:border-primary">
                    </div>

                    <!-- Purchase Price Per Unit -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Purchase Cost / Unit (৳) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.01" name="purchase_price" x-model.number="form.purchasePrice" min="0" required placeholder="0.00"
                               class="w-full px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-primary focus:border-primary">
                    </div>
                </div>

                <!-- Total Cost Live Preview Card -->
                <div class="p-3.5 rounded-xl bg-purple-50/70 dark:bg-purple-950/30 border border-purple-100 dark:border-purple-900/40 flex items-center justify-between">
                    <div>
                        <span class="text-xs font-medium text-purple-700 dark:text-purple-300">Total Purchase Cost:</span>
                        <p class="text-xs text-purple-500 mt-0.5">Quantity × Purchase Price</p>
                    </div>
                    <div class="text-right">
                        <span class="text-lg font-bold text-purple-700 dark:text-purple-300" x-text="'৳' + (form.quantity * form.purchasePrice).toFixed(2)">৳0.00</span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3.5">
                    <!-- Optional: Update Selling Price -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Selling Price (৳) (opt)
                        </label>
                        <input type="number" step="0.01" name="selling_price" x-model.number="form.sellingPrice" min="0" placeholder="Keep current"
                               class="w-full px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-primary focus:border-primary">
                        <p class="text-[11px] text-gray-400 mt-1">Leave empty to keep current retail price.</p>
                    </div>

                    <!-- Supplier / Vendor -->
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Supplier / Source (opt)
                        </label>
                        <input type="text" name="supplier" x-model="form.supplier" placeholder="e.g. Factory Dhaka"
                               class="w-full px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-primary focus:border-primary">
                    </div>
                </div>

                <!-- Notes / Batch Reference -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Batch Notes / Reference (opt)
                    </label>
                    <textarea name="notes" x-model="form.notes" rows="2" placeholder="e.g. Invoice #PO-9021, Received in sound condition"
                              class="w-full px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-primary focus:border-primary"></textarea>
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" @click="showAddModal = false" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white text-sm font-semibold shadow-sm transition-all flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Save & Update Stock</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ═════════ MODAL 2: QUICK EDIT STOCK & PRICE ═════════ -->
    <div x-show="showEditModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.away="showEditModal = false"
             x-show="showEditModal"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700">
            <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100 dark:border-gray-700">
                <div class="flex items-center gap-2.5">
                    <div class="p-2 rounded-xl bg-amber-500/10 text-amber-600">
                        <i data-lucide="sliders" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Quick Adjust Stock</h3>
                        <p class="text-xs text-gray-500 truncate max-w-[220px]" x-text="editForm.productName"></p>
                    </div>
                </div>
                <button type="button" @click="showEditModal = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>

            <form method="POST" :action="'/admin/stock/' + editForm.productId + '/quick-update'" class="space-y-4">
                @csrf

                <!-- Current Stock Quantity -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Stock Quantity <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="stock_quantity" x-model.number="editForm.stock" min="0" required
                           class="w-full px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-primary focus:border-primary">
                </div>

                <!-- Purchase Price (Cost) -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Purchase Price / Unit (৳) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" step="0.01" name="purchase_price" x-model.number="editForm.purchasePrice" min="0" required
                           class="w-full px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-primary focus:border-primary">
                </div>

                <!-- Selling Price -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Selling Price (৳)
                    </label>
                    <input type="number" step="0.01" name="price" x-model.number="editForm.sellingPrice" min="0"
                           class="w-full px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-primary focus:border-primary">
                </div>

                <!-- Adjustment Note -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                        Adjustment Reason (opt)
                    </label>
                    <input type="text" name="note" x-model="editForm.note" placeholder="e.g. Physical inventory count correction"
                           class="w-full px-3.5 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white text-sm focus:ring-primary focus:border-primary">
                </div>

                <!-- Buttons -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100 dark:border-gray-700">
                    <button type="button" @click="showEditModal = false" class="px-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-primary hover:bg-primary/90 text-white text-sm font-semibold shadow-sm transition-all flex items-center gap-2">
                        <i data-lucide="check" class="w-4 h-4"></i>
                        <span>Update Stock</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function stockManager() {
    return {
        showAddModal: false,
        showEditModal: false,
        form: {
            productId: '',
            quantity: 10,
            purchasePrice: 0.00,
            sellingPrice: '',
            supplier: '',
            notes: ''
        },
        editForm: {
            productId: null,
            productName: '',
            stock: 0,
            purchasePrice: 0.00,
            sellingPrice: 0.00,
            note: ''
        },
        openAddStockModal(productId = null, productName = '', cost = 0, price = 0) {
            if (productId) {
                this.form.productId = productId;
                this.form.purchasePrice = cost;
                this.form.sellingPrice = price;
            } else {
                this.form.productId = '';
                this.form.purchasePrice = 0.00;
                this.form.sellingPrice = '';
            }
            this.form.quantity = 10;
            this.form.supplier = '';
            this.form.notes = '';
            this.showAddModal = true;
        },
        onProductSelect() {
            const select = document.querySelector('select[name="product_id"]');
            const selectedOpt = select.options[select.selectedIndex];
            if (selectedOpt && selectedOpt.dataset) {
                this.form.purchasePrice = parseFloat(selectedOpt.dataset.cost || 0);
                this.form.sellingPrice = parseFloat(selectedOpt.dataset.price || 0);
            }
        },
        openQuickEditModal(productId, productName, stock, cost, price) {
            this.editForm.productId = productId;
            this.editForm.productName = productName;
            this.editForm.stock = stock;
            this.editForm.purchasePrice = cost;
            this.editForm.sellingPrice = price;
            this.editForm.note = '';
            this.showEditModal = true;
        }
    };
}
</script>
@endsection
