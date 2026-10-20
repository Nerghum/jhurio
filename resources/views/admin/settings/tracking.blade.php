@extends('layouts.admin')

@section('title', 'Tracking Configuration')
@section('header', 'Tracking & Pixels')

@section('content')
<div class="max-w-4xl mx-auto">
    @if(session('success'))
        <div class="mb-6 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 px-4 py-3 rounded-xl flex items-center">
            <i data-lucide="check-circle" class="w-5 h-5 mr-3"></i>
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.settings.tracking.update') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Facebook Pixel & CAPI -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm overflow-hidden" 
             x-data="{ 
                 pixels: {{ $tracking['fb_pixels'] ?: '[]' }},
                 addPixel() {
                     this.pixels.push({ pixel_id: '', capi_token: '', test_code: '' });
                 },
                 removePixel(index) {
                     this.pixels.splice(index, 1);
                 },
                 init() {
                     if (this.pixels.length === 0) {
                         this.addPixel();
                     }
                 }
             }">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center justify-between">
                <div class="flex items-center">
                    <i data-lucide="facebook" class="w-5 h-5 mr-3 text-blue-600"></i>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Facebook Pixels & Conversions API</h3>
                </div>
                <button type="button" @click="addPixel" class="flex items-center text-sm font-medium text-primary hover:text-secondary transition-colors">
                    <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Add New Pixel
                </button>
            </div>
            
            <div class="p-6 space-y-6">
                <template x-for="(pixel, index) in pixels" :key="index">
                    <div class="relative p-5 border border-gray-100 dark:border-gray-700 rounded-xl bg-gray-50/50 dark:bg-gray-800/80 mb-4">
                        <div class="absolute top-4 right-4">
                            <button type="button" @click="removePixel(index)" class="text-red-500 hover:text-red-700 transition-colors" title="Remove Pixel">
                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                            </button>
                        </div>
                        
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center">
                            <span class="bg-blue-100 text-blue-700 w-6 h-6 rounded-full flex items-center justify-center mr-2 text-xs" x-text="index + 1"></span>
                            Pixel Configuration
                        </h4>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Pixel ID <span class="text-red-500">*</span></label>
                                <input type="text" x-model="pixel.pixel_id" :name="'fb_pixels['+index+'][pixel_id]'" placeholder="123456789" required class="w-full h-10 px-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Test Event Code (Optional)</label>
                                <input type="text" x-model="pixel.test_code" :name="'fb_pixels['+index+'][test_code]'" placeholder="TEST12345" class="w-full h-10 px-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Conversions API Token</label>
                                <input type="text" x-model="pixel.capi_token" :name="'fb_pixels['+index+'][capi_token]'" placeholder="EAA..." class="w-full h-10 px-3 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all text-sm">
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Required to send events securely from the server.</p>
                            </div>
                        </div>
                    </div>
                </template>
                
                <div x-show="pixels.length === 0" class="text-center py-6">
                    <p class="text-gray-500 dark:text-gray-400 mb-4">No Facebook Pixels configured.</p>
                    <button type="button" @click="addPixel" class="px-4 py-2 bg-primary/10 text-primary font-medium rounded-lg hover:bg-primary/20 transition-colors text-sm">
                        Add First Pixel
                    </button>
                </div>
            </div>
        </div>

        <!-- Google Tag Manager & Analytics -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center">
                <i data-lucide="bar-chart-2" class="w-5 h-5 mr-3 text-yellow-500"></i>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Google Tracking</h3>
            </div>
            
            <div class="p-6 space-y-6">
                <div>
                    <label for="gtm_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Google Tag Manager ID</label>
                    <input type="text" id="gtm_id" name="gtm_id" value="{{ old('gtm_id', $tracking['gtm_id']) }}" placeholder="GTM-XXXXXXX" class="w-full h-11 px-4 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                </div>
                
                <div>
                    <label for="ga4_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Google Analytics 4 Measurement ID</label>
                    <input type="text" id="ga4_id" name="ga4_id" value="{{ old('ga4_id', $tracking['ga4_id']) }}" placeholder="G-XXXXXXXXXX" class="w-full h-11 px-4 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                </div>

                <div>
                    <label for="google_ads_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Google Ads Conversion ID</label>
                    <input type="text" id="google_ads_id" name="google_ads_id" value="{{ old('google_ads_id', $tracking['google_ads_id']) }}" placeholder="AW-XXXXXXXXX" class="w-full h-11 px-4 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                </div>
            </div>
        </div>

        <!-- TikTok Pixel -->
        <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 flex items-center">
                <i data-lucide="video" class="w-5 h-5 mr-3 text-black dark:text-white"></i>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">TikTok Pixel</h3>
            </div>
            
            <div class="p-6">
                <div>
                    <label for="tiktok_pixel_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">TikTok Pixel ID</label>
                    <input type="text" id="tiktok_pixel_id" name="tiktok_pixel_id" value="{{ old('tiktok_pixel_id', $tracking['tiktok_pixel_id']) }}" placeholder="XXXXXXXXXXXXXXXXXXX" class="w-full h-11 px-4 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary focus:border-transparent transition-all">
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4">
            <button type="submit" class="px-6 py-3 bg-primary text-white font-medium rounded-xl hover:bg-secondary transition-colors flex items-center shadow-lg shadow-primary/30">
                <i data-lucide="save" class="w-5 h-5 mr-2"></i>
                Save Tracking Configuration
            </button>
        </div>
    </form>
</div>
@endsection
