<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HomepageSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomepageSectionController extends Controller
{
    public function index(): JsonResponse
    {
        $sections = HomepageSection::orderBy('order', 'asc')->get();

        return response()->json([
            'total' => $sections->count(),
            'sections' => $sections,
        ]);
    }

    public function show(string|int $identifier): JsonResponse
    {
        $section = is_numeric($identifier)
            ? HomepageSection::find($identifier)
            : HomepageSection::where('type', $identifier)->first();

        if (! $section) {
            return response()->json(['error' => 'Homepage section not found'], 404);
        }

        return response()->json([
            'section' => $section,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => 'required|in:hero,category_grid,flash_sale,custom_products,image_text,faq',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'data' => 'nullable|array',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $maxOrder = HomepageSection::max('order') ?? 0;
        $validated['order'] = $validated['order'] ?? ($maxOrder + 1);
        $validated['is_active'] = $validated['is_active'] ?? true;

        $section = HomepageSection::create($validated);

        return response()->json([
            'message' => 'Homepage section created successfully',
            'section' => $section,
        ], 201);
    }

    public function update(Request $request, string|int $identifier): JsonResponse
    {
        $section = is_numeric($identifier)
            ? HomepageSection::find($identifier)
            : HomepageSection::where('type', $identifier)->first();

        if (! $section) {
            return response()->json(['error' => 'Homepage section not found'], 404);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'data' => 'nullable|array',
            'order' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        if (isset($validated['data']) && is_array($validated['data']) && is_array($section->data)) {
            // Deep merge data if requested or replace
            if ($request->boolean('merge_data', true)) {
                $validated['data'] = array_replace_recursive($section->data, $validated['data']);
            }
        }

        $section->update(array_filter($validated, fn ($v) => ! is_null($v)));

        return response()->json([
            'message' => 'Homepage section updated successfully',
            'section' => $section->fresh(),
        ]);
    }

    public function updateHero(Request $request): JsonResponse
    {
        $section = HomepageSection::firstOrCreate(
            ['type' => 'hero'],
            ['order' => 1, 'is_active' => true, 'data' => []]
        );

        $data = $section->data ?? [];
        $hero = $data['hero'] ?? [];

        if ($request->has('slider_image')) {
            $hero['slider']['image'] = $request->input('slider_image');
        }
        if ($request->has('slider_link')) {
            $hero['slider']['link'] = $request->input('slider_link');
        }
        if ($request->has('right_top_image')) {
            $hero['right_top']['image'] = $request->input('right_top_image');
        }
        if ($request->has('right_top_link')) {
            $hero['right_top']['link'] = $request->input('right_top_link');
        }
        if ($request->has('right_bottom_image')) {
            $hero['right_bottom']['image'] = $request->input('right_bottom_image');
        }
        if ($request->has('right_bottom_link')) {
            $hero['right_bottom']['link'] = $request->input('right_bottom_link');
        }
        if ($request->has('bottom_strip_image')) {
            $hero['bottom_strip']['image'] = $request->input('bottom_strip_image');
        }
        if ($request->has('bottom_strip_link')) {
            $hero['bottom_strip']['link'] = $request->input('bottom_strip_link');
        }

        $data['hero'] = $hero;
        $section->update(['data' => $data]);

        return response()->json([
            'message' => 'Hero section banners updated successfully',
            'hero' => $hero,
            'section' => $section->fresh(),
        ]);
    }

    public function destroy(string|int $identifier): JsonResponse
    {
        $section = is_numeric($identifier)
            ? HomepageSection::find($identifier)
            : HomepageSection::where('type', $identifier)->first();

        if (! $section) {
            return response()->json(['error' => 'Homepage section not found'], 404);
        }

        $section->delete();

        return response()->json([
            'message' => 'Homepage section deleted successfully',
        ]);
    }
}
