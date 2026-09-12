<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Support\InputRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    // ─── Login Form ──────────────────────────────────────────────────────────────
    public function showLogin()
    {
        if (Auth::guard('client')->check()) {
            return redirect()->route('client.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login_input' => 'required|string',
            'password'    => 'required|string',
        ]);

        $loginInput = $request->input('login_input');
        $password   = $request->input('password');

        // Find client by username or email
        $client = Client::where('username', $loginInput)
            ->orWhere('email', $loginInput)
            ->first();

        if (!$client) {
            return back()->withErrors(['login_input' => 'No account found with that username or email address.'])->withInput();
        }

        if (!Hash::check($password, $client->password)) {
            return back()->withErrors(['password' => 'Incorrect password. Please try again.'])->withInput();
        }

        // Auto-verify email if not verified
        if (empty($client->email_verified_at)) {
            $client->update(['email_verified_at' => now()]);
        }

        Auth::guard('client')->login($client);
        session()->flash('success_message', "Welcome back, {$client->firstname}!");

        return redirect()->route('client.dashboard');
    }

    // ─── Register Form ───────────────────────────────────────────────────────────
    public function showRegister()
    {
        if (Auth::guard('client')->check()) {
            return redirect()->route('client.dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        // Character rules mirror public/assets/js/form-restrictions.js, which
        // filters the same fields as the user types. That layer is bypassable,
        // so these are the checks that decide what reaches the database.
        $request->validate([
            'firstname'         => InputRules::name(true, 50),
            'middlename'        => InputRules::name(false, 50),
            'lastname'          => InputRules::name(true, 50),
            'place_of_birth'    => InputRules::name(false, 100),
            'age'               => InputRules::number(false, 1, 120),
            'email'             => 'required|email|max:100|unique:clients,email',
            'username'          => 'required|string|max:50|unique:clients,username',
            'password'          => 'required|string|min:8|confirmed',
            'contact_no'        => InputRules::mobile(),
            'address_barangay'  => InputRules::address(true, 100),
            'address_municipality' => 'required|string|max:100|in:Bantayan,Santa Fe,Madridejos',
            'address_province'  => InputRules::address(true, 100),
            'proof_of_billing'  => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
            'profile_photo'     => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ], array_merge(InputRules::messages([
            'name'    => ['firstname', 'middlename', 'lastname', 'place_of_birth'],
            'number'  => ['age'],
            'address' => ['address_barangay', 'address_province'],
            'mobile'  => ['contact_no'],
        ]), [
            'profile_photo.image'       => 'The profile photo must be an image file (JPG, PNG, GIF, or WEBP).',
            'profile_photo.mimes'       => 'The profile photo must be a JPG, PNG, GIF, or WEBP image.',
            'profile_photo.max'         => 'The profile photo must not be larger than 5 MB.',
            'proof_of_billing.required' => 'Please attach a photo of your proof of billing for account verification.',
            'proof_of_billing.image'    => 'The proof of billing must be a photo (JPG, PNG, or WEBP).',
            'proof_of_billing.max'      => 'The proof of billing photo must not be larger than 5 MB.',
        ]));

        // Auto-generate account number (YYYY-MM-NN series, e.g. 2026-01-01)
        $accountNumber = Client::nextAccountNumber();

        try {
            // Handle profile photo upload
            $profilePhotoPath = null;
            if ($request->hasFile('profile_photo') && $request->file('profile_photo')->isValid()) {
                $file = $request->file('profile_photo');
                $filename = 'client_' . time() . '_' . rand(1000, 9999) . '.' . ($file->extension() ?: 'jpg');
                $file->move(public_path('uploads/profile_photos'), $filename);
                $profilePhotoPath = 'uploads/profile_photos/' . $filename;
            }

            // Handle proof of billing upload (required for account verification)
            $proofOfBillingPath = null;
            if ($request->hasFile('proof_of_billing') && $request->file('proof_of_billing')->isValid()) {
                $file = $request->file('proof_of_billing');
                $filename = 'proof_' . time() . '_' . rand(1000, 9999) . '.' . ($file->extension() ?: 'jpg');
                $file->move(public_path('uploads/proof_of_billing'), $filename);
                $proofOfBillingPath = 'uploads/proof_of_billing/' . $filename;
            }
        } catch (\Exception $e) {
            return back()
                ->withErrors(['proof_of_billing' => 'Your uploaded photo could not be saved on the server. Please try again or contact BCTVI support.'])
                ->withInput();
        }

        // Calculate age
        $age = 25;
        $birthdate = $request->input('birthdate', '1995-01-01');
        if (!empty($birthdate)) {
            $age = \Carbon\Carbon::parse($birthdate)->age;
        }

        $client = Client::create([
            'account_number'       => $accountNumber,
            'firstname'            => $request->firstname,
            'middlename'           => $request->input('middlename', ''),
            'lastname'             => $request->lastname,
            'birthdate'            => $birthdate ?: '1995-01-01',
            'age'                  => $age,
            'place_of_birth'       => $request->input('place_of_birth', 'Bantayan, Cebu'),
            'gender'               => $request->input('gender', 'Prefer not to say'),
            'civil_status'         => $request->input('civil_status', 'Single'),
            'address_barangay'     => $request->address_barangay,
            'address_municipality' => $request->address_municipality,
            'address_province'     => $request->address_province,
            'contact_no'           => $request->contact_no,
            'email'                => $request->email,
            'username'             => $request->username,
            'password'             => Hash::make($request->password),
            'profile_photo'        => $profilePhotoPath,
            'proof_of_billing'     => $proofOfBillingPath,
            'email_verified_at'    => now(),
        ]);

        Auth::guard('client')->login($client);
        session()->flash('success_message', "Welcome, {$client->firstname}! Your BCTVI account has been created successfully. Your account number is {$accountNumber}.");

        return redirect()->route('client.dashboard');
    }

    // ─── Google Sign-In / Sign-Up ────────────────────────────────────────────────
    public function googleRedirect(Request $request)
    {
        $random = bin2hex(random_bytes(16));
        $fromApp = $request->query('from') === 'app' || $request->query('from') === 'mobile';
        
        $stateData = [
            'r' => $random,
            'f' => $fromApp ? 'app' : 'web',
        ];
        $encodedState = base64_encode(json_encode($stateData));

        session([
            'google_oauth_state'    => $random,
            'google_oauth_from_app' => $fromApp,
        ]);

        $query = http_build_query([
            'client_id'     => config('services.google.client_id'),
            'redirect_uri'  => config('services.google.redirect'),
            'response_type' => 'code',
            'scope'         => 'openid email profile',
            'state'         => $encodedState,
            'prompt'        => 'select_account',
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    public function googleCallback(Request $request)
    {
        $rawState = $request->input('state');
        $fromApp = false;
        $stateRandom = null;

        if ($rawState) {
            $decoded = json_decode(base64_decode($rawState), true);
            if (is_array($decoded)) {
                $stateRandom = $decoded['r'] ?? null;
                $fromApp = ($decoded['f'] ?? '') === 'app';
            } else {
                $stateRandom = $rawState;
            }
        }

        if (!$fromApp && session()->pull('google_oauth_from_app', false)) {
            $fromApp = true;
        }

        $sessionState = session()->pull('google_oauth_state');
        // Validate CSRF state if session is available
        if (!$request->filled('state') || ($sessionState && $stateRandom !== $sessionState)) {
            if ($fromApp) {
                return $this->redirectToApp(null, 'Google sign-in could not be verified. Please try again.');
            }
            return redirect()->route('login')
                ->withErrors(['login_input' => 'Google sign-in could not be verified. Please try again.']);
        }

        if (!$request->filled('code')) {
            if ($fromApp) {
                return $this->redirectToApp(null, 'Google sign-in was cancelled.');
            }
            return redirect()->route('login')
                ->withErrors(['login_input' => 'Google sign-in was cancelled.']);
        }

        $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id'     => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'code'          => $request->input('code'),
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => config('services.google.redirect'),
        ]);

        if (!$tokenResponse->successful() || !$tokenResponse->json('access_token')) {
            if ($fromApp) {
                return $this->redirectToApp(null, 'Google sign-in failed. Please try again or log in with your password.');
            }
            return redirect()->route('login')
                ->withErrors(['login_input' => 'Google sign-in failed. Please try again or log in with your password.']);
        }

        $profile = Http::withToken($tokenResponse->json('access_token'))
            ->get('https://www.googleapis.com/oauth2/v3/userinfo');

        $email = $profile->json('email');
        if (!$profile->successful() || !$email) {
            if ($fromApp) {
                return $this->redirectToApp(null, 'Could not read your Google profile. Please try again.');
            }
            return redirect()->route('login')
                ->withErrors(['login_input' => 'Could not read your Google profile. Please try again.']);
        }

        $client = Client::where('email', $email)->first();

        if (!$client) {
            // First Google sign-in: register a new client account
            $base = preg_replace('/[^a-z0-9_.]/', '', strtolower(strstr($email, '@', true) ?: 'client')) ?: 'client';
            $username = $base;
            $suffix = 1;
            while (Client::where('username', $username)->exists()) {
                $username = $base . $suffix++;
            }

            $client = Client::create([
                'account_number'    => Client::nextAccountNumber(),
                'firstname'         => $profile->json('given_name') ?: ($profile->json('name') ?: 'Google'),
                'lastname'          => $profile->json('family_name') ?: 'Client',
                'email'             => $email,
                'username'          => $username,
                'password'          => Hash::make(bin2hex(random_bytes(16))),
                'address_province'  => 'Cebu',
                'email_verified_at' => now(),
            ]);

            if ($fromApp) {
                $token = $client->createToken('mobile-app')->plainTextToken;
                return $this->redirectToApp($token);
            }

            Auth::guard('client')->login($client);
            session()->flash('success_message', "Welcome, {$client->firstname}! Your BCTVI account has been created with Google. Your account number is {$client->account_number}. Please complete your address and contact details in your profile.");

            return redirect()->route('client.dashboard');
        }

        if ($fromApp) {
            $token = $client->createToken('mobile-app')->plainTextToken;
            return $this->redirectToApp($token);
        }

        Auth::guard('client')->login($client);
        session()->flash('success_message', "Welcome back, {$client->firstname}!");

        return redirect()->route('client.dashboard');
    }

    private function redirectToApp(?string $token, ?string $error = null)
    {
        $params = [];
        if ($token) {
            $params['token'] = $token;
        }
        if ($error) {
            $params['error'] = $error;
        }
        $query = http_build_query($params);
        $customSchemeUrl = 'cctn://auth/callback?' . $query;
        $intentUrl = 'intent://auth/callback?' . $query . '#Intent;scheme=cctn;package=com.cctn.app;end';
        $title = $token ? 'Login Successful!' : 'Sign-In Notice';
        $message = $token ? 'Redirecting back to BCTVI App...' : htmlspecialchars($error ?? 'An error occurred.');

        return response(
            "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>{$title}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            background-color: #0f172a;
            color: #ffffff;
            text-align: center;
            padding: 24px;
        }
        .card {
            background: #1e293b;
            border-radius: 16px;
            padding: 32px 24px;
            max-width: 400px;
            width: 100%;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.5);
            border: 1px solid #334155;
        }
        .icon {
            width: 64px;
            height: 64px;
            border-radius: 50%;
            background: " . ($token ? "#22c55e" : "#ef4444") . ";
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            margin: 0 auto 20px;
        }
        h2 { margin: 0 0 10px; font-size: 22px; }
        p { color: #94a3b8; font-size: 14px; margin: 0 0 24px; line-height: 1.5; }
        .btn {
            display: block;
            width: 100%;
            background: #dc2626;
            color: #ffffff;
            padding: 14px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 700;
            font-size: 15px;
            box-shadow: 0 4px 12px rgba(220,38,38,0.35);
        }
        .btn:active { background: #b91c1c; }
    </style>
</head>
<body>
    <div class='card'>
        <div class='icon'>" . ($token ? "✓" : "!") . "</div>
        <h2>{$title}</h2>
        <p>{$message}</p>
        <a id='open-btn' href='{$intentUrl}' class='btn'>Open BCTVI App</a>
    </div>
    <script>
        function openApp() {
            window.location.href = '{$intentUrl}';
            setTimeout(function() {
                window.location.href = '{$customSchemeUrl}';
            }, 600);
        }
        openApp();
    </script>
</body>
</html>"
        );
    }

    // ─── Logout ──────────────────────────────────────────────────────────────────
    public function logout()
    {
        Auth::guard('client')->logout();
        session()->invalidate();
        session()->regenerateToken();
        return redirect()->route('home');
    }

    // ─── Forgot Password ─────────────────────────────────────────────────────────
    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        try {
            $status = Password::broker('clients')->sendResetLink(
                $request->only('email')
            );
        } catch (\Throwable $exception) {
            Log::error('Unable to send client password reset email.', [
                'email' => $request->input('email'),
                'exception' => $exception,
            ]);

            return back()
                ->withErrors(['email' => 'We could not send the reset email. Please try again later or contact BCTVI support.'])
                ->withInput();
        }

        if ($status !== Password::RESET_LINK_SENT) {
            return back()
                ->withErrors(['email' => __($status)])
                ->withInput();
        }

        return redirect()->route('login')->with('success_message', __($status));
    }

    public function showResetPassword(Request $request, string $token)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::broker('clients')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (Client $client, string $password) {
                $client->forceFill([
                    'password' => Hash::make($password),
                ])->save();

                event(new PasswordReset($client));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withErrors(['email' => __($status)])
                ->withInput($request->only('email'));
        }

        return redirect()->route('login')->with('success_message', __($status));
    }
}
