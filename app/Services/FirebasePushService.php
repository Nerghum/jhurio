<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FirebasePushService
{
    /**
     * Send a notification using FCM HTTP v1 API.
     */
    public static function send(string $token, string $title, string $body, array $data = [])
    {
        try {
            $serviceAccountJson = Setting::get('firebase_service_account');
            if (! $serviceAccountJson) {
                return false;
            }

            $credentials = json_decode($serviceAccountJson, true);
            if (! $credentials || ! isset($credentials['project_id']) || ! isset($credentials['private_key']) || ! isset($credentials['client_email'])) {
                Log::error('FirebasePushService: Invalid service account JSON.');

                return false;
            }

            $projectId = $credentials['project_id'];
            $accessToken = self::getAccessToken($credentials);

            if (! $accessToken) {
                return false;
            }

            $response = Http::withToken($accessToken)
                ->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body' => $body,
                        ],
                        'data' => array_merge($data, ['click_action' => 'FLUTTER_NOTIFICATION_CLICK']),
                    ],
                ]);

            if ($response->failed()) {
                Log::error('FirebasePushService send failed: '.$response->body());

                return false;
            }

            return true;

        } catch (\Exception $e) {
            Log::error('FirebasePushService Error: '.$e->getMessage());

            return false;
        }
    }

    /**
     * Generate an OAuth2 Bearer token using a Service Account JSON.
     */
    private static function getAccessToken(array $credentials)
    {
        // For performance, you should cache this token using Cache::remember for ~50 minutes
        return cache()->remember('firebase_access_token', 3000, function () use ($credentials) {
            $header = self::base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));

            $now = time();
            $payload = self::base64UrlEncode(json_encode([
                'iss' => $credentials['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud' => 'https://oauth2.googleapis.com/token',
                'iat' => $now,
                'exp' => $now + 3600,
            ]));

            $signatureInput = $header.'.'.$payload;
            $signature = '';

            if (! openssl_sign($signatureInput, $signature, $credentials['private_key'], 'sha256')) {
                Log::error('FirebasePushService: Failed to sign JWT.');

                return null;
            }

            $jwt = $signatureInput.'.'.self::base64UrlEncode($signature);

            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $jwt,
            ]);

            if ($response->successful() && $token = $response->json('access_token')) {
                return $token;
            }

            Log::error('FirebasePushService: Failed to fetch access token. '.$response->body());

            return null;
        });
    }

    private static function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }
}
