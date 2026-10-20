@extends('layouts.admin')
@section('title', 'Product Analytics')
@section('header', 'Product Analytics')
@section('content')

<div class="max-w-7xl mx-auto space-y-6">
    <!-- Date Filter -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Last {{ $days }} Days</h2>
        <form method="GET" action="{{ route('admin.analytics.products') }}" class="flex items-center space-x-2">
            <select name="days" class="bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-sm rounded-lg focus:ring-primary focus:border-primary p-2">
                <option value="7" {{ $days == 7 ? 'selected' : '' }}>Last 7 Days</option>
                <option value="30" {{ $days == 30 ? 'selected' : '' }}>Last 30 Days</option>
                <option value="90" {{ $days == 90 ? 'selected' : '' }}>Last 90 Days</option>
            </select>
            <button type="submit" class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                Apply
            </button>
        </form>
    </div>

    <!-- Product Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex justify-between items-center">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Product Performance</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-600 dark:text-gray-400">
                <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-300 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-6 py-4 font-medium whitespace-nowrap">Product</th>
                        <th class="px-6 py-4 font-medium text-right">Views</th>
                        <th class="px-6 py-4 font-medium text-right">Add to Cart</th>
                        <th class="px-6 py-4 font-medium text-right">Checkouts</th>
                        <th class="px-6 py-4 font-medium text-right">Purchases</th>
                        <th class="px-6 py-4 font-medium text-right">Conversion Rate</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <td class="px-6 py-4 flex items-center space-x-3">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" class="w-10 h-10 rounded-lg object-cover border border-gray-200 dark:border-gray-600">
                            @else
                                <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center border border-gray-200 dark:border-gray-600">
                                    <i data-lucide="image" class="w-4 h-4 text-gray-400"></i>
                                </div>
                            @endif
                            <span class="font-medium text-gray-900 dark:text-white line-clamp-1 max-w-[250px]">{{ $product->name }}</span>
                        </td>
                        <td class="px-6 py-4 text-right font-medium text-blue-600 dark:text-blue-400">
                            {{ number_format($product->analytics_views) }}
                        </td>
                        <td class="px-6 py-4 text-right font-medium text-yellow-600 dark:text-yellow-400">
                            {{ number_format($product->analytics_cart) }}
                        </td>
                        <td class="px-6 py-4 text-right font-medium text-orange-600 dark:text-orange-400">
                            {{ number_format($product->analytics_checkout) }}
                        </td>
                        <td class="px-6 py-4 text-right font-medium text-green-600 dark:text-green-400">
                            {{ number_format($product->analytics_purchases) }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->conversion_rate > 2 ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400' : ($product->conversion_rate > 0 ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300') }}">
                                {{ $product->conversion_rate }}%
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                            <i data-lucide="inbox" class="w-12 h-12 mx-auto text-gray-400 mb-3"></i>
                            <p>No product tracking data available for this date range.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection
