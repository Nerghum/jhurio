@extends('layouts.admin')

@section('title', 'Affiliate Profile - ' . $affiliate->name)

@section('header')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.affiliates.index') }}" class="p-2 rounded-xl bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-500 hover:text-gray-900 transition">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                {{ $affiliate->name }}
                @if($affiliate->status === 'approved')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Approved</span>
                @elseif($affiliate->status === 'pending')
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Pending Review</span>
                @else
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">{{ ucfirst($affiliate->status) }}</span>
                @endif
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">Referral Code: <span class="font-mono font-bold text-primary">{{ $affiliate->code }}</span></p>
        </div>
    </div>
    <div class="flex items-center gap-2">
        @if($affiliate->status === 'pending')
            <form method="POST" action="{{ route('admin.affiliates.approve', $affiliate) }}">
                @csrf
                <button type="submit" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-semibold transition flex items-center gap-2 shadow-sm">
                    <i data-lucide="check" class="w-4 h-4"></i> Approve Application
                </button>
            </form>
        @endif
    </div>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <!-- Balance Card -->
    <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-2xl p-6 text-white shadow-lg relative overflow-hidden">
        <div class="relative z-10">
            <p class="text-xs font-semibold text-emerald-100 uppercase tracking-wider">Available For Payout</p>
            <h3 class="text-3xl font-extrabold mt-1">৳{{ number_format($affiliate->balance, 2) }}</h3>
            
            <div class="mt-6 pt-4 border-t border-emerald-500/50 flex justify-between items-center text-xs">
                <div>
                    <span class="text-emerald-200 block">Pending Delivery</span>
                    <span class="font-bold text-sm">৳{{ number_format($affiliate->pending_balance, 2) }}</span>
                </div>
                <div class="text-right">
                    <span class="text-emerald-200 block">Lifetime Earned</span>
                    <span class="font-bold text-sm">৳{{ number_format($affiliate->lifetime_earnings, 2) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Payout Information -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-4 flex items-center gap-2">
            <i data-lucide="credit-card" class="w-4 h-4 text-primary"></i> Payout Details
        </h4>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-700">
                <dt class="text-gray-500">Method</dt>
                <dd class="font-bold uppercase text-gray-900 dark:text-white">{{ $affiliate->payout_method ?? 'None' }}</dd>
            </div>
            <div class="flex justify-between py-1 border-b border-gray-100 dark:border-gray-700">
                <dt class="text-gray-500">Account / Phone</dt>
                <dd class="font-mono font-semibold text-gray-900 dark:text-white">{{ $affiliate->payout_account ?? 'None' }}</dd>
            </div>
            <div class="flex justify-between py-1">
                <dt class="text-gray-500">Approved Date</dt>
                <dd class="text-gray-900 dark:text-white">{{ $affiliate->approved_at ? $affiliate->approved_at->format('M d, Y') : 'Not yet' }}</dd>
            </div>
        </dl>
    </div>

    <!-- Applicant Notes -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-2 flex items-center gap-2">
            <i data-lucide="file-text" class="w-4 h-4 text-primary"></i> Applicant Notes
        </h4>
        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed bg-gray-50 dark:bg-gray-900 p-3 rounded-xl min-h-[90px]">
            {{ $affiliate->notes ?: 'No notes provided at registration.' }}
        </p>
        @if($affiliate->rejection_reason)
            <div class="mt-3 p-3 bg-rose-50 text-rose-800 rounded-xl text-xs">
                <span class="font-bold">Rejection reason:</span> {{ $affiliate->rejection_reason }}
            </div>
        @endif
    </div>
</div>

<!-- Referrals and Payouts Tabs -->
<div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden" x-data="{ tab: 'referrals' }">
    <div class="border-b border-gray-100 dark:border-gray-700 px-6 pt-4 flex gap-6">
        <button @click="tab = 'referrals'" :class="tab === 'referrals' ? 'border-primary text-primary font-bold' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-3 border-b-2 text-sm flex items-center gap-2 transition">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i>
            <span>Referred Orders ({{ $affiliate->referrals->count() }})</span>
        </button>
        <button @click="tab = 'payouts'" :class="tab === 'payouts' ? 'border-primary text-primary font-bold' : 'border-transparent text-gray-500 hover:text-gray-700'" class="pb-3 border-b-2 text-sm flex items-center gap-2 transition">
            <i data-lucide="wallet" class="w-4 h-4"></i>
            <span>Payout History ({{ $affiliate->payouts->count() }})</span>
        </button>
    </div>

    <!-- Referrals Table -->
    <div x-show="tab === 'referrals'" class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50/75 dark:bg-gray-900/75 text-xs uppercase font-semibold text-gray-500">
                <tr>
                    <th class="px-6 py-3">Order</th>
                    <th class="px-6 py-3">Order Total</th>
                    <th class="px-6 py-3">Delivery Status</th>
                    <th class="px-6 py-3">Commission Status</th>
                    <th class="px-6 py-3">Commission</th>
                    <th class="px-6 py-3">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($affiliate->referrals as $ref)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.orders.show', $ref->order_id) }}" class="font-bold text-primary hover:underline">
                            #{{ $ref->order_id }}
                        </a>
                        <p class="text-xs text-gray-500">{{ $ref->order->name ?? 'Guest' }} ({{ $ref->order->phone ?? '' }})</p>
                    </td>
                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                        ৳{{ number_format($ref->order_amount, 2) }}
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                            {{ ucfirst($ref->order->status ?? 'unknown') }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($ref->status === 'approved')
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Earned</span>
                        @elseif($ref->status === 'pending')
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Pending Delivery</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">Cancelled</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 font-extrabold text-emerald-600 dark:text-emerald-400">
                        ৳{{ number_format($ref->commission_amount, 2) }}
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-500">
                        {{ $ref->created_at->format('M d, Y h:i A') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500 text-xs">No orders referred yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Payouts Table -->
    <div x-show="tab === 'payouts'" class="overflow-x-auto" style="display: none;">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50/75 dark:bg-gray-900/75 text-xs uppercase font-semibold text-gray-500">
                <tr>
                    <th class="px-6 py-3">Payout ID</th>
                    <th class="px-6 py-3">Amount</th>
                    <th class="px-6 py-3">Method & Details</th>
                    <th class="px-6 py-3">Status</th>
                    <th class="px-6 py-3">Transaction ID</th>
                    <th class="px-6 py-3">Requested At</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($affiliate->payouts as $payout)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-6 py-4 font-mono font-bold text-xs">#{{ $payout->id }}</td>
                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">৳{{ number_format($payout->amount, 2) }}</td>
                    <td class="px-6 py-4">
                        <span class="uppercase text-xs font-bold">{{ $payout->payout_method }}</span>
                        <p class="text-xs text-gray-500 font-mono">{{ $payout->account_details }}</p>
                    </td>
                    <td class="px-6 py-4">
                        @if($payout->status === 'paid')
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Paid</span>
                        @elseif($payout->status === 'pending')
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">Pending</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">Rejected</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 font-mono text-xs text-primary font-bold">
                        {{ $payout->transaction_id ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-500">
                        {{ $payout->created_at->format('M d, Y') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-gray-500 text-xs">No payout requests submitted yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
