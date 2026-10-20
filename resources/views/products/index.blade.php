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

                    <div class="space-y-8">
                        <!-- Search -->
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3 uppercase tracking-wider">Search</h3>
                            <div class="relative">
                                <input type="text" x-model="q" placeholder="Search products..." class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                                <i data-lucide="search" class="w-5 h-5 absolute left-3 top-3 text-gray-400"></i>
                            </div>
                        </div>

                        <hr class="border-gray-100 dark:border-gray-700">

                        <!-- Price Range -->
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3 uppercase tracking-wider">Price (BDT)</h3>
                            
                            <div class="mb-6 px-2">
                                <input type="range" min="0" max="10000" step="100" x-model="maxPrice" @change="filter" class="w-full h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-primary">
                                <div class="flex justify-between mt-2 text-xs text-gray-500 font-medium">
                                    <span>0 BDT</span>
                                    <span x-text="maxPrice + ' BDT'"></span>
                                </div>
                            </div>

                            <div class="flex items-center gap-3">
                                <input type="number" x-model.lazy="minPrice" @change="filter" placeholder="Min" class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                                <span class="text-gray-400">-</span>
                                <input type="number" x-model.lazy="maxPrice" @change="filter" placeholder="Max" class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                            </div>
                        </div>

                        <hr class="border-gray-100 dark:border-gray-700">

                        <!-- Categories -->
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-3 uppercase tracking-wider">Categories</h3>
                            <div class="space-y-1">
                                <button type="button" @click="setCategory('')" 
                                   class="w-full flex justify-between items-center px-3 py-2.5 rounded-xl transition-all"
                                   :class="category === '' ? 'bg-primary/10 text-primary font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-primary dark:hover:text-primary font-medium'">
                                    <span>All Categories</span>
                                </button>
                                @foreach($categories as $cat)
                                    <button type="button" @click="setCategory('{{ $cat->slug }}')" 
                                       class="w-full flex justify-between items-center px-3 py-2.5 rounded-xl transition-all"
                                       :class="category === '{{ $cat->slug }}' ? 'bg-primary/10 text-primary font-bold' : 'text-gray-600 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-primary dark:hover:text-primary font-medium'">
                                        <span>{{ $cat->name }}</span>
                                        <span class="bg-gray-100 dark:bg-gray-900 text-gray-500 text-xs py-1 px-2 rounded-lg font-bold">{{ $cat->products()->count() }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="pt-2" x-show="q || minPrice > 0 || maxPrice < 5000 || category !== ''" x-cloak>
                            <button type="button" @click="clearFilters" class="w-full bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 text-accent flex justify-center items-center py-2.5 px-4 rounded-xl text-sm font-bold transition-colors">
                                <i data-lucide="x-circle" class="w-4 h-4 mr-2"></i> Clear all filters
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Content (Product Grid) -->
            <div class="w-full lg:w-3/4">
                
                <!-- Top Toolbar -->
                <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-4 mb-6 flex flex-col sm:flex-row justify-between items-center gap-4 relative">
                    <div class="absolute inset-0 bg-white/50 dark:bg-gray-800/50 flex items-center justify-center z-10 rounded-2xl" x-show="loading" x-cloak>
                        <i data-lucide="loader" class="w-6 h-6 animate-spin text-primary"></i>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400">
                        Showing <span class="font-bold text-gray-900 dark:text-white" x-text="firstItem"></span>-<span class="font-bold text-gray-900 dark:text-white" x-text="lastItem"></span> of <span class="font-bold text-gray-900 dark:text-white" x-text="totalItems"></span> results
                    </div>
                    
                    <div class="flex items-center space-x-3">
                        <label for="sort" class="text-sm font-bold text-gray-700 dark:text-gray-300">Sort by:</label>
                        <select id="sort" x-model="sort" class="text-sm font-medium rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-2 focus:ring-primary py-2.5 pl-3 pr-8 shadow-sm">
                            <option value="latest">Latest Arrivals</option>
                            <option value="price_asc">Price: Low to High</option>
                            <option value="price_desc">Price: High to Low</option>
                        </select>
                    </div>
                </div>

                <!-- Products Grid container -->
                <div id="product-grid-container">
                    @include('products.partials.grid')
                </div>
                
                <!-- Pagination container -->
                <div id="pagination-container" class="mt-12 @if(!$products->hasPages()) hidden @endif bg-white dark:bg-gray-800 p-4 sm:p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex justify-center w-full overflow-x-auto">
                    {!! $products->links() !!}
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('shopFilters', () => ({
            mobileFiltersOpen: false,
            loading: false,
            q: '{{ request("q") }}',
            minPrice: {{ request('min') ?: 0 }},
            maxPrice: {{ request('max') ?: 5000 }},
            category: '{{ request("category") }}',
            sort: '{{ request("sort", "latest") }}',
            
            totalItems: {{ $products->total() }},
            firstItem: {{ $products->firstItem() ?? 0 }},
            lastItem: {{ $products->lastItem() ?? 0 }},

            init() {
                // Intercept pagination clicks
                document.getElementById('pagination-container').addEventListener('click', (e) => {
                    const link = e.target.closest('a');
                    if (link) {
                        e.preventDefault();
                        this.fetchResults(link.href);
                    }
                });

                // Debounce search input
                this.$watch('q', () => {
                    clearTimeout(this.searchTimeout);
                    this.searchTimeout = setTimeout(() => this.filter(), 500);
                });
                
                this.$watch('sort', () => this.filter());
            },

            setCategory(cat) {
                this.category = cat;
                this.filter();
            },

            clearFilters() {
                this.q = '';
                this.minPrice = 0;
                this.maxPrice = 5000;
                this.category = '';
                this.sort = 'latest';
                this.filter();
            },

            filter() {
                const params = new URLSearchParams();
                if (this.q) params.set('q', this.q);
                if (this.minPrice > 0) params.set('min', this.minPrice);
                if (this.maxPrice < 5000) params.set('max', this.maxPrice);
                if (this.category) params.set('category', this.category);
                if (this.sort && this.sort !== 'latest') params.set('sort', this.sort);

                const url = `{{ route('shop.index') }}?${params.toString()}`;
                this.fetchResults(url);
                
                // Update URL in browser without reloading
                window.history.pushState({}, '', url);
            },

            async fetchResults(url) {
                this.loading = true;
                try {
                    const response = await fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    });
                    const data = await response.json();
                    
                    document.getElementById('product-grid-container').innerHTML = data.html;
                    
                    const paginationContainer = document.getElementById('pagination-container');
                    if (data.pagination) {
                        paginationContainer.innerHTML = data.pagination;
                        paginationContainer.classList.remove('hidden');
                    } else {
                        paginationContainer.classList.add('hidden');
                    }
                    
                    this.totalItems = data.total;
                    this.firstItem = data.firstItem;
                    this.lastItem = data.lastItem;

                    // Re-initialize lucide icons for new content
                    if (window.lucide) {
                        window.lucide.createIcons();
                    }
                } catch (error) {
                    console.error('Error fetching results:', error);
                }
                this.loading = false;
            }
        }));
    });
</script>
@endpush
