<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GeoIpService
{
    /**
     * Common country name to ISO 2-letter country code map.
     */
    protected static array $countryCodeMap = [
        'bangladesh' => 'BD',
        'united states' => 'US',
        'usa' => 'US',
        'united kingdom' => 'GB',
        'uk' => 'GB',
        'india' => 'IN',
        'canada' => 'CA',
        'australia' => 'AU',
        'germany' => 'DE',
        'france' => 'FR',
        'united arab emirates' => 'AE',
        'uae' => 'AE',
        'saudi arabia' => 'SA',
        'singapore' => 'SG',
        'malaysia' => 'MY',
        'qatar' => 'QA',
        'kuwait' => 'KW',
        'oman' => 'OM',
        'italy' => 'IT',
        'spain' => 'ES',
        'netherlands' => 'NL',
        'pakistan' => 'PK',
        'nepal' => 'NP',
        'china' => 'CN',
        'japan' => 'JP',
    ];

    /**
     * Resolve IP address to location details with caching.
     */
    public static function resolve(?string $ip): array
    {
        if (! $ip || $ip === '127.0.0.1' || $ip === '::1' || str_starts_with($ip, '192.168.') || str_starts_with($ip, '10.')) {
            return [
                'country' => 'Localhost',
                'country_code' => 'DEV',
                'flag' => '💻',
                'division' => 'Internal',
                'city' => 'Local Network',
                'lat' => null,
                'lon' => null,
                'isp' => 'Loopback/LAN',
                'is_local' => true,
            ];
        }

        return Cache::remember("geoip_lookup_{$ip}", now()->addMonth(), function () use ($ip) {
            try {
                $response = Http::timeout(3)->get("http://ip-api.com/json/{$ip}?fields=status,message,country,countryCode,region,regionName,city,lat,lon,isp");

                if ($response->successful() && ($response['status'] ?? '') === 'success') {
                    $code = strtoupper($response['countryCode'] ?? '');

                    return [
                        'country' => $response['country'] ?? 'Unknown',
                        'country_code' => $code,
                        'flag' => self::getFlag($code, $response['country'] ?? null),
                        'division' => $response['regionName'] ?? '',
                        'city' => $response['city'] ?? '',
                        'lat' => isset($response['lat']) ? (float) $response['lat'] : null,
                        'lon' => isset($response['lon']) ? (float) $response['lon'] : null,
                        'isp' => $response['isp'] ?? '',
                        'is_local' => false,
                    ];
                }
            } catch (\Exception $e) {
                // Fallback on timeout or network error
            }

            return [
                'country' => 'Unknown',
                'country_code' => null,
                'flag' => '🌐',
                'division' => '',
                'city' => '',
                'lat' => null,
                'lon' => null,
                'isp' => '',
                'is_local' => false,
            ];
        });
    }

    /**
     * Get flag emoji from country code or country name.
     */
    public static function getFlag(?string $code, ?string $countryName = null): string
    {
        if (! $code && $countryName) {
            $lower = strtolower(trim($countryName));
            $code = self::$countryCodeMap[$lower] ?? null;
        }

        if ($code && strlen($code) === 2 && ctype_alpha($code)) {
            $code = strtoupper($code);
            $c1 = ord($code[0]);
            $c2 = ord($code[1]);

            if ($c1 >= 65 && $c1 <= 90 && $c2 >= 65 && $c2 <= 90) {
                return mb_chr($c1 + 127397, 'UTF-8').mb_chr($c2 + 127397, 'UTF-8');
            }
        }

        if ($countryName) {
            $lower = strtolower(trim($countryName));
            if (isset(self::$countryCodeMap[$lower])) {
                $mapped = self::$countryCodeMap[$lower];

                return mb_chr(ord($mapped[0]) + 127397, 'UTF-8').mb_chr(ord($mapped[1]) + 127397, 'UTF-8');
            }
        }

        return '🌐';
    }

    /**
     * Format a clean human-readable title from a URL path.
     */
    public static function formatPageTitle(?string $url): string
    {
        if (! $url) {
            return 'Homepage';
        }

        $path = parse_url($url, PHP_URL_PATH) ?: '/';
        $path = trim($path, '/');

        if ($path === '' || $path === '/') {
            return 'Homepage (/)';
        }

        if (str_starts_with($path, 'product/')) {
            $slug = substr($path, 8);
            $title = ucwords(str_replace('-', ' ', $slug));

            return 'Product: '.$title;
        }

        if ($path === 'shop') {
            return 'Shop Catalog';
        }

        if ($path === 'cart') {
            return 'Shopping Cart';
        }

        if (str_starts_with($path, 'checkout')) {
            return 'Checkout Page';
        }

        if (str_starts_with($path, 'order/')) {
            return 'Order Confirmation';
        }

        if ($path === 'contact') {
            return 'Contact Page';
        }

        if ($path === 'about') {
            return 'About Us';
        }

        if (str_starts_with($path, 'account')) {
            return 'Customer Account';
        }

        return '/'.Str::limit($path, 30);
    }
}
