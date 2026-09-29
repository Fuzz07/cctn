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
        $filter = $request->get('filter', 'all');
        $search = $request->get('search', '');
        $archivingSupported = Client::supportsArchiving();

        // Archived clients only appear under the "Archived" filter.
        $query = $filter === 'archived' ? Client::archived() : Client::active();

        if ($filter === 'active_bookings') {
            $query->whereIn('id', Appointment::where('status', 'approved')->distinct()->pluck('client_id'));
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

        $archivedCount  = Client::archived()->count();

        return view('admin.clients.index', compact(
            'clients', 'filter', 'search', 'archivedCount', 'archivingSupported'
        ));
    }

    /**
     * Archive a client: hide them from the active list and block sign-in.
     * Their bookings, billing and payment history are kept.
     */
    public function archive($id)
    {
        if (!Client::supportsArchiving()) {
            return redirect()->route('admin.clients')->with('error_message',
                'Customer unsubscribe management is unavailable until the database migration is run.'
            );
        }

        $client = Client::findOrFail($id);
        $client->update(['archived_at' => now()]);

        // Sign the client out of the mobile app immediately.
        $client->tokens()->delete();

        return redirect()->back()->with('success_message', "{$client->full_name} has been unsubscribed.");
    }

    public function restore($id)
    {
        if (!Client::supportsArchiving()) {
            return redirect()->route('admin.clients')->with('error_message',
                'Customer unsubscribe management is unavailable until the database migration is run.'
            );
        }

        $client = Client::findOrFail($id);
        $client->update(['archived_at' => null]);

        return redirect()->back()->with('success_message', "{$client->full_name} has been re-subscribed and returned to the registered clients list.");
    }
}
