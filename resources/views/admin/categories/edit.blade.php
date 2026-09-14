@extends('layouts.admin')

@section('header', 'Edit Category')

@section('content')
<div class="max-w-3xl mx-auto" x-data="categoryForm()">
    <form action="{{ route('admin.categories.update', $category) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 space-y-6">
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Category Image</label>
                <div class="w-32 h-32 bg-gray-50 dark:bg-gray-700 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 flex flex-col items-center justify-center cursor-pointer overflow-hidden relative group" @click="openMediaSelector()">
                    <template x-if="image.url">
                        <img :src="image.url" class="w-full h-full object-cover">
                    </template>
                    <template x-if="!image.url">
                        <div class="text-center p-2">
                            <i data-lucide="image" class="w-6 h-6 mx-auto text-gray-400 group-hover:text-primary mb-1"></i>
                            <span class="text-xs text-gray-500 font-medium">Select Image</span>
                        </div>
                    </template>
                    <input type="hidden" name="media_id" :value="image.id">
                </div>
                <button type="button" x-show="image.url" @click.prevent="image = {}" class="text-xs text-red-500 mt-2 hover:underline">Remove image</button>
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category Name</label>
                <input type="text" name="name" id="name" x-model="name" required class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Slug</label>
                <input type="text" name="slug" id="slug" x-model="slug" required class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary bg-gray-50 dark:bg-gray-900">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea name="description" id="description" rows="4" class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">{!! old('description', $category->description) !!}</textarea>
            </div>

            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="px-6 py-2.5 bg-primary text-white rounded-xl font-medium hover:bg-secondary transition-colors">
                    Update Category
                </button>
                <a href="{{ route('admin.categories.index') }}" class="ml-4 px-6 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-colors dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
                    Cancel
                </a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('categoryForm', () => ({
            name: @json($category->name),
            slug: @json($category->slug),
            image: @json($category->media ?? new \stdClass()),

            init() {
                window.addEventListener('media-selected', (e) => {
                    this.image = e.detail;
                });
            },

            openMediaSelector() {
                window.dispatchEvent(new CustomEvent('open-media-modal', { detail: { callbackEvent: 'media-selected' } }));
            }
        }));
    });
</script>
@endpush
