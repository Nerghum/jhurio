<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Media;
use App\Models\Product;
use App\Models\ProductVariation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $media = Media::latest()->get();

        if ($request->wantsJson()) {
            return response()->json($media);
        }

        return view('admin.media.index', compact('media'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        $path = $file->store('media', 'public');

        $media = Media::create([
            'filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        if ($request->wantsJson()) {
            return response()->json($media);
        }

        return back()->with('success', 'File uploaded successfully.');
    }

    public function replace(Request $request, Media $media)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        if (Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }

        $file = $request->file('file');
        $path = $file->store('media', 'public');

        $media->update([
            'filename' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ]);

        return back()->with('success', 'Image replaced successfully.');
    }

    public function destroy(Media $media)
    {
        // Unset nullable foreign keys
        Product::where('thumbnail_media_id', $media->id)->update(['thumbnail_media_id' => null]);
        ProductVariation::where('media_id', $media->id)->update(['media_id' => null]);
        Category::where('media_id', $media->id)->update(['media_id' => null]);

        // Note: banners and product_media will automatically cascade on delete via database constraints

        if (Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }

        $media->delete();

        return back()->with('success', 'Image deleted from all places.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:media,id',
        ]);

        $ids = $request->ids;

        // Unset nullable foreign keys
        Product::whereIn('thumbnail_media_id', $ids)->update(['thumbnail_media_id' => null]);
        ProductVariation::whereIn('media_id', $ids)->update(['media_id' => null]);
        Category::whereIn('media_id', $ids)->update(['media_id' => null]);

        $medias = Media::whereIn('id', $ids)->get();

        foreach ($medias as $media) {
            if (Storage::disk('public')->exists($media->file_path)) {
                Storage::disk('public')->delete($media->file_path);
            }
            $media->delete();
        }

        return response()->json(['success' => true, 'message' => count($ids).' items deleted successfully.']);
    }
}
