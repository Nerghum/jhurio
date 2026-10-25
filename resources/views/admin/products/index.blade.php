@extends('layouts.admin')

@section('header', 'Products')

@section('content')
<div x-data="{
    selectedIds: [],
    allIds: {{ json_encode($products->pluck('id')->values()->all()) }},
    get allSelected() {
        return this.allIds.length > 0 && this.allIds.every(id => this.selectedIds.includes(id));
    },
    toggleAll() {
        if (this.allSelected) {
            this.selectedIds = [];
        } else {
            this.selectedIds = [...this.allIds];
        }
    },
    submitBulk(action) {
        if (this.selectedIds.length === 0) return;
        if (action === 'delete' && !confirm('Are you sure you want to delete ' + this.selectedIds.length + ' selected product(s)?')) {
            return;
        }
        $refs.bulkActionInput.value = action;
        $refs.bulkForm.submit();
    }
}" class="space-y-6">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-sm font-semibold flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2.5">
                <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600"></i>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <i data-lucide="x" class="w-4 h-4"></i>
            </button>
        </div>
    @endif

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
        <div class="flex-1 w-full max-w-md">
            <form method="GET" action="{{ route('admin.products.index') }}" class="relative w-full">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Search products by title or slug..." 
                       class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-white focus:ring-primary focus:border-primary text-sm shadow-2xs">
                <i data-lucide="search" class="w-4 h-4 absolute left-3.5 top-3.5 text-gray-400"></i>
            </form>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.products.create') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary hover:bg-secondary text-white rounded-xl transition-all shadow-xs font-bold text-sm">
                <i data-lucide="plus" class="w-4 h-4"></i>
                <span>Add Product</span>
            </a>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════════════
         STICKY BULK ACTIONS BAR (Visible when items selected)
         ══════════════════════════════════════════════════════════════ -->
    <div x-show="selectedIds.length > 0" 
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="p-4 rounded-2xl bg-white dark:bg-gray-800 border-2 border-primary/30 dark:border-primary/50 shadow-lg flex flex-wrap items-center justify-between gap-4"
         style="display: none;">
        
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-primary text-white font-black text-xs">
                <span x-text="selectedIds.length"></span>
            </span>
            <div>
                <p class="text-sm font-bold text-gray-900 dark:text-white">
                    <span x-text="selectedIds.length"></span> product(s) selected
                </p>
                <p class="text-xs text-gray-500">Choose a bulk action to apply to all selected items</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <!-- Make Live / Published -->
            <button type="button" 
                    @click="submitBulk('published')" 
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold transition shadow-xs hover:scale-[1.02] active:scale-[0.98]">
                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                <span>Make Live (Publish)</span>
            </button>

            <!-- Make Draft -->
            <button type="button" 
                    @click="submitBulk('draft')" 
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold transition shadow-xs hover:scale-[1.02] active:scale-[0.98]">
                <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                <span>Make Draft</span>
            </button>

            <!-- Bulk Delete -->
            <button type="button" 
                    @click="submitBulk('delete')" 
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold transition shadow-xs">
                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                <span>Delete</span>
            </button>

            <!-- Deselect All -->
            <button type="button" 
                    @click="selectedIds = []" 
                    class="px-3 py-2 rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 text-xs font-semibold transition">
                Deselect
            </button>
        </div>
    </div>

    <!-- Hidden Bulk Form -->
    <form x-ref="bulkForm" action="{{ route('admin.products.bulkAction') }}" method="POST" style="display: none;">
        @csrf
        <input type="hidden" name="action" x-ref="bulkActionInput" value="">
        <template x-for="id in selectedIds" :key="id">
            <input type="hidden" name="product_ids[]" :value="id">
        </template>
    </form>

    <!-- Products Table Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700/80 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[850px]">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-800/60 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider border-b border-gray-200 dark:border-gray-700">
                        <th class="w-12 px-4 py-4 text-center">
                            <input type="checkbox" 
                                   @change="toggleAll()" 
                                   :checked="allSelected"
                                   class="w-4 h-4 rounded text-primary focus:ring-primary border-gray-300 dark:border-gray-600 dark:bg-gray-700 cursor-pointer">
                        </th>
                        <th class="px-6 py-4 font-semibold">Product</th>
                        <th class="px-6 py-4 font-semibold">Category</th>
                        <th class="px-6 py-4 font-semibold">Price</th>
                        <th class="px-6 py-4 font-semibold">Stock</th>
                        <th class="px-6 py-4 font-semibold">Status</th>
                        <th class="px-6 py-4 font-semibold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700/60 text-sm">
                    @forelse($products as $product)
                    <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-700/40 transition-colors"
                        :class="selectedIds.includes({{ $product->id }}) ? 'bg-primary/5 dark:bg-primary/10' : ''">
                        <td class="w-12 px-4 py-4 text-center">
                            <input type="checkbox" 
                                   value="{{ $product->id }}" 
                                   x-model="selectedIds"
                                   class="w-4 h-4 rounded text-primary focus:ring-primary border-gray-300 dark:border-gray-600 dark:bg-gray-700 cursor-pointer">
                        </td>
                        <td class="px-6 py-4 flex items-center">
                            <div class="h-11 w-11 flex-shrink-0 rounded-xl bg-gray-100 dark:bg-gray-700 overflow-hidden flex items-center justify-center border border-gray-200 dark:border-gray-600">
                                @if($product->thumbnail)
                                    <img src="{{ $product->thumbnail->url }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                                @else
                                    <img src="https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80" alt="Placeholder" class="h-full w-full object-cover opacity-50">
                                @endif
                            </div>
                            <div class="ml-4">
                                <a href="{{ route('admin.products.edit', $product) }}" class="text-sm font-bold text-gray-900 dark:text-white hover:text-primary transition line-clamp-1">
                                    {{ $product->name }}
                                </a>
                                <div class="text-xs text-gray-400 font-mono mt-0.5">/{{ $product->slug }}</div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold text-gray-600 dark:text-gray-300">
                            {{ $product->category->name ?? ($product->categories->first()->name ?? 'Uncategorized') }}
                        </td>
                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white font-mono">
                            ৳{{ number_format($product->price, 2) }}
                        </td>
                        <td class="px-6 py-4 text-xs font-semibold">
                            @if($product->stock_quantity > 5)
                                <span class="text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    {{ $product->stock_quantity }} in stock
                                </span>
                            @elseif($product->stock_quantity > 0)
                                <span class="text-amber-600 dark:text-amber-400 flex items-center gap-1.5 font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                    Low: {{ $product->stock_quantity }} left
                                </span>
                            @else
                                <span class="text-rose-600 dark:text-rose-400 flex items-center gap-1.5 font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                    Out of stock
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($product->status == 'published')
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800 whitespace-nowrap">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                Live / Published
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-600 whitespace-nowrap">
                                <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                Draft
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                            <a href="{{ route('product.show', $product->slug) }}" target="_blank" class="p-2 text-gray-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 rounded-lg transition inline-block mr-1" title="View in Store">
                                <i data-lucide="external-link" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('admin.products.edit', $product) }}" class="p-2 text-gray-400 hover:text-primary hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition inline-block mr-1" title="Edit Product">
                                <i data-lucide="edit-2" class="w-4 h-4"></i>
                            </a>
                            <form action="{{ route('admin.products.destroy', $product) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 text-gray-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 rounded-lg transition" title="Delete Product">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 dark:bg-gray-700 text-gray-400">
                                <i data-lucide="package" class="h-6 w-6"></i>
                            </div>
                            <h3 class="mt-3 text-sm font-bold text-gray-900 dark:text-white">No products found</h3>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Get started by creating your first product or adjusting your search.</p>
                            <div class="mt-6">
                                <a href="{{ route('admin.products.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl bg-primary text-white text-xs font-bold hover:bg-secondary transition shadow-sm">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                    <span>Add Product</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
        <div class="px-6 py-4 border-t border-gray-100 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/50">
            {{ $products->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
