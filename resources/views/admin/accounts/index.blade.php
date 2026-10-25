@extends('layouts.admin')

@section('title', 'Accounts & Sales Overview')

@section('content')
<div class="space-y-6" x-data="accountsManager()">
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2.5">
                <i data-lucide="wallet" class="w-7 h-7 text-primary"></i>
                Accounts & Sales Overview
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                Comprehensive financial report of store revenue, cost of goods sold (COGS), profit margins, and sales breakdown.
            </p>
        </div>
        <div class="flex items-center gap-3">
            <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 text-sm font-semibold hover:bg-gray-50 dark:hover:bg-gray-700/60 shadow-sm transition-all">
                <i data-lucide="printer" class="w-4 h-4"></i>
                <span>Print Report</span>
            </button>
            <a href="{{ route('admin.accounts.export', request()->all()) }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold shadow-sm transition-all hover:shadow hover:scale-[1.01] active:scale-[0.99]">
                <i data-lucide="download" class="w-4 h-4"></i>
                <span>Export CSV</span>
            </a>
        </div>
    </div>

    <!-- Filter Panel -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-sm p-5">
        <form method="GET" action="{{ route('admin.accounts.index') }}" class="space-y-4">
            <!-- Date Presets -->
            <div class="flex flex-wrap items-center gap-2 border-b border-gray-100 dark:border-gray-700/60 pb-4">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider mr-1">Period:</span>
                @php
                    $presets = [
                        'today' => 'Today',
                        'yesterday' => 'Yesterday',
                        'this_week' => 'This Week',
                        'last_7_days' => 'Last 7 Days',
                        'this_month' => 'This Month',
                        'last_month' => 'Last Month',
                        'this_year' => 'This Year',
                        'custom' => 'Custom Range',
                    ];
                @endphp
                @foreach($presets as $key => $label)
                    <button type="button"
                            @click="setRange('{{ $key }}')"
                            class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all {{ $range === $key ? 'bg-primary text-white shadow-sm' : 'bg-gray-100 dark:bg-gray-700/70 text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
                        {{ $label }}
                    </button>
                @endforeach
                <input type="hidden" name="range" x-model="selectedRange">
            </div>

            <!-- Custom Filter Controls Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5 pt-1">
                <!-- Start Date -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1">From Date</label>
                    <input type="date" name="start_date" x-model="startDate"
                           class="w-full px-3 py-2 rounded-xl text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                </div>

                <!-- End Date -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1">To Date</label>
                    <input type="date" name="end_date" x-model="endDate"
                           class="w-full px-3 py-2 rounded-xl text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                </div>

                <!-- Product Filter (Multi / Custom Select) -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1">Filter by Product</label>
                    <select name="product_ids[]" multiple
                            class="w-full px-3 py-1.5 rounded-xl text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary custom-scrollbar"
                            style="height: 42px;">
                        @foreach($allProducts as $p)
                            <option value="{{ $p->id }}" {{ in_array($p->id, $productIds ?? []) ? 'selected' : '' }}>
                                {{ $p->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[10px] text-gray-400 mt-0.5">Hold Ctrl/Cmd to select multiple products</p>
                </div>

                <!-- Order Status Filter -->
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-gray-600 dark:text-gray-300 mb-1">Order Status</label>
                    <select name="status"
                            class="w-full px-3 py-2 rounded-xl text-sm border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                        <option value="valid" {{ $status === 'valid' ? 'selected' : '' }}>Valid Sales (Non-Cancelled)</option>
                        <option value="delivered" {{ $status === 'delivered' ? 'selected' : '' }}>Delivered Only</option>
                        <option value="shipped" {{ $status === 'shipped' ? 'selected' : '' }}>Shipped</option>
                        <option value="processing" {{ $status === 'processing' ? 'selected' : '' }}>Processing</option>
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses (inc. Cancelled)</option>
                    </select>
                </div>

                <!-- Search / Buttons -->
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 px-4 py-2.5 bg-primary hover:bg-primary/90 text-white text-sm font-semibold rounded-xl shadow-sm transition-all flex items-center justify-center gap-2">
                        <i data-lucide="filter" class="w-4 h-4"></i>
                        <span>Apply Filters</span>
                    </button>
                    @if(request()->hasAny(['range', 'start_date', 'end_date', 'product_ids', 'status', 'search']))
                        <a href="{{ route('admin.accounts.index') }}" class="px-3 py-2.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </div>

            <!-- Active Selected Products Pills -->
            @if(!empty($productIds))
                <div class="pt-2 flex flex-wrap items-center gap-2">
                    <span class="text-xs text-gray-500 font-medium">Filtering {{ count($productIds) }} selected product(s):</span>
                    @foreach($allProducts->whereIn('id', $productIds) as $selProd)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-primary/10 text-primary border border-primary/20">
                            <span>{{ $selProd->name }}</span>
                        </span>
                    @endforeach
                    <a href="{{ route('admin.accounts.index', request()->except('product_ids')) }}" class="text-xs text-rose-500 hover:underline ml-1">
                        Clear product filter (Show overall store)
                    </a>
                </div>
            @endif
        </form>
    </div>

    <!-- ═════════ PRIMARY FINANCIAL KPI CARDS ═════════ -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <!-- Gross Revenue -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-emerald-100 dark:border-emerald-900/30 shadow-sm relative overflow-hidden">
            <div class="absolute -right-3 -bottom-3 w-20 h-20 bg-emerald-500/5 rounded-full pointer-events-none"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-emerald-700 dark:text-emerald-400">Total Revenue</span>
                <span class="p-2 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400">
                    <i data-lucide="trending-up" class="w-4 h-4"></i>
                </span>
            </div>
            <p class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white">
                ৳{{ number_format($grossRevenue, 2) }}
            </p>
            <div class="flex items-center gap-2 mt-2 text-xs text-gray-500 dark:text-gray-400">
                <span>{{ $totalOrdersCount }} orders</span>
                <span>•</span>
                <span>{{ $totalItemsCount }} items sold</span>
            </div>
        </div>

        <!-- Cost of Goods Sold (COGS) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-purple-100 dark:border-purple-900/30 shadow-sm relative overflow-hidden">
            <div class="absolute -right-3 -bottom-3 w-20 h-20 bg-purple-500/5 rounded-full pointer-events-none"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-purple-700 dark:text-purple-400">Cost of Goods (COGS)</span>
                <span class="p-2 rounded-xl bg-purple-50 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400">
                    <i data-lucide="receipt" class="w-4 h-4"></i>
                </span>
            </div>
            <p class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white">
                ৳{{ number_format($totalCogs, 2) }}
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                Total acquisition / purchase cost
            </p>
        </div>

        <!-- Net Profit -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-blue-100 dark:border-blue-900/30 shadow-sm relative overflow-hidden">
            <div class="absolute -right-3 -bottom-3 w-20 h-20 bg-blue-500/5 rounded-full pointer-events-none"></div>
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-blue-700 dark:text-blue-400">Gross Profit</span>
                <span class="p-2 rounded-xl bg-blue-50 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                    <i data-lucide="badge-dollar-sign" class="w-4 h-4"></i>
                </span>
            </div>
            <p class="text-2xl sm:text-3xl font-extrabold {{ $grossProfit >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-rose-600' }}">
                ৳{{ number_format($grossProfit, 2) }}
            </p>
            <div class="flex items-center gap-1.5 mt-2 text-xs font-bold {{ $profitMarginPct >= 20 ? 'text-emerald-600' : ($profitMarginPct >= 0 ? 'text-blue-600' : 'text-rose-600') }}">
                <i data-lucide="percent" class="w-3.5 h-3.5"></i>
                <span>{{ number_format($profitMarginPct, 1) }}% Profit Margin</span>
            </div>
        </div>

        <!-- Average Order Value (AOV) -->
        <div class="bg-white dark:bg-gray-800 rounded-2xl p-5 border border-gray-100 dark:border-gray-700/80 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Average Order Value</span>
                <span class="p-2 rounded-xl bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                </span>
            </div>
            <p class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white">
                ৳{{ number_format($averageOrderValue, 2) }}
            </p>
            <div class="flex items-center gap-2 mt-2 text-xs text-gray-500">
                <span>Shipping: ৳{{ number_format($deliveryTotal, 2) }}</span>
                @if($discountTotal > 0)
                    <span>• Disc: ৳{{ number_format($discountTotal, 2) }}</span>
                @endif
            </div>
        </div>
    </div>

    <!-- ═════════ SALES & PROFIT TREND VISUALIZER ═════════ -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-sm p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="bar-chart-3" class="w-5 h-5 text-primary"></i>
                    <span>Sales & Profit Trend ({{ $startDate->format('d M') }} — {{ $endDate->format('d M Y') }})</span>
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Day-by-day comparison of Gross Revenue vs Net Profit</p>
            </div>
            <div class="flex items-center gap-4 text-xs font-medium">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-md bg-emerald-500"></span>
                    <span class="text-gray-700 dark:text-gray-300">Revenue</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-md bg-blue-500"></span>
                    <span class="text-gray-700 dark:text-gray-300">Net Profit</span>
                </span>
            </div>
        </div>

        @php
            $chartLabels = $chartData['labels'] ?? [];
            $chartRevenues = $chartData['revenues'] ?? [];
            $chartProfits = $chartData['profits'] ?? [];
            $maxVal = max(1, max(count($chartRevenues) ? max($chartRevenues) : 1, count($chartProfits) ? max($chartProfits) : 1));
        @endphp

        @if(count($chartLabels) > 0)
            <div class="space-y-3">
                <div class="h-52 flex items-end gap-2 sm:gap-3 overflow-x-auto pb-2 custom-scrollbar pt-4">
                    @foreach($chartLabels as $idx => $lbl)
                        @php
                            $rev = $chartRevenues[$idx] ?? 0;
                            $prof = max(0, $chartProfits[$idx] ?? 0);
                            $revHeight = min(100, max(6, round(($rev / $maxVal) * 100)));
                            $profHeight = min(100, max(4, round(($prof / $maxVal) * 100)));
                        @endphp
                        <div class="flex-1 min-w-[34px] sm:min-w-[44px] flex flex-col items-center gap-1.5 group relative">
                            <!-- Tooltip on hover -->
                            <div class="absolute -top-12 z-20 hidden group-hover:flex flex-col items-center bg-gray-900 text-white text-[10px] py-1 px-2.5 rounded-lg shadow-lg whitespace-nowrap pointer-events-none">
                                <span>{{ $lbl }}: ৳{{ number_format($rev, 2) }}</span>
                                <span class="text-emerald-400 font-bold">Profit: ৳{{ number_format($prof, 2) }}</span>
                            </div>

                            <!-- Bars container -->
                            <div class="w-full flex items-end justify-center gap-1 h-36">
                                <!-- Revenue Bar -->
                                <div class="w-2.5 sm:w-3.5 bg-emerald-500/80 group-hover:bg-emerald-500 rounded-t-md transition-all"
                                     style="height: {{ $revHeight }}%;"></div>
                                <!-- Profit Bar -->
                                <div class="w-2.5 sm:w-3.5 bg-blue-500/80 group-hover:bg-blue-500 rounded-t-md transition-all"
                                     style="height: {{ $profHeight }}%;"></div>
                            </div>

                            <!-- Label -->
                            <span class="text-[10px] text-gray-400 truncate max-w-[42px]">{{ $lbl }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <div class="py-12 text-center text-gray-400 text-sm">
                No revenue activity recorded in this period.
            </div>
        @endif
    </div>

    <!-- ═════════ PRODUCT SALES BREAKDOWN TABLE ═════════ -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 dark:border-gray-700/80 flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="package" class="w-5 h-5 text-primary"></i>
                    <span>Product-wise Sales & Profit Performance</span>
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Showing performance for products sold in the selected period</p>
            </div>
            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                {{ count($productBreakdown) }} Product(s) Sold
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-800/50 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Product</th>
                        <th class="py-3.5 px-4 text-center">Units Sold</th>
                        <th class="py-3.5 px-4 text-right">Revenue (৳)</th>
                        <th class="py-3.5 px-4 text-right">Cost (COGS)</th>
                        <th class="py-3.5 px-4 text-right">Net Profit</th>
                        <th class="py-3.5 px-4 text-right">Profit Margin</th>
                        <th class="py-3.5 px-4 text-center">Revenue Share</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-sm">
                    @forelse($productBreakdown as $p)
                        @php
                            $sharePct = $grossRevenue > 0 ? ($p['revenue'] / $grossRevenue) * 100 : 0;
                        @endphp
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-700 overflow-hidden flex-shrink-0 flex items-center justify-center border border-gray-200 dark:border-gray-600">
                                        @if($p['thumbnail'])
                                            <img src="{{ $p['thumbnail'] }}" alt="{{ $p['name'] }}" class="w-full h-full object-cover">
                                        @else
                                            <i data-lucide="package" class="w-4 h-4 text-gray-400"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-900 dark:text-white line-clamp-1">
                                            {{ $p['name'] }}
                                        </div>
                                        <div class="text-xs text-gray-400">#ID: {{ $p['id'] }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center font-bold text-gray-900 dark:text-white">
                                {{ number_format($p['units_sold']) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold text-gray-900 dark:text-white">
                                ৳{{ number_format($p['revenue'], 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-semibold text-purple-700 dark:text-purple-400">
                                ৳{{ number_format($p['cogs'], 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold {{ $p['profit'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600' }}">
                                ৳{{ number_format($p['profit'], 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold {{ $p['margin_pct'] >= 30 ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400' : ($p['margin_pct'] >= 10 ? 'bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400') }}">
                                    {{ number_format($p['margin_pct'], 1) }}%
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <div class="w-16 h-2 bg-gray-100 dark:bg-gray-700 rounded-full overflow-hidden">
                                        <div class="h-full bg-primary rounded-full" style="width: {{ min(100, $sharePct) }}%;"></div>
                                    </div>
                                    <span class="text-xs text-gray-500 font-medium">{{ number_format($sharePct, 1) }}%</span>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-10 text-center text-gray-400">
                                No product sales recorded in the selected date range.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- ═════════ DETAILED SALES ORDERS LOG ═════════ -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700/80 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 dark:border-gray-700/80 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="receipt" class="w-5 h-5 text-primary"></i>
                    <span>Order-by-Order Sales Details</span>
                </h3>
                <p class="text-xs text-gray-500 mt-0.5">Individual transactions with revenue, COGS and profit margin</p>
            </div>
            <span class="text-xs font-semibold px-3 py-1 rounded-full bg-primary/10 text-primary">
                {{ $salesOrders->total() }} Orders Found
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-gray-700/60 bg-gray-50/50 dark:bg-gray-800/50 text-[11px] font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                        <th class="py-3.5 px-4">Order #</th>
                        <th class="py-3.5 px-4">Date & Time</th>
                        <th class="py-3.5 px-4">Customer</th>
                        <th class="py-3.5 px-4">Products Sold</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-4 text-right">Revenue</th>
                        <th class="py-3.5 px-4 text-right">COGS (Cost)</th>
                        <th class="py-3.5 px-4 text-right">Profit</th>
                        <th class="py-3.5 px-4 text-center">Invoice</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-sm">
                    @forelse($salesOrders as $ord)
                        @php
                            $orderRevenue = (float) $ord->total_amount;
                            $orderCogs = (float) $ord->items->sum(function($it) {
                                $unitCost = (float) ($it->purchase_price ?? ($it->product->purchase_price ?? 0));
                                return $it->quantity * $unitCost;
                            });
                            $orderProfit = $orderRevenue - $orderCogs;
                        @endphp
                        <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-700/30 transition-colors">
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <a href="{{ route('admin.orders.show', $ord->id) }}" class="font-bold text-primary hover:underline">
                                    #ORD-{{ str_pad($ord->id, 5, '0', STR_PAD_LEFT) }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4 whitespace-nowrap text-xs text-gray-500">
                                <div>{{ $ord->created_at->format('d M Y') }}</div>
                                <div class="text-[11px] text-gray-400">{{ $ord->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-gray-900 dark:text-white">{{ $ord->name }}</div>
                                <div class="text-xs text-gray-400">{{ $ord->phone }}</div>
                            </td>
                            <td class="py-3.5 px-4 max-w-xs">
                                <div class="space-y-1">
                                    @foreach($ord->items as $it)
                                        <div class="text-xs text-gray-700 dark:text-gray-300 truncate">
                                            <span class="font-medium text-gray-900 dark:text-white">{{ $it->product_name }}</span>
                                            <span class="text-gray-400">× {{ $it->quantity }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold 
                                    @if($ord->status === 'delivered') bg-emerald-50 text-emerald-700 dark:bg-emerald-950/40 dark:text-emerald-400
                                    @elseif($ord->status === 'cancelled') bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400
                                    @elseif($ord->status === 'shipped') bg-blue-50 text-blue-700 dark:bg-blue-950/40 dark:text-blue-400
                                    @else bg-amber-50 text-amber-700 dark:bg-amber-950/40 dark:text-amber-400
                                    @endif">
                                    {{ ucfirst($ord->status) }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold text-gray-900 dark:text-white">
                                ৳{{ number_format($orderRevenue, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-semibold text-purple-700 dark:text-purple-400">
                                ৳{{ number_format($orderCogs, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-right font-bold {{ $orderProfit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600' }}">
                                ৳{{ number_format($orderProfit, 2) }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <a href="{{ route('admin.orders.invoice', $ord->id) }}" target="_blank"
                                   class="p-1.5 rounded-lg text-gray-500 hover:text-primary hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors inline-flex items-center gap-1 text-xs">
                                    <i data-lucide="file-text" class="w-4 h-4"></i>
                                    <span>Invoice</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-12 text-center text-gray-400">
                                No orders matching the current filter criteria.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-gray-100 dark:border-gray-700/60">
            {{ $salesOrders->links() }}
        </div>
    </div>
</div>

<script>
function accountsManager() {
    return {
        selectedRange: '{{ $range }}',
        startDate: '{{ $startDate->format("Y-m-d") }}',
        endDate: '{{ $endDate->format("Y-m-d") }}',
        setRange(rangeKey) {
            this.selectedRange = rangeKey;
            const today = new Date();
            const format = (d) => d.toISOString().split('T')[0];

            if (rangeKey === 'today') {
                this.startDate = format(today);
                this.endDate = format(today);
            } else if (rangeKey === 'yesterday') {
                const yest = new Date(today);
                yest.setDate(yest.getDate() - 1);
                this.startDate = format(yest);
                this.endDate = format(yest);
            } else if (rangeKey === 'last_7_days') {
                const past7 = new Date(today);
                past7.setDate(past7.getDate() - 6);
                this.startDate = format(past7);
                this.endDate = format(today);
            } else if (rangeKey === 'this_month') {
                const startM = new Date(today.getFullYear(), today.getMonth(), 1);
                const endM = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                this.startDate = format(startM);
                this.endDate = format(endM);
            } else if (rangeKey === 'last_month') {
                const startLm = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                const endLm = new Date(today.getFullYear(), today.getMonth(), 0);
                this.startDate = format(startLm);
                this.endDate = format(endLm);
            } else if (rangeKey === 'this_year') {
                const startY = new Date(today.getFullYear(), 0, 1);
                const endY = new Date(today.getFullYear(), 11, 31);
                this.startDate = format(startY);
                this.endDate = format(endY);
            }
            // Auto-submit form when clicking a preset
            this.$nextTick(() => {
                this.$el.querySelector('form').submit();
            });
        }
    };
}
</script>
@endsection
