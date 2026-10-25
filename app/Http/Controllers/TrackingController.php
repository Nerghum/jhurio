<?php

namespace App\Http\Controllers;

use App\Models\TrackingEvent;
use App\Models\TrackingSession;
use App\Models\TrackingVisitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Jenssegers\Agent\Agent;

class TrackingController extends Controller
{
    public function track(Request $request)
    {
        $payload = $request->input('payload');

        if (! $payload || ! is_array($payload)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid payload'], 400);
        }

        $events = isset($payload['events']) ? $payload['events'] : [$payload];

        $visitorId = $request->input('visitor_id');
        $sessionId = $request->input('session_id');

        $clientIp = $request->header('CF-Connecting-IP')
            ?? ($request->header('X-Forwarded-For') ? trim(explode(',', $request->header('X-Forwarded-For'))[0]) : null)
            ?? $request->ip();
        $userAgent = $request->userAgent();

        // Resolve Visitor
        $visitor = TrackingVisitor::where('visitor_id', $visitorId)->first();

        if (! $visitor) {
            $agent = new Agent;
            $agent->setUserAgent($userAgent);

            $visitor = TrackingVisitor::create([
                'visitor_id' => $visitorId ?: (string) Str::uuid(),
                'ip_address' => $clientIp,
                'user_agent' => $userAgent,
                'first_seen_at' => now(),
                'last_seen_at' => now(),
                'device' => $this->getDeviceType($agent),
                'browser' => $agent->browser(),
                'os' => $agent->platform(),
                'screen_size' => $request->input('screen_size'),
                'language' => $request->input('language'),
                'first_source' => $request->input('url'),
            ]);

            // Resolve location async or inline using caching
            $this->resolveLocation($visitor, $clientIp);
        } else {
            $visitorUpdates = ['last_seen_at' => now()];
            if (empty($visitor->ip_address) && $clientIp) {
                $visitorUpdates['ip_address'] = $clientIp;
            }
            if (empty($visitor->user_agent) && $userAgent) {
                $visitorUpdates['user_agent'] = $userAgent;
            }
            if (empty($visitor->country) && $clientIp && $clientIp !== '127.0.0.1' && $clientIp !== '::1') {
                $this->resolveLocation($visitor, $clientIp);
            }
            $visitor->update($visitorUpdates);
        }

        // Resolve Session
        $session = TrackingSession::where('session_id', $sessionId)->first();

        if (! $session) {
            $url = $request->input('url');
            $referrer = $request->input('referrer');
            $utm = $this->extractUtms($url);

            $session = TrackingSession::create([
                'session_id' => $sessionId ?: (string) Str::uuid(),
                'tracking_visitor_id' => $visitor->visitor_id,
                'user_id' => auth()->id(),
                'started_at' => now(),
                'ended_at' => now(),
                'landing_page' => $url,
                'exit_page' => $url,
                'pages_viewed' => 0,
                'traffic_source' => $this->determineTrafficSource($referrer, $utm),
                'referrer' => $referrer,
                'utm_source' => $utm['utm_source'] ?? null,
                'utm_medium' => $utm['utm_medium'] ?? null,
                'utm_campaign' => $utm['utm_campaign'] ?? null,
                'utm_content' => $utm['utm_content'] ?? null,
                'utm_term' => $utm['utm_term'] ?? null,
            ]);
        } else {
            $session->update([
                'ended_at' => now(),
                'duration' => now()->diffInSeconds($session->started_at),
                'exit_page' => $request->input('url'),
            ]);
        }

        // Record Events
        foreach ($events as $eventData) {
            $eventType = $eventData['event_type'] ?? 'page_view';

            if ($eventType === 'page_view') {
                $session->increment('pages_viewed');
            }

            TrackingEvent::create([
                'tracking_visitor_id' => $visitor->visitor_id,
                'tracking_session_id' => $session->session_id,
                'user_id' => auth()->id(),
                'event_type' => $eventType,
                'page_url' => $eventData['url'] ?? $request->input('url'),
                'product_id' => $eventData['product_id'] ?? null,
                'category_id' => $eventData['category_id'] ?? null,
                'search_keyword' => $eventData['search_keyword'] ?? null,
                'country' => $visitor->country,
                'city' => $visitor->city,
                'metadata' => $eventData['metadata'] ?? null,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'visitor_id' => $visitor->visitor_id,
            'session_id' => $session->session_id,
        ]);
    }

    private function getDeviceType(Agent $agent)
    {
        if ($agent->isTablet()) {
            return 'Tablet';
        }
        if ($agent->isMobile()) {
            return 'Mobile';
        }

        return 'Desktop';
    }

    private function determineTrafficSource($referrer, $utm)
    {
        if (isset($utm['utm_source'])) {
            return $utm['utm_source'];
        }

        if (empty($referrer)) {
            return 'Direct';
        }

        $host = parse_url($referrer, PHP_URL_HOST);

        if (str_contains($host, 'google.')) {
            return 'Google Organic';
        }
        if (str_contains($host, 'facebook.com')) {
            return 'Facebook';
        }
        if (str_contains($host, 'instagram.com')) {
            return 'Instagram';
        }
        if (str_contains($host, 'tiktok.com')) {
            return 'TikTok';
        }
        if (str_contains($host, 'youtube.com')) {
            return 'YouTube';
        }

        return 'Referral';
    }

    private function extractUtms($url)
    {
        $parsed = parse_url($url);
        $utms = [];

        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $query);
            $keys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];
            foreach ($keys as $key) {
                if (isset($query[$key])) {
                    $utms[$key] = $query[$key];
                }
            }
        }

        return $utms;
    }

    private function resolveLocation(TrackingVisitor $visitor, $ip)
    {
        // Don't resolve localhost
        if ($ip === '127.0.0.1' || $ip === '::1') {
            return;
        }

        // Cache IP resolution to save API calls
        $location = Cache::remember("ip_location_{$ip}", now()->addMonth(), function () use ($ip) {
            try {
                $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}");
                if ($response->successful() && $response['status'] === 'success') {
                    return [
                        'country' => $response['country'],
                        'division' => $response['regionName'],
                        'city' => $response['city'],
                        'lat' => $response['lat'] ?? null,
                        'lon' => $response['lon'] ?? null,
                    ];
                }
            } catch (\Exception $e) {
                // Ignore timeout/errors
            }

            return null;
        });

        if ($location) {
            $visitor->update([
                'country' => $location['country'],
                'division' => $location['division'],
                'city' => $location['city'],
                'lat' => $location['lat'],
                'lon' => $location['lon'],
            ]);
        }
    }
}
