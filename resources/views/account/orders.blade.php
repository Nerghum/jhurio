@extends('layouts.app')

@section('title', 'My Orders')

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
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col md:flex-row gap-8">
    
    <!-- Sidebar -->
    <div class="w-full md:w-64 flex-shrink-0">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden sticky top-24">
            <div class="p-5 border-b border-gray-50 dark:border-gray-700 text-center">
                <div class="w-16 h-16 rounded-full bg-primary/10 text-primary flex items-center justify-center text-2xl font-bold mx-auto mb-3">
                    {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->phone, 0, 1)) }}
                </div>
                <h3 class="font-bold text-gray-900 dark:text-white truncate">{{ auth()->user()->name !== 'Customer' ? auth()->user()->name : auth()->user()->phone }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ auth()->user()->phone }}</p>
            </div>
            <div class="p-2">
                <a href="{{ route('account.orders') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors bg-primary/5 text-primary">
                    <i data-lucide="package" class="w-5 h-5"></i> My Orders
                </a>
                <a href="{{ route('account.addresses') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <i data-lucide="map-pin" class="w-5 h-5"></i> Saved Addresses
                </a>
                <form action="{{ route('account.logout') }}" method="POST" class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        <i data-lucide="log-out" class="w-5 h-5"></i> Sign Out
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">My Orders</h1>

        @forelse($orders as $order)
        @php $s = $statusConfig[$order->status] ?? $statusConfig['pending']; @endphp
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm mb-4 overflow-hidden hover:shadow-md transition-shadow">
            <!-- Order Header -->
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-50 dark:border-gray-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
                        <i data-lucide="package" class="w-5 h-5 text-primary"></i>
                    </div>
                    <div>
                        <p class="font-bold text-gray-900 dark:text-white text-sm">Order #{{ $order->id }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $order->created_at->format('d M Y') }}</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold {{ $s['class'] }}">
                    <i data-lucide="{{ $s['icon'] }}" class="w-3.5 h-3.5"></i>
                    {{ $s['label'] }}
                </span>
            </div>

            <!-- Status Progress Bar -->
            @php
            $flow = ['pending', 'processing', 'shipped', 'delivered', 'completed'];
            $idx = array_search($order->status, $flow);
            $pct = ($idx !== false && $order->status !== 'cancelled') ? round(($idx / (count($flow)-1)) * 100) : 0;
            @endphp
            @if($order->status !== 'cancelled')
            <div class="px-5 py-4 bg-gray-50/50 dark:bg-gray-900/20 border-b border-gray-50 dark:border-gray-700">
                <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mb-2">
                    @foreach($flow as $s2)
                    <span class="{{ array_search($s2, $flow) <= $idx ? 'text-primary font-semibold' : '' }}">{{ ucfirst($s2) }}</span>
                    @endforeach
                </div>
                <div class="h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                    <div class="h-full bg-primary rounded-full transition-all duration-700" style="width: {{ $pct }}%"></div>
                </div>
            </div>
            @endif

            <!-- Items Preview -->
            <div class="px-5 py-4">
                <div class="flex items-center gap-3 flex-wrap">
                    @foreach($order->items->take(4) as $item)
                    <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-700 px-3 py-1.5 rounded-lg border border-gray-100 dark:border-gray-600">
                        @if($item->product && $item->product->thumbnail)
                        <img src="{{ $item->product->thumbnail->url }}" class="w-6 h-6 rounded object-cover">
                        @endif
                        <span class="text-xs font-medium text-gray-700 dark:text-gray-300">
                            {{ Str::limit($item->product_name, 20) }} <span class="text-gray-400">×{{ $item->quantity }}</span>
                        </span>
                    </div>
                    @endforeach
                    @if($order->items->count() > 4)
                    <span class="text-xs font-medium text-gray-400 bg-gray-50 dark:bg-gray-800 px-3 py-1.5 rounded-lg border border-gray-100 dark:border-gray-700">
                        +{{ $order->items->count() - 4 }} more
                    </span>
                    @endif
                </div>
            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between px-5 py-4 border-t border-gray-50 dark:border-gray-700">
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Amount</p>
                    <span class="font-bold text-gray-900 dark:text-white text-lg">৳{{ number_format($order->total_amount, 0) }}</span>
                </div>
                <a href="{{ route('account.order_detail', $order) }}"
                   class="bg-gray-100 hover:bg-primary hover:text-white dark:bg-gray-700 dark:hover:bg-primary text-gray-700 dark:text-gray-200 px-4 py-2 rounded-xl text-sm font-semibold transition-colors flex items-center gap-2">
                    View Details <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </a>
            </div>
        </div>
        @empty
        <div class="text-center py-20 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="w-24 h-24 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center mx-auto mb-5">
                <i data-lucide="package-open" class="w-12 h-12 text-gray-400"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">No orders yet</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-sm mx-auto">Looks like you haven't made your first purchase yet. Discover our premium collection!</p>
            <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 bg-primary text-white px-8 py-3.5 rounded-xl font-bold hover:bg-primary/90 transition-all hover:shadow-lg hover:shadow-primary/30 hover:-translate-y-0.5">
                <i data-lucide="shopping-bag" class="w-5 h-5"></i> Start Shopping
            </a>
        </div>
        @endforelse

        @if($orders->hasPages())
        <div class="mt-6">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
