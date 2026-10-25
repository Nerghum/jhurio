<div class="grid w-full grid-cols-1 items-start gap-x-6 gap-y-8 sm:grid-cols-12 lg:gap-x-8 p-6 sm:p-8" 
    x-data="quickviewProduct({
        product: {{ json_encode($product) }},
        images: [
            @if($product->thumbnail) '{{ $product->thumbnail->url }}', @endif
            @foreach($product->gallery as $img) '{{ $img->url }}', @endforeach
        ],
        variations: {{ json_encode($product->variations->map(function($var) {
            return [
                'id' => $var->id,
                'name' => $var->name,
                'color_code' => $var->color_code,
                'size' => $var->size,
                'price' => $var->price,
                'stock_quantity' => $var->stock_quantity,
                'media_url' => $var->media ? $var->media->url : null
            ];
        })) }}
    })">
    <div class="aspect-h-3 aspect-w-2 overflow-hidden rounded-xl bg-gray-100 dark:bg-gray-800 sm:col-span-4 lg:col-span-5 relative">
        <img :src="activeImage" alt="{{ $product->name }}" class="object-cover object-center w-full h-full">
        @if($product->gallery && $product->gallery->count() > 0)
        <div class="absolute bottom-4 left-0 right-0 flex justify-center gap-2 px-4">
            <template x-for="(img, index) in images" :key="index">
                <button type="button" 
                        @click="activeImage = img"
                        class="w-2 h-2 rounded-full transition-all"
                        :class="activeImage === img ? 'bg-primary ring-2 ring-primary ring-offset-2' : 'bg-gray-300 dark:bg-gray-600'">
                </button>
            </template>
        </div>
        @endif
    </div>
    
    <div class="sm:col-span-8 lg:col-span-7 flex flex-col h-full">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white sm:pr-12">{{ $product->name }}</h2>

        <section aria-labelledby="information-heading" class="mt-2">
            <h3 id="information-heading" class="sr-only">Product information</h3>
            <div class="flex items-end gap-3">
                <p class="text-2xl text-accent dark:text-accent font-bold">
                    <span x-text="Number(currentPrice).toFixed(0)"></span> BDT
                </p>
                <p class="text-lg text-gray-500 dark:text-gray-400 line-through mb-0.5" x-show="currentPrice === basePrice && {{ $product->compare_at_price ?: 0 }} > basePrice">
                    {{ number_format($product->compare_at_price ?: 0, 0) }} BDT
                </p>
                <p class="text-lg text-gray-500 dark:text-gray-400 line-through mb-0.5" x-show="currentPrice < basePrice" x-text="Number(basePrice).toFixed(0) + ' BDT'"></p>
            </div>
            
            <div class="mt-4">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <span x-show="currentStock > 0" class="text-green-600 dark:text-green-400 font-medium"><i data-lucide="check-circle" class="w-4 h-4 inline mr-1"></i>In Stock (<span x-text="currentStock"></span>)</span>
                    <span x-show="currentStock <= 0" class="text-red-600 dark:text-red-400 font-medium"><i data-lucide="x-circle" class="w-4 h-4 inline mr-1"></i>Out of Stock</span>
                </p>
            </div>
            
            @if($product->description || $product->content)
            <div class="mt-4 max-h-36 overflow-y-auto pr-1 text-sm text-gray-600 dark:text-gray-400 rich-text-content product-description-snippet
                        [&_p]:mb-1.5 [&_p:last-child]:mb-0
                        [&_ul]:list-disc [&_ul]:pl-5 [&_ul]:my-1.5 [&_ul]:space-y-1
                        [&_ol]:list-decimal [&_ol]:pl-5 [&_ol]:my-1.5 [&_ol]:space-y-1
                        [&_strong]:font-bold [&_strong]:text-gray-900 dark:[&_strong]:text-white
                        [&_b]:font-bold [&_b]:text-gray-900 dark:[&_b]:text-white
                        [&_em]:italic [&_u]:underline
                        [&_a]:text-primary dark:[&_a]:text-secondary [&_a]:underline">
                {!! $product->description ?: Str::limit(strip_tags($product->content), 150) !!}
            </div>
            @endif
        </section>

        <section aria-labelledby="options-heading" class="mt-6 flex-1">
            <form>
                @if($product->variations->count() > 0)
                <div x-show="uniqueColors.length > 0" class="mb-4">
                    <h4 class="text-sm font-medium text-gray-900 dark:text-white">Color</h4>
                    <div class="mt-2 flex items-center space-x-3">
                        <template x-for="color in uniqueColors" :key="color.name">
                            <label class="relative -m-0.5 flex cursor-pointer items-center justify-center rounded-full p-0.5 focus:outline-none ring-gray-400"
                                   :class="{'ring-2 ring-primary': selectedColor === color.name}">
                                <input type="radio" name="color-choice" :value="color.name" class="sr-only" @change="selectColor(color.name)">
                                <span aria-hidden="true" class="h-8 w-8 rounded-full border border-black border-opacity-10 shadow-sm" :style="`background-color: ${color.code}`"></span>
                            </label>
                        </template>
                    </div>
                </div>

                <div x-show="uniqueSizes.length > 0" class="mb-6">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-medium text-gray-900 dark:text-white">Size</h4>
                    </div>
                    <div class="mt-2 grid grid-cols-4 gap-2">
                        <template x-for="size in uniqueSizes" :key="size">
                            <label class="flex items-center justify-center rounded-xl border border-gray-200 bg-white dark:bg-gray-800 px-3 py-2 text-sm font-medium uppercase text-gray-900 dark:text-white hover:bg-gray-50 dark:hover:bg-gray-700 cursor-pointer focus:outline-none sm:flex-1"
                                   :class="{'ring-2 ring-primary border-transparent': selectedSize === size}">
                                <input type="radio" name="size-choice" :value="size" class="sr-only" @change="selectSize(size)">
                                <span x-text="size"></span>
                            </label>
                        </template>
                    </div>
                </div>
                @endif
                
                <div class="mt-6 flex items-center mb-6">
                    <label class="sr-only" for="quantity">Quantity</label>
                    <div class="flex items-center border border-gray-300 dark:border-gray-700 rounded-xl overflow-hidden">
                        <button type="button" @click="if(quantity > 1) quantity--" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <i data-lucide="minus" class="w-4 h-4"></i>
                        </button>
                        <input type="number" id="quantity" x-model.number="quantity" min="1" :max="currentStock" class="w-16 text-center border-none focus:ring-0 text-gray-900 dark:text-white bg-transparent py-2 bg-white dark:bg-gray-900" readonly>
                        <button type="button" @click="if(quantity < currentStock) quantity++" class="px-4 py-2 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                        </button>
                    </div>
                </div>

                <div class="mt-auto pt-6 border-t border-gray-200 dark:border-gray-800 flex gap-4">
                    <button type="button" 
                            @click="addToCart"
                            :disabled="currentStock <= 0 || loading"
                            class="flex-1 bg-white dark:bg-gray-800 text-primary border-2 border-primary h-12 rounded-xl font-semibold text-base hover:bg-primary hover:text-white transition-colors flex items-center justify-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i data-lucide="shopping-bag" class="w-5 h-5"></i> 
                        <span x-text="currentStock > 0 ? (loading ? 'Adding...' : 'Add to Cart') : 'Out of Stock'"></span>
                    </button>
                    
                    <button type="button" 
                            @click="openExpress"
                            :disabled="currentStock <= 0" 
                            class="flex-1 bg-primary text-white h-12 rounded-xl font-semibold text-base hover:bg-secondary transition-colors shadow-lg shadow-secondary/30 dark:shadow-none disabled:opacity-50 disabled:cursor-not-allowed">
                        Buy it Now
                    </button>
                </div>
                
                <div class="mt-4 text-center">
                    <a href="{{ route('product.show', $product->slug) }}" class="text-sm font-medium text-primary hover:text-secondary underline underline-offset-4">View full details</a>
                </div>
            </form>
        </section>
    </div>
</div>


