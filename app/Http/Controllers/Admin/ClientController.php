<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Appointment;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        Client::expireSubscriptions();

        $filter = $request->get('filter', 'all');
        $search = $request->get('search', '');
        $archivingSupported = Client::supportsArchiving();

        $query = Client::with('currentService');

        if (in_array($filter, ['inactive', 'archived'], true)) {
            $query->inactive();
        } else {
            $query->active();
        }

        if ($filter === 'active_bookings') {
            $query->active()->whereIn('id', Appointment::where('status', 'approved')->distinct()->pluck('client_id'));
        } elseif ($filter === 'new_this_month') {
            $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                  ->orWhere('lastname', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('contact_no', 'like', "%{$search}%")
                  ->orWhere('address_barangay', 'like', "%{$search}%")
                  ->orWhere('address_municipality', 'like', "%{$search}%");
            });
        }

        $clients = $query->orderBy('id', 'desc')->get();

        $archivedCount = Client::inactive()->count();

        return view('admin.clients.index', compact(
            'clients', 'filter', 'search', 'archivedCount', 'archivingSupported'
        ));
    }

    /** Cancel the client's current subscription and mark the account inactive. */
    public function archive($id)
    {
        if (!Client::supportsArchiving()) {
            return redirect()->route('admin.clients')->with('error_message',
                'Customer unsubscribe management is unavailable until the database migration is run.'
            );
        }

        $client = Client::findOrFail($id);
        $client->deactivateSubscription('cancelled');

        return redirect()->back()->with('success_message', "{$client->full_name}'s subscription has been cancelled and the account is now Inactive.");
    }

    public function restore($id)
    {
        if (!Client::supportsArchiving()) {
            return redirect()->route('admin.clients')->with('error_message',
                'Customer unsubscribe management is unavailable until the database migration is run.'
            );
        }

        $client = Client::with(['currentService', 'currentAppointment'])->findOrFail($id);

        if (! $client->currentService || ! $client->currentAppointment) {
            $client->update(['archived_at' => null]);

            return redirect()->back()->with(
                'success_message',
                "{$client->full_name}'s account was restored but remains Inactive until a plan is approved."
            );
        }

        $client->activateSubscription(
            $client->currentService,
            $client->currentAppointment,
            $client->currentAppointment->subscription_ends_at,
        );

        return redirect()->back()->with('success_message', "{$client->full_name} has been re-subscribed and the account is now Active.");
    }
}
