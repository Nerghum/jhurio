<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check() && (auth()->user()->isAdmin() || auth()->user()->isManager())) {
            // Auto self-heal: run pending migrations and clear stale route cache
            try {
                if (! Schema::hasTable('affiliates')) {
                    Artisan::call('migrate', ['--force' => true]);
                }
                if (! Route::has('admin.affiliates.index')) {
                    Artisan::call('route:clear');
                }
            } catch (\Throwable $e) {
                // Ignore to avoid blocking admin access
            }

            return $next($request);
        }

        if (auth()->check()) {
            return redirect('/')->with('error', 'You do not have permission to access the admin panel.');
        }

        return redirect()->route('admin.login');
    }
}
