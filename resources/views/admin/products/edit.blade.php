@extends('layouts.admin')

@section('header', 'Edit Product')

@section('content')
<div class="max-w-6xl mx-auto" x-data="productForm()">
    <form action="{{ route('admin.products.update', $product) }}" method="POST" id="productForm" @submit="syncAllEditors()">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content Area -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-blue-100/80 dark:border-blue-900/40 border-t-4 border-t-blue-500">
                    <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                <i data-lucide="file-text" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">Basic Information</h3>
                            </div>
                        </div>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-blue-50 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300 border border-blue-200/60 dark:border-blue-800">Essential</span>
                    </div>

                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product Title</label>
                        <input type="text" name="name" id="name" x-model="name" @input="generateSlug()" required class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                    </div>
                    
                    <div class="mb-4">
                        <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Permalink</label>
                        <div class="flex rounded-xl shadow-sm">
                            <span class="inline-flex items-center px-3 rounded-xl border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm dark:bg-gray-600 dark:border-gray-500 dark:text-gray-300 font-mono">
                                {{ url('/') }}/product/
                            </span>
                            <input type="text" name="slug" id="slug" x-model="slug" required class="flex-1 min-w-0 block w-full px-3 py-2 rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary sm:text-sm font-mono">
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Short Description (Snippet)</label>
                            <span class="text-[11px] text-gray-400 font-medium">Rich Text &bull; Appears below price</span>
                        </div>
                        <div class="border border-gray-200 dark:border-gray-600 rounded-xl overflow-hidden bg-white dark:bg-gray-800">
                            <textarea id="description" name="description" rows="5" class="w-full text-sm block border-0 p-3 focus:ring-0 text-gray-900 dark:text-white dark:bg-gray-800" placeholder="Short description / key highlights...">{!! old('description', $product->description) !!}</textarea>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">This snippet appears right below the price on the product page. Supports bullet points, bold text, links, and formatting.</p>
                    </div>

                    <div class="mb-4">
                        <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                        <div class="border border-gray-200 dark:border-gray-600 rounded-xl overflow-hidden bg-white dark:bg-gray-800">
                            <textarea id="content" name="content" rows="12" class="w-full text-sm block border-0 p-3 focus:ring-0 text-gray-900 dark:text-white dark:bg-gray-800" placeholder="Detailed product story, features, benefits, specifications...">{!! old('content', $product->content ?? '') !!}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Options & Variations -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-purple-100/80 dark:border-purple-900/40 border-t-4 border-t-purple-500">
                    <div class="flex justify-between items-center pb-3 mb-4 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center">
                                <i data-lucide="layers" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">Options & Variations</h3>
                            </div>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-purple-50 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300 border border-purple-200/60 dark:border-purple-800" x-text="variations.length + ' Variations'"></span>
                    </div>

                    <!-- Options Builder -->
                    <div class="mb-6 space-y-4">
                        <template x-for="(option, optIndex) in options" :key="optIndex">
                            <div class="p-4 border border-gray-200 dark:border-gray-700 rounded-xl relative bg-gray-50 dark:bg-gray-900/50">
                                <button type="button" @click="removeOption(optIndex)" class="absolute top-3 right-3 text-red-500 hover:text-red-700">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                                
                                <div class="mb-3 w-full sm:w-1/3 sm:min-w-[200px]">
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Option Name</label>
                                    <input type="text" x-model="option.name" placeholder="e.g. Color, Size, Material" class="w-full px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-2">Option Values</label>
                                    <div class="flex flex-wrap gap-2 mb-2">
                                        <template x-for="(val, valIndex) in option.values" :key="valIndex">
                                            <div class="flex items-center bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-1 pr-2 shadow-sm">
                                                <template x-if="option.name.toLowerCase() === 'color'">
                                                    <input type="color" x-model="val.meta" class="w-6 h-6 rounded border-0 cursor-pointer p-0 bg-transparent mr-2">
                                                </template>
                                                <span class="text-sm font-medium mx-2" x-text="val.name"></span>
                                                <button type="button" @click="option.values.splice(valIndex, 1)" class="text-gray-400 hover:text-red-500">
                                                    <i data-lucide="x" class="w-3 h-3"></i>
                                                </button>
                                            </div>
                                        </template>
                                    </div>
                                    <div class="flex items-center gap-2 mt-2">
                                        <input type="text" x-model="option.newValue" @keydown.enter.prevent="addOptionValue(optIndex)" placeholder="Add value (e.g. Red, Small)" class="px-3 py-1.5 text-sm rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white w-48">
                                        <button type="button" @click="addOptionValue(optIndex)" class="px-3 py-1.5 bg-gray-200 dark:bg-gray-700 text-sm font-medium rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600">Add</button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <button type="button" @click="addOption()" class="text-sm px-4 py-2 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 font-medium flex items-center gap-2">
                            <i data-lucide="plus" class="w-4 h-4"></i> Add another option
                        </button>
                    </div>

                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6 mb-4 flex justify-between items-center">
                        <h4 class="font-medium text-gray-900 dark:text-white">Generated Variations (<span x-text="variations.length"></span>)</h4>
                        <button type="button" @click="generateVariations()" class="text-sm px-4 py-2 bg-primary text-white rounded-xl hover:bg-secondary font-medium shadow-sm transition">
                            Generate Variations
                        </button>
                    </div>

                    <div class="space-y-4">
                        <input type="hidden" name="options" :value="JSON.stringify(options)">
                        <input type="hidden" name="variations_json" :value="JSON.stringify(variations)">

                        <template x-for="(variation, index) in variations" :key="index">
                            <div class="flex items-start space-x-4 p-4 border border-gray-200 dark:border-gray-700 rounded-xl relative bg-white dark:bg-gray-800 shadow-sm">
                                <button type="button" @click="removeVariation(index)" class="absolute top-2 right-2 text-red-500 hover:text-red-700">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                                
                                <!-- Variation Image -->
                                <div class="w-20 h-20 flex-shrink-0 bg-gray-50 dark:bg-gray-700 rounded-xl border border-dashed border-gray-300 dark:border-gray-600 flex flex-col items-center justify-center cursor-pointer overflow-hidden group" @click="openMediaSelector('variation', index)">
                                    <template x-if="variation.media_url">
                                        <img :src="variation.media_url" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!variation.media_url">
                                        <div class="text-center p-2">
                                            <i data-lucide="image" class="w-5 h-5 mx-auto text-gray-400 group-hover:text-primary"></i>
                                            <span class="text-[10px] text-gray-500 mt-1 block">Image</span>
                                        </div>
                                    </template>
                                </div>

                                <div class="flex-1">
                                    <div class="font-semibold text-gray-900 dark:text-white mb-3 text-sm">
                                        <span x-text="variation.name"></span>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Stock</label>
                                            <input type="number" x-model.number="variation.stock_quantity" class="w-full px-3 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="0">
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Price (opt)</label>
                                            <input type="number" step="0.01" x-model.number="variation.price" class="w-full px-3 py-1.5 text-sm rounded-lg border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="Base price if empty">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <div x-show="variations.length === 0" class="text-center py-8 text-gray-500 text-sm border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-800/50">
                            No variations generated yet. Add options and click "Generate Variations".
                        </div>
                    </div>
                </div>

                <!-- Quantity Bundle Offers -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-emerald-100/80 dark:border-emerald-900/40 border-t-4 border-t-emerald-500">
                    <input type="hidden" name="bundle_settings" :value="JSON.stringify(bundle_settings)">

                    <!-- Card Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-4 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <i data-lucide="sparkles" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Quantity Bundle Offers</h3>
                                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200/70 dark:border-emerald-800 px-2 py-0.5 rounded">বান্ডেল অফার</span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Offer volume discounts (e.g. Buy 2 get 10% off, Buy 3 get ৳300 off) directly above checkout.
                                </p>
                            </div>
                        </div>

                        <!-- Toggle Switch with Active Badge -->
                        <div class="flex items-center gap-2 self-start sm:self-center flex-shrink-0">
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="bundle_settings.enabled" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            </label>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded"
                                  :class="bundle_settings.enabled ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300' : 'bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-400'"
                                  x-text="bundle_settings.enabled ? 'Enabled' : 'Disabled'"></span>
                        </div>
                    </div>

                    <!-- Bundle Configuration Body -->
                    <div x-show="bundle_settings.enabled" class="space-y-6 pt-5">
                        <!-- Heading & Subtitle -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Bundle Section Heading</label>
                                <input type="text" x-model="bundle_settings.heading" placeholder="e.g. Special Bundle Offers" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Bundle Subtitle / Promo Note (Optional)</label>
                                <input type="text" x-model="bundle_settings.subtitle" placeholder="e.g. Buy more, save more!" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                            </div>
                        </div>

                        <!-- Feature Toggles -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div @click="bundle_settings.show_single = !bundle_settings.show_single"
                                 class="p-3.5 rounded-xl border cursor-pointer transition flex items-start space-x-3 select-none"
                                 :class="bundle_settings.show_single 
                                     ? 'border-primary/40 bg-primary/[0.03] dark:bg-primary/10 shadow-sm' 
                                     : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 opacity-70 hover:opacity-100'">
                                <div class="mt-0.5 w-4 h-4 rounded border flex items-center justify-center flex-shrink-0 transition"
                                     :class="bundle_settings.show_single ? 'bg-primary border-primary text-white' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700'">
                                    <i data-lucide="check" class="w-3 h-3" x-show="bundle_settings.show_single"></i>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-gray-900 dark:text-white">Show "Buy 1" Option</span>
                                    <span class="block text-[11px] text-gray-500 mt-0.5">Show regular single item before bundle tiers</span>
                                </div>
                            </div>

                            <div @click="bundle_settings.enable_variations = !bundle_settings.enable_variations"
                                 class="p-3.5 rounded-xl border cursor-pointer transition flex items-start space-x-3 select-none"
                                 :class="bundle_settings.enable_variations 
                                     ? 'border-primary/40 bg-primary/[0.03] dark:bg-primary/10 shadow-sm' 
                                     : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 opacity-70 hover:opacity-100'">
                                <div class="mt-0.5 w-4 h-4 rounded border flex items-center justify-center flex-shrink-0 transition"
                                     :class="bundle_settings.enable_variations ? 'bg-primary border-primary text-white' : 'border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700'">
                                    <i data-lucide="check" class="w-3 h-3" x-show="bundle_settings.enable_variations"></i>
                                </div>
                                <div>
                                    <span class="block text-xs font-bold text-gray-900 dark:text-white">Customer Variation Choice</span>
                                    <span class="block text-[11px] text-gray-500 mt-0.5">Allow separate color/size choice per quantity</span>
                                </div>
                            </div>
                        </div>

                        <!-- Single option config (when show_single is true) -->
                        <div x-show="bundle_settings.show_single" 
                             class="p-4 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/30">
                            <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-200/80 dark:border-gray-700">
                                <div class="flex items-center space-x-2">
                                    <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-gray-200/70 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                        <i data-lucide="package" class="w-3.5 h-3.5 text-gray-600 dark:text-gray-400"></i>
                                        <span>Single Unit (Buy 1)</span>
                                    </span>
                                    <span class="text-xs text-gray-500">Regular Price (No discount)</span>
                                </div>
                                <button type="button" 
                                        @click="bundle_settings.default_tier = 'single'"
                                        class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg border text-xs font-medium transition"
                                        :class="bundle_settings.default_tier === 'single' 
                                            ? 'border-primary bg-primary text-white shadow-sm' 
                                            : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:border-gray-300 bg-white dark:bg-gray-800'">
                                    <i data-lucide="check" class="w-3.5 h-3.5" x-show="bundle_settings.default_tier === 'single'"></i>
                                    <span x-text="bundle_settings.default_tier === 'single' ? 'Default Selected' : 'Set as Default'"></span>
                                </button>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Buy 1 Display Title</label>
                                    <input type="text" x-model="bundle_settings.single_title" placeholder="Buy 1 (Regular Price)" class="w-full px-3 py-1.5 text-xs rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Buy 1 Badge / Tag (Optional)</label>
                                    <input type="text" x-model="bundle_settings.single_badge" placeholder="e.g. Standard" class="w-full px-3 py-1.5 text-xs rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                                </div>
                            </div>
                        </div>

                        <!-- Bundle Tiers List -->
                        <div class="pt-2">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h4 class="text-sm font-bold text-gray-900 dark:text-white flex items-center space-x-2">
                                        <span>Discount Tiers</span>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-extrabold bg-primary/10 text-primary" x-text="bundle_settings.tiers ? bundle_settings.tiers.length : 0"></span>
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-0.5">Define multi-quantity bundle offers with percentage or fixed cash discounts.</p>
                                </div>
                                <button type="button" @click="addBundleTier()" class="inline-flex items-center space-x-1.5 px-3 py-1.5 bg-primary text-white text-xs font-bold rounded-lg hover:bg-secondary transition shadow-sm">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                    <span>Add Offer Tier</span>
                                </button>
                            </div>

                            <div class="space-y-4">
                                <template x-for="(tier, tIndex) in bundle_settings.tiers" :key="tIndex">
                                    <div class="p-4 sm:p-5 border rounded-xl relative transition shadow-sm"
                                         :class="bundle_settings.default_tier === tIndex 
                                             ? 'border-primary bg-primary/[0.02] dark:border-primary dark:bg-primary/10 ring-1 ring-primary' 
                                             : 'border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800'">
                                        
                                        <!-- Top Bar: Tier Index, Live Discount Pill, Default Radio & Delete -->
                                        <div class="flex items-center justify-between pb-3 mb-3 border-b border-gray-100 dark:border-gray-700/70">
                                            <div class="flex items-center space-x-2 flex-wrap">
                                                <span class="inline-flex items-center space-x-1.5 px-2.5 py-1 rounded-lg text-xs font-bold bg-primary/10 text-primary border border-primary/20">
                                                    <i data-lucide="tag" class="w-3.5 h-3.5"></i>
                                                    <span x-text="`Tier #${tIndex + 1}: Buy ${tier.quantity || 2} Items`"></span>
                                                </span>

                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-bold bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300"
                                                      x-text="tier.discount_type === 'percentage' ? `${tier.discount_value || 0}% OFF` : `৳${tier.discount_value || 0} OFF`">
                                                </span>

                                                <template x-if="tier.badge">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300 uppercase tracking-wider"
                                                          x-text="tier.badge">
                                                    </span>
                                                </template>
                                            </div>

                                            <div class="flex items-center space-x-2">
                                                <button type="button" 
                                                        @click="bundle_settings.default_tier = tIndex"
                                                        class="inline-flex items-center space-x-1 px-2.5 py-1 rounded-lg border text-xs font-medium transition"
                                                        :class="bundle_settings.default_tier === tIndex 
                                                            ? 'border-primary bg-primary text-white shadow-sm' 
                                                            : 'border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:border-gray-300 bg-gray-50 dark:bg-gray-700/50'">
                                                    <i data-lucide="check" class="w-3.5 h-3.5" x-show="bundle_settings.default_tier === tIndex"></i>
                                                    <span x-text="bundle_settings.default_tier === tIndex ? 'Default Selected' : 'Set as Default'"></span>
                                                </button>

                                                <button type="button" 
                                                        @click="removeBundleTier(tIndex)" 
                                                        title="Remove Tier"
                                                        class="text-gray-400 hover:text-red-500 p-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Tier Inputs: Robust, non-overlapping Layout -->
                                        <div class="space-y-3">
                                            <!-- Row 1: Quantity (fixed 144px) & Offer Title (flex-1 all remaining width) -->
                                            <div class="flex flex-col sm:flex-row gap-3">
                                                <div class="w-full sm:w-36 flex-shrink-0">
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                        Quantity <span class="text-red-500">*</span>
                                                    </label>
                                                    <div class="flex rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden bg-white dark:bg-gray-700 focus-within:ring-1 focus-within:ring-primary focus-within:border-primary">
                                                        <input type="number" min="2" x-model.number="tier.quantity" placeholder="2" 
                                                               class="w-full min-w-0 px-3 py-1.5 text-xs font-bold border-0 focus:ring-0 bg-transparent text-gray-900 dark:text-white text-center">
                                                        <span class="inline-flex items-center px-2.5 bg-gray-100 dark:bg-gray-600 text-gray-500 dark:text-gray-300 text-xs font-semibold border-l border-gray-200 dark:border-gray-500 select-none">pcs</span>
                                                    </div>
                                                </div>

                                                <div class="flex-1 min-w-0">
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                                                        Offer Title / Display Name <span class="text-red-500">*</span>
                                                    </label>
                                                    <input type="text" x-model="tier.title" 
                                                           placeholder="e.g. Buy 2 and Save 10%" 
                                                           class="w-full px-3 py-1.5 text-xs rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                                                </div>
                                            </div>

                                            <!-- Row 2: 3-column grid for Discount Type, Discount Value, Badge -->
                                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Discount Type</label>
                                                    <select x-model="tier.discount_type" 
                                                            class="w-full px-3 py-1.5 text-xs rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                                                        <option value="percentage">% Percentage</option>
                                                        <option value="fixed">৳ Fixed Amount</option>
                                                    </select>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Discount Value</label>
                                                    <div class="flex rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden bg-white dark:bg-gray-700 focus-within:ring-1 focus-within:ring-primary focus-within:border-primary">
                                                        <span class="inline-flex items-center px-3 bg-gray-100 dark:bg-gray-600 text-gray-700 dark:text-gray-200 text-xs font-bold border-r border-gray-200 dark:border-gray-500 select-none"
                                                              x-text="tier.discount_type === 'percentage' ? '%' : '৳'"></span>
                                                        <input type="number" step="0.01" min="0" x-model.number="tier.discount_value" placeholder="10" 
                                                               class="w-full min-w-0 px-3 py-1.5 text-xs font-bold border-0 focus:ring-0 bg-transparent text-gray-900 dark:text-white">
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">Badge Text (Optional)</label>
                                                    <input type="text" x-model="tier.badge" 
                                                           placeholder="e.g. Most Popular" 
                                                           class="w-full px-3 py-1.5 text-xs rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <div x-show="!bundle_settings.tiers || bundle_settings.tiers.length === 0" 
                                     class="text-center py-8 px-4 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/30">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 text-gray-400 mx-auto flex items-center justify-center mb-2">
                                        <i data-lucide="tag" class="w-5 h-5"></i>
                                    </div>
                                    <h5 class="text-xs font-bold text-gray-700 dark:text-gray-300">No Bundle Tiers Configured</h5>
                                    <p class="text-[11px] text-gray-500 mt-1 max-w-sm mx-auto">Click "+ Add Offer Tier" above to create discount tiers like Buy 2, Buy 3, etc.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image & Text Story Sections -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-amber-100/80 dark:border-amber-900/40 border-t-4 border-t-amber-500">
                    <input type="hidden" name="image_text_sections" x-ref="imageTextSectionsInput" :value="JSON.stringify(image_text_sections)">

                    <!-- Card Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-4 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                <i data-lucide="layout-template" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-base font-bold text-gray-900 dark:text-white">Image & Text Story Sections</h3>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300" x-text="image_text_sections.length + ' Section' + (image_text_sections.length === 1 ? '' : 's')"></span>
                                </div>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                    Add rich visual sections with flippable image/text layouts and custom placement anywhere on the product page.
                                </p>
                            </div>
                        </div>
                        <button type="button" @click="addImageTextSection()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition shadow-sm self-start sm:self-center">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Add Story Section</span>
                        </button>
                    </div>

                    <!-- Sections List -->
                    <div class="space-y-4">
                        <template x-for="(section, index) in image_text_sections" :key="section.id">
                            <div class="p-5 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/40 relative space-y-5 transition-all hover:border-gray-300 dark:hover:border-gray-600">
                                <!-- Section Top Bar -->
                                <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-gray-200/80 dark:border-gray-700">
                                    <div class="flex items-center gap-2.5">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-primary text-white text-xs font-black" x-text="index + 1"></span>
                                        <span class="text-sm font-bold text-gray-900 dark:text-white" x-text="section.title ? section.title : 'Untitled Section'"></span>
                                        
                                        <!-- Layout Orientation Badge -->
                                        <span class="text-[11px] font-semibold px-2 py-0.5 rounded-md border"
                                              :class="section.image_position === 'left' ? 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-700' : 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:border-blue-700'"
                                              x-text="section.image_position === 'left' ? '⇄ Flipped (Image Left)' : 'Standard (Text Left)'">
                                        </span>

                                        <!-- Position Badge -->
                                        <span class="text-[11px] font-medium px-2 py-0.5 rounded-md bg-gray-200/80 text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                                              x-text="section.display_position === 'before_description' ? 'Top (Before Desc)' : (section.display_position === 'before_video' ? 'Before Video Reels' : (section.display_position === 'after_video' ? 'After Video Reels' : (section.display_position === 'before_reviews' ? 'Before Reviews' : (section.display_position === 'after_reviews' ? 'Bottom (After Reviews)' : 'After Description'))))">
                                        </span>
                                    </div>

                                    <!-- Action Controls: Up / Down / Remove -->
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="moveSectionUp(index)" :disabled="index === 0" 
                                                title="Move Up"
                                                class="p-1.5 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-200 dark:hover:bg-gray-700 dark:hover:text-white disabled:opacity-30 disabled:pointer-events-none transition">
                                            <i data-lucide="arrow-up" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" @click="moveSectionDown(index)" :disabled="index === image_text_sections.length - 1" 
                                                title="Move Down"
                                                class="p-1.5 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-200 dark:hover:bg-gray-700 dark:hover:text-white disabled:opacity-30 disabled:pointer-events-none transition">
                                            <i data-lucide="arrow-down" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" @click="removeImageTextSection(index)" 
                                                title="Remove Section"
                                                class="p-1.5 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/30 transition ml-1">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Form Grid -->
                                <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
                                    <!-- Left Column: Content Details (7 cols) -->
                                    <div class="lg:col-span-7 space-y-3.5">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                                Eyebrow / Subtitle Badge <span class="text-gray-400 font-normal">(Optional)</span>
                                            </label>
                                            <input type="text" x-model="section.subtitle" 
                                                   placeholder="e.g. 100% Organic, Premium Quality, Handcrafted" 
                                                   class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                                        </div>

                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                                Section Headline / Title <span class="text-red-500">*</span>
                                            </label>
                                            <input type="text" x-model="section.title" 
                                                   placeholder="e.g. Traditional Cold-Pressed Extraction Method" 
                                                   class="w-full px-3.5 py-2 text-xs font-medium rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                                        </div>

                                        <div x-data="sectionEditor(section)">
                                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                                Content / Story Description
                                            </label>
                                            <div class="border border-gray-300 dark:border-gray-600 rounded-xl overflow-hidden bg-white dark:bg-gray-800">
                                                <textarea :id="'section_editor_' + section.id" 
                                                          class="w-full text-sm block border-0 p-3 focus:ring-0 text-gray-900 dark:text-white dark:bg-gray-800"
                                                          rows="5"
                                                          x-text="section.content"></textarea>
                                            </div>
                                        </div>

                                        <!-- Optional Button Link -->
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                                    Button Label <span class="text-gray-400 font-normal">(Optional)</span>
                                                </label>
                                                <input type="text" x-model="section.button_text" 
                                                       placeholder="e.g. Learn More, Order Now" 
                                                       class="w-full px-3 py-1.5 text-xs rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                                    Button URL <span class="text-gray-400 font-normal">(Optional)</span>
                                                </label>
                                                <input type="text" x-model="section.button_url" 
                                                       placeholder="e.g. # or /contact" 
                                                       class="w-full px-3 py-1.5 text-xs rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right Column: Settings & Image (5 cols) -->
                                    <div class="lg:col-span-5 space-y-3.5">
                                        <!-- Placement Selector -->
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                                Where to Show on Product Page
                                            </label>
                                            <select x-model="section.display_position" 
                                                    class="w-full px-3 py-2 text-xs rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                                                <option value="before_description">Before Description</option>
                                                <option value="after_description">After Description (Default)</option>
                                                <option value="before_video">Before Video Reels</option>
                                                <option value="after_video">After Video Reels</option>
                                                <option value="before_reviews">Before Customer Reviews</option>
                                                <option value="after_reviews">After Customer Reviews</option>
                                            </select>
                                        </div>

                                        <!-- Flip Layout / Orientation Toggle -->
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                                Layout Orientation (Flip)
                                            </label>
                                            <div class="grid grid-cols-2 gap-2">
                                                <button type="button" @click="section.image_position = 'right'"
                                                        class="px-2.5 py-2 text-xs font-medium rounded-xl border flex items-center justify-center gap-1.5 transition select-none"
                                                        :class="section.image_position !== 'left' ? 'bg-primary text-white border-primary shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'">
                                                    <span class="font-bold">Text</span>
                                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                                    <span class="font-bold">Image</span>
                                                </button>
                                                <button type="button" @click="section.image_position = 'left'"
                                                        class="px-2.5 py-2 text-xs font-medium rounded-xl border flex items-center justify-center gap-1.5 transition select-none"
                                                        :class="section.image_position === 'left' ? 'bg-primary text-white border-primary shadow-sm' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700'">
                                                    <span class="font-bold">Image</span>
                                                    <i data-lucide="arrow-right" class="w-3.5 h-3.5"></i>
                                                    <span class="font-bold">Text</span>
                                                    <span class="text-[10px] ml-0.5 opacity-80">(Flip)</span>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Section Image Selection -->
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                                Section Image
                                            </label>
                                            
                                            <!-- When Image is selected -->
                                            <div x-show="section.image_url" class="relative group rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-800">
                                                <img :src="section.image_url" class="w-full h-36 object-cover object-center" alt="Section preview">
                                                <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                                                    <button type="button" @click="openSectionMediaSelector(index)" class="px-3 py-1.5 bg-white text-gray-900 rounded-lg text-xs font-semibold hover:bg-gray-100 transition shadow">
                                                        Change
                                                    </button>
                                                    <button type="button" @click="removeSectionImage(index)" class="px-3 py-1.5 bg-red-600 text-white rounded-lg text-xs font-semibold hover:bg-red-700 transition shadow">
                                                        Remove
                                                    </button>
                                                </div>
                                            </div>

                                            <!-- When No Image is selected -->
                                            <div x-show="!section.image_url" 
                                                 @click="openSectionMediaSelector(index)"
                                                 class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-xl p-4 text-center cursor-pointer hover:border-primary dark:hover:border-primary transition group bg-white dark:bg-gray-800">
                                                <div class="w-9 h-9 rounded-full bg-primary/10 text-primary mx-auto flex items-center justify-center mb-1 group-hover:scale-110 transition-transform">
                                                    <i data-lucide="image" class="w-4 h-4"></i>
                                                </div>
                                                <span class="text-xs font-bold text-gray-700 dark:text-gray-200 block">Select from Media Library</span>
                                                <span class="text-[10px] text-gray-400 block mt-0.5">Click to choose or upload image</span>
                                            </div>

                                            <!-- Direct Image URL Input (Fallback) -->
                                            <div class="mt-2">
                                                <input type="text" x-model="section.image_url" 
                                                       placeholder="Or paste direct image URL (https://...)" 
                                                       class="w-full px-3 py-1 text-[11px] rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Empty State -->
                        <div x-show="image_text_sections.length === 0" 
                             class="text-center py-10 px-4 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl bg-gray-50/50 dark:bg-gray-900/30">
                            <div class="w-12 h-12 rounded-full bg-primary/10 text-primary mx-auto flex items-center justify-center mb-3">
                                <i data-lucide="layout-template" class="w-6 h-6"></i>
                            </div>
                            <h5 class="text-sm font-bold text-gray-900 dark:text-white">No Story Sections Added Yet</h5>
                            <p class="text-xs text-gray-500 max-w-md mx-auto mt-1 mb-4">
                                Showcase your product features, organic sources, certifications, or craftsmanship using alternating left/right image and text cards.
                            </p>
                            <button type="button" @click="addImageTextSection()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary text-white text-xs font-bold rounded-xl hover:bg-secondary transition shadow-sm">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                <span>Add First Story Section</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Video Reels Carousel (YouTube Shorts / Reels) -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-red-100/80 dark:border-red-900/40 border-t-4 border-t-red-500">
                    <input type="hidden" name="video_reels" x-ref="videoReelsInput" :value="JSON.stringify(video_reels)">

                    <!-- Card Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-4 border-b border-gray-100 dark:border-gray-700">
                        <div>
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-lg bg-red-500/10 text-red-600 dark:bg-red-500/20 dark:text-red-400 flex items-center justify-center">
                                    <i data-lucide="video" class="w-4 h-4"></i>
                                </div>
                                <div class="flex items-center gap-2">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Video Reels Carousel</h3>
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-red-500/10 text-red-600 dark:bg-red-500/20 dark:text-red-400" 
                                          x-text="video_reels.length + ' Reel' + (video_reels.length === 1 ? '' : 's')"></span>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Display 5 vertical reels in a row after the product description. Hovering over a reel plays video muted, clicking opens the reel in full with sound. Paste a YouTube Shorts URL, watch URL, or 11-character video ID.
                            </p>
                        </div>
                        <button type="button" @click="addVideoReel()" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-red-600 text-white text-xs font-bold rounded-xl hover:bg-red-700 transition shadow-sm self-start sm:self-center">
                            <i data-lucide="plus" class="w-4 h-4"></i>
                            <span>Add Video Reel</span>
                        </button>
                    </div>

                    <!-- Reels List -->
                    <div class="space-y-4">
                        <template x-for="(reel, index) in video_reels" :key="reel.id">
                            <div class="p-4 sm:p-5 rounded-2xl border border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/40 relative space-y-4 transition-all hover:border-gray-300 dark:hover:border-gray-600">
                                <!-- Top Bar -->
                                <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-gray-200/80 dark:border-gray-700">
                                    <div class="flex items-center gap-2.5">
                                        <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-red-600 text-white text-xs font-black" x-text="index + 1"></span>
                                        <span class="text-sm font-bold text-gray-900 dark:text-white" x-text="reel.title ? reel.title : 'Reel #' + (index + 1)"></span>
                                        
                                        <template x-if="reel.badge">
                                            <span class="text-[11px] font-semibold px-2 py-0.5 rounded-md bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300" x-text="reel.badge"></span>
                                        </template>

                                        <template x-if="extractYouTubeId(reel.youtube_id)">
                                            <span class="text-[11px] font-mono px-2 py-0.5 rounded-md bg-gray-200/70 text-gray-700 dark:bg-gray-700 dark:text-gray-300" x-text="'ID: ' + extractYouTubeId(reel.youtube_id)"></span>
                                        </template>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-1">
                                        <button type="button" @click="moveReelUp(index)" :disabled="index === 0" 
                                                title="Move Up"
                                                class="p-1.5 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-200 dark:hover:bg-gray-700 dark:hover:text-white disabled:opacity-30 disabled:pointer-events-none transition">
                                            <i data-lucide="arrow-up" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" @click="moveReelDown(index)" :disabled="index === video_reels.length - 1" 
                                                title="Move Down"
                                                class="p-1.5 rounded-lg text-gray-500 hover:text-gray-900 hover:bg-gray-200 dark:hover:bg-gray-700 dark:hover:text-white disabled:opacity-30 disabled:pointer-events-none transition">
                                            <i data-lucide="arrow-down" class="w-4 h-4"></i>
                                        </button>
                                        <button type="button" @click="removeVideoReel(index)" 
                                                title="Remove Reel"
                                                class="p-1.5 rounded-lg text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/30 transition ml-1">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Input Fields & Live Thumbnail Preview -->
                                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                                    <!-- Left: Inputs (9 cols) -->
                                    <div class="md:col-span-9 space-y-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                                YouTube Shorts / Reel Video ID or Full URL <span class="text-red-500">*</span>
                                            </label>
                                            <div class="relative">
                                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-red-600">
                                                    <i data-lucide="youtube" class="w-4 h-4"></i>
                                                </div>
                                                <input type="text" x-model="reel.youtube_id" 
                                                       @input="reel.youtube_id = reel.youtube_id.trim()"
                                                       placeholder="e.g. https://www.youtube.com/shorts/dQw4w9WgXcQ or dQw4w9WgXcQ" 
                                                       class="w-full pl-9 pr-3.5 py-2 text-xs rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-red-500 focus:border-red-500">
                                            </div>
                                            <p class="text-[11px] text-gray-400 mt-1">
                                                Supports: <code class="font-mono text-gray-500 dark:text-gray-400">youtube.com/shorts/ID</code>, <code class="font-mono text-gray-500 dark:text-gray-400">youtu.be/ID</code>, or 11-char ID.
                                            </p>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                                    Reel Caption / Title <span class="text-gray-400 font-normal">(Optional)</span>
                                                </label>
                                                <input type="text" x-model="reel.title" 
                                                       placeholder="e.g. Real Customer Review, In Use Demonstration" 
                                                       class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-red-500 focus:border-red-500">
                                            </div>

                                            <div>
                                                <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1">
                                                    Corner Badge Tag <span class="text-gray-400 font-normal">(Optional)</span>
                                                </label>
                                                <input type="text" x-model="reel.badge" 
                                                       placeholder="e.g. Review, Trending, Featured, 4.9★" 
                                                       class="w-full px-3.5 py-2 text-xs rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-red-500 focus:border-red-500">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right: Live Thumbnail Preview (3 cols) -->
                                    <div class="md:col-span-3 flex flex-col items-center justify-center">
                                        <label class="block text-xs font-semibold text-gray-700 dark:text-gray-300 mb-1.5 self-start md:self-center">
                                            Live Thumbnail Preview
                                        </label>
                                        <div class="w-24 sm:w-28 aspect-[9/16] rounded-xl overflow-hidden bg-black border border-gray-300 dark:border-gray-700 relative shadow-sm flex items-center justify-center group/thumb">
                                            <template x-if="extractYouTubeId(reel.youtube_id)">
                                                <img :src="'https://img.youtube.com/vi/' + extractYouTubeId(reel.youtube_id) + '/hqdefault.jpg'" 
                                                     alt="Reel preview"
                                                     class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!extractYouTubeId(reel.youtube_id)">
                                                <div class="text-center p-2 text-gray-400">
                                                    <i data-lucide="play" class="w-6 h-6 mx-auto mb-1 opacity-40"></i>
                                                    <span class="text-[10px] block opacity-60">Enter ID above</span>
                                                </div>
                                            </template>
                                            <template x-if="extractYouTubeId(reel.youtube_id)">
                                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center pointer-events-none">
                                                    <i data-lucide="play" class="w-6 h-6 text-white drop-shadow"></i>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <!-- Empty State -->
                        <div x-show="video_reels.length === 0" 
                             class="text-center py-10 px-4 border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl bg-gray-50/50 dark:bg-gray-900/30">
                            <div class="w-12 h-12 rounded-full bg-red-500/10 text-red-600 mx-auto flex items-center justify-center mb-3">
                                <i data-lucide="video" class="w-6 h-6"></i>
                            </div>
                            <h5 class="text-sm font-bold text-gray-900 dark:text-white">No Video Reels Added Yet</h5>
                            <p class="text-xs text-gray-500 max-w-md mx-auto mt-1 mb-4">
                                Add 5 or more YouTube Shorts / Reel videos. They will be displayed in an interactive 5-in-a-row carousel after description, with hover preview and modal playback.
                            </p>
                            <button type="button" @click="addVideoReel()" class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-600 text-white text-xs font-bold rounded-xl hover:bg-red-700 transition shadow-sm">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                <span>Add First Video Reel</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Publish Box -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-emerald-100/80 dark:border-emerald-900/40 border-t-4 border-t-emerald-600">
                    <div class="flex items-center gap-2.5 pb-3 mb-4 border-b border-gray-100 dark:border-gray-700">
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <i data-lucide="check-circle-2" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Publish Settings</h3>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
                        <select name="status" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                            <option value="published" {{ $product->status == 'published' ? 'selected' : '' }}>Published</option>
                            <option value="draft" {{ $product->status == 'draft' ? 'selected' : '' }}>Draft</option>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Global Stock</label>
                        <input type="number" name="stock_quantity" value="{{ $product->stock_quantity }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Base Price (BDT)</label>
                        <input type="number" step="0.01" name="price" value="{{ $product->price }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white" required>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Purchase / Cost Price (BDT)</label>
                        <input type="number" step="0.01" name="purchase_price" value="{{ old('purchase_price', $product->purchase_price ?? '0.00') }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white" placeholder="0.00">
                        <p class="text-xs text-gray-500 mt-1">Cost to acquire/produce. Used for profit & accounts calculation.</p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Compare at Price (BDT) (opt)</label>
                        <input type="number" step="0.01" name="compare_at_price" value="{{ $product->compare_at_price }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                        <p class="text-xs text-gray-500 mt-1">To show a crossed-out regular price, enter it here.</p>
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Categories (Select Multiple)</label>
                        <select name="categories[]" multiple class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white" required style="height: 120px;">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $product->categories->contains('id', $category->id) ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1">Hold CMD/Ctrl to select multiple.</p>
                    </div>
                    <div class="mb-6" x-data="{
                        newTag: '',
                        tags: {!! json_encode($product->tags->pluck('name')) !!} || [],
                        addTag() {
                            if (this.newTag.trim() !== '' && !this.tags.includes(this.newTag.trim())) {
                                this.tags.push(this.newTag.trim());
                                this.newTag = '';
                            }
                        },
                        removeTag(index) {
                            this.tags.splice(index, 1);
                        }
                    }">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tags (Type & Enter)</label>
                        <div class="flex flex-wrap gap-2 mb-2 p-2 min-h-[42px] border border-gray-200 dark:border-gray-600 rounded-xl bg-gray-50 dark:bg-gray-900 focus-within:ring-2 focus-within:ring-primary focus-within:border-primary">
                            <template x-for="(tag, index) in tags" :key="index">
                                <span class="flex items-center gap-1 bg-white dark:bg-gray-800 px-2 py-1 rounded-xl border border-gray-200 dark:border-gray-700 text-sm">
                                    <span x-text="tag" class="text-gray-700 dark:text-gray-300"></span>
                                    <button type="button" @click="removeTag(index)" class="text-gray-400 hover:text-red-500 focus:outline-none">
                                        <i data-lucide="x" class="w-3 h-3"></i>
                                    </button>
                                </span>
                            </template>
                            <input type="text" x-model="newTag" @keydown.enter.prevent="addTag()" placeholder="Add a tag..." class="flex-1 bg-transparent border-0 focus:ring-0 p-0 text-sm text-gray-900 dark:text-white min-w-[100px]">
                        </div>
                        <input type="hidden" name="tags_json" :value="JSON.stringify(tags)">
                        
                        <div class="mt-2 text-xs text-gray-500">
                            Available: 
                            @foreach($tags as $t)
                                <button type="button" @click="if(!tags.includes('{{ $t->name }}')) tags.push('{{ $t->name }}')" class="hover:text-primary transition-colors">{{ $t->name }}</button>@if(!$loop->last), @endif
                            @endforeach
                        </div>
                    </div>
                    <button type="submit" class="w-full py-2.5 px-4 bg-primary text-white rounded-xl font-medium hover:bg-secondary transition-colors">
                        Update Product
                    </button>
                </div>

                <!-- Product Media -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-sky-100/80 dark:border-sky-900/40 border-t-4 border-t-sky-500">
                    <div class="flex items-center gap-2.5 pb-3 mb-4 border-b border-gray-100 dark:border-gray-700">
                        <div class="w-8 h-8 rounded-lg bg-sky-500/10 text-sky-600 dark:text-sky-400 flex items-center justify-center">
                            <i data-lucide="images" class="w-4 h-4"></i>
                        </div>
                        <h3 class="text-base font-bold text-gray-900 dark:text-white">Product Media</h3>
                    </div>
                    
                    <!-- Thumbnail -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Thumbnail Image</label>
                        <div class="w-full h-48 bg-gray-50 dark:bg-gray-700 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 flex flex-col items-center justify-center cursor-pointer overflow-hidden relative group" @click="openMediaSelector('thumbnail')">
                            <template x-if="thumbnail.url">
                                <img :src="thumbnail.url" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!thumbnail.url">
                                <div class="text-center p-4">
                                    <i data-lucide="image" class="w-8 h-8 mx-auto text-gray-400 group-hover:text-primary mb-2"></i>
                                    <span class="text-sm text-gray-500 font-medium">Set thumbnail image</span>
                                </div>
                            </template>
                            <input type="hidden" name="thumbnail_media_id" :value="thumbnail.id">
                        </div>
                        <button type="button" x-show="thumbnail.url" @click.prevent="thumbnail = {}" class="text-sm text-red-500 mt-2 hover:underline">Remove image</button>
                    </div>

                    <!-- Gallery -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Product Gallery</label>
                        <div class="grid grid-cols-3 gap-2 mb-2">
                            <template x-for="(img, index) in gallery" :key="index">
                                <div class="relative aspect-square rounded-xl border border-gray-200 overflow-hidden">
                                    <img :src="img.url" class="w-full h-full object-cover">
                                    <button type="button" @click="gallery.splice(index, 1)" class="absolute top-1 right-1 bg-white rounded-xl p-0.5 text-red-500 shadow">
                                        <i data-lucide="x" class="w-3 h-3"></i>
                                    </button>
                                </div>
                            </template>
                            <div class="aspect-square bg-gray-50 dark:bg-gray-700 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 flex items-center justify-center cursor-pointer hover:border-primary group" @click="openMediaSelector('gallery')">
                                <i data-lucide="plus" class="w-6 h-6 text-gray-400 group-hover:text-primary"></i>
                            </div>
                        </div>
                        <input type="hidden" name="gallery" :value="gallery.map(g => g.id).join(',')">
                    </div>

                    <!-- Video -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Video URL (YouTube/Vimeo)</label>
                        <input type="url" name="video_url" value="{{ $product->video_url }}" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white placeholder-gray-400" placeholder="https://youtube.com/watch?v=...">
                    </div>
                </div>

                <!-- Customer Reviews Summary & Quick Add -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-amber-100/80 dark:border-amber-900/40 border-t-4 border-t-amber-500 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 dark:border-gray-700">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                                <i data-lucide="star" class="w-4 h-4 fill-amber-400 text-amber-400"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">Customer Reviews</h3>
                                <span class="text-xs text-gray-400">{{ $product->reviews->count() }} reviews ({{ $product->reviewsCount() }} active)</span>
                            </div>
                        </div>
                        <a href="{{ route('admin.reviews.create', ['product_id' => $product->id]) }}" 
                           class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-bold text-white bg-primary rounded-xl hover:bg-secondary transition shadow-sm">
                            <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                            <span>Add Review</span>
                        </a>
                    </div>

                    <!-- Rating Score Banner -->
                    <div class="bg-amber-50/70 dark:bg-amber-900/20 rounded-xl p-4 border border-amber-200/60 dark:border-amber-800/60 flex items-center justify-between">
                        <div>
                            <div class="flex items-baseline gap-1">
                                <span class="text-3xl font-black text-gray-900 dark:text-white">{{ number_format($product->averageRating(), 1) }}</span>
                                <span class="text-xs text-gray-500 dark:text-gray-400">/ 5.0</span>
                            </div>
                            <span class="text-[11px] text-gray-500 dark:text-gray-400">Live Storefront Rating</span>
                        </div>
                        <div class="flex flex-col items-end">
                            <div class="flex items-center gap-0.5 text-amber-500">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= round($product->averageRating()) ? 'fill-amber-400 text-amber-400' : 'text-gray-300 dark:text-gray-600 fill-none' }}" viewBox="0 0 20 20" stroke="currentColor" stroke-width="0.5">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endfor
                            </div>
                            <span class="text-[11px] text-amber-600 dark:text-amber-400 font-bold mt-0.5">{{ $product->reviewsCount() }} Approved</span>
                        </div>
                    </div>

                    <!-- Product Reviews List with Direct Edit Buttons -->
                    <div class="space-y-3 max-h-[360px] overflow-y-auto pr-1">
                        @forelse($product->reviews as $r)
                            <div class="p-3 rounded-xl border border-gray-200/80 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/40 text-xs space-y-2 hover:border-gray-300 transition">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <div class="flex items-center gap-1.5 font-bold text-gray-900 dark:text-white">
                                            <span>{{ $r->author_name }}</span>
                                            @if($r->is_verified_purchase)
                                                <span class="text-[10px] bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 px-1.5 py-0.2 rounded font-bold">Verified</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-1 text-amber-500 mt-0.5">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-3 h-3 {{ $i <= $r->rating ? 'fill-amber-400 text-amber-400' : 'text-gray-300 fill-none' }}" viewBox="0 0 20 20" stroke="currentColor" stroke-width="0.5">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                                </svg>
                                            @endfor
                                            <span class="text-[10px] text-gray-400 ml-1">{{ $r->created_at->format('d M Y') }}</span>
                                        </div>
                                    </div>

                                    <!-- Direct Edit & Delete Buttons -->
                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        <a href="{{ route('admin.reviews.edit', $r) }}" 
                                           class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold text-primary bg-primary/10 hover:bg-primary hover:text-white transition shadow-sm"
                                           title="Edit this review">
                                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                            <span>Edit</span>
                                        </a>
                                    </div>
                                </div>

                                @if($r->comment)
                                    <p class="text-[11px] text-gray-600 dark:text-gray-300 line-clamp-2 leading-relaxed bg-white dark:bg-gray-800 p-2 rounded-lg border border-gray-100 dark:border-gray-700">
                                        {{ $r->comment }}
                                    </p>
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-5 px-3 border border-dashed border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-900/20">
                                <p class="text-xs text-gray-500">No reviews yet for this product.</p>
                                <a href="{{ route('admin.reviews.create', ['product_id' => $product->id]) }}" 
                                   class="inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline mt-1.5">
                                    <i data-lucide="plus" class="w-3 h-3"></i> Add the first review
                                </a>
                            </div>
                        @endforelse
                    </div>

                    <div class="pt-2 border-t border-gray-100 dark:border-gray-700">
                        <a href="{{ route('admin.reviews.index', ['product_id' => $product->id]) }}" 
                           class="w-full inline-flex items-center justify-center gap-1.5 py-2 px-3 text-xs font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white bg-gray-100 dark:bg-gray-700/60 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-xl transition">
                            <i data-lucide="list" class="w-3.5 h-3.5"></i>
                            <span>Manage All Reviews Table</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap" rel="stylesheet" />
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3/tinymce.min.js" referrerpolicy="origin"></script>
<style>
    .tox-tinymce {
        border-radius: 0.75rem !important;
        border-color: #e5e7eb !important;
    }
    .dark .tox-tinymce {
        border-color: #4b5563 !important;
    }
    .tox .tox-toolbar-overlord,
    .tox .tox-toolbar,
    .tox .tox-toolbar__primary {
        background: transparent !important;
    }
    .tox .tox-toolbar {
        flex-wrap: wrap !important;
        border-bottom: 1px solid #f3f4f6 !important;
    }
    .dark .tox .tox-toolbar {
        border-bottom-color: #374151 !important;
    }
    .tox .tox-toolbar:last-child {
        border-bottom: none !important;
    }
</style>
<script>
    function getTinyMCEConfig(selector, customOptions = {}) {
        const isDark = document.documentElement.classList.contains('dark') || 
                       (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
        
        return {
            selector: selector,
            base_url: 'https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.3',
            suffix: '.min',
            height: customOptions.height || 360,
            menubar: false,
            branding: false,
            promotion: false,
            statusbar: true,
            entity_encoding: 'raw',
            convert_urls: false,
            relative_urls: false,
            remove_script_host: false,
            skin: isDark ? 'oxide-dark' : 'oxide',
            content_css: isDark ? 'dark' : 'default',
            plugins: 'advlist autolink lists link image table code fullscreen wordcount',
            toolbar_mode: 'wrap',
            toolbar: [
                'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | forecolor backcolor | removeformat',
                'alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link media_library image table | code fullscreen'
            ],
            font_family_formats: 'Hind Siliguri=Hind Siliguri,sans-serif; Noto Sans Bengali=Noto Sans Bengali,sans-serif; Inter=Inter,sans-serif; Outfit=Outfit,sans-serif; Arial=arial,helvetica,sans-serif; Times New Roman=times new roman,times;',
            content_style: `
                @import url('https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Noto+Sans+Bengali:wght@400;500;600;700&display=swap');
                body {
                    font-family: 'Hind Siliguri', 'Noto Sans Bengali', 'Outfit', 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                    font-size: 15px;
                    line-height: 1.85;
                    color: ${isDark ? '#e5e7eb' : '#1f2937'};
                    background-color: ${isDark ? '#111827' : '#ffffff'};
                    padding: 12px 14px;
                    word-break: normal;
                    overflow-wrap: break-word;
                    -webkit-font-smoothing: antialiased;
                    text-rendering: optimizeLegibility;
                }
                p { margin: 0 0 0.75rem 0; line-height: 1.85; }
                h1, h2, h3, h4, h5, h6 { font-family: inherit; font-weight: 600; line-height: 1.35; margin-top: 1rem; margin-bottom: 0.5rem; }
                ul, ol { padding-left: 1.5rem; margin-bottom: 0.75rem; }
                li { line-height: 1.85; }
                img { max-width: 100%; height: auto; border-radius: 8px; }
                table { border-collapse: collapse; width: 100%; margin: 1rem 0; }
                th, td { border: 1px solid ${isDark ? '#374151' : '#e5e7eb'}; padding: 8px 12px; }
                a { color: #22c55e; text-decoration: underline; }
            `,
            setup: function(editor) {
                editor.ui.registry.addIcon('media-folder', '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"></path><circle cx="12" cy="13" r="3"></circle></svg>');

                editor.ui.registry.addButton('media_library', {
                    icon: 'media-folder',
                    tooltip: 'Choose from Jhurio Media Library',
                    onAction: function() {
                        const callbackName = 'tinymce_media_' + Math.random().toString(36).substring(2, 9);
                        window.addEventListener(callbackName, function(e) {
                            if (e.detail && e.detail.url) {
                                const url = e.detail.url;
                                const alt = e.detail.title || e.detail.name || '';
                                editor.insertContent('<img src="' + url + '" alt="' + alt + '" style="max-width: 100%; height: auto; border-radius: 8px;" />');
                            }
                        }, { once: true });
                        window.dispatchEvent(new CustomEvent('open-media-modal', {
                            detail: { callbackEvent: callbackName }
                        }));
                    }
                });

                if (customOptions && typeof customOptions.setup === 'function') {
                    customOptions.setup(editor);
                }
            }
        };
    }

    function initMainEditors() {
        if (typeof tinymce === 'undefined') return;
        tinymce.get('description')?.remove();
        tinymce.get('content')?.remove();

        tinymce.init(getTinyMCEConfig('#description', { height: 270 }));
        tinymce.init(getTinyMCEConfig('#content', { height: 480 }));
    }

    function initSectionEditor(id, section) {
        const editorId = 'section_editor_' + id;
        if (typeof tinymce === 'undefined') return;
        tinymce.get(editorId)?.remove();

        const el = document.getElementById(editorId);
        if (!el) return;

        tinymce.init({
            ...getTinyMCEConfig('#' + editorId, {
                height: 270,
                setup: function(editor) {
                    editor.on('init', function() {
                        if (section && section.content) {
                            editor.setContent(section.content);
                        }
                    });
                    editor.on('change keyup paste input nodechange', function() {
                        if (section) {
                            section.content = editor.getContent();
                        }
                    });
                }
            })
        });
    }

    window.initAllTinyMCEEditors = function() {
        if (typeof tinymce === 'undefined') return;
        initMainEditors();
    };

    if (typeof tinymce !== 'undefined') {
        window.initAllTinyMCEEditors();
    } else {
        document.addEventListener('DOMContentLoaded', () => {
            if (typeof tinymce !== 'undefined') {
                window.initAllTinyMCEEditors();
            } else {
                let count = 0;
                const timer = setInterval(() => {
                    count++;
                    if (typeof tinymce !== 'undefined') {
                        clearInterval(timer);
                        window.initAllTinyMCEEditors();
                    } else if (count > 60) {
                        clearInterval(timer);
                    }
                }, 100);
            }
        });
    }

    document.addEventListener('alpine:init', () => {
        Alpine.data('sectionEditor', (section) => ({
            init() {
                this.$nextTick(() => {
                    if (typeof tinymce !== 'undefined') {
                        initSectionEditor(section.id, section);
                    } else {
                        let count = 0;
                        const timer = setInterval(() => {
                            count++;
                            if (typeof tinymce !== 'undefined') {
                                clearInterval(timer);
                                initSectionEditor(section.id, section);
                            } else if (count > 60) {
                                clearInterval(timer);
                            }
                        }, 100);
                    }
                });
            },
            destroy() {
                if (typeof tinymce !== 'undefined') {
                    tinymce.get('section_editor_' + section.id)?.remove();
                }
            }
        }));

        Alpine.data('productForm', () => ({
            name: {!! json_encode(old('name', $product->name)) !!},
            slug: {!! json_encode(old('slug', $product->slug)) !!},
            thumbnail: {!! json_encode($product->thumbnail ?? new stdClass()) !!},
            gallery: {!! json_encode($product->gallery ?? []) !!},
            options: {!! json_encode($product->options ?? []) !!},
            variations: {!! json_encode($product->variations->map(function($var) {
                return [
                    'name' => $var->name,
                    'options' => $var->options ?? new stdClass(),
                    'stock_quantity' => $var->stock_quantity,
                    'price' => $var->price,
                    'media_id' => $var->media_id,
                    'media_url' => $var->media ? $var->media->url : null
                ];
            })) !!},
            bundle_settings: {!! json_encode($product->bundle_settings ?? [
                'enabled' => false,
                'heading' => 'Special Bundle Offers',
                'subtitle' => 'Buy more, save more!',
                'show_single' => true,
                'single_title' => 'Buy 1 (Regular Price)',
                'single_badge' => 'Standard',
                'enable_variations' => true,
                'default_tier' => 'single',
                'tiers' => [
                    [
                        'quantity' => 2,
                        'title' => 'Buy 2 and Save 10%',
                        'discount_type' => 'percentage',
                        'discount_value' => 10,
                        'badge' => 'Most Popular'
                    ],
                    [
                        'quantity' => 3,
                        'title' => 'Buy 3 and Save 20%',
                        'discount_type' => 'percentage',
                        'discount_value' => 20,
                        'badge' => 'Best Value'
                    ]
                ]
            ]) !!},
            image_text_sections: ({!! json_encode($product->image_text_sections ?? []) !!} || []).map((s, idx) => ({
                id: s.id || ('sec_' + Date.now() + '_' + idx + '_' + Math.random().toString(36).substr(2, 6)),
                subtitle: s.subtitle || '',
                title: s.title || '',
                content: s.content || '',
                image_position: s.image_position || 'right',
                display_position: s.display_position || 'after_description',
                media_id: s.media_id || null,
                image_url: s.image_url || '',
                button_text: s.button_text || '',
                button_url: s.button_url || ''
            })),
            video_reels: ({!! json_encode(old('video_reels', $product->video_reels ?? [])) !!} || []).map((r, idx) => ({
                id: r.id || ('reel_' + Date.now() + '_' + idx + '_' + Math.random().toString(36).substr(2, 6)),
                youtube_id: r.youtube_id || r.video_id || '',
                title: r.title || '',
                badge: r.badge || ''
            })),
            currentMediaTarget: null,
            currentVariationIndex: null,
            currentSectionIndex: null,

            init() {
                // Listen for media selected event
                window.addEventListener('media-selected', (e) => {
                    const media = e.detail;
                    if (this.currentMediaTarget === 'thumbnail') {
                        this.thumbnail = media;
                    } else if (this.currentMediaTarget === 'gallery') {
                        this.gallery.push(media);
                    } else if (this.currentMediaTarget === 'variation') {
                        this.variations[this.currentVariationIndex].media_id = media.id;
                        this.variations[this.currentVariationIndex].media_url = media.url;
                    } else if (this.currentMediaTarget === 'image_text_section') {
                        if (this.currentSectionIndex !== null && this.image_text_sections[this.currentSectionIndex]) {
                            this.image_text_sections[this.currentSectionIndex].media_id = media.id;
                            this.image_text_sections[this.currentSectionIndex].image_url = media.url;
                        }
                    }
                });

                setTimeout(() => {
                    if (typeof lucide !== 'undefined') lucide.createIcons();
                }, 100);
            },

            generateSlug() {
                this.slug = this.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
            },

            addOption() {
                this.options.push({ name: '', values: [], newValue: '' });
            },

            removeOption(index) {
                this.options.splice(index, 1);
            },

            addOptionValue(index) {
                let val = this.options[index].newValue.trim();
                if (val) {
                    let metaValue = null;
                    if (this.options[index].name.toLowerCase() === 'color') {
                        metaValue = '#000000';
                    }
                    this.options[index].values.push({ name: val, meta: metaValue });
                    this.options[index].newValue = '';
                }
            },

            generateVariations() {
                if (this.options.length === 0) return;
                
                // Keep existing variations by mapping their options to a key
                let existingMap = new Map();
                this.variations.forEach(v => {
                    let key = Object.keys(v.options).sort().map(k => v.options[k]).join('|');
                    existingMap.set(key, v);
                });

                let results = [];
                let generate = (optIndex, currentCombo, currentNameParts) => {
                    if (optIndex >= this.options.length) {
                        let name = currentNameParts.join(' - ');
                        let key = Object.keys(currentCombo).sort().map(k => currentCombo[k]).join('|');
                        
                        if (existingMap.has(key)) {
                            results.push(existingMap.get(key));
                        } else {
                            results.push({
                                name: name,
                                options: currentCombo,
                                price: '',
                                stock_quantity: 0,
                                media_id: null,
                                media_url: null
                            });
                        }
                        return;
                    }

                    let option = this.options[optIndex];
                    if (option.values.length === 0) {
                        generate(optIndex + 1, currentCombo, currentNameParts);
                    } else {
                        option.values.forEach(val => {
                            let newCombo = { ...currentCombo };
                            if (option.name.trim()) {
                                newCombo[option.name.trim()] = val.name;
                            }
                            generate(optIndex + 1, newCombo, [...currentNameParts, val.name]);
                        });
                    }
                };

                generate(0, {}, []);
                this.variations = results;
            },

            removeVariation(index) {
                this.variations.splice(index, 1);
            },

            removeGalleryImage(index) {
                this.gallery.splice(index, 1);
            },

            openMediaSelector(target, index = null) {
                this.currentMediaTarget = target;
                this.currentVariationIndex = index;
                window.dispatchEvent(new CustomEvent('open-media-modal', { detail: { callbackEvent: 'media-selected' } }));
            },

            addBundleTier() {
                if (!this.bundle_settings.tiers) {
                    this.bundle_settings.tiers = [];
                }
                const maxQty = this.bundle_settings.tiers.length > 0 
                    ? Math.max(...this.bundle_settings.tiers.map(t => parseInt(t.quantity) || 1)) 
                    : 1;
                const nextQty = maxQty + 1;
                this.bundle_settings.tiers.push({
                    quantity: nextQty,
                    title: `Buy ${nextQty} and Save 15%`,
                    discount_type: 'percentage',
                    discount_value: 15,
                    badge: 'Save More'
                });
                setTimeout(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }, 50);
            },

            removeBundleTier(index) {
                this.bundle_settings.tiers.splice(index, 1);
                if (this.bundle_settings.default_tier === index) {
                    this.bundle_settings.default_tier = 'single';
                }
            },

            addImageTextSection() {
                if (!this.image_text_sections) {
                    this.image_text_sections = [];
                }
                const newId = 'sec_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6);
                this.image_text_sections.push({
                    id: newId,
                    subtitle: '',
                    title: '',
                    content: '',
                    image_position: this.image_text_sections.length % 2 === 1 ? 'left' : 'right',
                    display_position: 'after_description',
                    media_id: null,
                    image_url: '',
                    button_text: '',
                    button_url: ''
                });
                setTimeout(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }, 50);
            },

            removeImageTextSection(index) {
                const sec = this.image_text_sections[index];
                if (sec && typeof tinymce !== 'undefined') {
                    tinymce.get('section_editor_' + sec.id)?.remove();
                }
                this.image_text_sections.splice(index, 1);
            },

            moveSectionUp(index) {
                if (index > 0) {
                    this.syncAllEditors();
                    const curId = this.image_text_sections[index].id;
                    const prevId = this.image_text_sections[index - 1].id;
                    if (typeof tinymce !== 'undefined') {
                        tinymce.get('section_editor_' + curId)?.remove();
                        tinymce.get('section_editor_' + prevId)?.remove();
                    }
                    const temp = this.image_text_sections[index];
                    this.image_text_sections.splice(index, 1);
                    this.image_text_sections.splice(index - 1, 0, temp);
                    this.$nextTick(() => {
                        initSectionEditor(curId, this.image_text_sections[index - 1]);
                        initSectionEditor(prevId, this.image_text_sections[index]);
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                }
            },

            moveSectionDown(index) {
                if (index < this.image_text_sections.length - 1) {
                    this.syncAllEditors();
                    const curId = this.image_text_sections[index].id;
                    const nextId = this.image_text_sections[index + 1].id;
                    if (typeof tinymce !== 'undefined') {
                        tinymce.get('section_editor_' + curId)?.remove();
                        tinymce.get('section_editor_' + nextId)?.remove();
                    }
                    const temp = this.image_text_sections[index];
                    this.image_text_sections.splice(index, 1);
                    this.image_text_sections.splice(index + 1, 0, temp);
                    this.$nextTick(() => {
                        initSectionEditor(curId, this.image_text_sections[index + 1]);
                        initSectionEditor(nextId, this.image_text_sections[index]);
                        if (typeof lucide !== 'undefined') lucide.createIcons();
                    });
                }
            },

            openSectionMediaSelector(index) {
                this.currentMediaTarget = 'image_text_section';
                this.currentSectionIndex = index;
                window.dispatchEvent(new CustomEvent('open-media-modal', { detail: { callbackEvent: 'media-selected' } }));
            },

            removeSectionImage(index) {
                if (this.image_text_sections[index]) {
                    this.image_text_sections[index].media_id = null;
                    this.image_text_sections[index].image_url = '';
                }
            },

            extractYouTubeId(urlOrId) {
                if (!urlOrId) return '';
                const match = urlOrId.match(/(?:shorts\/|v\/|watch\?v=|youtu\.be\/|embed\/|^)([a-zA-Z0-9_-]{11})/);
                return match ? match[1] : (urlOrId.trim().length === 11 ? urlOrId.trim() : '');
            },

            addVideoReel() {
                if (!this.video_reels) this.video_reels = [];
                const newId = 'reel_' + Date.now() + '_' + Math.random().toString(36).substr(2, 6);
                this.video_reels.push({
                    id: newId,
                    youtube_id: '',
                    title: '',
                    badge: 'Reel'
                });
                setTimeout(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }, 50);
            },

            removeVideoReel(index) {
                this.video_reels.splice(index, 1);
            },

            moveReelUp(index) {
                if (index > 0) {
                    const temp = this.video_reels[index];
                    this.video_reels.splice(index, 1);
                    this.video_reels.splice(index - 1, 0, temp);
                    setTimeout(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }, 50);
                }
            },

            moveReelDown(index) {
                if (index < this.video_reels.length - 1) {
                    const temp = this.video_reels[index];
                    this.video_reels.splice(index, 1);
                    this.video_reels.splice(index + 1, 0, temp);
                    setTimeout(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); }, 50);
                }
            },

            syncAllEditors() {
                if (typeof tinymce !== 'undefined') {
                    tinymce.triggerSave();
                    this.image_text_sections.forEach(sec => {
                        const ed = tinymce.get('section_editor_' + sec.id);
                        if (ed) {
                            sec.content = ed.getContent();
                        }
                    });
                }
                if (this.$refs.imageTextSectionsInput) {
                    this.$refs.imageTextSectionsInput.value = JSON.stringify(this.image_text_sections);
                }
                if (this.$refs.videoReelsInput) {
                    this.$refs.videoReelsInput.value = JSON.stringify(this.video_reels);
                }
            }
        }));
    });
</script>
@endpush
