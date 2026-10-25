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

<div class="max-w-4xl mx-auto px-4 py-8" x-data="{
    cancelModalOpen: false,
    cancelReason: 'Changed my mind'
}">
    <!-- Alerts -->
    @if(session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 flex items-center gap-3">
            <i data-lucide="check-circle" class="w-5 h-5 flex-shrink-0"></i>
            <span class="text-sm font-semibold">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 flex items-center gap-3">
            <i data-lucide="alert-circle" class="w-5 h-5 flex-shrink-0"></i>
            <span class="text-sm font-semibold">{{ session('error') }}</span>
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-3 sm:gap-4">
            <a href="{{ route('account.orders') }}" class="w-10 h-10 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-500 hover:text-primary hover:bg-primary/10 transition-colors">
                <i data-lucide="arrow-left" class="w-5 h-5"></i>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Order #{{ $order->id }}</h1>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $s['class'] }}">
                        <i data-lucide="{{ $s['icon'] }}" class="w-3.5 h-3.5"></i>
                        {{ $s['label'] }}
                    </span>
                </div>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-0.5">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
            </div>
        </div>
        
        <div class="flex items-center gap-2">
            @if($order->status === 'pending')
            <button type="button" @click="cancelModalOpen = true" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-xl bg-rose-50 text-rose-600 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200 dark:border-rose-900/60 hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-colors shadow-xs">
                <i data-lucide="x-circle" class="w-4 h-4"></i>
                <span>Cancel Order</span>
            </button>
            @endif
            <a href="{{ route('account.orders') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                <i data-lucide="list" class="w-4 h-4"></i>
                <span>All Orders</span>
            </a>
        </div>
    </div>

    @if($order->status === 'cancelled')
    <div class="bg-red-50/80 dark:bg-red-950/30 border border-red-200/80 dark:border-red-900/50 rounded-2xl p-5 mb-6 flex items-start gap-3.5">
        <div class="w-10 h-10 rounded-xl bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400 flex items-center justify-center flex-shrink-0">
            <i data-lucide="alert-triangle" class="w-5 h-5"></i>
        </div>
        <div class="flex-1 min-w-0">
            <h4 class="font-bold text-sm text-red-900 dark:text-red-200">This order was cancelled</h4>
            <p class="text-xs text-red-700 dark:text-red-300 mt-0.5">This order is cancelled and will not be shipped. You can re-order anytime.</p>
            <div class="mt-3">
                <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-semibold transition-colors">
                    <i data-lucide="shopping-bag" class="w-3.5 h-3.5"></i>
                    <span>Continue Shopping</span>
                </a>
            </div>
        </div>
    </div>
    @endif

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

            @if($order->status === 'pending')
            <div class="bg-rose-50/60 dark:bg-rose-950/20 rounded-2xl border border-rose-200/80 dark:border-rose-900/50 p-6">
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 flex items-center justify-center">
                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                    </div>
                    <h4 class="font-bold text-sm text-rose-900 dark:text-rose-200">Cancel Order</h4>
                </div>
                <p class="text-xs text-rose-700/80 dark:text-rose-300/80 mb-4 leading-relaxed">
                    Need to cancel this order? Since it is still pending confirmation, you can cancel it right now with one click.
                </p>
                <button type="button" @click="cancelModalOpen = true" 
                        class="w-full py-2.5 px-4 rounded-xl bg-white dark:bg-gray-800 border border-rose-300 dark:border-rose-800 text-rose-600 dark:text-rose-400 font-bold text-xs hover:bg-rose-600 hover:text-white dark:hover:bg-rose-600 dark:hover:text-white transition-all shadow-xs flex items-center justify-center gap-1.5">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                    <span>Cancel This Order</span>
                </button>
            </div>
            @endif

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

    <!-- ── CANCEL ORDER MODAL (Above Mobile Bottom Nav) ── -->
    <div x-show="cancelModalOpen" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
         style="display: none;"
         @keydown.escape.window="cancelModalOpen = false">
        
        <div class="fixed inset-0 bg-black/60 backdrop-blur-xs" @click="cancelModalOpen = false"></div>

        <div class="relative w-full max-w-md bg-white dark:bg-gray-800 rounded-3xl shadow-2xl border border-gray-100 dark:border-gray-700 p-6 sm:p-7 z-10"
             x-transition:enter="transition ease-out duration-200 transform"
             x-transition:enter-start="opacity-0 scale-95 translate-y-3"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150 transform"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-3">

            <div class="text-center">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-950/40 text-rose-500 flex items-center justify-center mx-auto mb-3">
                    <i data-lucide="alert-triangle" class="w-7 h-7"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Cancel Order #{{ $order->id }}?</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 max-w-xs mx-auto">
                    Are you sure you want to cancel this order? Once cancelled, it cannot be undone. You can always place a new order anytime!
                </p>
            </div>

            <!-- Cancellation Reason -->
            <div class="mt-4">
                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5">Reason for Cancellation (Optional)</label>
                <select x-model="cancelReason" class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:ring-primary focus:border-primary">
                    <option value="Changed my mind">Changed my mind</option>
                    <option value="Ordered by mistake">Ordered by mistake</option>
                    <option value="Want to change delivery address">Want to change delivery address</option>
                    <option value="Found a better price elsewhere">Found a better price elsewhere</option>
                    <option value="Order taking too long">Order taking too long</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="flex items-center gap-3 mt-6">
                <button type="button" @click="cancelModalOpen = false" 
                        class="flex-1 py-3 px-4 rounded-xl border border-gray-200 dark:border-gray-700 text-xs sm:text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    Keep Order
                </button>
                <form action="{{ route('account.orders.cancel', $order) }}" method="POST" class="flex-1">
                    @csrf
                    <input type="hidden" name="reason" :value="cancelReason">
                    <button type="submit" 
                            class="w-full py-3 px-4 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs sm:text-sm font-semibold transition-colors shadow-sm shadow-rose-600/30 flex items-center justify-center gap-1.5">
                        <i data-lucide="x" class="w-3.5 h-3.5"></i>
                        <span>Yes, Cancel</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
