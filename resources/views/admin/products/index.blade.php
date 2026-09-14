@extends('layouts.admin')

@section('header', 'Products')

@section('content')
<div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
    <div class="flex-1 w-full">
        <div class="relative max-w-md w-full">
            <input type="text" placeholder="Search products..." class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-primary focus:border-primary">
            <i data-lucide="search" class="w-5 h-5 absolute left-3 top-2.5 text-gray-400"></i>
        </div>
    </div>
    <div class="flex-shrink-0">
        <a href="{{ route('admin.products.create') }}" class="flex items-center justify-center px-4 py-2 bg-primary text-white rounded-xl hover:bg-secondary transition-colors w-full sm:w-auto font-medium">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            Add Product
        </a>
    </div>
</div>

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
    <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
        <table class="w-full text-left border-collapse min-w-[800px]">
            <thead>
                <tr class="bg-gray-50 dark:bg-gray-800/50 text-gray-500 dark:text-gray-400 text-sm border-b border-gray-200 dark:border-gray-700">
                    <th class="px-6 py-4 font-medium">Product</th>
                    <th class="px-6 py-4 font-medium">Category</th>
                    <th class="px-6 py-4 font-medium">Price</th>
                    <th class="px-6 py-4 font-medium">Stock</th>
                    <th class="px-6 py-4 font-medium">Status</th>
                    <th class="px-6 py-4 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors">
                    <td class="px-6 py-4 flex items-center">
                        <div class="h-10 w-10 flex-shrink-0 rounded-lg bg-gray-100 dark:bg-gray-700 overflow-hidden flex items-center justify-center">
                            @if($product->thumbnail)
                                <img src="{{ $product->thumbnail->url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                            @else
                                <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Placeholder" class="h-full w-full object-cover opacity-50">
                            @endif
                        </div>
                        <div class="ml-4">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $product->name }}</div>
                            <div class="text-xs text-gray-500">{{ $product->slug }}</div>
                        </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        {{ $product->category->name ?? 'None' }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900 dark:text-white font-medium">
                        ${{ number_format($product->price, 0) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        @if($product->stock_quantity > 0)
                            <span class="text-green-600 dark:text-green-400">{{ $product->stock_quantity }} in stock</span>
                        @else
                            <span class="text-red-600 dark:text-red-400">Out of stock</span>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($product->status == 'published')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-xl text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400 whitespace-nowrap">
                            Published
                        </span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-xl text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300 whitespace-nowrap">
                            Draft
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                        <a href="{{ route('product.show', $product->slug) }}" target="_blank" class="text-gray-400 hover:text-green-500 transition-colors mr-3 inline-block" title="View in Frontend">
                            <i data-lucide="external-link" class="w-4 h-4"></i>
                        </a>
                        <a href="{{ route('admin.products.edit', $product) }}" class="text-gray-400 hover:text-primary transition-colors mr-3 inline-block" title="Edit Product">
                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                        </a>
                        <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this product?');">
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
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-gray-100 dark:bg-gray-800">
                            <i data-lucide="package" class="h-6 w-6 text-gray-600 dark:text-gray-400"></i>
                        </div>
                        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">No products</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating a new product.</p>
                        <div class="mt-6">
                            <a href="{{ route('admin.products.create') }}" class="inline-flex items-center rounded-xl bg-primary px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-secondary/80 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-secondary">
                                <i data-lucide="plus" class="w-4 h-4 mr-1"></i> Add Product
                            </a>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
    <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
        {{ $products->links() }}
    </div>
    @endif
</div>
@endsection
