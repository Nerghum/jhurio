<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Setting::query();

        if ($request->filled('keys')) {
            $keys = array_map('trim', explode(',', $request->input('keys')));
            $query->whereIn('key', $keys);
        }

        $settings = $query->pluck('value', 'key');

        return response()->json([
            'settings' => $settings,
        ]);
    }

    public function show(string $key): JsonResponse
    {
        $setting = Setting::where('key', $key)->first();

        if (! $setting) {
            return response()->json(['error' => 'Setting not found'], 404);
        }

        return response()->json([
            'key' => $setting->key,
            'value' => $setting->value,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $settingsData = $request->input('settings', $request->all());

        if (! is_array($settingsData) || empty($settingsData)) {
            return response()->json(['error' => 'No settings provided to update'], 422);
        }

        $updated = [];
        foreach ($settingsData as $key => $val) {
            if ($key === 'api_token' || $key === '_token') {
                continue; // Prevent accidental lockout
            }

            $stringVal = is_array($val) || is_object($val) ? json_encode($val) : (string) $val;
            Setting::set($key, $stringVal);
            $updated[$key] = $val;
        }

        return response()->json([
            'message' => count($updated).' settings updated successfully',
            'updated' => $updated,
        ]);
    }
}
