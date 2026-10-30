<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\Review;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['categories', 'tags', 'thumbnail'])
            ->withCount(['reviews as approved_reviews_count' => function ($query) {
                $query->where('status', 'approved');
            }]);

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('slug', 'like', "%{$s}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('category')) {
            $cat = $request->input('category');
            $query->whereHas('categories', function ($q) use ($cat) {
                if (is_numeric($cat)) {
                    $q->where('categories.id', $cat);
                } else {
                    $q->where('categories.slug', $cat)->orWhere('categories.name', $cat);
                }
            });
        }

        if ($request->has('is_featured')) {
            $query->where('is_featured', filter_var($request->input('is_featured'), FILTER_VALIDATE_BOOLEAN));
        }

        $query->latest();

        if ($request->has('limit')) {
            $limit = min((int) $request->input('limit', 20), 100);
            $products = $query->paginate($limit);

            return response()->json([
                'total' => $products->total(),
                'products' => $products->items(),
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
            ]);
        }

        $products = $query->get();

        return response()->json([
            'total' => $products->count(),
            'products' => $products,
        ]);
    }

    public function show(string|int $identifier): JsonResponse
    {
        $product = is_numeric($identifier)
            ? Product::find($identifier)
            : Product::where('slug', $identifier)->first();

        if (! $product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $product->load([
            'categories',
            'tags',
            'thumbnail',
            'gallery',
            'stockPurchases',
        ])->loadCount(['reviews as approved_reviews_count' => function ($query) {
            $query->where('status', 'approved');
        }]);

        return response()->json([
            'product' => $product,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        // 1. Support listing products via POST /api/products { "action": "list_products" }
        if ($request->input('action') === 'list_products' || $request->input('action') === 'get_products') {
            return $this->index($request);
        }

        // 2. Support adding reviews via POST /api/products { "action": "add_reviews", ... }
        if ($request->input('action') === 'add_reviews' || ($request->has('reviews') && ($request->has('product_id') || $request->has('slug')))) {
            $product = null;
            if ($request->filled('product_id')) {
                $product = Product::find($request->input('product_id'));
            } elseif ($request->filled('slug')) {
                $product = Product::where('slug', $request->input('slug'))->first();
            }

            if (! $product) {
                return response()->json([
                    'error' => 'Product not found. Please provide a valid product_id or slug.',
                ], 404);
            }

            $reviewsData = $request->input('reviews');
            if (! is_array($reviewsData) || empty($reviewsData)) {
                return response()->json([
                    'error' => 'The reviews field must be a non-empty array.',
                ], 422);
            }

            $createdReviews = [];
            foreach ($reviewsData as $rev) {
                if (empty($rev['reviewer_name'])) {
                    continue;
                }

                $createdAt = ! empty($rev['created_at'])
                    ? Carbon::parse($rev['created_at'])
                    : now();

                $createdReviews[] = Review::create([
                    'product_id' => $product->id,
                    'user_id' => $rev['user_id'] ?? null,
                    'reviewer_name' => $rev['reviewer_name'],
                    'reviewer_email' => $rev['reviewer_email'] ?? null,
                    'rating' => (int) ($rev['rating'] ?? 5),
                    'comment' => $rev['comment'] ?? null,
                    'is_verified_purchase' => isset($rev['is_verified_purchase']) ? (bool) $rev['is_verified_purchase'] : true,
                    'status' => $rev['status'] ?? 'approved',
                    'media' => ! empty($rev['media']) && is_array($rev['media']) ? $rev['media'] : null,
                    'created_at' => $createdAt,
                ]);
            }

            return response()->json([
                'message' => count($createdReviews).' reviews added successfully',
                'count' => count($createdReviews),
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'total_approved_reviews' => $product->reviews()->where('status', 'approved')->count(),
                ],
                'reviews' => $createdReviews,
            ], 201);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'price' => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'image_text_sections' => 'nullable|array',
            'video_url' => 'nullable|string',
            'video_reels' => 'nullable|array',
            'status' => 'nullable|in:draft,published,out_of_stock',
            'stock_quantity' => 'nullable|integer|min:0',
            'is_featured' => 'nullable|boolean',
            'categories' => 'nullable|array',
            'categories.*' => 'string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'image_urls' => 'nullable|array',
            'image_urls.*' => 'url',
            'thumbnail_media_id' => 'nullable|exists:media,id',
            'bundle_settings' => 'nullable|array',
            'options' => 'nullable|array',
        ]);

        $slug = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);

        // Ensure unique slug
        $originalSlug = $slug;
        $counter = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        $imageTextSections = $validated['image_text_sections'] ?? null;
        if (is_array($imageTextSections)) {
            $imageTextSections = array_map(function ($sec, $idx) {
                if (! isset($sec['content']) && isset($sec['description'])) {
                    $sec['content'] = $sec['description'];
                }
                if (! isset($sec['image_url']) && isset($sec['image'])) {
                    $sec['image_url'] = $sec['image'];
                }
                if (! isset($sec['display_position'])) {
                    $sec['display_position'] = 'after_description';
                }
                if (! isset($sec['image_position'])) {
                    $sec['image_position'] = $idx % 2 === 1 ? 'left' : 'right';
                }

                return $sec;
            }, $imageTextSections, array_keys($imageTextSections));
        }

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'price' => $validated['price'],
            'purchase_price' => $validated['purchase_price'] ?? 0,
            'compare_at_price' => $validated['compare_at_price'] ?? null,
            'description' => $validated['description'] ?? '',
            'content' => $validated['content'] ?? null,
            'image_text_sections' => $imageTextSections,
            'video_url' => $validated['video_url'] ?? null,
            'video_reels' => $validated['video_reels'] ?? null,
            'status' => $validated['status'] ?? 'published',
            'stock_quantity' => $validated['stock_quantity'] ?? 10,
            'is_featured' => $validated['is_featured'] ?? false,
            'thumbnail_media_id' => $validated['thumbnail_media_id'] ?? null,
            'bundle_settings' => $validated['bundle_settings'] ?? null,
            'options' => $validated['options'] ?? null,
        ]);

        // Attach categories
        if (! empty($validated['categories'])) {
            $categoryIds = collect($validated['categories'])->map(function ($categoryName) {
                $category = Category::firstOrCreate(
                    ['name' => $categoryName],
                    ['slug' => Str::slug($categoryName), 'is_active' => true]
                );

                return $category->id;
            })->toArray();

            $product->categories()->sync($categoryIds);
        }

        // Attach tags
        if (! empty($validated['tags'])) {
            $tagIds = collect($validated['tags'])->map(function ($tagName) {
                $tag = Tag::firstOrCreate(
                    ['name' => $tagName],
                    ['slug' => Str::slug($tagName)]
                );

                return $tag->id;
            })->toArray();

            $product->tags()->sync($tagIds);
        }

        // Process images
        if (! empty($validated['image_urls'])) {
            $this->processProductImages($product, $validated['image_urls']);
        }

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product->load('categories', 'tags', 'thumbnail', 'gallery'),
        ], 201);
    }

    public function update(Request $request, string|int $identifier): JsonResponse
    {
        $product = is_numeric($identifier)
            ? Product::find($identifier)
            : Product::where('slug', $identifier)->first();

        if (! $product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'image_text_sections' => 'nullable|array',
            'video_url' => 'nullable|string',
            'video_reels' => 'nullable|array',
            'status' => 'nullable|in:draft,published,out_of_stock',
            'stock_quantity' => 'nullable|integer|min:0',
            'is_featured' => 'nullable|boolean',
            'categories' => 'nullable|array',
            'categories.*' => 'string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'image_urls' => 'nullable|array',
            'image_urls.*' => 'url',
            'thumbnail_media_id' => 'nullable|exists:media,id',
            'bundle_settings' => 'nullable|array',
            'options' => 'nullable|array',
        ]);

        $updateData = array_filter([
            'name' => $validated['name'] ?? null,
            'slug' => ! empty($validated['slug']) ? Str::slug($validated['slug']) : null,
            'price' => $validated['price'] ?? null,
            'purchase_price' => $validated['purchase_price'] ?? null,
            'compare_at_price' => $validated['compare_at_price'] ?? null,
            'description' => $validated['description'] ?? null,
            'content' => $validated['content'] ?? null,
            'video_url' => $validated['video_url'] ?? null,
            'video_reels' => $validated['video_reels'] ?? null,
            'status' => $validated['status'] ?? null,
            'stock_quantity' => $validated['stock_quantity'] ?? null,
            'is_featured' => $validated['is_featured'] ?? null,
            'thumbnail_media_id' => $validated['thumbnail_media_id'] ?? null,
            'bundle_settings' => $validated['bundle_settings'] ?? null,
            'options' => $validated['options'] ?? null,
        ], fn ($val) => ! is_null($val));

        $product->update($updateData);

        if (array_key_exists('image_text_sections', $validated)) {
            $product->update(['image_text_sections' => $validated['image_text_sections']]);
        }

        if (isset($validated['categories'])) {
            $categoryIds = collect($validated['categories'])->map(function ($categoryName) {
                $category = Category::firstOrCreate(
                    ['name' => $categoryName],
                    ['slug' => Str::slug($categoryName), 'is_active' => true]
                );

                return $category->id;
            })->toArray();

            $product->categories()->sync($categoryIds);
        }

        if (isset($validated['tags'])) {
            $tagIds = collect($validated['tags'])->map(function ($tagName) {
                $tag = Tag::firstOrCreate(
                    ['name' => $tagName],
                    ['slug' => Str::slug($tagName)]
                );

                return $tag->id;
            })->toArray();

            $product->tags()->sync($tagIds);
        }

        if (! empty($validated['image_urls'])) {
            $this->processProductImages($product, $validated['image_urls']);
        }

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product->fresh()->load('categories', 'tags', 'thumbnail', 'gallery'),
        ]);
    }

    public function destroy(string|int $identifier): JsonResponse
    {
        $product = is_numeric($identifier)
            ? Product::find($identifier)
            : Product::where('slug', $identifier)->first();

        if (! $product) {
            return response()->json(['error' => 'Product not found'], 404);
        }

        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }

    public function addReview(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
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

        $review = Review::create([
            'product_id' => $product->id,
            'user_id' => null,
            'reviewer_name' => $validated['reviewer_name'],
            'reviewer_email' => $validated['reviewer_email'] ?? null,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_verified_purchase' => $validated['is_verified_purchase'] ?? true,
            'status' => $validated['status'] ?? 'approved',
            'media' => $validated['media'] ?? null,
            'created_at' => $validated['created_at'] ?? now(),
        ]);

        return response()->json([
            'message' => 'Review added successfully',
            'review' => $review,
        ], 201);
    }

    public function addReviewsBatch(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
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

        $createdReviews = [];
        foreach ($validated['reviews'] as $rev) {
            $createdReviews[] = Review::create([
                'product_id' => $product->id,
                'user_id' => null,
                'reviewer_name' => $rev['reviewer_name'],
                'reviewer_email' => $rev['reviewer_email'] ?? null,
                'rating' => (int) $rev['rating'],
                'comment' => $rev['comment'] ?? null,
                'is_verified_purchase' => $rev['is_verified_purchase'] ?? true,
                'status' => $rev['status'] ?? 'approved',
                'media' => $rev['media'] ?? null,
                'created_at' => $rev['created_at'] ?? now(),
            ]);
        }

        return response()->json([
            'message' => count($createdReviews).' reviews added successfully',
            'count' => count($createdReviews),
            'reviews' => $createdReviews,
        ], 201);
    }

    public function storeReviewDirect(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
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

        $review = Review::create([
            'product_id' => $validated['product_id'],
            'user_id' => null,
            'reviewer_name' => $validated['reviewer_name'],
            'reviewer_email' => $validated['reviewer_email'] ?? null,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_verified_purchase' => $validated['is_verified_purchase'] ?? true,
            'status' => $validated['status'] ?? 'approved',
            'media' => $validated['media'] ?? null,
            'created_at' => $validated['created_at'] ?? now(),
        ]);

        return response()->json([
            'message' => 'Review added successfully',
            'review' => $review,
        ], 201);
    }

    protected function processProductImages(Product $product, array $imageUrls): void
    {
        $sortOrder = $product->gallery()->max('sort_order') ?? 0;
        foreach ($imageUrls as $index => $imageUrl) {
            try {
                $imageContent = Http::timeout(20)->get($imageUrl)->body();

                if ($imageContent) {
                    $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
                    if (! $extension || ! in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                        $extension = 'png';
                    }

                    $filename = 'product-'.$product->id.'-'.Str::random(10).'.'.$extension;
                    $path = 'media/'.$filename;

                    Storage::disk('public')->put($path, $imageContent);

                    $media = Media::create([
                        'filename' => $filename,
                        'file_path' => $path,
                        'file_type' => 'image/'.($extension === 'jpg' ? 'jpeg' : $extension),
                        'file_size' => strlen($imageContent),
                    ]);

                    // Set first image as thumbnail if not set
                    if ($index === 0 && ! $product->thumbnail_media_id) {
                        $product->update(['thumbnail_media_id' => $media->id]);
                    }

                    // Attach to gallery
                    $product->gallery()->attach($media->id, ['sort_order' => ++$sortOrder]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to download image for product API: '.$imageUrl.' - '.$e->getMessage());
            }
        }
    }
}
