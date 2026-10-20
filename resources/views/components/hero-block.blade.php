@props(['section', 'categories'])

@php
    $hero = $section->data['hero'] ?? [];
    $slider = $hero['slider'] ?? [];
    $rightTop = $hero['right_top'] ?? [];
    $rightBottom = $hero['right_bottom'] ?? [];
    $bottomStrip = $hero['bottom_strip'] ?? [];
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="grid grid-cols-12 gap-4">
        
        <!-- Part 1: Sidebar Categories -->
        <div class="hidden lg:block lg:col-span-3 bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden h-[420px] flex flex-col">
            <div class="bg-primary text-white p-4 font-bold flex items-center space-x-2">
                <i data-lucide="list" class="w-5 h-5"></i>
                <span>Categories</span>
            </div>
            <ul class="flex-1 overflow-y-auto custom-scrollbar p-2">
                @foreach($categories as $category)
                    <li>
                        <a href="{{ route('shop.index', ['category' => $category->slug]) }}" class="flex items-center justify-between px-3 py-2 text-sm text-gray-700 dark:text-gray-300 hover:text-primary dark:hover:text-primary hover:bg-gray-50 dark:hover:bg-gray-700/50 rounded-lg transition-colors group">
                            <span class="flex items-center space-x-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-gray-300 dark:bg-gray-600 group-hover:bg-primary transition-colors"></span>
                                <span>{{ $category->name }}</span>
                            </span>
                            @if($category->products_count > 0)
                                <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-400 px-2 py-0.5 rounded-full">{{ $category->products_count }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        <!-- Part 2: Main Slider -->
        <div class="col-span-12 md:col-span-12 lg:col-span-6 rounded-xl overflow-hidden shadow-sm relative h-[300px] lg:h-[420px] group bg-gray-100 dark:bg-gray-800">
            @if(!empty($slider['image']))
                <a href="{{ $slider['link'] ?? '#' }}" class="block w-full h-full">
                    <img src="{{ $slider['image'] }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Hero Banner">
                </a>
            @else
                <div class="w-full h-full flex flex-col items-center justify-center text-gray-400">
                    <i data-lucide="image" class="w-12 h-12 mb-2"></i>
                    <span>Configure Main Image</span>
                </div>
            @endif
        </div>

        <!-- Parts 3 & 4: Right Side Static Banners -->
        @if(!empty($rightTop['image']) || !empty($rightBottom['image']))
        <div class="hidden md:flex lg:flex md:col-span-12 lg:col-span-3 flex-row lg:flex-col gap-4 h-[200px] lg:h-[420px]">
            @if(!empty($rightTop['image']))
            <!-- Top Banner -->
            <div class="flex-1 rounded-xl overflow-hidden shadow-sm relative group bg-gray-100 dark:bg-gray-800">
                <a href="{{ $rightTop['link'] ?? '#' }}" class="block w-full h-full relative">
                    <img src="{{ $rightTop['image'] }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Right Top Banner">
                    <div class="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-colors"></div>
                </a>
            </div>
            @endif
            
            @if(!empty($rightBottom['image']))
            <!-- Bottom Banner -->
            <div class="flex-1 rounded-xl overflow-hidden shadow-sm relative group bg-gray-100 dark:bg-gray-800">
                <a href="{{ $rightBottom['link'] ?? '#' }}" class="block w-full h-full relative">
                    <img src="{{ $rightBottom['image'] }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" alt="Right Bottom Banner">
                    <div class="absolute inset-0 bg-black/20 group-hover:bg-black/10 transition-colors"></div>
                </a>
            </div>
            @endif
        </div>
        @endif
    </div>
    
    <!-- Part 5: Bottom Strip (Optional) -->
    @if(!empty($bottomStrip['image']))
        <div class="mt-4 rounded-xl overflow-hidden shadow-sm h-[100px] md:h-[120px] relative group">
            <a href="{{ $bottomStrip['link'] ?? '#' }}" class="block w-full h-full">
                <img src="{{ $bottomStrip['image'] }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-[1.02]" alt="Bottom Strip Banner">
            </a>
        </div>
    @endif
    
    <!-- Trust Badges Strip -->
    <div class="mt-4 grid grid-cols-2 md:grid-cols-4 gap-4 bg-white dark:bg-gray-800 rounded-xl p-4 shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex items-center space-x-3 p-2">
            <div class="text-primary"><i data-lucide="truck" class="w-6 h-6"></i></div>
            <div><h4 class="text-sm font-semibold text-gray-900 dark:text-white">Free Delivery</h4><p class="text-xs text-gray-500">From BDT 5000</p></div>
        </div>
        <div class="flex items-center space-x-3 p-2">
            <div class="text-primary"><i data-lucide="refresh-cw" class="w-6 h-6"></i></div>
            <div><h4 class="text-sm font-semibold text-gray-900 dark:text-white">Easy Returns</h4><p class="text-xs text-gray-500">14 Days Return</p></div>
        </div>
        <div class="flex items-center space-x-3 p-2">
            <div class="text-primary"><i data-lucide="shield-check" class="w-6 h-6"></i></div>
            <div><h4 class="text-sm font-semibold text-gray-900 dark:text-white">Secure Payment</h4><p class="text-xs text-gray-500">100% Safe</p></div>
        </div>
        <div class="flex items-center space-x-3 p-2">
            <div class="text-primary"><i data-lucide="headphones" class="w-6 h-6"></i></div>
            <div><h4 class="text-sm font-semibold text-gray-900 dark:text-white">24/7 Support</h4><p class="text-xs text-gray-500">Dedicated Team</p></div>
        </div>
    </div>
</div>

@push('scripts')
<style>
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: transparent;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #e5e7eb;
        border-radius: 20px;
    }
    .dark .custom-scrollbar::-webkit-scrollbar-thumb {
        background-color: #374151;
    }
</style>
@endpush
