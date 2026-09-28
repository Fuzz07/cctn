<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceMode
{
    /**
     * If maintenance mode is ON (MAINTENANCE_MODE=true in .env),
     * block everyone except logged-in admins.
     * Admin login/logout routes are always allowed so admins can still sign in.
     */
    public function handle(Request $request, Closure $next)
    {
        if (!config('cctn.maintenance_mode', false)) {
            return $next($request);
        }

        // Always allow the admin section through (login page + authenticated panel)
        if ($request->is('admin*')) {
            return $next($request);
        }

        // Allow logged-in admins through anywhere
        if (Auth::guard('admin')->check()) {
            return $next($request);
        }

        // Everyone else sees the maintenance page
        return response()->view('maintenance', [], 503);
    }
}
