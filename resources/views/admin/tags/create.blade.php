@extends('layouts.admin')

@section('header', 'Add Tag')

@section('content')
<div class="max-w-2xl mx-auto" x-data="tagForm()">
    <form action="{{ route('admin.tags.store') }}" method="POST">
        @csrf
        <div class="bg-white dark:bg-gray-800 rounded-xl p-6 shadow-sm border border-gray-100 dark:border-gray-700 space-y-6">
            
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Tag Name</label>
                <input type="text" name="name" id="name" x-model="name" @input="generateSlug()" required class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
            </div>
            
            <div>
                <label for="slug" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Slug</label>
                <input type="text" name="slug" id="slug" x-model="slug" required class="w-full px-4 py-2 rounded-xl border border-gray-200 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary bg-gray-50 dark:bg-gray-900">
            </div>

            <div class="pt-4 border-t border-gray-200 dark:border-gray-700">
                <button type="submit" class="px-6 py-2.5 bg-primary text-white rounded-xl font-medium hover:bg-secondary transition-colors">
                    Save Tag
                </button>
                <a href="{{ route('admin.tags.index') }}" class="ml-4 px-6 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-xl font-medium hover:bg-gray-50 transition-colors dark:bg-gray-700 dark:border-gray-600 dark:text-gray-300 dark:hover:bg-gray-600">
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
        Alpine.data('tagForm', () => ({
            name: '',
            slug: '',

            generateSlug() {
                this.slug = this.name.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
            }
        }));
    });
</script>
@endpush
