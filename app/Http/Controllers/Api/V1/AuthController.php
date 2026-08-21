<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Client;
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

    // ─── POST /api/v1/auth/register ──────────────────────────────────────────
    public function register(Request $request)
    {
        $request->validate([
            'firstname'            => 'required|string|max:50',
            'lastname'             => 'required|string|max:50',
            'email'                => 'required|email|max:100|unique:clients,email',
            'username'             => 'required|string|max:50|unique:clients,username',
            'password'             => 'required|string|min:8|confirmed',
            'contact_no'           => ['required', 'digits:11', 'regex:/^09[0-9]{9}$/'],
            'address_barangay'     => 'required|string|max:100',
            'address_municipality' => 'required|string|max:100',
            'address_province'     => 'required|string|max:100',
        ], [
            'contact_no.digits' => 'Mobile number must be exactly 11 digits in the Philippine format (e.g. 09123456789).',
            'contact_no.regex'  => 'Mobile number must be exactly 11 digits in the Philippine format (e.g. 09123456789).',
        ]);

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
