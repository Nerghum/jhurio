@extends('layouts.admin')

@section('header', 'Homepage Builder')

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
@endpush

@section('content')
<div x-data="homepageBuilder()" class="max-w-5xl mx-auto space-y-6 pb-12">

    @if(session('success'))
        <div class="bg-green-50 dark:bg-green-900/20 text-green-600 dark:text-green-400 p-4 rounded-xl flex items-center border border-green-200 dark:border-green-800">
            <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i>
            {{ session('success') }}
        </div>
    @endif

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Page Sections</h2>
            <p class="text-sm text-gray-500">Add, configure, and reorder blocks to build your storefront.</p>
        </div>
        
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" @click.outside="open = false" class="px-4 py-2 bg-primary text-white font-medium rounded-xl hover:bg-secondary transition-colors inline-flex items-center shadow-sm">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add Section
            </button>
            
            <div x-show="open" x-transition class="absolute right-0 mt-2 w-56 rounded-xl shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 z-10 border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="py-1">
                    <button @click="addSection('image_text'); open = false" class="w-full text-left flex items-center px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <i data-lucide="image" class="w-4 h-4 mr-3 text-gray-400"></i>
                        Image & Text Block
                    </button>
                    <button @click="addSection('hero'); open = false" class="w-full text-left flex items-center px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">
                        <i data-lucide="image" class="w-4 h-4 mr-3 text-gray-400"></i> Hero Grid
                    </button>
                    <button @click="addSection('category_grid'); open = false" class="w-full text-left flex items-center px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 border-t border-gray-50 dark:border-gray-700/50">
                        <i data-lucide="layout-grid" class="w-4 h-4 mr-3 text-gray-400"></i> Category Grid
                    </button>
                    <button @click="addSection('flash_sale'); open = false" class="w-full text-left flex items-center px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 border-t border-gray-50 dark:border-gray-700/50">
                        <i data-lucide="zap" class="w-4 h-4 mr-3 text-gray-400"></i> Flash Sale
                    </button>
                    <button @click="addSection('custom_products'); open = false" class="w-full text-left flex items-center px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 border-t border-gray-50 dark:border-gray-700/50">
                        <i data-lucide="package" class="w-4 h-4 mr-3 text-gray-400"></i> Custom Products
                    </button>
                    <button @click="addSection('faq'); open = false" class="w-full text-left flex items-center px-4 py-3 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 border-t border-gray-50 dark:border-gray-700/50">
                        <i data-lucide="help-circle" class="w-4 h-4 mr-3 text-gray-400"></i> FAQ Section
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- New Section Form (Unsaved) -->
    <template x-if="newSection">
        <div class="bg-primary/5 border border-primary/20 rounded-xl overflow-hidden shadow-sm mb-6">
            <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between border-b border-primary/10">
                <div class="flex items-center">
                    <span class="inline-flex items-center justify-center h-8 w-8 rounded-lg bg-primary text-white mr-3">
                        <i data-lucide="plus" class="w-5 h-5"></i>
                    </span>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white" x-text="getSectionTypeName(newSection.type)"></h3>
                        <p class="text-xs text-gray-500">New Section (Not Saved)</p>
                    </div>
                </div>
                <div class="flex items-center mt-4 sm:mt-0 space-x-2">
                    <button type="button" @click="saveNewSection()" class="px-4 py-2 bg-primary text-white text-sm font-medium rounded-lg hover:bg-secondary transition-colors">
                        Save
                    </button>
                    <button type="button" @click="newSection = null" class="px-4 py-2 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-600 transition-colors">
                        Cancel
                    </button>
                </div>
            </div>
            <div class="p-4 sm:p-6 bg-white dark:bg-gray-800">
                <template x-if="newSection.type === 'hero'">
                    <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 text-yellow-800 dark:text-yellow-400 rounded-lg text-sm border border-yellow-200 dark:border-yellow-900/30">
                        <i data-lucide="info" class="w-4 h-4 inline mr-1"></i> Save this section first, then expand it to configure the hero images via the Media Library.
                    </div>
                </template>
                <template x-if="newSection.type !== 'hero'">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Section Title</label>
                            <input type="text" x-model="newSection.title" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subtitle</label>
                            <input type="text" x-model="newSection.subtitle" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </template>

    <!-- Sections List -->
    <div class="space-y-4" id="sections-list">
        @forelse($sections as $section)
            <div data-id="{{ $section->id }}" x-data="{ expanded: false }" class="section-item bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden shadow-sm transition-all duration-200" :class="{ 'ring-2 ring-primary border-transparent': expanded }">
                
                <!-- Header (Collapsed View) -->
                <div class="p-4 flex items-center justify-between bg-white dark:bg-gray-800">
                    <div class="flex items-center space-x-4 flex-1">
                        <div class="text-gray-400 cursor-move hover:text-primary transition-colors py-2 pl-1 pr-2">
                            <i data-lucide="grip-vertical" class="w-5 h-5"></i>
                        </div>
                        <div class="w-12 h-12 rounded-lg flex items-center justify-center {{ $section->is_active ? 'bg-primary/10 text-primary' : 'bg-gray-100 text-gray-400 dark:bg-gray-700' }}">
                            @if($section->type === 'category_grid')
                                <i data-lucide="layout-grid" class="w-6 h-6"></i>
                            @elseif($section->type === 'flash_sale')
                                <i data-lucide="zap" class="w-6 h-6"></i>
                            @elseif($section->type === 'custom_products')
                                <i data-lucide="package" class="w-6 h-6"></i>
                            @elseif($section->type === 'image_text')
                                <i data-lucide="image-minus" class="w-6 h-6"></i>
                            @elseif($section->type === 'hero')
                                <i data-lucide="image" class="w-6 h-6"></i>
                            @else
                                <i data-lucide="layers" class="w-6 h-6"></i>
                            @endif
                        </div>
                        <div class="flex-1 cursor-pointer" @click="expanded = !expanded">
                            <h3 class="font-bold text-gray-900 dark:text-white">{{ $section->title ?: 'Untitled ' . ucwords(str_replace('_', ' ', $section->type)) }}</h3>
                            <p class="text-sm text-gray-500">
                                {{ ucwords(str_replace('_', ' ', $section->type)) }} 
                                @if(!$section->is_active)
                                    <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400">Hidden</span>
                                @endif
                            </p>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-1">
                        <button type="button" @click="expanded = !expanded" class="p-2 text-gray-400 hover:text-primary transition-colors bg-white dark:bg-gray-800 rounded-lg">
                            <i data-lucide="chevron-down" class="w-5 h-5 transition-transform duration-200" :class="{ 'rotate-180': expanded }"></i>
                        </button>
                    </div>
                </div>

                <!-- Expanded Content (Form) -->
                <div x-show="expanded" x-collapse class="border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
                    <form action="{{ route('admin.homepage-builder.update', $section->id) }}" method="POST" class="p-6">
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Section Title</label>
                                <input type="text" name="title" value="{{ $section->title }}" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Subtitle</label>
                                <input type="text" name="subtitle" value="{{ $section->subtitle }}" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                            </div>
                        </div>

                        <!-- Dynamic Settings -->
                        <div class="bg-white dark:bg-gray-800 rounded-xl p-5 border border-gray-200 dark:border-gray-700 shadow-sm mb-6">
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4 border-b border-gray-100 dark:border-gray-700 pb-2">Configuration</h4>
                            
                            @if($section->type === 'category_grid')
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Select Category</label>
                                        <select name="data[category_id]" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                                            <option value="">-- Choose a category --</option>
                                            @php $currentCat = $section->data['category_id'] ?? ''; @endphp
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ $currentCat == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Number of products to show</label>
                                        <input type="number" name="data[limit]" value="{{ $section->data['limit'] ?? 8 }}" min="4" max="20" step="4" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                                    </div>
                                </div>
                            @elseif($section->type === 'flash_sale')
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date & Time</label>
                                        <input type="datetime-local" name="data[end_time]" value="{{ $section->data['end_time'] ?? '' }}" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white shadow-sm focus:border-primary focus:ring-primary sm:text-sm">
                                    </div>
                                    <input type="hidden" name="data[auto_pull_discounted]" value="1">
                                    <p class="text-sm text-gray-500">Automatically pulls discounted products.</p>
                                </div>
                                                        @elseif($section->type === 'image_text')
                                <div class="space-y-4">
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Image URL</label>
                                            <div x-data="{ imageUrl: '{{ $section->data['image'] ?? '' }}' }">
                                                <div class="flex space-x-2">
                                                    <input type="text" id="image-text-{{ $section->id }}" name="data[image]" value="{{ $section->data['image'] ?? '' }}" @input="imageUrl = $event.target.value" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 sm:text-sm" placeholder="https://...">
                                                    <button type="button" class="btn btn-secondary px-3 py-2 text-sm flex-shrink-0" @click="openMedia('image-text-{{ $section->id }}')">
                                                        <i data-lucide="folder-search" class="w-4 h-4"></i>
                                                    </button>
                                                </div>
                                                <template x-if="imageUrl">
                                                    <div class="mt-2 w-32 h-20 bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                                                        <img :src="imageUrl" class="w-full h-full object-cover">
                                                    </div>
                                                </template>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Image Alignment</label>
                                            <select name="data[layout]" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 sm:text-sm">
                                                <option value="left" {{ ($section->data['layout'] ?? 'left') === 'left' ? 'selected' : '' }}>Image Left, Text Right</option>
                                                <option value="right" {{ ($section->data['layout'] ?? 'left') === 'right' ? 'selected' : '' }}>Image Right, Text Left</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Button Text (Optional)</label>
                                            <input type="text" name="data[button_text]" value="{{ $section->data['button_text'] ?? '' }}" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 sm:text-sm" placeholder="Shop Now">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Button Link (Optional)</label>
                                            <input type="text" name="data[button_link]" value="{{ $section->data['button_link'] ?? '' }}" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 sm:text-sm" placeholder="/shop">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Content / Description (Optional)</label>
                                        <textarea name="data[description]" rows="3" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 sm:text-sm">{{ $section->data['description'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            @elseif($section->type === 'hero')
                                <div class="space-y-6">
                                    <p class="text-sm text-gray-500 mb-2">Configure the images for the Hero grid layout.</p>
                                    
                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                                        <!-- Main Slider -->
                                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                            <h5 class="font-medium text-sm mb-3">Main Slider Image (Left)</h5>
                                            <div class="flex items-end gap-2">
                                                <div class="flex-1">
                                                    <label class="block text-xs text-gray-500 mb-1">Image URL</label>
                                                    <div x-data="{ imageUrl: '{{ $section->data['hero']['slider']['image'] ?? '' }}' }">
                                                        <div class="flex space-x-2">
                                                            <input type="text" id="hero-slider-{{ $section->id }}" name="data[hero][slider][image]" value="{{ $section->data['hero']['slider']['image'] ?? '' }}" @input="imageUrl = $event.target.value" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 sm:text-sm" placeholder="URL">
                                                            <button type="button" class="btn btn-secondary px-3 py-2 text-sm flex-shrink-0" @click="openMedia('hero-slider-{{ $section->id }}')">
                                                                <i data-lucide="folder-search" class="w-4 h-4"></i>
                                                            </button>
                                                        </div>
                                                        <template x-if="imageUrl">
                                                            <div class="mt-2 w-full h-24 bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                                                                <img :src="imageUrl" class="w-full h-full object-cover">
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                                <div class="flex-1">
                                                    <label class="block text-xs text-gray-500 mb-1">Link URL</label>
                                                    <input type="text" name="data[hero][slider][link]" value="{{ $section->data['hero']['slider']['link'] ?? '' }}" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 sm:text-sm" placeholder="/shop">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Right Top -->
                                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                            <h5 class="font-medium text-sm mb-3">Right Top Banner</h5>
                                            <div class="flex items-end gap-2">
                                                <div class="flex-1">
                                                    <label class="block text-xs text-gray-500 mb-1">Image URL</label>
                                                    <div x-data="{ imageUrl: '{{ $section->data['hero']['right_top']['image'] ?? '' }}' }">
                                                        <div class="flex space-x-2">
                                                            <input type="text" id="hero-right-top-{{ $section->id }}" name="data[hero][right_top][image]" value="{{ $section->data['hero']['right_top']['image'] ?? '' }}" @input="imageUrl = $event.target.value" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 sm:text-sm" placeholder="URL">
                                                            <button type="button" class="btn btn-secondary px-3 py-2 text-sm flex-shrink-0" @click="openMedia('hero-right-top-{{ $section->id }}')">
                                                                <i data-lucide="folder-search" class="w-4 h-4"></i>
                                                            </button>
                                                        </div>
                                                        <template x-if="imageUrl">
                                                            <div class="mt-2 w-full h-24 bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                                                                <img :src="imageUrl" class="w-full h-full object-cover">
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                                <div class="flex-1">
                                                    <label class="block text-xs text-gray-500 mb-1">Link URL</label>
                                                    <input type="text" name="data[hero][right_top][link]" value="{{ $section->data['hero']['right_top']['link'] ?? '' }}" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 sm:text-sm" placeholder="/shop">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Right Bottom -->
                                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                            <h5 class="font-medium text-sm mb-3">Right Bottom Banner</h5>
                                            <div class="flex items-end gap-2">
                                                <div class="flex-1">
                                                    <label class="block text-xs text-gray-500 mb-1">Image URL</label>
                                                    <div x-data="{ imageUrl: '{{ $section->data['hero']['right_bottom']['image'] ?? '' }}' }">
                                                        <div class="flex space-x-2">
                                                            <input type="text" id="hero-right-bottom-{{ $section->id }}" name="data[hero][right_bottom][image]" value="{{ $section->data['hero']['right_bottom']['image'] ?? '' }}" @input="imageUrl = $event.target.value" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 sm:text-sm" placeholder="URL">
                                                            <button type="button" class="btn btn-secondary px-3 py-2 text-sm flex-shrink-0" @click="openMedia('hero-right-bottom-{{ $section->id }}')">
                                                                <i data-lucide="folder-search" class="w-4 h-4"></i>
                                                            </button>
                                                        </div>
                                                        <template x-if="imageUrl">
                                                            <div class="mt-2 w-full h-24 bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                                                                <img :src="imageUrl" class="w-full h-full object-cover">
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                                <div class="flex-1">
                                                    <label class="block text-xs text-gray-500 mb-1">Link URL</label>
                                                    <input type="text" name="data[hero][right_bottom][link]" value="{{ $section->data['hero']['right_bottom']['link'] ?? '' }}" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 sm:text-sm" placeholder="/shop">
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Bottom Strip -->
                                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                            <h5 class="font-medium text-sm mb-3">Bottom Strip Banner</h5>
                                            <div class="flex items-end gap-2">
                                                <div class="flex-1">
                                                    <label class="block text-xs text-gray-500 mb-1">Image URL</label>
                                                    <div x-data="{ imageUrl: '{{ $section->data['hero']['bottom_strip']['image'] ?? '' }}' }">
                                                        <div class="flex space-x-2">
                                                            <input type="text" id="hero-bottom-strip-{{ $section->id }}" name="data[hero][bottom_strip][image]" value="{{ $section->data['hero']['bottom_strip']['image'] ?? '' }}" @input="imageUrl = $event.target.value" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 sm:text-sm" placeholder="URL">
                                                            <button type="button" class="btn btn-secondary px-3 py-2 text-sm flex-shrink-0" @click="openMedia('hero-bottom-strip-{{ $section->id }}')">
                                                                <i data-lucide="folder-search" class="w-4 h-4"></i>
                                                            </button>
                                                        </div>
                                                        <template x-if="imageUrl">
                                                            <div class="mt-2 w-full h-24 bg-gray-100 dark:bg-gray-800 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700">
                                                                <img :src="imageUrl" class="w-full h-full object-cover">
                                                            </div>
                                                        </template>
                                                    </div>
                                                </div>
                                                <div class="flex-1">
                                                    <label class="block text-xs text-gray-500 mb-1">Link URL</label>
                                                    <input type="text" name="data[hero][bottom_strip][link]" value="{{ $section->data['hero']['bottom_strip']['link'] ?? '' }}" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 sm:text-sm" placeholder="/shop">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @elseif($section->type === 'custom_products')
                                <div class="space-y-4" x-data="productSelector({{ $section->id }}, {{ json_encode($section->data['product_ids'] ?? []) }}, {{ json_encode($section->custom_products ?? []) }})">
                                    <div class="relative">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Search Products to Add</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <i data-lucide="search" class="h-5 w-5 text-gray-400"></i>
                                            </div>
                                            <input type="text" x-model="searchQuery" @input.debounce.300ms="searchProducts" class="block w-full pl-10 pr-3 py-2 border border-gray-300 dark:border-gray-700 rounded-xl leading-5 bg-white dark:bg-gray-900 placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary focus:border-primary sm:text-sm transition duration-150 ease-in-out" placeholder="Search by name or SKU...">
                                            
                                            <div x-show="isLoading" class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                                <i data-lucide="loader" class="h-5 w-5 text-gray-400 animate-spin"></i>
                                            </div>
                                        </div>

                                        <!-- Dropdown -->
                                        <div x-show="searchResults.length > 0 && searchQuery.length > 0" @click.away="searchResults = []" class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-800 shadow-lg max-h-60 rounded-xl py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm border border-gray-100 dark:border-gray-700">
                                            <template x-for="product in searchResults" :key="product.id">
                                                <div @click="addProduct(product)" class="cursor-pointer select-none relative py-2 pl-3 pr-9 hover:bg-gray-50 dark:hover:bg-gray-700/50 flex items-center group">
                                                    <img :src="product.image || 'https://via.placeholder.com/40'" class="h-10 w-10 rounded-lg object-cover mr-3 border border-gray-200 dark:border-gray-700 group-hover:border-primary/30 transition-colors">
                                                    <div class="flex-1 min-w-0">
                                                        <span class="block truncate font-medium text-gray-900 dark:text-white" x-text="product.name"></span>
                                                        <span class="block truncate text-gray-500 text-xs">SKU: <span x-text="product.sku"></span> | BDT <span x-text="product.price"></span></span>
                                                    </div>
                                                    <div class="text-primary pr-4 opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <i data-lucide="plus-circle" class="w-5 h-5"></i>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                    
                                    <template x-for="id in selectedIds" :key="id">
                                        <input type="hidden" name="data[product_ids][]" :value="id">
                                    </template>
                                    <template x-if="selectedIds.length === 0">
                                        <input type="hidden" name="data[product_ids]" value="">
                                    </template>

                                    <div>
                                        <h5 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Selected Products (<span x-text="selectedProducts.length"></span>)</h5>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                            <template x-for="(product, index) in selectedProducts" :key="product.id">
                                                <div class="flex items-center justify-between p-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 shadow-sm group">
                                                    <div class="flex items-center space-x-3 overflow-hidden">
                                                        <span class="text-gray-400 font-mono text-xs w-4" x-text="index + 1"></span>
                                                        <img :src="product.image || 'https://via.placeholder.com/32'" class="h-8 w-8 rounded object-cover flex-shrink-0">
                                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-200 truncate" x-text="product.name"></span>
                                                    </div>
                                                    <button type="button" @click="removeProduct(product.id)" class="text-gray-400 hover:text-red-500 p-1 rounded-md opacity-0 group-hover:opacity-100 transition-opacity">
                                                        <i data-lucide="x" class="w-4 h-4"></i>
                                                    </button>
                                                </div>
                                            </template>
                                            <template x-if="selectedProducts.length === 0">
                                                <div class="col-span-full py-4 text-center text-sm text-gray-500 border border-dashed border-gray-300 dark:border-gray-700 rounded-lg">
                                                    No products selected. Search and add products above.
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            @elseif($section->type === 'faq')
                                <div class="space-y-4" x-data="faqEditor({{ json_encode($section->data['faqs'] ?? []) }})">
                                    <div class="flex justify-between items-center mb-4">
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Frequently Asked Questions</label>
                                        <button type="button" @click="addFaq()" class="text-sm bg-primary/10 text-primary px-3 py-1.5 rounded-lg hover:bg-primary/20 transition-colors">
                                            + Add FAQ
                                        </button>
                                    </div>
                                    <div class="space-y-3">
                                        <template x-for="(faq, index) in faqs" :key="index">
                                            <div class="p-4 bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl relative group">
                                                <button type="button" @click="removeFaq(index)" class="absolute top-2 right-2 text-red-500 hover:bg-red-50 dark:hover:bg-red-900/30 p-1.5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                </button>
                                                <div class="space-y-3 mr-8">
                                                    <div>
                                                        <input type="text" :name="`data[faqs][${index}][question]`" x-model="faq.question" placeholder="Question" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 sm:text-sm">
                                                    </div>
                                                    <div>
                                                        <textarea :name="`data[faqs][${index}][answer]`" x-model="faq.answer" rows="2" placeholder="Answer" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 sm:text-sm"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                        <template x-if="faqs.length === 0">
                                            <div class="text-center py-6 border border-dashed border-gray-300 dark:border-gray-700 rounded-xl text-gray-500 text-sm">
                                                No FAQs added yet. Click "+ Add FAQ" to get started.
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Footer Actions -->
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <input type="checkbox" name="is_active" id="is_active_{{ $section->id }}" value="1" {{ $section->is_active ? 'checked' : '' }} class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                                <label for="is_active_{{ $section->id }}" class="ml-2 block text-sm font-medium text-gray-900 dark:text-gray-300">
                                    Active & Visible
                                </label>
                            </div>
                            
                            <div class="flex items-center space-x-3">
                                <button type="button" @click="$dispatch('delete-section', {{ $section->id }})" class="text-red-600 hover:text-red-700 font-medium text-sm py-2 px-3">
                                    Delete Section
                                </button>
                                <button type="submit" class="inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-2 bg-primary text-base font-medium text-white hover:bg-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary sm:text-sm transition-colors">
                                    Save Changes
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div class="text-center py-12 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
                <i data-lucide="layout" class="mx-auto h-12 w-12 text-gray-400"></i>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-200">No sections yet</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Click "Add Section" above to start building.</p>
            </div>
        @endforelse
    </div>

    <!-- Hidden Form for creating new via POST -->
    <form id="create-form" action="{{ route('admin.homepage-builder.store') }}" method="POST" class="hidden">
        @csrf
        <input type="hidden" name="type" id="create-type">
        <input type="hidden" name="title" id="create-title">
        <input type="hidden" name="subtitle" id="create-subtitle">
        <input type="hidden" name="is_active" value="1">
    </form>
    
    <!-- Hidden Form for deletion -->
    <form id="delete-form" method="POST" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('faqEditor', (initialFaqs) => ({
            faqs: initialFaqs && initialFaqs.length > 0 ? initialFaqs : [
                { question: 'What is your return policy?', answer: 'We offer a 14-day return policy for all unused items.' },
                { question: 'How long does shipping take?', answer: 'Standard shipping usually takes 3-5 business days depending on your location.' },
                { question: 'Do you ship internationally?', answer: 'Yes, we ship worldwide. Shipping costs and times will apply.' },
                { question: 'Are your products handmade?', answer: 'Yes, all our products are beautifully handcrafted with love and care by skilled artisans.' },
                { question: 'How can I track my order?', answer: 'You will receive a tracking link via email once your order has successfully shipped.' }
            ],
            addFaq() {
                this.faqs.push({ question: '', answer: '' });
                setTimeout(() => lucide.createIcons(), 50);
            },
            removeFaq(index) {
                this.faqs.splice(index, 1);
            }
        }));
        
        Alpine.data('productSelector', (sectionId, initialIds, initialProducts) => ({
            searchQuery: '',
            searchResults: [],
            isLoading: false,
            selectedIds: initialIds || [],
            selectedProducts: initialProducts || [],
            
            async init() {
            },
            
            async searchProducts() {
                if (this.searchQuery.length < 2) {
                    this.searchResults = [];
                    return;
                }
                
                this.isLoading = true;
                try {
                    const response = await fetch(`/admin/homepage-builder/search-products?q=${encodeURIComponent(this.searchQuery)}`);
                    const data = await response.json();
                    // Filter out already selected products
                    this.searchResults = data.filter(p => !this.selectedIds.includes(p.id.toString()) && !this.selectedIds.includes(p.id));
                } catch (e) {
                    console.error(e);
                }
                this.isLoading = false;
                setTimeout(() => lucide.createIcons(), 50);
            },
            
            addProduct(product) {
                if (!this.selectedIds.includes(product.id) && !this.selectedIds.includes(product.id.toString())) {
                    this.selectedIds.push(product.id);
                    this.selectedProducts.push(product);
                }
                this.searchQuery = '';
                this.searchResults = [];
                setTimeout(() => lucide.createIcons(), 50);
            },
            
            removeProduct(id) {
                this.selectedIds = this.selectedIds.filter(i => i.toString() !== id.toString());
                this.selectedProducts = this.selectedProducts.filter(p => p.id.toString() !== id.toString());
            }
        }));

        Alpine.data('homepageBuilder', () => ({
            newSection: null,
            
            init() {
                const list = document.getElementById('sections-list');
                if(list) {
                    new Sortable(list, {
                        animation: 150,
                        handle: '.cursor-move',
                        ghostClass: 'opacity-50',
                        onEnd: (evt) => {
                            this.saveOrder();
                        }
                    });
                }
                
                window.addEventListener('delete-section', (e) => {
                    if (confirm('Are you sure you want to delete this section?')) {
                        const form = document.getElementById('delete-form');
                        form.action = `/admin/homepage-builder/${e.detail}`;
                        form.submit();
                    }
                });
                
                setTimeout(() => lucide.createIcons(), 50);
            },
            
            getSectionTypeName(type) {
                return type.split('_').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');
            },
            
            openMedia(targetInputId) {
                const eventName = 'media-selected-' + targetInputId;
                
                if (this._currentMediaListener) {
                    window.removeEventListener(eventName, this._currentMediaListener);
                }
                
                this._currentMediaListener = (e) => {
                    const input = document.getElementById(targetInputId);
                    if (input) {
                        input.value = e.detail.url;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                    window.removeEventListener(eventName, this._currentMediaListener);
                    this._currentMediaListener = null;
                };
                
                window.addEventListener(eventName, this._currentMediaListener);
                window.dispatchEvent(new CustomEvent('open-media-modal', { detail: { callbackEvent: eventName } }));
            },
            
            addSection(type) {
                this.newSection = {
                    type: type,
                    title: type === 'hero' ? 'Hero Grid' : (type === 'image_text' ? 'Image & Text Block' : this.getSectionTypeName(type)),
                    subtitle: ''
                };
                window.scrollTo({ top: 0, behavior: 'smooth' });
                setTimeout(() => lucide.createIcons(), 50);
            },
            
            saveNewSection() {
                document.getElementById('create-type').value = this.newSection.type;
                document.getElementById('create-title').value = this.newSection.title;
                document.getElementById('create-subtitle').value = this.newSection.subtitle;
                document.getElementById('create-form').submit();
            },
            
            async saveOrder() {
                const items = Array.from(document.querySelectorAll('.section-item'));
                const orders = items.map((item, index) => ({
                    id: item.dataset.id,
                    order: index
                }));
                
                try {
                    await fetch('{{ route("admin.homepage_builder.reorder") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ orders })
                    });
                } catch (e) {
                    console.error('Failed to save order', e);
                }
            }
        }));
    });
</script>
@endpush