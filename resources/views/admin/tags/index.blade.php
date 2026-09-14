@extends('layouts.admin')

@section('header', 'Tags')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div class="flex-1">
        <div class="relative max-w-md">
            <input type="text" placeholder="Search tags..." class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-primary focus:border-primary">
            <i data-lucide="search" class="w-5 h-5 absolute left-3 top-2.5 text-gray-400"></i>
        </div>
    </div>
    <div>
        <a href="{{ route('admin.tags.create') }}" class="flex items-center px-4 py-2 bg-primary text-white rounded-xl hover:bg-secondary transition-colors">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            Add Tag
        </a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 text-sm border-b border-gray-200 dark:border-gray-700">
                    <th class="px-6 py-4 font-medium">Tag Name</th>
                    <th class="px-6 py-4 font-medium">Products</th>
                    <th class="px-6 py-4 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($tags as $tag)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-6 py-4">
                        <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $tag->name }}</div>
                        <div class="text-xs text-gray-500">{{ $tag->slug }}</div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white">
                        {{ $tag->products_count }}
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        <a href="{{ route('admin.tags.edit', $tag) }}" class="text-gray-400 hover:text-primary transition-colors mr-3 inline-block">
                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                        </a>
                        <form action="{{ route('admin.tags.destroy', $tag) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this tag?');">
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
                    <td colspan="3" class="px-6 py-12 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
                            <i data-lucide="tag" class="h-6 w-6 text-gray-600 dark:text-gray-400"></i>
                        </div>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No tags</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new tag.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($tags->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        {{ $tags->links() }}
    </div>
    @endif
</div>
@endsection
