<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticateClient
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('client')->check()) {
            session()->flash('redirect_message', 'Please log in to access this page.');
            return redirect()->route('login');
        }

        // A subscription that lapses mid-session turns Inactive here; the
        // client stays signed in so they can subscribe again.
        Auth::guard('client')->user()->syncSubscriptionStatus();

        return $next($request);
    }
}
