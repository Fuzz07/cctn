<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\BillingAccount;
use App\Models\MaintenanceRequest;
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

        return view('client.dashboard', compact(
            'client', 'totalAppointments', 'pendingAppointments',
            'approvedAppointments', 'recentAppointments'
        ));
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

        return redirect()->route('client.dashboard')->with('success_message', 'Profile updated successfully!');
    }

    /**
     * Let a signed-in client deactivate their own account while retaining their
     * booking and payment history for a future re-subscription.
     */
    public function unsubscribe(Request $request)
    {
        if (! Client::supportsArchiving()) {
            return redirect()->route('client.dashboard')->with('error_message',
                'Account unsubscribe is temporarily unavailable. Please contact support.'
            );
        }

        $client = Auth::guard('client')->user();
        $client->update(['archived_at' => now()]);
        $client->tokens()->delete();

        Auth::guard('client')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with(
            'success_message',
            'Your account has been unsubscribed. Your booking and payment history is retained; contact BCTVI support whenever you would like to re-subscribe.'
        );
    }
}
