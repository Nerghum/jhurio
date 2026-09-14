@extends('layouts.app')

@section('title', $product->name)

@section('content')
<div class="bg-white dark:bg-dark py-8" x-data="productPage()">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumbs -->
        <nav class="flex text-sm text-gray-500 dark:text-gray-400 mb-8" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="hover:text-primary transition-colors">Home</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
                        <a href="{{ route('shop.index') }}" class="hover:text-primary transition-colors">{{ $product->categories->first()->name ?? 'Shop' }}</a>
                    </div>
                </li>
                <li aria-current="page">
                    <div class="flex items-center">
                        <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
                        <span class="text-gray-800 dark:text-gray-200 font-medium truncate max-w-xs">{{ $product->name }}</span>
                    </div>
                </li>
            </ol>
        </nav>

        <!-- Product Main Section -->
        <div class="lg:grid lg:grid-cols-2 lg:gap-x-12 xl:gap-x-16">
            
            <!-- Left: Media Gallery -->
            <div class="flex flex-col-reverse lg:flex-row gap-4 lg:gap-6 mb-10 lg:mb-0">
                <!-- Thumbnails (Vertical on Desktop, Horizontal on Mobile) -->
                <div class="flex lg:flex-col gap-3 overflow-x-auto lg:overflow-y-auto lg:max-h-[600px] scrollbar-hide py-1 lg:py-0 w-full lg:w-24 flex-shrink-0">
                    <template x-for="(img, index) in images" :key="index">
                        <button 
                            @click="activeImage = img"
                            :class="{'border-primary dark:border-primary': activeImage === img, 'border-gray-200 dark:border-gray-700 hover:border-primary/50': activeImage !== img}"
                            class="relative flex-shrink-0 w-20 h-20 lg:w-full lg:h-24 rounded-xl border-2 overflow-hidden focus:outline-none transition-all"
                        >
                            <img :src="img" alt="Thumbnail" class="w-full h-full object-cover">
                        </button>
                    </template>
                </div>
                
                <!-- Main Image -->
                <div class="w-full relative bg-gray-100 dark:bg-gray-800 rounded-xl overflow-hidden aspect-[4/5] lg:aspect-auto lg:h-[600px] group">
                    <img :src="activeImage" alt="{{ $product->name }}" class="w-full h-full object-cover object-center transition-transform duration-700" :class="{'scale-150 cursor-zoom-in': zoom}" @click="zoom = !zoom" @mousemove="zoomImage(event)">
                    
                    @if($product->video_url)
                    <a href="{{ $product->video_url }}" target="_blank" class="absolute bottom-4 right-4 bg-white/90 dark:bg-gray-800/90 backdrop-blur text-gray-900 dark:text-white p-3 rounded-xl shadow-lg hover:scale-110 transition-transform">
                        <i data-lucide="play" class="w-5 h-5 fill-current"></i>
                    </a>
                    @endif
                </div>
            </div>

            <!-- Right: Product Details -->
            <div class="flex flex-col">
                <h1 class="text-3xl sm:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-2">{{ $product->name }}</h1>
                
                <!-- Reviews & Badges -->
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex items-center">
                        <div class="flex text-yellow-400">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= round($product->averageRating()))
                                    <i data-lucide="star" class="w-4 h-4 fill-current"></i>
                                @else
                                    <i data-lucide="star" class="w-4 h-4 text-gray-300 dark:text-gray-600"></i>
                                @endif
                            @endfor
                        </div>
                        <span class="ml-2 text-sm text-gray-500 dark:text-gray-400">
                            <a href="#customer-reviews" class="hover:text-primary transition-colors">({{ $product->reviewsCount() }} reviews)</a>
                        </span>
                    </div>
                    @if($product->tags->count() > 0)
                        <div class="hidden sm:block w-1.5 h-1.5 rounded-xl bg-gray-300 dark:bg-gray-600"></div>
                        <div class="flex gap-2">
                            @foreach($product->tags as $tag)
                                <span class="px-2.5 py-0.5 rounded-xl text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200">
                                    {{ $tag->name }}
                                </span>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Price -->
                <div class="mb-6 flex items-end gap-3">
                    <span class="text-3xl font-bold text-accent dark:text-accent" x-text="currentPrice.toFixed(0) + ' BDT'">{{ number_format($product->price, 0) }} BDT</span>
                    @if($product->compare_at_price > $product->price)
                        <span class="text-lg text-gray-500 dark:text-gray-400 line-through" x-show="currentPrice === basePrice">{{ number_format($product->compare_at_price, 0) }} BDT</span>
                    @endif
                    <span class="text-lg text-gray-500 dark:text-gray-400 line-through" x-show="currentPrice < basePrice" x-text="basePrice.toFixed(0) + ' BDT'"></span>
                </div>

                <!-- Description Snippet -->
                <div class="text-base text-gray-600 dark:text-gray-300 mb-8 prose prose-sm dark:prose-invert">
                    {!! $product->description ?: Str::limit(strip_tags($product->content), 150) !!}
                </div>

                <form class="mt-auto">
                    <!-- Dynamic Variations Selector -->
                    <template x-for="(option, optIndex) in product.options" :key="optIndex">
                        <div class="mb-6">
                            <div class="flex items-center justify-between mb-3">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white">
                                    <span x-text="option.name"></span>: 
                                    <span class="text-gray-500 dark:text-gray-400 font-normal ml-1" x-text="selectedOptions[option.name]"></span>
                                </h3>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <template x-for="(val, valIndex) in option.values" :key="valIndex">
                                    <!-- Color Swatch style -->
                                    <template x-if="option.name.toLowerCase() === 'color'">
                                        <button 
                                            type="button"
                                            @click="selectOption(option.name, val.name)"
                                            :class="{
                                                'ring-2 ring-offset-2 ring-dark dark:ring-offset-dark border-transparent scale-110': selectedOptions[option.name] === val.name, 
                                                'border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-500': selectedOptions[option.name] !== val.name,
                                                'opacity-50': !isOptionAvailable(option.name, val.name)
                                            }"
                                            class="relative flex items-center justify-center p-0.5 rounded-full border-2 focus:outline-none transition-all"
                                            :title="val.name"
                                        >
                                            <span class="w-8 h-8 rounded-full border border-black/10 dark:border-white/10 flex items-center justify-center text-xs font-bold relative overflow-hidden shadow-sm" 
                                                  :style="`background-color: ${val.meta || '#e5e7eb'}; color: ${val.meta ? '#fff' : '#000'}`">
                                                <span x-text="!val.meta ? val.name.charAt(0) : ''"></span>
                                                
                                                <template x-if="!isOptionAvailable(option.name, val.name)">
                                                    <div class="absolute inset-0 flex items-center justify-center rotate-45">
                                                        <div class="w-[150%] h-px bg-red-500 shadow-[0_0_2px_rgba(255,255,255,0.8)]"></div>
                                                    </div>
                                                </template>
                                            </span>
                                        </button>
                                    </template>
                                    
                                    <!-- Pill style -->
                                    <template x-if="option.name.toLowerCase() !== 'color'">
                                        <button 
                                            type="button"
                                            @click="selectOption(option.name, val.name)"
                                            :class="{
                                                'bg-dark text-white dark:bg-white dark:text-dark border-dark dark:border-white shadow-md': selectedOptions[option.name] === val.name, 
                                                'bg-white text-gray-900 border-gray-200 dark:bg-gray-800 dark:text-white dark:border-gray-700 hover:border-gray-400 dark:hover:border-gray-500': selectedOptions[option.name] !== val.name && isOptionAvailable(option.name, val.name),
                                                'bg-gray-50 text-gray-400 border-gray-100 dark:bg-gray-800/40 dark:text-gray-600 dark:border-gray-800': !isOptionAvailable(option.name, val.name)
                                            }"
                                            class="px-5 py-2.5 text-sm font-semibold rounded-full border focus:outline-none transition-all min-w-[3.5rem] relative overflow-hidden flex items-center justify-center"
                                        >
                                            <span x-text="val.name" :class="{'line-through opacity-70': !isOptionAvailable(option.name, val.name)}"></span>
                                        </button>
                                    </template>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- Urgency / Scarcity -->
                    <div class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-100 dark:border-red-900/30 rounded-xl p-3 flex items-center text-red-700 dark:text-red-400">
                        <i data-lucide="clock" class="w-5 h-5 mr-2"></i>
                        <span class="text-sm font-medium">
                            <span x-show="currentStock > 0 && currentStock < 10" x-text="`Only ${currentStock} left in stock - order soon!`"></span>
                            <span x-show="currentStock >= 10">In Stock - Ready to ship!</span>
                            <span x-show="currentStock <= 0">Out of Stock</span>
                        </span>
                    </div>

                    <!-- Quantity & Actions -->
                    <div class="mb-8">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Quantity</label>
                            <div class="inline-flex items-center border border-gray-300 dark:border-gray-600 rounded-xl h-14 bg-white dark:bg-gray-800">
                                <button type="button" @click="if(quantity > 1) quantity--" class="px-4 py-2 text-gray-500 hover:text-primary transition-colors focus:outline-none">
                                    <i data-lucide="minus" class="w-4 h-4"></i>
                                </button>
                                <input type="number" x-model.number="quantity" min="1" :max="currentStock" class="w-16 text-center text-gray-900 dark:text-white font-medium border-0 focus:ring-0 bg-transparent p-0 appearance-none m-0" style="-moz-appearance: textfield;">
                                <button type="button" @click="if(quantity < currentStock) quantity++" class="px-4 py-2 text-gray-500 hover:text-primary transition-colors focus:outline-none">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <button type="button" :disabled="currentStock <= 0 || loading" 
                                    @click="
                                        loading = true;
                                        window.dispatchEvent(new CustomEvent('add-to-cart', {
                                            detail: {
                                                product_id: {{ $product->id }},
                                                variation_id: activeVariation ? activeVariation.id : null,
                                                quantity: quantity
                                            }
                                        }));
                                        setTimeout(() => loading = false, 500);
                                    "
                                    class="w-full bg-dark dark:bg-white text-white dark:text-dark h-14 rounded-xl font-semibold text-base sm:text-lg hover:bg-primary dark:hover:bg-primary dark:hover:text-white transition-colors flex items-center justify-center gap-2 shadow-lg shadow-gray-200 dark:shadow-none disabled:opacity-50 disabled:cursor-not-allowed">
                                <i data-lucide="shopping-bag" class="w-5 h-5"></i> 
                                <span x-text="currentStock > 0 ? (loading ? 'Adding...' : 'Add to Cart') : 'Out of Stock'"></span>
                            </button>
                            
                            <button type="button" @click="
                                window.dispatchEvent(new CustomEvent('open-checkout', {
                                    detail: {
                                        product_id: {{ $product->id }},
                                        variation_id: activeVariation ? activeVariation.id : null,
                                        product_name: '{{ addslashes($product->name) }}',
                                        price: currentPrice,
                                        quantity: quantity,
                                        product_image: activeImage
                                    }
                                }))
                            " :disabled="currentStock <= 0" class="w-full bg-primary text-white h-14 rounded-xl font-semibold text-base sm:text-lg hover:bg-secondary transition-colors shadow-lg shadow-secondary/30 dark:shadow-none disabled:opacity-50 disabled:cursor-not-allowed">
                                Buy it Now
                            </button>
                        </div>
                    </div>

                    <!-- Trust Badges -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-6 border-t border-b border-gray-200 dark:border-gray-700 mb-8">
                        <div class="flex flex-col items-center text-center">
                            <i data-lucide="truck" class="w-6 h-6 text-gray-400 mb-2"></i>
                            <span class="text-xs text-gray-500 font-medium">Free Shipping</span>
                        </div>
                        <div class="flex flex-col items-center text-center">
                            <i data-lucide="shield-check" class="w-6 h-6 text-gray-400 mb-2"></i>
                            <span class="text-xs text-gray-500 font-medium">Secure Checkout</span>
                        </div>
                        <div class="flex flex-col items-center text-center">
                            <i data-lucide="refresh-cw" class="w-6 h-6 text-gray-400 mb-2"></i>
                            <span class="text-xs text-gray-500 font-medium">30-Day Returns</span>
                        </div>
                        <div class="flex flex-col items-center text-center">
                            <i data-lucide="award" class="w-6 h-6 text-gray-400 mb-2"></i>
                            <span class="text-xs text-gray-500 font-medium">1 Year Warranty</span>
                        </div>
                    </div>

                    <!-- Accordions -->
                    <div class="space-y-4">

                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden bg-white dark:bg-gray-800">
                            <button type="button" @click="accordion = accordion === 'shipping' ? '' : 'shipping'" class="w-full px-6 py-4 flex justify-between items-center text-left focus:outline-none">
                                <span class="font-semibold text-gray-900 dark:text-white">Shipping & Returns</span>
                                <i data-lucide="chevron-down" class="w-5 h-5 text-gray-400 transition-transform duration-300" :class="{'rotate-180': accordion === 'shipping'}"></i>
                            </button>
                            <div x-show="accordion === 'shipping'" x-collapse>
                                <div class="px-6 pb-4 text-sm text-gray-600 dark:text-gray-300 space-y-2">
                                    <p><strong>Free Standard Shipping:</strong> 3-5 business days.</p>
                                    <p><strong>Express Shipping:</strong> 1-2 business days ($15.00).</p>
                                    <p>We accept returns within 30 days of receipt. Items must be in their original condition and packaging. Return shipping is covered by us for defective items.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    <!-- Full Width Description -->
    @if($product->content)
    <div class="mt-16 lg:mt-24 border-t border-gray-200 dark:border-gray-800 pt-12">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">Product Description</h2>
        <div class="bg-white dark:bg-gray-800 p-4 sm:p-8 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm w-full overflow-hidden">
            <div class="prose dark:prose-invert max-w-none w-full break-words overflow-x-auto">
                {!! $product->content !!}
            </div>
        </div>
    </div>
    @endif

    <!-- Customer Reviews Section -->
    <div id="customer-reviews" class="mt-16 lg:mt-24 border-t border-gray-200 dark:border-gray-800 pt-12">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">Customer Reviews</h2>
        
        @if(session('success'))
            <div class="mb-8 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700">
                {!! session('success') !!}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-8 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            <!-- Review Summary & Form -->
            <div class="lg:col-span-1">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700">
                    <div class="text-center mb-6">
                        <div class="text-5xl font-extrabold text-gray-900 dark:text-white mb-2">{{ number_format($product->averageRating(), 1) }}</div>
                        <div class="flex justify-center text-yellow-400 mb-2">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= round($product->averageRating()))
                                    <i data-lucide="star" class="w-5 h-5 fill-current"></i>
                                @else
                                    <i data-lucide="star" class="w-5 h-5 text-gray-300 dark:text-gray-600"></i>
                                @endif
                            @endfor
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Based on {{ $product->reviewsCount() }} reviews</p>
                    </div>

                    @auth
                        @php
                            $deliveredOrders = \App\Models\Order::where('user_id', auth()->id())
                                ->where('status', 'delivered')
                                ->whereHas('items', function ($query) use ($product) {
                                    $query->where('product_id', $product->id);
                                })->get();

                            $unreviewedCount = 0;
                            foreach($deliveredOrders as $ord) {
                                $reviewed = \App\Models\Review::where('user_id', auth()->id())
                                    ->where('product_id', $product->id)
                                    ->where('order_id', $ord->id)
                                    ->exists();
                                if(!$reviewed) {
                                    $unreviewedCount++;
                                }
                            }

                            $canReview = $unreviewedCount > 0;
                            $hasReviewed = $deliveredOrders->count() > 0 && $unreviewedCount === 0;
                        @endphp
                        
                        <hr class="border-gray-200 dark:border-gray-700 my-6">
                        @if($hasReviewed)
                            <div class="text-center mt-6 p-4 bg-gray-50 border border-gray-100 dark:bg-gray-800 dark:border-gray-700 rounded-xl">
                                <p class="text-sm text-gray-600 dark:text-gray-400">You have reviewed all your purchases for this product. Thank you!</p>
                            </div>
                        @elseif($canReview)
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Write a Review</h3>
                            <form action="{{ route('product.review.store', $product->id) }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rating</label>
                                    <div class="flex gap-2" x-data="{ rating: 5, hover: 0 }">
                                        <input type="hidden" name="rating" x-model="rating">
                                        <template x-for="i in 5">
                                            <button type="button" 
                                                @click="rating = i" 
                                                @mouseenter="hover = i" 
                                                @mouseleave="hover = 0"
                                                class="focus:outline-none transition-colors"
                                                :class="(hover >= i || (!hover && rating >= i)) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600'">
                                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-6 h-6"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Comment (Optional)</label>
                                    <textarea name="comment" rows="3" class="w-full border-gray-300 dark:border-gray-600 rounded-xl bg-white dark:bg-gray-700 focus:ring-primary focus:border-primary sm:text-sm" placeholder="What did you like or dislike?"></textarea>
                                </div>
                                <button type="submit" class="w-full bg-primary text-white rounded-xl py-2 px-4 hover:bg-secondary transition-colors font-medium">Submit Review</button>
                            </form>
                        @else
                            <div class="text-center mt-6 p-4 bg-gray-50 border border-gray-100 dark:bg-gray-800 dark:border-gray-700 rounded-xl">
                                <p class="text-sm text-gray-600 dark:text-gray-400">Only customers who have purchased this product can leave a review.</p>
                            </div>
                        @endif
                    @else
                        <div class="text-center mt-6">
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">Please log in to write a review.</p>
                            <a href="{{ route('account.login') }}" class="inline-block border border-primary text-primary hover:bg-primary hover:text-white rounded-xl py-2 px-6 font-medium transition-colors">Log In</a>
                        </div>
                    @endauth
                </div>
            </div>

            <!-- Review List -->
            <div class="lg:col-span-2">
                @if($product->reviews->count() > 0)
                    <div class="space-y-6">
                        @foreach($product->reviews as $review)
                            <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 border border-gray-100 dark:border-gray-700 shadow-sm">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="font-bold text-gray-900 dark:text-white">{{ $review->user->name ?? 'Anonymous' }}</span>
                                            @if($review->is_verified_purchase)
                                                <span class="inline-flex items-center gap-1 text-[10px] uppercase tracking-wider font-bold text-green-600 bg-green-100 px-2 py-0.5 rounded-full">
                                                    <i data-lucide="check-circle" class="w-3 h-3"></i> Verified Buyer
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex text-yellow-400">
                                            @for($i = 1; $i <= 5; $i++)
                                                @if($i <= $review->rating)
                                                    <i data-lucide="star" class="w-3.5 h-3.5 fill-current"></i>
                                                @else
                                                    <i data-lucide="star" class="w-3.5 h-3.5 text-gray-300 dark:text-gray-600"></i>
                                                @endif
                                            @endfor
                                        </div>
                                    </div>
                                    <span class="text-xs text-gray-500">{{ $review->created_at->diffForHumans() }}</span>
                                </div>
                                @if($review->comment)
                                    <p class="text-gray-600 dark:text-gray-300 text-sm leading-relaxed">{{ $review->comment }}</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 bg-gray-50 dark:bg-gray-800/50 rounded-2xl border border-dashed border-gray-200 dark:border-gray-700">
                        <i data-lucide="message-square" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white">No reviews yet</h3>
                        <p class="text-gray-500 dark:text-gray-400 mt-1">Be the first to share your experience!</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Related Products -->
    @if($relatedProducts->count() > 0)
    <div class="border-t border-gray-200 dark:border-gray-800 mt-24">
        <x-product-block 
            title="You Might Also Like" 
            :products="$relatedProducts" 
        />
    </div>
    @endif
    
    </div> <!-- Close max-w-7xl container -->
</div> <!-- Close Alpine component wrapper -->
@endsection

@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('productPage', () => ({
            product: @json($product),
            basePrice: {{ $product->price }},
            currentPrice: {{ $product->price }},
            baseStock: {{ $product->stock_quantity }},
            currentStock: {{ $product->stock_quantity }},
            quantity: 1,
            loading: false,
            images: [],
            activeImage: '',
            variations: {!! json_encode($product->variations->map(function($var) {
                return [
                    'id' => $var->id,
                    'name' => $var->name,
                    'options' => $var->options,
                    'price' => $var->price,
                    'stock_quantity' => $var->stock_quantity,
                    'media_url' => $var->media ? $var->media->url : null
                ];
            })) !!},
            activeVariation: null,
            selectedOptions: {},
            accordion: 'description',
            zoom: false,

            init() {
                // Compile images array
                if (this.product.thumbnail && this.product.thumbnail.url) {
                    this.images.push(this.product.thumbnail.url);
                }
                
                if (this.product.gallery) {
                    this.product.gallery.forEach(img => {
                        this.images.push(img.url);
                    });
                }
                
                // Add variation images
                this.variations.forEach(v => {
                    if (v.media_url && !this.images.includes(v.media_url)) {
                        this.images.push(v.media_url);
                    }
                });

                // Initialize selected options with the first available values
                if (this.product.options && this.product.options.length > 0) {
                    this.product.options.forEach(opt => {
                        if (opt.values && opt.values.length > 0) {
                            this.selectedOptions[opt.name] = opt.values[0].name;
                        }
                    });
                }

                // Fallback image
                if (this.images.length === 0) {
                    this.images.push('https://images.unsplash.com/photo-1505740420928-5e560c06d30e?ixlib=rb-1.2.1&auto=format&fit=crop&w=800&q=80');
                }

                this.activeImage = this.images[0];
                
                if(this.variations.length > 0) {
                    this.updateSelection();
                }
            },

            updateSelection() {
                let match = this.variations.find(v => {
                    if (!v.options) return false;
                    // Check if all selected options match this variation's options
                    for (let key in this.selectedOptions) {
                        if (v.options[key] !== this.selectedOptions[key]) {
                            return false;
                        }
                    }
                    return true;
                });

                if (match) {
                    this.activeVariation = match;
                    this.currentPrice = match.price ? parseFloat(match.price) : this.basePrice;
                    this.currentStock = match.stock_quantity;
                    this.quantity = 1;
                    if (match.media_url) this.activeImage = match.media_url;
                } else {
                    this.activeVariation = null;
                    this.currentStock = 0;
                }
            },

            selectOption(optionName, value) {
                this.selectedOptions[optionName] = value;
                this.updateSelection();
            },

            isOptionAvailable(optionName, value) {
                // Find if there is ANY variation that has `value` for `optionName`
                // AND matches all OTHER currently selected options, AND has stock > 0
                let match = this.variations.find(v => {
                    if (!v.options || v.options[optionName] !== value || v.stock_quantity <= 0) {
                        return false;
                    }
                    
                    // Check if it matches other selected options
                    for (let key in this.selectedOptions) {
                        if (key !== optionName && v.options[key] !== this.selectedOptions[key]) {
                            return false;
                        }
                    }
                    
                    return true;
                });
                return !!match;
            },

            zoomImage(e) {
                if(!this.zoom) return;
                const image = e.target;
                const { left, top, width, height } = image.getBoundingClientRect();
                const x = (e.clientX - left) / width * 100;
                const y = (e.clientY - top) / height * 100;
                image.style.transformOrigin = `${x}% ${y}%`;
            }
        }));
    });
</script>
@endpush
