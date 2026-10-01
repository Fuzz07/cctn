<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\BillingAccount;
use App\Models\MaintenanceRequest;
use App\Models\Notification;
use App\Models\Service;
use App\Support\InputRules;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function index()
    {
        $client = Auth::guard('client')->user();

        // Fetch stats
        $totalAppointments  = Appointment::where('client_id', $client->id)->count();
        $pendingAppointments = Appointment::where('client_id', $client->id)->where('status', 'pending')->count();
        $approvedAppointments = Appointment::where('client_id', $client->id)->where('status', 'approved')->count();

        // Recent appointments
        $recentAppointments = Appointment::with('service')
            ->where('client_id', $client->id)
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $client->load('currentService');

        return view('client.dashboard', compact(
            'client', 'totalAppointments', 'pendingAppointments',
            'approvedAppointments', 'recentAppointments'
        ));
    }

    public function settings()
    {
        $client = Auth::guard('client')->user();
        $client->load('currentService');

        // Paid plans are shown when an inactive client wants to subscribe again.
        $availablePlans = $client->isAccountActive()
            ? collect()
            : Service::active()->where('price', '>', 0)->orderBy('price')->get()->unique('service_name')->values();

        $pendingPlan = $client->isAccountActive()
            ? null
            : Appointment::with('service')
                ->where('client_id', $client->id)
                ->where('status', 'pending')
                ->whereHas('service', fn ($query) => $query->where('price', '>', 0))
                ->latest()
                ->first();

        return view('client.settings', compact('client', 'availablePlans', 'pendingPlan'));
    }

    public function updateProfile(Request $request)
    {
        $client = Auth::guard('client')->user();

        // Character rules mirror public/assets/js/form-restrictions.js.
        $request->validate([
            'firstname'      => InputRules::name(true, 50),
            'middlename'     => InputRules::name(false, 50),
            'lastname'       => InputRules::name(true, 50),
            'place_of_birth' => InputRules::name(false, 100),
            'email'          => 'required|email|max:100|unique:clients,email,' . $client->id,
            'username'       => 'required|string|max:50|unique:clients,username,' . $client->id,
            'contact_no'     => InputRules::mobile(),
            'birthdate'      => 'required|date',
        ], InputRules::messages([
            'name'   => ['firstname', 'middlename', 'lastname', 'place_of_birth'],
            'mobile' => ['contact_no'],
        ]));

        $data = $request->only([
            'firstname', 'middlename', 'lastname', 'email', 'username',
            'contact_no', 'civil_status', 'address_barangay', 'address_municipality',
            'address_province', 'gender', 'birthdate', 'place_of_birth',
        ]);

        // Calculate age
        if (!empty($data['birthdate'])) {
            $data['age'] = \Carbon\Carbon::parse($data['birthdate'])->age;
        }

        // Handle new password
        if ($request->filled('new_password')) {
            $request->validate(['new_password' => 'min:8']);
            $data['password'] = Hash::make($request->new_password);
        }

        $client->update($data);

        return redirect()->route('client.settings')->with('success_message', 'Profile updated successfully!');
    }

    /** Cancel the current plan. The account and its history stay, and the client can subscribe again. */
    public function unsubscribe()
    {
        $client = Auth::guard('client')->user();
        $client->load('currentService');

        if (! $client->unsubscribe()) {
            return redirect()->route('client.settings', ['tab' => 'service'])->with(
                'error_message',
                'You do not have an active subscription to cancel.'
            );
        }

        $planName = $client->currentService?->service_name ?? 'their subscription';

        Notification::create([
            'for_admin' => true,
            'client_id' => $client->id,
            'title' => 'Client Unsubscribed',
            'message' => "{$client->full_name} unsubscribed from {$planName}. The account is now Inactive.",
            'link' => 'admin/clients?filter=inactive',
        ]);

        return redirect()->route('client.settings', ['tab' => 'service'])->with(
            'success_message',
            'You have unsubscribed. Your subscription is now Inactive, and your account and subscription history have been kept. You can choose a plan to subscribe again at any time.'
        );
    }

    /** Ask for the service to be disconnected. This is separate from the subscription status. */
    public function requestDisconnection()
    {
        if (! Client::supportsDisconnectionRequests()) {
            return redirect()->route('client.settings', ['tab' => 'service'])->with('error_message',
                'Disconnection requests are temporarily unavailable. Please contact support.'
            );
        }

        $client = Auth::guard('client')->user();
        $client->load('currentService');

        if ($client->hasPendingDisconnectionRequest()) {
            return redirect()->route('client.settings', ['tab' => 'service'])->with(
                'success_message',
                'Your disconnection request is already awaiting administrator review.'
            );
        }

        if (! $client->requestDisconnection()) {
            return redirect()->route('client.settings', ['tab' => 'service'])->with(
                'error_message',
                $client->isDisconnected()
                    ? 'Your service has already been disconnected.'
                    : 'You do not have a connected service to disconnect.'
            );
        }

        $planName = $client->currentService?->service_name ?? 'their current service';

        Notification::create([
            'for_admin' => true,
            'client_id' => $client->id,
            'title' => 'Disconnection Request',
            'message' => "{$client->full_name} requested disconnection of {$planName}.",
            'link' => 'admin/clients?filter=disconnection_requests',
        ]);

        return redirect()->route('client.settings', ['tab' => 'service'])->with(
            'success_message',
            'Your disconnection request was sent to the administrator. Your account and subscription history will be kept.'
        );
    }
}
