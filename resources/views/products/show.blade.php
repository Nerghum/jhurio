@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="bg-white dark:bg-dark py-4 sm:py-6 lg:py-8" x-data="productPage()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        @php
            $firstCategory = $product->categories->first();
        @endphp
        <!-- Breadcrumbs (Mobile Optimized: Clean, Compact, No Product Name) -->
        <nav class="flex items-center text-xs sm:text-sm text-gray-500 dark:text-gray-400 mb-3 sm:mb-4 lg:mb-6" aria-label="Breadcrumb">
            <ol class="inline-flex items-center flex-wrap gap-1 sm:gap-2">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="hover:text-primary dark:hover:text-primary transition-colors flex items-center gap-1 font-medium">
                        <i data-lucide="home" class="w-3.5 h-3.5 flex-shrink-0"></i>
                        <span>Home</span>
                    </a>
                </li>
                <li>
                    <div class="flex items-center gap-1 sm:gap-2">
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400 flex-shrink-0"></i>
                        <a href="{{ route('shop.index') }}" class="hover:text-primary dark:hover:text-primary transition-colors font-medium">Shop</a>
                    </div>
                </li>
                @if($firstCategory)
                <li aria-current="page">
                    <div class="flex items-center gap-1 sm:gap-2">
                        <i data-lucide="chevron-right" class="w-3.5 h-3.5 text-gray-400 flex-shrink-0"></i>
                        <a href="{{ route('shop.index', ['category' => $firstCategory->slug]) }}" class="text-gray-800 dark:text-gray-200 font-semibold hover:text-primary dark:hover:text-primary transition-colors truncate max-w-[180px] sm:max-w-none">
                            {{ $firstCategory->name }}
                        </a>
                    </div>
                </li>
                @endif
            </ol>
        </nav>

        <!-- Product Main Section -->
        <div class="lg:grid lg:grid-cols-2 lg:gap-x-12 xl:gap-x-16">
            
            <!-- Left: Media Gallery (Optimized Mobile: 24% Vertical Thumbnails Left, Main Image Right, Max 60vh) -->
            <div class="flex flex-row gap-2 sm:gap-3 lg:gap-4 mb-6 lg:mb-0 h-[50vh] sm:h-[55vh] max-h-[60vh] lg:h-[580px] lg:max-h-[600px] items-stretch">
                <!-- Thumbnails (Vertical on Mobile 24% width, Vertical on Desktop w-24) -->
                <div x-show="images.length > 1" 
                     class="flex flex-col gap-2 sm:gap-2.5 overflow-y-auto max-h-full scrollbar-hide py-0.5 w-[24%] lg:w-24 flex-shrink-0" 
                     style="scrollbar-width: none; -ms-overflow-style: none;">
                    <template x-for="(img, index) in images" :key="index">
                        <button 
                            type="button"
                            @click="activeImage = img"
                            :class="{'border-primary dark:border-primary ring-2 ring-primary/20 shadow-sm opacity-100': activeImage === img, 'border-gray-200 dark:border-gray-700 hover:border-primary/50 opacity-60 hover:opacity-100': activeImage !== img}"
                            class="relative flex-shrink-0 w-full aspect-square rounded-lg sm:rounded-xl border-2 overflow-hidden focus:outline-none transition-all p-0.5 bg-white dark:bg-gray-800"
                        >
                            <img :src="img" alt="Thumbnail" class="w-full h-full object-cover rounded-md sm:rounded-lg">
                        </button>
                    </template>
                </div>
                
                <!-- Main Large Image -->
                <div class="flex-1 min-w-0 h-full max-h-full relative bg-gray-50 dark:bg-gray-800/80 rounded-xl sm:rounded-2xl border border-gray-100 dark:border-gray-700/60 overflow-hidden flex items-center justify-center group">
                    <img :src="activeImage" 
                         src="{{ $product->thumbnail?->url ?? $product->image_url ?? '' }}"
                         alt="{{ $product->name }}" 
                         class="w-full h-full object-contain p-2 sm:p-3 transition-transform duration-500" 
                         :class="{'scale-150 cursor-zoom-in': zoom}" 
                         @click="zoom = !zoom" 
                         @mousemove="zoomImage(event)">
                    
                    @if($product->video_url)
                    <a href="{{ $product->video_url }}" target="_blank" class="absolute bottom-3 right-3 sm:bottom-4 sm:right-4 bg-white/90 dark:bg-gray-800/90 backdrop-blur text-gray-900 dark:text-white p-2 sm:p-2.5 rounded-xl shadow-lg hover:scale-110 transition-transform z-10" title="Watch Product Video">
                        <i data-lucide="play" class="w-4 h-4 sm:w-5 sm:h-5 fill-current"></i>
                    </a>
                    @endif
                </div>
            </div>

            <!-- Right: Product Details -->
            <div class="flex flex-col">
                <div class="flex justify-between items-start mb-2 gap-4">
                    <h1 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight">{{ $product->name }}</h1>
                    
                    @php
                        $inWishlist = false;
                        if (auth()->check()) {
                            $inWishlist = \App\Models\Wishlist::where('user_id', auth()->id())->where('product_id', $product->id)->exists();
                        }
                    @endphp
                    <button type="button" 
                            onclick="toggleWishlist({{ $product->id }}, this)"
                            class="flex-shrink-0 bg-white/90 backdrop-blur-sm dark:bg-gray-800/90 text-gray-700 dark:text-gray-200 hover:text-red-500 rounded-full p-3 shadow-sm hover:shadow-md transition-all border border-gray-100 dark:border-gray-700 mt-1"
                            title="Add to Wishlist">
                        <i data-lucide="heart" class="w-6 h-6 {{ $inWishlist ? 'fill-red-500 text-red-500' : 'text-gray-400' }}"></i>
                    </button>
                </div>
                
                <!-- Reviews -->
                <div class="flex items-center mb-2">
                    <div class="flex text-yellow-400">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= round($product->averageRating()))
                                <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                            @else
                                <i data-lucide="star" class="w-4 h-4 text-gray-300 dark:text-gray-600"></i>
                            @endif
                        @endfor
                    </div>
                    <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">
                        <a href="#customer-reviews" class="hover:text-primary transition-colors">({{ $product->reviewsCount() }} reviews)</a>
                    </span>
                </div>

                <!-- Categories & Tags (Horizontal under review in one line, as link text, no label text, tight gap, no bottom margin) -->
                @php
                    $categoryLinks = collect();
                    foreach ($product->categories as $category) {
                        $categoryLinks->push([
                            'name' => $category->name,
                            'url' => route('shop.index', ['category' => $category->slug]),
                        ]);
                    }
                    foreach ($product->tags as $tag) {
                        $categoryLinks->push([
                            'name' => $tag->name,
                            'url' => route('shop.index', ['q' => $tag->name]),
                        ]);
                    }
                @endphp

                @if($categoryLinks->count() > 0)
                    <!-- Desktop: Shown under review, hidden on mobile -->
                    <div class="hidden lg:flex text-xs sm:text-sm text-gray-500 dark:text-gray-400 flex-wrap items-center gap-y-0.5 mb-0">
                        @foreach($categoryLinks as $index => $item)
                            <a href="{{ $item['url'] }}" 
                               class="text-primary hover:text-secondary dark:text-secondary dark:hover:text-primary font-medium hover:underline transition-colors">
                                {{ $item['name'] }}
                            </a>@if(!$loop->last)<span class="text-gray-400 dark:text-gray-500 mr-1.5">,</span>@endif
                        @endforeach
                    </div>
                @endif

                <!-- Price -->
                <div class="mb-6 flex items-end gap-3">
                    <span class="text-3xl font-bold text-accent dark:text-accent" x-text="currentPrice.toFixed(0) + ' BDT'">{{ number_format($product->price, 0) }} BDT</span>
                    @if($product->compare_at_price > $product->price)
                        <span class="text-lg text-gray-500 dark:text-gray-400 line-through" x-show="currentPrice === basePrice">{{ number_format($product->compare_at_price, 0) }} BDT</span>
                    @endif
                    <span class="text-lg text-gray-500 dark:text-gray-400 line-through" x-show="currentPrice < basePrice" x-text="basePrice.toFixed(0) + ' BDT'"></span>
                </div>

                <!-- Description Snippet -->
                <div class="text-base text-gray-600 dark:text-gray-300 mb-8 prose prose-sm dark:prose-invert">
                    {!! $product->description ?: Str::limit(strip_tags($product->content), 150) !!}
                </div>

                <form class="mt-auto">
                    <!-- Dynamic Variations Selector -->
                    <template x-for="(option, optIndex) in product.options" :key="optIndex">
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                                    <span x-text="option.name"></span>: 
                                    <span class="text-gray-500 dark:text-gray-400 font-normal ml-1" x-text="selectedOptions[option.name]"></span>
                                </h3>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <template x-for="(val, valIndex) in option.values" :key="valIndex">
                                    <!-- Color Swatch style -->
                                    <template x-if="option.name.toLowerCase() === 'color'">
                                        <button 
                                            type="button"
                                            @click="selectOption(option.name, val.name)"
                                            :class="{
                                                'ring-2 ring-offset-2 ring-dark dark:ring-offset-dark border-transparent scale-110': selectedOptions[option.name] === val.name, 
                                                'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-500': selectedOptions[option.name] !== val.name,
                                                'opacity-50': !isOptionAvailable(option.name, val.name)
                                            }"
                                            class="relative flex items-center justify-center p-0.5 rounded-full border-2 focus:outline-none transition-all"
                                            :title="val.name"
                                        >
                                            <span class="w-8 h-8 rounded-full border border-black/10 dark:border-white/10 flex items-center justify-center text-xs font-bold relative overflow-hidden shadow-sm" 
                                                  :style="`background-color: ${val.meta || '#e5e7eb'}; color: ${val.meta ? '#fff' : '#000'}`">
                                                <span x-text="!val.meta ? val.name.charAt(0) : ''"></span>
                                                
                                                <template x-if="!isOptionAvailable(option.name, val.name)">
                                                    <div class="absolute inset-0 flex items-center justify-center rotate-45">
                                                        <div class="w-[150%] h-px bg-red-500 shadow-[0_0_2px_rgba(255,255,255,0.8)]"></div>
                                                    </div>
                                                </template>
                                            </span>
                                        </button>
                                    </template>
                                    
                                    <!-- Pill style -->
                                    <template x-if="option.name.toLowerCase() !== 'color'">
                                        <button 
                                            type="button"
                                            @click="selectOption(option.name, val.name)"
                                            :class="{
                                                'bg-dark text-white dark:bg-white dark:text-dark border-dark dark:border-white shadow-md': selectedOptions[option.name] === val.name, 
                                                'bg-white text-gray-900 border-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-500': selectedOptions[option.name] !== val.name && isOptionAvailable(option.name, val.name),
                                                'bg-gray-50 text-gray-400 border-gray-100 dark:bg-gray-800/40 dark:text-gray-600 dark:border-gray-800': !isOptionAvailable(option.name, val.name)
                                            }"
                                            class="px-5 py-2.5 text-sm font-semibold rounded-full border focus:outline-none transition-all min-w-[3.5rem] relative overflow-hidden flex items-center justify-center"
                                        >
                                            <span x-text="val.name" :class="{'line-through opacity-70': !isOptionAvailable(option.name, val.name)}"></span>
                                        </button>
                                    </template>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Urgency / Scarcity -->
                    <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/30 rounded-xl p-3 flex items-center text-red-700 dark:text-red-400">
                        <i data-lucide="clock" class="w-5 h-5 mr-2"></i>
                        <span class="text-sm font-medium">
                            <span x-show="currentStock > 0 && currentStock < 10" x-text="`Only ${currentStock} left in stock - order soon!`"></span>
                            <span x-show="currentStock >= 10">In Stock - Ready to ship!</span>
                            <span x-show="currentStock <= 0">Out of Stock</span>
                        </span>
                    </div>

                    <!-- Quantity Bundle Offers (Minimal, sleek DTC design) -->
                    <template x-if="hasBundles">
                        <div class="mb-6 space-y-3">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xs uppercase tracking-wider font-extrabold text-gray-900 dark:text-white flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-primary inline-block"></span>
                                    <span x-text="bundleSettings.heading || 'Special Bundle Offers'"></span>
                                </h3>
                                <span x-show="bundleSettings.subtitle" 
                                      class="text-[11px] font-bold text-emerald-700 bg-emerald-50 dark:bg-emerald-950/50 dark:text-emerald-300 px-2.5 py-0.5 rounded-full border border-emerald-200/60 dark:border-emerald-800/60" 
                                      x-text="bundleSettings.subtitle"></span>
                            </div>

                            <div class="space-y-2.5">
                                <!-- Buy 1 / Single Option -->
                                <template x-if="bundleSettings.show_single !== false">
                                    <div @click="selectBundleTier('single')"
                                         class="border-2 rounded-2xl p-3.5 cursor-pointer transition-all relative overflow-hidden"
                                         :class="selectedBundleTier === 'single' 
                                             ? 'border-primary bg-primary/[0.03] dark:bg-primary/10 shadow-sm ring-1 ring-primary/30' 
                                             : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800'">
                                        
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-all"
                                                     :class="selectedBundleTier === 'single' ? 'border-primary' : 'border-gray-300 dark:border-gray-600'">
                                                    <div x-show="selectedBundleTier === 'single'" class="w-2 h-2 rounded-full bg-primary"></div>
                                                </div>

                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2">
                                                        <span class="font-bold text-sm text-gray-900 dark:text-white truncate" 
                                                              x-text="bundleSettings.single_title || 'Buy 1 (Regular Price)'"></span>

                                                        <span x-show="bundleSettings.single_badge" 
                                                              class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 uppercase tracking-wider"
                                                              x-text="bundleSettings.single_badge"></span>
                                                    </div>
                                                    <span class="text-[11px] text-gray-400 dark:text-gray-500 font-medium">Standard packaging</span>
                                                </div>
                                            </div>

                                            <div class="text-right flex-shrink-0">
                                                <div class="font-extrabold text-sm sm:text-base text-accent dark:text-accent" x-text="formatMoney(baseUnitEffectivePrice)"></div>
                                                <span class="text-[10px] text-gray-400 font-medium">regular price</span>
                                            </div>
                                        </div>

                                        <!-- Variation select for single item if enabled and options exist -->
                                        <div x-show="selectedBundleTier === 'single' && bundleSettings.enable_variations && product.options && product.options.length > 0" 
                                             class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700/80 bg-gray-50/60 dark:bg-gray-900/40 -mx-3.5 -mb-3.5 p-3 rounded-b-2xl"
                                             @click.stop>
                                            <div class="flex items-center gap-2 text-xs">
                                                <span class="font-bold text-gray-700 dark:text-gray-300 text-[11px] whitespace-nowrap flex items-center gap-1">
                                                    <i data-lucide="sliders" class="w-3 h-3 text-primary"></i> Choice:
                                                </span>
                                                <div class="flex flex-wrap items-center gap-2 flex-1">
                                                    <template x-for="option in product.options" :key="option.name">
                                                        <div class="flex items-center gap-1">
                                                            <span class="text-[11px] text-gray-400" x-text="option.name + ':'"></span>
                                                            <select x-model="bundleItemSelections[0][option.name]" 
                                                                    @change="onBundleItemOptionChange(0)"
                                                                    class="py-1 px-2.5 text-xs font-semibold rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-800 dark:text-white focus:ring-primary focus:border-primary">
                                                                <template x-for="val in option.values" :key="val.name">
                                                                    <option :value="val.name" x-text="val.name"></option>
                                                                </template>
                                                            </select>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <!-- Discount Bundle Tiers (Buy 2, Buy 3, etc.) -->
                                <template x-for="(tier, tIndex) in bundleSettings.tiers" :key="tIndex">
                                    <div @click="selectBundleTier(tIndex)"
                                         class="border-2 rounded-2xl p-3.5 cursor-pointer transition-all relative overflow-hidden"
                                         :class="selectedBundleTier === tIndex 
                                             ? 'border-primary bg-primary/[0.03] dark:bg-primary/10 shadow-sm ring-1 ring-primary/30' 
                                             : 'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 bg-white dark:bg-gray-800'">
                                        
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="flex items-center gap-3 min-w-0">
                                                <div class="w-4 h-4 rounded-full border-2 flex items-center justify-center flex-shrink-0 transition-all"
                                                     :class="selectedBundleTier === tIndex ? 'border-primary' : 'border-gray-300 dark:border-gray-600'">
                                                    <div x-show="selectedBundleTier === tIndex" class="w-2 h-2 rounded-full bg-primary"></div>
                                                </div>

                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-2 flex-wrap">
                                                        <span class="font-bold text-sm text-gray-900 dark:text-white" x-text="tier.title"></span>

                                                        <span x-show="tier.badge" 
                                                              class="text-[10px] font-black px-2 py-0.5 rounded-full bg-primary text-white uppercase tracking-wider shadow-xs"
                                                              x-text="tier.badge"></span>
                                                    </div>

                                                    <div class="flex items-center gap-2 mt-0.5">
                                                        <span class="text-[11px] text-gray-500 dark:text-gray-400 font-semibold"
                                                              x-text="formatMoney(calculateTierUnitPrice(tier)) + ' / piece'"></span>
                                                        <span class="text-[10px] font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/40 px-1.5 py-0.2 rounded"
                                                              x-text="'Save ' + formatMoney(calculateTierSavings(tier))"></span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="text-right flex-shrink-0">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    <span class="text-xs text-gray-400 line-through" 
                                                          x-text="formatMoney(tier.quantity * baseUnitEffectivePrice)"></span>
                                                    <span class="font-extrabold text-sm sm:text-base text-accent dark:text-accent" 
                                                          x-text="formatMoney(calculateTierTotal(tier))"></span>
                                                </div>
                                                <span class="text-[10px] text-gray-400 font-medium" x-text="`Total for ${tier.quantity} pcs`"></span>
                                            </div>
                                        </div>

                                        <!-- Variation Select for each product in bundle -->
                                        <div x-show="selectedBundleTier === tIndex && bundleSettings.enable_variations && product.options && product.options.length > 0" 
                                             class="mt-3 pt-3 border-t border-gray-100 dark:border-gray-700/80 bg-gray-50/60 dark:bg-gray-900/40 -mx-3.5 -mb-3.5 p-3 rounded-b-2xl space-y-2"
                                             @click.stop>
                                            <p class="text-[11px] font-bold text-gray-600 dark:text-gray-300 flex items-center gap-1.5">
                                                <i data-lucide="sliders" class="w-3.5 h-3.5 text-primary"></i> 
                                                <span>Select variation for each piece:</span>
                                            </p>
                                            
                                            <div class="space-y-1.5">
                                                <template x-for="(item, iIdx) in (bundleItemSelections.slice(0, tier.quantity))" :key="iIdx">
                                                    <div class="flex items-center gap-2 bg-white dark:bg-gray-800 p-1.5 px-3 rounded-xl border border-gray-200/80 dark:border-gray-700 text-xs shadow-xs">
                                                        <span class="font-extrabold text-primary text-[11px] w-14 flex-shrink-0" x-text="`Piece #${iIdx + 1}:`"></span>
                                                        
                                                        <div class="flex flex-wrap items-center gap-2 flex-1">
                                                            <template x-for="option in product.options" :key="option.name">
                                                                <div class="flex items-center gap-1">
                                                                    <span class="text-[10px] font-medium text-gray-400" x-text="option.name + ':'"></span>
                                                                    <select x-model="bundleItemSelections[iIdx][option.name]" 
                                                                            @change="onBundleItemOptionChange(iIdx)"
                                                                            class="py-1 px-2 text-xs font-semibold rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                                                                        <template x-for="val in option.values" :key="val.name">
                                                                            <option :value="val.name" x-text="val.name"></option>
                                                                        </template>
                                                                    </select>
                                                                </div>
                                                            </template>
                                                        </div>
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Quantity & Actions -->
                    <div class="mb-8">
                        <div class="mb-4" x-show="!hasBundles || selectedBundleTier === 'single'">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Quantity</label>
                            <div class="inline-flex items-center border border-gray-300 dark:border-gray-600 rounded-xl h-14 bg-white dark:bg-gray-800">
                                <button type="button" @click="if(quantity > 1) { quantity--; recalculateBundlePrices(); }" class="px-4 py-2 text-gray-500 hover:text-primary transition-colors focus:outline-none">
                                    <i data-lucide="minus" class="w-4 h-4"></i>
                                </button>
                                <input type="number" x-model.number="quantity" min="1" :max="currentStock" class="w-16 text-center text-gray-900 dark:text-white font-medium border-0 focus:ring-0 bg-transparent p-0 appearance-none m-0" style="-moz-appearance: textfield;">
                                <button type="button" @click="if(quantity < currentStock) { quantity++; recalculateBundlePrices(); }" class="px-4 py-2 text-gray-500 hover:text-primary transition-colors focus:outline-none">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <button type="button" :disabled="currentStock <= 0 || loading" 
                                    @click="
                                        loading = true;
                                        const bInfo = getSelectedBundleInfo();
                                        window.dispatchEvent(new CustomEvent('add-to-cart', {
                                            detail: {
                                                product_id: {{ $product->id }},
                                                variation_id: activeVariation ? activeVariation.id : null,
                                                quantity: quantity,
                                                bundle_info: bInfo
                                            }
                                        }));
                                        setTimeout(() => loading = false, 500);
                                    "
                                    class="w-full bg-dark dark:bg-white text-white dark:text-dark h-14 rounded-xl font-semibold text-base sm:text-lg hover:bg-primary dark:hover:bg-primary dark:hover:text-white transition-colors flex items-center justify-center gap-2 shadow-lg shadow-gray-200 dark:shadow-none disabled:opacity-50 disabled:cursor-not-allowed">
                                <i data-lucide="shopping-bag" class="w-5 h-5"></i> 
                                <span x-text="currentStock > 0 ? (loading ? 'Adding...' : 'Add to Cart') : 'Out of Stock'"></span>
                            </button>
                            
                            <button type="button" @click="
                                const bInfo = getSelectedBundleInfo();
                                window.dispatchEvent(new CustomEvent('open-checkout', {
                                    detail: {
                                        product_id: {{ $product->id }},
                                        variation_id: activeVariation ? activeVariation.id : null,
                                        product_name: '{{ addslashes($product->name) }}',
                                        price: currentPrice,
                                        quantity: quantity,
                                        product_image: activeImage,
                                        bundle_info: bInfo
                                    }
                                }))
                            " :disabled="currentStock <= 0" class="w-full bg-primary text-white h-14 rounded-xl font-semibold text-base sm:text-lg hover:bg-secondary transition-colors shadow-lg shadow-secondary/30 dark:shadow-none disabled:opacity-50 disabled:cursor-not-allowed">
                                Buy it Now
                            </button>
                        </div>

                        <!-- Categories (Mobile: Placed under Add to Cart / Buy Now buttons) -->
                        @if($categoryLinks->count() > 0)
                            <div class="mt-3.5 text-xs sm:text-sm text-gray-500 dark:text-gray-400 flex lg:hidden flex-wrap items-center gap-y-0.5">
                                @foreach($categoryLinks as $index => $item)
                                    <a href="{{ $item['url'] }}" 
                                       class="text-primary hover:text-secondary dark:text-secondary dark:hover:text-primary font-medium hover:underline transition-colors">
                                        {{ $item['name'] }}
                                    </a>@if(!$loop->last)<span class="text-gray-400 dark:text-gray-500 mr-1.5">,</span>@endif
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Trust Badges -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-6 border-t border-b border-gray-200 dark:border-gray-700 mb-8">
                        <div class="flex flex-col items-center text-center">
                            <i data-lucide="truck" class="w-6 h-6 text-gray-400 mb-2"></i>
                            <span class="text-xs text-gray-500 font-medium">Free Shipping</span>
                        </div>
                        <div class="flex flex-col items-center text-center">
                            <i data-lucide="shield-check" class="w-6 h-6 text-gray-400 mb-2"></i>
                            <span class="text-xs text-gray-500 font-medium">Secure Checkout</span>
                        </div>
                        <div class="flex flex-col items-center text-center">
                            <i data-lucide="refresh-cw" class="w-6 h-6 text-gray-400 mb-2"></i>
                            <span class="text-xs text-gray-500 font-medium">30-Day Returns</span>
                        </div>
                        <div class="flex flex-col items-center text-center">
                            <i data-lucide="award" class="w-6 h-6 text-gray-400 mb-2"></i>
                            <span class="text-xs text-gray-500 font-medium">1 Year Warranty</span>
                        </div>
                    </div>

                    <!-- Accordions -->
                    <div class="space-y-4">

                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden bg-white dark:bg-gray-800">
                            <button type="button" @click="accordion = accordion === 'shipping' ? '' : 'shipping'" class="w-full px-6 py-4 flex justify-between items-center text-left focus:outline-none">
                                <span class="font-semibold text-gray-900 dark:text-white">Shipping & Returns</span>
                                <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400 transition-transform duration-300" :class="{'rotate-180': accordion === 'shipping'}"></i>
                            </button>
                            <div x-show="accordion === 'shipping'" x-collapse>
                                <div class="px-6 pb-4 text-sm text-gray-600 dark:text-gray-300 space-y-2">
                                    <p><strong>Free Standard Shipping:</strong> 3-5 business days.</p>
                                    <p><strong>Express Shipping:</strong> 1-2 business days ($15.00).</p>
                                    <p>We accept returns within 30 days of receipt. Items must be in their original condition and packaging. Return shipping is covered by us for defective items.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    <!-- Image Text Sections: Before Description -->
    <x-product-image-text-sections :sections="$product->image_text_sections ?? []" position="before_description" />

    <!-- Full Width Description -->
    @if($product->content)
    <div class="mt-16 lg:mt-24 border-t border-gray-200 dark:border-gray-800 pt-12">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">Product Description</h2>
        <div class="bg-white dark:bg-gray-800 p-4 sm:p-8 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm w-full overflow-hidden">
            <div class="prose dark:prose-invert max-w-none w-full break-words overflow-x-auto">
                {!! $product->content !!}
            </div>
        </div>
    </div>
    @endif
    
    <!-- Image Text Sections: After Description -->
    <x-product-image-text-sections :sections="$product->image_text_sections ?? []" position="after_description" />

    <!-- Image Text Sections: Before Video -->
    <x-product-image-text-sections :sections="$product->image_text_sections ?? []" position="before_video" />

    <!-- Product Video Reels Carousel (5 in a row, hover to play) -->
    @if(!empty($product->video_reels) && count($product->video_reels) > 0)
        <x-product-video-reels :reels="$product->video_reels" />
    @endif

    <!-- Image Text Sections: After Video -->
    <x-product-image-text-sections :sections="$product->image_text_sections ?? []" position="after_video" />
    
    <!-- Frequently Bought Together (Bundles) -->
    @if($product->bundles->count() > 0)
    <div class="mt-16 lg:mt-24 border-t border-gray-200 dark:border-gray-800 pt-12">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">Frequently Bought Together</h2>
        <div class="grid grid-cols-1 gap-6">
            @foreach($product->bundles as $bundle)
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6 shadow-sm flex flex-col md:flex-row gap-8 items-center justify-between">
                    <div class="flex-1 w-full">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">{{ $bundle->title }}</h3>
                        @if($bundle->description)
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">{{ $bundle->description }}</p>
                        @endif
                        
                        <div class="flex flex-wrap items-center gap-4">
                            @foreach($bundle->products as $bp)
                                <a href="{{ route('product.show', $bp->slug) }}" class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors border border-transparent hover:border-gray-200 dark:hover:border-gray-600">
                                    <img src="{{ $bp->thumbnail ? $bp->thumbnail->url : '/assets/images/placeholder.jpg' }}" class="w-16 h-16 rounded-lg object-cover bg-gray-100 dark:bg-gray-900 border border-gray-100 dark:border-gray-700">
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-900 dark:text-white max-w-[120px] truncate" title="{{ $bp->name }}">{{ $bp->name }}</h4>
                                        <span class="text-xs font-bold text-gray-500">{{ number_format($bp->price, 0) }} Tk</span>
                                    </div>
                                </a>
                                @if(!$loop->last)
                                    <i data-lucide="plus" class="w-5 h-5 text-gray-300 dark:text-gray-600 hidden sm:block"></i>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    
                    @php
                        $totalPrice = $bundle->products->sum('price');
                        $discountAmount = $bundle->discount_type === 'percentage' 
                            ? $totalPrice * ($bundle->discount_value / 100) 
                            : $bundle->discount_value;
                        $bundlePrice = max(0, $totalPrice - $discountAmount);
                    @endphp
                    
                    <div class="w-full md:w-auto flex flex-col items-center md:items-end justify-center bg-gray-50 dark:bg-gray-900/50 p-6 rounded-xl border border-gray-100 dark:border-gray-700 min-w-[250px]">
                        <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Total bundle price:</p>
                        <div class="flex items-center gap-3 mb-2">
                            <span class="text-2xl font-black text-accent">{{ number_format($bundlePrice, 0) }} Tk</span>
                            <span class="text-sm text-gray-400 line-through">{{ number_format($totalPrice, 0) }} Tk</span>
                        </div>
                        <span class="text-xs font-bold text-green-600 bg-green-100 dark:bg-green-900/30 dark:text-green-400 px-3 py-1 rounded-full mb-4">
                            Save {{ number_format($discountAmount, 0) }} Tk!
                        </span>
                        <button type="button" 
                            @click="
                                let items = [
                                    @foreach($bundle->products as $bp)
                                        { product_id: {{ $bp->id }}, quantity: 1 },
                                    @endforeach
                                ];
                                items.forEach(item => {
                                    window.dispatchEvent(new CustomEvent('add-to-cart', { detail: item }));
                                });
                            "
                            class="w-full bg-primary hover:bg-secondary text-white font-bold py-3 px-6 rounded-xl transition-colors shadow-sm">
                            Add Bundle to Cart
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Image Text Sections: Before Reviews -->
    <x-product-image-text-sections :sections="$product->image_text_sections ?? []" position="before_reviews" />

    <!-- Customer Reviews Section -->
    <div id="customer-reviews" class="mt-16 lg:mt-24 border-t border-gray-200 dark:border-gray-800 pt-12">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">Customer Reviews</h2>
        
        @if(session('success'))
            <div class="mb-8 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700">
                {!! session('success') !!}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-8 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <!-- Review Summary & Form -->
            <div class="lg:col-span-1">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700">
                    <div class="text-center mb-6">
                        <div class="text-5xl font-extrabold text-gray-900 dark:text-white mb-2">{{ number_format($product->averageRating(), 1) }}</div>
                        <div class="flex justify-center text-yellow-400 mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= round($product->averageRating()))
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                @else
                                    <i data-lucide="star" class="w-5 h-5 text-gray-300 dark:text-gray-600"></i>
                                @endif
                            @endfor
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Based on {{ $product->reviewsCount() }} reviews</p>
                    </div>

                    @auth
                        @php
                            $deliveredOrders = \App\Models\Order::where('user_id', auth()->id())
                                ->where('status', 'delivered')
                                ->whereHas('items', function ($query) use ($product) {
                                    $query->where('product_id', $product->id);
                                })->get();

                            $unreviewedCount = 0;
                            foreach($deliveredOrders as $ord) {
                                $reviewed = \App\Models\Review::where('user_id', auth()->id())
                                    ->where('product_id', $product->id)
                                    ->where('order_id', $ord->id)
                                    ->exists();
                                if(!$reviewed) {
                                    $unreviewedCount++;
                                }
                            }

                            $canReview = $unreviewedCount > 0;
                            $hasReviewed = $deliveredOrders->count() > 0 && $unreviewedCount === 0;
                        @endphp
                        
                        <hr class="border-gray-200 dark:border-gray-700 my-6">
                        @if($hasReviewed)
                            <div class="text-center mt-6 p-4 bg-gray-50 border border-gray-100 dark:bg-gray-800 dark:border-gray-700 rounded-xl">
                                <p class="text-sm text-gray-600 dark:text-gray-400">You have reviewed all your purchases for this product. Thank you!</p>
                            </div>
                        @elseif($canReview)
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Write a Review</h3>
                            <form action="{{ route('product.review.store', $product->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rating</label>
                                    <div class="flex gap-2" x-data="{ rating: 5, hover: 0 }">
                                        <input type="hidden" name="rating" x-model="rating">
                                        <template x-for="i in 5">
                                            <button type="button" 
                                                @click="rating = i" 
                                                @mouseenter="hover = i" 
                                                @mouseleave="hover = 0"
                                                class="focus:outline-none transition-colors"
                                                :class="(hover >= i || (!hover && rating >= i)) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comment (Optional)</label>
                                    <textarea name="comment" rows="3" class="w-full border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 focus:ring-primary focus:border-primary sm:text-sm" placeholder="What did you like or dislike?"></textarea>
                                </div>
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Photos (Optional)</label>
                                    <input type="file" name="media[]" multiple accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-colors">
                                    <p class="text-xs text-gray-400 mt-1">Upload up to 5 images (max 5MB each)</p>
                                </div>
                                <button type="submit" class="w-full bg-primary text-white rounded-xl py-3 px-4 hover:bg-secondary transition-colors font-bold shadow-sm shadow-primary/30">Submit Review</button>
                            </form>
                        @else
                            <div class="text-center mt-6 p-4 bg-gray-50 border border-gray-100 dark:bg-gray-800 dark:border-gray-700 rounded-xl">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Only customers who have purchased this product can leave a review.</p>
                            </div>
                        @endif
                    @else
                        <div class="text-center mt-6">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Please log in to write a review.</p>
                            <a href="{{ route('account.login') }}" class="inline-block border border-primary text-primary hover:bg-primary hover:text-white rounded-xl py-2 px-6 font-medium transition-colors">Log In</a>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- Review List -->
            <div class="lg:col-span-2">
                @if($product->reviews->count() > 0)
                    <div class="space-y-6">
                        @foreach($product->reviews as $review)
                            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-bold text-gray-900 dark:text-white">{{ $review->author_name }}</span>
                                            @if($review->is_verified_purchase)
                                                <span class="inline-flex items-center gap-1 text-[10px] uppercase tracking-wider font-bold text-green-600 bg-green-100 px-2 py-0.5 rounded-full">
                                                    <i data-lucide="check-circle" class="w-3 h-3"></i> Verified Buyer
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex text-yellow-400">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $review->rating)
                                                    <i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i>
                                                @else
                                                    <i data-lucide="star" class="w-3.5 h-3.5 text-gray-300 dark:text-gray-600"></i>
                                                @endif
                                            @endfor
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ $review->created_at->diffForHumans() }}</span>
                                </div>
                                @if($review->comment)
                                    <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed">{{ $review->comment }}</p>
                                @endif
                                
                                @if(!empty($review->media) && is_array($review->media))
                                    <div class="mt-4 flex gap-2 overflow-x-auto pb-2 scrollbar-hide">
                                        @foreach($review->media as $mediaUrl)
                                            <a href="{{ $mediaUrl }}" target="_blank" class="flex-shrink-0">
                                                <img src="{{ $mediaUrl }}" class="h-20 w-20 object-cover rounded-lg border border-gray-200 dark:border-gray-700 hover:opacity-80 transition-opacity">
                                            </a>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                        <i data-lucide="message-square" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">No reviews yet</h3>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">Be the first to share your experience!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Image Text Sections: After Reviews -->
    <x-product-image-text-sections :sections="$product->image_text_sections ?? []" position="after_reviews" />

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
    <div class="border-t border-gray-200 dark:border-gray-800 mt-24">
        <x-product-block 
            title="You Might Also Like" 
            :products="$relatedProducts" 
        />
    </div>
    @endif
    
    <!-- Recently Viewed -->
    @if(isset($recentlyViewed) && $recentlyViewed->count() > 0)
    <div class="border-t border-gray-200 dark:border-gray-800 mt-12 mb-12">
        <x-product-block 
            title="Recently Viewed" 
            :products="$recentlyViewed" 
        />
    </div>
    @endif
    </div> <!-- Close max-w-7xl container -->
</div> <!-- Close Alpine component wrapper -->
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('productPage', () => ({
            product: @json($product),
            basePrice: {{ $product->price }},
            currentPrice: {{ $product->price }},
            baseStock: {{ $product->stock_quantity }},
            currentStock: {{ $product->stock_quantity }},
            quantity: 1,
            loading: false,
            images: [],
            activeImage: '',
            variations: {!! json_encode($product->variations->map(function($var) {
                return [
                    'id' => $var->id,
                    'name' => $var->name,
                    'options' => $var->options,
                    'price' => $var->price,
                    'stock_quantity' => $var->stock_quantity,
                    'media_url' => $var->media ? $var->media->url : null
                ];
            })) !!},
            activeVariation: null,
            selectedOptions: {},
            bundleSettings: @json($product->bundle_settings),
            hasBundles: false,
            selectedBundleTier: 'single',
            bundleItemSelections: [],
            accordion: 'description',
            zoom: false,

            get baseUnitEffectivePrice() {
                return (this.activeVariation && this.activeVariation.price) 
                    ? parseFloat(this.activeVariation.price) 
                    : this.basePrice;
            },

            init() {
                // Compile images array
                if (this.product.thumbnail && this.product.thumbnail.url) {
                    this.images.push(this.product.thumbnail.url);
                }
                
                if (this.product.gallery) {
                    this.product.gallery.forEach(img => {
                        this.images.push(img.url);
                    });
                }
                
                // Add variation images
                this.variations.forEach(v => {
                    if (v.media_url && !this.images.includes(v.media_url)) {
                        this.images.push(v.media_url);
                    }
                });

                // Initialize selected options with the first available values
                if (this.product.options && this.product.options.length > 0) {
                    this.product.options.forEach(opt => {
                        if (opt.values && opt.values.length > 0) {
                            this.selectedOptions[opt.name] = opt.values[0].name;
                        }
                    });
                }

                // Fallback image
                if (this.images.length === 0) {
                    this.images.push('https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80');
                }

                this.activeImage = this.images[0];
                
                if (this.variations.length > 0) {
                    this.updateSelection();
                }

                // Initialize Bundle Offers
                if (this.bundleSettings && this.bundleSettings.enabled && 
                    ((this.bundleSettings.tiers && this.bundleSettings.tiers.length > 0) || this.bundleSettings.show_single !== false)) {
                    this.hasBundles = true;
                    
                    // Determine initial selected tier
                    if (this.bundleSettings.default_tier === 'single') {
                        this.selectedBundleTier = this.bundleSettings.show_single !== false ? 'single' : 0;
                    } else if (typeof this.bundleSettings.default_tier === 'number' && this.bundleSettings.tiers && this.bundleSettings.tiers[this.bundleSettings.default_tier]) {
                        this.selectedBundleTier = this.bundleSettings.default_tier;
                    } else {
                        this.selectedBundleTier = this.bundleSettings.show_single !== false ? 'single' : 0;
                    }

                    // Prepare per-item variation selection models
                    const maxQty = Math.max(10, ...(this.bundleSettings.tiers || []).map(t => parseInt(t.quantity) || 1));
                    this.bundleItemSelections = [];
                    for (let i = 0; i < maxQty; i++) {
                        this.bundleItemSelections.push({ ...this.selectedOptions });
                    }

                    this.recalculateBundlePrices();
                }

                // Fire frontend ViewContent event
                if (typeof window.trackEvent === 'function') {
                    window.trackEvent('ViewContent', {
                        content_name: this.product.name,
                        content_ids: [this.product.id],
                        content_type: 'product',
                        value: this.basePrice,
                        currency: 'BDT'
                    });
                }
            },

            updateSelection() {
                let match = this.variations.find(v => {
                    if (!v.options) return false;
                    for (let key in this.selectedOptions) {
                        if (v.options[key] !== this.selectedOptions[key]) {
                            return false;
                        }
                    }
                    return true;
                });

                if (match) {
                    this.activeVariation = match;
                    this.currentStock = match.stock_quantity;
                    if (match.media_url) this.activeImage = match.media_url;
                } else {
                    this.activeVariation = null;
                    this.currentStock = 0;
                }

                if (this.hasBundles) {
                    this.recalculateBundlePrices();
                } else {
                    this.currentPrice = this.baseUnitEffectivePrice;
                }
            },

            selectOption(optionName, value) {
                this.selectedOptions[optionName] = value;
                this.updateSelection();

                // Keep bundle item default synced
                if (this.bundleItemSelections && this.bundleItemSelections.length > 0) {
                    this.bundleItemSelections.forEach(itemOpts => {
                        itemOpts[optionName] = value;
                    });
                }
            },

            selectBundleTier(tierKey) {
                this.selectedBundleTier = tierKey;
                this.recalculateBundlePrices();
                setTimeout(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }, 40);
            },

            recalculateBundlePrices() {
                if (!this.hasBundles) {
                    this.currentPrice = this.baseUnitEffectivePrice;
                    return;
                }

                if (this.selectedBundleTier === 'single') {
                    this.quantity = 1;
                    this.currentPrice = this.baseUnitEffectivePrice;
                } else if (this.bundleSettings && this.bundleSettings.tiers && this.bundleSettings.tiers[this.selectedBundleTier]) {
                    const tier = this.bundleSettings.tiers[this.selectedBundleTier];
                    this.quantity = parseInt(tier.quantity) || 1;
                    const baseTotal = this.quantity * this.baseUnitEffectivePrice;
                    let discount = 0;
                    if (tier.discount_type === 'percentage') {
                        discount = (baseTotal * (parseFloat(tier.discount_value) || 0)) / 100;
                    } else {
                        discount = parseFloat(tier.discount_value) || 0;
                    }
                    if (discount > baseTotal) discount = baseTotal;
                    const total = baseTotal - discount;
                    this.currentPrice = total / this.quantity;
                }
            },

            calculateTierTotal(tier) {
                const qty = parseInt(tier.quantity) || 1;
                const baseTotal = qty * this.baseUnitEffectivePrice;
                let discount = 0;
                if (tier.discount_type === 'percentage') {
                    discount = (baseTotal * (parseFloat(tier.discount_value) || 0)) / 100;
                } else {
                    discount = parseFloat(tier.discount_value) || 0;
                }
                if (discount > baseTotal) discount = baseTotal;
                return Math.max(0, baseTotal - discount);
            },

            calculateTierSavings(tier) {
                const qty = parseInt(tier.quantity) || 1;
                const baseTotal = qty * this.baseUnitEffectivePrice;
                let discount = 0;
                if (tier.discount_type === 'percentage') {
                    discount = (baseTotal * (parseFloat(tier.discount_value) || 0)) / 100;
                } else {
                    discount = parseFloat(tier.discount_value) || 0;
                }
                return Math.min(discount, baseTotal);
            },

            calculateTierUnitPrice(tier) {
                const qty = parseInt(tier.quantity) || 1;
                return this.calculateTierTotal(tier) / qty;
            },

            formatMoney(val) {
                return Number(val).toFixed(0) + ' BDT';
            },

            onBundleItemOptionChange(itemIndex) {
                const itemOpts = this.bundleItemSelections[itemIndex];
                if (!itemOpts) return;
                let match = this.variations.find(v => {
                    if (!v.options) return false;
                    for (let key in itemOpts) {
                        if (v.options[key] !== itemOpts[key]) return false;
                    }
                    return true;
                });
                if (match && match.media_url) {
                    this.activeImage = match.media_url;
                }
            },

            getSelectedBundleInfo() {
                if (!this.hasBundles) return null;

                if (this.selectedBundleTier === 'single') {
                    if (this.bundleSettings.enable_variations && this.product.options && this.product.options.length > 0) {
                        const singleOpts = this.bundleItemSelections[0] || { ...this.selectedOptions };
                        const varMatch = this.getVariationFromOptions(singleOpts);
                        return {
                            is_bundle: false,
                            bundle_name: this.bundleSettings.single_title || 'Buy 1',
                            unit_price: this.baseUnitEffectivePrice,
                            discount_amount: 0,
                            items: [
                                {
                                    item_number: 1,
                                    options: singleOpts,
                                    variation_id: varMatch ? varMatch.id : (this.activeVariation ? this.activeVariation.id : null),
                                    variation_name: this.getVariationNameFromOptions(singleOpts)
                                }
                            ]
                        };
                    }
                    return null;
                }

                const tier = this.bundleSettings.tiers[this.selectedBundleTier];
                if (!tier) return null;

                const qty = parseInt(tier.quantity) || 1;
                const baseTotal = qty * this.baseUnitEffectivePrice;
                let discount = 0;
                if (tier.discount_type === 'percentage') {
                    discount = (baseTotal * (parseFloat(tier.discount_value) || 0)) / 100;
                } else {
                    discount = parseFloat(tier.discount_value) || 0;
                }
                if (discount > baseTotal) discount = baseTotal;
                const total = baseTotal - discount;
                const unitPrice = total / qty;

                const items = [];
                for (let i = 0; i < qty; i++) {
                    const itemOpts = (this.bundleItemSelections[i] && Object.keys(this.bundleItemSelections[i]).length > 0)
                        ? { ...this.bundleItemSelections[i] }
                        : { ...this.selectedOptions };
                    const varMatch = this.getVariationFromOptions(itemOpts);
                    items.push({
                        item_number: i + 1,
                        options: itemOpts,
                        variation_id: varMatch ? varMatch.id : (this.activeVariation ? this.activeVariation.id : null),
                        variation_name: this.getVariationNameFromOptions(itemOpts)
                    });
                }

                return {
                    is_bundle: true,
                    bundle_name: tier.title,
                    tier_index: this.selectedBundleTier,
                    badge: tier.badge || '',
                    quantity: qty,
                    unit_price: unitPrice,
                    discount_amount: discount,
                    items: items
                };
            },

            getVariationFromOptions(opts) {
                if (!opts || Object.keys(opts).length === 0) return null;
                return this.variations.find(v => {
                    if (!v.options) return false;
                    for (let k in opts) {
                        if (v.options[k] !== opts[k]) return false;
                    }
                    return true;
                }) || null;
            },

            getVariationNameFromOptions(opts) {
                if (!opts || Object.keys(opts).length === 0) return null;
                const match = this.getVariationFromOptions(opts);
                if (match && match.name) return match.name;
                return Object.values(opts).join(' - ');
            },

            isOptionAvailable(optionName, value) {
                // Find if there is ANY variation that has `value` for `optionName`
                // AND matches all OTHER currently selected options, AND has stock > 0
                let match = this.variations.find(v => {
                    if (!v.options || v.options[optionName] !== value || v.stock_quantity <= 0) {
                        return false;
                    }
                    
                    // Check if it matches other selected options
                    for (let key in this.selectedOptions) {
                        if (key !== optionName && v.options[key] !== this.selectedOptions[key]) {
                            return false;
                        }
                    }
                    
                    return true;
                });
                return !!match;
            },

            zoomImage(e) {
                if(!this.zoom) return;
                const image = e.target;
                const { left, top, width, height } = image.getBoundingClientRect();
                const x = (e.clientX - left) / width * 100;
                const y = (e.clientY - top) / height * 100;
                image.style.transformOrigin = `${x}% ${y}%`;
            }
        }));
    });
</script>
@endpush
