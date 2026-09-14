@extends('layouts.admin')

@section('header', 'Categories')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div class="flex-1">
        <div class="relative max-w-md">
            <input type="text" placeholder="Search categories..." class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-primary focus:border-primary">
            <i data-lucide="search" class="w-5 h-5 absolute left-3 top-2.5 text-gray-400"></i>
        </div>
    </div>
    <div>
        <a href="{{ route('admin.categories.create') }}" class="flex items-center px-4 py-2 bg-primary text-white rounded-xl hover:bg-secondary transition-colors">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            Add Category
        </a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 text-sm border-b border-gray-200 dark:border-gray-700">
                    <th class="px-6 py-4 font-medium">Category</th>
                    <th class="px-6 py-4 font-medium">Description</th>
                    <th class="px-6 py-4 font-medium">Products</th>
                    <th class="px-6 py-4 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($categories as $category)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-6 py-4 flex items-center">
                        <div class="h-10 w-10 flex-shrink-0 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden flex items-center justify-center">
                            @if($category->media)
                                <img src="{{ $category->media->url }}" alt="{{ $category->name }}" class="h-full w-full object-cover">
                            @else
                                <i data-lucide="layers" class="w-5 h-5 text-gray-400"></i>
                            @endif
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $category->name }}</div>
                            <div class="text-xs text-gray-500">{{ $category->slug }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                        {{ $category->description ?? '-' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                        {{ $category->products_count }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        <a href="{{ route('admin.categories.edit', $category) }}" class="text-gray-400 hover:text-primary transition-colors mr-3 inline-block">
                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                        </a>
                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this category?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
                            <i data-lucide="layers" class="h-6 w-6 text-gray-600 dark:text-gray-400"></i>
                        </div>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No categories</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new category.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($categories->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        {{ $categories->links() }}
    </div>
    @endif
</div>
@endsection
