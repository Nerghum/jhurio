@extends('layouts.admin')

@section('header', 'Edit Product')

@section('content')
<div class="max-w-6xl mx-auto" x-data="productForm()">
    <form action="{{ route('admin.products.update', $product) }}" method="POST" id="productForm">
        @csrf
        @method('PUT')
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content Area -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Product Title</label>
                        <input type="text" name="name" id="name" x-model="name" @input="generateSlug()" required class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                    </div>
                    
                    <div class="mb-4">
                        <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Permalink</label>
                        <div class="flex rounded-xl shadow-sm">
                            <span class="inline-flex items-center px-3 rounded-xl border border-r-0 border-gray-300 bg-gray-50 text-gray-500 sm:text-sm dark:bg-gray-600 dark:border-gray-500 dark:text-gray-300">
                                {{ url('/') }}/product/
                            </span>
                            <input type="text" name="slug" id="slug" x-model="slug" required class="flex-1 min-w-0 block w-full px-3 py-2 rounded-xl rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary sm:text-sm">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Short Description (Snippet)</label>
                        <textarea name="description" id="description" rows="3" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">{{ old('description', $product->description) }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">This text appears right below the price on the product page. Keep it concise.</p>
                    </div>

                    <div class="mb-4" x-data="quillEditor()">
                        <label for="content" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Description</label>
                        
                        <div class="border border-gray-200 dark:border-gray-600 rounded-xl overflow-hidden bg-white dark:bg-gray-800">
                            <!-- Quill Editor Area -->
                            <div x-ref="quillContainer" class="min-h-[300px] text-gray-900 dark:text-white">{!! old('content', $product->content ?? '') !!}</div>
                            
                            <!-- Hidden input -->
                            <input type="hidden" name="content" x-ref="hiddenInput" value="">
                        </div>
                    </div>
                </div>

                                <!-- Options & Variations -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Options & Variations</h3>
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
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                    <input type="hidden" name="bundle_settings" :value="JSON.stringify(bundle_settings)">

                    <!-- Card Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 mb-4 border-b border-gray-100 dark:border-gray-700">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Quantity Bundle Offers</h3>
                                <span class="text-xs font-medium text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">বান্ডেল অফার</span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                Offer volume discounts (e.g. Buy 2 get 10% off, Buy 3 get ৳300 off) directly above checkout.
                            </p>
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
            </div>
            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Publish Box -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Publish</h3>
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
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Product Media</h3>
                    
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
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet" />
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script src="https://unpkg.com/quill-image-resize-module@3.0.0/image-resize.min.js"></script>
<style>
    .ql-editor ul { list-style-type: disc; padding-left: 1.5em; }
    .ql-editor ol { list-style-type: decimal; padding-left: 1.5em; }
</style>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('quillEditor', () => ({
            quill: null,
            init() {
                const self = this;
                this.quill = new Quill(this.$refs.quillContainer, {
                    theme: 'snow',
                    modules: {
                        imageResize: {},
                        toolbar: {
                            container: [
                                [{ 'header': [1, 2, 3, false] }],
                                ['bold', 'italic', 'underline', 'strike'],
                                [{'list': 'ordered'}, {'list': 'bullet'}],
                                [{'align': []}],
                                ['link', 'image'],
                                ['clean']
                            ],
                            handlers: {
                                image: function() {
                                    window.addEventListener('media-selected-editor', (e) => {
                                        let range = self.quill.getSelection(true);
                                        if(!range) range = { index: self.quill.getLength() };
                                        self.quill.insertEmbed(range.index, 'image', e.detail.url);
                                    }, { once: true });
                                    window.dispatchEvent(new CustomEvent('open-media-modal', { detail: { callbackEvent: 'media-selected-editor' } }));
                                }
                            }
                        }
                    }
                });
                
                this.$refs.hiddenInput.value = this.quill.root.innerHTML;

                this.quill.on('text-change', () => {
                    this.$refs.hiddenInput.value = this.quill.root.innerHTML;
                });
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
            currentMediaTarget: null,
            currentVariationIndex: null,

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
            }
        }));
    });
</script>
@endpush
