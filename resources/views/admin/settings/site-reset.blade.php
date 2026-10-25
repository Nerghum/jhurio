@extends('layouts.admin')

@section('title', 'Site Factory Reset (Superadmin)')
@section('header', 'Site Factory Reset')

@section('content')
<div class="max-w-5xl mx-auto space-y-6" x-data="{
    options: {
        wipe_orders: true,
        wipe_abandoned_carts: true,
        wipe_tracking: true,
        wipe_products: false,
        wipe_categories: false,
        wipe_bundles: false,
        wipe_reviews: false,
        wipe_stock_purchases: false,
        wipe_media: false,
        wipe_customers: false,
        wipe_wishlists: false,
        wipe_messages: false
    },
    confirmPhrase: '',
    password: '',
    selectAll() {
        Object.keys(this.options).forEach(k => this.options[k] = true);
    },
    keepAll() {
        Object.keys(this.options).forEach(k => this.options[k] = false);
    },
    get selectedCount() {
        return Object.values(this.options).filter(v => v).length;
    },
    get isFormValid() {
        return this.confirmPhrase.trim().toUpperCase() === 'RESET' && this.password.length > 0 && this.selectedCount > 0;
    }
}">

    <!-- Flash Notifications -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm font-semibold flex items-start gap-3 shadow-xs">
            <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 flex-shrink-0 mt-0.5"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-sm font-semibold flex items-start gap-3 shadow-xs">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-rose-600 flex-shrink-0 mt-0.5"></i>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    @if(session('info'))
        <div class="p-4 rounded-2xl bg-blue-50 dark:bg-blue-950/40 border border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-300 text-sm font-semibold flex items-start gap-3 shadow-xs">
            <i data-lucide="info" class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5"></i>
            <div>{{ session('info') }}</div>
        </div>
    @endif

    <!-- Superadmin Danger Banner -->
    <div class="rounded-3xl bg-gradient-to-br from-rose-950 via-rose-900 to-rose-950 text-white p-6 sm:p-8 shadow-xl shadow-rose-950/20 border border-rose-800/60 relative overflow-hidden">
        <div class="absolute -right-8 -top-8 w-48 h-48 rounded-full bg-rose-500/20 blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
            <div class="flex items-start gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 flex items-center justify-center flex-shrink-0 text-rose-300 shadow-inner">
                    <i data-lucide="flame" class="w-8 h-8"></i>
                </div>
                <div>
                    <div class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full bg-rose-500/30 text-rose-200 border border-rose-400/40 text-[11px] font-bold uppercase tracking-wider mb-2">
                        <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i> Superadmin Exclusive Control
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-black tracking-tight">Site Factory Reset</h1>
                    <p class="text-xs sm:text-sm text-rose-200/80 mt-1 max-w-2xl leading-relaxed">
                        Selectively wipe test orders, visitor analytics, products, media files, or reset the whole store. 
                        <strong>Uncheck any item below to keep it safe.</strong>
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" @click="selectAll()" class="px-3.5 py-2 rounded-xl bg-white/15 hover:bg-white/25 border border-white/20 text-white text-xs font-bold transition">
                    Wipe All Items
                </button>
                <button type="button" @click="keepAll()" class="px-3.5 py-2 rounded-xl bg-white/15 hover:bg-white/25 border border-white/20 text-white text-xs font-bold transition">
                    Keep All (Uncheck All)
                </button>
            </div>
        </div>
    </div>

    <!-- Main Selection Form -->
    <form action="{{ route('admin.settings.site_reset.execute') }}" method="POST" class="space-y-6">
        @csrf

        <div class="bg-white dark:bg-gray-800 rounded-3xl border border-gray-100 dark:border-gray-700/80 p-6 sm:p-8 shadow-sm space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-gray-100 dark:border-gray-700/80 pb-4">
                <div>
                    <h3 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <i data-lucide="layers" class="w-5 h-5 text-primary"></i>
                        <span>Select What to Wipe vs. What to Keep</span>
                    </h3>
                    <p class="text-xs text-gray-500 mt-0.5">Checked items will be permanently erased. Unchecked items remain preserved.</p>
                </div>
                <div class="text-xs font-bold px-3 py-1 rounded-full bg-rose-50 text-rose-700 dark:bg-rose-950/40 dark:text-rose-400 border border-rose-200 dark:border-rose-800">
                    <span x-text="selectedCount"></span> of 12 categories selected for wipe
                </div>
            </div>

            <!-- Categories Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                
                <!-- 1. Orders & Transactions -->
                <label class="p-4 rounded-2xl border transition-all cursor-pointer relative flex items-start gap-3.5 select-none"
                       :class="options.wipe_orders ? 'border-rose-300 dark:border-rose-800 bg-rose-50/30 dark:bg-rose-950/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800'">
                    <input type="checkbox" name="wipe_orders" value="1" x-model="options.wipe_orders" class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-gray-300 mt-0.5">
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-900 dark:text-white">Orders &amp; Logs</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ $counts['orders'] }} Orders
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">Clears orders, line items, and audit history logs.</p>
                        <span class="inline-block mt-2 text-[10px] font-extrabold uppercase tracking-wider" :class="options.wipe_orders ? 'text-rose-600' : 'text-emerald-600'">
                            <span x-text="options.wipe_orders ? '✕ WILL BE WIPED' : '✓ KEEPING SAFE'"></span>
                        </span>
                    </div>
                </label>

                <!-- 2. Abandoned Carts -->
                <label class="p-4 rounded-2xl border transition-all cursor-pointer relative flex items-start gap-3.5 select-none"
                       :class="options.wipe_abandoned_carts ? 'border-rose-300 dark:border-rose-800 bg-rose-50/30 dark:bg-rose-950/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800'">
                    <input type="checkbox" name="wipe_abandoned_carts" value="1" x-model="options.wipe_abandoned_carts" class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-gray-300 mt-0.5">
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-900 dark:text-white">Abandoned Carts</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ $counts['abandoned_carts'] }} Carts
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">Clears unfinished customer checkout cart sessions.</p>
                        <span class="inline-block mt-2 text-[10px] font-extrabold uppercase tracking-wider" :class="options.wipe_abandoned_carts ? 'text-rose-600' : 'text-emerald-600'">
                            <span x-text="options.wipe_abandoned_carts ? '✕ WILL BE WIPED' : '✓ KEEPING SAFE'"></span>
                        </span>
                    </div>
                </label>

                <!-- 3. Visitor Tracking Data -->
                <label class="p-4 rounded-2xl border transition-all cursor-pointer relative flex items-start gap-3.5 select-none"
                       :class="options.wipe_tracking ? 'border-rose-300 dark:border-rose-800 bg-rose-50/30 dark:bg-rose-950/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800'">
                    <input type="checkbox" name="wipe_tracking" value="1" x-model="options.wipe_tracking" class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-gray-300 mt-0.5">
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-900 dark:text-white">Visitor &amp; Tracking Data</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ $counts['tracking_visitors'] }} Visitors
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">Clears tracking visitors, sessions, and events timeline.</p>
                        <span class="inline-block mt-2 text-[10px] font-extrabold uppercase tracking-wider" :class="options.wipe_tracking ? 'text-rose-600' : 'text-emerald-600'">
                            <span x-text="options.wipe_tracking ? '✕ WILL BE WIPED' : '✓ KEEPING SAFE'"></span>
                        </span>
                    </div>
                </label>

                <!-- 4. Products & Variations -->
                <label class="p-4 rounded-2xl border transition-all cursor-pointer relative flex items-start gap-3.5 select-none"
                       :class="options.wipe_products ? 'border-rose-300 dark:border-rose-800 bg-rose-50/30 dark:bg-rose-950/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800'">
                    <input type="checkbox" name="wipe_products" value="1" x-model="options.wipe_products" class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-gray-300 mt-0.5">
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-900 dark:text-white">Products &amp; Variations</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ $counts['products'] }} Products
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">Clears all products, variations, options, and tag links.</p>
                        <span class="inline-block mt-2 text-[10px] font-extrabold uppercase tracking-wider" :class="options.wipe_products ? 'text-rose-600' : 'text-emerald-600'">
                            <span x-text="options.wipe_products ? '✕ WILL BE WIPED' : '✓ KEEPING SAFE'"></span>
                        </span>
                    </div>
                </label>

                <!-- 5. Categories -->
                <label class="p-4 rounded-2xl border transition-all cursor-pointer relative flex items-start gap-3.5 select-none"
                       :class="options.wipe_categories ? 'border-rose-300 dark:border-rose-800 bg-rose-50/30 dark:bg-rose-950/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800'">
                    <input type="checkbox" name="wipe_categories" value="1" x-model="options.wipe_categories" class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-gray-300 mt-0.5">
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-900 dark:text-white">Product Categories</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ $counts['categories'] }} Categories
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">Clears store taxonomy categories &amp; descriptions.</p>
                        <span class="inline-block mt-2 text-[10px] font-extrabold uppercase tracking-wider" :class="options.wipe_categories ? 'text-rose-600' : 'text-emerald-600'">
                            <span x-text="options.wipe_categories ? '✕ WILL BE WIPED' : '✓ KEEPING SAFE'"></span>
                        </span>
                    </div>
                </label>

                <!-- 6. Product Bundles -->
                <label class="p-4 rounded-2xl border transition-all cursor-pointer relative flex items-start gap-3.5 select-none"
                       :class="options.wipe_bundles ? 'border-rose-300 dark:border-rose-800 bg-rose-50/30 dark:bg-rose-950/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800'">
                    <input type="checkbox" name="wipe_bundles" value="1" x-model="options.wipe_bundles" class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-gray-300 mt-0.5">
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-900 dark:text-white">Product Bundles</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ $counts['bundles'] }} Bundles
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">Clears combo bundles and pricing rules.</p>
                        <span class="inline-block mt-2 text-[10px] font-extrabold uppercase tracking-wider" :class="options.wipe_bundles ? 'text-rose-600' : 'text-emerald-600'">
                            <span x-text="options.wipe_bundles ? '✕ WILL BE WIPED' : '✓ KEEPING SAFE'"></span>
                        </span>
                    </div>
                </label>

                <!-- 7. Customer Reviews -->
                <label class="p-4 rounded-2xl border transition-all cursor-pointer relative flex items-start gap-3.5 select-none"
                       :class="options.wipe_reviews ? 'border-rose-300 dark:border-rose-800 bg-rose-50/30 dark:bg-rose-950/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800'">
                    <input type="checkbox" name="wipe_reviews" value="1" x-model="options.wipe_reviews" class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-gray-300 mt-0.5">
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-900 dark:text-white">Product Reviews</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ $counts['reviews'] }} Reviews
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">Clears customer product reviews, stars, and photos.</p>
                        <span class="inline-block mt-2 text-[10px] font-extrabold uppercase tracking-wider" :class="options.wipe_reviews ? 'text-rose-600' : 'text-emerald-600'">
                            <span x-text="options.wipe_reviews ? '✕ WILL BE WIPED' : '✓ KEEPING SAFE'"></span>
                        </span>
                    </div>
                </label>

                <!-- 8. Stock Purchases & Inward History -->
                <label class="p-4 rounded-2xl border transition-all cursor-pointer relative flex items-start gap-3.5 select-none"
                       :class="options.wipe_stock_purchases ? 'border-rose-300 dark:border-rose-800 bg-rose-50/30 dark:bg-rose-950/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800'">
                    <input type="checkbox" name="wipe_stock_purchases" value="1" x-model="options.wipe_stock_purchases" class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-gray-300 mt-0.5">
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-900 dark:text-white">Stock Purchase Logs</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ $counts['stock_purchases'] }} Logs
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">Clears inventory supplier purchase cost history.</p>
                        <span class="inline-block mt-2 text-[10px] font-extrabold uppercase tracking-wider" :class="options.wipe_stock_purchases ? 'text-rose-600' : 'text-emerald-600'">
                            <span x-text="options.wipe_stock_purchases ? '✕ WILL BE WIPED' : '✓ KEEPING SAFE'"></span>
                        </span>
                    </div>
                </label>

                <!-- 9. Media & Uploaded Files (Disk + DB) -->
                <label class="p-4 rounded-2xl border transition-all cursor-pointer relative flex items-start gap-3.5 select-none"
                       :class="options.wipe_media ? 'border-rose-300 dark:border-rose-800 bg-rose-50/30 dark:bg-rose-950/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800'">
                    <input type="checkbox" name="wipe_media" value="1" x-model="options.wipe_media" class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-gray-300 mt-0.5">
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-900 dark:text-white">Media Files (Disk &amp; DB)</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ $counts['media'] }} Files
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">Deletes all uploaded images from storage &amp; media library.</p>
                        <span class="inline-block mt-2 text-[10px] font-extrabold uppercase tracking-wider" :class="options.wipe_media ? 'text-rose-600' : 'text-emerald-600'">
                            <span x-text="options.wipe_media ? '✕ WILL BE WIPED' : '✓ KEEPING SAFE'"></span>
                        </span>
                    </div>
                </label>

                <!-- 10. Customer Accounts (Never User #1 or Admins) -->
                <label class="p-4 rounded-2xl border transition-all cursor-pointer relative flex items-start gap-3.5 select-none"
                       :class="options.wipe_customers ? 'border-rose-300 dark:border-rose-800 bg-rose-50/30 dark:bg-rose-950/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800'">
                    <input type="checkbox" name="wipe_customers" value="1" x-model="options.wipe_customers" class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-gray-300 mt-0.5">
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-900 dark:text-white">Customer Accounts</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ $counts['customers'] }} Customers
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">Clears registered customer accounts. User #1 &amp; admins are always safe.</p>
                        <span class="inline-block mt-2 text-[10px] font-extrabold uppercase tracking-wider" :class="options.wipe_customers ? 'text-rose-600' : 'text-emerald-600'">
                            <span x-text="options.wipe_customers ? '✕ WILL BE WIPED' : '✓ KEEPING SAFE'"></span>
                        </span>
                    </div>
                </label>

                <!-- 11. Wishlists -->
                <label class="p-4 rounded-2xl border transition-all cursor-pointer relative flex items-start gap-3.5 select-none"
                       :class="options.wipe_wishlists ? 'border-rose-300 dark:border-rose-800 bg-rose-50/30 dark:bg-rose-950/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800'">
                    <input type="checkbox" name="wipe_wishlists" value="1" x-model="options.wipe_wishlists" class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-gray-300 mt-0.5">
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-900 dark:text-white">Customer Wishlists</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ $counts['wishlists'] }} Items
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">Clears saved favorite wishlist product records.</p>
                        <span class="inline-block mt-2 text-[10px] font-extrabold uppercase tracking-wider" :class="options.wipe_wishlists ? 'text-rose-600' : 'text-emerald-600'">
                            <span x-text="options.wipe_wishlists ? '✕ WILL BE WIPED' : '✓ KEEPING SAFE'"></span>
                        </span>
                    </div>
                </label>

                <!-- 12. Contact Messages -->
                <label class="p-4 rounded-2xl border transition-all cursor-pointer relative flex items-start gap-3.5 select-none"
                       :class="options.wipe_messages ? 'border-rose-300 dark:border-rose-800 bg-rose-50/30 dark:bg-rose-950/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800'">
                    <input type="checkbox" name="wipe_messages" value="1" x-model="options.wipe_messages" class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500 border-gray-300 mt-0.5">
                    <div class="flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold text-gray-900 dark:text-white">Contact Messages</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300">
                                {{ $counts['messages'] }} Messages
                            </span>
                        </div>
                        <p class="text-[11px] text-gray-500 mt-1">Clears received customer contact inquiries.</p>
                        <span class="inline-block mt-2 text-[10px] font-extrabold uppercase tracking-wider" :class="options.wipe_messages ? 'text-rose-600' : 'text-emerald-600'">
                            <span x-text="options.wipe_messages ? '✕ WILL BE WIPED' : '✓ KEEPING SAFE'"></span>
                        </span>
                    </div>
                </label>

            </div>

            <!-- Double Security Authentication Section -->
            <div class="pt-6 border-t border-gray-100 dark:border-gray-700/80 space-y-4">
                <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-900/60 text-amber-800 dark:text-amber-300 text-xs flex items-start gap-3">
                    <i data-lucide="shield-alert" class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5"></i>
                    <div>
                        <strong>Safety Confirmation Required:</strong>
                        This action cannot be undone. To execute the reset, type <strong>RESET</strong> in capital letters and enter your Superadmin password below.
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Type <span class="text-rose-600 font-black">RESET</span> to confirm
                        </label>
                        <input type="text" 
                               name="confirm_phrase" 
                               x-model="confirmPhrase" 
                               placeholder="Type RESET"
                               required
                               autocomplete="off"
                               class="w-full text-sm font-mono font-bold px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:border-rose-500 shadow-2xs">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-gray-700 dark:text-gray-300 mb-1.5">
                            Superadmin Password
                        </label>
                        <input type="password" 
                               name="password" 
                               x-model="password" 
                               placeholder="Enter your account password"
                               required
                               class="w-full text-sm font-medium px-3.5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-rose-500 focus:border-rose-500 shadow-2xs">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4">
                    <a href="{{ route('admin.dashboard') }}" class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 text-xs font-bold transition">
                        Cancel &amp; Go Back
                    </a>

                    <button type="submit" 
                            :disabled="!isFormValid"
                            onclick="return confirm('CRITICAL WARNING: Are you 100% sure you want to execute this site reset? Selected data will be permanently erased.')"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-black uppercase tracking-wider shadow-md shadow-rose-600/30 transition-all disabled:opacity-50 disabled:cursor-not-allowed hover:scale-[1.01] active:scale-[0.99]">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                        <span>Execute Factory Reset</span>
                    </button>
                </div>
            </div>

        </div>
    </form>

</div>
@endsection
