@props(['section'])

@php
    $data = $section->data ?? [];
    $image = $data['image'] ?? '';
    $layout = $data['layout'] ?? 'left';
    $buttonText = $data['button_text'] ?? '';
    $buttonLink = $data['button_link'] ?? '#';
    $description = $data['description'] ?? '';
    $title = $section->title ?? '';
    $subtitle = $section->subtitle ?? '';
@endphp

@if($image || $title || $description)
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 my-12">
    <div class="bg-white dark:bg-gray-800 rounded-3xl overflow-hidden shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="flex flex-col {{ $layout === 'right' ? 'md:flex-row-reverse' : 'md:flex-row' }} items-stretch">
            
            <!-- Image Side -->
            @if($image)
            <div class="w-full md:w-1/2 h-64 md:h-auto md:min-h-[400px] relative">
                <img src="{{ $image }}" alt="{{ $title }}" class="absolute inset-0 w-full h-full object-cover">
            </div>
            @endif

            <!-- Content Side -->
            <div class="w-full {{ $image ? 'md:w-1/2' : 'md:w-full' }} p-8 md:p-12 lg:p-16 flex flex-col justify-center">
                @if($subtitle)
                    <span class="text-primary font-semibold tracking-wider uppercase text-sm mb-3">{{ $subtitle }}</span>
                @endif
                
                @if($title)
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-6 leading-tight">{{ $title }}</h2>
                @endif
                
                @if($description)
                    <p class="text-gray-600 dark:text-gray-300 text-lg mb-8 leading-relaxed whitespace-pre-line">{{ $description }}</p>
                @endif
                
                @if($buttonText)
                    <div>
                        <a href="{{ $buttonLink }}" class="inline-flex items-center justify-center px-8 py-3 text-base font-medium text-white bg-primary rounded-xl hover:bg-secondary transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                            {{ $buttonText }}
                            <i data-lucide="arrow-right" class="w-5 h-5 ml-2"></i>
                        </a>
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endif
