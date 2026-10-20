@extends('layouts.admin')

@section('title', 'Marketing Settings')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Marketing & Popups</h1>
    </div>

    @if(session('success'))
        <div class="bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 p-4 rounded-xl text-sm border border-green-200 dark:border-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700">
        <div class="p-6">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Exit-Intent Popup</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Configure the popup that appears when a user tries to leave the site.</p>
            
            <form action="{{ route('admin.settings.marketing.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="space-y-4">
                    <div class="flex items-center">
                        <input type="checkbox" id="exit_popup_enabled" name="exit_popup_enabled" value="1" {{ $settings['exit_popup_enabled'] == '1' ? 'checked' : '' }} class="w-4 h-4 text-primary bg-gray-100 border-gray-300 rounded focus:ring-primary dark:focus:ring-primary dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <label for="exit_popup_enabled" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Enable Exit-Intent Popup</label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Title</label>
                        <input type="text" name="exit_popup_title" value="{{ $settings['exit_popup_title'] }}" class="w-full border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 focus:ring-primary sm:text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                        <textarea name="exit_popup_description" rows="3" class="w-full border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 focus:ring-primary sm:text-sm">{{ $settings['exit_popup_description'] }}</textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Coupon Code (Optional)</label>
                        <input type="text" name="exit_popup_coupon" value="{{ $settings['exit_popup_coupon'] }}" class="w-full border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 focus:ring-primary sm:text-sm">
                        <p class="text-xs text-gray-500 mt-1">Provide a coupon code to display in the popup.</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Image (Optional)</label>
                        @if($settings['exit_popup_image'])
                            <div class="mb-2">
                                <img src="{{ $settings['exit_popup_image'] }}" class="h-32 object-contain rounded border border-gray-200 dark:border-gray-700">
                            </div>
                        @endif
                        <input type="file" name="exit_popup_image" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-colors">
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-primary hover:bg-secondary text-white font-bold py-2 px-6 rounded-xl transition-colors text-sm shadow-sm">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
