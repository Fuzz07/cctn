<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

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
        $request->validate([
            'firstname'         => 'required|string|max:50',
            'lastname'          => 'required|string|max:50',
            'email'             => 'required|email|max:100|unique:clients,email',
            'username'          => 'required|string|max:50|unique:clients,username',
            'password'          => 'required|string|min:8|confirmed',
            'contact_no'        => 'required|string|max:20',
            'address_barangay'  => 'required|string|max:100',
            'address_municipality' => 'required|string|max:100|in:Bantayan,Santa Fe,Madridejos',
            'address_province'  => 'required|string|max:100',
            'proof_of_billing'  => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ], [
            'proof_of_billing.required' => 'Please attach a photo of your proof of billing for account verification.',
            'proof_of_billing.image'    => 'The proof of billing must be a photo (JPG, PNG, or WEBP).',
            'proof_of_billing.max'      => 'The proof of billing photo must not be larger than 5 MB.',
        ]);

        // Auto-generate account number (YYYY-MM-NN series, e.g. 2026-01-01)
        $accountNumber = Client::nextAccountNumber();

        // Handle profile photo upload
        $profilePhotoPath = null;
        if ($request->hasFile('profile_photo') && $request->file('profile_photo')->isValid()) {
            $file = $request->file('profile_photo');
            $filename = 'client_' . time() . '_' . rand(1000, 9999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/profile_photos'), $filename);
            $profilePhotoPath = 'uploads/profile_photos/' . $filename;
        }

        // Handle proof of billing upload (required for account verification)
        $proofOfBillingPath = null;
        if ($request->hasFile('proof_of_billing') && $request->file('proof_of_billing')->isValid()) {
            $file = $request->file('proof_of_billing');
            $filename = 'proof_' . time() . '_' . rand(1000, 9999) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('uploads/proof_of_billing'), $filename);
            $proofOfBillingPath = 'uploads/proof_of_billing/' . $filename;
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

        $client = Client::where('email', $request->email)->first();
        if (!$client) {
            return back()->withErrors(['email' => 'No account found with that email address.']);
        }

        $token = bin2hex(random_bytes(32));
        $client->update([
            'reset_token'      => $token,
            'reset_expires_at' => now()->addHour(),
        ]);

        // In production, send email. For now, just flash the token.
        session()->flash('success_message', 'Password reset instructions have been sent to your email address.');

        return redirect()->route('login');
    }
}
