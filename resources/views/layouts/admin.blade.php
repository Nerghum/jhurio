<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LuxeCart</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #e5e7eb;
            border-radius: 20px;
        }
        .dark .custom-scrollbar::-webkit-scrollbar-thumb {
            background-color: #374151;
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-200 h-screen flex overflow-hidden" x-data="{ mobileMenuOpen: false }">

    <!-- Mobile Backdrop -->
    <div x-show="mobileMenuOpen" 
         x-transition.opacity 
         @click="mobileMenuOpen = false" 
         class="fixed inset-0 bg-black/50 z-40 md:hidden"
         style="display: none;"></div>

    <!-- Sidebar -->
    <aside class="w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col transition-transform duration-300 fixed inset-y-0 left-0 z-50 md:relative transform" :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'">
        <div class="h-16 flex items-center px-6 border-b border-gray-200 dark:border-gray-700">
            <a href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('assets/images/logo.png') }}" alt="Jhurio" class="h-8 w-auto">
            </a>
        </div>
                <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto custom-scrollbar">
            <!-- Dashboard -->
            <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('admin.dashboard') ? 'bg-secondary/10 dark:bg-secondary/20 text-primary font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-gray-100 font-medium' }} rounded-xl transition-all">
                <i data-lucide="layout-dashboard" class="w-5 h-5 mr-3 {{ request()->routeIs('admin.dashboard') ? 'text-primary' : '' }}"></i>
                <span>Dashboard</span>
            </a>
            
            <!-- Catalog Group -->
            @php $isCatalogActive = request()->routeIs('admin.products.*', 'admin.categories.*', 'admin.tags.*', 'admin.bundles.*'); @endphp
            <div x-data="{ open: {{ $isCatalogActive ? 'true' : 'false' }} }" class="relative">
                 
                <div @click="open = !open" class="flex items-center justify-between px-4 py-3 {{ $isCatalogActive ? 'bg-secondary/5 dark:bg-secondary/10 text-primary font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-gray-100 font-medium' }} rounded-xl transition-all cursor-pointer">
                    <div class="flex items-center">
                        <i data-lucide="shopping-bag" class="w-5 h-5 mr-3 {{ $isCatalogActive ? 'text-primary' : '' }}"></i>
                        <span>Catalog</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-300 {{ $isCatalogActive ? 'text-primary' : 'text-gray-400' }}" :class="{ 'rotate-180': open }"></i>
                </div>
                
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="pl-11 pr-4 py-2 space-y-1 mt-1 bg-gray-50/50 dark:bg-gray-800/50 rounded-xl">
                    <a href="{{ route('admin.products.index') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.products.*') ? 'text-primary font-medium bg-white dark:bg-gray-700 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-gray-700' }}">
                        Products
                    </a>
                    <a href="{{ route('admin.categories.index') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.categories.*') ? 'text-primary font-medium bg-white dark:bg-gray-700 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-gray-700' }}">
                        Categories
                    </a>
                    <a href="{{ route('admin.tags.index') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.tags.*') ? 'text-primary font-medium bg-white dark:bg-gray-700 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-gray-700' }}">
                        Tags
                    </a>
                    <a href="{{ route('admin.bundles.index') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.bundles.*') ? 'text-primary font-medium bg-white dark:bg-gray-700 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-gray-700' }}">
                        Product Bundles
                    </a>
                </div>
            </div>
            
            <!-- Storefront Group -->
            @php $isStorefrontActive = request()->routeIs('admin.banners.*', 'admin.featured_products.*', 'admin.homepage-builder.*'); @endphp
            <div x-data="{ open: {{ $isStorefrontActive ? 'true' : 'false' }} }" class="relative">
                 
                <div @click="open = !open" class="flex items-center justify-between px-4 py-3 {{ $isStorefrontActive ? 'bg-secondary/5 dark:bg-secondary/10 text-primary font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-gray-100 font-medium' }} rounded-xl transition-all cursor-pointer">
                    <div class="flex items-center">
                        <i data-lucide="layout" class="w-5 h-5 mr-3 {{ $isStorefrontActive ? 'text-primary' : '' }}"></i>
                        <span>Storefront</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-300 {{ $isStorefrontActive ? 'text-primary' : 'text-gray-400' }}" :class="{ 'rotate-180': open }"></i>
                </div>
                
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="pl-11 pr-4 py-2 space-y-1 mt-1 bg-gray-50/50 dark:bg-gray-800/50 rounded-xl">
                    <a href="{{ route('admin.homepage-builder.index') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.homepage-builder.*') ? 'text-primary font-medium bg-white dark:bg-gray-700 shadow-sm flex items-center' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-gray-700 flex items-center' }}">
                        Page Builder
                        @if(request()->routeIs('admin.homepage-builder.*'))
                            <i data-lucide="layers" class="w-3 h-3 ml-2 text-primary"></i>
                        @endif
                    </a>
                </div>
            </div>

            <!-- Media -->
            <a href="{{ route('admin.media.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('admin.media.*') ? 'bg-secondary/10 dark:bg-secondary/20 text-primary font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-gray-100 font-medium' }} rounded-xl transition-all">
                <i data-lucide="image" class="w-5 h-5 mr-3 {{ request()->routeIs('admin.media.*') ? 'text-primary' : '' }}"></i>
                <span>Media Library</span>
            </a>
            
            <!-- Orders -->
            <a href="{{ route('admin.orders.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('admin.orders.*') ? 'bg-secondary/10 dark:bg-secondary/20 text-primary font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-gray-100 font-medium' }} rounded-xl transition-all">
                <i data-lucide="shopping-cart" class="w-5 h-5 mr-3 {{ request()->routeIs('admin.orders.*') ? 'text-primary' : '' }}"></i>
                <span>Orders</span>
            </a>
            
            <a href="{{ route('admin.abandoned-carts.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('admin.abandoned-carts.*') ? 'bg-secondary/10 dark:bg-secondary/20 text-primary font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-gray-100 font-medium' }} rounded-xl transition-all">
                <i data-lucide="shopping-bag" class="w-5 h-5 mr-3 {{ request()->routeIs('admin.abandoned-carts.*') ? 'text-primary' : '' }}"></i>
                <span>Abandoned Carts</span>
            </a>
            
            <!-- Analytics Group -->
            @php $isAnalyticsActive = request()->routeIs('admin.customers.*', 'admin.analytics.*'); @endphp
            <div x-data="{ open: {{ $isAnalyticsActive ? 'true' : 'false' }} }" class="relative">
                 
                <div @click="open = !open" class="flex items-center justify-between px-4 py-3 {{ $isAnalyticsActive ? 'bg-secondary/5 dark:bg-secondary/10 text-primary font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-gray-100 font-medium' }} rounded-xl transition-all cursor-pointer">
                    <div class="flex items-center">
                        <i data-lucide="pie-chart" class="w-5 h-5 mr-3 {{ $isAnalyticsActive ? 'text-primary' : '' }}"></i>
                        <span>Analytics</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-300 {{ $isAnalyticsActive ? 'text-primary' : 'text-gray-400' }}" :class="{ 'rotate-180': open }"></i>
                </div>
                
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="pl-11 pr-4 py-2 space-y-1 mt-1 bg-gray-50/50 dark:bg-gray-800/50 rounded-xl">
                    <a href="{{ route('admin.customers.index') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.customers.*') ? 'text-primary font-medium bg-white dark:bg-gray-700 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-gray-700' }}">
                        Customers
                    </a>
                    
                    <div class="px-3 pt-3 pb-1">
                        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Tracking</p>
                    </div>
                    
                    <a href="{{ route('admin.analytics.dashboard') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.analytics.dashboard') ? 'text-primary font-medium bg-white dark:bg-gray-700 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-gray-700' }}">
                        Live Tracking
                    </a>
                    <a href="{{ route('admin.analytics.overview') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.analytics.overview') ? 'text-primary font-medium bg-white dark:bg-gray-700 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-gray-700' }}">
                        Overview
                    </a>
                    <a href="{{ route('admin.analytics.products') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.analytics.products') ? 'text-primary font-medium bg-white dark:bg-gray-700 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-gray-700' }}">
                        Products
                    </a>
                    <a href="{{ route('admin.analytics.geography') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.analytics.geography') ? 'text-primary font-medium bg-white dark:bg-gray-700 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-gray-700' }}">
                        Geography
                    </a>
                    <a href="{{ route('admin.analytics.traffic') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.analytics.traffic') ? 'text-primary font-medium bg-white dark:bg-gray-700 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-gray-700' }}">
                        Traffic Sources
                    </a>
                    <a href="{{ route('admin.analytics.funnel') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.analytics.funnel') ? 'text-primary font-medium bg-white dark:bg-gray-700 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-gray-700' }}">
                        Conversion Funnel
                    </a>
                </div>
            </div>
            
            <!-- Messages -->
            <a href="{{ route('admin.contact.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('admin.contact.*') ? 'bg-secondary/10 dark:bg-secondary/20 text-primary font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-gray-100 font-medium' }} rounded-xl transition-all">
                <i data-lucide="mail" class="w-5 h-5 mr-3 {{ request()->routeIs('admin.contact.*') ? 'text-primary' : '' }}"></i>
                <span>Messages</span>
                @php
                    $unreadCount = \App\Models\ContactMessage::where('is_read', false)->count();
                @endphp
                @if($unreadCount > 0)
                    <span class="ml-auto bg-primary text-white text-xs font-bold px-2 py-0.5 rounded-full">{{ $unreadCount }}</span>
                @endif
            </a>
            
            <a href="{{ route('admin.users.index') }}" class="flex items-center px-4 py-3 {{ request()->routeIs('admin.users.*') ? 'bg-secondary/10 dark:bg-secondary/20 text-primary font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-gray-100 font-medium' }} rounded-xl transition-all">
                <i data-lucide="users" class="w-5 h-5 mr-3 {{ request()->routeIs('admin.users.*') ? 'text-primary' : '' }}"></i>
                <span>Users & Roles</span>
            </a>

            <!-- Settings Group -->
            @php $isSettingsActive = request()->routeIs('admin.settings.*'); @endphp
            <div x-data="{ open: {{ $isSettingsActive ? 'true' : 'false' }} }" class="relative">
                 
                <div @click="open = !open" class="flex items-center justify-between px-4 py-3 {{ $isSettingsActive ? 'bg-secondary/5 dark:bg-secondary/10 text-primary font-semibold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700/50 hover:text-gray-900 dark:hover:text-gray-100 font-medium' }} rounded-xl transition-all cursor-pointer">
                    <div class="flex items-center">
                        <i data-lucide="settings" class="w-5 h-5 mr-3 {{ $isSettingsActive ? 'text-primary' : '' }}"></i>
                        <span>Settings</span>
                    </div>
                    <i data-lucide="chevron-down" class="w-4 h-4 transition-transform duration-300 {{ $isSettingsActive ? 'text-primary' : 'text-gray-400' }}" :class="{ 'rotate-180': open }"></i>
                </div>
                
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="pl-11 pr-4 py-2 space-y-1 mt-1 bg-gray-50/50 dark:bg-gray-800/50 rounded-xl">
                    <a href="{{ route('admin.settings.api') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.settings.api') ? 'text-primary font-medium bg-white dark:bg-gray-700 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-gray-700' }}">
                        API Settings
                    </a>
                    <a href="{{ route('admin.settings.tracking') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.settings.tracking') ? 'text-primary font-medium bg-white dark:bg-gray-700 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-gray-700' }}">
                        Tracking Config
                    </a>
                    <a href="{{ route('admin.settings.notifications') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.settings.notifications') ? 'text-primary font-medium bg-white dark:bg-gray-700 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-gray-700' }}">
                        Push Notifications
                    </a>
                    <a href="{{ route('admin.settings.checkout') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.settings.checkout') ? 'text-primary font-medium bg-white dark:bg-gray-700 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-gray-700' }}">
                        Checkout
                    </a>
                    <a href="{{ route('admin.delivery-methods.index') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.delivery-methods.*') ? 'text-primary font-medium bg-white dark:bg-gray-700 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-gray-700' }}">
                        Delivery Methods
                    </a>
                    <a href="{{ route('admin.settings.marketing') }}" class="block px-3 py-2 text-sm rounded-lg transition-colors {{ request()->routeIs('admin.settings.marketing') ? 'text-primary font-medium bg-white dark:bg-gray-700 shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200 hover:bg-white dark:hover:bg-gray-700' }}">
                        Marketing & Popups
                    </a>
                </div>
            </div>
        </nav>
        <div class="p-4 border-t border-gray-200 dark:border-gray-700">
            <a href="/" class="flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                <i data-lucide="external-link" class="w-4 h-4 mr-2"></i>
                View Store
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Header -->
        <header class="h-16 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between px-4 sm:px-6">
            <div class="flex items-center">
                <button @click="mobileMenuOpen = true" class="mr-3 md:hidden text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                    <i data-lucide="menu" class="w-6 h-6"></i>
                </button>
                <h1 class="text-xl font-semibold text-gray-800 dark:text-white truncate max-w-[200px] sm:max-w-none">@yield('header', 'Dashboard')</h1>
            </div>
            <div class="flex items-center space-x-4">
                <button class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition-colors">
                    <i data-lucide="bell" class="w-5 h-5"></i>
                </button>
                <div class="h-8 w-8 rounded-xl bg-primary flex items-center justify-center text-white font-bold shadow-sm">
                    A
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 dark:bg-gray-900 p-6">
            @yield('content')
        </main>
    </div>

    @include('admin.components.media-modal')

    <script>
        lucide.createIcons();
    </script>
    
    @php
        $firebaseApiKey = \App\Models\Setting::get('firebase_api_key');
    @endphp
    @if($firebaseApiKey)
    <!-- Firebase SDKs -->
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
    <script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js"></script>
    <script>
        const firebaseConfig = {
            apiKey: "{{ \App\Models\Setting::get('firebase_api_key') }}",
            authDomain: "{{ \App\Models\Setting::get('firebase_auth_domain') }}",
            projectId: "{{ \App\Models\Setting::get('firebase_project_id') }}",
            storageBucket: "{{ \App\Models\Setting::get('firebase_storage_bucket') }}",
            messagingSenderId: "{{ \App\Models\Setting::get('firebase_messaging_sender_id') }}",
            appId: "{{ \App\Models\Setting::get('firebase_app_id') }}"
        };

        if (!firebase.apps.length) {
            firebase.initializeApp(firebaseConfig);
        }
        
        const messaging = firebase.messaging();

        // Request permission
        function requestFirebasePermission() {
            Notification.requestPermission().then((permission) => {
                if (permission === 'granted') {
                    console.log('Notification permission granted.');
                    messaging.getToken({ vapidKey: "{{ \App\Models\Setting::get('firebase_vapid_key') }}" })
                        .then((currentToken) => {
                            if (currentToken) {
                                saveTokenToServer(currentToken);
                            }
                        }).catch((err) => {
                            console.log('An error occurred while retrieving token. ', err);
                        });
                }
            });
        }

        function saveTokenToServer(token) {
            fetch("{{ route('admin.settings.fcm_token') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ token: token })
            });
        }

        // Run on load
        requestFirebasePermission();

        messaging.onMessage((payload) => {
            console.log('Message received. ', payload);
            if(Notification.permission === 'granted') {
                new Notification(payload.notification.title, {
                    body: payload.notification.body,
                    icon: '/icon.png'
                });
            }
        });
    </script>
    @endif
    
    @stack('scripts')
</body>
</html>
