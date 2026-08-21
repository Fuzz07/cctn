<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    // ─── GET /api/v1/profile ─────────────────────────────────────────────────
    public function show(Request $request)
    {
        return response()->json([
            'success' => true,
            'client'  => new ClientResource($request->user()),
        ]);
    }

    // ─── PUT /api/v1/profile ─────────────────────────────────────────────────
    public function update(Request $request)
    {
        $client = $request->user();

        $request->validate([
            'firstname'  => 'required|string|max:50',
            'lastname'   => 'required|string|max:50',
            'email'      => 'required|email|max:100|unique:clients,email,' . $client->id,
            'username'   => 'required|string|max:50|unique:clients,username,' . $client->id,
            'contact_no' => ['required', 'digits:11', 'regex:/^09[0-9]{9}$/'],
            'birthdate'  => 'nullable|date',
        ], [
            'contact_no.digits' => 'Mobile number must be exactly 11 digits in the Philippine format (e.g. 09123456789).',
            'contact_no.regex'  => 'Mobile number must be exactly 11 digits in the Philippine format (e.g. 09123456789).',
        ]);

        $data = $request->only([
            'firstname', 'middlename', 'lastname', 'email', 'username',
            'contact_no', 'civil_status', 'address_barangay',
            'address_municipality', 'address_province', 'gender',
            'birthdate', 'place_of_birth',
        ]);

        if (!empty($data['birthdate'])) {
            $data['age'] = Carbon::parse($data['birthdate'])->age;
        }

        if ($request->filled('new_password')) {
            $request->validate(['new_password' => 'min:8']);
            $data['password'] = Hash::make($request->new_password);
        }

        $client->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'client'  => new ClientResource($client->fresh()),
        ]);
    }
}
