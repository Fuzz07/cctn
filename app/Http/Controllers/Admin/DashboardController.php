<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        Client::expireSubscriptions();

        $admin = Auth::guard('admin')->user();

        $stats = [
            'clients'  => Client::count(),
            'active_clients' => Client::active()->count(),
            'inactive_clients' => Client::inactive()->count(),
            'disconnection_requests' => Client::supportsDisconnectionRequests()
                ? Client::active()->where('disconnection_request_status', 'pending')->count()
                : 0,
            'total'    => Appointment::count(),
            'pending'  => Appointment::where('status', 'pending')->count(),
            'approved' => Appointment::where('status', 'approved')->count(),
            'services' => Service::where('status', 'Active')->count(),
        ];

        $recentBookings = Appointment::with(['client.currentService', 'service'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('admin', 'stats', 'recentBookings'));
    }

    public function quickUpdateStatus(Request $request)
    {
        $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'status'         => 'required|in:approved,cancelled',
        ]);

        $appointment = Appointment::findOrFail($request->appointment_id);
        $appointment->update([
            'status'      => $request->status,
            'admin_notes' => $request->input('admin_notes', ''),
        ]);

        return redirect()->route('admin.dashboard')
            ->with('success_message', "Appointment #{$appointment->id} status set to {$request->status}.");
    }
}
