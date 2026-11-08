@extends('layouts.admin')

@section('title', 'Affiliate Management')

@section('header')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <i data-lucide="share-2" class="w-6 h-6 text-primary"></i>
            Affiliate Management
        </h2>
        <p class="text-sm text-gray-500 mt-1">Review registrations, manage approvals, and monitor revenue sharing</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('admin.affiliates.referrals') }}" class="px-4 py-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700 transition flex items-center gap-2">
            <i data-lucide="shopping-bag" class="w-4 h-4 text-emerald-500"></i>
            <span>Referral Sales</span>
        </a>
        <a href="{{ route('admin.affiliates.payouts') }}" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-xl hover:bg-secondary transition flex items-center gap-2 shadow-sm">
            <i data-lucide="credit-card" class="w-4 h-4"></i>
            <span>Payout Requests</span>
            @if(($stats['pending_payouts'] ?? 0) > 0)
                <span class="bg-white text-primary text-xs font-bold px-1.5 py-0.5 rounded-full">{{ $stats['pending_payouts'] }}</span>
            @endif
        </a>
    </div>
</div>
@endsection

@section('content')
<!-- Stats Cards -->
<div class="grid grid-cols-2 md:grid-cols-6 gap-4 mb-6">
    <a href="{{ route('admin.affiliates.index') }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl border {{ !request('status') ? 'border-primary ring-1 ring-primary' : 'border-gray-100 dark:border-gray-700' }} shadow-sm text-center transition">
        <p class="text-xs font-semibold text-gray-500 uppercase">All Affiliates</p>
        <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total'] }}</p>
    </a>
    <a href="{{ route('admin.affiliates.index', ['status' => 'pending']) }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl border {{ request('status') === 'pending' ? 'border-amber-500 ring-1 ring-amber-500' : 'border-gray-100 dark:border-gray-700' }} shadow-sm text-center transition">
        <p class="text-xs font-semibold text-amber-500 uppercase flex items-center justify-center gap-1">
            <span>Pending Review</span>
            @if($stats['pending'] > 0)
                <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
            @endif
        </p>
        <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending'] }}</p>
    </a>
    <a href="{{ route('admin.affiliates.index', ['status' => 'approved']) }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl border {{ request('status') === 'approved' ? 'border-emerald-500 ring-1 ring-emerald-500' : 'border-gray-100 dark:border-gray-700' }} shadow-sm text-center transition">
        <p class="text-xs font-semibold text-emerald-500 uppercase">Approved</p>
        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['approved'] }}</p>
    </a>
    <a href="{{ route('admin.affiliates.index', ['status' => 'rejected']) }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl border {{ request('status') === 'rejected' ? 'border-rose-500 ring-1 ring-rose-500' : 'border-gray-100 dark:border-gray-700' }} shadow-sm text-center transition">
        <p class="text-xs font-semibold text-rose-500 uppercase">Rejected</p>
        <p class="text-2xl font-bold text-rose-600 mt-1">{{ $stats['rejected'] }}</p>
    </a>
    <div class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm text-center">
        <p class="text-xs font-semibold text-indigo-500 uppercase">Total Paid Out</p>
        <p class="text-xl font-bold text-gray-900 dark:text-white mt-1">৳{{ number_format($stats['total_paid'], 0) }}</p>
    </div>
    <a href="{{ route('admin.affiliates.payouts', ['status' => 'pending']) }}" class="bg-white dark:bg-gray-800 p-4 rounded-xl border border-gray-100 dark:border-gray-700 shadow-sm text-center hover:border-primary transition">
        <p class="text-xs font-semibold text-purple-500 uppercase">Pending Payouts</p>
        <p class="text-2xl font-bold text-purple-600 mt-1">{{ $stats['pending_payouts'] }}</p>
    </a>
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

<!-- Affiliates Table Container -->
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden" x-data="{ rejectModalOpen: false, selectedAffiliateId: null, selectedAffiliateName: '' }">
    
    <!-- Filter and Search -->
    <div class="p-4 border-b border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50 flex flex-col sm:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-2">
            <span class="text-sm font-semibold text-gray-700 dark:text-gray-300">Affiliates</span>
            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 font-medium">{{ $affiliates->total() }}</span>
        </div>

        <form method="GET" action="{{ route('admin.affiliates.index') }}" class="flex-1 max-w-md relative w-full">
            @if(request('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search Name, Phone, Code, Email..." class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-700 px-4 py-2 text-sm focus:ring-2 focus:ring-primary focus:border-primary pl-10">
            <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-2.5 text-gray-400"></i>
        </form>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
            <thead class="bg-gray-50/75 dark:bg-gray-900/75 text-xs uppercase font-semibold text-gray-500 border-b border-gray-100 dark:border-gray-700">
                <tr>
                    <th class="px-6 py-4">Affiliate</th>
                    <th class="px-6 py-4">Referral Code</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4">Balances</th>
                    <th class="px-6 py-4">Payout Account</th>
                    <th class="px-6 py-4">Referrals / Sales</th>
                    <th class="px-6 py-4">Registered</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($affiliates as $affiliate)
                <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-bold text-base flex-shrink-0">
                                {{ strtoupper(substr($affiliate->name, 0, 1)) }}
                            </div>
                            <div>
                                <a href="{{ route('admin.affiliates.show', $affiliate) }}" class="font-semibold text-gray-900 dark:text-white hover:text-primary transition">
                                    {{ $affiliate->name }}
                                </a>
                                <p class="text-xs text-gray-500 font-mono mt-0.5">{{ $affiliate->phone }}</p>
                                @if($affiliate->email)
                                    <p class="text-xs text-gray-400">{{ $affiliate->email }}</p>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 font-mono text-xs font-semibold">
                            <i data-lucide="link" class="w-3 h-3 text-primary"></i>
                            {{ $affiliate->code }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        @if($affiliate->status === 'approved')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/50 dark:text-emerald-400">
                                <i data-lucide="check" class="w-3 h-3"></i> Approved
                            </span>
                        @elseif($affiliate->status === 'pending')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/50 dark:text-amber-400">
                                <i data-lucide="clock" class="w-3 h-3 animate-spin"></i> Pending Review
                            </span>
                        @elseif($affiliate->status === 'rejected')
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950/50 dark:text-rose-400">
                                <i data-lucide="x" class="w-3 h-3"></i> Rejected
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                {{ ucfirst($affiliate->status) }}
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="space-y-0.5">
                            <div class="text-xs font-bold text-emerald-600 dark:text-emerald-400">
                                Available: ৳{{ number_format($affiliate->balance, 2) }}
                            </div>
                            <div class="text-[11px] text-amber-500 font-medium">
                                Pending: ৳{{ number_format($affiliate->pending_balance, 2) }}
                            </div>
                            <div class="text-[10px] text-gray-400">
                                Lifetime: ৳{{ number_format($affiliate->lifetime_earnings, 2) }}
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        @if($affiliate->payout_method)
                            <div>
                                <span class="uppercase text-xs font-bold text-gray-800 dark:text-gray-200">{{ $affiliate->payout_method }}</span>
                                <p class="text-xs text-gray-500 font-mono mt-0.5">{{ $affiliate->payout_account ?? 'N/A' }}</p>
                            </div>
                        @else
                            <span class="text-xs text-gray-400 italic">Not configured</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-xs">
                            <span class="font-bold text-gray-900 dark:text-white">{{ $affiliate->referrals_count }}</span> referrals
                            <div class="text-[11px] text-gray-400 mt-0.5">
                                {{ $affiliate->payouts_count }} payouts
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-xs text-gray-500">
                        {{ $affiliate->created_at->format('M d, Y') }}
                        <div class="text-[10px] text-gray-400">{{ $affiliate->created_at->diffForHumans() }}</div>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if($affiliate->status === 'pending')
                                <form method="POST" action="{{ route('admin.affiliates.approve', $affiliate) }}" onsubmit="return confirm('Are you sure you want to approve {{ $affiliate->name }} as an affiliate?');">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold transition flex items-center gap-1 shadow-sm" title="Approve Affiliate">
                                        <i data-lucide="check" class="w-3.5 h-3.5"></i>
                                        <span>Approve</span>
                                    </button>
                                </form>
                                <button type="button" 
                                        @click="rejectModalOpen = true; selectedAffiliateId = {{ $affiliate->id }}; selectedAffiliateName = '{{ addslashes($affiliate->name) }}';" 
                                        class="px-3 py-1.5 bg-rose-50 hover:bg-rose-100 text-rose-700 dark:bg-rose-950/40 dark:text-rose-300 rounded-lg text-xs font-semibold transition flex items-center gap-1" title="Reject Affiliate">
                                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                    <span>Reject</span>
                                </button>
                            @elseif($affiliate->status === 'approved')
                                <button type="button" 
                                        @click="rejectModalOpen = true; selectedAffiliateId = {{ $affiliate->id }}; selectedAffiliateName = '{{ addslashes($affiliate->name) }}';" 
                                        class="p-1.5 text-gray-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition" title="Suspend / Reject">
                                    <i data-lucide="user-x" class="w-4 h-4"></i>
                                </button>
                            @elseif($affiliate->status === 'rejected')
                                <form method="POST" action="{{ route('admin.affiliates.approve', $affiliate) }}">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg text-xs font-semibold transition" title="Re-approve">
                                        Re-approve
                                    </button>
                                </form>
                            @endif

                            <a href="{{ route('admin.affiliates.show', $affiliate) }}" class="p-1.5 text-gray-400 hover:text-primary rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition" title="View Details">
                                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                        <i data-lucide="users" class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-3"></i>
                        <p class="text-base font-semibold text-gray-700 dark:text-gray-300">No affiliates found</p>
                        <p class="text-xs text-gray-400 mt-1">Registrations submitted from the mobile app or web footer will appear here.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($affiliates->hasPages())
    <div class="p-4 border-t border-gray-100 dark:border-gray-700">
        {{ $affiliates->links() }}
    </div>
    @endif

    <!-- Rejection Modal -->
    <div x-show="rejectModalOpen" 
         x-transition.opacity 
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" 
         style="display: none;" 
         x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 dark:border-gray-700" @click.away="rejectModalOpen = false">
            <div class="flex items-center gap-3 text-rose-600 mb-4">
                <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/50 flex items-center justify-center">
                    <i data-lucide="alert-triangle" class="w-5 h-5"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Reject Affiliate Application</h3>
                    <p class="text-xs text-gray-500" x-text="selectedAffiliateName"></p>
                </div>
            </div>

            <form :action="'/admin/affiliates/' + selectedAffiliateId + '/reject'" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold uppercase text-gray-600 dark:text-gray-400 mb-1.5">
                        Reason for Rejection (Optional)
                    </label>
                    <textarea name="reason" rows="3" placeholder="Provide a brief explanation for the applicant..." class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-3 text-sm focus:ring-2 focus:ring-primary focus:border-primary"></textarea>
                </div>

                <div class="flex justify-end gap-3">
                    <button type="button" @click="rejectModalOpen = false" class="px-4 py-2 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700">
                        Cancel
                    </button>
                    <button type="submit" class="px-4 py-2 bg-rose-600 text-white rounded-xl text-sm font-semibold hover:bg-rose-700 shadow-sm transition">
                        Confirm Rejection
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
