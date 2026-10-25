@extends('layouts.app')

@section('title', 'Order Confirmation')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="bg-green-500 px-6 py-8 text-center">
            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-xl bg-white mb-4">
                <i data-lucide="check" class="h-8 w-8 text-green-500"></i>
            </div>
            <h2 class="text-2xl font-bold text-white">Order Confirmed!</h2>
            <p class="text-green-100 mt-2">Thank you for your purchase, {{ $order->name }}.</p>
        </div>
        
        <div class="p-6 sm:p-8">
            <div class="flex justify-between items-center border-b border-gray-200 dark:border-gray-700 pb-6 mb-6">
                <div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Order Number</p>
                    <p class="font-bold text-gray-900 dark:text-white">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500 dark:text-gray-400">Date</p>
                    <p class="font-medium text-gray-900 dark:text-white">{{ $order->created_at->format('M d, Y') }}</p>
                </div>
            </div>

            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Order Items</h3>
                <ul role="list" class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($order->items as $item)
                        <li class="py-4 flex justify-between">
                            <div class="flex flex-col">
                                <span class="font-medium text-gray-900 dark:text-white">{{ $item->product_name }}</span>
                                @if($item->variation_name)
                                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ $item->variation_name }}</span>
                                @endif
                                <span class="text-sm text-gray-500 dark:text-gray-400">Qty: {{ $item->quantity }}</span>
                            </div>
                            <span class="font-medium text-gray-900 dark:text-white">{{ number_format($item->price * $item->quantity, 0) }} BDT</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <div class="flex justify-between text-lg font-bold">
                    <p class="text-gray-900 dark:text-white">Total Amount</p>
                    <p class="text-accent">{{ number_format($order->total_amount, 0) }} BDT</p>
                </div>
            </div>

            <div class="mt-8 bg-gray-50 dark:bg-gray-900 rounded-xl p-6 border border-gray-100 dark:border-gray-800">
                <h3 class="text-md font-semibold text-gray-900 dark:text-white mb-4">Shipping Details</h3>
                <p class="text-gray-600 dark:text-gray-300"><span class="font-medium">Name:</span> {{ $order->name }}</p>
                <p class="text-gray-600 dark:text-gray-300"><span class="font-medium">Phone:</span> {{ $order->phone }}</p>
                <p class="text-gray-600 dark:text-gray-300 mt-2"><span class="font-medium">Address:</span><br> {{ nl2br(e($order->shipping_address)) }}</p>
            </div>
            
            <!-- Auto-login & Account Notification -->
            <div class="mt-8 bg-gradient-to-r from-primary/10 via-secondary/10 to-primary/5 rounded-2xl p-5 border border-primary/20 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3 text-center sm:text-left">
                    <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center flex-shrink-0 shadow-sm">
                        <i data-lucide="user-check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white text-sm">Account Created & Logged In!</h4>
                        <p class="text-xs text-gray-600 dark:text-gray-300">You are automatically logged in on this device. Track your order status, manage wishlist, or cancel if pending anytime.</p>
                    </div>
                </div>
                <a href="{{ route('account.orders') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary text-white text-xs font-bold rounded-xl hover:bg-secondary transition shadow-sm whitespace-nowrap w-full sm:w-auto">
                    <i data-lucide="package" class="w-4 h-4"></i> Track in My Account
                </a>
            </div>
            
            <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                <a href="{{ route('account.orders') }}" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-6 py-3 rounded-xl text-sm font-bold text-white bg-primary hover:bg-secondary transition-all shadow-sm">
                    <i data-lucide="layout-dashboard" class="w-4 h-4"></i> View in Account Hub
                </a>
                <a href="{{ route('shop.index') }}" class="w-full sm:w-auto inline-flex justify-center items-center gap-2 px-6 py-3 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <i data-lucide="shopping-bag" class="w-4 h-4"></i> Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Fire frontend Purchase event
    if (typeof window.trackEvent === 'function') {
        window.trackEvent('Purchase', {
            content_ids: {!! json_encode($order->items->pluck('product_id')->toArray()) !!},
            content_type: 'product',
            value: {{ $order->total_amount }},
            currency: 'BDT',
            num_items: {{ $order->items->sum('quantity') }},
            transaction_id: '{{ $order->id }}'
        });
    }
</script>
@endpush
