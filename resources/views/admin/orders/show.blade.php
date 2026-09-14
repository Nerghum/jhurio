@extends('layouts.admin')

@section('title', 'Order Details')

@section('header')
<div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.orders.index') }}" class="p-2.5 text-gray-500 hover:text-gray-900 dark:hover:text-white bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-700 rounded-xl shadow-sm transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <div class="flex items-center gap-3">
                <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white tracking-tight">Order #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</h2>
                @php $color = $statuses[$order->status]['color'] ?? 'gray'; @endphp
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-{{ $color }}-50 text-{{ $color }}-700 dark:bg-{{ $color }}-900/30 dark:text-{{ $color }}-400 border border-{{ $color }}-100 dark:border-{{ $color }}-800/50">
                    <span class="w-2 h-2 rounded-full bg-{{ $color }}-500 animate-pulse"></span>
                    {{ Str::headline($order->status) }}
                </span>
            </div>
            <p class="text-sm text-gray-500 font-medium mt-1 flex items-center gap-1.5">
                <i data-lucide="calendar" class="w-4 h-4"></i> {{ $order->created_at->format('F d, Y \a\t h:i A') }}
            </p>
        </div>
    </div>
    
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.orders.invoice', $order) }}" target="_blank" class="inline-flex items-center gap-2 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:text-gray-900 dark:hover:text-white border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 px-4 py-2.5 rounded-xl text-sm font-bold transition-colors shadow-sm">
            <i data-lucide="printer" class="w-4 h-4"></i>
            Print Invoice
        </a>
    </div>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Order Items -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-5 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white">Order Items</h3>
            </div>
            
            <div class="p-5">
                <table class="w-full text-sm text-left">
                    <thead class="text-gray-500 dark:text-gray-400 font-medium border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="pb-3">Product</th>
                            <th class="pb-3 text-center">Qty</th>
                            <th class="pb-3 text-right">Price</th>
                            <th class="pb-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @foreach($order->items as $item)
                        <tr>
                            <td class="py-4">
                                <div class="flex items-center gap-4">
                                    @if($item->product && $item->product->thumbnail)
                                        <img src="{{ $item->product->thumbnail->url }}" alt="{{ $item->product_name }}" class="w-12 h-12 rounded-lg border border-gray-200 dark:border-gray-600 object-cover">
                                    @else
                                        <div class="w-12 h-12 rounded-lg bg-gray-100 dark:bg-gray-700 flex items-center justify-center text-gray-400 border border-gray-200 dark:border-gray-600">
                                            <i data-lucide="image" class="w-5 h-5"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $item->product_name }}</p>
                                        @if($item->variation_name)
                                        <p class="text-xs text-gray-500 mt-0.5">Variation: {{ $item->variation_name }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 text-center font-medium">{{ $item->quantity }}</td>
                            <td class="py-4 text-right text-gray-500">{{ number_format($item->price, 0) }} Tk</td>
                            <td class="py-4 text-right font-bold text-gray-900 dark:text-white">{{ number_format($item->price * $item->quantity, 0) }} Tk</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Totals -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="p-5 flex flex-col items-end gap-3 w-full">
                <div class="flex justify-between w-64 text-sm text-gray-600 dark:text-gray-400">
                    <span>Subtotal</span>
                    <span>{{ number_format($order->total_amount + $order->discount_amount, 0) }} Tk</span>
                </div>
                
                @if($order->discount_amount > 0)
                <div class="flex justify-between w-64 text-sm text-green-600 font-medium">
                    <span>Discount ({{ $order->coupon_code }})</span>
                    <span>-{{ number_format($order->discount_amount, 0) }} Tk</span>
                </div>
                @endif
                
                <div class="flex justify-between w-64 text-sm text-gray-600 dark:text-gray-400">
                    <span>Shipping</span>
                    <span>0.00 Tk</span>
                </div>
                
                <div class="w-64 border-t border-gray-100 dark:border-gray-700 my-2"></div>
                
                <div class="flex justify-between w-64 text-lg font-extrabold text-gray-900 dark:text-white">
                    <span>Total</span>
                    <span>{{ number_format($order->total_amount, 0) }} Tk</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Update Status -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i data-lucide="refresh-cw" class="w-4 h-4 text-gray-400"></i> Update Status
            </h3>
            <form action="{{ route('admin.orders.update_status', $order) }}" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                    <div class="relative">
                        <select name="status" class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary appearance-none font-medium text-gray-700 dark:text-gray-200">
                            @foreach($statuses as $key => $status)
                                <option value="{{ $key }}" {{ $order->status === $key ? 'selected' : '' }}>{{ $status['label'] }}</option>
                            @endforeach
                        </select>
                        <i data-lucide="chevron-down" class="w-4 h-4 absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Note (Optional)</label>
                    <textarea name="note" rows="2" class="w-full rounded-lg border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 px-4 py-2 text-sm focus:ring-2 focus:ring-primary/50 focus:border-primary text-gray-700 dark:text-gray-200 placeholder-gray-400" placeholder="Add a note to this status update..."></textarea>
                </div>
                
                <button type="submit" class="w-full bg-gray-900 dark:bg-primary text-white hover:bg-gray-800 dark:hover:bg-primary/90 px-4 py-2.5 rounded-lg text-sm font-bold transition-colors flex justify-center items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i> Apply Update
                </button>
            </form>
        </div>

        <!-- Status History Log -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i data-lucide="clock" class="w-4 h-4 text-gray-400"></i> Change Log
            </h3>
            
            <div class="space-y-4 relative before:absolute before:inset-0 before:ml-2 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-gray-200 dark:before:via-gray-700 before:to-transparent pl-6 md:pl-0">
                @forelse($order->logs as $log)
                <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                    <!-- Icon -->
                    <div class="flex items-center justify-center w-4 h-4 rounded-full border-2 border-white dark:border-gray-800 bg-primary absolute left-0 md:left-1/2 -translate-x-1/2 md:translate-x-[-50%]"></div>
                    
                    <!-- Card -->
                    <div class="w-[calc(100%-1rem)] md:w-[calc(50%-1.5rem)] p-3 rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 shadow-sm">
                        <div class="flex items-center justify-between mb-1">
                            @php $color = $statuses[$log->status]['color'] ?? 'gray'; @endphp
                            <span class="text-xs font-bold text-{{ $color }}-600 dark:text-{{ $color }}-400">{{ Str::headline($log->status) }}</span>
                            <span class="text-[10px] font-medium text-gray-500">{{ $log->created_at->diffForHumans() }}</span>
                        </div>
                        @if($log->note)
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">{{ $log->note }}</p>
                        @endif
                    </div>
                </div>
                @empty
                <div class="text-sm text-gray-500 text-center py-4">No status changes yet.</div>
                @endforelse
            </div>
        </div>

        <!-- Customer Info -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <i data-lucide="user" class="w-4 h-4 text-gray-400"></i> Customer details
            </h3>
            
            <div class="space-y-3 text-sm">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold flex-shrink-0">
                        {{ substr($order->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $order->name }}</p>
                        @if($order->user)
                            <p class="text-xs text-primary font-medium mt-0.5">Registered Customer</p>
                        @else
                            <p class="text-xs text-gray-500 mt-0.5">Guest Customer</p>
                        @endif
                    </div>
                </div>
                
                <div class="pt-3 border-t border-gray-100 dark:border-gray-700 space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Contact</span>
                        <span class="font-medium text-gray-900 dark:text-white">{{ $order->phone }}</span>
                    </div>
                    
                    <div>
                        <span class="text-gray-500 block mb-1">Shipping Address</span>
                        <span class="font-medium text-gray-900 dark:text-white block">{{ $order->shipping_address }}</span>
                        @if($order->district)
                            <span class="font-medium text-gray-900 dark:text-white block">{{ $order->district }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Notes -->
        @if($order->notes)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-5">
            <h3 class="text-base font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                <i data-lucide="file-text" class="w-4 h-4 text-gray-400"></i> Order Notes
            </h3>
            <p class="text-sm text-gray-600 dark:text-gray-300 bg-yellow-50 dark:bg-yellow-900/10 p-3 rounded-lg border border-yellow-100 dark:border-yellow-800/50">
                {{ $order->notes }}
            </p>
        </div>
        @endif
    </div>
</div>
@endsection
