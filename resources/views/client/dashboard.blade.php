@extends('layouts.app')

@section('title', 'Client Dashboard')

@push('styles')
<style>
    body { background-color: #f8fafc; color: #0f172a; font-family: system-ui, -apple-system, sans-serif; }
    
    .c-dash-container { max-width: 1280px; margin: 2rem auto; padding: 0 1.5rem; }

    /* Welcome Header */
    .c-dash-welcome { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem; }
    .c-dash-welcome h1 { font-size: 1.65rem; font-weight: 800; color: #0f172a; margin: 0; line-height: 1.2; }
    .c-dash-welcome p { color: #64748b; margin: 0.25rem 0 0 0; font-size: 0.9rem; }
    .btn-new-appt { background: #dc2626; color: #ffffff !important; padding: 0.75rem 1.35rem; border-radius: 12px; font-weight: 700; font-size: 0.9rem; text-decoration: none; display: inline-flex; align-items: center; gap: 0.5rem; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.3); transition: all 0.2s ease; }
    .btn-new-appt:hover { background: #b91c1c; transform: translateY(-1px); box-shadow: 0 6px 18px rgba(220, 38, 38, 0.4); }

    /* Stat Cards Grid */
    .c-stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 1.25rem; margin-bottom: 2rem; }
    .c-stat-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.25rem 1.5rem; display: flex; align-items: center; gap: 1.1rem; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03); text-decoration: none; transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s; cursor: pointer; }
    .c-stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06); border-color: #cbd5e1; }
    .c-stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .c-stat-title { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin-bottom: 0.2rem; }
    .c-stat-val { font-size: 1.6rem; font-weight: 800; color: #0f172a; line-height: 1; }

    /* Main Workspace Split */
    .c-dash-workspace { display: grid; grid-template-columns: 1fr 340px; gap: 1.75rem; }

    /* Cards Styling */
    .c-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 1.75rem; box-shadow: 0 4px 16px rgba(0,0,0,0.03); margin-bottom: 1.75rem; }
    .c-card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
    .c-card-title { font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0; display: flex; align-items: center; gap: 0.5rem; }
    .c-card-link { color: #dc2626; font-weight: 700; font-size: 0.85rem; text-decoration: none; }
    .c-card-link:hover { text-decoration: underline; }

    /* Table Styling */
    .c-table-wrap { overflow-x: auto; }
    .c-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem; }
    .c-table th { background: #f8fafc; padding: 0.75rem 1rem; font-weight: 700; color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e2e8f0; }
    .c-table td { padding: 1rem; border-bottom: 1px solid #f1f5f9; color: #334155; }
    .c-table tr:last-child td { border-bottom: none; }

    /* Status Pills */
    .status-pill { padding: 0.35rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 700; text-transform: capitalize; display: inline-block; }
    .status-pill.pending { background: #fef3c7; color: #d97706; }
    .status-pill.approved { background: #dcfce7; color: #16a34a; }
    .status-pill.cancelled { background: #fee2e2; color: #dc2626; }

    /* Form Fields inside Edit Profile */
    .c-form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .c-form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; }
    .c-field { margin-bottom: 1rem; display: flex; flex-direction: column; gap: 0.35rem; }
    .c-label { font-size: 0.82rem; font-weight: 700; color: #334155; }
    .c-input { padding: 0.65rem 0.85rem; border: 1px solid #cbd5e1; border-radius: 10px; font-size: 0.88rem; color: #0f172a; background: #f8fafc; width: 100%; box-sizing: border-box; }
    .c-input:focus { outline: none; border-color: #dc2626; background: #ffffff; box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1); }
    .btn-save-profile { background: #0f172a; color: #ffffff; padding: 0.7rem 1.25rem; border-radius: 10px; font-weight: 700; font-size: 0.88rem; border: none; cursor: pointer; transition: background 0.2s; margin-top: 1rem; display: inline-block; }
    .btn-save-profile:hover { background: #dc2626; }

    /* Right Profile Card */
    .c-profile-card { text-align: center; }
    .c-avatar-img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid #ffffff; box-shadow: 0 8px 20px rgba(0,0,0,0.1); margin-bottom: 1rem; }
    .c-profile-name { font-size: 1.2rem; font-weight: 800; color: #0f172a; margin: 0; }
    .c-profile-user { font-size: 0.82rem; font-weight: 700; color: #dc2626; margin: 0.2rem 0 1.25rem 0; }

    .c-info-list { border-top: 1px solid #e2e8f0; padding-top: 1.25rem; text-align: left; display: flex; flex-direction: column; gap: 1rem; }
    .c-info-item span { font-size: 0.72rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 0.15rem; }
    .c-info-item strong { font-size: 0.88rem; color: #1e293b; font-weight: 600; }

    @media (max-width: 1024px) {
        .c-stats-grid { grid-template-columns: repeat(2, 1fr); }
        .c-dash-workspace { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .c-stats-grid { grid-template-columns: 1fr; }
        .c-form-grid-2, .c-form-grid-3 { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="c-dash-container">
    <div class="c-dash-welcome">
        <div>
            <h1>Welcome back, {{ $client->firstname }}! 👋</h1>
            <p>Manage your profile, monitor booking requests, and view service details.</p>
        </div>
        <a href="{{ route('client.book') }}" class="btn-new-appt">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            Book New Appointment
        </a>
    </div>

    <!-- Stat Cards -->
    <div class="c-stats-grid">
        <a href="{{ route('client.appointments', ['status' => 'all']) }}" class="c-stat-card">
            <div class="c-stat-icon" style="background:#fef2f2; color:#dc2626;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
            </div>
            <div>
                <div class="c-stat-title">Total Bookings</div>
                <div class="c-stat-val">{{ $totalAppointments }}</div>
            </div>
        </a>

        <a href="{{ route('client.appointments', ['status' => 'pending']) }}" class="c-stat-card">
            <div class="c-stat-icon" style="background:#fff7ed; color:#ea580c;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </div>
            <div>
                <div class="c-stat-title">Pending</div>
                <div class="c-stat-val">{{ $pendingAppointments }}</div>
            </div>
        </a>

        <a href="{{ route('client.appointments', ['status' => 'approved']) }}" class="c-stat-card">
            <div class="c-stat-icon" style="background:#f0fdf4; color:#16a34a;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
                <div class="c-stat-title">Approved</div>
                <div class="c-stat-val">{{ $approvedAppointments }}</div>
            </div>
        </a>

        <a href="{{ route('client.appointments', ['status' => 'cancelled']) }}" class="c-stat-card">
            <div class="c-stat-icon" style="background:#fef2f2; color:#ef4444;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </div>
            <div>
                <div class="c-stat-title">Cancelled</div>
                <div class="c-stat-val">{{ $recentAppointments->where('status','cancelled')->count() }}</div>
            </div>
        </a>
    </div>

    <!-- Main Workspace Split -->
    <div class="c-dash-workspace">
        
        <!-- Left Side -->
        <div>
            <!-- Recent Appointments Table -->
            <div class="c-card">
                <div class="c-card-header">
                    <h3 class="c-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        Recent Appointments
                    </h3>
                    <a href="{{ route('client.appointments') }}" class="c-card-link">View History &rarr;</a>
                </div>

                <div class="c-table-wrap">
                    <table class="c-table">
                        <thead>
                            <tr>
                                <th>Date & Time</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Ref #</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentAppointments as $appt)
                                <tr>
                                    <td>
                                        <div style="font-weight:700; color:#0f172a;">{{ date('M d, Y', strtotime($appt->preferred_date)) }}</div>
                                        <div style="font-size:0.8rem; color:#64748b;">{{ date('g:i A', strtotime($appt->preferred_time)) }}</div>
                                    </td>
                                    <td>
                                        <div style="font-weight:600;">{{ $appt->service->service_name }}</div>
                                        <div style="font-size:0.8rem; color:#64748b;">~{{ $appt->service->duration_minutes }} mins</div>
                                    </td>
                                    <td>
                                        <span class="status-pill {{ $appt->status }}">{{ ucfirst($appt->status) }}</span>
                                    </td>
                                    <td style="font-family:monospace; font-size:0.85rem; color:#94a3b8;">
                                        #{{ str_pad($appt->id, 5, '0', STR_PAD_LEFT) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" style="text-align:center; padding: 2rem; color: #94a3b8;">
                                        No recent appointments found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Edit Profile Form -->
            <div class="c-card" id="edit-profile">
                <div class="c-card-header">
                    <h3 class="c-card-title">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#0f172a" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit Profile
                    </h3>
                </div>

                <form action="{{ route('client.update-profile') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="c-form-grid-3">
                        <div class="c-field">
                            <label class="c-label">First Name</label>
                            <input type="text" name="firstname" class="c-input" value="{{ old('firstname', $client->firstname) }}" required>
                        </div>
                        <div class="c-field">
                            <label class="c-label">Middle Name</label>
                            <input type="text" name="middlename" class="c-input" value="{{ old('middlename', $client->middlename) }}">
                        </div>
                        <div class="c-field">
                            <label class="c-label">Last Name</label>
                            <input type="text" name="lastname" class="c-input" value="{{ old('lastname', $client->lastname) }}" required>
                        </div>
                    </div>

                    <div class="c-form-grid-2">
                        <div class="c-field">
                            <label class="c-label">Email Address</label>
                            <input type="email" name="email" class="c-input" value="{{ old('email', $client->email) }}" required>
                        </div>
                        <div class="c-field">
                            <label class="c-label">Username</label>
                            <input type="text" name="username" class="c-input" value="{{ old('username', $client->username) }}" required>
                        </div>
                    </div>

                    <div class="c-form-grid-3">
                        <div class="c-field">
                            <label class="c-label">Contact No.</label>
                            <input type="text" name="contact_no" class="c-input" value="{{ old('contact_no', $client->contact_no) }}" required>
                        </div>
                        <div class="c-field">
                            <label class="c-label">Birth Date</label>
                            <input type="date" name="birthdate" class="c-input" value="{{ old('birthdate', $client->birthdate) }}" required>
                        </div>
                        <div class="c-field">
                            <label class="c-label">Gender</label>
                            <select name="gender" class="c-input" required>
                                <option value="Male" {{ old('gender', $client->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                <option value="Female" {{ old('gender', $client->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                            </select>
                        </div>
                    </div>

                    <div class="c-form-grid-2">
                        <div class="c-field">
                            <label class="c-label">Civil Status</label>
                            <select name="civil_status" class="c-input" required>
                                <option value="Single" {{ old('civil_status', $client->civil_status) == 'Single' ? 'selected' : '' }}>Single</option>
                                <option value="Married" {{ old('civil_status', $client->civil_status) == 'Married' ? 'selected' : '' }}>Married</option>
                                <option value="Widowed" {{ old('civil_status', $client->civil_status) == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                <option value="Legally Separated" {{ old('civil_status', $client->civil_status) == 'Legally Separated' ? 'selected' : '' }}>Legally Separated</option>
                            </select>
                        </div>
                        <div class="c-field">
                            <label class="c-label">Place of Birth</label>
                            <input type="text" name="place_of_birth" class="c-input" value="{{ old('place_of_birth', $client->place_of_birth) }}">
                        </div>
                    </div>

                    <div class="c-form-grid-3">
                        <div class="c-field">
                            <label class="c-label">Barangay</label>
                            <select name="address_barangay" class="c-input" required>
                                @php
                                    $brgys = ['Atop-atop','Baigad','Bantigue','Baod','Binaobao','Botigues','Doong','Guiwanon','Hilotongan','Kabac','Kabangbang','Kampingganon','Kangkaibe','Lipayran','Luyongbaybay','Mojon','Obo-ob','Patao','Puting Bato','Sillion','Suba','Sulangan','Sungko','Ticad'];
                                @endphp
                                @foreach ($brgys as $brgy)
                                    <option value="{{ $brgy }}" {{ old('address_barangay', $client->address_barangay) == $brgy ? 'selected' : '' }}>{{ $brgy }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="c-field">
                            <label class="c-label">Municipality</label>
                            <input type="text" name="address_municipality" class="c-input" value="Bantayan" readonly>
                        </div>
                        <div class="c-field">
                            <label class="c-label">Province</label>
                            <input type="text" name="address_province" class="c-input" value="Cebu" readonly>
                        </div>
                    </div>

                    <hr style="border:0; border-top:1px solid #e2e8f0; margin: 1.5rem 0;">

                    <div class="c-form-grid-2">
                        <div class="c-field">
                            <label class="c-label">New Password <span style="font-weight:400; color:#94a3b8;">(Leave blank to keep current)</span></label>
                            <input type="password" name="new_password" class="c-input" placeholder="••••••••">
                        </div>
                        <div class="c-field">
                            <label class="c-label">Update Profile Photo</label>
                            <input type="file" name="profile_photo" class="c-input" accept="image/png, image/jpeg, image/gif, image/webp" style="padding: 0.4rem 0.85rem;">
                            <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 0.35rem;">JPG, PNG, GIF, or WEBP &middot; up to 5 MB</div>
                        </div>
                    </div>

                    <div style="text-align: right; margin-top: 1rem;">
                        <button type="submit" name="update_profile" class="btn-save-profile">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Right Side: Profile Summary -->
        <div>
            <div class="c-card c-profile-card">
                <img src="{{ $client->profile_photo ? asset($client->profile_photo) : asset('assets/img/default-avatar.svg') }}" alt="Profile Photo" class="c-avatar-img">
                <h2 class="c-profile-name">{{ $client->firstname }} {{ $client->lastname }}</h2>
                <p class="c-profile-user">{{ '@' . $client->username }}</p>

                <div class="c-info-list">
                    <div class="c-info-item">
                        <span>Email</span>
                        <strong>{{ $client->email }}</strong>
                    </div>
                    <div class="c-info-item">
                        <span>Phone</span>
                        <strong>{{ $client->contact_no }}</strong>
                    </div>
                    <div class="c-info-item">
                        <span>Address</span>
                        <strong>{{ $client->address_barangay }}, {{ $client->address_municipality }}</strong>
                    </div>
                    <div class="c-info-item">
                        <span>Member Since</span>
                        <strong>{{ $client->created_at->format('M d, Y') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
