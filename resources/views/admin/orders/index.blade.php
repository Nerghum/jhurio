@extends('layouts.admin')

@section('title', 'Manage Orders')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            Orders
        </h2>
        <p class="text-sm text-gray-500 mt-1">Manage all customer orders</p>
    </div>
</div>
@endsection

@section('content')
<!-- Stats -->
<div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
    <a href="{{ route('admin.orders.index') }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm hover:border-primary transition-colors text-center">
        <p class="text-xs font-semibold text-gray-500 uppercase">All</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ collect($counts)->sum() }}</p>
    </a>
    @foreach($statuses as $key => $status)
    <a href="{{ route('admin.orders.index', ['status' => $key]) }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl border {{ request('status') === $key ? 'border-'.$status['color'].'-500 ring-1 ring-'.$status['color'].'-500' : 'border-gray-100 dark:border-gray-700 hover:border-'.$status['color'].'-300' }} shadow-sm transition-colors text-center">
        <p class="text-xs font-semibold text-{{ $status['color'] }}-500 uppercase">{{ $status['label'] }}</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $counts[$key] ?? 0 }}</p>
    </a>
    @endforeach
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    
    <!-- Filter Bar -->
    <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 flex justify-between items-center gap-4">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="flex-1 max-w-md relative">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search ID, Name, Phone..." class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-primary pl-10">
            <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-2.5 text-gray-400"></i>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-medium border-b border-gray-100 dark:border-gray-700">
                <tr>
                    <th class="px-6 py-4">Order ID</th>
                    <th class="px-6 py-4">Date</th>
                    <th class="px-6 py-4">Customer</th>
                    <th class="px-6 py-4">Amount</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($orders as $order)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                        #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                    </td>
                    <td class="px-6 py-4 text-gray-500">
                        {{ $order->created_at->format('d M Y, h:i A') }}
                    </td>
                    <td class="px-6 py-4">
                        <div class="font-medium text-gray-900 dark:text-white">{{ $order->name }}</div>
                        <div class="text-xs text-gray-500">{{ $order->phone }}</div>
                    </td>
                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                        {{ number_format($order->total_amount, 0) }} Tk
                    </td>
                    <td class="px-6 py-4">
                        @php $color = $statuses[$order->status]['color'] ?? 'gray'; @endphp
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-{{ $color }}-50 text-{{ $color }}-700 dark:bg-{{ $color }}-900/30 dark:text-{{ $color }}-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-{{ $color }}-500"></span>
                            {{ Str::headline($order->status) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('admin.orders.show', $order) }}" class="inline-flex items-center gap-1 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 rounded-lg text-xs font-medium transition-colors">
                            <i data-lucide="eye" class="w-3.5 h-3.5"></i> View
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                        <i data-lucide="package-x" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
                        <p>No orders found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($orders->hasPages())
    <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
        {{ $orders->links() }}
    </div>
    @endif
</div>
@endsection
