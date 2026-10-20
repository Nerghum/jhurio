<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FacebookCAPIService
{
    /**
     * Send an event to Facebook Conversions API
     *
     * @param  array  $additionalPixels  Array of associative arrays with 'pixel_id' and 'capi_token'
     * @return void
     */
    public static function sendEvent(string $eventName, array $customData = [], array $userData = [], ?string $eventId = null, array $additionalPixels = [])
    {
        $fbPixelsStr = Setting::get('fb_pixels', '[]');
        $fbPixels = json_decode($fbPixelsStr, true);

        if (! is_array($fbPixels)) {
            $fbPixels = [];
        }

        // Merge additional pixels
        foreach ($additionalPixels as $additional) {
            if (! empty($additional['pixel_id']) && ! empty($additional['capi_token'])) {
                // Check for duplicates
                $exists = false;
                foreach ($fbPixels as $fp) {
                    if (isset($fp['pixel_id']) && $fp['pixel_id'] === $additional['pixel_id']) {
                        $exists = true;
                        break;
                    }
                }
                if (! $exists) {
                    $fbPixels[] = $additional;
                }
            }
        }

        if (empty($fbPixels)) {
            return;
        }

        // Add standard user data fields if missing (Client IP, User Agent)
        $userData = array_merge([
            'client_ip_address' => request()->ip(),
            'client_user_agent' => request()->userAgent(),
        ], $userData);

        // Remove empty user data to avoid CAPI errors
        $userData = array_filter($userData);

        $eventData = [
            'event_name' => $eventName,
            'event_time' => time(),
            'action_source' => 'website',
            'user_data' => $userData,
            'custom_data' => $customData,
            'event_source_url' => request()->fullUrl(),
        ];

        if ($eventId) {
            $eventData['event_id'] = $eventId;
        }

        foreach ($fbPixels as $pixel) {
            if (empty($pixel['pixel_id']) || empty($pixel['capi_token'])) {
                continue;
            }

            $pixelId = trim($pixel['pixel_id']);
            $token = trim($pixel['capi_token']);
            $testCode = isset($pixel['test_code']) ? trim($pixel['test_code']) : null;

            $payload = [
                'data' => [$eventData],
            ];

            if (! empty($testCode)) {
                $payload['test_event_code'] = $testCode;
            }

            try {
                $url = "https://graph.facebook.com/v19.0/{$pixelId}/events?access_token={$token}";
                $response = Http::post($url, $payload);

                if (! $response->successful()) {
                    Log::error("Facebook CAPI Error for Pixel {$pixelId}: ".$response->body());
                }
            } catch (\Exception $e) {
                Log::error("Facebook CAPI Exception for Pixel {$pixelId}: ".$e->getMessage());
            }
        }
    }
}
