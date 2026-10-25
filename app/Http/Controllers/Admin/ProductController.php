<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('categories')->latest()->paginate(10);

        return view('admin.products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        $tags = Tag::all();

        return view('admin.products.create', compact('categories', 'tags'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products,slug',
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0|gt:price',
            'status' => 'required|in:published,draft',
            'stock_quantity' => 'required|integer|min:0',
            'thumbnail_media_id' => 'nullable|exists:media,id',
            'video_url' => 'nullable|url',
            'tags_json' => 'nullable|string',
            'options' => 'nullable|json',
            'bundle_settings' => 'nullable|string',
            'image_text_sections' => 'nullable',
            'video_reels' => 'nullable',
        ]);

        $productData = $request->except(['categories', 'tags_json']);
        if ($request->has('options')) {
            $productData['options'] = json_decode($request->options, true);
        }
        if ($request->filled('bundle_settings')) {
            $productData['bundle_settings'] = is_array($request->bundle_settings)
                ? $request->bundle_settings
                : json_decode($request->bundle_settings, true);
        } else {
            $productData['bundle_settings'] = null;
        }
        if ($request->filled('image_text_sections')) {
            $productData['image_text_sections'] = is_array($request->image_text_sections)
                ? $request->image_text_sections
                : json_decode($request->image_text_sections, true);
        } else {
            $productData['image_text_sections'] = null;
        }
        if ($request->filled('video_reels')) {
            $productData['video_reels'] = is_array($request->video_reels)
                ? $request->video_reels
                : json_decode($request->video_reels, true);
        } else {
            $productData['video_reels'] = null;
        }

        $product = Product::create($productData);

        $product->categories()->sync($request->categories);

        // Process tags
        $this->syncDynamicTags($product, $request->tags_json);

        // Handle gallery
        if ($request->has('gallery')) {
            $galleryData = [];
            foreach (explode(',', $request->gallery) as $index => $mediaId) {
                if (trim($mediaId) !== '') {
                    $galleryData[$mediaId] = ['sort_order' => $index];
                }
            }
            $product->gallery()->sync($galleryData);
        }

        // Save Variations
        if ($request->has('variations_json')) {
            $variations = json_decode($request->variations_json, true);
            if (is_array($variations)) {
                foreach ($variations as $var) {
                    $product->variations()->create([
                        'name' => $var['name'] ?? null,
                        'options' => $var['options'] ?? null,
                        'price' => $var['price'] ?? null,
                        'purchase_price' => $var['purchase_price'] ?? null,
                        'stock_quantity' => $var['stock_quantity'] ?? 0,
                        'media_id' => $var['media_id'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product created successfully');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $tags = Tag::all();
        $product->load('gallery', 'variations', 'thumbnail', 'tags', 'categories', 'reviews.user');

        return view('admin.products.edit', compact('product', 'categories', 'tags'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products,slug,'.$product->id,
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0|gt:price',
            'status' => 'required|in:published,draft',
            'stock_quantity' => 'required|integer|min:0',
            'thumbnail_media_id' => 'nullable|exists:media,id',
            'video_url' => 'nullable|url',
            'tags_json' => 'nullable|string',
            'options' => 'nullable|json',
            'bundle_settings' => 'nullable|string',
            'image_text_sections' => 'nullable',
            'video_reels' => 'nullable',
        ]);

        $productData = $request->except(['categories', 'tags_json']);
        if ($request->has('options')) {
            $productData['options'] = json_decode($request->options, true);
        }
        if ($request->filled('bundle_settings')) {
            $productData['bundle_settings'] = is_array($request->bundle_settings)
                ? $request->bundle_settings
                : json_decode($request->bundle_settings, true);
        } else {
            $productData['bundle_settings'] = null;
        }
        if ($request->filled('image_text_sections')) {
            $productData['image_text_sections'] = is_array($request->image_text_sections)
                ? $request->image_text_sections
                : json_decode($request->image_text_sections, true);
        } else {
            $productData['image_text_sections'] = null;
        }
        if ($request->filled('video_reels')) {
            $productData['video_reels'] = is_array($request->video_reels)
                ? $request->video_reels
                : json_decode($request->video_reels, true);
        } else {
            $productData['video_reels'] = null;
        }

        $product->update($productData);

        // Sync Categories
        $product->categories()->sync($request->categories);

        // Sync Tags Dynamically
        $this->syncDynamicTags($product, $request->tags_json);

        // Sync Gallery
        if ($request->has('gallery')) {
            $galleryData = [];
            foreach (explode(',', $request->gallery) as $index => $mediaId) {
                if (trim($mediaId) !== '') {
                    $galleryData[$mediaId] = ['sort_order' => $index];
                }
            }
            $product->gallery()->sync($galleryData);
        } else {
            $product->gallery()->detach();
        }

        // Save Variations
        $product->variations()->delete();
        if ($request->has('variations_json')) {
            $variations = json_decode($request->variations_json, true);
            if (is_array($variations)) {
                foreach ($variations as $var) {
                    $product->variations()->create([
                        'name' => $var['name'] ?? null,
                        'options' => $var['options'] ?? null,
                        'price' => $var['price'] ?? null,
                        'purchase_price' => $var['purchase_price'] ?? null,
                        'stock_quantity' => $var['stock_quantity'] ?? 0,
                        'media_id' => $var['media_id'] ?? null,
                    ]);
                }
            }
        }

        return redirect()->route('admin.products.index')->with('success', 'Product updated successfully.');
    }

    private function syncDynamicTags(Product $product, $tagsJson)
    {
        if (empty($tagsJson)) {
            $product->tags()->detach();

            return;
        }

        $tagNames = json_decode($tagsJson, true);
        if (! is_array($tagNames)) {
            $product->tags()->detach();

            return;
        }

        $tagIds = [];
        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if (! empty($tagName)) {
                $tag = Tag::firstOrCreate(
                    ['name' => $tagName],
                    ['slug' => Str::slug($tagName)]
                );
                $tagIds[] = $tag->id;
            }
        }

        $product->tags()->sync($tagIds);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Product deleted successfully.');
    }

    public function bulkAction(Request $request)
    {
        $validated = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
            'action' => 'required|in:published,draft,delete',
        ]);

        $count = count($validated['product_ids']);

        if ($validated['action'] === 'published') {
            Product::whereIn('id', $validated['product_ids'])->update(['status' => 'published']);

            return back()->with('success', "{$count} product(s) successfully marked as Live (Published).");
        }

        if ($validated['action'] === 'draft') {
            Product::whereIn('id', $validated['product_ids'])->update(['status' => 'draft']);

            return back()->with('success', "{$count} product(s) successfully marked as Draft.");
        }

        if ($validated['action'] === 'delete') {
            Product::whereIn('id', $validated['product_ids'])->delete();

            return back()->with('success', "{$count} product(s) successfully deleted.");
        }

        return back();
    }
}
