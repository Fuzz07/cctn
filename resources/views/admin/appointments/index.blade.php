@extends('layouts.admin')

@section('title', 'Manage Appointments - BCTVI Bantayan')

@push('styles')
<style>
    .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
    .page-title { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0; }
    
    .filter-card { background: #fff; border-radius: 12px; padding: 1.25rem; border: 1px solid #e2e8f0; margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end; }
    .filter-group { display: flex; flex-direction: column; gap: 0.4rem; min-width: 200px; flex: 1; }
    .filter-label { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
    .filter-input { padding: 0.6rem 0.85rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; width: 100%; box-sizing: border-box; }
    .btn-filter { background: #0f172a; color: #fff; padding: 0.65rem 1.25rem; border-radius: 8px; font-weight: 700; font-size: 0.9rem; border: none; cursor: pointer; white-space: nowrap; }
    .btn-filter:hover { background: #1e293b; }

    .table-card { background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow-x: auto; }
    .data-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem; }
    .data-table th { padding: 1rem; background: #f8fafc; border-bottom: 2px solid #e2e8f0; color: #64748b; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; }
    .data-table td { padding: 1rem; border-bottom: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
    .data-table tbody tr:hover { background: #f8fafc; }

    .badge { padding: 0.35rem 0.75rem; border-radius: 99px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: inline-block; }
    .badge-pending { background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; }
    .badge-approved { background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }
    .badge-cancelled { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }

    .action-links { display: flex; gap: 0.75rem; align-items: center; }
    .btn-sm { padding: 0.4rem 0.8rem; border-radius: 6px; font-size: 0.8rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 0.25rem; border: 1px solid transparent; cursor: pointer; }
    .btn-primary { background: #eff6ff; color: #2563eb; border-color: #bfdbfe; }
    .btn-primary:hover { background: #dbeafe; }
    
    /* Modal Styles */
    .modal-overlay { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 100; opacity: 0; pointer-events: none; transition: opacity 0.2s; }
    .modal-overlay.active { opacity: 1; pointer-events: auto; }
    .modal-content { background: #fff; width: 100%; max-width: 600px; border-radius: 16px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); transform: scale(0.95); transition: transform 0.2s; max-height: 90vh; overflow-y: auto; }
    .modal-overlay.active .modal-content { transform: scale(1); }
    
    .modal-header { padding: 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
    .modal-title { font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0; }
    .btn-close { background: none; border: none; color: #94a3b8; cursor: pointer; padding: 0.5rem; }
    .btn-close:hover { color: #0f172a; }
    
    .modal-body { padding: 1.5rem; }
    .modal-footer { padding: 1.25rem 1.5rem; border-top: 1px solid #e2e8f0; background: #f8fafc; border-radius: 0 0 16px 16px; display: flex; justify-content: flex-end; gap: 0.75rem; }
    
    .detail-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem; }
    .detail-item { display: flex; flex-direction: column; gap: 0.25rem; }
    .detail-label { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; }
    .detail-value { font-size: 0.95rem; font-weight: 600; color: #0f172a; }
    
    .form-group { margin-bottom: 1.25rem; }
    .form-label { display: block; font-size: 0.85rem; font-weight: 700; color: #334155; margin-bottom: 0.5rem; }
    .form-control { width: 100%; padding: 0.75rem; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; box-sizing: border-box; }
    .form-control:focus { outline: none; border-color: #dc2626; box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.1); }
    
    .btn-save { background: #0f172a; color: #fff; padding: 0.65rem 1.25rem; border-radius: 8px; font-weight: 700; font-size: 0.9rem; border: none; cursor: pointer; }
    .btn-save:hover { background: #1e293b; }
    .btn-cancel { background: #fff; color: #64748b; padding: 0.65rem 1.25rem; border-radius: 8px; font-weight: 600; font-size: 0.9rem; border: 1px solid #e2e8f0; cursor: pointer; text-decoration: none; }
    .btn-cancel:hover { background: #f1f5f9; color: #0f172a; }

</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">Appointment & Installation Management</h1>
    <a href="{{ route('admin.walkin.create') }}" class="btn-filter" style="background:#dc2626; color:#ffffff; text-decoration:none; display:inline-flex; align-items:center; gap:0.5rem; padding:0.75rem 1.25rem; font-size:0.95rem; border-radius:10px;">
        ➕ New Walk-In Client Booking
    </a>
</div>

@if (session('success_message'))
    <div style="background:#dcfce7; color:#15803d; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 500; border: 1px solid #bbf7d0;">
        {{ session('success_message') }}
    </div>
@endif
@if ($errors->any())
    <div style="background:#fef2f2; color:#b91c1c; padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 500; border: 1px solid #fecaca;">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form action="{{ route('admin.appointments') }}" method="GET" class="filter-card">
    <div class="filter-group">
        <label class="filter-label">Status</label>
        <select name="status" class="filter-input">
            <option value="all" {{ $filterStatus == 'all' ? 'selected' : '' }}>All Statuses</option>
            <option value="pending" {{ $filterStatus == 'pending' ? 'selected' : '' }}>Pending</option>
            <option value="approved" {{ $filterStatus == 'approved' ? 'selected' : '' }}>Approved</option>
            <option value="cancelled" {{ $filterStatus == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
    </div>
    <div class="filter-group">
        <label class="filter-label">Service</label>
        <select name="service_id" class="filter-input">
            <option value="0">All Services</option>
            @foreach($services as $serv)
                <option value="{{ $serv->id }}" {{ $filterService == $serv->id ? 'selected' : '' }}>
                    {{ $serv->service_name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="filter-group">
        <label class="filter-label">Date</label>
        <input type="date" name="date" class="filter-input" value="{{ $filterDate }}">
    </div>
    <button type="submit" class="btn-filter">Apply Filters</button>
    <a href="{{ route('admin.appointments') }}" class="btn-filter" style="background:#f1f5f9; color:#64748b; text-decoration:none; text-align:center;">Clear</a>
</form>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Ref ID</th>
                <th>Client Info</th>
                <th>Service & Schedule</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($appointments as $appt)
                <tr>
                    <td><strong style="color: #64748b; font-family: monospace;">#{{ str_pad($appt->id, 5, '0', STR_PAD_LEFT) }}</strong></td>
                    <td>
                        <div style="font-weight:700; color:#0f172a;">{{ $appt->client->firstname }} {{ $appt->client->lastname }}</div>
                        <div style="font-size:0.8rem; color:#64748b;">{{ $appt->client->contact_no }} &middot; {{ $appt->client->address_barangay }}</div>
                    </td>
                    <td>
                        <div style="font-weight:600; color:#0f172a;">{{ $appt->service->service_name }}</div>
                        <div style="font-size:0.85rem; color:#dc2626; font-weight:700;">{{ date('M d, Y', strtotime($appt->preferred_date)) }} @ {{ date('g:i A', strtotime($appt->preferred_time)) }}</div>
                    </td>
                    <td>
                        <span class="badge badge-{{ $appt->status }}">{{ $appt->status }}</span>
                    </td>
                    <td>
                        <div class="action-links">
                            <a href="{{ request()->fullUrlWithQuery(['manage_id' => $appt->id]) }}" class="btn-sm btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Manage
                            </a>
                            
                            @if($appt->status == 'pending')
                                <form action="{{ route('admin.appointments.quick_update') }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="appointment_id" value="{{ $appt->id }}">
                                    <input type="hidden" name="status" value="approved">
                                    <button type="submit" class="btn-sm" style="background:#dcfce7; color:#15803d; border-color:#bbf7d0;">
                                        Approve
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; padding: 3rem; color: #94a3b8;">
                        No appointments found matching your criteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Manage Appointment Modal -->
@if($manageAppointment)
    <div class="modal-overlay active">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Manage Appointment #{{ str_pad($manageAppointment->id, 5, '0', STR_PAD_LEFT) }}</h3>
                <a href="{{ route('admin.appointments') }}" class="btn-close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </a>
            </div>
            <form action="{{ route('admin.appointments.update') }}" method="POST">
                @csrf
                <input type="hidden" name="appointment_id" value="{{ $manageAppointment->id }}">
                <div class="modal-body">
                    
                    <div class="detail-grid">
                        <div class="detail-item">
                            <span class="detail-label">Client Name</span>
                            <span class="detail-value">{{ $manageAppointment->client->firstname }} {{ $manageAppointment->client->lastname }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Contact</span>
                            <span class="detail-value">{{ $manageAppointment->client->contact_no }}<br>{{ $manageAppointment->client->email }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Address</span>
                            <span class="detail-value">{{ $manageAppointment->client->address_barangay }}, {{ $manageAppointment->client->address_municipality }}</span>
                        </div>
                        <div class="detail-item">
                            <span class="detail-label">Service</span>
                            <span class="detail-value">{{ $manageAppointment->service->service_name }} (₱{{ number_format($manageAppointment->service->price, 2) }})</span>
                        </div>
                    </div>
                    
                    @if($manageAppointment->message)
                        <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #e2e8f0;">
                            <span class="detail-label" style="margin-bottom: 0.5rem; display:block;">Client Note:</span>
                            <span class="detail-value" style="font-weight: 500; font-size: 0.9rem;">{{ $manageAppointment->message }}</span>
                        </div>
                    @endif

                    <h4 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0 0 1rem; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.5rem;">Update Scheduling</h4>
                    
                    <div class="detail-grid" style="margin-bottom: 0;">
                        <div class="form-group">
                            <label class="form-label">Preferred Date</label>
                            <input type="date" name="preferred_date" class="form-control" value="{{ old('preferred_date', $manageAppointment->preferred_date) }}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Preferred Time</label>
                            <input type="time" name="preferred_time" class="form-control" value="{{ old('preferred_time', $manageAppointment->preferred_time) }}" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control" required>
                            <option value="pending" {{ $manageAppointment->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="approved" {{ $manageAppointment->status == 'approved' ? 'selected' : '' }}>Approved</option>
                            <option value="cancelled" {{ $manageAppointment->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="form-group" style="margin-bottom: 0;">
                        <label class="form-label">Admin Notes (Optional)</label>
                        <textarea name="admin_notes" class="form-control" rows="2" placeholder="Internal notes or reason for cancellation...">{{ old('admin_notes', $manageAppointment->admin_notes) }}</textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <a href="{{ route('admin.appointments') }}" class="btn-cancel">Cancel</a>
                    <button type="submit" class="btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endif

@endsection
