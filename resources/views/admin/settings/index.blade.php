@extends('layouts.admin')

@section('title', 'Settings - BCTVI Bantayan')

@push('styles')
<style>
    /* ── Page Layout ── */
    .settings-page { display: grid; grid-template-columns: 220px 1fr; gap: 1.5rem; align-items: start; }
    @media (max-width: 900px) { .settings-page { grid-template-columns: 1fr; } }

    /* ── Tab Nav ── */
    .settings-nav { background: var(--bg-card); border: 1px solid var(--border-light); border-radius: 14px; overflow: hidden; position: sticky; top: 1.5rem; }
    .settings-nav-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-light); background: var(--bg-page); }
    .settings-nav-title { font-size: 0.7rem; font-weight: 800; color: var(--text-faint); text-transform: uppercase; letter-spacing: 0.08em; }
    .settings-nav-link { display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem 1.25rem; color: var(--text-muted); font-size: 0.875rem; font-weight: 600; text-decoration: none; border-left: 3px solid transparent; transition: all 0.15s; cursor: pointer; background: none; border-top: none; border-right: none; border-bottom: none; width: 100%; text-align: left; }
    .settings-nav-link:hover { background: var(--bg-page); color: var(--text-dark); }
    .settings-nav-link.active { color: #dc2626; border-left-color: #dc2626; background: #fef2f2; }
    .settings-nav-link svg { flex-shrink: 0; }

    /* ── Panels ── */
    .settings-panel { display: none; flex-direction: column; gap: 1.5rem; }
    .settings-panel.active { display: flex; }

    /* ── Cards ── */
    .settings-card { background: var(--bg-card); border: 1px solid var(--border-light); border-radius: 14px; overflow: hidden; }
    .settings-card-header { padding: 1.1rem 1.5rem; border-bottom: 1px solid var(--border-light); background: var(--bg-page); display: flex; align-items: center; gap: 0.75rem; }
    .settings-card-icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .settings-card-title { font-size: 0.95rem; font-weight: 800; color: var(--text-dark); }
    .settings-card-desc  { font-size: 0.78rem; color: var(--text-muted); margin-top: 0.1rem; }
    .settings-card-body  { padding: 1.5rem; }

    /* ── Form ── */
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    @media (max-width: 640px) { .form-grid { grid-template-columns: 1fr; } }
    .form-group { display: flex; flex-direction: column; gap: 0.4rem; }
    .form-group.full { grid-column: 1 / -1; }
    .form-label { font-size: 0.8rem; font-weight: 700; color: var(--text-body); }
    .form-label .req { color: #dc2626; }
    .form-control { padding: 0.65rem 0.9rem; border: 1px solid var(--border); border-radius: 8px; font-size: 0.88rem; color: var(--text-dark); background: var(--bg-card); transition: border-color 0.15s, box-shadow 0.15s; width: 100%; box-sizing: border-box; }
    .form-control:focus { outline: none; border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220,38,38,0.1); }
    .form-hint { font-size: 0.75rem; color: var(--text-faint); }
    .form-footer { display: flex; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid var(--bg-subtle); margin-top: 1rem; }

    /* ── Buttons ── */
    .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1.25rem; border-radius: 8px; font-size: 0.875rem; font-weight: 700; cursor: pointer; border: none; transition: all 0.15s; text-decoration: none; }
    .btn-primary { background: #dc2626; color: #fff; }
    .btn-primary:hover { background: #b91c1c; }
    .btn-outline { background: var(--bg-subtle); color: var(--text-muted); border: 1px solid var(--border-light); }
    .btn-outline:hover { background: var(--border-light); color: var(--text-dark); }
    .btn-danger  { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .btn-danger:hover  { background: #dc2626; color: #fff; }
    .btn-sm { padding: 0.4rem 0.85rem; font-size: 0.78rem; }
    .btn-success { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
    .btn-success:hover { background: #16a34a; color: #fff; }

    /* ── Toggle Switch ── */
    .toggle-row { display: flex; justify-content: space-between; align-items: center; padding: 1rem 0; border-bottom: 1px solid var(--bg-subtle); }
    .toggle-row:last-child { border-bottom: none; }
    .toggle-info .toggle-label { font-size: 0.875rem; font-weight: 700; color: var(--text-dark); }
    .toggle-info .toggle-desc  { font-size: 0.78rem; color: var(--text-muted); margin-top: 0.1rem; }
    .switch { position: relative; display: inline-block; width: 46px; height: 26px; flex-shrink: 0; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .slider { position: absolute; cursor: pointer; inset: 0; background: #cbd5e1; border-radius: 26px; transition: 0.3s; }
    .slider:before { position: absolute; content: ""; height: 20px; width: 20px; left: 3px; bottom: 3px; background: var(--bg-card); border-radius: 50%; transition: 0.3s; box-shadow: 0 1px 3px rgba(0,0,0,0.2); }
    input:checked + .slider { background: #dc2626; }
    input:checked + .slider:before { transform: translateX(20px); }

    /* ── Admin Table ── */
    .admin-table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
    .admin-table th { padding: 0.75rem 1rem; background: var(--bg-page); border-bottom: 2px solid var(--border-light); color: var(--text-muted); font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; text-align: left; }
    .admin-table td { padding: 0.85rem 1rem; border-bottom: 1px solid var(--bg-subtle); color: var(--text-body); vertical-align: middle; }
    .admin-table tbody tr:last-child td { border-bottom: none; }
    .admin-table tbody tr:hover td { background: #fafafa; }
    .role-badge { display: inline-block; padding: 0.25rem 0.7rem; border-radius: 50px; font-size: 0.72rem; font-weight: 700; }
    .role-super { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .role-admin { background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd; }

    /* ── Page title ── */
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
    .page-title  { font-size: 1.5rem; font-weight: 800; color: var(--text-dark); margin: 0; }

    /* ── Modal ── */
    .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.45); z-index: 2000; align-items: center; justify-content: center; }
    .modal-overlay.open { display: flex; }
    .modal-box { background: var(--bg-card); border-radius: 16px; width: 100%; max-width: 520px; box-shadow: 0 25px 60px rgba(0,0,0,0.18); overflow: hidden; animation: modalIn 0.2s ease; }
    @keyframes modalIn { from { opacity:0; transform:scale(0.95) translateY(-10px); } to { opacity:1; transform:scale(1) translateY(0); } }
    .modal-header { padding: 1.1rem 1.5rem; border-bottom: 1px solid var(--border-light); display: flex; justify-content: space-between; align-items: center; background: var(--bg-page); }
    .modal-title  { font-size: 1rem; font-weight: 800; color: var(--text-dark); }
    .modal-close  { background: none; border: none; cursor: pointer; color: var(--text-muted); display: flex; }
    .modal-close:hover { color: var(--text-dark); }
    .modal-body   { padding: 1.5rem; }
    .modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--bg-subtle); display: flex; justify-content: flex-end; gap: 0.75rem; background: var(--bg-page); }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">Settings</h1>
</div>

<div class="settings-page">

    {{-- ── Tab Navigation ─────────────────────────────────── --}}
    <nav class="settings-nav">
        <div class="settings-nav-header">
            <div class="settings-nav-title">Preferences</div>
        </div>
        <button class="settings-nav-link active" data-tab="profile">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            My Profile
        </button>
        <button class="settings-nav-link" data-tab="password">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            Password
        </button>
        <button class="settings-nav-link" data-tab="system">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
            System
        </button>
        @if(auth('admin')->user()->isSuper())
        <button class="settings-nav-link" data-tab="admins">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            Admin Accounts
        </button>
        @endif
    </nav>

    {{-- ── Panels ─────────────────────────────────────────── --}}
    <div>

        {{-- ── Profile Panel ── --}}
        <div class="settings-panel active" id="panel-profile">
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon" style="background:#fef2f2; color:#dc2626;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div>
                        <div class="settings-card-title">Profile Information</div>
                        <div class="settings-card-desc">Update your name, username and contact email.</div>
                    </div>
                </div>
                <div class="settings-card-body">
                    <form action="{{ route('admin.settings.profile') }}" method="POST">
                        @csrf
                        <div class="form-grid">
                            <div class="form-group">
                                <label class="form-label" for="fullname">Full Name <span class="req">*</span></label>
                                <input id="fullname" type="text" name="fullname" class="form-control" value="{{ old('fullname', $admin->fullname) }}" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="username">Username <span class="req">*</span></label>
                                <input id="username" type="text" name="username" class="form-control" value="{{ old('username', $admin->username) }}" required>
                            </div>
                            <div class="form-group full">
                                <label class="form-label" for="email">Email Address</label>
                                <input id="email" type="email" name="email" class="form-control" value="{{ old('email', $admin->email) }}" placeholder="optional">
                                <span class="form-hint">Used for notifications. Leave blank to skip.</span>
                            </div>
                            <div class="form-group full">
                                <label class="form-label">Role</label>
                                <input type="text" class="form-control" value="{{ $admin->role === 'super_admin' ? 'Super Administrator' : 'Administrator' }}" disabled style="background:var(--bg-page); color:var(--text-muted);">
                                <span class="form-hint">Role can only be changed by a super admin from the Admin Accounts tab.</span>
                            </div>
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Save Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Password Panel ── --}}
        <div class="settings-panel" id="panel-password">
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon" style="background:#eff6ff; color:#2563eb;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </div>
                    <div>
                        <div class="settings-card-title">Change Password</div>
                        <div class="settings-card-desc">Use a strong password of at least 8 characters.</div>
                    </div>
                </div>
                <div class="settings-card-body">
                    <form action="{{ route('admin.settings.password') }}" method="POST">
                        @csrf
                        <div class="form-grid">
                            <div class="form-group full">
                                <label class="form-label" for="current_password">Current Password <span class="req">*</span></label>
                                <input id="current_password" type="password" name="current_password" class="form-control" required autocomplete="current-password">
                                @error('current_password')<span style="color:#dc2626; font-size:0.78rem;">{{ $message }}</span>@enderror
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="new_password">New Password <span class="req">*</span></label>
                                <input id="new_password" type="password" name="new_password" class="form-control" required minlength="8" autocomplete="new-password">
                            </div>
                            <div class="form-group">
                                <label class="form-label" for="new_password_confirmation">Confirm Password <span class="req">*</span></label>
                                <input id="new_password_confirmation" type="password" name="new_password_confirmation" class="form-control" required autocomplete="new-password">
                            </div>
                            <div class="form-group full">
                                <div id="pw-strength-bar" style="height:5px; border-radius:3px; background:var(--border-light); margin-top:0.25rem;">
                                    <div id="pw-strength-fill" style="height:100%; border-radius:3px; width:0%; transition:width 0.3s, background 0.3s;"></div>
                                </div>
                                <span id="pw-strength-label" class="form-hint"></span>
                            </div>
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── System Panel ── --}}
        <div class="settings-panel" id="panel-system">
            <div class="settings-card">
                <div class="settings-card-header">
                    <div class="settings-card-icon" style="background:#f0fdf4; color:#16a34a;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                    </div>
                    <div>
                        <div class="settings-card-title">System Controls</div>
                        <div class="settings-card-desc">Toggle booking, maintenance mode and daily limits.</div>
                    </div>
                </div>
                <div class="settings-card-body">
                    <form action="{{ route('admin.settings.system') }}" method="POST">
                        @csrf

                        <div class="toggle-row">
                            <div class="toggle-info">
                                <div class="toggle-label">Online Booking</div>
                                <div class="toggle-desc">Allow clients to book appointments through the website.</div>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="booking_enabled" {{ $systemSettings['booking_enabled'] ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="toggle-row">
                            <div class="toggle-info">
                                <div class="toggle-label">Maintenance Mode</div>
                                <div class="toggle-desc">Show a maintenance notice to clients on the front-end.</div>
                            </div>
                            <label class="switch">
                                <input type="checkbox" name="maintenance_mode" {{ $systemSettings['maintenance_mode'] ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="toggle-row">
                            <div class="toggle-info">
                                <div class="toggle-label">Max Appointments Per Day</div>
                                <div class="toggle-desc">Maximum number of bookings accepted each day.</div>
                            </div>
                            <input type="number" name="max_daily_bookings" class="form-control" style="width:90px; text-align:center;"
                                   min="1" max="500" value="{{ $systemSettings['max_daily_bookings'] }}">
                        </div>

                        <div class="form-footer" style="margin-top:1.5rem;">
                            <button type="submit" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                Save System Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Admin Accounts Panel ── --}}
        @if(auth('admin')->user()->isSuper())
        <div class="settings-panel" id="panel-admins">
            <div class="settings-card">
                <div class="settings-card-header" style="justify-content: space-between;">
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <div class="settings-card-icon" style="background:#fdf4ff; color:#9333ea;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                        <div>
                            <div class="settings-card-title">Admin Accounts</div>
                            <div class="settings-card-desc">Manage who can log in to the admin panel.</div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openCreateModal()">
                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        New Admin
                    </button>
                </div>
                <div style="overflow-x:auto;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($admins as $a)
                            <tr>
                                <td style="color:var(--text-faint); font-weight:600;">{{ $a->id }}</td>
                                <td style="font-weight:700; color:var(--text-dark);">{{ $a->fullname }}</td>
                                <td style="color:var(--text-muted);">{{ '@' . $a->username }}</td>
                                <td style="color:var(--text-muted);">{{ $a->email ?? '—' }}</td>
                                <td>
                                    <span class="role-badge {{ $a->role === 'super_admin' ? 'role-super' : 'role-admin' }}">
                                        {{ $a->role === 'super_admin' ? 'Super Admin' : 'Admin' }}
                                    </span>
                                </td>
                                <td>
                                    <div style="display:flex; gap:0.4rem;">
                                        <button type="button" class="btn btn-outline btn-sm"
                                            onclick="openEditModal({{ $a->id }}, '{{ addslashes($a->fullname) }}', '{{ addslashes($a->username) }}', '{{ addslashes($a->email ?? '') }}', '{{ $a->role }}')">
                                            Edit
                                        </button>
                                        @if($a->id !== auth('admin')->id())
                                        <form action="{{ route('admin.settings.admins.destroy', $a->id) }}" method="POST"
                                              onsubmit="return confirm('Delete admin {{ addslashes($a->username) }}? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

    </div>{{-- end panels --}}
</div>

{{-- ── Create Admin Modal ── --}}
<div class="modal-overlay" id="modal-create" role="dialog" aria-modal="true" aria-labelledby="modal-create-title">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title" id="modal-create-title">Create Admin Account</div>
            <button type="button" class="modal-close" onclick="closeModal('modal-create')">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form action="{{ route('admin.settings.admins.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="req">*</span></label>
                        <input type="text" name="fullname" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username <span class="req">*</span></label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" placeholder="optional">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role <span class="req">*</span></label>
                        <select name="role" class="form-control">
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Password <span class="req">*</span></label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password <span class="req">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modal-create')">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Account</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Edit Admin Modal ── --}}
<div class="modal-overlay" id="modal-edit" role="dialog" aria-modal="true" aria-labelledby="modal-edit-title">
    <div class="modal-box">
        <div class="modal-header">
            <div class="modal-title" id="modal-edit-title">Edit Admin Account</div>
            <button type="button" class="modal-close" onclick="closeModal('modal-edit')">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form id="edit-admin-form" method="POST">
            @csrf
            @method('PUT')
            <div class="modal-body">
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="req">*</span></label>
                        <input type="text" id="edit-fullname" name="fullname" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username <span class="req">*</span></label>
                        <input type="text" id="edit-username" name="username" class="form-control" required>
                    </div>
                    <div class="form-group full">
                        <label class="form-label">Email</label>
                        <input type="email" id="edit-email" name="email" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role <span class="req">*</span></label>
                        <select id="edit-role" name="role" class="form-control">
                            <option value="admin">Admin</option>
                            <option value="super_admin">Super Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-control" minlength="8" placeholder="Leave blank to keep">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Leave blank to keep">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modal-edit')">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ── Tab switching ──────────────────────────────────────────
document.querySelectorAll('.settings-nav-link[data-tab]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var tab = this.dataset.tab;

        document.querySelectorAll('.settings-nav-link').forEach(function(b) { b.classList.remove('active'); });
        document.querySelectorAll('.settings-panel').forEach(function(p) { p.classList.remove('active'); });

        this.classList.add('active');
        var panel = document.getElementById('panel-' + tab);
        if (panel) panel.classList.add('active');
    });
});

// ── Auto-open tab if there's a validation error ────────────
@if($errors->has('current_password') || $errors->has('new_password'))
    openTab('password');
@endif

function openTab(tab) {
    var btn = document.querySelector('.settings-nav-link[data-tab="' + tab + '"]');
    if (btn) btn.click();
}

// ── Password strength ──────────────────────────────────────
var pwInput = document.getElementById('new_password');
if (pwInput) {
    pwInput.addEventListener('input', function() {
        var val = this.value;
        var score = 0;
        if (val.length >= 8)  score++;
        if (/[A-Z]/.test(val)) score++;
        if (/[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;

        var fill  = document.getElementById('pw-strength-fill');
        var label = document.getElementById('pw-strength-label');
        var colors = ['#ef4444','#f97316','#eab308','#22c55e'];
        var labels = ['Weak','Fair','Good','Strong'];

        fill.style.width   = (score * 25) + '%';
        fill.style.background = colors[score - 1] || '#e2e8f0';
        label.textContent  = score > 0 ? labels[score - 1] : '';
    });
}

// ── Modal helpers ──────────────────────────────────────────
function openCreateModal() {
    document.getElementById('modal-create').classList.add('open');
}

function openEditModal(id, fullname, username, email, role) {
    var form = document.getElementById('edit-admin-form');
    form.action = '/admin/settings/admins/' + id;
    document.getElementById('edit-fullname').value = fullname;
    document.getElementById('edit-username').value = username;
    document.getElementById('edit-email').value    = email;
    document.getElementById('edit-role').value     = role;
    document.getElementById('modal-edit').classList.add('open');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('open');
}

// Close modals on overlay click
document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});

// Close modals on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.open').forEach(function(m) {
            m.classList.remove('open');
        });
    }
});
</script>
@endpush
