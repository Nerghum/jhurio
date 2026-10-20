@extends('layouts.app')

@section('title', 'My Wishlist')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col md:flex-row gap-8">
    
    <!-- Sidebar -->
    <div class="w-full md:w-64 flex-shrink-0">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm overflow-hidden sticky top-24">
            <div class="p-5 border-b border-gray-50 dark:border-gray-700 text-center">
                <div class="w-16 h-16 rounded-full bg-primary/10 text-primary flex items-center justify-center text-2xl font-bold mx-auto mb-3">
                    {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->phone, 0, 1)) }}
                </div>
                <h3 class="font-bold text-gray-900 dark:text-white truncate">{{ auth()->user()->name !== 'Customer' ? auth()->user()->name : auth()->user()->phone }}</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ auth()->user()->phone }}</p>
            </div>
            <div class="p-2">
                <a href="{{ route('account.orders') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <i data-lucide="package" class="w-5 h-5"></i> My Orders
                </a>
                <a href="{{ route('account.wishlist') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-colors bg-primary/5 text-primary">
                    <i data-lucide="heart" class="w-5 h-5"></i> My Wishlist
                </a>
                <a href="{{ route('account.addresses') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                    <i data-lucide="map-pin" class="w-5 h-5"></i> Saved Addresses
                </a>
                <form action="{{ route('account.logout') }}" method="POST" class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-700">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                        <i data-lucide="log-out" class="w-5 h-5"></i> Sign Out
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">My Wishlist</h1>

        @if($wishlists->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach($wishlists as $wishlist)
            <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-xl transition-all duration-300 group flex flex-col h-full" id="wishlist-item-{{ $wishlist->id }}">
                <div class="relative pt-[100%] bg-gray-100 dark:bg-gray-700 overflow-hidden">
                    <a href="{{ route('product.show', $wishlist->product->slug) }}">
                        @if($wishlist->product->thumbnail)
                            <img src="{{ $wishlist->product->thumbnail->url }}" alt="{{ $wishlist->product->name }}" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        @else
                            <div class="absolute inset-0 flex items-center justify-center text-gray-400">
                                <i data-lucide="image" class="w-12 h-12"></i>
                            </div>
                        @endif
                    </a>
                    <!-- Remove button -->
                    <button onclick="toggleWishlist({{ $wishlist->product->id }}, this)" class="absolute top-3 right-3 w-8 h-8 bg-white dark:bg-gray-800 rounded-full flex items-center justify-center text-red-500 shadow-sm hover:scale-110 transition-transform">
                        <i data-lucide="trash-2" class="w-4 h-4 text-red-500 fill-red-500"></i>
                    </button>
                </div>
                
                <div class="p-4 flex flex-col flex-1">
                    <a href="{{ route('product.show', $wishlist->product->slug) }}">
                        <h3 class="font-semibold text-gray-900 dark:text-white text-sm line-clamp-2 mb-1 group-hover:text-primary transition-colors">{{ $wishlist->product->name }}</h3>
                    </a>
                    
                    <div class="mt-auto pt-3 flex items-center justify-between">
                        <div>
                            @if($wishlist->product->compare_at_price > $wishlist->product->price)
                                <span class="text-xs text-gray-500 dark:text-gray-400 line-through block">৳{{ number_format($wishlist->product->compare_at_price) }}</span>
                            @endif
                            <span class="font-bold text-gray-900 dark:text-white">৳{{ number_format($wishlist->product->price) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        
        @if($wishlists->hasPages())
        <div class="mt-8">
            {{ $wishlists->links() }}
        </div>
        @endif
        @else
        <div class="text-center py-20 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
            <div class="w-24 h-24 bg-gray-50 dark:bg-gray-900 rounded-full flex items-center justify-center mx-auto mb-5">
                <i data-lucide="heart" class="w-12 h-12 text-gray-400"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Your wishlist is empty</h3>
            <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-sm mx-auto">Save items you love to your wishlist and review them later!</p>
            <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 bg-primary text-white px-8 py-3.5 rounded-xl font-bold hover:bg-primary/90 transition-all hover:shadow-lg hover:shadow-primary/30 hover:-translate-y-0.5">
                <i data-lucide="shopping-bag" class="w-5 h-5"></i> Explore Products
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
