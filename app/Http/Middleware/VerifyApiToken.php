<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Setting;

class VerifyApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if API is enabled
        if (Setting::get('api_enabled', '0') !== '1') {
            return response()->json([
                'error' => 'API is disabled. Enable it from the admin dashboard.'
            ], 403);
        }

        $expectedToken = Setting::get('api_token');
        
        if (!$expectedToken) {
            return response()->json([
                'error' => 'API token is not configured. Generate one in the admin dashboard.'
            ], 500);
        }

        $providedToken = $request->bearerToken();

        if (!$providedToken || $providedToken !== $expectedToken) {
            return response()->json([
                'error' => 'Unauthorized. Invalid or missing API token.'
            ], 401);
        }

        return $next($request);
    }
}
