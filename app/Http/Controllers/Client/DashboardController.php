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
            'profile_photo'  => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ], array_merge(InputRules::messages([
            'name'   => ['firstname', 'middlename', 'lastname', 'place_of_birth'],
            'mobile' => ['contact_no'],
        ]), [
            'profile_photo.image' => 'The profile photo must be an image file (JPG, PNG, GIF, or WEBP).',
            'profile_photo.mimes' => 'The profile photo must be a JPG, PNG, GIF, or WEBP image.',
            'profile_photo.max'   => 'The profile photo must not be larger than 5 MB.',
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

        // Handle photo upload
        $photoUpdated = false;
        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');

            if (!$file->isValid()) {
                return back()
                    ->withErrors(['profile_photo' => 'The profile photo failed to upload (' . $file->getErrorMessage() . '). Please try a smaller image.'])
                    ->withInput();
            }

            $oldPhoto = $client->profile_photo;
            $filename = 'avatar_' . uniqid('', true) . '.' . ($file->extension() ?: 'jpg');

            try {
                $file->move(public_path('uploads/profile_photos'), $filename);
            } catch (\Exception $e) {
                return back()
                    ->withErrors(['profile_photo' => 'Your profile photo could not be saved on the server. Please try again or contact BCTVI support.'])
                    ->withInput();
            }

            $data['profile_photo'] = 'uploads/profile_photos/' . $filename;
            $photoUpdated = true;

            // Remove the previous photo so old avatars do not pile up
            if ($oldPhoto && $oldPhoto !== $data['profile_photo'] && is_file(public_path($oldPhoto))) {
                @unlink(public_path($oldPhoto));
            }
        }

        $client->update($data);

        $message = $photoUpdated
            ? 'Profile and photo updated successfully!'
            : 'Profile updated successfully!';

        return redirect()->route('client.dashboard')->with('success_message', $message);
    }
}
