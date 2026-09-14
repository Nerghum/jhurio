<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Media;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'nullable|in:draft,published,out_of_stock',
            'stock_quantity' => 'nullable|integer|min:0',
            'categories' => 'nullable|array',
            'categories.*' => 'string', // array of category names
            'image_urls' => 'nullable|array',
            'image_urls.*' => 'url',
        ]);

        $slug = Str::slug($validated['name']);
        
        // Ensure unique slug
        $originalSlug = $slug;
        $counter = 1;
        while (Product::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        $product = Product::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'price' => $validated['price'],
            'compare_at_price' => $validated['compare_at_price'] ?? null,
            'description' => $validated['description'] ?? '',
            'status' => $validated['status'] ?? 'published',
            'stock_quantity' => $validated['stock_quantity'] ?? 10,
        ]);

        // Attach categories
        if (!empty($validated['categories'])) {
            $categoryIds = collect($validated['categories'])->map(function ($categoryName) {
                $category = Category::firstOrCreate(
                    ['name' => $categoryName],
                    ['slug' => Str::slug($categoryName), 'is_active' => true]
                );
                return $category->id;
            })->toArray();
            
            $product->categories()->sync($categoryIds);
        }

        // Process images
        if (!empty($validated['image_urls'])) {
            $sortOrder = 0;
            foreach ($validated['image_urls'] as $index => $imageUrl) {
                try {
                    $imageContent = Http::timeout(10)->get($imageUrl)->body();
                    
                    if ($imageContent) {
                        // Extract extension or default to jpg
                        $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION);
                        if (!$extension || !in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                            $extension = 'jpg';
                        }
                        
                        $filename = 'product-' . $product->id . '-' . Str::random(10) . '.' . $extension;
                        $path = 'media/' . $filename;
                        
                        Storage::disk('public')->put($path, $imageContent);
                        
                        $media = Media::create([
                            'filename' => $filename,
                            'file_path' => $path,
                            'file_type' => 'image/' . ($extension == 'jpg' ? 'jpeg' : $extension),
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
                    Log::error("Failed to download image for product API: " . $imageUrl . " - " . $e->getMessage());
                    // Continue with next image
                }
            }
        }

        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product->load('categories', 'thumbnail', 'gallery')
        ], 201);
    }
}
