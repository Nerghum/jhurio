@extends('layouts.admin')

@section('title', 'Affiliate Payout Requests')

@section('header')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i data-lucide="credit-card" class="w-6 h-6 text-primary"></i>
            Affiliate Payout Requests
        </h2>
        <p class="text-sm text-gray-500 mt-1">Review withdrawal requests, make payments, and approve with Transaction IDs</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.affiliates.index') }}" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl hover:bg-gray-50 transition flex items-center gap-2">
            <i data-lucide="users" class="w-4 h-4 text-primary"></i>
            <span>Affiliates</span>
        </a>
        <a href="{{ route('admin.affiliates.referrals') }}" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl hover:bg-gray-50 transition flex items-center gap-2">
            <i data-lucide="shopping-bag" class="w-4 h-4 text-emerald-500"></i>
            <span>Referrals</span>
        </a>
    </div>
</div>
@endsection

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <a href="{{ route('admin.affiliates.payouts') }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl border {{ !request('status') ? 'border-primary ring-1 ring-primary' : 'border-gray-100 dark:border-gray-700' }} shadow-sm text-center">
        <p class="text-xs font-semibold text-gray-500 uppercase">All Requests</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total_payouts'] }}</p>
    </a>
    <a href="{{ route('admin.affiliates.payouts', ['status' => 'pending']) }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl border {{ request('status') === 'pending' ? 'border-amber-500 ring-1 ring-amber-500' : 'border-gray-100 dark:border-gray-700' }} shadow-sm text-center">
        <p class="text-xs font-semibold text-amber-500 uppercase flex items-center justify-center gap-1">
            <span>Pending Payout</span>
            @if($stats['pending_count'] > 0)
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
            @endif
        </p>
        <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending_count'] }}</p>
    </a>
    <a href="{{ route('admin.affiliates.payouts', ['status' => 'paid']) }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl border {{ request('status') === 'paid' ? 'border-emerald-500 ring-1 ring-emerald-500' : 'border-gray-100 dark:border-gray-700' }} shadow-sm text-center">
        <p class="text-xs font-semibold text-emerald-500 uppercase">Paid Out</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['paid_count'] }}</p>
    </a>
    <a href="{{ route('admin.affiliates.payouts', ['status' => 'rejected']) }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl border {{ request('status') === 'rejected' ? 'border-rose-500 ring-1 ring-rose-500' : 'border-gray-100 dark:border-gray-700' }} shadow-sm text-center">
        <p class="text-xs font-semibold text-rose-500 uppercase">Rejected</p>
        <p class="text-2xl font-bold text-rose-600 mt-1">{{ $stats['rejected_count'] }}</p>
    </a>
    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm text-center">
        <p class="text-xs font-semibold text-indigo-500 uppercase">Total Settled</p>
        <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">৳{{ number_format($stats['total_paid_amount'], 0) }}</p>
    </div>
</div>

@if(session('success'))
<div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 dark:bg-emerald-950/40 dark:border-emerald-800 dark:text-emerald-300 flex items-center gap-3">
    <i data-lucide="check-circle" class="w-5 h-5 text-emerald-600"></i>
    <span class="text-sm font-medium">{{ session('success') }}</span>
</div>
@endif

@if(session('error'))
<div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 dark:bg-rose-950/40 dark:border-rose-800 dark:text-rose-300 flex items-center gap-3">
    <i data-lucide="alert-circle" class="w-5 h-5 text-rose-600"></i>
    <span class="text-sm font-medium">{{ session('error') }}</span>
</div>
@endif

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden" 
     x-data="{ 
        approveModalOpen: false, 
        rejectModalOpen: false, 
        payoutId: null, 
        affiliateName: '', 
        amount: '', 
        method: '', 
        account: '',
        transactionId: '' 
     }">
    
    <!-- Filter and Search -->
    <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-2">
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Payout Requests</span>
            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium">{{ $payouts->total() }}</span>
        </div>

        <form method="GET" action="{{ route('admin.affiliates.payouts') }}" class="flex-1 max-w-md relative w-full">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Transaction ID, Account, Affiliate..." class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-primary pl-10">
            <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-2.5 text-gray-400"></i>
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50/75 dark:bg-gray-900/75 text-xs uppercase font-semibold text-gray-500 border-b border-gray-100 dark:border-gray-700">
                <tr>
                    <th class="px-6 py-4">ID</th>
                    <th class="px-6 py-4">Affiliate</th>
                    <th class="px-6 py-4">Requested Amount</th>
                    <th class="px-6 py-4">Payment Method & Account</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Transaction ID</th>
                    <th class="px-6 py-4">Requested Date</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($payouts as $payout)
                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-6 py-4 font-mono font-bold text-xs">#{{ $payout->id }}</td>
                    <td class="px-6 py-4">
                        <a href="{{ route('admin.affiliates.show', $payout->affiliate_id) }}" class="font-bold text-gray-900 dark:text-white hover:text-primary">
                            {{ $payout->affiliate->name ?? 'Unknown' }}
                        </a>
                        <p class="text-xs text-gray-400 font-mono">{{ $payout->affiliate->phone ?? '' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        <span class="text-base font-extrabold text-emerald-600 dark:text-emerald-400">
                            ৳{{ number_format($payout->amount, 2) }}
                        </span>
                        <div class="text-[11px] text-gray-400">
                            Current balance: ৳{{ number_format($payout->affiliate->balance ?? 0, 2) }}
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-gray-700 uppercase font-bold text-xs text-gray-800 dark:text-gray-200">
                                {{ $payout->payout_method }}
                            </span>
                            <span class="font-mono text-xs font-semibold text-gray-700 dark:text-gray-300">
                                {{ $payout->account_details }}
                            </span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($payout->status === 'paid')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                <i data-lucide="check-circle" class="w-3 h-3"></i> Paid
                            </span>
                        @elseif($payout->status === 'pending')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                <i data-lucide="clock" class="w-3 h-3 animate-spin"></i> Pending Payment
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                <i data-lucide="x-circle" class="w-3 h-3"></i> Rejected
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($payout->transaction_id)
                            <span class="inline-flex items-center gap-1 font-mono text-xs font-bold text-primary bg-primary/10 px-2 py-1 rounded-md">
                                <i data-lucide="check" class="w-3 h-3"></i>
                                {{ $payout->transaction_id }}
                            </span>
                        @else
                            <span class="text-xs text-gray-400 italic">Not set</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-500">
                        {{ $payout->created_at->format('M d, Y h:i A') }}
                    </td>
                    <td class="px-6 py-4 text-right">
                        @if($payout->status === 'pending')
                            <div class="flex items-center justify-end gap-2">
                                <button type="button" 
                                        @click="
                                            approveModalOpen = true; 
                                            payoutId = {{ $payout->id }}; 
                                            affiliateName = '{{ addslashes($payout->affiliate->name ?? '') }}';
                                            amount = '{{ $payout->amount }}';
                                            method = '{{ strtoupper($payout->payout_method) }}';
                                            account = '{{ $payout->account_details }}';
                                            transactionId = '';
                                        " 
                                        class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1 shadow-sm">
                                    <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                    <span>Approve & Pay</span>
                                </button>
                                <button type="button" 
                                        @click="
                                            rejectModalOpen = true; 
                                            payoutId = {{ $payout->id }}; 
                                            affiliateName = '{{ addslashes($payout->affiliate->name ?? '') }}';
                                            amount = '{{ $payout->amount }}';
                                        " 
                                        class="px-2.5 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-lg text-xs font-semibold transition" title="Reject request">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                </button>
                            </div>
                        @else
                            <span class="text-xs text-gray-400 font-medium">Processed</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                        <i data-lucide="credit-card" class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3"></i>
                        <p class="text-base font-semibold text-gray-700 dark:text-gray-300">No payout requests found</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($payouts->hasPages())
    <div class="p-4 border-t border-gray-100 dark:border-gray-700">
        {{ $payouts->links() }}
    </div>
    @endif

    <!-- Approve Payout with Transaction ID Modal -->
    <div x-show="approveModalOpen" 
         x-transition.opacity 
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" 
         style="display: none;" 
         x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700" @click.away="approveModalOpen = false">
            <div class="flex items-center gap-3 text-emerald-600 mb-4">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 flex items-center justify-center">
                    <i data-lucide="check-circle" class="w-6 h-6"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Approve Affiliate Payout</h3>
                    <p class="text-xs text-gray-500">Confirm payment and attach transaction ID</p>
                </div>
            </div>

            <!-- Payment Summary Card -->
            <div class="p-4 rounded-xl bg-gray-50 dark:bg-gray-900 border border-gray-200 dark:border-gray-700 mb-5 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Affiliate:</span>
                    <span class="font-bold text-gray-900 dark:text-white" x-text="affiliateName"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Amount to Send:</span>
                    <span class="text-lg font-extrabold text-emerald-600 dark:text-emerald-400">৳<span x-text="amount"></span></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Transfer Via:</span>
                    <span class="font-bold uppercase text-primary" x-text="method"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Account / Phone:</span>
                    <span class="font-mono font-bold text-gray-900 dark:text-white" x-text="account"></span>
                </div>
            </div>

            <form :action="'/admin/affiliates/payouts/' + payoutId + '/approve'" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1.5">
                        Transaction ID (TrxID) <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" 
                           name="transaction_id" 
                           x-model="transactionId" 
                           required 
                           placeholder="e.g. 9J28DA712 or BKASH-TrxID" 
                           class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 p-3 text-sm font-mono font-bold focus:ring-2 focus:ring-primary focus:border-primary">
                    <p class="text-[11px] text-gray-400 mt-1">This will be shared with the affiliate on their dashboard.</p>
                </div>

                <div class="mb-5">
                    <label class="block text-xs font-bold uppercase text-gray-700 dark:text-gray-300 mb-1.5">
                        Admin Note (Optional)
                    </label>
                    <input type="text" 
                           name="admin_notes" 
                           placeholder="e.g. Sent via bKash Personal at 11:30 AM" 
                           class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 p-3 text-sm focus:ring-2 focus:ring-primary focus:border-primary">
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="approveModalOpen = false" class="px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50">
                        Cancel
                    </button>
                    <button type="submit" 
                            :disabled="!transactionId.trim()" 
                            class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-bold hover:bg-emerald-700 shadow-md transition disabled:opacity-50">
                        Confirm & Mark as Paid
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Reject Modal -->
    <div x-show="rejectModalOpen" 
         x-transition.opacity 
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" 
         style="display: none;" 
         x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700" @click.away="rejectModalOpen = false">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Reject Payout Request</h3>
            <p class="text-xs text-gray-500 mb-4">Provide a reason so the affiliate knows why this payout was rejected (e.g. incorrect account number).</p>

            <form :action="'/admin/affiliates/payouts/' + payoutId + '/reject'" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-400 mb-1.5">
                        Reason <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="admin_notes" rows="3" required placeholder="e.g. Invalid bKash wallet number provided. Please re-check." class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-3 text-sm focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 border border-gray-200 rounded-xl text-sm font-medium">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 text-white rounded-xl text-sm font-semibold hover:bg-rose-700">Reject Payout</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
