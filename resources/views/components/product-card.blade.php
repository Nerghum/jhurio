@props(['product', 'highlight' => false])

<div class="group relative bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 dark:border-gray-700 h-full flex flex-col justify-between">
    <div>
        <div class="aspect-w-1 aspect-h-1 w-full overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-700 relative">
            <a href="{{ route('product.show', $product->slug) }}" class="block w-full h-full">
                <img src="{{ $product->thumbnail ? $product->thumbnail->url : 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80' }}" alt="{{ $product->name }}" class="h-64 w-full object-cover object-center group-hover:scale-105 transition-transform duration-500">
            </a>
            
            <!-- Badges -->
            <div class="absolute top-2 left-2 flex flex-col gap-2">
                @if($highlight)
                    <span class="bg-red-500 text-white text-[10px] uppercase font-bold px-2 py-1 rounded-lg tracking-wider shadow-sm">
                        Sale
                    </span>
                @endif
            </div>

            <div class="absolute top-2 right-2 flex flex-col gap-2">
                <!-- Quickview (Always visible subtle button) -->
                <button type="button" 
                        @click="window.dispatchEvent(new CustomEvent('open-quickview', { detail: { slug: '{{ $product->slug }}' } }))"
                        class="bg-white/90 backdrop-blur-sm dark:bg-gray-800/90 text-gray-700 dark:text-gray-200 hover:text-primary dark:hover:text-primary rounded-full p-2 shadow-sm hover:shadow-md transition-all"
                        title="Quick View">
                    <i data-lucide="eye" class="w-4 h-4"></i>
                </button>
            </div>
        </div>
        
        <div class="mt-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">
                @if($product->categories && $product->categories->count() > 0)
                    {{ $product->categories->first()->name }}
                @else
                    Uncategorized
                @endif
            </p>
            <h3 class="text-base font-semibold text-gray-900 dark:text-white line-clamp-2 hover:text-primary transition-colors">
                <a href="{{ route('product.show', $product->slug) }}">
                    {{ $product->name }}
                </a>
            </h3>

            @if($product->reviewsCount() > 0)
            <div class="flex items-center gap-1 mt-1">
                <div class="flex text-yellow-400">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= round($product->averageRating()))
                            <i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i>
                        @else
                            <i data-lucide="star" class="w-3.5 h-3.5 text-gray-300 dark:text-gray-600"></i>
                        @endif
                    @endfor
                </div>
                <span class="text-xs text-gray-500 dark:text-gray-400">({{ $product->reviewsCount() }})</span>
            </div>
            @endif
        </div>
    </div>
    
    <div class="mt-4 flex flex-col gap-4">
        <div class="flex items-center gap-2">
            <p class="text-lg font-bold text-accent dark:text-accent">{{ number_format($product->price, 0) }} BDT</p>
            @if($product->compare_at_price > $product->price)
                <p class="text-sm text-gray-500 line-through">{{ number_format($product->compare_at_price, 0) }} BDT</p>
            @endif
        </div>
        
        <!-- Explicit Actions -->
        <div class="grid grid-cols-2 gap-2">
            @if($product->variations->count() > 0)
                <!-- Select Options (Full Width) -->
                <button type="button" @click="window.dispatchEvent(new CustomEvent('open-quickview', { detail: { slug: '{{ $product->slug }}' } }))"
                        class="col-span-2 bg-primary text-white hover:bg-secondary rounded-xl py-2 px-4 shadow-sm hover:shadow-md transition-all flex items-center justify-center gap-2 font-medium text-sm w-full">
                    <i data-lucide="sliders-horizontal" class="w-4 h-4"></i> Select Options
                </button>
            @else
                <!-- Add to Cart -->
                <button type="button" x-data="{ loading: false }" 
                        @click="
                            loading = true;
                            window.dispatchEvent(new CustomEvent('add-to-cart', {
                                detail: { product_id: {{ $product->id }}, variation_id: null, quantity: 1 }
                            }));
                            setTimeout(() => loading = false, 500);
                        "
                        :disabled="loading"
                        class="bg-white dark:bg-gray-800 text-primary border border-primary hover:bg-primary hover:text-white rounded-xl py-2 px-2 shadow-sm transition-all flex items-center justify-center gap-1 font-medium text-sm disabled:opacity-50">
                    <i data-lucide="shopping-bag" class="w-4 h-4" x-show="!loading"></i>
                    <div x-show="loading" class="w-4 h-4 rounded-full border-2 border-primary border-t-transparent animate-spin"></div>
                    <span>Add</span>
                </button>
                
                <!-- Express Checkout -->
                <button type="button" 
                        @click="window.dispatchEvent(new CustomEvent('open-checkout', {
                                detail: {
                                    product_id: {{ $product->id }},
                                    variation_id: null,
                                    product_name: '{{ addslashes($product->name) }}',
                                    price: {{ $product->price }},
                                    quantity: 1,
                                    product_image: '{{ $product->thumbnail ? $product->thumbnail->url : '' }}'
                                }
                            }))"
                        class="bg-primary text-white hover:bg-secondary rounded-xl py-2 px-2 shadow-sm transition-all flex items-center justify-center gap-1 font-medium text-sm">
                    <i data-lucide="zap" class="w-4 h-4"></i> Buy
                </button>
            @endif
        </div>
    </div>
</div>
