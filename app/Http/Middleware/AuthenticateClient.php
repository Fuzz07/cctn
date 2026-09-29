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

        $client = Auth::guard('client')->user();
        $client->syncSubscriptionStatus();

        // An admin may cancel or expire a subscription while the client is signed in.
        if ($client->isArchived()) {
            Auth::guard('client')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['login_input' => 'This account is inactive because its subscription was cancelled or expired. Please contact BCTVI support.']);
        }

        return $next($request);
    }
}
