@extends('layouts.admin')

@section('title', 'Product Bundles')

@section('header')
<div class="flex justify-between items-center">
    <div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            Product Bundles
        </h2>
        <p class="text-sm text-gray-500 mt-1">Manage frequently bought together items</p>
    </div>
    <a href="{{ route('admin.bundles.create') }}" class="bg-primary hover:bg-secondary text-white font-bold py-2 px-6 rounded-xl transition-colors shadow-sm flex items-center">
        <i data-lucide="plus" class="w-4 h-4 mr-2"></i> Add Bundle
    </a>
</div>
@endsection

@section('content')
<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 dark:bg-gray-700/50 text-gray-500 dark:text-gray-400 font-medium border-b border-gray-100 dark:border-gray-700">
                <tr>
                    <th class="px-6 py-4">Title</th>
                    <th class="px-6 py-4">Products</th>
                    <th class="px-6 py-4">Discount</th>
                    <th class="px-6 py-4">Status</th>
                    <th class="px-6 py-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                @forelse($bundles as $bundle)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                        {{ $bundle->title }}
                    </td>
                    <td class="px-6 py-4 text-gray-500">
                        {{ $bundle->products->count() }} items
                    </td>
                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                        {{ $bundle->discount_value }} {{ $bundle->discount_type === 'percentage' ? '%' : 'Tk' }}
                    </td>
                    <td class="px-6 py-4">
                        @if($bundle->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                Inactive
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.bundles.edit', $bundle) }}" class="p-2 text-gray-400 hover:text-primary transition-colors bg-gray-50 hover:bg-primary/10 dark:bg-gray-700 dark:hover:bg-primary/20 rounded-lg">
                                <i data-lucide="edit" class="w-4 h-4"></i>
                            </a>
                            <form action="{{ route('admin.bundles.destroy', $bundle) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this bundle?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-gray-400 hover:text-red-500 transition-colors bg-gray-50 hover:bg-red-50 dark:bg-gray-700 dark:hover:bg-red-900/20 rounded-lg">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                        <i data-lucide="package-x" class="w-12 h-12 mx-auto text-gray-300 mb-3"></i>
                        <p>No bundles found.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($bundles->hasPages())
    <div class="p-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-900/50">
        {{ $bundles->links() }}
    </div>
    @endif
</div>
@endsection
