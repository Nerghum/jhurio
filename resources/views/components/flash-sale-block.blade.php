@props(['section'])

@php
    $data = $section->data ?? [];
    $endTime = $data['end_time'] ?? null;
    $products = $section->products;
@endphp

@if($products && $products->count() > 0)
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 bg-red-50 dark:bg-red-900/10 rounded-2xl my-2 sm:my-3 border border-red-100 dark:border-red-900/30 relative overflow-hidden">
    <!-- Decorative background elements -->
    <div class="absolute top-0 right-0 -mt-10 -mr-10 w-40 h-40 bg-red-100 dark:bg-red-900/20 rounded-full blur-2xl opacity-50"></div>
    <div class="absolute bottom-0 left-0 -mb-10 -ml-10 w-40 h-40 bg-yellow-100 dark:bg-yellow-900/20 rounded-full blur-2xl opacity-50"></div>

    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-end mb-4 sm:mb-6 gap-3 sm:gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-sm font-bold mb-2">
                <i data-lucide="zap" class="w-4 h-4"></i>
                Flash Sale
            </div>
            <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900 dark:text-white">
                {{ $section->title ?: 'Limited Time Offer' }}
            </h2>
            @if($section->subtitle)
                <p class="mt-1 text-sm sm:text-base text-gray-500 dark:text-gray-400">{{ $section->subtitle }}</p>
            @endif
        </div>
        
        @if($endTime)
        <!-- Countdown Timer -->
        <div x-data="countdownTimer('{{ $endTime }}')" class="flex items-center gap-3 bg-white dark:bg-gray-800 p-3 rounded-xl shadow-sm border border-red-100 dark:border-red-900/50">
            <div class="flex flex-col items-center">
                <span class="text-xl font-bold text-red-600 dark:text-red-400 w-10 text-center" x-text="days">00</span>
                <span class="text-[10px] uppercase font-bold text-gray-400">Days</span>
            </div>
            <span class="text-lg font-bold text-gray-300 dark:text-gray-600">:</span>
            <div class="flex flex-col items-center">
                <span class="text-xl font-bold text-red-600 dark:text-red-400 w-10 text-center" x-text="hours">00</span>
                <span class="text-[10px] uppercase font-bold text-gray-400">Hrs</span>
            </div>
            <span class="text-lg font-bold text-gray-300 dark:text-gray-600">:</span>
            <div class="flex flex-col items-center">
                <span class="text-xl font-bold text-red-600 dark:text-red-400 w-10 text-center" x-text="minutes">00</span>
                <span class="text-[10px] uppercase font-bold text-gray-400">Min</span>
            </div>
            <span class="text-lg font-bold text-gray-300 dark:text-gray-600">:</span>
            <div class="flex flex-col items-center">
                <span class="text-xl font-bold text-red-600 dark:text-red-400 w-10 text-center" x-text="seconds">00</span>
                <span class="text-[10px] uppercase font-bold text-gray-400">Sec</span>
            </div>
        </div>
        @endif
    </div>

    <div class="relative z-10 flex overflow-x-auto gap-4 sm:gap-6 pb-3 sm:pb-4 snap-x snap-mandatory hide-scrollbar">
        @foreach($products as $product)
            <div class="w-full sm:w-[calc(50%-12px)] lg:w-[calc(25%-18px)] flex-none snap-start transform hover:-translate-y-1 transition-transform duration-300">
                <x-product-card :product="$product" :highlight="true" />
            </div>
        @endforeach
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        if (!Alpine.store('countdownTimerRegistered')) {
            Alpine.data('countdownTimer', (endDate) => ({
                days: '00',
                hours: '00',
                minutes: '00',
                seconds: '00',
                timer: null,
                
                init() {
                    const end = new Date(endDate).getTime();
                    
                    this.timer = setInterval(() => {
                        const now = new Date().getTime();
                        const distance = end - now;
                        
                        if (distance < 0) {
                            clearInterval(this.timer);
                            return;
                        }
                        
                        this.days = String(Math.floor(distance / (1000 * 60 * 60 * 24))).padStart(2, '0');
                        this.hours = String(Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))).padStart(2, '0');
                        this.minutes = String(Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60))).padStart(2, '0');
                        this.seconds = String(Math.floor((distance % (1000 * 60)) / 1000)).padStart(2, '0');
                    }, 1000);
                }
            }));
            Alpine.store('countdownTimerRegistered', true);
        }
    });
</script>
<style>
    /* Hide scrollbar for Chrome, Safari and Opera */
    .hide-scrollbar::-webkit-scrollbar {
        display: none;
    }
    /* Hide scrollbar for IE, Edge and Firefox */
    .hide-scrollbar {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }
</style>
@endpush
@endif
