@props(['section'])

@php
    $data = $section->data ?? [];
    $faqs = $data['faqs'] ?? [];
    $title = $section->title ?? 'Frequently Asked Questions';
    $subtitle = $section->subtitle ?? 'Everything you need to know about our products and services.';
@endphp

@if(count($faqs) > 0)
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16 my-8">
    
    <div class="text-center mb-12">
        <h2 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-4">{{ $title }}</h2>
        @if($subtitle)
            <p class="text-lg text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">{{ $subtitle }}</p>
        @endif
    </div>

    <div class="space-y-4" x-data="{ activeAccordion: 0 }">
        @foreach($faqs as $index => $faq)
            <div class="bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 rounded-2xl shadow-sm hover:shadow-md transition-all duration-200 overflow-hidden cursor-pointer" 
                 x-data="{ 
                    id: {{ $index }}, 
                    get expanded() { return this.activeAccordion === this.id },
                    set expanded(value) { this.activeAccordion = value ? this.id : null }
                 }"
                 @click="expanded = !expanded">
                 
                <div class="flex justify-between items-center w-full px-6 py-5 text-left focus:outline-none">
                    <span class="text-lg font-semibold text-gray-900 dark:text-gray-100 pr-4 transition-colors duration-200" :class="{ 'text-primary dark:text-primary': expanded }">
                        {{ $faq['question'] ?? '' }}
                    </span>
                    <span class="flex-shrink-0 h-8 w-8 rounded-full flex items-center justify-center bg-primary/5 text-primary transition-all duration-300" :class="{ '-rotate-180 bg-primary text-white': expanded }">
                        <i data-lucide="chevron-down" class="w-5 h-5"></i>
                    </span>
                </div>
                
                <div x-show="expanded" x-collapse x-cloak>
                    <div class="px-6 pb-6 pt-0 text-gray-600 dark:text-gray-400 leading-relaxed text-base border-t border-gray-50 dark:border-gray-700/50 mt-2">
                        <div class="pt-4 whitespace-pre-line">{{ $faq['answer'] ?? '' }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    
</div>
@endif
