@extends('layouts.admin')

@section('header', 'Edit Review')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Edit Review #{{ $review->id }}</h2>
            <p class="text-xs text-gray-500 mt-1">Update review details, verified status, or rating.</p>
        </div>
        <a href="{{ route('admin.reviews.index') }}" class="px-3.5 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
            Back to Reviews
        </a>
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 text-xs space-y-1">
            @foreach($errors->all() as $error)
                <p>• {{ $error }}</p>
            @endforeach
        </div>
    @endif

    <form action="{{ route('admin.reviews.update', $review) }}" method="POST" enctype="multipart/form-data" 
          x-data="{ rating: {{ old('rating', $review->rating) }}, verified: {{ old('is_verified_purchase', $review->is_verified_purchase) ? 'true' : 'false' }} }"
          class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-gray-800 rounded-2xl p-6 sm:p-8 border border-gray-100 dark:border-gray-700 shadow-sm space-y-6">
            
            <!-- Row 1: Product Selection & Star Rating -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Select Product -->
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                        Product <span class="text-red-500">*</span>
                    </label>
                    <select name="product_id" required 
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ old('product_id', $review->product_id) == $product->id ? 'selected' : '' }}>
                                {{ $product->name }} (৳{{ number_format($product->price, 2) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Star Rating -->
                <div x-data="{ hoverRating: 0 }">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                        Rating (1 - 5 Stars) <span class="text-red-500">*</span>
                    </label>
                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            <input type="hidden" name="rating" :value="rating">
                            
                            <!-- 5 Interactive Clickable / Hoverable Stars -->
                            <div class="inline-flex items-center gap-1 p-2 rounded-xl bg-amber-50/80 dark:bg-amber-950/30 border border-amber-200/80 dark:border-amber-800/80 shadow-inner">
                                @for($i = 1; $i <= 5; $i++)
                                    <button type="button" 
                                            @click="rating = {{ $i }}"
                                            @mouseenter="hoverRating = {{ $i }}"
                                            @mouseleave="hoverRating = 0"
                                            class="p-1 focus:outline-none transition-transform hover:scale-125 cursor-pointer"
                                            title="{{ $i }} Star{{ $i > 1 ? 's' : '' }}">
                                        <svg class="w-8 h-8 transition-colors drop-shadow-sm pointer-events-none" 
                                             :class="(hoverRating ? {{ $i }} <= hoverRating : {{ $i }} <= rating) ? 'text-amber-400 fill-amber-400' : 'text-gray-300 dark:text-gray-600 fill-none'" 
                                             viewBox="0 0 20 20" 
                                             stroke="currentColor"
                                             stroke-width="0.5">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </button>
                                @endfor
                            </div>

                            <!-- Live Star Count Badge -->
                            <span class="text-sm font-extrabold text-amber-500 bg-amber-50 dark:bg-amber-900/30 px-3 py-1.5 rounded-xl border border-amber-200 dark:border-amber-700" 
                                  x-text="(hoverRating || rating) + ' Stars (' + (hoverRating || rating) + '.0)'"></span>
                        </div>

                        <!-- Dropdown selector fallback -->
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400 font-medium">Or select:</span>
                            <select @change="rating = parseInt($event.target.value)" :value="rating" 
                                    class="px-3 py-1 text-xs font-semibold rounded-lg border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                                <option value="5">★★★★★ 5 Stars (Excellent)</option>
                                <option value="4">★★★★☆ 4 Stars (Good)</option>
                                <option value="3">★★★☆☆ 3 Stars (Average)</option>
                                <option value="2">★★☆☆☆ 2 Stars (Below Average)</option>
                                <option value="1">★☆☆☆☆ 1 Star (Poor)</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 2: Reviewer Name & Email -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                        Reviewer Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="reviewer_name" value="{{ old('reviewer_name', $review->reviewer_name ?: ($review->user?->name ?? '')) }}" required 
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                        Reviewer Email <span class="text-gray-400 font-normal">(Optional)</span>
                    </label>
                    <input type="email" name="reviewer_email" value="{{ old('reviewer_email', $review->reviewer_email ?: ($review->user?->email ?? '')) }}" 
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                </div>
            </div>

            <!-- Row 3: Registered User Link & Custom Date -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                        Link to Registered User <span class="text-gray-400 font-normal">(Optional)</span>
                    </label>
                    <select name="user_id" 
                            class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                        <option value="">-- No User Account Link (Anonymous / Guest) --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('user_id', $review->user_id) == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} {{ $user->phone ? '('.$user->phone.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                        Review Date
                    </label>
                    <input type="datetime-local" name="created_at" 
                           value="{{ old('created_at', $review->created_at ? $review->created_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')) }}"
                           class="w-full px-3.5 py-2.5 text-xs rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                </div>
            </div>

            <!-- Row 4: Status & Verified Purchase Toggle -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                <div>
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                        Publication Status <span class="text-red-500">*</span>
                    </label>
                    <select name="status" required 
                            class="w-full px-3.5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">
                        <option value="approved" {{ old('status', $review->status) === 'approved' ? 'selected' : '' }}>Approved (Visible on Website)</option>
                        <option value="pending" {{ old('status', $review->status) === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ old('status', $review->status) === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <div class="flex flex-col justify-center">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2">
                        Verification Badge
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <input type="checkbox" name="is_verified_purchase" value="1" x-model="verified" class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary relative"></div>
                        <div>
                            <span class="text-xs font-bold text-gray-800 dark:text-gray-200 block" x-text="verified ? 'Verified Purchase (Badge ON)' : 'Unverified'"></span>
                            <span class="text-[11px] text-gray-400 block">Displays green verified buyer badge</span>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Row 5: Review Comment -->
            <div class="pt-2">
                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-1.5">
                    Customer Review / Feedback Text
                </label>
                <textarea name="comment" rows="4" 
                          class="w-full px-4 py-3 text-xs sm:text-sm rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary">{{ old('comment', $review->comment) }}</textarea>
            </div>

            <!-- Row 6: Existing Photos & New Photos -->
            <div class="pt-2 border-t border-gray-100 dark:border-gray-700 space-y-4">
                <div>
                    <h4 class="text-xs font-bold text-gray-900 dark:text-white">Review Photos</h4>
                </div>

                @if(!empty($review->media) && is_array($review->media))
                    <div>
                        <span class="text-xs font-medium text-gray-500 block mb-2">Current Photos (Uncheck to remove):</span>
                        <div class="flex flex-wrap gap-3">
                            @foreach($review->media as $idx => $mUrl)
                                <label class="relative group cursor-pointer border rounded-xl overflow-hidden p-1 bg-gray-50 dark:bg-gray-700">
                                    <img src="{{ $mUrl }}" class="w-16 h-16 object-cover rounded-lg">
                                    <input type="checkbox" name="existing_media[]" value="{{ $mUrl }}" checked class="absolute top-2 left-2 rounded text-primary focus:ring-primary">
                                </label>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Add More Photos from Device
                        </label>
                        <input type="file" name="media_files[]" multiple accept="image/*" 
                               class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 cursor-pointer">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Or Add Image URLs (one per line)
                        </label>
                        <textarea name="media_urls" rows="2" 
                                  placeholder="https://...&#10;https://..." 
                                  class="w-full px-3 py-1.5 text-xs rounded-xl border border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white focus:ring-primary focus:border-primary"></textarea>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="pt-4 flex items-center justify-end gap-3 border-t border-gray-100 dark:border-gray-700">
                <a href="{{ route('admin.reviews.index') }}" class="px-5 py-2.5 text-xs font-semibold rounded-xl border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2.5 bg-primary text-white text-xs font-bold rounded-xl hover:bg-secondary transition shadow-sm flex items-center gap-2">
                    <i data-lucide="check" class="w-4 h-4"></i>
                    <span>Update Review</span>
                </button>
            </div>
        </div>
    </form>
</div>
@endsection
