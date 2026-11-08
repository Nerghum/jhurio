@extends('layouts.app')

@section('title', 'Affiliate Partner Portal')

@section('content')
<div class="py-10 bg-gray-50 dark:bg-darker min-h-screen" x-data="{ payoutModal: false, activeTab: 'links' }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Partner Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 bg-white dark:bg-gray-800 p-6 rounded-3xl border border-gray-100 dark:border-gray-700 shadow-sm mb-8">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-600 text-white flex items-center justify-center font-bold text-xl shadow-md">
                    {{ strtoupper(substr($affiliate->name, 0, 1)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-xl sm:text-2xl font-extrabold text-gray-900 dark:text-white">
                            {{ $affiliate->name }}
                        </h1>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400">
                            <i data-lucide="check-circle" class="w-3 h-3"></i> Approved Partner
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">
                        Referral Code: <span class="font-mono font-bold text-primary">{{ $affiliate->code }}</span>
                        &bull; Connected to <span class="font-mono font-semibold">{{ $affiliate->phone }}</span>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" @click="payoutModal = true" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-xl shadow-md transition flex items-center gap-2">
                    <i data-lucide="wallet" class="w-4 h-4"></i>
                    <span>Request Payout</span>
                </button>
                <form method="POST" action="{{ route('affiliate.logout') }}">
                    @csrf
                    <button type="submit" class="px-3.5 py-2.5 border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400 hover:text-rose-600 rounded-xl text-sm transition" title="Log Out">
                        <i data-lucide="log-out" class="w-4 h-4"></i>
                    </button>
                </form>
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

        <!-- Stats Overview Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 mb-8">
            <div class="p-6 rounded-3xl bg-gradient-to-br from-emerald-600 to-teal-700 text-white shadow-lg relative overflow-hidden">
                <p class="text-xs font-bold text-emerald-100 uppercase tracking-wider">Available Balance</p>
                <h3 class="text-3xl font-extrabold mt-1">৳{{ number_format($affiliate->balance, 2) }}</h3>
                <p class="text-[11px] text-emerald-200 mt-2">Ready to withdraw to {{ strtoupper($affiliate->payout_method) }}</p>
            </div>

            <div class="p-6 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-xs font-bold text-amber-500 uppercase tracking-wider">Pending Delivery</p>
                <h3 class="text-3xl font-extrabold text-gray-900 dark:text-white mt-1">৳{{ number_format($affiliate->pending_balance, 2) }}</h3>
                <p class="text-[11px] text-gray-400 mt-2">Credits automatically when order is delivered</p>
            </div>

            <div class="p-6 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-xs font-bold text-indigo-500 uppercase tracking-wider">Lifetime Earnings</p>
                <h3 class="text-3xl font-extrabold text-gray-900 dark:text-white mt-1">৳{{ number_format($affiliate->lifetime_earnings, 2) }}</h3>
                <p class="text-[11px] text-gray-400 mt-2">Total accumulated revenue earned</p>
            </div>

            <div class="p-6 rounded-3xl bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 shadow-sm">
                <p class="text-xs font-bold text-primary uppercase tracking-wider">Referred Orders</p>
                <h3 class="text-3xl font-extrabold text-gray-900 dark:text-white mt-1">{{ $referrals->total() }}</h3>
                <p class="text-[11px] text-gray-400 mt-2">Total successful customer purchases</p>
            </div>
        </div>

        <!-- Default Referral Link Quick Banner -->
        <div class="bg-primary/5 border border-primary/20 rounded-2xl p-5 mb-8 flex flex-col md:flex-row items-center justify-between gap-4" x-data="{ copied: false }">
            <div>
                <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <i data-lucide="link" class="w-4 h-4 text-primary"></i>
                    Your Primary Storefront Link
                </h4>
                <p class="text-xs text-gray-500 mt-0.5">Share this link anywhere. Any order placed by visitors will earn you commissions.</p>
                <div class="mt-2 font-mono text-xs font-bold text-primary bg-white dark:bg-gray-800 px-3 py-1.5 rounded-lg border border-primary/20 inline-block">
                    {{ url('/?ref=' . $affiliate->code) }}
                </div>
            </div>
            <button type="button" 
                    @click="navigator.clipboard.writeText('{{ url('/?ref=' . $affiliate->code) }}'); copied = true; setTimeout(() => copied = false, 2500);" 
                    class="px-5 py-2.5 bg-primary text-white text-xs font-bold rounded-xl hover:bg-secondary transition flex items-center gap-1.5 shadow-sm whitespace-nowrap">
                <i data-lucide="copy" class="w-4 h-4" x-show="!copied"></i>
                <i data-lucide="check" class="w-4 h-4" x-show="copied" style="display: none;"></i>
                <span x-text="copied ? 'Copied to Clipboard!' : 'Copy Storefront Link'"></span>
            </button>
        </div>

        <!-- Navigation Tabs: Links / Referrals / Payouts -->
        <div class="border-b border-gray-200 dark:border-gray-700 mb-6 flex gap-6">
            <button @click="activeTab = 'links'" :class="activeTab === 'links' ? 'border-primary text-primary font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 font-medium'" class="pb-3 border-b-2 text-sm flex items-center gap-2 transition">
                <i data-lucide="share-2" class="w-4 h-4"></i> Product Link Generator
            </button>
            <button @click="activeTab = 'referrals'" :class="activeTab === 'referrals' ? 'border-primary text-primary font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 font-medium'" class="pb-3 border-b-2 text-sm flex items-center gap-2 transition">
                <i data-lucide="shopping-bag" class="w-4 h-4"></i> Orders & Earnings ({{ $referrals->total() }})
            </button>
            <button @click="activeTab = 'payouts'" :class="activeTab === 'payouts' ? 'border-primary text-primary font-bold' : 'border-transparent text-gray-500 hover:text-gray-700 font-medium'" class="pb-3 border-b-2 text-sm flex items-center gap-2 transition">
                <i data-lucide="credit-card" class="w-4 h-4"></i> Payout History ({{ $payouts->count() }})
            </button>
        </div>

        <!-- Tab 1: Product Links -->
        <div x-show="activeTab === 'links'">
            <div class="mb-4 flex justify-between items-center">
                <h3 class="text-base font-bold text-gray-900 dark:text-white">Trending Products to Promote</h3>
                <p class="text-xs text-gray-500">Each item earns ৳150 or 10% (up to ৳300)</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($products as $product)
                @php
                    $pPrice = floatval($product->sale_price ?? $product->price);
                    $estComm = min(300.0, max(150.0, 0.10 * $pPrice));
                    $pLink = url("/product/{$product->slug}?ref={$affiliate->code}");
                @endphp
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 p-4 shadow-sm flex flex-col justify-between" x-data="{ prodCopied: false }">
                    <div>
                        <div class="aspect-square rounded-xl bg-gray-100 dark:bg-gray-700 overflow-hidden mb-3">
                            <img src="{{ $product->thumbnail?->url ?? $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                        </div>
                        <h4 class="font-bold text-sm text-gray-900 dark:text-white line-clamp-1">{{ $product->name }}</h4>
                        <div class="flex items-center justify-between mt-2">
                            <span class="font-bold text-sm text-gray-900 dark:text-white">৳{{ number_format($pPrice, 0) }}</span>
                            <span class="text-xs font-bold text-emerald-600 bg-emerald-50 dark:bg-emerald-950/50 px-2 py-0.5 rounded-md">
                                Earn ৳{{ number_format($estComm, 0) }}
                            </span>
                        </div>
                    </div>
                    
                    <button type="button" 
                            @click="navigator.clipboard.writeText('{{ $pLink }}'); prodCopied = true; setTimeout(() => prodCopied = false, 2000);" 
                            class="mt-4 w-full py-2 bg-gray-900 dark:bg-gray-700 hover:bg-primary dark:hover:bg-primary text-white text-xs font-bold rounded-xl transition flex items-center justify-center gap-1.5">
                        <i data-lucide="copy" class="w-3.5 h-3.5" x-show="!prodCopied"></i>
                        <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-400" x-show="prodCopied" style="display: none;"></i>
                        <span x-text="prodCopied ? 'Link Copied!' : 'Copy Product Link'"></span>
                    </button>
                </div>
                @endforeach
            </div>
        </div>

        <!-- Tab 2: Referrals Table -->
        <div x-show="activeTab === 'referrals'" style="display: none;">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                    <thead class="bg-gray-50/75 dark:bg-gray-900/75 text-xs uppercase font-semibold text-gray-500">
                        <tr>
                            <th class="px-6 py-4">Order ID</th>
                            <th class="px-6 py-4">Order Total</th>
                            <th class="px-6 py-4">Delivery Status</th>
                            <th class="px-6 py-4">Commission Status</th>
                            <th class="px-6 py-4">Commission Amount</th>
                            <th class="px-6 py-4">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($referrals as $ref)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 font-mono font-bold text-xs text-primary">#{{ $ref->order_id }}</td>
                            <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">৳{{ number_format($ref->order_amount, 2) }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                    {{ ucfirst($ref->order->status ?? 'pending') }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($ref->status === 'approved')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        <i data-lucide="check" class="w-3 h-3"></i> Earned & Settled
                                    </span>
                                @elseif($ref->status === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                        <i data-lucide="clock" class="w-3 h-3"></i> Pending Delivery
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                        <i data-lucide="x" class="w-3 h-3"></i> Cancelled
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-base font-extrabold text-emerald-600 dark:text-emerald-400">
                                ৳{{ number_format($ref->commission_amount, 2) }}
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500">
                                {{ $ref->created_at->format('M d, Y h:i A') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-400 text-xs">
                                No sales referred yet. Start sharing your links!
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($referrals->hasPages())
                <div class="mt-4">{{ $referrals->links() }}</div>
            @endif
        </div>

        <!-- Tab 3: Payouts History -->
        <div x-show="activeTab === 'payouts'" style="display: none;">
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden">
                <table class="w-full text-left text-sm text-gray-600 dark:text-gray-300">
                    <thead class="bg-gray-50/75 dark:bg-gray-900/75 text-xs uppercase font-semibold text-gray-500">
                        <tr>
                            <th class="px-6 py-4">Payout #</th>
                            <th class="px-6 py-4">Amount</th>
                            <th class="px-6 py-4">Method & Account</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Transaction ID</th>
                            <th class="px-6 py-4">Processed Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($payouts as $payout)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-6 py-4 font-mono font-bold text-xs">#{{ $payout->id }}</td>
                            <td class="px-6 py-4 font-extrabold text-gray-900 dark:text-white">৳{{ number_format($payout->amount, 2) }}</td>
                            <td class="px-6 py-4">
                                <span class="uppercase font-bold text-xs">{{ $payout->payout_method }}</span>
                                <span class="font-mono text-xs text-gray-500 ml-1">{{ $payout->account_details }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($payout->status === 'paid')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        <i data-lucide="check" class="w-3 h-3"></i> Paid
                                    </span>
                                @elseif($payout->status === 'pending')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800">
                                        <i data-lucide="clock" class="w-3 h-3"></i> Pending
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-800">
                                        <i data-lucide="x" class="w-3 h-3"></i> Rejected
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-mono text-xs font-bold text-primary">
                                {{ $payout->transaction_id ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-xs text-gray-500">
                                {{ $payout->processed_at ? $payout->processed_at->format('M d, Y h:i A') : $payout->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-400 text-xs">
                                No payout requests submitted yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <!-- Payout Request Modal -->
    <div x-show="payoutModal" 
         x-transition.opacity 
         class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4" 
         style="display: none;" 
         x-cloak>
        <div class="bg-white dark:bg-gray-800 rounded-3xl max-w-md w-full p-6 sm:p-8 shadow-2xl border border-gray-100 dark:border-gray-700" @click.away="payoutModal = false">
            <h3 class="text-xl font-extrabold text-gray-900 dark:text-white mb-1">Request Earnings Payout</h3>
            <p class="text-xs text-gray-500 mb-6">
                Available balance: <span class="font-extrabold text-emerald-600">৳{{ number_format($affiliate->balance, 2) }}</span>
            </p>

            <form method="POST" action="{{ route('affiliate.payout') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">
                        Amount to Withdraw (৳)
                    </label>
                    <input type="number" 
                           name="amount" 
                           min="100" 
                           max="{{ $affiliate->balance }}" 
                           step="1" 
                           value="{{ min($affiliate->balance, 500) }}" 
                           required 
                           class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-3 text-lg font-bold text-gray-900 dark:text-white focus:ring-2 focus:ring-primary">
                    <p class="text-[11px] text-gray-400 mt-1">Minimum payout is ৳100.</p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">Method</label>
                        <select name="payout_method" required class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-3 text-sm font-semibold">
                            <option value="bkash" {{ $affiliate->payout_method === 'bkash' ? 'selected' : '' }}>bKash</option>
                            <option value="nagad" {{ $affiliate->payout_method === 'nagad' ? 'selected' : '' }}>Nagad</option>
                            <option value="rocket" {{ $affiliate->payout_method === 'rocket' ? 'selected' : '' }}>Rocket</option>
                            <option value="bank" {{ $affiliate->payout_method === 'bank' ? 'selected' : '' }}>Bank Transfer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold uppercase text-gray-600 dark:text-gray-400 mb-1">Account / Number</label>
                        <input type="text" name="account_details" value="{{ $affiliate->payout_account }}" required class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 p-3 text-sm font-mono font-semibold">
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <button type="button" @click="payoutModal = false" class="px-4 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm font-medium">
                        Cancel
                    </button>
                    <button type="submit" 
                            {{ $affiliate->balance < 100 ? 'disabled' : '' }} 
                            class="px-5 py-2.5 bg-emerald-600 text-white rounded-xl text-sm font-bold hover:bg-emerald-700 transition disabled:opacity-50">
                        Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
