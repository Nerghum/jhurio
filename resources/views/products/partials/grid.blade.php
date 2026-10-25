@if($products->count() > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($products as $product)
            <div class="h-full">
                <x-product-card :product="$product" />
            </div>
        @endforeach
    </div>
@else
    <div class="text-center py-12 sm:py-16 bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-dashed border-gray-300 dark:border-gray-700">
        <div class="bg-gray-50 dark:bg-gray-900 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-4">
            <i data-lucide="search-x" class="h-10 w-10 text-gray-400"></i>
        </div>
        <h3 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">No products found</h3>
        <p class="mt-2 text-sm sm:text-base text-gray-500 dark:text-gray-400 max-w-md mx-auto">We couldn't find any products matching your current filters. Try adjusting your search criteria.</p>
        <button @click="clearFilters" class="mt-6 inline-flex items-center justify-center bg-primary text-white font-bold py-2.5 px-6 rounded-xl hover:bg-secondary transition-colors shadow-sm shadow-primary/30 text-sm">
            <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i> Clear Filters
        </button>
    </div>
@endif
