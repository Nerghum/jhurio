@extends('layouts.admin')
@section('title', 'Conversion Funnel')
@section('header', 'Conversion Funnel')
@section('content')

@php
    $max = max($funnelData['sessions'], 1);
    
    // Calculate widths relative to maximum (sessions)
    $wSessions = ($funnelData['sessions'] / $max) * 100;
    $wViewers = ($funnelData['viewers'] / $max) * 100;
    $wCart = ($funnelData['cart'] / $max) * 100;
    $wCheckout = ($funnelData['checkout'] / $max) * 100;
    $wPurchase = ($funnelData['purchase'] / $max) * 100;

    // Calculate drop-off rates
    $drViewers = $funnelData['sessions'] > 0 ? round(($funnelData['viewers'] / $funnelData['sessions']) * 100, 1) : 0;
    $drCart = $funnelData['viewers'] > 0 ? round(($funnelData['cart'] / $funnelData['viewers']) * 100, 1) : 0;
    $drCheckout = $funnelData['cart'] > 0 ? round(($funnelData['checkout'] / $funnelData['cart']) * 100, 1) : 0;
    $drPurchase = $funnelData['checkout'] > 0 ? round(($funnelData['purchase'] / $funnelData['checkout']) * 100, 1) : 0;
    
    // Overall conversion rate
    $overallCr = $funnelData['sessions'] > 0 ? round(($funnelData['purchase'] / $funnelData['sessions']) * 100, 1) : 0;
@endphp

<div class="max-w-4xl mx-auto space-y-6">
    <!-- Date Filter -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200">Last {{ $days }} Days</h2>
        <form method="GET" action="{{ route('admin.analytics.funnel') }}" class="flex items-center space-x-2">
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

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8">
        <div class="text-center mb-10">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">Ecommerce Funnel</h3>
            <p class="text-gray-500 dark:text-gray-400 mt-2">Overall Store Conversion Rate: <span class="font-bold text-green-600">{{ $overallCr }}%</span></p>
        </div>

        <div class="space-y-6">
            <!-- Step 1: Sessions -->
            <div class="relative">
                <div class="flex justify-between items-end mb-1">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">1. Total Sessions</span>
                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($funnelData['sessions']) }}</span>
                </div>
                <div class="w-full bg-gray-100 dark:bg-gray-700 rounded-full h-8 overflow-hidden">
                    <div class="bg-blue-500 h-8 rounded-full flex items-center justify-end px-3 transition-all duration-1000" style="width: {{ $wSessions }}%">
                    </div>
                </div>
            </div>

            <!-- Arrow Down -->
            <div class="flex justify-center -my-2 z-10 relative">
                <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 px-3 py-1 rounded-full text-xs font-semibold text-gray-500 flex items-center shadow-sm">
                    {{ $drViewers }}% continued
                    <i data-lucide="arrow-down" class="w-3 h-3 ml-1 text-gray-400"></i>
                </div>
            </div>

            <!-- Step 2: Product Views -->
            <div class="relative">
                <div class="flex justify-between items-end mb-1">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">2. Viewed a Product</span>
                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($funnelData['viewers']) }}</span>
                </div>
                <div class="w-full flex justify-center">
                    <div class="bg-gray-100 dark:bg-gray-700 rounded-full h-8 overflow-hidden" style="width: {{ $wViewers }}%; min-width: 10%;">
                        <div class="bg-indigo-500 h-8 rounded-full w-full"></div>
                    </div>
                </div>
            </div>

            <!-- Arrow Down -->
            <div class="flex justify-center -my-2 z-10 relative">
                <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 px-3 py-1 rounded-full text-xs font-semibold text-gray-500 flex items-center shadow-sm">
                    {{ $drCart }}% added to cart
                    <i data-lucide="arrow-down" class="w-3 h-3 ml-1 text-gray-400"></i>
                </div>
            </div>

            <!-- Step 3: Add to Cart -->
            <div class="relative">
                <div class="flex justify-between items-end mb-1">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">3. Added to Cart</span>
                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($funnelData['cart']) }}</span>
                </div>
                <div class="w-full flex justify-center">
                    <div class="bg-gray-100 dark:bg-gray-700 rounded-full h-8 overflow-hidden" style="width: {{ $wCart }}%; min-width: 10%;">
                        <div class="bg-yellow-500 h-8 rounded-full w-full"></div>
                    </div>
                </div>
            </div>

            <!-- Arrow Down -->
            <div class="flex justify-center -my-2 z-10 relative">
                <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 px-3 py-1 rounded-full text-xs font-semibold text-gray-500 flex items-center shadow-sm">
                    {{ $drCheckout }}% initiated checkout
                    <i data-lucide="arrow-down" class="w-3 h-3 ml-1 text-gray-400"></i>
                </div>
            </div>

            <!-- Step 4: Checkout Started -->
            <div class="relative">
                <div class="flex justify-between items-end mb-1">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">4. Started Checkout</span>
                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($funnelData['checkout']) }}</span>
                </div>
                <div class="w-full flex justify-center">
                    <div class="bg-gray-100 dark:bg-gray-700 rounded-full h-8 overflow-hidden" style="width: {{ $wCheckout }}%; min-width: 10%;">
                        <div class="bg-orange-500 h-8 rounded-full w-full"></div>
                    </div>
                </div>
            </div>

            <!-- Arrow Down -->
            <div class="flex justify-center -my-2 z-10 relative">
                <div class="bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 px-3 py-1 rounded-full text-xs font-semibold text-gray-500 flex items-center shadow-sm">
                    {{ $drPurchase }}% purchased
                    <i data-lucide="arrow-down" class="w-3 h-3 ml-1 text-gray-400"></i>
                </div>
            </div>

            <!-- Step 5: Purchases -->
            <div class="relative">
                <div class="flex justify-between items-end mb-1">
                    <span class="text-sm font-medium text-gray-700 dark:text-gray-300">5. Completed Purchase</span>
                    <span class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($funnelData['purchase']) }}</span>
                </div>
                <div class="w-full flex justify-center">
                    <div class="bg-gray-100 dark:bg-gray-700 rounded-full h-8 overflow-hidden shadow-[0_0_15px_rgba(34,197,94,0.5)]" style="width: {{ $wPurchase }}%; min-width: 10%;">
                        <div class="bg-green-500 h-8 rounded-full w-full"></div>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

@endsection
