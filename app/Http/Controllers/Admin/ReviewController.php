<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{
    /**
     * Display a listing of customer and admin reviews.
     */
    public function index(Request $request): View
    {
        $query = Review::with(['product.thumbnail', 'user'])->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->rating);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('reviewer_name', 'like', "%{$search}%")
                    ->orWhere('reviewer_email', 'like', "%{$search}%")
                    ->orWhere('comment', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('product', function ($pq) use ($search) {
                        $pq->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $reviews = $query->paginate(15)->withQueryString();

        $stats = [
            'total' => Review::count(),
            'approved' => Review::where('status', 'approved')->count(),
            'pending' => Review::where('status', 'pending')->count(),
            'average_rating' => round(Review::where('status', 'approved')->avg('rating') ?? 0, 1),
        ];

        $products = Product::select('id', 'name')->orderBy('name')->get();

        return view('admin.reviews.index', compact('reviews', 'stats', 'products'));
    }

    /**
     * Show the form for creating a new review.
     */
    public function create(Request $request): View
    {
        $products = Product::select('id', 'name', 'price')->orderBy('name')->get();
        $users = User::select('id', 'name', 'phone')->orderBy('name')->get();
        $selectedProductId = $request->query('product_id');

        return view('admin.reviews.create', compact('products', 'users', 'selectedProductId'));
    }

    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'reviewer_name' => 'required|string|max:255',
            'reviewer_email' => 'nullable|email|max:255',
            'user_id' => 'nullable|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'is_verified_purchase' => 'nullable|boolean',
            'status' => 'required|in:approved,pending,rejected',
            'created_at' => 'nullable|date',
            'media_files.*' => 'nullable|image|max:5120',
            'media_urls' => 'nullable|string',
        ]);

        $mediaList = [];

        // Upload media files if provided
        if ($request->hasFile('media_files')) {
            foreach ($request->file('media_files') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('reviews', 'public');
                    $mediaList[] = asset('storage/'.$path);
                }
            }
        }

        // Add media URLs if provided
        if ($request->filled('media_urls')) {
            $urls = array_filter(array_map('trim', explode("\n", $request->media_urls)));
            foreach ($urls as $url) {
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $mediaList[] = $url;
                }
            }
        }

        $reviewData = [
            'product_id' => $validated['product_id'],
            'user_id' => $validated['user_id'] ?? null,
            'reviewer_name' => $validated['reviewer_name'],
            'reviewer_email' => $validated['reviewer_email'] ?? null,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_verified_purchase' => $request->boolean('is_verified_purchase'),
            'status' => $validated['status'],
            'media' => count($mediaList) > 0 ? $mediaList : null,
        ];

        if ($request->filled('created_at')) {
            $reviewData['created_at'] = $request->created_at;
        }

        Review::create($reviewData);

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Review added successfully! It is now counted and active for the product.');
    }

    /**
     * Show the form for editing the specified review.
     */
    public function edit(Review $review): View
    {
        $products = Product::select('id', 'name', 'price')->orderBy('name')->get();
        $users = User::select('id', 'name', 'phone')->orderBy('name')->get();

        return view('admin.reviews.edit', compact('review', 'products', 'users'));
    }

    /**
     * Update the specified review in storage.
     */
    public function update(Request $request, Review $review): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'reviewer_name' => 'required|string|max:255',
            'reviewer_email' => 'nullable|email|max:255',
            'user_id' => 'nullable|exists:users,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'is_verified_purchase' => 'nullable|boolean',
            'status' => 'required|in:approved,pending,rejected',
            'created_at' => 'nullable|date',
            'media_files.*' => 'nullable|image|max:5120',
            'media_urls' => 'nullable|string',
            'existing_media' => 'nullable|array',
        ]);

        $mediaList = $request->input('existing_media', []);

        // Upload new media files
        if ($request->hasFile('media_files')) {
            foreach ($request->file('media_files') as $file) {
                if ($file->isValid()) {
                    $path = $file->store('reviews', 'public');
                    $mediaList[] = asset('storage/'.$path);
                }
            }
        }

        // Add additional URLs
        if ($request->filled('media_urls')) {
            $urls = array_filter(array_map('trim', explode("\n", $request->media_urls)));
            foreach ($urls as $url) {
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $mediaList[] = $url;
                }
            }
        }

        $reviewData = [
            'product_id' => $validated['product_id'],
            'user_id' => $validated['user_id'] ?? null,
            'reviewer_name' => $validated['reviewer_name'],
            'reviewer_email' => $validated['reviewer_email'] ?? null,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_verified_purchase' => $request->boolean('is_verified_purchase'),
            'status' => $validated['status'],
            'media' => count($mediaList) > 0 ? array_values($mediaList) : null,
        ];

        if ($request->filled('created_at')) {
            $reviewData['created_at'] = $request->created_at;
        }

        $review->update($reviewData);

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Review updated successfully.');
    }

    /**
     * Remove the specified review from storage.
     */
    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return redirect()->route('admin.reviews.index')
            ->with('success', 'Review deleted successfully.');
    }

    /**
     * Toggle or update status directly from index.
     */
    public function updateStatus(Request $request, Review $review): RedirectResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,pending,rejected',
        ]);

        $review->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', "Review marked as {$validated['status']}.");
    }
}
