<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::withCount('products')->latest();

        if ($request->filled('search')) {
            $s = $request->input('search');
            $query->where('name', 'like', "%{$s}%")
                ->orWhere('slug', 'like', "%{$s}%");
        }

        $categories = $request->has('limit')
            ? $query->paginate((int) $request->input('limit'))
            : $query->get();

        return response()->json([
            'total' => is_countable($categories) ? count($categories) : $categories->total(),
            'categories' => $categories,
        ]);
    }

    public function show(string|int $identifier): JsonResponse
    {
        $category = is_numeric($identifier)
            ? Category::withCount('products')->find($identifier)
            : Category::withCount('products')->where('slug', $identifier)->first();

        if (! $category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        return response()->json([
            'category' => $category->load('media'),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'media_id' => 'nullable|exists:media,id',
            'fb_pixel_id' => 'nullable|string|max:255',
            'fb_capi_token' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        $slug = ! empty($validated['slug'])
            ? Str::slug($validated['slug'])
            : Str::slug($validated['name']);

        $originalSlug = $slug;
        $counter = 1;
        while (Category::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        $validated['slug'] = $slug;
        $validated['is_active'] = $validated['is_active'] ?? true;

        $category = Category::create($validated);

        return response()->json([
            'message' => 'Category created successfully',
            'category' => $category,
        ], 201);
    }

    public function update(Request $request, string|int $identifier): JsonResponse
    {
        $category = is_numeric($identifier)
            ? Category::find($identifier)
            : Category::where('slug', $identifier)->first();

        if (! $category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'media_id' => 'nullable|exists:media,id',
            'fb_pixel_id' => 'nullable|string|max:255',
            'fb_capi_token' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);

        if (! empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['slug']);
        }

        $category->update(array_filter($validated, fn ($v) => ! is_null($v)));

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category->fresh(),
        ]);
    }

    public function destroy(string|int $identifier): JsonResponse
    {
        $category = is_numeric($identifier)
            ? Category::find($identifier)
            : Category::where('slug', $identifier)->first();

        if (! $category) {
            return response()->json(['error' => 'Category not found'], 404);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }
}
