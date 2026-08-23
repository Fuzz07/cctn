<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Support\InputRules;
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

        // Same character rules as the web forms; the mobile app has no
        // client-side filter of its own, so these are the only check.
        $request->validate([
            'firstname'      => InputRules::name(true, 50),
            'middlename'     => InputRules::name(false, 50),
            'lastname'       => InputRules::name(true, 50),
            'place_of_birth' => InputRules::name(false, 100),
            'email'          => 'required|email|max:100|unique:clients,email,' . $client->id,
            'username'       => 'required|string|max:50|unique:clients,username,' . $client->id,
            'contact_no'     => InputRules::mobile(),
            'birthdate'      => 'nullable|date',
        ], InputRules::messages([
            'name'   => ['firstname', 'middlename', 'lastname', 'place_of_birth'],
            'mobile' => ['contact_no'],
        ]));

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
