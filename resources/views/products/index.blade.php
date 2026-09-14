@extends('layouts.app')

@section('content')
<!-- Shop Banner -->
<div class="relative bg-primary overflow-hidden">
    <!-- Background Image -->
    <div class="absolute inset-0">
        <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?ixlib=rb-1.2.1&auto=format&fit=crop&w=1920&q=80" alt="Shop Banner" class="w-full h-full object-cover opacity-40 mix-blend-multiply">
    </div>
    
    <div class="relative max-w-7xl mx-auto py-16 px-4 sm:py-24 sm:px-6 lg:px-8 text-center">
        <h1 class="text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">Discover Our Collection</h1>
        <p class="mt-4 max-w-xl mx-auto text-xl text-gray-200">Shop the most authentic and premium items. Carefully curated for your lifestyle.</p>
    </div>
</div>

<div class="bg-gray-50 dark:bg-gray-900 py-12" x-data="shopFilters()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Mobile Filter Toggle -->
            <div class="lg:hidden flex justify-between items-center mb-4 bg-white dark:bg-gray-800 p-4 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700">
                <button @click="mobileFiltersOpen = true" class="flex items-center text-sm font-bold text-gray-700 dark:text-gray-200 hover:text-primary transition-colors">
                    <i data-lucide="filter" class="w-5 h-5 mr-2"></i> Filters
                </button>
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ $products->total() }} Results
                </div>
            </div>

            <!-- Left Sidebar (Filters) -->
            <div class="w-full lg:w-1/4 hidden lg:block" :class="{'hidden': !mobileFiltersOpen, 'fixed inset-0 z-50 bg-gray-900/50 block': mobileFiltersOpen, 'hidden lg:block': !mobileFiltersOpen}">
                
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-6 h-full overflow-y-auto" :class="{'fixed inset-y-0 left-0 w-4/5 max-w-sm z-50 rounded-none': mobileFiltersOpen}">
                    
                    <div class="lg:hidden flex justify-between items-center mb-6 pb-4 border-b border-gray-100 dark:border-gray-700">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Filters</h2>
                        <button @click="mobileFiltersOpen = false" class="text-gray-500 hover:text-red-500 bg-gray-100 dark:bg-gray-700 rounded-full p-2 transition-colors">
                            <i data-lucide="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <form action="{{ route('shop.index') }}" method="GET" class="space-y-8" id="filterForm">
                        <!-- Preserve sort param if exists -->
                        @if(request('sort'))
                            <input type="hidden" name="sort" value="{{ request('sort') }}">
                        @endif

                        <!-- Search -->
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3 uppercase tracking-wider">Search</h3>
                            <div class="relative">
                                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search products..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                                <i data-lucide="search" class="w-5 h-5 absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>

                        <hr class="border-gray-100 dark:border-gray-700">

                        <!-- Price Range -->
                        <div x-data="{ minPrice: {{ request('min') ?: 0 }}, maxPrice: {{ request('max') ?: 5000 }} }">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3 uppercase tracking-wider">Price (BDT)</h3>
                            
                            <div class="mb-6 px-2">
                                <input type="range" min="0" max="10000" step="100" x-model="maxPrice" class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-primary">
                                <div class="flex justify-between mt-2 text-xs text-gray-500 font-medium">
                                    <span>0 BDT</span>
                                    <span x-text="maxPrice + ' BDT'"></span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <input type="number" name="min" x-model="minPrice" placeholder="Min" class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                                <span class="text-gray-400">-</span>
                                <input type="number" name="max" x-model="maxPrice" placeholder="Max" class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                            </div>
                            <button type="submit" class="mt-4 w-full bg-primary hover:bg-secondary text-white font-bold py-2.5 px-4 rounded-xl text-sm transition-colors shadow-sm shadow-primary/30 flex items-center justify-center gap-2">
                                <i data-lucide="filter" class="w-4 h-4"></i> Apply Filter
                            </button>
                        </div>

                        <hr class="border-gray-100 dark:border-gray-700">

                        <!-- Categories -->
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3 uppercase tracking-wider">Categories</h3>
                            <div class="space-y-1">
                                <a href="{{ route('shop.index', array_merge(request()->except(['category', 'page']))) }}" 
                                   class="flex justify-between items-center px-3 py-2.5 rounded-xl transition-all {{ !request('category') ? 'bg-primary/10 text-primary font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-primary dark:hover:text-primary font-medium' }}">
                                    <span>All Categories</span>
                                </a>
                                @foreach($categories as $category)
                                    <a href="{{ route('shop.index', array_merge(request()->except('page'), ['category' => $category->slug])) }}" 
                                       class="flex justify-between items-center px-3 py-2.5 rounded-xl transition-all {{ request('category') == $category->slug ? 'bg-primary/10 text-primary font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-primary dark:hover:text-primary font-medium' }}">
                                        <span>{{ $category->name }}</span>
                                        <span class="bg-gray-100 dark:bg-gray-900 text-gray-500 text-xs py-1 px-2 rounded-lg font-bold">{{ $category->products()->count() }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        @if(request()->hasAny(['q', 'category', 'min', 'max']))
                            <div class="pt-2">
                                <a href="{{ route('shop.index') }}" class="w-full bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 text-accent flex justify-center items-center py-2.5 px-4 rounded-xl text-sm font-bold transition-colors">
                                    <i data-lucide="x-circle" class="w-4 h-4 mr-2"></i> Clear all filters
                                </a>
                            </div>
                        @endif
                    </form>
                </div>
            </div>

            <!-- Right Content (Product Grid) -->
            <div class="w-full lg:w-3/4">
                
                <!-- Top Toolbar -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 mb-6 flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Showing <span class="font-bold text-gray-900 dark:text-white">{{ $products->firstItem() ?? 0 }}-{{ $products->lastItem() ?? 0 }}</span> of <span class="font-bold text-gray-900 dark:text-white">{{ $products->total() }}</span> results
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <label for="sort" class="text-sm font-bold text-gray-700 dark:text-gray-300">Sort by:</label>
                        <select id="sort" onchange="window.location.href=this.value" class="text-sm font-medium rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary py-2.5 pl-3 pr-8 shadow-sm">
                            <option value="{{ route('shop.index', array_merge(request()->except(['sort', 'page']), ['sort' => 'latest'])) }}" {{ request('sort') == 'latest' || !request('sort') ? 'selected' : '' }}>Latest Arrivals</option>
                            <option value="{{ route('shop.index', array_merge(request()->except(['sort', 'page']), ['sort' => 'price_asc'])) }}" {{ request('sort') == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="{{ route('shop.index', array_merge(request()->except(['sort', 'page']), ['sort' => 'price_desc'])) }}" {{ request('sort') == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                        </select>
                    </div>
                </div>

                <!-- Products Grid -->
                @if($products->count() > 0)
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                        @foreach($products as $product)
                            <div class="h-full">
                                <x-product-card :product="$product" />
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    @if($products->hasPages())
                    <div class="mt-12 bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex justify-center w-full overflow-x-auto">
                        {{ $products->links() }}
                    </div>
                    @endif
                @else
                    <div class="text-center py-24 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-dashed border-gray-300 dark:border-gray-700">
                        <div class="bg-gray-50 dark:bg-gray-900 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i data-lucide="search-x" class="h-12 w-12 text-gray-400"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">No products found</h3>
                        <p class="mt-3 text-lg text-gray-500 dark:text-gray-400 max-w-md mx-auto">We couldn't find any products matching your current filters. Try adjusting your search criteria.</p>
                        <a href="{{ route('shop.index') }}" class="mt-8 inline-flex items-center justify-center bg-primary text-white font-bold py-3 px-8 rounded-xl hover:bg-secondary transition-colors shadow-sm shadow-primary/30">
                            <i data-lucide="refresh-cw" class="w-5 h-5 mr-2"></i> Clear Filters
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('shopFilters', () => ({
            mobileFiltersOpen: false
        }));
    });
</script>
@endpush
