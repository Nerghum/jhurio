@extends('layouts.admin')

@section('header', 'Customer & Product Reviews')

@section('content')
<div class="space-y-6">
    <!-- Top Stats Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center flex-shrink-0">
                <i data-lucide="message-square" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs text-gray-500 dark:text-gray-400 block font-medium">Total Reviews</span>
                <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</span>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center flex-shrink-0">
                <i data-lucide="star" class="w-6 h-6 fill-current"></i>
            </div>
            <div>
                <span class="text-xs text-gray-500 dark:text-gray-400 block font-medium">Average Rating</span>
                <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['average_rating'] }} / 5</span>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-green-500/10 text-green-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="check-circle" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs text-gray-500 dark:text-gray-400 block font-medium">Approved (Active)</span>
                <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['approved'] }}</span>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-amber-400/10 text-amber-600 flex items-center justify-center flex-shrink-0">
                <i data-lucide="clock" class="w-6 h-6"></i>
            </div>
            <div>
                <span class="text-xs text-gray-500 dark:text-gray-400 block font-medium">Pending Moderation</span>
                <span class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['pending'] }}</span>
            </div>
        </div>
    </div>

    <!-- Actions & Filter Bar -->
    <div class="bg-white dark:bg-gray-800 p-4 sm:p-5 rounded-2xl border border-gray-100 dark:border-gray-700 shadow-sm">
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center justify-between">
            <div class="flex-1 grid grid-cols-1 sm:grid-cols-3 gap-3">
                <!-- Search Input -->
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search reviewer, comment, product..." 
                           class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-2.5 text-gray-400"></i>
                </div>

                <!-- Status Filter -->
                <div>
                    <select name="status" onchange="this.form.submit()" 
                            class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary">
                        <option value="">All Statuses</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved (Public)</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <!-- Product Filter -->
                <div>
                    <select name="product_id" onchange="this.form.submit()" 
                            class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 dark:bg-gray-900 dark:text-white focus:ring-primary focus:border-primary truncate">
                        <option value="">All Products</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}" {{ request('product_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                @if(request()->hasAny(['search', 'status', 'product_id', 'rating']))
                    <a href="{{ route('admin.reviews.index') }}" class="px-3.5 py-2 text-xs rounded-xl border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        Reset Filters
                    </a>
                @endif

                <a href="{{ route('admin.reviews.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary text-white text-xs font-bold rounded-xl hover:bg-secondary transition shadow-sm whitespace-nowrap">
                    <i data-lucide="plus" class="w-4 h-4"></i>
                    <span>Add Real Review</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Reviews Table Card -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 dark:bg-gray-900/40 text-gray-500 dark:text-gray-400 text-xs uppercase tracking-wider border-b border-gray-100 dark:border-gray-700">
                        <th class="px-6 py-3.5 font-bold">Product</th>
                        <th class="px-6 py-3.5 font-bold">Reviewer</th>
                        <th class="px-6 py-3.5 font-bold">Rating & Comment</th>
                        <th class="px-6 py-3.5 font-bold">Photos</th>
                        <th class="px-6 py-3.5 font-bold">Date</th>
                        <th class="px-6 py-3.5 font-bold">Status</th>
                        <th class="px-6 py-3.5 font-bold text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700 text-sm">
                    @forelse($reviews as $review)
                    <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-700/30 transition-colors">
                        <!-- Product -->
                        <td class="px-6 py-4">
                            @if($review->product)
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-700 flex-shrink-0 border border-gray-200 dark:border-gray-600">
                                        <img src="{{ $review->product->thumbnail ? $review->product->thumbnail->url : asset('placeholder.png') }}" 
                                             class="w-full h-full object-cover" 
                                             alt="{{ $review->product->name }}">
                                    </div>
                                    <div class="min-w-0 max-w-[200px]">
                                        <a href="{{ route('product.show', $review->product->slug) }}" target="_blank" 
                                           class="text-xs font-bold text-gray-900 dark:text-white hover:text-primary transition-colors block truncate"
                                           title="{{ $review->product->name }}">
                                            {{ $review->product->name }}
                                        </a>
                                        <span class="text-[11px] text-gray-400 block">৳{{ number_format($review->product->price, 2) }}</span>
                                    </div>
                                </div>
                            @else
                                <span class="text-xs text-gray-400 italic">Deleted Product</span>
                            @endif
                        </td>

                        <!-- Reviewer -->
                        <td class="px-6 py-4">
                            <div class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                <span>{{ $review->author_name }}</span>
                                @if($review->is_verified_purchase)
                                    <span title="Verified Purchase" class="text-primary">
                                        <i data-lucide="check-circle" class="w-3.5 h-3.5 fill-primary/10"></i>
                                    </span>
                                @endif
                            </div>
                            @if($review->reviewer_email || ($review->user && $review->user->email))
                                <span class="text-[11px] text-gray-400 block">{{ $review->reviewer_email ?: $review->user->email }}</span>
                            @elseif($review->user && $review->user->phone)
                                <span class="text-[11px] text-gray-400 block">{{ $review->user->phone }}</span>
                            @else
                                <span class="text-[10px] text-gray-400 bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded inline-block mt-0.5">Admin entry</span>
                            @endif
                        </td>

                        <!-- Rating & Comment -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-1 text-amber-400 mb-1">
                                @for($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-amber-400 fill-amber-400' : 'text-gray-300 dark:text-gray-600 fill-none' }}" viewBox="0 0 20 20" stroke="currentColor" stroke-width="0.5">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endfor
                                <span class="text-xs font-extrabold text-gray-700 dark:text-gray-300 ml-1">{{ $review->rating }}.0</span>
                            </div>
                            <div class="text-xs text-gray-600 dark:text-gray-300 max-w-sm line-clamp-2">
                                {{ $review->comment ?: '(No written feedback)' }}
                            </div>
                        </td>

                        <!-- Photos -->
                        <td class="px-6 py-4">
                            @if(!empty($review->media) && is_array($review->media))
                                <div class="flex items-center gap-1.5">
                                    @foreach(array_slice($review->media, 0, 3) as $mediaUrl)
                                        <a href="{{ $mediaUrl }}" target="_blank" class="w-8 h-8 rounded-lg overflow-hidden border border-gray-200 dark:border-gray-700 block hover:opacity-80 transition">
                                            <img src="{{ $mediaUrl }}" class="w-full h-full object-cover">
                                        </a>
                                    @endforeach
                                    @if(count($review->media) > 3)
                                        <span class="text-[10px] text-gray-400 font-bold">+{{ count($review->media) - 3 }}</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>

                        <!-- Date -->
                        <td class="px-6 py-4 text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                            {{ $review->created_at->format('M d, Y') }}
                            <span class="block text-[10px] text-gray-400">{{ $review->created_at->diffForHumans() }}</span>
                        </td>

                        <!-- Status with quick toggle -->
                        <td class="px-6 py-4 whitespace-nowrap">
                            <form action="{{ route('admin.reviews.update_status', $review) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" 
                                        class="text-xs font-bold py-1 px-2.5 rounded-lg border cursor-pointer transition
                                        {{ $review->status === 'approved' ? 'bg-green-50 text-green-700 border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800' : '' }}
                                        {{ $review->status === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/30 dark:text-amber-300 dark:border-amber-800' : '' }}
                                        {{ $review->status === 'rejected' ? 'bg-red-50 text-red-700 border-red-200 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800' : '' }}">
                                    <option value="approved" {{ $review->status === 'approved' ? 'selected' : '' }}>Approved</option>
                                    <option value="pending" {{ $review->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="rejected" {{ $review->status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </form>
                        </td>

                        <!-- Actions -->
                        <td class="px-6 py-4 text-right whitespace-nowrap">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.reviews.edit', $review) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold text-primary bg-primary/10 hover:bg-primary hover:text-white transition shadow-sm">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    <span>Edit</span>
                                </a>
                                <form action="{{ route('admin.reviews.destroy', $review) }}" method="POST" 
                                      onsubmit="return confirm('Are you sure you want to permanently delete this review?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            title="Delete Review"
                                            class="p-1.5 rounded-xl text-red-500 hover:text-red-700 hover:bg-red-50 dark:hover:bg-red-900/30 transition">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="w-14 h-14 rounded-2xl bg-primary/10 text-primary mx-auto flex items-center justify-center mb-3">
                                <i data-lucide="message-square" class="w-7 h-7"></i>
                            </div>
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">No Reviews Found</h4>
                            <p class="text-xs text-gray-500 mt-1 max-w-sm mx-auto mb-4">
                                No customer reviews match your criteria. You can create verified reviews to build credibility and trust.
                            </p>
                            <a href="{{ route('admin.reviews.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 bg-primary text-white text-xs font-bold rounded-xl hover:bg-secondary transition shadow-sm">
                                <i data-lucide="plus" class="w-4 h-4"></i>
                                <span>Add First Review</span>
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reviews->hasPages())
            <div class="p-4 border-t border-gray-100 dark:border-gray-700">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
