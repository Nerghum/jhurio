@extends('layouts.app')

@section('title', 'Invoice #ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT) . ' - Jhurio')

@section('content')
@php
    $itemsSubtotal = $order->items->sum(function($item) {
        return $item->price * $item->quantity;
    });

    $statusMap = [
        'pending'    => [
            'label' => 'Pending Confirmation',
            'badge' => 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-400 dark:border-amber-800',
            'dot'   => 'bg-amber-500',
            'icon'  => 'clock',
            'step'  => 1
        ],
        'processing' => [
            'label' => 'Processing Order',
            'badge' => 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-800',
            'dot'   => 'bg-blue-500',
            'icon'  => 'package',
            'step'  => 2
        ],
        'shipped'    => [
            'label' => 'Shipped / Out for Delivery',
            'badge' => 'bg-purple-50 text-purple-700 border-purple-200 dark:bg-purple-900/30 dark:text-purple-400 dark:border-purple-800',
            'dot'   => 'bg-purple-500',
            'icon'  => 'truck',
            'step'  => 3
        ],
        'delivered'  => [
            'label' => 'Delivered',
            'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800',
            'dot'   => 'bg-emerald-500',
            'icon'  => 'check-circle-2',
            'step'  => 4
        ],
        'completed'  => [
            'label' => 'Completed',
            'badge' => 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-400 dark:border-emerald-800',
            'dot'   => 'bg-emerald-500',
            'icon'  => 'check-circle-2',
            'step'  => 4
        ],
        'cancelled'  => [
            'label' => 'Order Cancelled',
            'badge' => 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-900/30 dark:text-rose-400 dark:border-rose-800',
            'dot'   => 'bg-rose-500',
            'icon'  => 'x-circle',
            'step'  => 0
        ],
    ];

    $currentStatus = $statusMap[$order->status] ?? $statusMap['pending'];
    $activeStep = $currentStatus['step'];
    $formattedOrderNumber = '#ORD-' . str_pad($order->id, 5, '0', STR_PAD_LEFT);
@endphp

<div class="invoice-page-wrapper bg-gray-50/60 dark:bg-dark min-h-screen py-8 sm:py-12" x-data="{
    copied: false,
    copyOrder() {
        navigator.clipboard.writeText('{{ $formattedOrderNumber }}');
        this.copied = true;
        setTimeout(() => this.copied = false, 2000);
    }
}">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

        <!-- ══════════════════════════════════════════════
             TOP ACTION BAR & BREADCRUMB (Hidden on Print)
        ══════════════════════════════════════════════ -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden">
            <div class="flex items-center gap-3">
                <a href="{{ route('shop.index') }}" class="w-10 h-10 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 flex items-center justify-center text-gray-500 hover:text-primary hover:border-primary/40 transition shadow-xs">
                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                </a>
                <div>
                    <h1 class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight flex items-center gap-2">
                        Invoice Details
                    </h1>
                    <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Customer Copy &middot; Verified Digital Invoice</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 flex-wrap">
                <button type="button" onclick="window.print()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 text-xs font-bold transition shadow-xs">
                    <i data-lucide="printer" class="w-4 h-4 text-gray-500 dark:text-gray-400"></i>
                    <span>Print Invoice</span>
                </button>

                @auth
                <a href="{{ route('account.orders') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-white hover:bg-secondary text-xs font-bold transition shadow-sm shadow-primary/20">
                    <i data-lucide="package" class="w-4 h-4"></i>
                    <span>My Orders</span>
                </a>
                @else
                <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-primary text-white hover:bg-secondary text-xs font-bold transition shadow-sm shadow-primary/20">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    <span>Continue Shopping</span>
                </a>
                @endauth
            </div>
        </div>

        <!-- ══════════════════════════════════════════════
             STATUS BANNER & CELEBRATION (Hidden on Print)
        ══════════════════════════════════════════════ -->
        @if($order->status !== 'cancelled')
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary via-[#0f4a0a] to-primary p-6 sm:p-8 text-white shadow-xl shadow-primary/10 border border-white/10 print:hidden">
            <!-- Decorative background accents -->
            <div class="absolute -right-8 -top-8 w-48 h-48 rounded-full bg-secondary/20 blur-3xl pointer-events-none"></div>
            <div class="absolute -left-8 -bottom-8 w-48 h-48 rounded-full bg-white/10 blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                <div class="flex items-start gap-4">
                    <div class="w-14 h-14 rounded-2xl bg-white/15 backdrop-blur-md border border-white/20 flex items-center justify-center flex-shrink-0 shadow-inner">
                        <i data-lucide="check-check" class="w-7 h-7 text-secondary"></i>
                    </div>
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-secondary/25 border border-secondary/40 text-secondary text-xs font-bold uppercase tracking-wider mb-2">
                            <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                            Order Confirmed
                        </div>
                        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                            Thank you, {{ Str::title($order->name) }}!
                        </h2>
                        <p class="text-sm text-gray-200 mt-1 max-w-xl leading-relaxed">
                            Your order has been received and is being processed. We will contact you at <strong class="text-white">{{ $order->phone }}</strong> before dispatching your package.
                        </p>
                    </div>
                </div>

                <div class="flex sm:flex-col items-center sm:items-end justify-between sm:justify-center border-t sm:border-t-0 border-white/15 pt-4 sm:pt-0">
                    <span class="text-xs uppercase tracking-wider text-gray-300 font-semibold">Payment Status</span>
                    <span class="text-lg font-black text-secondary flex items-center gap-1.5 mt-0.5">
                        <i data-lucide="banknote" class="w-5 h-5"></i> Cash on Delivery
                    </span>
                    <span class="text-xs text-gray-300">Pay ৳{{ number_format($order->total_amount, 0) }} on arrival</span>
                </div>
            </div>

            <!-- Visual Progress Stepper -->
            <div class="mt-8 pt-6 border-t border-white/15">
                <div class="grid grid-cols-4 gap-2 sm:gap-4 relative">
                    @php
                        $steps = [
                            1 => ['name' => 'Order Placed', 'icon' => 'clipboard-check'],
                            2 => ['name' => 'Processing',   'icon' => 'package'],
                            3 => ['name' => 'Shipped',      'icon' => 'truck'],
                            4 => ['name' => 'Delivered',    'icon' => 'home'],
                        ];
                    @endphp

                    @foreach($steps as $sIndex => $step)
                        @php
                            $isComplete = $activeStep >= $sIndex;
                            $isCurrent  = $activeStep === $sIndex;
                        @endphp
                        <div class="flex flex-col items-center text-center">
                            <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-full flex items-center justify-center transition-all duration-300 {{ $isComplete ? 'bg-secondary text-primary font-bold shadow-lg shadow-secondary/30 scale-105' : 'bg-white/10 text-white/50 border border-white/20' }}">
                                @if($isComplete && $activeStep > $sIndex)
                                    <i data-lucide="check" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                                @else
                                    <i data-lucide="{{ $step['icon'] }}" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                                @endif
                            </div>
                            <span class="mt-2 text-[11px] sm:text-xs font-bold leading-tight {{ $isComplete ? 'text-white' : 'text-white/50' }}">
                                {{ $step['name'] }}
                            </span>
                            @if($isCurrent)
                                <span class="hidden sm:inline-block text-[10px] text-secondary font-semibold mt-0.5">Current Stage</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @else
        <!-- Order Cancelled Banner -->
        <div class="rounded-3xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 p-6 flex items-start gap-4 print:hidden">
            <div class="w-12 h-12 rounded-2xl bg-rose-100 dark:bg-rose-900/60 text-rose-600 dark:text-rose-400 flex items-center justify-center flex-shrink-0">
                <i data-lucide="alert-circle" class="w-6 h-6"></i>
            </div>
            <div>
                <h3 class="text-lg font-bold text-rose-900 dark:text-rose-200">This Order Has Been Cancelled</h3>
                <p class="text-xs sm:text-sm text-rose-700 dark:text-rose-300 mt-1">
                    This order was cancelled and will not be delivered. If this was a mistake or you wish to order again, please browse our store.
                </p>
                <div class="mt-4">
                    <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i> Browse Products
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- ══════════════════════════════════════════════
             THE MAIN INVOICE DOCUMENT CARD
        ══════════════════════════════════════════════ -->
        <div class="invoice-printable-card bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-xl overflow-hidden print:border-none print:shadow-none print:p-0">
            
            <!-- Invoice Header Bar -->
            <div class="p-6 sm:p-8 lg:p-10 border-b border-gray-100 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6">
                    
                    <!-- Left: Brand Logo & Title -->
                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            <a href="/" class="inline-block">
                                <img src="{{ asset('assets/images/logo.png') }}" alt="Jhurio" class="h-10 sm:h-12 w-auto object-contain">
                            </a>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-primary/10 text-primary dark:text-secondary text-[11px] font-bold">
                                <i data-lucide="badge-check" class="w-3.5 h-3.5"></i> Verified
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Dhaka, Bangladesh &nbsp;&bull;&nbsp; Hotline: <span class="font-semibold text-gray-700 dark:text-gray-300">+880 1924-301474</span>
                        </p>
                    </div>

                    <!-- Right: Invoice Pill & Order ID -->
                    <div class="sm:text-right space-y-1.5">
                        <div class="flex sm:justify-end items-center gap-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-extrabold uppercase tracking-widest bg-gray-900 text-white dark:bg-gray-700">
                                Invoice
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $currentStatus['badge'] }}">
                                <span class="w-2 h-2 rounded-full {{ $currentStatus['dot'] }}"></span>
                                {{ $currentStatus['label'] }}
                            </span>
                        </div>
                        
                        <!-- Order Number with Click-to-Copy -->
                        <div class="flex sm:justify-end items-center gap-2 pt-1">
                            <span class="text-xl sm:text-2xl font-black text-gray-900 dark:text-white tracking-tight font-mono">
                                {{ $formattedOrderNumber }}
                            </span>
                            <button type="button" @click="copyOrder()" title="Copy Order Number" class="p-1.5 text-gray-400 hover:text-primary hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition print:hidden relative">
                                <i data-lucide="copy" class="w-4 h-4"></i>
                                <span x-show="copied" x-cloak class="absolute -top-7 right-0 text-[10px] bg-gray-900 text-white px-2 py-0.5 rounded-md shadow-md whitespace-nowrap">
                                    Copied!
                                </span>
                            </button>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            Issued: <strong class="text-gray-700 dark:text-gray-300">{{ $order->created_at->format('d M Y, h:i A') }}</strong>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Metadata Info Grid: Billed/Shipped To & Method -->
            <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-100 dark:divide-gray-700 bg-gray-50/50 dark:bg-gray-900/40 border-b border-gray-100 dark:border-gray-700">
                
                <!-- Recipient & Delivery Details -->
                <div class="p-6 sm:p-8 space-y-3">
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                        <i data-lucide="map-pin" class="w-4 h-4 text-primary dark:text-secondary"></i>
                        <span>Customer &amp; Shipping Details</span>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            {{ $order->name }}
                        </h4>
                        <div class="mt-2 space-y-1.5 text-xs sm:text-sm text-gray-600 dark:text-gray-300">
                            <p class="flex items-center gap-2 font-medium">
                                <i data-lucide="phone" class="w-3.5 h-3.5 text-gray-400"></i>
                                <a href="tel:{{ $order->phone }}" class="hover:text-primary hover:underline">{{ $order->phone }}</a>
                            </p>
                            <p class="flex items-start gap-2 leading-relaxed">
                                <i data-lucide="navigation" class="w-3.5 h-3.5 text-gray-400 mt-0.5 flex-shrink-0"></i>
                                <span>{{ $order->shipping_address }}{{ $order->district ? ', ' . $order->district : '' }}</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Shipping Method & Payment Details -->
                <div class="p-6 sm:p-8 space-y-3">
                    <div class="flex items-center gap-2 text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                        <i data-lucide="truck" class="w-4 h-4 text-primary dark:text-secondary"></i>
                        <span>Delivery &amp; Payment Method</span>
                    </div>
                    <div class="space-y-2 text-xs sm:text-sm">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Delivery Method:</span>
                            <span class="font-bold text-gray-900 dark:text-white">
                                {{ $order->deliveryMethod ? $order->deliveryMethod->name : 'Standard Courier Delivery' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Estimated Timeline:</span>
                            <span class="font-semibold text-gray-800 dark:text-gray-200">
                                @if(stripos($order->shipping_address, 'dhaka') !== false || ($order->deliveryMethod && stripos($order->deliveryMethod->name, 'inside') !== false))
                                    24 &ndash; 48 Hours
                                @else
                                    2 &ndash; 4 Business Days
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 dark:text-gray-400">Payment Option:</span>
                            <span class="inline-flex items-center gap-1 font-bold text-primary dark:text-secondary">
                                <i data-lucide="banknote" class="w-3.5 h-3.5"></i> Cash on Delivery (COD)
                            </span>
                        </div>
                        @if($order->notes)
                        <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                            <span class="text-xs text-gray-500 dark:text-gray-400 block mb-0.5">Order Note:</span>
                            <p class="text-xs text-gray-700 dark:text-gray-300 italic bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-100 dark:border-gray-700">
                                &ldquo;{{ $order->notes }}&rdquo;
                            </p>
                        </div>
                        @endif
                    </div>
                </div>

            </div>

            <!-- ══════════════════════════════════════════════
                 ITEMIZED PRODUCTS TABLE
            ══════════════════════════════════════════════ -->
            <div class="p-6 sm:p-8 lg:p-10">
                <h3 class="text-sm font-bold uppercase tracking-wider text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                    <i data-lucide="shopping-bag" class="w-4 h-4 text-primary dark:text-secondary"></i>
                    <span>Ordered Items ({{ $order->items->sum('quantity') }})</span>
                </h3>

                <!-- Desktop / Tablet Table View -->
                <div class="overflow-x-auto rounded-2xl border border-gray-100 dark:border-gray-700">
                    <table class="w-full text-left text-xs sm:text-sm">
                        <thead class="bg-gray-50 dark:bg-gray-900/60 text-gray-500 dark:text-gray-400 uppercase font-semibold text-[11px] tracking-wider border-b border-gray-100 dark:border-gray-700">
                            <tr>
                                <th scope="col" class="py-3.5 px-4 sm:px-6 w-12 text-center">#</th>
                                <th scope="col" class="py-3.5 px-4 sm:px-6">Item Description</th>
                                <th scope="col" class="py-3.5 px-4 sm:px-6 text-center w-24">Qty</th>
                                <th scope="col" class="py-3.5 px-4 sm:px-6 text-right w-28">Unit Price</th>
                                <th scope="col" class="py-3.5 px-4 sm:px-6 text-right w-32">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @foreach($order->items as $index => $item)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-750 transition-colors">
                                <td class="py-4 px-4 sm:px-6 text-center text-gray-400 font-medium font-mono">
                                    {{ $index + 1 }}
                                </td>
                                <td class="py-4 px-4 sm:px-6">
                                    <div class="flex items-center gap-3.5">
                                        <!-- Thumbnail -->
                                        <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-100 dark:border-gray-700 overflow-hidden flex-shrink-0 flex items-center justify-center">
                                            @if($item->product && $item->product->thumbnail)
                                                <img src="{{ $item->product->thumbnail->url }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                                            @else
                                                <i data-lucide="package" class="w-6 h-6 text-gray-300 dark:text-gray-600"></i>
                                            @endif
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            @if($item->product && $item->product->slug)
                                                <a href="{{ route('product.show', $item->product->slug) }}" class="font-bold text-gray-900 dark:text-white hover:text-primary dark:hover:text-secondary transition line-clamp-1">
                                                    {{ $item->product_name }}
                                                </a>
                                            @else
                                                <span class="font-bold text-gray-900 dark:text-white line-clamp-1">
                                                    {{ $item->product_name }}
                                                </span>
                                            @endif

                                            @if($item->variation_name)
                                                <span class="inline-block mt-1 text-[11px] font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded-md">
                                                    {{ $item->variation_name }}
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-4 sm:px-6 text-center">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700 font-bold text-gray-800 dark:text-gray-200 text-xs">
                                        {{ $item->quantity }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 sm:px-6 text-right font-medium text-gray-700 dark:text-gray-300 font-mono">
                                    ৳{{ number_format($item->price, 2) }}
                                </td>
                                <td class="py-4 px-4 sm:px-6 text-right font-bold text-gray-900 dark:text-white font-mono text-sm sm:text-base">
                                    ৳{{ number_format($item->price * $item->quantity, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- ══════════════════════════════════════════════
                     TOTALS & FINANCIAL BREAKDOWN
                ══════════════════════════════════════════════ -->
                <div class="mt-8 flex flex-col sm:flex-row justify-between items-start gap-6 pt-6 border-t border-gray-100 dark:border-gray-700">
                    
                    <!-- Left: Delivery & Return Note / Guarantee -->
                    <div class="w-full sm:max-w-xs space-y-3">
                        <div class="p-4 rounded-2xl bg-gray-50 dark:bg-gray-900/50 border border-gray-100 dark:border-gray-700 text-xs space-y-2">
                            <div class="flex items-center gap-2 font-bold text-gray-900 dark:text-white">
                                <i data-lucide="shield-check" class="w-4 h-4 text-primary dark:text-secondary"></i>
                                <span>100% Buyer Protection</span>
                            </div>
                            <p class="text-gray-500 dark:text-gray-400 leading-relaxed text-[11px]">
                                Check your package upon delivery. If you receive any damaged or defective items, our 7-day hassle-free return policy has you covered.
                            </p>
                        </div>
                    </div>

                    <!-- Right: Calculations Table -->
                    <div class="w-full sm:w-80 space-y-2.5">
                        <div class="flex items-center justify-between text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                            <span>Subtotal:</span>
                            <span class="font-mono font-medium text-gray-900 dark:text-white">৳{{ number_format($itemsSubtotal, 2) }}</span>
                        </div>

                        <div class="flex items-center justify-between text-xs sm:text-sm text-gray-600 dark:text-gray-400">
                            <span class="flex items-center gap-1">
                                Delivery Fee:
                                @if($order->deliveryMethod)
                                    <span class="text-[11px] text-gray-400">({{ $order->deliveryMethod->name }})</span>
                                @endif
                            </span>
                            <span class="font-mono font-medium text-gray-900 dark:text-white">
                                @if($order->delivery_cost > 0)
                                    ৳{{ number_format($order->delivery_cost, 2) }}
                                @else
                                    <span class="text-emerald-600 font-bold uppercase text-xs">Free</span>
                                @endif
                            </span>
                        </div>

                        @if($order->discount_amount > 0)
                        <div class="flex items-center justify-between text-xs sm:text-sm text-emerald-600 font-medium">
                            <span class="flex items-center gap-1">
                                Discount Applied
                                @if($order->coupon_code)
                                    <span class="text-[10px] bg-emerald-100 text-emerald-800 px-1.5 py-0.5 rounded font-mono font-bold">{{ $order->coupon_code }}</span>
                                @endif
                                :
                            </span>
                            <span class="font-mono font-bold">-৳{{ number_format($order->discount_amount, 2) }}</span>
                        </div>
                        @endif

                        <!-- Grand Total Highlight Card -->
                        <div class="mt-4 p-4 rounded-2xl bg-gradient-to-r from-primary/10 via-primary/5 to-secondary/10 dark:from-gray-700 dark:to-gray-700/60 border border-primary/20 dark:border-gray-600">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300">Total Payable</span>
                                    <span class="text-[10px] text-gray-500 dark:text-gray-400">Cash on Delivery</span>
                                </div>
                                <span class="text-xl sm:text-2xl font-black text-primary dark:text-secondary font-mono">
                                    ৳{{ number_format($order->total_amount, 2) }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Invoice Footer Note -->
            <div class="p-6 bg-gray-50/80 dark:bg-gray-900/60 border-t border-gray-100 dark:border-gray-700 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-gray-500 dark:text-gray-400">
                <div class="flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-4 h-4 text-primary dark:text-secondary"></i>
                    <span>Thank you for shopping with <strong>Jhurio</strong>. Have a wonderful day!</span>
                </div>
                <div class="text-[11px] text-gray-400">
                    Computer Generated Invoice &bull; No Signature Required
                </div>
            </div>

        </div>

        <!-- ══════════════════════════════════════════════
             CUSTOMER HELP & SUPPORT BAR (Hidden on Print)
        ══════════════════════════════════════════════ -->
        <div class="rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 p-6 sm:p-8 shadow-sm flex flex-col md:flex-row items-center justify-between gap-6 print:hidden">
            <div class="flex items-center gap-4 text-center sm:text-left">
                <div class="w-12 h-12 rounded-2xl bg-primary/10 text-primary dark:text-secondary flex items-center justify-center flex-shrink-0">
                    <i data-lucide="help-circle" class="w-6 h-6"></i>
                </div>
                <div>
                    <h4 class="font-bold text-gray-900 dark:text-white text-base">Questions or changes to your order?</h4>
                    <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                        Our support team is active 24/7. Reach out directly with your Order ID <strong class="text-gray-800 dark:text-gray-200">{{ $formattedOrderNumber }}</strong>.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3 w-full sm:w-auto">
                <!-- WhatsApp Support -->
                <a href="https://wa.me/8801924301474?text={{ urlencode('Hi Jhurio Support, I need help with my Order ' . $formattedOrderNumber) }}" target="_blank" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs">
                    <i data-lucide="message-circle" class="w-4 h-4"></i>
                    <span>WhatsApp</span>
                </a>

                <!-- Call Hotline -->
                <a href="tel:+8801924301474" class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 text-xs font-bold transition">
                    <i data-lucide="phone-call" class="w-4 h-4"></i>
                    <span>Call Us</span>
                </a>
            </div>
        </div>

        <!-- ══════════════════════════════════════════════
             BOTTOM NAVIGATION BUTTONS (Hidden on Print)
        ══════════════════════════════════════════════ -->
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4 print:hidden pt-4 pb-12">
            @auth
            <a href="{{ route('account.orders') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-2xl bg-primary hover:bg-secondary text-white font-bold text-sm shadow-lg shadow-primary/20 transition">
                <i data-lucide="layout-dashboard" class="w-4 h-4"></i>
                <span>Go to My Account Orders</span>
            </a>
            @endauth

            <a href="{{ route('shop.index') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-3.5 rounded-2xl bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 text-gray-800 dark:text-gray-200 font-bold text-sm shadow-xs transition">
                <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                <span>Continue Shopping</span>
            </a>
        </div>

    </div>
</div>

<style>
/* ══════════════════════════════════════════════
   HIGH FIDELITY PRINT STYLESHEET
   Triggered on window.print() or Ctrl+P
══════════════════════════════════════════════ */
@media print {
    /* Hide navigation bar, footer, and interactive chrome from layout */
    nav, 
    footer, 
    .fixed, 
    .bottom-nav, 
    header,
    .print\:hidden {
        display: none !important;
    }

    body {
        background: #ffffff !important;
        color: #000000 !important;
        font-size: 12pt;
    }

    main {
        padding-top: 0 !important;
        min-height: auto !important;
    }

    .invoice-page-wrapper {
        background: #ffffff !important;
        padding: 0 !important;
        margin: 0 !important;
        min-height: auto !important;
    }

    .invoice-printable-card {
        box-shadow: none !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }

    /* Keep crisp contrast on paper */
    .text-primary, .text-secondary {
        color: #0b3506 !important;
    }
}
</style>
@endsection

@push('scripts')
<script>
    // Fire frontend Purchase event for Facebook Pixel & Analytics Tracker
    if (typeof window.trackEvent === 'function') {
        window.trackEvent('Purchase', {
            content_ids: {!! json_encode($order->items->pluck('product_id')->filter()->values()->toArray()) !!},
            content_type: 'product',
            value: {{ $order->total_amount }},
            currency: 'BDT',
            num_items: {{ $order->items->sum('quantity') }},
            transaction_id: '{{ $order->id }}'
        });
    }

    // Initialize lucide icons if loaded dynamically
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
@endpush
