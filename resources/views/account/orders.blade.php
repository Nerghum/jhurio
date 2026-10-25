@extends('layouts.app')

@section('title', 'My Account')

@section('content')
@php
$statusConfig = [
    'pending'    => ['label' => 'Pending',    'class' => 'bg-amber-100 text-amber-800 border-amber-200',    'icon' => 'clock',         'dot' => 'bg-amber-400'],
    'processing' => ['label' => 'Processing', 'class' => 'bg-blue-100 text-blue-800 border-blue-200',       'icon' => 'package',       'dot' => 'bg-blue-400'],
    'shipped'    => ['label' => 'Shipped',    'class' => 'bg-violet-100 text-violet-800 border-violet-200', 'icon' => 'truck',         'dot' => 'bg-violet-400'],
    'delivered'  => ['label' => 'Delivered',  'class' => 'bg-teal-100 text-teal-800 border-teal-200',       'icon' => 'map-pin',       'dot' => 'bg-teal-400'],
    'completed'  => ['label' => 'Completed',  'class' => 'bg-emerald-100 text-emerald-800 border-emerald-200','icon' => 'check-circle-2','dot' => 'bg-emerald-400'],
    'cancelled'  => ['label' => 'Cancelled',  'class' => 'bg-red-100 text-red-800 border-red-200',          'icon' => 'x-circle',      'dot' => 'bg-red-400'],
];
$user = auth()->user();
$userName = ($user->name && $user->name !== 'Customer') ? $user->name : 'Valued Customer';
$userInitial = strtoupper(substr($userName, 0, 1));
@endphp

{{-- ══════════════════════════════════════════════════════════════════
     UNIFIED ACCOUNT CONTAINER
     ══════════════════════════════════════════════════════════════════ --}}
<div class="min-h-screen bg-slate-50 dark:bg-gray-950"
     x-data="accountHub({ initialTab: '{{ $currentTab }}' })"
     x-cloak>

    {{-- Global Session Alerts --}}
    @if(session('success'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 xl:px-8 pt-4" x-data="{s:true}" x-show="s">
        <div class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm">
            <i data-lucide="check-circle-2" class="w-4 h-4 flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
            <button @click="s=false" class="ml-auto text-emerald-500 hover:text-emerald-700"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
    </div>
    @endif
    @if(session('error'))
    <div class="max-w-7xl mx-auto px-4 sm:px-6 xl:px-8 pt-4" x-data="{s:true}" x-show="s">
        <div class="flex items-center gap-3 px-4 py-3 rounded-2xl bg-red-50 border border-red-200 text-red-800 text-xs sm:text-sm">
            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
            <span>{{ session('error') }}</span>
            <button @click="s=false" class="ml-auto text-red-500 hover:text-red-700"><i data-lucide="x" class="w-4 h-4"></i></button>
        </div>
    </div>
    @endif

    {{-- ══════════════════════════════════════════════════════════════════
         DESKTOP LAYOUT (lg:block hidden) — Sidebar + Main Content
         ══════════════════════════════════════════════════════════════════ --}}
    <div class="hidden lg:block max-w-7xl mx-auto px-6 xl:px-8 py-8">
        <div class="flex gap-7 xl:gap-8 items-start">

            {{-- ── DESKTOP SIDEBAR ──────────────────────────────────────── --}}
            <aside class="w-72 xl:w-80 flex-shrink-0 sticky top-24 space-y-4">

                {{-- Profile card --}}
                <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                    <div class="h-24 bg-gradient-to-135 from-primary via-primary/90 to-secondary/60 relative overflow-hidden">
                        <div class="absolute inset-0"
                             style="background-image:radial-gradient(circle at 70% 50%, rgba(107,193,18,.25) 0%, transparent 60%);"></div>
                        <div class="absolute bottom-0 left-0 w-full h-px bg-white/10"></div>
                        <div class="absolute -bottom-7 left-5">
                            <div class="w-14 h-14 rounded-2xl bg-secondary text-primary font-black text-xl flex items-center justify-center shadow-lg ring-4 ring-white dark:ring-gray-900">
                                {{ $userInitial }}
                            </div>
                        </div>
                    </div>
                    <div class="pt-9 px-5 pb-5">
                        <h2 class="font-bold text-gray-900 dark:text-white text-base leading-snug">{{ $userName }}</h2>
                        <p class="text-xs text-gray-400 dark:text-gray-500 font-mono mt-0.5">{{ $user->phone }}</p>
                        <span class="mt-2 inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900">
                            <i data-lucide="shield-check" class="w-3 h-3"></i> Verified Customer
                        </span>
                    </div>
                </div>

                {{-- Navigation menu --}}
                <nav class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm p-2 space-y-0.5">
                    @php
                    $navItems = [
                        ['tab' => 'current',   'icon' => 'clock',          'label' => 'Active Orders',   'count' => $currentOrders->count(),   'badge' => 'amber'],
                        ['tab' => 'completed', 'icon' => 'check-circle-2', 'label' => 'Completed Orders','count' => $completedOrders->count(), 'badge' => 'emerald'],
                        ['tab' => 'wishlist',  'icon' => 'heart',           'label' => 'My Wishlist',     'count' => $wishlists->count(),        'badge' => 'rose'],
                        ['tab' => 'cart',      'icon' => 'shopping-cart',   'label' => 'Cart Details',    'count' => $cartCount,                 'badge' => 'amber'],
                        ['tab' => 'all',       'icon' => 'history',         'label' => 'Order History',   'count' => $allOrders->total(),        'badge' => 'gray'],
                        ['tab' => 'addresses', 'icon' => 'map-pin',         'label' => 'My Addresses',    'count' => $addresses->count(),        'badge' => 'gray'],
                    ];
                    $badgeColors = [
                        'amber'   => 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400',
                        'emerald' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400',
                        'rose'    => 'bg-rose-100 text-rose-600 dark:bg-rose-950 dark:text-rose-400',
                        'gray'    => 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
                    ];
                    @endphp
                    @foreach($navItems as $nav)
                    <button type="button" @click="setTab('{{ $nav['tab'] }}')"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl text-sm font-semibold transition-all duration-150 text-left cursor-pointer"
                            :class="activeTab === '{{ $nav['tab'] }}' ? 'bg-primary text-white shadow-sm' : 'text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/70'">
                        <i data-lucide="{{ $nav['icon'] }}" class="w-4 h-4 flex-shrink-0"></i>
                        <span class="flex-1 truncate">{{ $nav['label'] }}</span>
                        @if($nav['count'] > 0)
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-md min-w-[18px] text-center"
                              :class="activeTab === '{{ $nav['tab'] }}' ? 'bg-secondary/90 text-primary' : '{{ $badgeColors[$nav['badge']] }}'">
                            {{ $nav['count'] }}
                        </span>
                        @endif
                    </button>
                    @endforeach
                </nav>

                {{-- Utility links --}}
                <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm p-2 space-y-0.5">
                    <a href="{{ route('shop.index') }}"
                       class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-400 hover:bg-primary/5 hover:text-primary dark:hover:text-secondary transition-colors">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                        <span>Continue Shopping</span>
                    </a>
                    <form action="{{ route('account.logout') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-red-500 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors text-left cursor-pointer">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                            <span>Sign Out</span>
                        </button>
                    </form>
                </div>
            </aside>

            {{-- ── DESKTOP MAIN CONTENT ─────────────────────────────────── --}}
            <main class="flex-1 min-w-0 space-y-5">

                {{-- 4 Stats Cards in Top Row --}}
                <div class="grid grid-cols-4 gap-4">
                    {{-- Active Orders --}}
                    <button type="button" @click="setTab('current')"
                            class="bg-white dark:bg-gray-900 rounded-2xl border p-4 text-center shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md cursor-pointer group"
                            :class="activeTab === 'current' ? 'border-primary ring-2 ring-primary/20 dark:border-secondary' : 'border-gray-100 dark:border-gray-800'">
                        <div class="text-3xl font-black text-primary dark:text-secondary group-hover:scale-105 transition-transform">{{ $currentOrders->count() }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">Active Orders</div>
                    </button>

                    {{-- Completed --}}
                    <button type="button" @click="setTab('completed')"
                            class="bg-white dark:bg-gray-900 rounded-2xl border p-4 text-center shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md cursor-pointer group"
                            :class="activeTab === 'completed' ? 'border-emerald-500 ring-2 ring-emerald-500/20' : 'border-gray-100 dark:border-gray-800'">
                        <div class="text-3xl font-black text-emerald-500 dark:text-emerald-400 group-hover:scale-105 transition-transform">{{ $completedOrders->count() }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">Completed</div>
                    </button>

                    {{-- Wishlist --}}
                    <button type="button" @click="setTab('wishlist')"
                            class="bg-white dark:bg-gray-900 rounded-2xl border p-4 text-center shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md cursor-pointer group"
                            :class="activeTab === 'wishlist' ? 'border-rose-400 ring-2 ring-rose-400/20' : 'border-gray-100 dark:border-gray-800'">
                        <div class="text-3xl font-black text-rose-500 dark:text-rose-400 group-hover:scale-105 transition-transform">{{ $wishlists->count() }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">Wishlist</div>
                    </button>

                    {{-- In Cart --}}
                    <button type="button" @click="setTab('cart')"
                            class="bg-white dark:bg-gray-900 rounded-2xl border p-4 text-center shadow-sm transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md cursor-pointer group"
                            :class="activeTab === 'cart' ? 'border-amber-500 ring-2 ring-amber-500/20' : 'border-gray-100 dark:border-gray-800'">
                        <div class="text-3xl font-black text-amber-500 dark:text-amber-400 group-hover:scale-105 transition-transform">{{ $cartCount }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium mt-1">In Cart</div>
                    </button>
                </div>

                <div class="bg-white dark:bg-gray-900 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden">
                    {{-- Content header --}}
                    <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                        <h1 class="font-bold text-gray-900 dark:text-white text-base"
                            x-text="getTabTitle()">Active Orders</h1>
                        <button type="button" x-show="activeTab === 'addresses'"
                                @click="addressModalOpen = true"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary/90 transition shadow-sm cursor-pointer">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Add Address</span>
                        </button>
                    </div>
                    <div class="p-5 lg:p-6">
                        {{-- TAB CONTENT --}}
                        @include('account.partials.all-tabs')
                    </div>
                </div>
            </main>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         MOBILE LAYOUT (below lg) — Clean, Native App Style
         ══════════════════════════════════════════════════════════════════ --}}
    <div class="lg:hidden pb-24">

        {{-- ── MOBILE COMPACT PROFILE HEADER ───────────────────────────── --}}
        <div class="bg-gradient-to-br from-primary via-[#0f4414] to-primary text-white px-4 pt-4 pb-5 rounded-b-3xl shadow-sm relative overflow-hidden">
            {{-- Ambient radial glows --}}
            <div class="absolute -right-6 -top-6 w-36 h-36 rounded-full bg-secondary/15 blur-2xl pointer-events-none"></div>
            <div class="absolute -left-6 -bottom-6 w-36 h-36 rounded-full bg-secondary/10 blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex items-center justify-between gap-3">
                {{-- Profile info --}}
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-12 h-12 rounded-full bg-secondary text-primary font-black text-xl flex items-center justify-center shrink-0 shadow-md ring-2 ring-white/20">
                        {{ $userInitial }}
                    </div>
                    <div class="min-w-0">
                        <h1 class="font-bold text-white text-base leading-tight truncate">{{ $userName }}</h1>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <span class="text-xs text-white/70 font-mono">{{ $user->phone }}</span>
                            <span class="inline-flex items-center gap-0.5 px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-white/15 text-emerald-200 border border-white/10">
                                <i data-lucide="shield-check" class="w-2.5 h-2.5"></i> Verified
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Action buttons --}}
                <div class="flex items-center gap-1.5 shrink-0">
                    <a href="{{ route('shop.index') }}"
                       title="Browse Shop"
                       class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/20 text-white flex items-center justify-center transition border border-white/10 active:scale-95">
                        <i data-lucide="shopping-bag" class="w-4 h-4"></i>
                    </a>
                    <form action="{{ route('account.logout') }}" method="POST" class="contents">
                        @csrf
                        <button type="submit"
                                title="Sign Out"
                                class="w-9 h-9 rounded-full bg-white/10 hover:bg-red-500/20 text-white/80 hover:text-red-300 flex items-center justify-center transition border border-white/10 active:scale-95 cursor-pointer">
                            <i data-lucide="log-out" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── 4 STAT CARDS ────────────────────────────────────────────── --}}
        <div class="grid grid-cols-4 gap-2 px-3 mt-3">
            {{-- Active Orders --}}
            <button type="button" @click="setTab('current')"
                    class="rounded-2xl p-2.5 text-center transition-all bg-white dark:bg-gray-900 border shadow-xs active:scale-95 cursor-pointer"
                    :class="activeTab==='current' ? 'border-primary ring-2 ring-primary/20 bg-primary/5 dark:bg-primary/20' : 'border-gray-200/80 dark:border-gray-800'">
                <div class="font-black text-xl leading-none text-primary dark:text-secondary">{{ $currentOrders->count() }}</div>
                <div class="text-[10px] font-semibold mt-1"
                     :class="activeTab==='current' ? 'text-primary dark:text-secondary font-bold' : 'text-gray-500 dark:text-gray-400'">Active</div>
            </button>

            {{-- Completed --}}
            <button type="button" @click="setTab('completed')"
                    class="rounded-2xl p-2.5 text-center transition-all bg-white dark:bg-gray-900 border shadow-xs active:scale-95 cursor-pointer"
                    :class="activeTab==='completed' ? 'border-emerald-500 ring-2 ring-emerald-500/20 bg-emerald-50/50 dark:bg-emerald-950/30' : 'border-gray-200/80 dark:border-gray-800'">
                <div class="font-black text-xl leading-none text-emerald-600 dark:text-emerald-400">{{ $completedOrders->count() }}</div>
                <div class="text-[10px] font-semibold mt-1"
                     :class="activeTab==='completed' ? 'text-emerald-700 dark:text-emerald-400 font-bold' : 'text-gray-500 dark:text-gray-400'">Done</div>
            </button>

            {{-- Wishlist --}}
            <button type="button" @click="setTab('wishlist')"
                    class="rounded-2xl p-2.5 text-center transition-all bg-white dark:bg-gray-900 border shadow-xs active:scale-95 cursor-pointer"
                    :class="activeTab==='wishlist' ? 'border-rose-400 ring-2 ring-rose-400/20 bg-rose-50/50 dark:bg-rose-950/30' : 'border-gray-200/80 dark:border-gray-800'">
                <div class="font-black text-xl leading-none text-rose-500 dark:text-rose-400">{{ $wishlists->count() }}</div>
                <div class="text-[10px] font-semibold mt-1"
                     :class="activeTab==='wishlist' ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-gray-500 dark:text-gray-400'">Saved</div>
            </button>

            {{-- Cart --}}
            <button type="button" @click="setTab('cart')"
                    class="rounded-2xl p-2.5 text-center transition-all bg-white dark:bg-gray-900 border shadow-xs active:scale-95 cursor-pointer"
                    :class="activeTab==='cart' ? 'border-amber-400 ring-2 ring-amber-400/20 bg-amber-50/50 dark:bg-amber-950/30' : 'border-gray-200/80 dark:border-gray-800'">
                <div class="font-black text-xl leading-none text-amber-500 dark:text-amber-400">{{ $cartCount }}</div>
                <div class="text-[10px] font-semibold mt-1"
                     :class="activeTab==='cart' ? 'text-amber-600 dark:text-amber-400 font-bold' : 'text-gray-500 dark:text-gray-400'">Cart</div>
            </button>
        </div>

        {{-- ── HORIZONTAL SCROLLING TABS ────────────────────────────────── --}}
        <div class="px-3 mt-3 flex items-center gap-1.5 overflow-x-auto scrollbar-hide py-0.5">
            <button type="button" @click="setTab('current')"
                    class="px-3 py-1.5 rounded-full text-xs font-semibold shrink-0 flex items-center gap-1.5 transition-all cursor-pointer"
                    :class="activeTab==='current' ? 'bg-primary text-white shadow-xs' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 border border-gray-200/80 dark:border-gray-800'">
                <i data-lucide="clock" class="w-3.5 h-3.5"></i>
                <span>Active ({{ $currentOrders->count() }})</span>
            </button>

            <button type="button" @click="setTab('completed')"
                    class="px-3 py-1.5 rounded-full text-xs font-semibold shrink-0 flex items-center gap-1.5 transition-all cursor-pointer"
                    :class="activeTab==='completed' ? 'bg-primary text-white shadow-xs' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 border border-gray-200/80 dark:border-gray-800'">
                <i data-lucide="check-circle-2" class="w-3.5 h-3.5"></i>
                <span>Completed ({{ $completedOrders->count() }})</span>
            </button>

            <button type="button" @click="setTab('all')"
                    class="px-3 py-1.5 rounded-full text-xs font-semibold shrink-0 flex items-center gap-1.5 transition-all cursor-pointer"
                    :class="activeTab==='all' ? 'bg-primary text-white shadow-xs' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 border border-gray-200/80 dark:border-gray-800'">
                <i data-lucide="history" class="w-3.5 h-3.5"></i>
                <span>All History</span>
            </button>

            <button type="button" @click="setTab('wishlist')"
                    class="px-3 py-1.5 rounded-full text-xs font-semibold shrink-0 flex items-center gap-1.5 transition-all cursor-pointer"
                    :class="activeTab==='wishlist' ? 'bg-primary text-white shadow-xs' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 border border-gray-200/80 dark:border-gray-800'">
                <i data-lucide="heart" class="w-3.5 h-3.5"></i>
                <span>Wishlist ({{ $wishlists->count() }})</span>
            </button>

            <button type="button" @click="setTab('cart')"
                    class="px-3 py-1.5 rounded-full text-xs font-semibold shrink-0 flex items-center gap-1.5 transition-all cursor-pointer"
                    :class="activeTab==='cart' ? 'bg-primary text-white shadow-xs' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 border border-gray-200/80 dark:border-gray-800'">
                <i data-lucide="shopping-cart" class="w-3.5 h-3.5"></i>
                <span>Cart ({{ $cartCount }})</span>
            </button>

            <button type="button" @click="setTab('addresses')"
                    class="px-3 py-1.5 rounded-full text-xs font-semibold shrink-0 flex items-center gap-1.5 transition-all cursor-pointer"
                    :class="activeTab==='addresses' ? 'bg-primary text-white shadow-xs' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-300 border border-gray-200/80 dark:border-gray-800'">
                <i data-lucide="map-pin" class="w-3.5 h-3.5"></i>
                <span>Addresses</span>
            </button>
        </div>

        {{-- ── TAB HEADING & QUICK ACTION ───────────────────────────────── --}}
        <div class="px-3 mt-3.5 mb-2 flex items-center justify-between">
            <h2 class="font-bold text-gray-900 dark:text-white text-sm tracking-tight"
                x-text="getTabTitle()">Active Orders</h2>
            <button type="button" x-show="activeTab === 'addresses'"
                    @click="addressModalOpen = true"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-primary text-white text-xs font-bold shadow-xs active:scale-95 cursor-pointer">
                <i data-lucide="plus" class="w-3 h-3"></i>
                <span>Add Address</span>
            </button>
        </div>

        {{-- ── CONTENT CARD ────────────────────────────────────────────── --}}
        <div class="mx-3 bg-white dark:bg-gray-900 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-xs p-3 sm:p-4 overflow-hidden">
            @include('account.partials.all-tabs')
        </div>
    </div>

    {{-- Single Modal instance for both desktop and mobile --}}
    @include('account.partials.account-modals')
</div>

@endsection

@push('scripts')
<script>
function accountHub(config) {
    return {
        activeTab: config?.initialTab || 'current',
        cancelModalOpen: false,
        cancellingOrder: null,
        cancelReason: 'Changed my mind',
        addressModalOpen: false,

        setTab(tab) {
            this.activeTab = tab;
            const url = new URL(window.location);
            url.searchParams.set('tab', tab);
            window.history.replaceState({}, '', url);
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
        },

        getTabTitle() {
            const titles = {
                'current': 'Active Orders',
                'completed': 'Completed Orders',
                'wishlist': 'My Wishlist',
                'cart': 'Cart Details',
                'all': 'Order History',
                'addresses': 'Saved Addresses'
            };
            return titles[this.activeTab] || 'Active Orders';
        },

        openCancelModal(order) {
            this.cancellingOrder = order;
            this.cancelReason = 'Changed my mind';
            this.cancelModalOpen = true;
            this.$nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); });
        },

        closeCancelModal() {
            this.cancelModalOpen = false;
            this.cancellingOrder = null;
        },

        async setDefaultAddress(addressId) {
            try {
                const res = await fetch(`/account/addresses/${addressId}/default`, {
                    method: 'PATCH',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
                });
                const data = await res.json();
                if (data.success) window.location.reload();
            } catch (err) { console.error(err); }
        },

        async removeFromWishlist(productId, elementId) {
            try {
                const res = await fetch('{{ route('wishlist.toggle') }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ product_id: productId })
                });
                const data = await res.json();
                if (data.success) {
                    const el = document.getElementById(elementId);
                    if (el) { el.style.opacity='0'; el.style.transform='scale(0.9)'; el.style.transition='all .25s'; setTimeout(() => el.remove(), 250); }
                }
            } catch (err) { console.error(err); }
        },

        async quickAddToCart(productId, btn) {
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<span class="inline-block animate-spin mr-1 text-xs">↻</span> Adding...`;
            try {
                const res = await fetch('/cart/add', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                    body: JSON.stringify({ product_id: productId, quantity: 1 })
                });
                const data = await res.json();
                if (data.success) {
                    btn.innerHTML = `✓ Added!`;
                    btn.classList.remove('bg-primary'); btn.classList.add('bg-emerald-600');
                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: data }));
                    setTimeout(() => { btn.innerHTML = originalText; btn.classList.remove('bg-emerald-600'); btn.classList.add('bg-primary'); btn.disabled = false; if (typeof lucide !== 'undefined') lucide.createIcons(); }, 1500);
                } else { btn.innerHTML = originalText; btn.disabled = false; }
            } catch (e) { btn.innerHTML = originalText; btn.disabled = false; }
        }
    };
}

document.addEventListener('alpine:init', () => {
    Alpine.data('accountHub', accountHub);
});
if (window.Alpine) {
    window.Alpine.data('accountHub', accountHub);
}
</script>
@endpush
