<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function store(Request $request)
    {
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
            'status' => 'nullable|in:draft,published,out_of_stock',
            'stock_quantity' => 'nullable|integer|min:0',
            'categories' => 'nullable|array',
            'categories.*' => 'string', // array of category names
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'image_urls' => 'nullable|array',
            'image_urls.*' => 'url',
        ]);

        $slug = ! empty($validated['slug']) ? Str::slug($validated['slug']) : Str::slug($validated['name']);

        // Ensure unique slug
        $originalSlug = $slug;
        $counter = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'price' => $validated['price'],
            'purchase_price' => $validated['purchase_price'] ?? 0,
            'compare_at_price' => $validated['compare_at_price'] ?? null,
            'description' => $validated['description'] ?? '',
            'content' => $validated['content'] ?? null,
            'image_text_sections' => $validated['image_text_sections'] ?? null,
            'video_url' => $validated['video_url'] ?? null,
            'status' => $validated['status'] ?? 'published',
            'stock_quantity' => $validated['stock_quantity'] ?? 10,
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
            $sortOrder = 0;
            foreach ($validated['image_urls'] as $index => $imageUrl) {
                try {
                    $imageContent = Http::timeout(20)->get($imageUrl)->body();

                    if ($imageContent) {
                        // Extract extension or default to png
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
                            'file_type' => 'image/'.($extension == 'jpg' ? 'jpeg' : $extension),
                            'file_size' => strlen($imageContent),
                        ]);

                        // Set first image as thumbnail
                        if ($index === 0) {
                            $product->update(['thumbnail_media_id' => $media->id]);
                        }

                        // Attach to gallery
                        $product->gallery()->attach($media->id, ['sort_order' => $sortOrder++]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to download image for product API: '.$imageUrl.' - '.$e->getMessage());
                }
            }
        }

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product->load('categories', 'tags', 'thumbnail', 'gallery'),
        ], 201);
    }

    public function update(Request $request, Product $product)
    {
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
            'status' => 'nullable|in:draft,published,out_of_stock',
            'stock_quantity' => 'nullable|integer|min:0',
            'categories' => 'nullable|array',
            'categories.*' => 'string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
        ]);

        $product->update(array_filter([
            'name' => $validated['name'] ?? null,
            'slug' => ! empty($validated['slug']) ? Str::slug($validated['slug']) : null,
            'price' => $validated['price'] ?? null,
            'purchase_price' => $validated['purchase_price'] ?? null,
            'compare_at_price' => $validated['compare_at_price'] ?? null,
            'description' => $validated['description'] ?? null,
            'content' => $validated['content'] ?? null,
            'image_text_sections' => array_key_exists('image_text_sections', $validated) ? $validated['image_text_sections'] : null,
            'video_url' => $validated['video_url'] ?? null,
            'status' => $validated['status'] ?? null,
            'stock_quantity' => $validated['stock_quantity'] ?? null,
        ], fn ($val) => ! is_null($val)));

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

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product->load('categories', 'tags', 'thumbnail', 'gallery'),
        ]);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
        ]);
    }
}
