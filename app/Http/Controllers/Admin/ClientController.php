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

        $query = Client::query();

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

        // Stats (independent of filters)
        $totalClients   = Client::count();
        $newThisMonth   = Client::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        $activeBookings = Appointment::where('status', 'approved')->distinct('client_id')->count('client_id');

        return view('admin.clients.index', compact(
            'clients', 'filter', 'search', 'totalClients', 'newThisMonth', 'activeBookings'
        ));
    }

    /**
     * Return live stats as JSON for real-time polling.
     */
    public function stats()
    {
        return response()->json([
            'total_clients'   => Client::count(),
            'new_this_month'  => Client::whereMonth('created_at', now()->month)
                                       ->whereYear('created_at', now()->year)
                                       ->count(),
            'active_bookings' => Appointment::where('status', 'approved')
                                            ->distinct('client_id')
                                            ->count('client_id'),
        ]);
    }
}
