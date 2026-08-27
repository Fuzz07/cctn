<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Support\InputRules;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // ─── POST /api/v1/auth/login ─────────────────────────────────────────────
    public function login(Request $request)
    {
        $request->validate([
            'login_input' => 'required|string',
            'password'    => 'required|string',
        ]);

        $client = Client::where('username', $request->login_input)
            ->orWhere('email', $request->login_input)
            ->first();

        if (!$client || !Hash::check($request->password, $client->password)) {
            throw ValidationException::withMessages([
                'login_input' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Auto-verify on first mobile login
        if (empty($client->email_verified_at)) {
            $client->update(['email_verified_at' => now()]);
        }

        $token = $client->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => "Welcome back, {$client->firstname}!",
            'token'   => $token,
            'client'  => new ClientResource($client),
        ]);
    }

    // --- POST /api/v1/auth/google -------------------------------------------
    public function google(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string',
        ]);

        $googleClientId = config('services.google.android_client_id') ?: config('services.google.client_id');
        if (!$googleClientId) {
            throw ValidationException::withMessages([
                'id_token' => ['Google sign-in is not configured for this app.'],
            ]);
        }

        $profile = Http::acceptJson()->get('https://oauth2.googleapis.com/tokeninfo', [
            'id_token' => $request->input('id_token'),
        ]);

        $email = $profile->json('email');
        if (!$profile->successful()
            || !$email
            || $profile->json('aud') !== $googleClientId
            || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || !filter_var($profile->json('email_verified'), FILTER_VALIDATE_BOOLEAN)) {
            throw ValidationException::withMessages([
                'id_token' => ['Google sign-in could not be verified. Please try again.'],
            ]);
        }

        $client = Client::where('email', $email)->first();

        if (!$client) {
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
        } elseif (empty($client->email_verified_at)) {
            $client->update(['email_verified_at' => now()]);
        }

        $token = $client->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => "Welcome back, {$client->firstname}!",
            'token'   => $token,
            'client'  => new ClientResource($client->fresh()),
        ]);
    }
    // ─── POST /api/v1/auth/register ──────────────────────────────────────────
    public function register(Request $request)
    {
        // Same character rules as the web forms; the mobile app has no
        // client-side filter of its own, so these are the only check.
        $request->validate([
            'firstname'            => InputRules::name(true, 50),
            'middlename'           => InputRules::name(false, 50),
            'lastname'             => InputRules::name(true, 50),
            'email'                => 'required|email|max:100|unique:clients,email',
            'username'             => 'required|string|max:50|unique:clients,username',
            'password'             => 'required|string|min:8|confirmed',
            'contact_no'           => InputRules::mobile(),
            'address_barangay'     => InputRules::address(true, 100),
            'address_municipality' => InputRules::address(true, 100),
            'address_province'     => InputRules::address(true, 100),
        ], InputRules::messages([
            'name'    => ['firstname', 'middlename', 'lastname'],
            'address' => ['address_barangay', 'address_municipality', 'address_province'],
            'mobile'  => ['contact_no'],
        ]));

        $accountNumber = Client::nextAccountNumber();

        $birthdate = $request->input('birthdate') ?: '1995-01-01';
        $age       = Carbon::parse($birthdate)->age;

        $client = Client::create([
            'account_number'       => $accountNumber,
            'firstname'            => $request->firstname,
            'middlename'           => $request->input('middlename') ?: '',
            'lastname'             => $request->lastname,
            'birthdate'            => $birthdate,
            'age'                  => $age,
            'place_of_birth'       => $request->input('place_of_birth') ?: 'Bantayan, Cebu',
            'gender'               => $request->input('gender') ?: 'Prefer not to say',
            'civil_status'         => $request->input('civil_status') ?: 'Single',
            'address_barangay'     => $request->address_barangay,
            'address_municipality' => $request->address_municipality,
            'address_province'     => $request->address_province,
            'contact_no'           => $request->contact_no,
            'email'                => $request->email,
            'username'             => $request->username,
            'password'             => Hash::make($request->password),
            'email_verified_at'    => now(),
        ]);

        $token = $client->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success'        => true,
            'message'        => "Welcome, {$client->firstname}! Your account number is {$accountNumber}.",
            'token'          => $token,
            'client'         => new ClientResource($client),
        ], 201);
    }

    // ─── POST /api/v1/auth/logout ────────────────────────────────────────────
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}
