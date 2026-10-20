<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Premium eCommerce - @yield('title', 'Home')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    

    <!-- Analytics Tracker -->
    <script src="{{ asset('js/tracker.js') }}"></script>
    
    <style>
        [x-cloak] { display: none !important; }
        
        body { font-family: 'Outfit', sans-serif; }
        .glassmorphism {
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        .dark .glassmorphism {
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>
    @include('components.tracking-scripts')
</head>
<body class="bg-gray-50 dark:bg-darker text-gray-800 dark:text-gray-200 transition-colors duration-300" x-data="cartState()">

    <!-- Navigation -->
    <nav class="fixed w-full z-50 glassmorphism shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center cursor-pointer">
                    <a href="/" class="flex-shrink-0 flex items-center">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Jhurio" class="h-10 w-auto">
                    </a>
                </div>
                
                <!-- Desktop Navigation -->
                <div class="hidden md:flex flex-1 items-center justify-between ml-8">
                    
                    <!-- Search Component -->
                    <div class="flex-1 max-w-2xl pr-8 relative" x-data="searchComponent()" @click.away="isOpen = false">
                        <div class="flex items-center w-full">
                            <div class="relative w-full">
                                <input type="text" x-model="query" @focus="isOpen = true" @input.debounce.300ms="fetchResults" placeholder="Search products..." class="w-full h-10 pl-10 pr-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                                <button @click="isOpen = true" class="absolute left-0 top-0 h-10 w-10 flex items-center justify-center text-gray-500 hover:text-primary transition pointer-events-none">
                                    <i data-lucide="search" class="w-5 h-5"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Dropdown -->
                        <div x-show="isOpen && (query.length > 0 || isLoading || suggested.length > 0)" x-transition x-cloak class="absolute left-0 right-8 mt-2 bg-white dark:bg-gray-800 rounded-2xl shadow-xl border border-gray-100 dark:border-gray-700 overflow-hidden z-50 flex max-h-[32rem]">
                            
                            <!-- Left: Search Results -->
                            <div class="w-1/2 flex flex-col border-r border-gray-100 dark:border-gray-700">
                                <div class="p-3 bg-gray-50 dark:bg-gray-900 border-b border-gray-100 dark:border-gray-700">
                                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Search Results</h3>
                                </div>
                                
                                <div x-show="isLoading" class="p-6 text-center text-gray-500">
                                    <i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto"></i>
                                </div>

                                <div x-show="!isLoading && results.length === 0 && query.length > 0" class="p-6 text-center text-gray-500 text-sm flex flex-col items-center justify-center h-full">
                                    <div class="mb-2">No products found for "<span x-text="query" class="font-bold"></span>".</div>
                                    <template x-if="didYouMean">
                                        <div class="mt-2 p-3 bg-primary/10 rounded-xl text-primary font-medium text-xs border border-primary/20">
                                            Did you mean: <a href="#" @click.prevent="query = didYouMean; fetchResults()" class="font-bold underline hover:text-secondary transition" x-text="didYouMean"></a>?
                                        </div>
                                    </template>
                                </div>
                                <div x-show="!isLoading && results.length === 0 && query.length === 0" class="p-6 text-center text-gray-500 text-sm">
                                    Start typing to search...
                                </div>

                                <ul x-show="!isLoading && results.length > 0" class="flex-1 overflow-y-auto">
                                    <template x-for="item in results" :key="item.id">
                                        <li>
                                            <a :href="item.url" class="flex items-center gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors border-b border-gray-50 dark:border-gray-700/50 last:border-0">
                                                <img :src="item.image" class="w-10 h-10 rounded-lg object-cover bg-gray-100 dark:bg-gray-900 border border-gray-100 dark:border-gray-700">
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white truncate" x-text="item.name"></h4>
                                                    <div class="flex items-center gap-2 mt-0.5">
                                                        <span class="text-xs font-bold text-accent" x-text="item.price + ' BDT'"></span>
                                                        <template x-if="item.compare_at_price">
                                                            <span class="text-[10px] text-gray-400 line-through" x-text="item.compare_at_price + ' BDT'"></span>
                                                        </template>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                    </template>
                                </ul>
                                
                                <div x-show="!isLoading && results.length > 0" class="p-2 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 mt-auto">
                                    <a :href="'/shop?q=' + query" class="block w-full text-center text-sm text-primary font-bold hover:text-secondary py-1.5 transition-colors">See all results</a>
                                </div>
                            </div>

                            <!-- Right: Suggested Products -->
                            <div class="w-1/2 flex flex-col bg-gray-50/50 dark:bg-gray-800/50">
                                <div class="p-3 bg-gray-50 dark:bg-gray-900 border-b border-gray-100 dark:border-gray-700">
                                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Suggested Products</h3>
                                </div>
                                <div class="p-4 overflow-y-auto">
                                    <div class="grid grid-cols-2 gap-3">
                                        <template x-for="item in suggested" :key="'sug_'+item.id">
                                            <a :href="item.url" class="block bg-white dark:bg-gray-800 p-2 rounded-xl border border-gray-100 dark:border-gray-700 hover:shadow-md hover:border-primary/30 transition-all group">
                                                <div class="aspect-square rounded-lg overflow-hidden mb-2 bg-gray-100 dark:bg-gray-900">
                                                    <img :src="item.image" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                                </div>
                                                <h4 class="text-xs font-semibold text-gray-900 dark:text-white truncate" x-text="item.name"></h4>
                                                <div class="mt-1 flex items-center justify-between">
                                                    <span class="text-xs font-bold text-accent" x-text="item.price + ' BDT'"></span>
                                                </div>
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex space-x-8 items-center shrink-0">
                        <div class="flex space-x-6 items-center">
                            <a href="/" class="text-gray-600 dark:text-gray-300 hover:text-primary dark:hover:text-primary transition font-medium">Home</a>
                            <a href="{{ route('shop.index') }}" class="text-gray-600 dark:text-gray-300 hover:text-primary dark:hover:text-primary transition font-medium">Shop</a>
                            <a href="{{ route('about') }}" class="text-gray-600 dark:text-gray-300 hover:text-primary dark:hover:text-primary transition font-medium">About</a>
                            <a href="{{ route('contact.show') }}" class="text-gray-600 dark:text-gray-300 hover:text-primary dark:hover:text-primary transition font-medium">Contact</a>
                        </div>
                        
                        <div class="h-6 w-px bg-gray-300 dark:bg-gray-700"></div>

                        <div class="flex items-center gap-4">


                            @auth
                            {{-- Logged-in Customer Greeting --}}
                            <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                <button @click="open = !open"
                                        class="flex items-center gap-2.5 group">
                                    <!-- Avatar circle -->
                                    <div class="w-8 h-8 rounded-full bg-primary/10 border-2 border-primary/20 group-hover:border-primary/50 flex items-center justify-center transition-all flex-shrink-0">
                                        <span class="text-xs font-bold text-primary">{{ strtoupper(substr(auth()->user()->name ?? auth()->user()->phone, 0, 1)) }}</span>
                                    </div>
                                    <!-- Greeting text -->
                                    <div class="hidden lg:block text-left">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 leading-none mb-0.5">Welcome back 👋</p>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white leading-none truncate max-w-[100px]">
                                            {{ auth()->user()->name !== 'Customer' ? auth()->user()->name : auth()->user()->phone }}
                                        </p>
                                    </div>
                                    <i data-lucide="chevron-down" class="w-3.5 h-3.5 text-gray-400 transition-transform hidden lg:block" :class="open ? 'rotate-180' : ''"></i>
                                </button>

                                <!-- Dropdown -->
                                <div x-show="open"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 translate-y-1 scale-95"
                                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                     x-transition:leave-end="opacity-0 translate-y-1 scale-95"
                                     class="absolute right-0 top-full mt-2 w-52 bg-white dark:bg-gray-800 rounded-2xl shadow-xl shadow-black/10 border border-gray-100 dark:border-gray-700 overflow-hidden z-50"
                                     x-cloak>
                                    <!-- User info header -->
                                    <div class="px-4 py-3 bg-gradient-to-r from-primary/5 to-secondary/5 border-b border-gray-100 dark:border-gray-700">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Signed in as</p>
                                        <p class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ auth()->user()->phone }}</p>
                                    </div>
                                    <div class="py-1">
                                        <a href="{{ route('account.orders') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                            <i data-lucide="package" class="w-4 h-4 text-gray-400"></i>
                                            My Orders
                                        </a>
                                        <a href="#" class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                            <i data-lucide="map-pin" class="w-4 h-4 text-gray-400"></i>
                                            Addresses
                                        </a>
                                    </div>
                                    <div class="border-t border-gray-100 dark:border-gray-700 py-1">
                                        <form action="{{ route('account.logout') }}" method="POST">
                                            @csrf
                                            <button type="submit" class="flex items-center gap-3 w-full px-4 py-2.5 text-sm text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                                <i data-lucide="log-out" class="w-4 h-4"></i>
                                                Sign Out
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @else
                            {{-- Guest: Show Login Link --}}
                            <a href="{{ route('account.login') }}"
                               class="flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-300 hover:text-primary dark:hover:text-primary transition-colors">
                                <i data-lucide="user" class="w-5 h-5"></i>
                                <span class="hidden lg:inline">Login</span>
                            </a>
                            @endauth

                            <!-- Cart -->
                            <a href="#" @click.prevent="isOpen = true" class="text-gray-600 dark:text-gray-300 hover:text-primary dark:hover:text-primary transition relative group">
                                <i data-lucide="shopping-cart" class="w-5 h-5"></i>
                                <span x-text="totalItems" x-show="totalItems > 0" x-cloak class="absolute -top-2 -right-2 bg-secondary text-white text-[10px] font-bold h-4 w-4 flex items-center justify-center rounded-xl group-hover:scale-110 transition-transform">0</span>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Mobile Buttons -->
                <div class="md:hidden flex items-center space-x-1">
                    <button @click="mobileSearchOpen = !mobileSearchOpen" class="text-gray-600 dark:text-gray-300 focus:outline-none p-2">
                        <i data-lucide="search" class="w-6 h-6"></i>
                    </button>
                    <button @click="mobileMenuOpen = true" class="text-gray-600 dark:text-gray-300 focus:outline-none p-2">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Search Bar (Expandable) -->
            <div x-show="mobileSearchOpen" x-transition x-cloak class="md:hidden pb-4" x-data="searchComponent()">
                <div class="relative w-full">
                    <input type="text" x-model="query" @input.debounce.300ms="fetchResults" placeholder="Search products, brands..." class="w-full h-10 pl-10 pr-10 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm focus:ring-2 focus:ring-primary focus:border-transparent transition-all" x-ref="searchInput">
                    <div class="absolute left-0 top-0 h-10 w-10 flex items-center justify-center text-gray-500 pointer-events-none">
                        <i data-lucide="search" class="w-5 h-5"></i>
                    </div>
                    <button x-show="query.length > 0" @click="query = ''; fetchResults()" class="absolute right-0 top-0 h-10 w-10 flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <i data-lucide="x-circle" class="w-4 h-4"></i>
                    </button>
                </div>
                
                <!-- Mobile Dropdown -->
                <div x-show="query.length > 0 || isLoading || suggested.length > 0" x-transition x-cloak class="absolute left-0 right-0 top-[100%] h-[calc(100vh-192px)] bg-white dark:bg-gray-800 shadow-xl border-t border-gray-100 dark:border-gray-700 overflow-hidden z-50 flex flex-col">
                    <div class="flex-1 overflow-y-auto">
                        <div x-show="isLoading" class="p-6 text-center text-gray-500">
                            <i data-lucide="loader-2" class="w-6 h-6 animate-spin mx-auto"></i>
                        </div>

                        <div x-show="!isLoading && results.length === 0 && query.length > 0" class="p-6 text-center text-gray-500 text-sm">
                            <div class="mb-2">No products found for "<span x-text="query" class="font-bold"></span>".</div>
                            <template x-if="didYouMean">
                                <div class="mt-2 p-3 bg-primary/10 rounded-xl text-primary font-medium text-xs border border-primary/20">
                                    Did you mean: <a href="#" @click.prevent="query = didYouMean; fetchResults()" class="font-bold underline hover:text-secondary transition" x-text="didYouMean"></a>?
                                </div>
                            </template>
                        </div>

                        <ul x-show="!isLoading && results.length > 0" class="divide-y divide-gray-50 dark:divide-gray-700/50">
                            <template x-for="item in results" :key="item.id">
                                <li>
                                    <a :href="item.url" class="flex items-center gap-3 p-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                        <img :src="item.image" class="w-12 h-12 rounded-lg object-cover bg-gray-100 dark:bg-gray-900 border border-gray-100 dark:border-gray-700">
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-sm font-semibold text-gray-900 dark:text-white truncate" x-text="item.name"></h4>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="text-sm font-bold text-accent" x-text="item.price + ' BDT'"></span>
                                                <template x-if="item.compare_at_price">
                                                    <span class="text-xs text-gray-400 line-through" x-text="item.compare_at_price + ' BDT'"></span>
                                                </template>
                                            </div>
                                        </div>
                                    </a>
                                </li>
                            </template>
                        </ul>
                    </div>
                    <div x-show="!isLoading && results.length > 0" class="p-2 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-100 dark:border-gray-700 mt-auto">
                        <a :href="'/shop?q=' + query" class="block w-full text-center text-sm text-primary font-bold hover:text-secondary py-2 transition-colors">See all results</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-20 min-h-screen">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-dark border-t border-gray-100 dark:border-gray-800 mt-20">
        <!-- Main Footer Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
                
                <!-- Brand & About -->
                <div class="space-y-6">
                    <a href="/" class="block">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="Jhurio" class="h-10 w-auto">
                    </a>
                    <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed">
                        Your premium destination for the finest curated products. We deliver excellence and quality right to your doorstep.
                    </p>
                    <div class="flex items-center space-x-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center text-gray-500 hover:bg-primary hover:text-white transition-colors group">
                            <i data-lucide="facebook" class="w-4 h-4 group-hover:scale-110 transition-transform"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center text-gray-500 hover:bg-primary hover:text-white transition-colors group">
                            <i data-lucide="instagram" class="w-4 h-4 group-hover:scale-110 transition-transform"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-50 dark:bg-gray-800 flex items-center justify-center text-gray-500 hover:bg-primary hover:text-white transition-colors group">
                            <i data-lucide="twitter" class="w-4 h-4 group-hover:scale-110 transition-transform"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Links -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-6">Quick Links</h3>
                    <ul class="space-y-4">
                        <li><a href="{{ route('shop.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary dark:hover:text-primary transition-colors">Shop All Products</a></li>
                        <li><a href="{{ route('about') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary dark:hover:text-primary transition-colors">Our Story</a></li>
                        <li><a href="#" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary dark:hover:text-primary transition-colors">Flash Sales</a></li>
                        <li><a href="#" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary dark:hover:text-primary transition-colors">Top Categories</a></li>
                    </ul>
                </div>

                <!-- Customer Service -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-6">Customer Service</h3>
                    <ul class="space-y-4">
                        <li><a href="#" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary dark:hover:text-primary transition-colors">Track Your Order</a></li>
                        <li><a href="#" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary dark:hover:text-primary transition-colors">Return Policy</a></li>
                        <li><a href="#" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary dark:hover:text-primary transition-colors">Shipping Info</a></li>
                        <li><a href="#" class="text-sm text-gray-500 dark:text-gray-400 hover:text-primary dark:hover:text-primary transition-colors">FAQs & Support</a></li>
                    </ul>
                </div>

                <!-- Newsletter & Contact -->
                <div>
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-6">Stay in the Loop</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                        Subscribe to get special offers, free giveaways, and updates.
                    </p>
                    <form class="mb-6 relative">
                        <input type="email" placeholder="Enter your email" class="w-full h-11 pl-4 pr-12 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-sm focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                        <button type="submit" class="absolute right-1 top-1 bottom-1 px-3 bg-primary text-white rounded-lg hover:bg-secondary transition-colors flex items-center justify-center">
                            <i data-lucide="arrow-right" class="w-4 h-4"></i>
                        </button>
                    </form>
                    <div class="space-y-2">
                        <a href="mailto:support@jhurio.com" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-primary transition-colors">
                            <i data-lucide="mail" class="w-4 h-4"></i> support@jhurio.com
                        </a>
                        <a href="tel:+8801234567890" class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 hover:text-primary transition-colors">
                            <i data-lucide="phone" class="w-4 h-4"></i> +880 1234-567890
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="border-t border-gray-100 dark:border-gray-800 pb-24 md:pb-0">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col md:flex-row justify-between items-center gap-4">
                <p class="text-sm text-gray-500 dark:text-gray-400 text-center md:text-left">
                    &copy; {{ date('Y') }} LuxeCart. All rights reserved.
                </p>
                <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                    <a href="#" class="hover:text-primary transition-colors">Privacy Policy</a>
                    <span class="w-1 h-1 rounded-full bg-gray-300 dark:bg-gray-600"></span>
                    <a href="#" class="hover:text-primary transition-colors">Terms of Service</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Bottom Menu -->
    <div class="md:hidden fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 flex justify-around items-center py-2 z-50 shadow-[0_-4px_10px_rgba(0,0,0,0.05)] pb-[env(safe-area-inset-bottom)]">
        <a href="/" class="flex flex-col items-center p-2 text-gray-500 hover:text-primary transition-colors {{ request()->is('/') ? 'text-primary' : '' }}">
            <i data-lucide="home" class="w-6 h-6 mb-1"></i>
            <span class="text-[10px] font-medium">Home</span>
        </a>
        <a href="{{ route('shop.index') }}" class="flex flex-col items-center p-2 text-gray-500 hover:text-primary transition-colors {{ request()->routeIs('shop.index') ? 'text-primary' : '' }}">
            <i data-lucide="layout-grid" class="w-6 h-6 mb-1"></i>
            <span class="text-[10px] font-medium">Shop</span>
        </a>
        <a href="#" @click.prevent="isOpen = true" class="flex flex-col items-center p-2 text-gray-500 hover:text-primary transition-colors relative">
            <div class="relative">
                <i data-lucide="shopping-cart" class="w-6 h-6 mb-1"></i>
                <span x-text="totalItems" x-show="totalItems > 0" x-cloak class="absolute -top-1.5 -right-1.5 bg-secondary text-white text-[10px] font-bold h-4 w-4 flex items-center justify-center rounded-xl">0</span>
            </div>
            <span class="text-[10px] font-medium">Cart</span>
        </a>
        <a href="#" class="flex flex-col items-center p-2 text-gray-500 hover:text-primary transition-colors">
            <i data-lucide="user" class="w-6 h-6 mb-1"></i>
            <span class="text-[10px] font-medium">Account</span>
        </a>
    </div>

    <!-- Mobile Slide Menu Drawer -->
    <div x-show="mobileMenuOpen" class="fixed inset-0 z-50 md:hidden" x-cloak>
        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition-opacity ease-linear duration-300" 
             x-transition:enter-start="opacity-0" 
             x-transition:enter-end="opacity-100" 
             x-transition:leave="transition-opacity ease-linear duration-300" 
             x-transition:leave-start="opacity-100" 
             x-transition:leave-end="opacity-0" 
             class="fixed inset-0 bg-black/50" @click="mobileMenuOpen = false"></div>

        <div x-show="mobileMenuOpen" 
             x-transition:enter="transition ease-in-out duration-300 transform" 
             x-transition:enter-start="translate-x-full" 
             x-transition:enter-end="translate-x-0" 
             x-transition:leave="transition ease-in-out duration-300 transform" 
             x-transition:leave-start="translate-x-0" 
             x-transition:leave-end="translate-x-full" 
             class="fixed inset-y-0 right-0 max-w-xs w-full bg-white dark:bg-dark shadow-xl flex flex-col h-full z-50">
            
            <div class="flex items-center justify-between p-4 border-b border-gray-100 dark:border-gray-800">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">Menu</h2>
                <button @click="mobileMenuOpen = false" class="text-gray-500 hover:text-gray-900 dark:hover:text-white p-2 bg-gray-50 dark:bg-gray-800 rounded-full">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>
            </div>
            
            <div class="flex-1 overflow-y-auto p-4 space-y-2">
                <a href="/" class="flex items-center gap-3 px-4 py-3 rounded-xl text-base font-medium text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-800">
                    <i data-lucide="home" class="w-5 h-5 text-gray-500"></i> Home
                </a>
                <a href="{{ route('shop.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-base font-medium text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-800">
                    <i data-lucide="layout-grid" class="w-5 h-5 text-gray-500"></i> Shop
                </a>
                <a href="{{ route('about') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-base font-medium text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-800">
                    <i data-lucide="info" class="w-5 h-5 text-gray-500"></i> About
                </a>
                <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-xl text-base font-medium text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-800">
                    <i data-lucide="user" class="w-5 h-5 text-gray-500"></i> My Account
                </a>
                

            </div>
            
            <div class="p-4 border-t border-gray-100 dark:border-gray-800 mb-[env(safe-area-inset-bottom)]">
                <a href="/admin" class="block w-full text-center px-5 py-3 rounded-xl bg-primary text-white font-medium hover:bg-secondary transition-colors">Admin Portal</a>
            </div>
        </div>
    </div>

    @include('components.side-cart')
    @include('components.checkout-modal')
    @include('components.quickview-modal')
    @include('components.exit-intent-popup')

    <script>
        lucide.createIcons();

        document.addEventListener('alpine:init', () => {
            Alpine.data('cartState', () => ({
                isOpen: false,
                mobileMenuOpen: false,
                mobileSearchOpen: false,
                items: [],
                totalItems: 0,
                totalPrice: 0,
                
                init() {
                    this.fetchCart();
                    
                    // Listen for global event to add item
                    window.addEventListener('add-to-cart', (e) => {
                        this.addToCart(e.detail);
                    });
                },
                
                async fetchCart() {
                    try {
                        const response = await fetch('/cart', {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        const data = await response.json();
                        this.items = data.items;
                        this.totalItems = data.totalItems;
                        this.totalPrice = data.totalPrice;
                    } catch (error) {
                        console.error('Error fetching cart:', error);
                    }
                },
                
                async addToCart(itemData) {
                    try {
                        const response = await fetch('/cart/add', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(itemData)
                        });
                        const data = await response.json();
                        this.items = data.items;
                        this.totalItems = data.totalItems;
                        this.totalPrice = data.totalPrice;
                        this.isOpen = true; // Open the side cart
                        
                        // Fire frontend AddToCart event
                        if (typeof window.trackEvent === 'function') {
                            window.trackEvent('AddToCart', {
                                content_ids: [itemData.product_id],
                                content_type: 'product',
                                value: this.totalPrice, // Note: This fires with cart total; can adjust to item price if preferred
                                currency: 'BDT'
                            });
                        }
                    } catch (error) {
                        console.error('Error adding to cart:', error);
                    }
                },
                
                async removeItem(id) {
                    try {
                        const response = await fetch('/cart/remove', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ id })
                        });
                        const data = await response.json();
                        this.items = data.items;
                        this.totalItems = data.totalItems;
                        this.totalPrice = data.totalPrice;
                    } catch (error) {
                        console.error('Error removing from cart:', error);
                    }
                },
                
                async updateQuantity(id, quantity) {
                    if (quantity < 1) return;
                    
                    try {
                        const response = await fetch('/cart/update', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ id, quantity })
                        });
                        const data = await response.json();
                        this.items = data.items;
                        this.totalItems = data.totalItems;
                        this.totalPrice = data.totalPrice;
                    } catch (error) {
                        console.error('Error updating quantity:', error);
                    }
                },
                
                checkoutCart() {
                    if (this.items.length === 0) return;
                    
                    window.dispatchEvent(new CustomEvent('open-checkout', {
                        detail: {
                            is_cart: true,
                            cart_items: this.items,
                            cart_total: this.totalPrice
                        }
                    }));
                    this.isOpen = false;
                }
            }));

            Alpine.data('searchComponent', () => ({
                isOpen: false,
                query: '',
                results: [],
                suggested: [],
                didYouMean: null,
                isLoading: false,

                init() {
                    this.$watch('isOpen', value => {
                        if (value && this.suggested.length === 0) {
                            this.fetchResults();
                        }
                    });
                },

                async fetchResults() {
                    this.isLoading = true;
                    this.didYouMean = null;
                    try {
                        const response = await fetch(`/search/ajax?q=${encodeURIComponent(this.query)}`);
                        const data = await response.json();
                        this.results = data.results || [];
                        if (data.suggested) this.suggested = data.suggested;
                        if (data.did_you_mean) this.didYouMean = data.did_you_mean;
                    } catch (error) {
                        console.error('Search error:', error);
                    } finally {
                        this.isLoading = false;
                        
                        // Fire frontend Search event
                        if (this.query.length > 2 && typeof window.trackEvent === 'function') {
                            window.trackEvent('Search', {
                                search_string: this.query
                            });
                        }
                    }
                }
            }));
        });

        window.toggleWishlist = async function(productId, btn) {
            try {
                const response = await fetch('/wishlist/toggle', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ product_id: productId })
                });
                const data = await response.json();
                
                if (data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }
                
                if (data.success) {
                    if (data.action === 'added') {
                        btn.querySelector('i').classList.add('fill-red-500', 'text-red-500');
                        btn.querySelector('i').classList.remove('text-gray-400');
                    } else {
                        btn.querySelector('i').classList.remove('fill-red-500', 'text-red-500');
                        btn.querySelector('i').classList.add('text-gray-400');
                        // if we are on wishlist page, remove the item
                        let item = document.getElementById('wishlist-item-' + productId);
                        if (item) item.remove();
                    }
                    showNotification(data.message);
                }
            } catch (e) {
                console.error(e);
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
