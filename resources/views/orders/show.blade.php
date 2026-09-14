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
            
            <div class="mt-8 text-center">
                <a href="{{ route('shop.index') }}" class="inline-flex justify-center items-center px-6 py-3 border border-transparent text-base font-medium rounded-xl text-white bg-dark dark:bg-gray-700 hover:bg-gray-800 dark:hover:bg-gray-600 transition-colors">
                    Continue Shopping
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
