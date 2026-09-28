<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    // ─── Settings Page ─────────────────────────────────────────────────────────

    public function index()
    {
        $admin   = Auth::guard('admin')->user();
        $admins  = Admin::orderBy('id')->get();

        // Simple key-value settings stored in Laravel config / .env helpers
        $systemSettings = [
            'booking_enabled'    => config('cctn.booking_enabled', true),
            'maintenance_mode'   => config('cctn.maintenance_mode', false),
            'max_daily_bookings' => config('cctn.max_daily_bookings', 10),
        ];

        return view('admin.settings.index', compact('admin', 'admins', 'systemSettings'));
    }

    // ─── Profile ───────────────────────────────────────────────────────────────

    public function updateProfile(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'fullname' => 'required|string|max:100',
            'username' => ['required', 'string', 'max:50', Rule::unique('admins')->ignore($admin->id)],
            'email'    => ['nullable', 'email', 'max:150', Rule::unique('admins')->ignore($admin->id)],
        ]);

        $admin->update($request->only('fullname', 'username', 'email'));

        return redirect()->route('admin.settings')->with('success_message', 'Profile updated successfully.');
    }

    // ─── Password ──────────────────────────────────────────────────────────────

    public function updatePassword(Request $request)
    {
        $admin = Auth::guard('admin')->user();

        $request->validate([
            'current_password'      => 'required|string',
            'new_password'          => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, $admin->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
        }

        $admin->update(['password' => Hash::make($request->new_password)]);

        return redirect()->route('admin.settings')->with('success_message', 'Password changed successfully.');
    }

    // ─── System Settings ────────────────────────────────────────────────────────

    public function updateSystem(Request $request)
    {
        $request->validate([
            'max_daily_bookings' => 'required|integer|min:1|max:500',
        ]);

        $envPath = base_path('.env');
        $env     = file_get_contents($envPath);

        $bookingEnabled  = $request->has('booking_enabled')  ? 'true' : 'false';
        $maintenanceMode = $request->has('maintenance_mode') ? 'true' : 'false';
        $maxBookings     = (int) $request->max_daily_bookings;

        $env = $this->setEnvValue($env, 'BOOKING_ENABLED',    $bookingEnabled);
        $env = $this->setEnvValue($env, 'MAINTENANCE_MODE',   $maintenanceMode);
        $env = $this->setEnvValue($env, 'MAX_DAILY_BOOKINGS', $maxBookings);

        if (file_put_contents($envPath, $env, LOCK_EX) === false) {
            return back()->withErrors([
                'system_settings' => 'System settings could not be saved. Please check that the .env file is writable.',
            ]);
        }

        // A cached config file ignores new .env values. Remove it so the next
        // request immediately uses the settings just saved in this panel.
        $cachedConfig = bootstrap_path('cache/config.php');
        if (is_file($cachedConfig) && !unlink($cachedConfig)) {
            return back()->withErrors([
                'system_settings' => 'Settings were saved, but the configuration cache could not be cleared. Please make bootstrap/cache writable.',
            ]);
        }

        // Keep this request consistent too; the redirect will reload from .env.
        config([
            'cctn.booking_enabled' => $bookingEnabled === 'true',
            'cctn.maintenance_mode' => $maintenanceMode === 'true',
            'cctn.max_daily_bookings' => $maxBookings,
        ]);

        return redirect()->route('admin.settings')->with('success_message', 'System settings saved.');
    }

    // ─── Sub-Admin Management ──────────────────────────────────────────────────

    public function storeAdmin(Request $request)
    {
        $this->requireSuperAdmin();

        $request->validate([
            'fullname' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:admins,username',
            'email'    => 'nullable|email|max:150|unique:admins,email',
            'role'     => 'required|in:admin,super_admin',
            'password' => 'required|string|min:8|confirmed',
        ]);

        Admin::create([
            'fullname' => $request->fullname,
            'username' => $request->username,
            'email'    => $request->email,
            'role'     => $request->role,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('admin.settings')->with('success_message', 'Admin account created successfully.');
    }

    public function updateAdmin(Request $request, $id)
    {
        $this->requireSuperAdmin();

        $target = Admin::findOrFail($id);

        $request->validate([
            'fullname' => 'required|string|max:100',
            'username' => ['required', 'string', 'max:50', Rule::unique('admins')->ignore($id)],
            'email'    => ['nullable', 'email', 'max:150', Rule::unique('admins')->ignore($id)],
            'role'     => 'required|in:admin,super_admin',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        $data = $request->only('fullname', 'username', 'email', 'role');
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $target->update($data);

        return redirect()->route('admin.settings')->with('success_message', "Admin '{$target->username}' updated.");
    }

    public function destroyAdmin($id)
    {
        $this->requireSuperAdmin();

        $me     = Auth::guard('admin')->user();
        $target = Admin::findOrFail($id);

        if ($me->id === $target->id) {
            return redirect()->route('admin.settings')->with('error_message', 'You cannot delete your own account.');
        }

        $target->delete();

        return redirect()->route('admin.settings')->with('success_message', "Admin '{$target->username}' deleted.");
    }

    // ─── Helpers ───────────────────────────────────────────────────────────────

    private function requireSuperAdmin()
    {
        if (!Auth::guard('admin')->user()->isSuper()) {
            abort(403, 'Only super admins can manage accounts.');
        }
    }

    private function setEnvValue(string $env, string $key, $value): string
    {
        if (preg_match("/^{$key}=.*/m", $env)) {
            return preg_replace("/^{$key}=.*/m", "{$key}={$value}", $env);
        }
        return $env . "\n{$key}={$value}";
    }
}
