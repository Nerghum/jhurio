@extends('layouts.admin')

@section('header', 'Media Library')

@section('content')
<div x-data="mediaLibrary()" class="space-y-6">
    <div class="flex justify-between items-center">
        <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Media Library</h2>
        <div class="flex gap-3">
            <button x-show="selected.length > 0" x-cloak @click="bulkDelete" class="flex items-center px-4 py-2 bg-red-500 text-white rounded-xl hover:bg-red-600 transition-colors">
                <i data-lucide="trash-2" class="w-4 h-4 mr-2"></i>
                Delete Selected (<span x-text="selected.length"></span>)
            </button>
            <button @click="window.dispatchEvent(new CustomEvent('open-media-modal', { detail: { callbackEvent: null } }))" class="flex items-center px-4 py-2 bg-primary text-white rounded-xl hover:bg-secondary transition-colors">
                <i data-lucide="upload-cloud" class="w-4 h-4 mr-2"></i>
                Upload New
            </button>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
            @forelse($media as $item)
                <div class="relative group aspect-square rounded-xl overflow-hidden border bg-gray-100 dark:bg-gray-900 transition-colors" :class="selected.includes({{ $item->id }}) ? 'border-primary ring-2 ring-primary/50' : 'border-gray-200 dark:border-gray-700'">
                    @if(str_starts_with($item->file_type, 'image/'))
                        <img src="{{ $item->url }}" alt="{{ $item->filename }}" class="w-full h-full object-cover cursor-pointer" @click="toggleSelect({{ $item->id }})">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-400 cursor-pointer" @click="toggleSelect({{ $item->id }})">
                            <i data-lucide="file" class="w-12 h-12"></i>
                        </div>
                    @endif
                    
                    <!-- Checkbox -->
                    <div class="absolute top-2 left-2 z-10" x-show="selected.includes({{ $item->id }}) || $el.closest('.group').matches(':hover')">
                        <input type="checkbox" :value="{{ $item->id }}" x-model="selected" class="w-5 h-5 rounded border-gray-300 text-primary focus:ring-primary cursor-pointer shadow-sm">
                    </div>

                    <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-60 transition-all flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 p-2 pointer-events-none">
                        <span class="text-white text-xs truncate w-full text-center mb-2">{{ $item->filename }}</span>
                        
                        <div class="flex space-x-2 pointer-events-auto">
                            <!-- Replace Form -->
                            <form action="{{ route('admin.media.replace', $item->id) }}" method="POST" enctype="multipart/form-data" class="inline">
                                @csrf
                                <label class="cursor-pointer bg-white text-gray-800 p-1.5 rounded-lg hover:bg-gray-200 transition-colors inline-block" title="Replace Image">
                                    <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                                    <input type="file" name="file" class="hidden" onchange="this.form.submit()">
                                </label>
                            </form>

                            <!-- Delete Form -->
                            <form action="{{ route('admin.media.destroy', $item->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this image? It will be removed from all products and banners.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="bg-red-500 text-white p-1.5 rounded-lg hover:bg-red-600 transition-colors" title="Delete Image">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
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
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('mediaLibrary', () => ({
            selected: [],
            
            toggleSelect(id) {
                if (this.selected.includes(id)) {
                    this.selected = this.selected.filter(i => i !== id);
                } else {
                    this.selected.push(id);
                }
            },
            
            async bulkDelete() {
                if (!confirm('Are you sure you want to delete ' + this.selected.length + ' selected items? They will be removed from all products and banners.')) {
                    return;
                }
                
                try {
                    const response = await fetch('{{ route('admin.media.bulk_destroy') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ ids: this.selected })
                    });
                    
                    const data = await response.json();
                    if (data.success) {
                        window.location.reload();
                    }
                } catch (error) {
                    console.error('Error during bulk delete:', error);
                    alert('An error occurred during bulk deletion.');
                }
            }
        }));
    });
</script>
@endsection
