@props([
    'reels' => [],
    'title' => 'সরাসরি ভিডিওতে দেখুন',
    'subtitle' => 'প্রিভিউ দেখতে মাউস রাখুন • সম্পূর্ণ ভিডিও দেখতে ক্লিক করুন'
])

@php
    $validReels = collect($reels)->filter(function ($item) {
        return !empty($item['youtube_id'] ?? $item['video_id'] ?? null);
    })->values();
@endphp

@if($validReels->count() > 0)
<div x-data="reelsCarousel({{ json_encode($validReels) }})" class="my-14 lg:my-20">
    <div class="border-t border-gray-200 dark:border-gray-800 pt-10 lg:pt-14">
        <!-- Header with Title & Navigation Controls -->
        <div class="flex items-center justify-between gap-3 mb-6 sm:mb-8">
            <div class="min-w-0 flex-1">
                <!-- Badge & Title Inline on Mobile and Desktop -->
                <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap mb-1">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] sm:text-xs font-bold uppercase tracking-wider bg-red-500/10 text-red-600 dark:bg-red-500/20 dark:text-red-400 flex-shrink-0">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-600 animate-pulse"></span>
                        <span>ভিডিও শোকেস</span>
                    </span>
                    <h2 class="text-lg sm:text-2xl lg:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                        {{ $title }}
                    </h2>
                </div>
                <p class="text-[11px] sm:text-sm text-gray-500 dark:text-gray-400 line-clamp-1 sm:line-clamp-none">
                    {{ $subtitle }}
                </p>
            </div>

            <!-- Scroll Buttons (Inline on Mobile & Desktop) -->
            <div class="flex items-center gap-1.5 sm:gap-2 flex-shrink-0">
                <button type="button" @click="scrollLeft()" 
                        class="w-8 h-8 sm:w-10 sm:h-10 rounded-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-center shadow-sm transition-all hover:scale-105 active:scale-95"
                        title="Scroll Left">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <button type="button" @click="scrollRight()" 
                        class="w-8 h-8 sm:w-10 sm:h-10 rounded-full border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 flex items-center justify-center shadow-sm transition-all hover:scale-105 active:scale-95"
                        title="Scroll Right">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>
        </div>

        <!-- 5 Reels in a Row Carousel Container -->
        <div x-ref="slider" 
             class="flex overflow-x-auto scroll-smooth gap-4 sm:gap-5 pb-4 -mx-4 px-4 sm:mx-0 sm:px-0 no-scrollbar snap-x snap-mandatory">
            @foreach($validReels as $index => $reel)
                @php
                    $rawId = $reel['youtube_id'] ?? $reel['video_id'] ?? '';
                    // Clean YouTube ID extraction
                    preg_match('/(?:shorts\/|v\/|watch\?v=|youtu\.be\/|embed\/|^)([a-zA-Z0-9_-]{11})/', $rawId, $matches);
                    $youtubeId = $matches[1] ?? $rawId;
                    $reelTitle = $reel['title'] ?? 'Customer Spotlight';
                    $reelBadge = $reel['badge'] ?? null;
                    $thumbnailUrl = "https://img.youtube.com/vi/{$youtubeId}/hqdefault.jpg";
                @endphp

                <div x-data="{ isHovered: false }" 
                     class="flex-shrink-0 w-[calc(50%-10px)] sm:w-[calc(33.333%-14px)] md:w-[calc(25%-15px)] lg:w-[calc(20%-16px)] snap-start group select-none">
                    
                    <div @mouseenter="isHovered = true"
                         @mouseleave="isHovered = false"
                         @click="openModal('{{ $youtubeId }}', '{{ addslashes($reelTitle) }}')"
                         class="relative aspect-[9/16] rounded-2xl sm:rounded-3xl overflow-hidden bg-black border border-gray-200/70 dark:border-gray-700/80 shadow-md group-hover:shadow-2xl transition-all duration-500 cursor-pointer group-hover:-translate-y-1">
                        
                        <!-- Poster Image (Default State) -->
                        <img src="{{ $thumbnailUrl }}" 
                             alt="{{ $reelTitle }}" 
                             loading="lazy"
                             x-show="!isHovered"
                             class="w-full h-full object-cover object-center group-hover:scale-105 transition-transform duration-700">

                        <!-- YouTube Hover Video Player (Active on Hover) -->
                        <template x-if="isHovered">
                            <iframe src="https://www.youtube-nocookie.com/embed/{{ $youtubeId }}?autoplay=1&mute=1&controls=0&loop=1&playlist={{ $youtubeId }}&modestbranding=1&rel=0&playsinline=1&showinfo=0&iv_load_policy=3"
                                    class="w-full h-full pointer-events-none object-cover scale-105 transition-opacity duration-300"
                                    frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen>
                            </iframe>
                        </template>

                        <!-- Top Floating Badges: YouTube / Custom Badge -->
                        <div class="absolute top-3 left-3 right-3 flex items-center justify-between pointer-events-none z-10">
                            <span class="w-7 h-7 rounded-full bg-black/60 backdrop-blur-md text-white flex items-center justify-center border border-white/20">
                                <i data-lucide="play" class="w-3.5 h-3.5 fill-current ml-0.5"></i>
                            </span>

                            @if($reelBadge)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-primary/90 text-white backdrop-blur-sm shadow">
                                    {{ $reelBadge }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-red-600/90 text-white backdrop-blur-sm shadow">
                                    রিল
                                </span>
                            @endif
                        </div>

                        <!-- Center Play Button (Shown when not hovered) -->
                        <div x-show="!isHovered" class="absolute inset-0 flex items-center justify-center pointer-events-none transition-opacity duration-300">
                            <div class="w-12 h-12 rounded-full bg-white/90 dark:bg-gray-900/90 text-gray-900 dark:text-white shadow-xl flex items-center justify-center transform group-hover:scale-110 transition-transform">
                                <i data-lucide="play" class="w-5 h-5 fill-current ml-0.5 text-primary dark:text-secondary"></i>
                            </div>
                        </div>

                        <!-- Bottom Gradient Overlay & Title -->
                        <div class="absolute inset-x-0 bottom-0 p-3.5 sm:p-4 bg-gradient-to-t from-black/90 via-black/40 to-transparent pointer-events-none z-10 flex flex-col justify-end">
                            <h4 class="text-xs sm:text-sm font-bold text-white line-clamp-2 leading-snug drop-shadow-sm">
                                {{ $reelTitle }}
                            </h4>
                            <span class="text-[10px] text-gray-300 flex items-center gap-1 mt-1 opacity-90">
                                <i data-lucide="volume-2" class="w-3 h-3"></i> সাউন্ডসহ দেখতে ক্লিক করুন
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Fullscreen Reel Lightbox Modal with Audio -->
    <template x-teleport="body">
        <div x-show="modalOpen" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="closeModal()"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/80 backdrop-blur-md p-4">
            
            <div @click.away="closeModal()" 
                 class="relative w-full max-w-sm sm:max-w-md aspect-[9/16] max-h-[90vh] bg-black rounded-3xl overflow-hidden shadow-2xl border border-white/10 flex flex-col">
                
                <!-- Close Button -->
                <button type="button" @click="closeModal()" 
                        class="absolute top-4 right-4 z-20 w-9 h-9 rounded-full bg-black/60 text-white hover:bg-black flex items-center justify-center transition border border-white/20">
                    <i data-lucide="x" class="w-5 h-5"></i>
                </button>

                <!-- Modal YouTube Video Player with Full Audio -->
                <template x-if="modalOpen && activeVideoId">
                    <iframe :src="'https://www.youtube.com/embed/' + activeVideoId + '?autoplay=1&controls=1&modestbranding=1&rel=0&playsinline=1'"
                            class="w-full h-full object-cover"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                    </iframe>
                </template>
            </div>
        </div>
    </template>
</div>
@endif

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('reelsCarousel', (reels) => ({
            reels: reels || [],
            modalOpen: false,
            activeVideoId: null,
            activeTitle: '',

            scrollLeft() {
                if (this.$refs.slider) {
                    this.$refs.slider.scrollBy({ left: -320, behavior: 'smooth' });
                }
            },

            scrollRight() {
                if (this.$refs.slider) {
                    this.$refs.slider.scrollBy({ left: 320, behavior: 'smooth' });
                }
            },

            openModal(videoId, title) {
                this.activeVideoId = videoId;
                this.activeTitle = title;
                this.modalOpen = true;
                document.body.style.overflow = 'hidden';
            },

            closeModal() {
                this.modalOpen = false;
                this.activeVideoId = null;
                document.body.style.overflow = '';
            }
        }));
    });
</script>

<style>
    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }
    .no-scrollbar {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
</style>
