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

    /* Compact table proof indicators */
    .tbl-pay-cell { font-size: 0.82rem; }
    .tbl-pay-method { font-weight: 700; color: #0f172a; font-size: 0.82rem; }
    .tbl-pay-ref { font-family: monospace; font-size: 0.78rem; color: #64748b; font-weight: 600; }
    .tbl-proof-thumb {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-top: 0.35rem;
        padding: 0.2rem 0.55rem;
        border-radius: 5px;
        font-size: 0.72rem;
        font-weight: 700;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.15s;
    }
    .tbl-proof-thumb.has-proof {
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
    }
    .tbl-proof-thumb.has-proof:hover {
        background: #dcfce7;
    }
    .tbl-proof-thumb.no-proof {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }

    .badge-paid { background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }
    .badge-unpaid { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }
    .badge-pending-pay { background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; }

    /* Payment Proof Card */
    .proof-card {
        background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        color: #fff;
    }
    .proof-card-title {
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        color: #94a3b8;
        margin-bottom: 0.85rem;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }
    .proof-meta-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.85rem;
        margin-bottom: 1rem;
    }
    .proof-meta-item .proof-meta-label {
        font-size: 0.7rem;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        display: block;
        margin-bottom: 0.2rem;
    }
    .proof-meta-item .proof-meta-value {
        font-size: 0.92rem;
        font-weight: 700;
        color: #e2e8f0;
        font-family: monospace;
    }
    .proof-img-wrap {
        position: relative;
        cursor: pointer;
        border-radius: 10px;
        overflow: hidden;
        border: 2px dashed #334155;
        background: #0f172a;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 140px;
        transition: border-color 0.2s;
    }
    .proof-img-wrap:hover { border-color: #dc2626; }
    .proof-img-wrap img {
        width: 100%;
        max-height: 220px;
        object-fit: cover;
        display: block;
        border-radius: 8px;
        transition: opacity 0.2s;
    }
    .proof-img-wrap:hover img { opacity: 0.85; }
    .proof-img-overlay {
        position: absolute;
        inset: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.2s;
        background: rgba(220,38,38,0.15);
    }
    .proof-img-wrap:hover .proof-img-overlay { opacity: 1; }
    .proof-img-overlay span {
        background: #dc2626;
        color: #fff;
        font-size: 0.8rem;
        font-weight: 800;
        padding: 0.45rem 0.85rem;
        border-radius: 6px;
        display: flex;
        align-items: center;
        gap: 0.35rem;
    }
    .proof-no-img {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.5rem;
        color: #475569;
        padding: 2rem;
        text-align: center;
    }

    /* Lightbox */
    #proof-lightbox {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.92);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        gap: 1rem;
        cursor: zoom-out;
    }
    #proof-lightbox.open { display: flex; }
    #proof-lightbox img {
        max-width: 92vw;
        max-height: 84vh;
        border-radius: 10px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        object-fit: contain;
    }
    #proof-lightbox-close {
        position: absolute;
        top: 1.25rem;
        right: 1.25rem;
        background: rgba(255,255,255,0.12);
        border: none;
        color: #fff;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        font-size: 1.2rem;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }
    #proof-lightbox-close:hover { background: #dc2626; }
    #proof-lightbox-caption {
        color: #94a3b8;
        font-size: 0.82rem;
        font-weight: 600;
        text-align: center;
    }
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
                <th>Payment</th>
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
                    <td class="tbl-pay-cell">
                        @if($appt->payment_method)
                            <div class="tbl-pay-method">{{ $appt->payment_method }}</div>
                        @endif
                        @if($appt->reference_number)
                            <div class="tbl-pay-ref">Ref: {{ $appt->reference_number }}</div>
                        @endif
                        @if($appt->payment_proof)
                            <a href="{{ request()->fullUrlWithQuery(['manage_id' => $appt->id]) }}" class="tbl-proof-thumb has-proof" title="View Receipt">
                                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                Receipt Attached
                            </a>
                        @elseif($appt->payment_method)
                            <span class="tbl-proof-thumb no-proof">
                                <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                                No Receipt
                            </span>
                        @else
                            <span style="color:#94a3b8; font-size:0.8rem;">&mdash;</span>
                        @endif
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
                    <td colspan="6" style="text-align: center; padding: 3rem; color: #94a3b8;">
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

                    {{-- ── Payment Details Card ── --}}
                    @if($manageAppointment->payment_method || $manageAppointment->reference_number || $manageAppointment->payment_proof)
                    <div class="proof-card">
                        <div class="proof-card-title">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2.5"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                            Payment Details
                            @php
                                $payStatus = $manageAppointment->payment_status ?? 'pending';
                            @endphp
                            <span class="badge @if($payStatus=='paid') badge-paid @elseif($payStatus=='unpaid') badge-unpaid @else badge-pending-pay @endif" style="font-size:0.65rem; margin-left:auto;">
                                {{ ucfirst($payStatus) }}
                            </span>
                        </div>

                        <div class="proof-meta-grid">
                            @if($manageAppointment->payment_method)
                            <div class="proof-meta-item">
                                <span class="proof-meta-label">Payment Method</span>
                                <span class="proof-meta-value" style="font-family: inherit; color: #f1f5f9;">{{ $manageAppointment->payment_method }}</span>
                            </div>
                            @endif
                            @if($manageAppointment->reference_number)
                            <div class="proof-meta-item">
                                <span class="proof-meta-label">Reference / Transaction #</span>
                                <span class="proof-meta-value">{{ $manageAppointment->reference_number }}</span>
                            </div>
                            @endif
                        </div>

                        @if($manageAppointment->payment_proof)
                            <div style="font-size: 0.7rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; margin-bottom: 0.5rem;">
                                Payment Receipt / Screenshot
                            </div>
                            <div class="proof-img-wrap" onclick="openProofLightbox('{{ asset('storage/' . $manageAppointment->payment_proof) }}', '#{{ str_pad($manageAppointment->id, 5, '0', STR_PAD_LEFT) }} — {{ $manageAppointment->client->firstname }} {{ $manageAppointment->client->lastname }}')">
                                <img src="{{ asset('storage/' . $manageAppointment->payment_proof) }}"
                                     alt="Payment Receipt"
                                     onerror="this.parentElement.innerHTML='<div class=\'proof-no-img\'><svg xmlns=\'http://www.w3.org/2000/svg\' width=\'28\' height=\'28\' viewBox=\'0 0 24 24\' fill=\'none\' stroke=\'#475569\' stroke-width=\'2\'><rect x=\'3\' y=\'3\' width=\'18\' height=\'18\' rx=\'2\'/><circle cx=\'8.5\' cy=\'8.5\' r=\'1.5\'/><polyline points=\'21 15 16 10 5 21\'/></svg><span style=\'font-size:0.8rem;\'>Image not found</span></div>'">
                                <div class="proof-img-overlay">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/><line x1="11" y1="8" x2="11" y2="14"/><line x1="8" y1="11" x2="14" y2="11"/></svg>
                                        View Full Receipt
                                    </span>
                                </div>
                            </div>
                            <div style="margin-top: 0.5rem; display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <a href="{{ asset('storage/' . $manageAppointment->payment_proof) }}" download target="_blank"
                                   style="font-size: 0.75rem; font-weight: 700; color: #60a5fa; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    Download Receipt
                                </a>
                                <span style="color: #334155;">|</span>
                                <a href="{{ asset('storage/' . $manageAppointment->payment_proof) }}" target="_blank"
                                   style="font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-decoration: none; display: inline-flex; align-items: center; gap: 0.3rem;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                                    Open in New Tab
                                </a>
                            </div>
                        @else
                            <div class="proof-no-img" style="background: rgba(255,255,255,0.03); border-radius: 8px; border: 1px dashed #334155;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#475569" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                <span style="font-size: 0.8rem; color: #64748b;">No payment receipt uploaded</span>
                            </div>
                        @endif
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

                    <div class="detail-grid" style="margin-bottom: 0;">
                        <div class="form-group">
                            <label class="form-label">Booking Status</label>
                            <select name="status" class="form-control" required>
                                <option value="pending" {{ $manageAppointment->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ $manageAppointment->status == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="cancelled" {{ $manageAppointment->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Payment Status</label>
                            <select name="payment_status" class="form-control">
                                <option value="Pending Payment" {{ in_array($manageAppointment->payment_status, ['Pending Payment', 'pending', 'unpaid', null]) ? 'selected' : '' }}>Pending Payment</option>
                                <option value="Payment Confirmed" {{ in_array($manageAppointment->payment_status, ['Payment Confirmed', 'paid']) ? 'selected' : '' }}>Payment Confirmed</option>
                                <option value="Cancelled" {{ $manageAppointment->payment_status == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>
                        </div>
                    </div>

                    @if($manageAppointment->client?->email)
                        <div style="font-size: 0.75rem; color: #0369a1; background: #e0f2fe; padding: 0.45rem 0.75rem; border-radius: 6px; margin-bottom: 1rem; display: flex; align-items: center; gap: 0.4rem; border: 1px solid #bae6fd;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                            Payment confirmation receipt will be emailed to <strong>{{ $manageAppointment->client->email }}</strong> upon approval or confirmation.
                        </div>
                    @endif

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

@push('scripts')
<script>
    /* ── Receipt Lightbox ── */
    function openProofLightbox(src, caption) {
        const lb = document.getElementById('proof-lightbox');
        if (!lb) return;
        document.getElementById('proof-lightbox-img').src = src;
        document.getElementById('proof-lightbox-caption').textContent = caption;
        lb.classList.add('open');
        document.body.style.overflow = 'hidden';
    }
    function closeProofLightbox() {
        const lb = document.getElementById('proof-lightbox');
        if (!lb) return;
        lb.classList.remove('open');
        document.body.style.overflow = '';
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeProofLightbox();
    });
</script>
@endpush

{{-- Receipt Lightbox (global, outside modal) --}}
<div id="proof-lightbox" onclick="closeProofLightbox()">
    <button id="proof-lightbox-close" onclick="event.stopPropagation(); closeProofLightbox();" aria-label="Close">&times;</button>
    <img id="proof-lightbox-img" src="" alt="Payment Receipt" onclick="event.stopPropagation()">
    <div id="proof-lightbox-caption"></div>
</div>
