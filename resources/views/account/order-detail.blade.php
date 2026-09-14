@extends('layouts.app')

@section('title', 'Order #' . $order->id)

@section('content')
@php
$statusConfig = [
    'pending'    => ['label' => 'Pending',    'class' => 'bg-yellow-100 text-yellow-800', 'icon' => 'clock'],
    'processing' => ['label' => 'Processing', 'class' => 'bg-blue-100 text-blue-800',   'icon' => 'package'],
    'shipped'    => ['label' => 'Shipped',    'class' => 'bg-purple-100 text-purple-800','icon' => 'truck'],
    'delivered'  => ['label' => 'Delivered',  'class' => 'bg-indigo-100 text-indigo-800','icon' => 'map-pin'],
    'completed'  => ['label' => 'Completed',  'class' => 'bg-green-100 text-green-800', 'icon' => 'check-circle-2'],
    'cancelled'  => ['label' => 'Cancelled',  'class' => 'bg-red-100 text-red-800',     'icon' => 'x-circle'],
];
$s = $statusConfig[$order->status] ?? $statusConfig['pending'];
$flow = ['pending', 'processing', 'shipped', 'delivered', 'completed'];
$idx = array_search($order->status, $flow);
@endphp

<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('account.orders') }}" class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-500 hover:text-primary hover:bg-primary/10 transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Order #{{ $order->id }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
        </div>
        <span class="ml-auto inline-flex items-center gap-1.5 px-4 py-2 rounded-full text-sm font-semibold {{ $s['class'] }}">
            <i data-lucide="{{ $s['icon'] }}" class="w-4 h-4"></i>
            {{ $s['label'] }}
        </span>
    </div>

    <!-- Progress Timeline -->
    @if($order->status !== 'cancelled')
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 sm:p-8 mb-6">
        <h3 class="font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-8 text-sm">Delivery Progress</h3>
        <div class="relative flex items-start justify-between">
            <div class="absolute left-6 right-6 top-5 h-1 bg-gray-100 dark:bg-gray-700 rounded-full"></div>
            @if($idx !== false)
            <div class="absolute left-6 top-5 h-1 bg-primary rounded-full transition-all duration-700"
                 style="width: calc({{ $idx > 0 ? ($idx / (count($flow)-1)) * 100 : 0 }}% - 0px); right: auto;"></div>
            @endif

            @foreach($flow as $i => $step)
            @php $done = ($idx !== false && $i <= $idx); @endphp
            <div class="flex flex-col items-center gap-3 relative z-10 w-1/5">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-full border-4 flex items-center justify-center transition-all bg-white dark:bg-gray-800
                    {{ $done ? 'border-primary text-primary shadow-md shadow-primary/20' : 'border-gray-100 dark:border-gray-700 text-gray-400' }}">
                    @if($step === 'pending') <i data-lucide="clock" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                    @elseif($step === 'processing') <i data-lucide="package" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                    @elseif($step === 'shipped') <i data-lucide="truck" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                    @elseif($step === 'delivered') <i data-lucide="map-pin" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                    @elseif($step === 'completed') <i data-lucide="check-circle-2" class="w-4 h-4 sm:w-5 sm:h-5"></i>
                    @endif
                </div>
                <span class="text-xs sm:text-sm font-semibold text-center leading-tight {{ $done ? 'text-gray-900 dark:text-white' : 'text-gray-400 dark:text-gray-500' }}">
                    {{ ucfirst($step) }}
                </span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Items List -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-50 dark:border-gray-700">
                    <h3 class="font-bold text-gray-900 dark:text-white">Order Items</h3>
                </div>
                <div class="divide-y divide-gray-50 dark:divide-gray-700">
                    @foreach($order->items as $item)
                    <div class="flex items-center gap-4 px-6 py-5">
                        <div class="w-16 h-16 rounded-xl bg-gray-50 dark:bg-gray-900 flex-shrink-0 border border-gray-100 dark:border-gray-700 overflow-hidden">
                            @if($item->product?->thumbnail)
                            <img src="{{ $item->product->thumbnail->url }}" alt="{{ $item->product_name }}" class="w-full h-full object-cover">
                            @else
                            <div class="w-full h-full flex items-center justify-center"><i data-lucide="image" class="w-6 h-6 text-gray-400"></i></div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="font-semibold text-gray-900 dark:text-white text-base truncate">{{ $item->product_name }}</h4>
                            @if($item->variation_name)
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $item->variation_name }}</p>
                            @endif
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">Qty: <span class="font-bold">{{ $item->quantity }}</span> <span class="mx-1 text-gray-300 dark:text-gray-600">•</span> ৳{{ number_format($item->price, 0) }} each</p>
                            
                            @if($order->status === 'delivered' && $item->product)
                                <div class="mt-3">
                                    <a href="{{ route('product.show', $item->product->slug) }}#customer-reviews" class="inline-flex items-center gap-1.5 text-xs font-semibold text-primary bg-primary/10 hover:bg-primary/20 px-3 py-1.5 rounded-lg transition-colors">
                                        <i data-lucide="star" class="w-3.5 h-3.5"></i> Write a Review
                                    </a>
                                </div>
                            @endif
                        </div>
                        <p class="font-bold text-gray-900 dark:text-white text-lg flex-shrink-0">৳{{ number_format($item->price * $item->quantity, 0) }}</p>
                    </div>
                    @endforeach
                </div>
                <div class="px-6 py-5 bg-gray-50/50 dark:bg-gray-900/30 border-t border-gray-100 dark:border-gray-700 space-y-2">
                    @if($order->discount_amount > 0)
                    <div class="flex justify-between text-base text-gray-600 dark:text-gray-400">
                        <span>Subtotal</span>
                        <span>৳{{ number_format($order->total_amount + $order->discount_amount, 0) }}</span>
                    </div>
                    <div class="flex justify-between text-base text-green-600 font-medium">
                        <span>Discount @if($order->coupon_code)<span class="text-xs bg-green-100 text-green-800 px-2 py-0.5 rounded-full ml-1">{{ $order->coupon_code }}</span>@endif</span>
                        <span>-৳{{ number_format($order->discount_amount, 0) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between font-bold text-gray-900 dark:text-white text-xl pt-3 border-t border-gray-200 dark:border-gray-700 mt-2">
                        <span>Total Paid</span>
                        <span class="text-primary">৳{{ number_format($order->total_amount, 0) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Info -->
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6">
                <h3 class="font-bold text-gray-900 dark:text-white mb-5 flex items-center gap-2">
                    <i data-lucide="truck" class="w-5 h-5 text-gray-400"></i> Delivery Information
                </h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold mb-1">Recipient Name</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $order->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold mb-1">Phone Number</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $order->phone }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider font-semibold mb-1">Shipping Address</p>
                        <p class="font-medium text-gray-900 dark:text-white leading-relaxed">{{ $order->shipping_address }}</p>
                        @if($order->district)
                        <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ $order->district }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm p-6 flex items-start gap-4">
                <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                    <i data-lucide="help-circle" class="w-5 h-5"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-900 dark:text-white mb-1">Need Help?</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">If you have any issues with this order, please contact our support team.</p>
                    <a href="#" class="inline-block text-sm font-semibold text-primary hover:underline">Contact Support &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
