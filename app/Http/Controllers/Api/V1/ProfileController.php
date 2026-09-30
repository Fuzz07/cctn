<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ClientResource;
use App\Models\Client;
use App\Models\Notification;
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

    /** Submit a disconnection request for administrator review. */
    public function requestDisconnection(Request $request)
    {
        if (! Client::supportsDisconnectionRequests()) {
            return response()->json([
                'success' => false,
                'message' => 'Disconnection requests are temporarily unavailable. Please contact support.',
            ], 503);
        }

        $client = $request->user();
        $client->load('currentService');

        if (! $client->isAccountActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Only an active subscription can request disconnection.',
            ], 422);
        }

        if (! $client->requestDisconnection()) {
            return response()->json([
                'success' => true,
                'message' => 'Your disconnection request is already awaiting administrator review.',
                'client' => new ClientResource($client->fresh()->load('currentService')),
            ]);
        }

        $planName = $client->currentService?->service_name ?? 'their current subscription';

        Notification::create([
            'for_admin' => true,
            'client_id' => $client->id,
            'title' => 'Disconnection Request',
            'message' => "{$client->full_name} requested disconnection of {$planName}.",
            'link' => 'admin/clients?filter=disconnection_requests',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your disconnection request was sent to the administrator. Your subscription remains Active until it is approved.',
            'client' => new ClientResource($client->fresh()->load('currentService')),
        ]);
    }
}
