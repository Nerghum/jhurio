<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\HomepageSection;
use App\Models\Category;

class HomepageBuilderController extends Controller
{
    public function index()
    {
        $sections = HomepageSection::orderBy('order')->get();
        $categories = Category::orderBy('name')->get();
        
        return view('admin.homepage_builder.index', compact('sections', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:hero,category_grid,flash_sale,custom_products,image_text,faq',
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'data' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        $maxOrder = HomepageSection::max('order') ?? 0;
        $validated['order'] = $maxOrder + 1;
        $validated['is_active'] = $request->has('is_active');

        HomepageSection::create($validated);

        return redirect()->back()->with('success', 'Section added successfully.');
    }

    public function update(Request $request, HomepageSection $homepage_section)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'data' => 'nullable|array',
            'is_active' => 'boolean'
        ]);

        $validated['is_active'] = $request->has('is_active');

        $homepage_section->update($validated);

        return redirect()->back()->with('success', 'Section updated successfully.');
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*.id' => 'required|exists:homepage_sections,id',
            'orders.*.order' => 'required|integer',
        ]);

        foreach ($request->orders as $orderData) {
            HomepageSection::where('id', $orderData['id'])->update(['order' => $orderData['order']]);
        }

        return response()->json(['success' => true]);
    }

    public function destroy(HomepageSection $homepage_section)
    {
        $homepage_section->delete();
        return redirect()->back()->with('success', 'Section deleted successfully.');
    }
}
