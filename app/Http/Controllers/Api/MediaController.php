<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = (int) $request->input('limit', 24);
        $media = Media::latest()->paginate($limit);

        return response()->json([
            'total' => $media->total(),
            'media' => $media->items(),
            'current_page' => $media->currentPage(),
            'last_page' => $media->lastPage(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $media = Media::find($id);

        if (! $media) {
            return response()->json(['error' => 'Media not found'], 404);
        }

        return response()->json([
            'media' => $media,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        // 1. Upload from URL
        if ($request->filled('image_url')) {
            $url = $request->input('image_url');
            try {
                $content = Http::timeout(25)->get($url)->body();
                if (! $content) {
                    return response()->json(['error' => 'Failed to download image from URL.'], 422);
                }

                $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);
                if (! $ext || ! in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    $ext = 'png';
                }

                $filename = 'media-'.Str::random(12).'.'.$ext;
                $path = 'media/'.$filename;
                Storage::disk('public')->put($path, $content);

                $media = Media::create([
                    'filename' => $filename,
                    'file_path' => $path,
                    'file_type' => 'image/'.($ext === 'jpg' ? 'jpeg' : $ext),
                    'file_size' => strlen($content),
                ]);

                return response()->json([
                    'message' => 'Media uploaded successfully from URL',
                    'media' => $media,
                ], 201);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Error fetching image: '.$e->getMessage()], 500);
            }
        }

        // 2. Upload from file input
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time().'_'.Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)).'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('media', $filename, 'public');

            $media = Media::create([
                'filename' => $filename,
                'file_path' => $path,
                'file_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);

            return response()->json([
                'message' => 'Media uploaded successfully',
                'media' => $media,
            ], 201);
        }

        return response()->json(['error' => 'Please provide an image file or image_url.'], 422);
    }

    public function destroy(int $id): JsonResponse
    {
        $media = Media::find($id);

        if (! $media) {
            return response()->json(['error' => 'Media not found'], 404);
        }

        if (Storage::disk('public')->exists($media->file_path)) {
            Storage::disk('public')->delete($media->file_path);
        }

        $media->delete();

        return response()->json([
            'message' => 'Media deleted successfully',
        ]);
    }
}
