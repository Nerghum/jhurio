@extends('layouts.admin')

@section('title', 'Manage Hot Deals')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            Hot Deals <i data-lucide="flame" class="w-6 h-6 text-orange-500"></i>
        </h2>
        <p class="text-sm text-gray-500 mt-1">Manage the featured products displayed on the homepage</p>
    </div>
</div>
@endsection

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Add New Hot Deal -->
    <div class="lg:col-span-1">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 p-6"
             x-data="productSearch">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Add Product</h3>
            
            <div class="relative mb-4">
                <input type="text" 
                       x-model="searchQuery" 
                       @input.debounce.300ms="search"
                       placeholder="Search by name or SKU..." 
                       class="w-full rounded-xl border border-gray-200 dark:border-gray-600 bg-gray-50 dark:bg-gray-900 px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary focus:border-primary pl-10">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-3.5 text-gray-400"></i>
            </div>

            <!-- Search Results Dropdown -->
            <div x-show="results.length > 0" class="mt-2 bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-xl shadow-lg overflow-hidden max-h-60 overflow-y-auto">
                <template x-for="product in results" :key="product.id">
                    <form action="{{ route('admin.featured_products.add') }}" method="POST" class="border-b border-gray-50 dark:border-gray-700/50 last:border-0">
                        @csrf
                        <input type="hidden" name="product_id" :value="product.id">
                        <button type="submit" class="w-full text-left px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors flex justify-between items-center group">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-primary transition-colors" x-text="product.name"></p>
                                <p class="text-xs text-gray-500 mt-0.5"><span x-text="product.sku"></span> &bull; <span x-text="product.price"></span> Tk</p>
                            </div>
                            <i data-lucide="plus-circle" class="w-4 h-4 text-gray-400 group-hover:text-primary transition-colors"></i>
                        </button>
                    </form>
                </template>
            </div>
            
            <div x-show="searchQuery.length > 0 && results.length === 0 && !loading" class="text-center py-4 text-sm text-gray-500">
                No products found.
            </div>
            <div x-show="loading" class="text-center py-4 text-sm text-gray-500">
                Searching...
            </div>
        </div>
    </div>

    <!-- Current Hot Deals List -->
    <div class="lg:col-span-2">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-medium border-b border-gray-100 dark:border-gray-700">
                        <tr>
                            <th class="px-6 py-4">Image</th>
                            <th class="px-6 py-4">Product Name</th>
                            <th class="px-6 py-4">Price</th>
                            <th class="px-6 py-4 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                        @forelse($featuredProducts as $product)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                            <td class="px-6 py-4">
                                @if($product->thumbnail)
                                <img src="{{ $product->thumbnail->url }}" class="w-12 h-12 rounded-lg object-cover border border-gray-200 dark:border-gray-700">
                                @else
                                <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center text-gray-400">
                                    <i data-lucide="image" class="w-5 h-5"></i>
                                </div>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">{{ $product->name }}</div>
                                <div class="text-xs text-gray-500 mt-1">SKU: {{ $product->sku }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white font-medium">
                                {{ number_format($product->price, 0) }} Tk
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('admin.featured_products.remove', $product) }}" method="POST" onsubmit="return confirm('Remove this product from Hot Deals?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition-colors">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                                <i data-lucide="flame" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
                                <p>No hot deals active right now. Search and add products from the panel.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('productSearch', () => ({
            searchQuery: '',
            results: [],
            loading: false,
            
            async search() {
                if (this.searchQuery.length < 2) {
                    this.results = [];
                    return;
                }
                
                this.loading = true;
                try {
                    const response = await fetch(`{{ route('admin.featured_products.search') }}?q=${encodeURIComponent(this.searchQuery)}`);
                    this.results = await response.json();
                } catch (e) {
                    console.error('Search failed', e);
                }
                this.loading = false;
                setTimeout(() => lucide.createIcons(), 50);
            }
        }));
    });
</script>
@endpush
@endsection
