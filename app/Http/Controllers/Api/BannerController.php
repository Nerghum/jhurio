<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Banner::with('media')->orderBy('order', 'asc');

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->has('is_active')) {
            $query->where('is_active', filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN));
        }

        $banners = $query->get();

        return response()->json([
            'total' => $banners->count(),
            'banners' => $banners,
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $banner = Banner::with('media')->find($id);

        if (! $banner) {
            return response()->json(['error' => 'Banner not found'], 404);
        }

        return response()->json([
            'banner' => $banner,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'type' => 'required|in:slider,right_top,right_bottom,bottom_strip',
            'media_id' => 'nullable|exists:media,id',
            'image_url' => 'nullable|url',
            'link_url' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        $mediaId = $validated['media_id'] ?? null;

        if (! $mediaId && ! empty($validated['image_url'])) {
            $mediaId = $this->createMediaFromUrl($validated['image_url']);
        }

        if (! $mediaId) {
            return response()->json([
                'error' => 'Either media_id or image_url must be provided.',
            ], 422);
        }

        $banner = Banner::create([
            'title' => $validated['title'] ?? null,
            'type' => $validated['type'],
            'media_id' => $mediaId,
            'link_url' => $validated['link_url'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'order' => $validated['order'] ?? (Banner::where('type', $validated['type'])->max('order') + 1),
        ]);

        return response()->json([
            'message' => 'Banner created successfully',
            'banner' => $banner->load('media'),
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $banner = Banner::find($id);

        if (! $banner) {
            return response()->json(['error' => 'Banner not found'], 404);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'type' => 'nullable|in:slider,right_top,right_bottom,bottom_strip',
            'media_id' => 'nullable|exists:media,id',
            'image_url' => 'nullable|url',
            'link_url' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'order' => 'nullable|integer',
        ]);

        if (! empty($validated['image_url'])) {
            $validated['media_id'] = $this->createMediaFromUrl($validated['image_url']);
            unset($validated['image_url']);
        }

        $banner->update(array_filter($validated, fn ($v) => ! is_null($v)));

        return response()->json([
            'message' => 'Banner updated successfully',
            'banner' => $banner->fresh()->load('media'),
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $banner = Banner::find($id);

        if (! $banner) {
            return response()->json(['error' => 'Banner not found'], 404);
        }

        $banner->delete();

        return response()->json([
            'message' => 'Banner deleted successfully',
        ]);
    }

    protected function createMediaFromUrl(string $url): ?int
    {
        try {
            $content = Http::timeout(20)->get($url)->body();
            if (! $content) {
                return null;
            }

            $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
            if (! $ext || ! in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                $ext = 'png';
            }

            $filename = 'banner-'.Str::random(12).'.'.$ext;
            $path = 'media/'.$filename;
            Storage::disk('public')->put($path, $content);

            $media = Media::create([
                'filename' => $filename,
                'file_path' => $path,
                'file_type' => 'image/'.($ext === 'jpg' ? 'jpeg' : $ext),
                'file_size' => strlen($content),
            ]);

            return $media->id;
        } catch (\Exception $e) {
            return null;
        }
    }
}
