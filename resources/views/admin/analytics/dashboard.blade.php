@extends('layouts.admin')

@section('title', 'Live Analytics Dashboard')
@section('header', 'Live Tracking Dashboard')

@section('content')
<div class="max-w-7xl mx-auto space-y-6" x-data="liveDashboard()">
    
    <!-- Header Metrics -->
    <div class="flex flex-col md:flex-row justify-between items-center bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="flex items-center mb-4 md:mb-0">
            <div class="relative flex h-4 w-4 mr-3">
              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
              <span class="relative inline-flex rounded-full h-4 w-4 bg-green-500"></span>
            </div>
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">
                <span x-text="data.live_users" class="text-2xl text-primary mr-2">0</span> 
                Live Online Users
            </h2>
        </div>
        <div class="text-sm text-gray-500 dark:text-gray-400">
            Auto-refreshing every 10 seconds. Last updated: <span x-text="lastUpdated"></span>
        </div>
    </div>

    <!-- Today's KPIs -->
    <h3 class="text-md font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wider mt-8 mb-4">Today's Overview</h3>
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <!-- KPI Card -->
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Total Visitors</p>
            <h4 class="text-2xl font-bold text-gray-900 dark:text-white mt-1" x-text="data.today_visitors">0</h4>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">New Visitors</p>
            <h4 class="text-2xl font-bold text-gray-900 dark:text-white mt-1" x-text="data.today_new">0</h4>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Returning</p>
            <h4 class="text-2xl font-bold text-gray-900 dark:text-white mt-1" x-text="data.today_returning">0</h4>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Sessions</p>
            <h4 class="text-2xl font-bold text-gray-900 dark:text-white mt-1" x-text="data.today_sessions">0</h4>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 shadow-sm border border-gray-100 dark:border-gray-700">
            <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">Pageviews</p>
            <h4 class="text-2xl font-bold text-gray-900 dark:text-white mt-1" x-text="data.today_pageviews">0</h4>
        </div>
    </div>

    <!-- Ecommerce KPIs -->
    <h3 class="text-md font-semibold text-gray-800 dark:text-gray-200 uppercase tracking-wider mt-8 mb-4">Ecommerce Funnel (Today)</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-5 shadow-sm border border-blue-100 dark:border-blue-800/30">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-blue-600 dark:text-blue-400 font-medium">Product Views</p>
                <i data-lucide="eye" class="w-4 h-4 text-blue-500"></i>
            </div>
            <h4 class="text-2xl font-bold text-gray-900 dark:text-white" x-text="data.today_product_views">0</h4>
        </div>
        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-xl p-5 shadow-sm border border-yellow-100 dark:border-yellow-800/30">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-yellow-600 dark:text-yellow-400 font-medium">Add to Cart</p>
                <i data-lucide="shopping-cart" class="w-4 h-4 text-yellow-500"></i>
            </div>
            <h4 class="text-2xl font-bold text-gray-900 dark:text-white" x-text="data.today_add_cart">0</h4>
        </div>
        <div class="bg-orange-50 dark:bg-orange-900/20 rounded-xl p-5 shadow-sm border border-orange-100 dark:border-orange-800/30">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-orange-600 dark:text-orange-400 font-medium">Checkouts</p>
                <i data-lucide="credit-card" class="w-4 h-4 text-orange-500"></i>
            </div>
            <h4 class="text-2xl font-bold text-gray-900 dark:text-white" x-text="data.today_checkout">0</h4>
        </div>
        <div class="bg-green-50 dark:bg-green-900/20 rounded-xl p-5 shadow-sm border border-green-100 dark:border-green-800/30">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm text-green-600 dark:text-green-400 font-medium">Purchases</p>
                <i data-lucide="check-circle" class="w-4 h-4 text-green-500"></i>
            </div>
            <h4 class="text-2xl font-bold text-gray-900 dark:text-white" x-text="data.today_purchases">0</h4>
        </div>
    </div>

    <!-- Live Activity Feed -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden mt-8">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Live Activity Feed</h3>
        </div>
        <div class="p-0">
            <ul class="divide-y divide-gray-100 dark:divide-gray-700 max-h-[500px] overflow-y-auto custom-scrollbar">
                <template x-for="event in data.feed" :key="event.id">
                    <li class="px-6 py-4 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                        <div class="flex items-start">
                            <div class="flex-shrink-0 mt-1">
                                <span class="inline-flex items-center justify-center h-8 w-8 rounded-full" 
                                      :class="getIconBg(event.type)">
                                    <i :data-lucide="getIcon(event.type)" class="h-4 w-4" :class="getIconColor(event.type)"></i>
                                </span>
                            </div>
                            <div class="ml-4 flex-1">
                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white">
                                        User <span x-text="getEventText(event)"></span>
                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400" x-text="event.time"></p>
                                </div>
                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 flex items-center space-x-3">
                                    <span class="flex items-center"><i data-lucide="map-pin" class="w-3 h-3 mr-1"></i> <span x-text="event.city + ', ' + event.country"></span></span>
                                    <span class="flex items-center"><i data-lucide="monitor" class="w-3 h-3 mr-1"></i> <span x-text="event.device"></span></span>
                                </div>
                            </div>
                        </div>
                    </li>
                </template>
                <li x-show="data.feed.length === 0" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                    No recent activity. Waiting for visitors...
                </li>
            </ul>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function liveDashboard() {
        return {
            data: {
                live_users: 0,
                today_visitors: 0,
                today_new: 0,
                today_returning: 0,
                today_sessions: 0,
                today_pageviews: 0,
                today_product_views: 0,
                today_add_cart: 0,
                today_checkout: 0,
                today_purchases: 0,
                feed: []
            },
            lastUpdated: '-',
            init() {
                this.fetchData();
                setInterval(() => this.fetchData(), 10000); // 10 seconds polling
            },
            fetchData() {
                fetch('{{ route('admin.analytics.live_data') }}')
                    .then(res => res.json())
                    .then(res => {
                        this.data = res;
                        const now = new Date();
                        this.lastUpdated = now.toLocaleTimeString();
                        // Re-initialize lucide icons for new elements
                        setTimeout(() => lucide.createIcons(), 50);
                    })
                    .catch(err => console.error('Error fetching live data:', err));
            },
            getIcon(type) {
                switch(type) {
                    case 'page_view': return 'file-text';
                    case 'product_view': return 'eye';
                    case 'add_to_cart': return 'shopping-cart';
                    case 'checkout_start': return 'credit-card';
                    case 'purchase': return 'check-circle';
                    case 'search': return 'search';
                    default: return 'activity';
                }
            },
            getIconBg(type) {
                switch(type) {
                    case 'page_view': return 'bg-gray-100 dark:bg-gray-800';
                    case 'product_view': return 'bg-blue-100 dark:bg-blue-900/30';
                    case 'add_to_cart': return 'bg-yellow-100 dark:bg-yellow-900/30';
                    case 'checkout_start': return 'bg-orange-100 dark:bg-orange-900/30';
                    case 'purchase': return 'bg-green-100 dark:bg-green-900/30';
                    case 'search': return 'bg-purple-100 dark:bg-purple-900/30';
                    default: return 'bg-gray-100 dark:bg-gray-800';
                }
            },
            getIconColor(type) {
                switch(type) {
                    case 'page_view': return 'text-gray-600 dark:text-gray-400';
                    case 'product_view': return 'text-blue-600 dark:text-blue-400';
                    case 'add_to_cart': return 'text-yellow-600 dark:text-yellow-400';
                    case 'checkout_start': return 'text-orange-600 dark:text-orange-400';
                    case 'purchase': return 'text-green-600 dark:text-green-400';
                    case 'search': return 'text-purple-600 dark:text-purple-400';
                    default: return 'text-gray-600 dark:text-gray-400';
                }
            },
            getEventText(event) {
                switch(event.type) {
                    case 'page_view': return 'viewed ' + event.url;
                    case 'product_view': return 'viewed product: ' + (event.product_name || 'Unknown');
                    case 'add_to_cart': return 'added product to cart: ' + (event.product_name || 'Unknown');
                    case 'checkout_start': return 'started checkout';
                    case 'purchase': return 'completed a purchase!';
                    case 'search': return 'searched for a product';
                    default: return 'performed ' + event.type;
                }
            }
        }
    }
</script>
@endpush
