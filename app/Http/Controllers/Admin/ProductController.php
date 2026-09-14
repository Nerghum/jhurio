<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
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
            'compare_at_price' => 'nullable|numeric|min:0|gt:price',
            'status' => 'required|in:published,draft',
            'stock_quantity' => 'required|integer|min:0',
            'thumbnail_media_id' => 'nullable|exists:media,id',
            'video_url' => 'nullable|url',
            'tags_json' => 'nullable|string',
            'options' => 'nullable|json',
        ]);

        $productData = $request->except(['categories', 'tags_json']);
        if ($request->has('options')) {
            $productData['options'] = json_decode($request->options, true);
        }

        $product = Product::create($productData);

        $product->categories()->sync($request->categories);

        // Process tags
        $this->syncDynamicTags($product, $request->tags_json);

        // Handle gallery
        if ($request->has('gallery')) {
            $galleryData = [];
            foreach (explode(',', $request->gallery) as $index => $mediaId) {
                if(trim($mediaId) !== "") {
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
        $product->load('gallery', 'variations', 'thumbnail', 'tags', 'categories');
        return view('admin.products.edit', compact('product', 'categories', 'tags'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|unique:products,slug,' . $product->id,
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'compare_at_price' => 'nullable|numeric|min:0|gt:price',
            'status' => 'required|in:published,draft',
            'stock_quantity' => 'required|integer|min:0',
            'thumbnail_media_id' => 'nullable|exists:media,id',
            'video_url' => 'nullable|url',
            'tags_json' => 'nullable|string',
            'options' => 'nullable|json',
        ]);

        $productData = $request->except(['categories', 'tags_json']);
        if ($request->has('options')) {
            $productData['options'] = json_decode($request->options, true);
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
                if(trim($mediaId) !== "") {
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
        if (!is_array($tagNames)) {
            $product->tags()->detach();
            return;
        }

        $tagIds = [];
        foreach ($tagNames as $tagName) {
            $tagName = trim($tagName);
            if (!empty($tagName)) {
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
}
