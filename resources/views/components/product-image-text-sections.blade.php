@props([
    'sections' => [],
    'position' => null,
])

@php
    $filteredSections = collect($sections)->filter(function ($item) use ($position) {
        $pos = $item['display_position'] ?? 'after_description';
        return $position === null || $pos === $position;
    });
@endphp

@if($filteredSections->count() > 0)
    <div class="my-6 sm:my-8 lg:my-10 space-y-6 sm:space-y-8">
        @foreach($filteredSections as $section)
            @php
                $title = $section['title'] ?? '';
                $subtitle = $section['subtitle'] ?? '';
                $content = $section['content'] ?? '';
                $imageUrl = $section['image_url'] ?? '';
                $imagePosition = $section['image_position'] ?? 'right'; // 'right' (Text Left, Image Right) or 'left' (Image Left, Text Right)
                $btnText = $section['button_text'] ?? '';
                $btnUrl = $section['button_url'] ?? '#';
            @endphp

            <div class="bg-gradient-to-br from-gray-50/80 via-white to-gray-50/80 dark:from-gray-800/90 dark:via-gray-800/60 dark:to-gray-900/90 rounded-3xl p-6 sm:p-8 lg:p-10 border border-gray-100 dark:border-gray-700/80 shadow-sm relative overflow-hidden group">
                <div class="flex flex-col {{ $imagePosition === 'left' ? 'lg:flex-row-reverse' : 'lg:flex-row' }} items-center gap-8 lg:gap-12">
                    
                    <!-- Content Column -->
                    @if($title || $subtitle || $content || $btnText)
                    <div class="w-full {{ $imageUrl ? 'lg:w-1/2' : 'lg:w-full' }} flex flex-col justify-center">
                        @if($subtitle)
                            <div class="mb-3">
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-primary/10 text-primary dark:bg-primary/20 dark:text-secondary">
                                    <span class="w-1.5 h-1.5 rounded-full bg-primary dark:bg-secondary"></span>
                                    {{ $subtitle }}
                                </span>
                            </div>
                        @endif

                        @if($title)
                            <h3 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white tracking-normal mb-4 leading-[1.35]">
                                {{ $title }}
                            </h3>
                        @endif

                        @if($content)
                            <div class="text-gray-600 dark:text-gray-300 text-sm sm:text-base leading-[1.8] sm:leading-[1.85] mb-6 prose prose-sm sm:prose-base dark:prose-invert max-w-none [&_p]:leading-[1.85] [&_li]:leading-[1.75]">
                                {!! Str::contains($content, ['<p>', '<div>', '<br>', '<h', '<ul>', '<ol>']) ? $content : nl2br(e($content)) !!}
                            </div>
                        @endif

                        @if($btnText)
                            <div class="pt-1">
                                <a href="{{ $btnUrl }}" class="inline-flex items-center justify-center px-6 py-3 text-sm sm:text-base font-semibold text-white bg-primary rounded-xl hover:bg-secondary transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5">
                                    <span>{{ $btnText }}</span>
                                    <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                                </a>
                            </div>
                        @endif
                    </div>
                    @endif

                    <!-- Image Column (Full width, Auto height, Natural ratio) -->
                    @if($imageUrl)
                    <div class="w-full {{ ($title || $subtitle || $content || $btnText) ? 'lg:w-1/2' : 'lg:w-full' }}">
                        <div class="w-full rounded-2xl overflow-hidden bg-white dark:bg-gray-800 shadow-md border border-gray-200/60 dark:border-gray-700/60">
                            <img src="{{ $imageUrl }}" 
                                 alt="{{ $title ?: 'Product feature image' }}" 
                                 loading="lazy"
                                 class="w-full h-auto block rounded-2xl group-hover:scale-[1.01] transition-transform duration-500 ease-out">
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        @endforeach
    </div>
@endif
