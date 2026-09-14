@extends('layouts.admin')

@section('header', 'Media Library')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Media Library</h2>
    <button @click="window.dispatchEvent(new CustomEvent('open-media-modal', { detail: { callbackEvent: null } }))" class="flex items-center px-4 py-2 bg-primary text-white rounded-xl hover:bg-secondary transition-colors">
        <i data-lucide="upload-cloud" class="w-4 h-4 mr-2"></i>
        Upload New
    </button>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
        @forelse($media as $item)
            <div class="relative group aspect-square rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 bg-gray-100 dark:bg-gray-900">
                @if(str_starts_with($item->file_type, 'image/'))
                    <img src="{{ $item->url }}" alt="{{ $item->filename }}" class="w-full h-full object-cover">
                @else
                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                        <i data-lucide="file" class="w-12 h-12"></i>
                    </div>
                @endif
                <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-40 transition-all flex items-center justify-center opacity-0 group-hover:opacity-100">
                    <span class="text-white text-xs truncate px-2 text-center">{{ $item->filename }}</span>
                </div>
            </div>
        @empty
            <div class="col-span-full py-16 text-center text-gray-500">
                <i data-lucide="image" class="mx-auto h-12 w-12 text-gray-400 mb-3"></i>
                <p>No media files found. Upload some to get started.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
