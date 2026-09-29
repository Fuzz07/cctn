<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Appointment;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    public function proofOfBilling($id)
    {
        $client = Client::findOrFail($id);
        $storedValue = (string) $client->proof_of_billing;
        $urlPath = parse_url($storedValue, PHP_URL_PATH);
        $storedPath = str_replace('\\', '/', ltrim($urlPath ?: $storedValue, '/'));

        $filename = basename($storedPath);
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($storedPath === ''
            || str_contains($storedPath, '..')
            || ! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return redirect()->back()->with('error_message', 'The proof of billing file is unavailable.');
        }

        $withoutPublicPrefix = str_starts_with($storedPath, 'public/')
            ? substr($storedPath, strlen('public/'))
            : $storedPath;
        $storageRelativePath = str_starts_with($withoutPublicPrefix, 'storage/')
            ? substr($withoutPublicPrefix, strlen('storage/'))
            : $withoutPublicPrefix;

        $candidates = array_unique([
            public_path($withoutPublicPrefix),
            Storage::disk('public')->path($storageRelativePath),
            public_path('uploads/proof_of_billing/' . $filename),
            base_path('uploads/proof_of_billing/' . $filename),
            Storage::disk('public')->path('proof_of_billing/' . $filename),
            Storage::disk('public')->path('uploads/proof_of_billing/' . $filename),
        ]);

        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                return response()->file($candidate, [
                    'Content-Disposition' => 'inline; filename="' . basename($candidate) . '"',
                    'Cache-Control' => 'private, no-store, max-age=0',
                    'X-Content-Type-Options' => 'nosniff',
                ]);
            }
        }

        return redirect()->back()->with('error_message', 'The proof of billing file could not be found on the server.');
    }

    public function updateProofOfBilling(Request $request, $id)
    {
        $request->validate([
            'proof_of_billing' => 'required|image|mimes:jpeg,jpg,png,webp|max:5120',
        ], [
            'proof_of_billing.required' => 'Please choose a proof of billing image.',
            'proof_of_billing.image' => 'The proof of billing must be a valid image.',
            'proof_of_billing.max' => 'The proof of billing image must not exceed 5 MB.',
        ]);

        $client = Client::findOrFail($id);
        $oldPath = (string) $client->proof_of_billing;
        $storedPath = $request->file('proof_of_billing')->store('proof_of_billing', 'public');

        if (! $storedPath) {
            return redirect()->back()->with('error_message', 'The proof of billing image could not be saved.');
        }

        $client->update(['proof_of_billing' => 'storage/' . $storedPath]);

        if (str_starts_with($oldPath, 'storage/')) {
            $oldStoragePath = substr($oldPath, strlen('storage/'));
            if ($oldStoragePath !== $storedPath) {
                Storage::disk('public')->delete($oldStoragePath);
            }
        }

        return redirect()->back()->with('success_message', "{$client->full_name}'s proof of billing was updated.");
    }

    public function index(Request $request)
    {
        Client::expireSubscriptions();

        $filter = $request->get('filter', 'all');
        $search = $request->get('search', '');
        $archivingSupported = Client::supportsArchiving();
        $disconnectionRequestsSupported = Client::supportsDisconnectionRequests();

        $query = Client::with('currentService');

        if ($filter === 'disconnection_requests' && $disconnectionRequestsSupported) {
            $query->active()->where('disconnection_request_status', 'pending');
        } elseif ($filter === 'disconnection_requests') {
            $query->whereRaw('1 = 0');
        } elseif (in_array($filter, ['inactive', 'archived'], true)) {
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
        $pendingDisconnectionCount = $disconnectionRequestsSupported
            ? Client::active()->where('disconnection_request_status', 'pending')->count()
            : 0;

        return view('admin.clients.index', compact(
            'clients', 'filter', 'search', 'archivedCount', 'pendingDisconnectionCount',
            'archivingSupported', 'disconnectionRequestsSupported'
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
        if ($client->hasPendingDisconnectionRequest()) {
            $client->approveDisconnection('Approved by administrator.');
        } else {
            $client->deactivateSubscription('cancelled');
        }

        Notification::create([
            'for_admin' => false,
            'client_id' => $client->id,
            'title' => 'Subscription Deactivated',
            'message' => 'Your subscription has been deactivated and your account is now Inactive.',
            'link' => 'dashboard',
        ]);

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

    public function approveDisconnection(Request $request, $id)
    {
        $request->validate(['review_note' => 'nullable|string|max:500']);

        $client = Client::findOrFail($id);
        if (! $client->approveDisconnection($request->input('review_note'))) {
            return redirect()->back()->with('error_message', 'This disconnection request is no longer pending.');
        }

        Notification::create([
            'for_admin' => false,
            'client_id' => $client->id,
            'title' => 'Disconnection Approved',
            'message' => 'Your disconnection request was approved. Your subscription and account are now Inactive.',
            'link' => 'dashboard',
        ]);

        return redirect()->route('admin.clients', ['filter' => 'disconnection_requests'])
            ->with('success_message', "{$client->full_name}'s disconnection request was approved.");
    }

    public function rejectDisconnection(Request $request, $id)
    {
        $request->validate(['review_note' => 'nullable|string|max:500']);

        $client = Client::findOrFail($id);
        if (! $client->rejectDisconnection($request->input('review_note'))) {
            return redirect()->back()->with('error_message', 'This disconnection request is no longer pending.');
        }

        Notification::create([
            'for_admin' => false,
            'client_id' => $client->id,
            'title' => 'Disconnection Request Declined',
            'message' => 'Your disconnection request was declined. Your subscription remains Active.',
            'link' => 'dashboard',
        ]);

        return redirect()->route('admin.clients', ['filter' => 'disconnection_requests'])
            ->with('success_message', "{$client->full_name}'s disconnection request was declined.");
    }
}
