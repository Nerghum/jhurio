@props(['title', 'subtitle' => '', 'category' => null, 'products' => null, 'highlight' => false, 'section' => null])

@php
    $items = $products ?? ($category ? $category->products : collect());
@endphp

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 {{ $highlight ? 'bg-secondary/5 dark:bg-gray-800/50 rounded-2xl py-6 sm:py-8 my-2 sm:my-3' : 'py-1 sm:py-2' }}">
    <div class="flex justify-between items-end mb-4 sm:mb-6">
        <div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white flex items-center gap-2.5 sm:gap-3">
                @if($highlight)
                    <i data-lucide="zap" class="w-6 h-6 sm:w-7 sm:h-7 text-yellow-500"></i>
                @endif
                {{ $title }}
            </h2>
            @if($subtitle)
                <p class="mt-1 text-sm sm:text-base text-gray-500 dark:text-gray-400">{{ $subtitle }}</p>
            @endif
        </div>
        <a href="{{ route('shop.index') }}" class="text-primary font-medium hover:text-secondary transition-colors text-sm sm:text-base hidden sm:block">View all &rarr;</a>
    </div>

    @if($items && $items->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6 lg:gap-8">
            @foreach($items as $product)
                <div class="h-full">
                    <x-product-card :product="$product" :highlight="$highlight" />
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-8 bg-white dark:bg-gray-800 rounded-xl border border-dashed border-gray-300 dark:border-gray-700">
            <i data-lucide="package" class="mx-auto h-10 w-10 text-gray-400"></i>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-200">No products</h3>
            <p class="mt-1 text-xs sm:text-sm text-gray-500 dark:text-gray-400">Check back later for {{ strtolower($title) }}.</p>
        </div>
    @endif
</div>
