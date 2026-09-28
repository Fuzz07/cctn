<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceMode
{
    /**
     * Routes that remain accessible during maintenance. Clients can still
     * authenticate and recover their accounts, even while service actions are
     * temporarily unavailable.
     */
    protected array $publicAllowed = [
        '/',
        'login',
        'register',
        'forgot-password',
        'reset-password',
        'auth/google',
        'auth/google/callback',
        'terms',
        'terms-and-conditions',
    ];

    public function handle(Request $request, Closure $next)
    {
        if (!config('cctn.maintenance_mode', false)) {
            return $next($request);
        }

        // Always allow the admin section through (login page + panel)
        if ($request->is('admin*')) {
            return $next($request);
        }

        // Allow logged-in admins through anywhere
        if (Auth::guard('admin')->check()) {
            return $next($request);
        }

        // Allow the public landing page so clients see the notice, not a blank wall
        foreach ($this->publicAllowed as $path) {
            if ($request->is($path)) {
                return $next($request);
            }
        }

        // Everything else (dashboard, booking, billing, login, register…) is blocked
        return response()->view('maintenance', [], 503);
    }
}
