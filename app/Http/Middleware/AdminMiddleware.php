<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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
            return $next($request);
        }

        if (auth()->check()) {
            return redirect('/')->with('error', 'You do not have permission to access the admin panel.');
        }

        return redirect()->route('admin.login');
    }
}
