<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    public function index()
    {
        $banners = Banner::with('media')->orderBy('order')->get();
        return view('admin.banners.index', compact('banners'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'type' => 'required|in:slider,right_top,right_bottom,bottom_strip',
            'media_id' => 'required|exists:media,id',
            'link_url' => 'nullable|url|max:255',
            'is_active' => 'boolean',
            'order' => 'integer'
        ]);

        Banner::create($validated);
        return back()->with('success', 'Banner created successfully.');
    }

    public function update(Request $request, Banner $banner)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'type' => 'required|in:slider,right_top,right_bottom,bottom_strip',
            'media_id' => 'required|exists:media,id',
            'link_url' => 'nullable|url|max:255',
            'is_active' => 'boolean',
            'order' => 'integer'
        ]);

        $banner->update($validated);
        return back()->with('success', 'Banner updated successfully.');
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();
        return back()->with('success', 'Banner deleted successfully.');
    }
}
