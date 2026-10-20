@php
    $enabled = \App\Models\Setting::get('exit_popup_enabled', '0') === '1';
    $title = \App\Models\Setting::get('exit_popup_title', 'Wait! Don\'t leave yet!');
    $description = \App\Models\Setting::get('exit_popup_description', 'Here is a 10% discount for you.');
    $coupon = \App\Models\Setting::get('exit_popup_coupon');
    $image = \App\Models\Setting::get('exit_popup_image');
@endphp

@if($enabled)
<div x-data="exitIntentPopup()" 
     x-show="show" 
     x-transition.opacity.duration.300ms
     class="fixed inset-0 z-[100] flex items-center justify-center bg-black/60 backdrop-blur-sm"
     style="display: none;">
     
    <div @click.away="close()" 
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-90 translate-y-4"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
         x-transition:leave-end="opacity-0 scale-90 translate-y-4"
         class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl max-w-lg w-full mx-4 overflow-hidden relative">
         
        <button @click="close()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 z-10 bg-white/50 dark:bg-gray-900/50 rounded-full p-1 backdrop-blur">
            <i data-lucide="x" class="w-5 h-5"></i>
        </button>

        @if($image)
            <div class="h-48 w-full bg-gray-100 dark:bg-gray-900">
                <img src="{{ $image }}" class="w-full h-full object-cover">
            </div>
        @endif

        <div class="p-8 text-center {{ !$image ? 'pt-12' : '' }}">
            <h2 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-4">{{ $title }}</h2>
            <p class="text-lg text-gray-600 dark:text-gray-300 mb-8">{{ $description }}</p>
            
            @if($coupon)
                <div class="bg-gray-50 dark:bg-gray-900 rounded-xl p-4 border border-dashed border-gray-300 dark:border-gray-600 mb-6">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Use this code at checkout:</p>
                    <div class="flex items-center justify-center gap-3">
                        <span class="text-2xl font-black text-primary tracking-widest uppercase">{{ $coupon }}</span>
                        <button @click="copyCoupon('{{ $coupon }}')" class="text-gray-400 hover:text-primary transition-colors" title="Copy Coupon">
                            <i data-lucide="copy" class="w-5 h-5" x-show="!copied"></i>
                            <i data-lucide="check" class="w-5 h-5 text-green-500" x-show="copied" x-cloak></i>
                        </button>
                    </div>
                </div>
            @endif

            <button @click="close()" class="w-full bg-dark dark:bg-white text-white dark:text-dark font-bold rounded-xl py-4 px-6 hover:bg-gray-800 dark:hover:bg-gray-100 transition-colors shadow-lg">
                Continue Shopping
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('exitIntentPopup', () => ({
            show: false,
            copied: false,
            hasTriggered: false,

            init() {
                // Check if already triggered in this session
                if (sessionStorage.getItem('exit_intent_shown')) {
                    this.hasTriggered = true;
                    return;
                }

                // Mouse leave event (top of viewport)
                document.addEventListener('mouseout', (e) => {
                    if (e.clientY < 0 && !this.hasTriggered && !this.show) {
                        this.trigger();
                    }
                });
            },

            trigger() {
                this.show = true;
                this.hasTriggered = true;
                sessionStorage.setItem('exit_intent_shown', 'true');
            },

            close() {
                this.show = false;
            },

            copyCoupon(code) {
                navigator.clipboard.writeText(code).then(() => {
                    this.copied = true;
                    setTimeout(() => {
                        this.copied = false;
                    }, 2000);
                });
            }
        }));
    });
</script>
@endif
