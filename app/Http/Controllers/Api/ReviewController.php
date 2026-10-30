<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Review::with('product:id,name,slug')->latest();

        if ($request->filled('product_id')) {
            $query->where('product_id', $request->input('product_id'));
        } elseif ($request->filled('slug')) {
            $product = Product::where('slug', $request->input('slug'))->first();
            if ($product) {
                $query->where('product_id', $product->id);
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $limit = (int) $request->input('limit', 50);
        $reviews = $query->paginate($limit);

        return response()->json([
            'total' => $reviews->total(),
            'reviews' => $reviews->items(),
            'current_page' => $reviews->currentPage(),
            'last_page' => $reviews->lastPage(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $review = Review::with('product')->find($id);

        if (! $review) {
            return response()->json(['error' => 'Review not found'], 404);
        }

        return response()->json([
            'review' => $review,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'slug' => 'nullable|string',
            'reviewer_name' => 'required|string|max:255',
            'reviewer_email' => 'nullable|email|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'is_verified_purchase' => 'nullable|boolean',
            'status' => 'nullable|in:approved,pending,spam',
            'media' => 'nullable|array',
            'media.*' => 'string',
            'created_at' => 'nullable|date',
        ]);

        $productId = $validated['product_id'] ?? null;
        if (! $productId && ! empty($validated['slug'])) {
            $product = Product::where('slug', $validated['slug'])->first();
            $productId = $product?->id;
        }

        if (! $productId) {
            return response()->json(['error' => 'A valid product_id or slug is required.'], 422);
        }

        $review = Review::create([
            'product_id' => $productId,
            'user_id' => null,
            'reviewer_name' => $validated['reviewer_name'],
            'reviewer_email' => $validated['reviewer_email'] ?? null,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_verified_purchase' => $validated['is_verified_purchase'] ?? true,
            'status' => $validated['status'] ?? 'approved',
            'media' => $validated['media'] ?? null,
            'created_at' => ! empty($validated['created_at']) ? Carbon::parse($validated['created_at']) : now(),
        ]);

        return response()->json([
            'message' => 'Review created successfully',
            'review' => $review->load('product:id,name,slug'),
        ], 201);
    }

    public function storeBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'slug' => 'nullable|string',
            'reviews' => 'required|array|min:1',
            'reviews.*.reviewer_name' => 'required|string|max:255',
            'reviews.*.reviewer_email' => 'nullable|email|max:255',
            'reviews.*.rating' => 'required|integer|min:1|max:5',
            'reviews.*.comment' => 'nullable|string',
            'reviews.*.is_verified_purchase' => 'nullable|boolean',
            'reviews.*.status' => 'nullable|in:approved,pending,spam',
            'reviews.*.media' => 'nullable|array',
            'reviews.*.media.*' => 'string',
            'reviews.*.created_at' => 'nullable|date',
        ]);

        $productId = $validated['product_id'] ?? null;
        if (! $productId && ! empty($validated['slug'])) {
            $product = Product::where('slug', $validated['slug'])->first();
            $productId = $product?->id;
        }

        if (! $productId) {
            return response()->json(['error' => 'A valid product_id or slug is required.'], 422);
        }

        $created = [];
        foreach ($validated['reviews'] as $rev) {
            $created[] = Review::create([
                'product_id' => $productId,
                'user_id' => null,
                'reviewer_name' => $rev['reviewer_name'],
                'reviewer_email' => $rev['reviewer_email'] ?? null,
                'rating' => (int) $rev['rating'],
                'comment' => $rev['comment'] ?? null,
                'is_verified_purchase' => $rev['is_verified_purchase'] ?? true,
                'status' => $rev['status'] ?? 'approved',
                'media' => $rev['media'] ?? null,
                'created_at' => ! empty($rev['created_at']) ? Carbon::parse($rev['created_at']) : now(),
            ]);
        }

        return response()->json([
            'message' => count($created).' reviews created successfully',
            'count' => count($created),
            'reviews' => $created,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $review = Review::find($id);

        if (! $review) {
            return response()->json(['error' => 'Review not found'], 404);
        }

        $validated = $request->validate([
            'status' => 'nullable|in:approved,pending,spam',
            'rating' => 'nullable|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'reviewer_name' => 'nullable|string|max:255',
            'reviewer_email' => 'nullable|email|max:255',
            'is_verified_purchase' => 'nullable|boolean',
        ]);

        $review->update(array_filter($validated, fn ($v) => ! is_null($v)));

        return response()->json([
            'message' => 'Review updated successfully',
            'review' => $review->fresh(),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $review = Review::find($id);

        if (! $review) {
            return response()->json(['error' => 'Review not found'], 404);
        }

        $review->delete();

        return response()->json([
            'message' => 'Review deleted successfully',
        ]);
    }
}
