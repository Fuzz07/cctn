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

        // An admin may archive a client while they are signed in.
        if (Auth::guard('client')->user()->isArchived()) {
            Auth::guard('client')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['login_input' => 'This account has been archived. Please contact BCTVI support.']);
        }

        return $next($request);
    }
}
