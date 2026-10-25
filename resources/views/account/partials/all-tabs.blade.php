{{-- ══════════════════════════════════════════════════════════════
     ALL TAB CONTENT PANELS  (shared between desktop & mobile)
     ══════════════════════════════════════════════════════════════ --}}

{{-- TAB 1: Active / Current Orders --}}
<div x-show="activeTab === 'current'"
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0 translate-y-1"
     x-transition:enter-end="opacity-100 translate-y-0">
    @forelse($currentOrders as $order)
        @include('account.partials.order-card', ['order' => $order, 'allowCancel' => true])
    @empty
    <div class="text-center py-8 sm:py-10 px-4">
        <div class="w-14 h-14 bg-primary/10 dark:bg-primary/20 rounded-2xl flex items-center justify-center mx-auto mb-3 text-primary dark:text-secondary">
            <i data-lucide="package-check" class="w-7 h-7"></i>
        </div>
        <h3 class="font-bold text-gray-900 dark:text-white mb-1 text-base">No Active Orders</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4 max-w-xs mx-auto">When you place an order you can track it live right here.</p>
        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 bg-primary text-white px-5 py-2.5 rounded-xl text-xs font-bold hover:bg-primary/90 transition shadow-sm">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i> Start Shopping
        </a>
    </div>
    @endforelse
</div>

{{-- TAB 2: Completed Orders --}}
<div x-show="activeTab === 'completed'"
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0 translate-y-1"
     x-transition:enter-end="opacity-100 translate-y-0">
    @forelse($completedOrders as $order)
        @include('account.partials.order-card', ['order' => $order, 'allowCancel' => false])
    @empty
    <div class="text-center py-8 sm:py-10 px-4">
        <div class="w-14 h-14 bg-emerald-50 dark:bg-emerald-950/40 rounded-2xl flex items-center justify-center mx-auto mb-3 text-emerald-500">
            <i data-lucide="check-circle-2" class="w-7 h-7"></i>
        </div>
        <h3 class="font-bold text-gray-900 dark:text-white mb-1 text-base">No Completed Orders Yet</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4 max-w-xs mx-auto">Delivered orders appear here with review options.</p>
        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 bg-primary text-white px-5 py-2.5 rounded-xl text-xs font-bold hover:bg-primary/90 transition shadow-sm">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i> Browse Products
        </a>
    </div>
    @endforelse
</div>

{{-- TAB 3: Wishlist --}}
<div x-show="activeTab === 'wishlist'"
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0 translate-y-1"
     x-transition:enter-end="opacity-100 translate-y-0">
    @if($wishlists->count() > 0)
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3">
        @foreach($wishlists as $wishlist)
        @if($wishlist->product)
        <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden border border-gray-100 dark:border-gray-700 shadow-sm hover:shadow-md transition-all flex flex-col group"
             id="wishlist-item-{{ $wishlist->id }}">
            <div class="relative pt-[100%] bg-gray-50 dark:bg-gray-700 overflow-hidden flex-shrink-0">
                <a href="{{ route('product.show', $wishlist->product->slug) }}" class="block">
                    @if($wishlist->product->thumbnail)
                    <img src="{{ $wishlist->product->thumbnail->url }}" alt="{{ $wishlist->product->name }}"
                         class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                    <div class="absolute inset-0 flex items-center justify-center text-gray-300 dark:text-gray-600">
                        <i data-lucide="image" class="w-8 h-8"></i>
                    </div>
                    @endif
                </a>
                <button type="button"
                        @click="removeFromWishlist({{ $wishlist->product->id }}, 'wishlist-item-{{ $wishlist->id }}')"
                        class="absolute top-2 right-2 w-7 h-7 rounded-full bg-white/90 dark:bg-gray-800/90 text-red-500 hover:bg-red-500 hover:text-white shadow flex items-center justify-center transition-all hover:scale-110">
                    <i data-lucide="x" class="w-3.5 h-3.5"></i>
                </button>
            </div>
            <div class="p-3 flex flex-col flex-1 gap-2">
                <div class="flex-1">
                    <h4 class="font-semibold text-gray-900 dark:text-white text-xs line-clamp-2 leading-snug">
                        <a href="{{ route('product.show', $wishlist->product->slug) }}" class="hover:text-primary transition-colors">
                            {{ $wishlist->product->name }}
                        </a>
                    </h4>
                    <div class="flex items-baseline gap-1.5 mt-1.5">
                        <span class="text-sm font-extrabold text-primary dark:text-secondary">৳{{ number_format($wishlist->product->price, 0) }}</span>
                        @if($wishlist->product->compare_at_price > $wishlist->product->price)
                        <span class="text-[10px] text-gray-400 line-through">৳{{ number_format($wishlist->product->compare_at_price, 0) }}</span>
                        @endif
                    </div>
                </div>
                <button type="button"
                        @click="quickAddToCart({{ $wishlist->product->id }}, $el)"
                        class="w-full inline-flex items-center justify-center gap-1.5 py-2 rounded-xl bg-primary text-white text-[11px] font-bold hover:bg-primary/90 transition shadow-sm">
                    <i data-lucide="shopping-cart" class="w-3 h-3"></i> Add to Cart
                </button>
            </div>
        </div>
        @endif
        @endforeach
    </div>
    @else
    <div class="text-center py-8 sm:py-10 px-4">
        <div class="w-14 h-14 bg-rose-50 dark:bg-rose-950/40 rounded-2xl flex items-center justify-center mx-auto mb-3 text-rose-400">
            <i data-lucide="heart" class="w-7 h-7"></i>
        </div>
        <h3 class="font-bold text-gray-900 dark:text-white mb-1 text-base">Wishlist is Empty</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4 max-w-xs mx-auto">Save items you love while browsing and they will wait here!</p>
        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 bg-primary text-white px-5 py-2.5 rounded-xl text-xs font-bold hover:bg-primary/90 transition shadow-sm">
            <i data-lucide="compass" class="w-4 h-4"></i> Explore Collection
        </a>
    </div>
    @endif
</div>

{{-- TAB 4: Cart Details --}}
<div x-show="activeTab === 'cart'"
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0 translate-y-1"
     x-transition:enter-end="opacity-100 translate-y-0">
    @if(count($cart) > 0)
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 space-y-2.5">
            @foreach($cart as $itemKey => $item)
            <div class="flex items-center gap-3.5 p-3.5 rounded-2xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-700">
                <div class="w-14 h-14 rounded-xl overflow-hidden bg-white dark:bg-gray-700 flex-shrink-0 border border-gray-100 dark:border-gray-600">
                    @if(!empty($item['thumbnail']))
                    <img src="{{ $item['thumbnail'] }}" alt="{{ $item['name'] }}" class="w-full h-full object-cover">
                    @else
                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                        <i data-lucide="package" class="w-5 h-5"></i>
                    </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="font-semibold text-gray-900 dark:text-white text-xs truncate">{{ $item['name'] }}</h4>
                    @if(!empty($item['variation_name']))
                    <span class="text-[10px] text-gray-500 dark:text-gray-400">{{ $item['variation_name'] }}</span>
                    @endif
                    <div class="flex items-center gap-2 mt-1">
                        <span class="text-sm font-extrabold text-primary dark:text-secondary">৳{{ number_format($item['price'], 0) }}</span>
                        <span class="text-[10px] text-gray-400">× {{ $item['quantity'] }}</span>
                    </div>
                </div>
                <div class="text-right flex-shrink-0">
                    <span class="font-bold text-sm text-gray-900 dark:text-white">৳{{ number_format($item['price'] * $item['quantity'], 0) }}</span>
                </div>
            </div>
            @endforeach
        </div>
        <div class="lg:col-span-1">
            <div class="bg-gray-50 dark:bg-gray-800/60 rounded-2xl border border-gray-100 dark:border-gray-700 p-4 lg:sticky lg:top-36">
                <h3 class="font-bold text-gray-900 dark:text-white text-sm mb-4 pb-3 border-b border-gray-200 dark:border-gray-700">Order Summary</h3>
                <div class="space-y-2.5 text-xs text-gray-600 dark:text-gray-400">
                    <div class="flex justify-between">
                        <span>Items ({{ $cartCount }})</span>
                        <span class="font-bold text-gray-900 dark:text-white">৳{{ number_format($cartTotal, 0) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-400">
                        <span>Delivery</span>
                        <span>At checkout</span>
                    </div>
                    <div class="pt-3 border-t border-gray-200 dark:border-gray-700 flex justify-between">
                        <span class="font-bold text-sm text-gray-900 dark:text-white">Estimated Total</span>
                        <span class="font-black text-sm text-primary dark:text-secondary">৳{{ number_format($cartTotal, 0) }}</span>
                    </div>
                </div>
                <div class="mt-4 space-y-2">
                    <button type="button"
                            @click="window.dispatchEvent(new CustomEvent('open-checkout'))"
                            class="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-primary text-white text-xs font-bold hover:bg-primary/90 transition shadow-md shadow-primary/20">
                        <i data-lucide="credit-card" class="w-3.5 h-3.5"></i> Proceed to Checkout
                    </button>
                    <button type="button"
                            @click="window.dispatchEvent(new CustomEvent('toggle-cart'))"
                            class="w-full flex items-center justify-center gap-2 py-2.5 rounded-xl border border-gray-200 dark:border-gray-700 text-xs font-semibold text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        <i data-lucide="edit-3" class="w-3.5 h-3.5"></i> Edit Cart
                    </button>
                </div>
            </div>
        </div>
    </div>
    @else
    <div class="text-center py-8 sm:py-10 px-4">
        <div class="w-14 h-14 bg-amber-50 dark:bg-amber-950/40 rounded-2xl flex items-center justify-center mx-auto mb-3 text-amber-500">
            <i data-lucide="shopping-cart" class="w-7 h-7"></i>
        </div>
        <h3 class="font-bold text-gray-900 dark:text-white mb-1 text-base">Your Cart is Empty</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4 max-w-xs mx-auto">Browse our products and add items to your cart.</p>
        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 bg-primary text-white px-5 py-2.5 rounded-xl text-xs font-bold hover:bg-primary/90 transition shadow-sm">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i> Shop Now
        </a>
    </div>
    @endif
</div>

{{-- TAB 5: All Orders --}}
<div x-show="activeTab === 'all'"
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0 translate-y-1"
     x-transition:enter-end="opacity-100 translate-y-0">
    @forelse($allOrders as $order)
        @include('account.partials.order-card', ['order' => $order, 'allowCancel' => true])
    @empty
    <div class="text-center py-8 sm:py-10 px-4">
        <div class="w-14 h-14 bg-gray-100 dark:bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-3 text-gray-400">
            <i data-lucide="history" class="w-7 h-7"></i>
        </div>
        <h3 class="font-bold text-gray-900 dark:text-white mb-1 text-base">No Orders Found</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4 max-w-xs mx-auto">Your complete purchase history will appear here.</p>
        <a href="{{ route('shop.index') }}" class="inline-flex items-center gap-2 bg-primary text-white px-5 py-2.5 rounded-xl text-xs font-bold hover:bg-primary/90 transition shadow-sm">
            <i data-lucide="shopping-bag" class="w-4 h-4"></i> Start Shopping
        </a>
    </div>
    @endforelse
    @if($allOrders->hasPages())
    <div class="mt-5">{{ $allOrders->appends(['tab' => 'all'])->links() }}</div>
    @endif
</div>

{{-- TAB 6: Addresses --}}
<div x-show="activeTab === 'addresses'"
     x-transition:enter="transition ease-out duration-150"
     x-transition:enter-start="opacity-0 translate-y-1"
     x-transition:enter-end="opacity-100 translate-y-0">
    @if($addresses->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach($addresses as $addr)
        <div class="bg-white dark:bg-gray-800 rounded-2xl border {{ $addr->is_default ? 'border-primary/40 dark:border-primary/30' : 'border-gray-100 dark:border-gray-700' }} p-4 shadow-sm relative">
            <div class="flex items-center justify-between mb-3">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold {{ $addr->is_default ? 'bg-primary/10 text-primary dark:text-secondary' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">
                    <i data-lucide="map-pin" class="w-3 h-3"></i>
                    {{ $addr->label ?: 'Address' }}
                </span>
                @if($addr->is_default)
                <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                    <i data-lucide="check" class="w-3 h-3"></i> Default
                </span>
                @else
                <button type="button" @click="setDefaultAddress({{ $addr->id }})"
                        class="text-[10px] text-gray-400 hover:text-primary dark:hover:text-secondary transition underline font-medium">
                    Set default
                </button>
                @endif
            </div>
            <h4 class="font-bold text-gray-900 dark:text-white text-sm">{{ $addr->name }}</h4>
            <p class="text-[11px] text-gray-400 font-mono mt-0.5">{{ $addr->phone }}</p>
            <p class="text-xs text-gray-600 dark:text-gray-300 mt-2 leading-relaxed">{{ $addr->address }}</p>
            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-0.5">{{ $addr->district }}</p>
            <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700 flex justify-end">
                <form action="{{ route('account.addresses.destroy', $addr) }}" method="POST"
                      onsubmit="return confirm('Delete this address?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="text-[11px] text-red-400 hover:text-red-600 transition flex items-center gap-1 font-medium">
                        <i data-lucide="trash-2" class="w-3 h-3"></i> Delete
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <div class="text-center py-14 px-4">
        <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-2xl flex items-center justify-center mx-auto mb-4 text-gray-400">
            <i data-lucide="map-pin" class="w-8 h-8"></i>
        </div>
        <h3 class="font-bold text-gray-900 dark:text-white mb-1 text-base">No Addresses Saved</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-5 max-w-xs mx-auto">Save your delivery address for faster checkout.</p>
        <button type="button" @click="addressModalOpen = true"
                class="inline-flex items-center gap-2 bg-primary text-white px-5 py-2.5 rounded-xl text-xs font-bold hover:bg-primary/90 transition shadow-sm">
            <i data-lucide="plus" class="w-4 h-4"></i> Add First Address
        </button>
    </div>
    @endif
</div>
