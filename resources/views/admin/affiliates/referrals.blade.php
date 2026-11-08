@extends('layouts.admin')

@section('title', 'Affiliate Referral Sales')

@section('header')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i data-lucide="shopping-bag" class="w-6 h-6 text-emerald-500"></i>
            Affiliate Referral Sales
        </h2>
        <p class="text-sm text-gray-500 mt-1">Track all orders referred by affiliates and commission settlement</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.affiliates.index') }}" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl hover:bg-gray-50 transition flex items-center gap-2">
            <i data-lucide="users" class="w-4 h-4 text-primary"></i>
            <span>Affiliates List</span>
        </a>
        <a href="{{ route('admin.affiliates.payouts') }}" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-xl hover:bg-secondary transition flex items-center gap-2 shadow-sm">
            <i data-lucide="credit-card" class="w-4 h-4"></i>
            <span>Payout Requests</span>
        </a>
    </div>
</div>
@endsection

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <a href="{{ route('admin.affiliates.referrals') }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl border {{ !request('status') ? 'border-primary ring-1 ring-primary' : 'border-gray-100 dark:border-gray-700' }} shadow-sm text-center">
        <p class="text-xs font-semibold text-gray-500 uppercase">Total Referrals</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total_referrals'] }}</p>
    </a>
    <a href="{{ route('admin.affiliates.referrals', ['status' => 'pending']) }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl border {{ request('status') === 'pending' ? 'border-amber-500 ring-1 ring-amber-500' : 'border-gray-100 dark:border-gray-700' }} shadow-sm text-center">
        <p class="text-xs font-semibold text-amber-500 uppercase">Pending Delivery</p>
        <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending_referrals'] }}</p>
    </a>
    <a href="{{ route('admin.affiliates.referrals', ['status' => 'approved']) }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl border {{ request('status') === 'approved' ? 'border-emerald-500 ring-1 ring-emerald-500' : 'border-gray-100 dark:border-gray-700' }} shadow-sm text-center">
        <p class="text-xs font-semibold text-emerald-500 uppercase">Delivered & Earned</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['approved_referrals'] }}</p>
    </a>
    <a href="{{ route('admin.affiliates.referrals', ['status' => 'cancelled']) }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl border {{ request('status') === 'cancelled' ? 'border-rose-500 ring-1 ring-rose-500' : 'border-gray-100 dark:border-gray-700' }} shadow-sm text-center">
        <p class="text-xs font-semibold text-rose-500 uppercase">Cancelled / Returned</p>
        <p class="text-2xl font-bold text-rose-600 mt-1">{{ $stats['cancelled_referrals'] }}</p>
    </a>
    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm text-center">
        <p class="text-xs font-semibold text-indigo-500 uppercase">Total Commission</p>
        <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">৳{{ number_format($stats['total_commission_paid'], 0) }}</p>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    <!-- Filter and Search -->
    <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-2">
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Referral Orders</span>
            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium">{{ $referrals->total() }}</span>
        </div>

        <form method="GET" action="{{ route('admin.affiliates.referrals') }}" class="flex-1 max-w-md relative w-full">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Order ID, Affiliate Name or Code..." class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-primary pl-10">
            <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-2.5 text-gray-400"></i>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50/75 dark:bg-gray-900/75 text-xs uppercase font-semibold text-gray-500 border-b border-gray-100 dark:border-gray-700">
                <tr>
                    <th class="px-6 py-4">Order #</th>
                    <th class="px-6 py-4">Affiliate</th>
                    <th class="px-6 py-4">Order Total</th>
                    <th class="px-6 py-4">Order Status</th>
                    <th class="px-6 py-4">Commission Status</th>
                    <th class="px-6 py-4">Commission Earned</th>
                    <th class="px-6 py-4">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($referrals as $ref)
                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.orders.show', $ref->order_id) }}" class="font-bold text-primary hover:underline">
                            #{{ $ref->order_id }}
                        </a>
                        <p class="text-xs text-gray-500 mt-0.5">{{ $ref->order->name ?? 'Guest' }} ({{ $ref->order->phone ?? '' }})</p>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <div>
                                <a href="{{ route('admin.affiliates.show', $ref->affiliate_id) }}" class="font-semibold text-gray-900 dark:text-white hover:text-primary">
                                    {{ $ref->affiliate->name ?? 'Unknown' }}
                                </a>
                                <span class="block text-xs font-mono text-gray-400">{{ $ref->affiliate->code ?? '' }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                        ৳{{ number_format($ref->order_amount, 2) }}
                    </td>
                    <td class="px-6 py-4">
                        @php
                            $orderStatus = strtolower($ref->order->status ?? 'pending');
                        @endphp
                        @if(in_array($orderStatus, ['delivered', 'completed']))
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                <i data-lucide="check-circle" class="w-3 h-3"></i> {{ ucfirst($orderStatus) }}
                            </span>
                        @elseif(in_array($orderStatus, ['cancelled', 'returned']))
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                <i data-lucide="x-circle" class="w-3 h-3"></i> {{ ucfirst($orderStatus) }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                <i data-lucide="clock" class="w-3 h-3"></i> {{ ucfirst($orderStatus) }}
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($ref->status === 'approved')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                <i data-lucide="check" class="w-3 h-3"></i> Earned
                            </span>
                        @elseif($ref->status === 'pending')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                <i data-lucide="clock" class="w-3 h-3"></i> Pending Delivery
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                <i data-lucide="x" class="w-3 h-3"></i> Cancelled
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-base font-extrabold text-emerald-600 dark:text-emerald-400">
                            ৳{{ number_format($ref->commission_amount, 2) }}
                        </span>
                        @if(!empty($ref->items_breakdown))
                            <span class="block text-[11px] text-gray-400 mt-0.5">
                                {{ count($ref->items_breakdown) }} item(s) calculated
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-500">
                        {{ $ref->created_at->format('M d, Y h:i A') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                        <i data-lucide="shopping-bag" class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3"></i>
                        <p class="text-base font-semibold text-gray-700 dark:text-gray-300">No referral sales recorded yet</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($referrals->hasPages())
    <div class="p-4 border-t border-gray-100 dark:border-gray-700">
        {{ $referrals->links() }}
    </div>
    @endif
</div>
@endsection
