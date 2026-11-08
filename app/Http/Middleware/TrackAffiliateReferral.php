<?php

namespace App\Http\Middleware;

use App\Models\Affiliate;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackAffiliateReferral
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ref = $request->query('ref');

        if ($ref) {
            $code = strtoupper(trim($ref));
            // Check if valid affiliate exists
            $affiliate = Affiliate::where('code', $code)->where('status', 'approved')->first();
            if ($affiliate) {
                session(['affiliate_code' => $affiliate->code]);
                $response = $next($request);

                // 30 days cookie
                return $response->cookie('affiliate_ref', $affiliate->code, 60 * 24 * 30, null, null, false, false);
            }
        }

        return $next($request);
    }
}
