<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\TrackingEvent;
use App\Models\TrackingSession;
use App\Models\TrackingVisitor;
use App\Services\GeoIpService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function dashboard(Request $request)
    {
        return view('admin.analytics.dashboard');
    }

    public function liveData(Request $request)
    {
        $now = now();
        $today = now()->startOfDay();

        // 1. Live Online Users (active in the last 5 minutes)
        $liveUsersCount = TrackingVisitor::where('last_seen_at', '>=', $now->copy()->subMinutes(5))->count();

        // 2. Today's metrics
        $todayVisitors = TrackingVisitor::where('last_seen_at', '>=', $today)->count();
        $todayNewVisitors = TrackingVisitor::where('first_seen_at', '>=', $today)->count();
        $todayReturning = max(0, $todayVisitors - $todayNewVisitors);

        $todaySessions = TrackingSession::where('started_at', '>=', $today)->count();
        $todayPageViews = TrackingEvent::where('created_at', '>=', $today)->where('event_type', 'page_view')->count();
        $todayProductViews = TrackingEvent::where('created_at', '>=', $today)->where('event_type', 'product_view')->count();
        $todayAddCart = TrackingEvent::where('created_at', '>=', $today)->where('event_type', 'add_to_cart')->count();
        $todayCheckout = TrackingEvent::where('created_at', '>=', $today)->where('event_type', 'checkout_start')->count();
        $todayPurchases = TrackingEvent::where('created_at', '>=', $today)->where('event_type', 'purchase')->count();

        // Helper to format visitor details with IP, location, device, and active status
        $formatVisitor = function (TrackingVisitor $visitor) use ($now) {
            $latestSession = $visitor->sessions->first();
            $latestEvent = $visitor->events->first();
            $totalSessions = $visitor->sessions()->count();
            $totalEvents = $visitor->events()->where('event_type', '!=', 'heartbeat')->count();

            $secondsAgo = $visitor->last_seen_at ? max(0, $now->timestamp - $visitor->last_seen_at->timestamp) : null;
            $isActiveNow = ($secondsAgo !== null && $secondsAgo <= 180); // Active within 3 minutes

            if ($secondsAgo !== null && $secondsAgo < 30) {
                $lastSeenHuman = 'Active Just Now';
            } elseif ($secondsAgo !== null && $secondsAgo < 60) {
                $lastSeenHuman = $secondsAgo.'s ago';
            } elseif ($secondsAgo !== null && $secondsAgo < 3600) {
                $lastSeenHuman = floor($secondsAgo / 60).'m ago';
            } else {
                $lastSeenHuman = $visitor->last_seen_at ? $visitor->last_seen_at->diffForHumans() : 'Unknown';
            }

            // Ensure location is resolved if empty and not localhost
            $country = $visitor->country;
            $city = $visitor->city;
            $division = $visitor->division;
            $lat = $visitor->lat;
            $lon = $visitor->lon;
            $flag = GeoIpService::getFlag(null, $country);

            if (empty($country) && $visitor->ip_address) {
                $geo = GeoIpService::resolve($visitor->ip_address);
                if (! empty($geo['country']) && $geo['country'] !== 'Unknown') {
                    $country = $geo['country'];
                    $city = $geo['city'] ?: $city;
                    $division = $geo['division'] ?: $division;
                    $flag = $geo['flag'];
                    $lat = $geo['lat'] ?? $lat;
                    $lon = $geo['lon'] ?? $lon;
                    $visitor->update([
                        'country' => $country,
                        'city' => $city,
                        'division' => $division,
                        'lat' => $lat,
                        'lon' => $lon,
                    ]);
                } elseif ($geo['is_local'] ?? false) {
                    $country = 'Localhost';
                    $city = 'Local Network';
                    $flag = '💻';
                }
            }

            $locationDisplay = trim(($city ? $city.', ' : '').($country ?: 'Unknown Location'), ', ');
            $deviceType = ucfirst(strtolower($visitor->device ?: 'Desktop'));
            $deviceIcon = match (strtolower($visitor->device ?: '')) {
                'mobile' => 'smartphone',
                'tablet' => 'tablet',
                default => 'monitor'
            };

            $currentPageUrl = $latestEvent ? $latestEvent->page_url : ($latestSession ? $latestSession->exit_page : $visitor->first_source);
            $currentPageTitle = GeoIpService::formatPageTitle($currentPageUrl);

            // Calculate duration
            $durationSeconds = $latestSession ? (int) $latestSession->duration : 0;
            if ($durationSeconds >= 60) {
                $durationHuman = floor($durationSeconds / 60).'m '.($durationSeconds % 60).'s';
            } else {
                $durationHuman = $durationSeconds.'s';
            }

            return [
                'id' => $visitor->id,
                'visitor_id' => $visitor->visitor_id,
                'ip_address' => $visitor->ip_address ?: 'Unknown IP',
                'country' => $country ?: 'Unknown',
                'division' => $division ?: '',
                'city' => $city ?: 'Unknown City',
                'location_display' => $locationDisplay,
                'flag' => $flag,
                'lat' => $lat ? round((float) $lat, 4) : null,
                'lon' => $lon ? round((float) $lon, 4) : null,
                'has_coordinates' => ! empty($lat) && ! empty($lon),
                'device' => $deviceType,
                'device_icon' => $deviceIcon,
                'browser' => $visitor->browser ?: 'Browser',
                'os' => $visitor->os ?: 'OS',
                'screen_size' => $visitor->screen_size ?: 'Standard',
                'language' => $visitor->language ?: 'en',
                'user_agent' => $visitor->user_agent ?: 'Standard User Agent',
                'first_seen_at' => $visitor->first_seen_at ? $visitor->first_seen_at->toIso8601String() : null,
                'first_seen_human' => $visitor->first_seen_at ? $visitor->first_seen_at->diffForHumans() : 'Unknown',
                'last_seen_at' => $visitor->last_seen_at ? $visitor->last_seen_at->toIso8601String() : null,
                'last_seen_human' => $lastSeenHuman,
                'seconds_ago' => $secondsAgo ?? 99999,
                'is_active_now' => $isActiveNow,
                'is_returning' => $totalSessions > 1,
                'total_sessions' => max(1, $totalSessions),
                'total_events' => $totalEvents,
                'current_page' => $currentPageUrl ?: '/',
                'current_page_title' => $currentPageTitle,
                'latest_event_type' => $latestEvent ? $latestEvent->event_type : 'page_view',
                'latest_product' => ($latestEvent && $latestEvent->product) ? $latestEvent->product->name : null,
                'traffic_source' => $latestSession ? ($latestSession->traffic_source ?: 'Direct') : 'Direct',
                'session_duration' => $durationHuman,
                'recent_events' => $visitor->events->map(function ($ev) {
                    return [
                        'id' => $ev->id,
                        'type' => $ev->event_type,
                        'url' => $ev->page_url,
                        'title' => GeoIpService::formatPageTitle($ev->page_url),
                        'product_name' => $ev->product ? $ev->product->name : null,
                        'time' => $ev->created_at ? $ev->created_at->diffForHumans() : 'Just now',
                    ];
                })->values(),
            ];
        };

        // 3. Live Visitors (Active in the last 10 minutes)
        $liveVisitors = TrackingVisitor::where('last_seen_at', '>=', $now->copy()->subMinutes(10))
            ->with([
                'sessions' => fn ($q) => $q->latest('started_at')->limit(1),
                'events' => fn ($q) => $q->where('event_type', '!=', 'heartbeat')->latest('created_at')->limit(6)->with('product'),
            ])
            ->orderByDesc('last_seen_at')
            ->limit(50)
            ->get();

        // 4. Recent Visitors (Today or up to latest 30 visitors)
        $recentVisitorsQuery = TrackingVisitor::where('last_seen_at', '>=', $today)
            ->with([
                'sessions' => fn ($q) => $q->latest('started_at')->limit(1),
                'events' => fn ($q) => $q->where('event_type', '!=', 'heartbeat')->latest('created_at')->limit(6)->with('product'),
            ])
            ->orderByDesc('last_seen_at')
            ->limit(30);

        $recentVisitors = $recentVisitorsQuery->get();

        // If today has very few visitors, pad with latest visitors for visibility
        if ($recentVisitors->count() < 10) {
            $recentVisitors = TrackingVisitor::with([
                'sessions' => fn ($q) => $q->latest('started_at')->limit(1),
                'events' => fn ($q) => $q->where('event_type', '!=', 'heartbeat')->latest('created_at')->limit(6)->with('product'),
            ])
                ->orderByDesc('last_seen_at')
                ->limit(20)
                ->get();
        }

        // 5. Live Feed (Last 25 meaningful events with IP and device info)
        $recentEvents = TrackingEvent::with(['visitor', 'product'])
            ->where('event_type', '!=', 'heartbeat')
            ->orderBy('created_at', 'desc')
            ->limit(25)
            ->get()
            ->map(function ($event) {
                $country = $event->visitor->country ?? 'Unknown';

                return [
                    'id' => $event->id,
                    'type' => $event->event_type,
                    'time' => $event->created_at ? $event->created_at->diffForHumans() : 'Just now',
                    'ip' => $event->visitor->ip_address ?? 'Unknown IP',
                    'city' => $event->visitor->city ?? 'Unknown',
                    'country' => $country,
                    'flag' => GeoIpService::getFlag(null, $country),
                    'device' => $event->visitor->device ?? 'Desktop',
                    'browser' => $event->visitor->browser ?? '',
                    'os' => $event->visitor->os ?? '',
                    'url' => $event->page_url,
                    'title' => GeoIpService::formatPageTitle($event->page_url),
                    'product_name' => $event->product ? $event->product->name : null,
                ];
            });

        return response()->json([
            'live_users' => $liveUsersCount,
            'live_visitors' => $liveVisitors->map($formatVisitor)->values(),
            'recent_visitors' => $recentVisitors->map($formatVisitor)->values(),
            'today_visitors' => $todayVisitors,
            'today_new' => $todayNewVisitors,
            'today_returning' => $todayReturning,
            'today_sessions' => $todaySessions,
            'today_pageviews' => $todayPageViews,
            'today_product_views' => $todayProductViews,
            'today_add_cart' => $todayAddCart,
            'today_checkout' => $todayCheckout,
            'today_purchases' => $todayPurchases,
            'feed' => $recentEvents,
        ]);
    }

    public function overview(Request $request)
    {
        $days = (int) $request->input('days', 30);
        $startDate = now()->subDays($days)->startOfDay();
        $endDate = now()->endOfDay();

        // Totals
        $totalVisitors = TrackingVisitor::whereBetween('first_seen_at', [$startDate, $endDate])->count();
        $totalSessions = TrackingSession::whereBetween('started_at', [$startDate, $endDate])->count();
        $totalPageviews = TrackingEvent::whereBetween('created_at', [$startDate, $endDate])->where('event_type', 'page_view')->count();
        $totalPurchases = TrackingEvent::whereBetween('created_at', [$startDate, $endDate])->where('event_type', 'purchase')->count();

        // Chart Data (Group by Date)
        // Grouping events for pageviews and visitors
        $chartDates = [];
        $pageviewData = [];
        $visitorData = [];

        // Pre-fill array with 0s to avoid missing days
        for ($i = $days; $i >= 0; $i--) {
            $dateStr = now()->subDays($i)->format('Y-m-d');
            $chartDates[] = now()->subDays($i)->format('M d');
            $pageviewData[$dateStr] = 0;
            $visitorData[$dateStr] = 0;
        }

        $eventsQuery = TrackingEvent::selectRaw('DATE(created_at) as date, count(*) as total')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->where('event_type', 'page_view')
            ->groupBy('date')
            ->get();

        foreach ($eventsQuery as $row) {
            if (isset($pageviewData[$row->date])) {
                $pageviewData[$row->date] = $row->total;
            }
        }

        $visitorsQuery = TrackingVisitor::selectRaw('DATE(first_seen_at) as date, count(*) as total')
            ->whereBetween('first_seen_at', [$startDate, $endDate])
            ->groupBy('date')
            ->get();

        foreach ($visitorsQuery as $row) {
            if (isset($visitorData[$row->date])) {
                $visitorData[$row->date] = $row->total;
            }
        }

        // Top Landing Pages
        $topPages = TrackingEvent::whereBetween('created_at', [$startDate, $endDate])
            ->where('event_type', 'page_view')
            ->select('page_url')
            ->selectRaw('count(*) as views')
            ->groupBy('page_url')
            ->orderByDesc('views')
            ->limit(8)
            ->get();

        // Device Breakdown
        $devices = TrackingVisitor::whereBetween('first_seen_at', [$startDate, $endDate])
            ->select('device')
            ->selectRaw('count(*) as total')
            ->groupBy('device')
            ->orderByDesc('total')
            ->get();

        return view('admin.analytics.overview', compact(
            'days', 'totalVisitors', 'totalSessions', 'totalPageviews', 'totalPurchases',
            'chartDates', 'pageviewData', 'visitorData', 'topPages', 'devices'
        ));
    }

    public function products(Request $request)
    {
        $days = (int) $request->input('days', 30);
        $startDate = now()->subDays($days)->startOfDay();
        $endDate = now()->endOfDay();

        // Get product event aggregates
        $productEvents = TrackingEvent::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('product_id')
            ->select('product_id', 'event_type')
            ->selectRaw('count(*) as total')
            ->groupBy('product_id', 'event_type')
            ->get();

        $stats = [];
        foreach ($productEvents as $event) {
            $pid = $event->product_id;
            if (! isset($stats[$pid])) {
                $stats[$pid] = [
                    'views' => 0,
                    'cart' => 0,
                    'checkout' => 0,
                    'purchases' => 0,
                ];
            }

            if ($event->event_type === 'product_view') {
                $stats[$pid]['views'] += $event->total;
            }
            if ($event->event_type === 'add_to_cart') {
                $stats[$pid]['cart'] += $event->total;
            }
            if ($event->event_type === 'checkout_start') {
                $stats[$pid]['checkout'] += $event->total;
            }
            if ($event->event_type === 'purchase') {
                $stats[$pid]['purchases'] += $event->total;
            }
        }

        // Fetch products and map stats
        $productIds = array_keys($stats);
        $products = Product::whereIn('id', $productIds)->get()->map(function ($product) use ($stats) {
            $s = $stats[$product->id];
            $product->analytics_views = $s['views'];
            $product->analytics_cart = $s['cart'];
            $product->analytics_checkout = $s['checkout'];
            $product->analytics_purchases = $s['purchases'];

            $product->conversion_rate = $s['views'] > 0 ? round(($s['purchases'] / $s['views']) * 100, 1) : 0;

            return $product;
        })->sortByDesc('analytics_views')->values();

        return view('admin.analytics.products', compact('products', 'days'));
    }

    public function geography(Request $request)
    {
        $days = (int) $request->input('days', 30);
        $startDate = now()->subDays($days)->startOfDay();
        $endDate = now()->endOfDay();

        // Top Countries
        $countries = TrackingVisitor::whereBetween('first_seen_at', [$startDate, $endDate])
            ->whereNotNull('country')
            ->select('country')
            ->selectRaw('count(*) as total')
            ->groupBy('country')
            ->orderByDesc('total')
            ->get();

        // Top Cities in BD
        $cities = TrackingVisitor::whereBetween('first_seen_at', [$startDate, $endDate])
            ->where('country', 'Bangladesh')
            ->whereNotNull('city')
            ->select('city', 'country')
            ->selectRaw('count(*) as total')
            ->groupBy('city', 'country')
            ->orderByDesc('total')
            ->limit(15)
            ->get();

        return view('admin.analytics.geography', compact('countries', 'cities', 'days'));
    }

    public function liveLocations()
    {
        // Get visitors active in the last 15 minutes with coordinates
        $locations = TrackingVisitor::where('last_seen_at', '>=', now()->subMinutes(15))
            ->whereNotNull('lat')
            ->whereNotNull('lon')
            ->where('country', 'Bangladesh')
            ->select('visitor_id', 'city', 'lat', 'lon', 'last_seen_at', 'device')
            ->get()
            ->map(function ($visitor) {
                return [
                    'id' => $visitor->visitor_id,
                    'city' => $visitor->city,
                    'lat' => (float) $visitor->lat,
                    'lng' => (float) $visitor->lon,
                    'device' => $visitor->device,
                    'active_seconds_ago' => now()->diffInSeconds($visitor->last_seen_at),
                ];
            });

        return response()->json($locations);
    }

    public function traffic(Request $request)
    {
        $days = (int) $request->input('days', 30);
        $startDate = now()->subDays($days)->startOfDay();
        $endDate = now()->endOfDay();

        // Top Sources
        $sources = TrackingSession::whereBetween('started_at', [$startDate, $endDate])
            ->whereNotNull('utm_source')
            ->select('utm_source')
            ->selectRaw('count(*) as total')
            ->groupBy('utm_source')
            ->orderByDesc('total')
            ->get();

        // Top Mediums
        $mediums = TrackingSession::whereBetween('started_at', [$startDate, $endDate])
            ->whereNotNull('utm_medium')
            ->select('utm_medium')
            ->selectRaw('count(*) as total')
            ->groupBy('utm_medium')
            ->orderByDesc('total')
            ->get();

        // Top Campaigns
        $campaigns = TrackingSession::whereBetween('started_at', [$startDate, $endDate])
            ->whereNotNull('utm_campaign')
            ->select('utm_campaign', 'utm_source')
            ->selectRaw('count(*) as total')
            ->groupBy('utm_campaign', 'utm_source')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Top Referrers (Extract domain)
        $referrers = TrackingSession::whereBetween('started_at', [$startDate, $endDate])
            ->whereNotNull('referrer')
            ->get()
            ->map(function ($session) {
                $host = parse_url($session->referrer, PHP_URL_HOST);

                return $host ? str_replace('www.', '', $host) : 'Direct/Unknown';
            })
            ->reject(function ($host) {
                // Ignore self-referrals
                $appHost = parse_url(config('app.url'), PHP_URL_HOST);

                return $host === 'Direct/Unknown' || $host === str_replace('www.', '', $appHost);
            })
            ->countBy()
            ->sortDesc()
            ->take(10);

        return view('admin.analytics.traffic', compact('sources', 'mediums', 'campaigns', 'referrers', 'days'));
    }

    public function funnel(Request $request)
    {
        $days = (int) $request->input('days', 30);
        $startDate = now()->subDays($days)->startOfDay();
        $endDate = now()->endOfDay();

        // Total Sessions
        $totalSessions = TrackingSession::whereBetween('started_at', [$startDate, $endDate])->count();

        // Get unique sessions that triggered each event step
        // Step 1: Product View
        $viewers = TrackingEvent::whereBetween('created_at', [$startDate, $endDate])
            ->where('event_type', 'product_view')
            ->distinct('tracking_session_id')
            ->count('tracking_session_id');

        // Step 2: Add to Cart
        $cartAdders = TrackingEvent::whereBetween('created_at', [$startDate, $endDate])
            ->where('event_type', 'add_to_cart')
            ->distinct('tracking_session_id')
            ->count('tracking_session_id');

        // Step 3: Checkout
        $checkouts = TrackingEvent::whereBetween('created_at', [$startDate, $endDate])
            ->where('event_type', 'checkout_start')
            ->distinct('tracking_session_id')
            ->count('tracking_session_id');

        // Step 4: Purchase
        $purchasers = TrackingEvent::whereBetween('created_at', [$startDate, $endDate])
            ->where('event_type', 'purchase')
            ->distinct('tracking_session_id')
            ->count('tracking_session_id');

        $funnelData = [
            'sessions' => $totalSessions,
            'viewers' => $viewers,
            'cart' => $cartAdders,
            'checkout' => $checkouts,
            'purchase' => $purchasers,
        ];

        return view('admin.analytics.funnel', compact('funnelData', 'days'));
    }
}
