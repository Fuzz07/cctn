@extends('layouts.app')

@section('title', 'My Appointments - BCTVI Bantayan')

@push('styles')
<style>
    .page-header { margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
    .page-title { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0; }
    .page-subtitle { color: #64748b; font-size: 0.9rem; margin-top: 0.25rem; }
    
    .filter-pills { display: flex; gap: 0.5rem; overflow-x: auto; padding-bottom: 0.5rem; }
    .filter-pill { padding: 0.5rem 1rem; border-radius: 99px; background: #fff; border: 1px solid #e2e8f0; color: #64748b; font-size: 0.85rem; font-weight: 600; text-decoration: none; white-space: nowrap; transition: all 0.2s; }
    .filter-pill:hover { background: #f1f5f9; color: #0f172a; }
    .filter-pill.active { background: #0f172a; color: #fff; border-color: #0f172a; }

    .appt-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid #e2e8f0; margin-bottom: 1rem; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s; }
    .appt-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.06); }
    
    .appt-header { padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #f1f5f9; background: #fafbfc; }
    .appt-ref { font-family: monospace; font-weight: 700; color: #94a3b8; font-size: 0.9rem; }
    
    .appt-body { padding: 1.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    @media (max-width: 640px) { .appt-body { grid-template-columns: 1fr; } }
    
    .appt-detail { display: flex; flex-direction: column; gap: 0.25rem; }
    .appt-label { font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #94a3b8; letter-spacing: 0.05em; }
    .appt-value { font-size: 1rem; font-weight: 600; color: #0f172a; }
    
    .status-badge { padding: 0.35rem 0.85rem; border-radius: 99px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 0.35rem; }
    .status-badge.pending { background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; }
    .status-badge.approved { background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }
    .status-badge.cancelled { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }
    .status-badge.completed { background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; }

    .appt-footer { padding: 1rem 1.5rem; background: #fff; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; gap: 0.75rem; }
    .btn-action { padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.85rem; font-weight: 600; text-decoration: none; cursor: pointer; border: 1px solid transparent; transition: all 0.2s; }
    .btn-outline-danger { background: #fff; color: #dc2626; border-color: #fca5a5; }
    .btn-outline-danger:hover { background: #fef2f2; }
</style>
@endpush

@section('content')
<div style="max-width: 1000px; margin: 2rem auto; padding: 0 1.5rem;">
    <div class="page-header">
        <div>
            <h1 class="page-title">My Appointments</h1>
            <p class="page-subtitle">Track your booking requests and history</p>
        </div>
        <a href="{{ route('client.book') }}" class="btn" style="background:#0f172a; color:#fff; padding:0.75rem 1.5rem; border-radius:8px; text-decoration:none; font-weight:700;">+ Book New</a>
    </div>

    @php
        $statusFilter = request('status', 'all');
        $filteredAppts = $appointments->filter(function($a) use ($statusFilter) {
            return $statusFilter === 'all' || $a->status === $statusFilter;
        });
    @endphp

    <div class="filter-pills" style="margin-bottom: 1.5rem;">
        <a href="{{ route('client.appointments', ['status' => 'all']) }}" class="filter-pill {{ $statusFilter == 'all' ? 'active' : '' }}">All</a>
        <a href="{{ route('client.appointments', ['status' => 'pending']) }}" class="filter-pill {{ $statusFilter == 'pending' ? 'active' : '' }}">Pending</a>
        <a href="{{ route('client.appointments', ['status' => 'approved']) }}" class="filter-pill {{ $statusFilter == 'approved' ? 'active' : '' }}">Approved</a>
        <a href="{{ route('client.appointments', ['status' => 'cancelled']) }}" class="filter-pill {{ $statusFilter == 'cancelled' ? 'active' : '' }}">Cancelled</a>
    </div>

    <div>
        @forelse ($filteredAppts as $appt)
            <div class="appt-card">
                <div class="appt-header">
                    <span class="appt-ref">REF #{{ str_pad($appt->id, 6, '0', STR_PAD_LEFT) }}</span>
                    <span class="status-badge {{ $appt->status }}">
                        @if($appt->status == 'pending')
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        @elseif($appt->status == 'approved')
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                        @else
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                        @endif
                        {{ $appt->status }}
                    </span>
                </div>
                <div class="appt-body">
                    <div class="appt-detail">
                        <span class="appt-label">Service Required</span>
                        <span class="appt-value">{{ $appt->service->service_name }}</span>
                    </div>
                    <div class="appt-detail">
                        <span class="appt-label">Schedule Date & Time</span>
                        <span class="appt-value" style="color: #dc2626;">{{ date('l, M d, Y', strtotime($appt->preferred_date)) }} at {{ date('h:i A', strtotime($appt->preferred_time)) }}</span>
                    </div>
                    <div class="appt-detail">
                        <span class="appt-label">Payment Method</span>
                        <span class="appt-value" style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                            <span style="background: #f1f5f9; border: 1px solid #cbd5e1; padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.85rem; font-weight: 700; color: #0f172a;">
                                {{ $appt->payment_method ?: 'GCash (Digital Payment)' }}
                            </span>
                            @if($appt->reference_number)
                                <span style="font-size: 0.8rem; color: #64748b;">Ref: {{ $appt->reference_number }}</span>
                            @endif
                            @if($appt->payment_proof)
                                <a href="{{ asset('storage/' . $appt->payment_proof) }}" target="_blank" style="font-size: 0.8rem; color: #dc2626; text-decoration: underline; font-weight: 600;">View Receipt</a>
                            @endif
                        </span>
                    </div>
                    @if($appt->message)
                        <div class="appt-detail">
                            <span class="appt-label">Your Note</span>
                            <span class="appt-value" style="font-weight: 500; font-size: 0.9rem;">{{ $appt->message }}</span>
                        </div>
                    @endif
                </div>

                <!-- Footer Action: Payment Method Update -->
                <div class="appt-footer" style="display: flex; justify-content: space-between; align-items: center;">
                    <button type="button" class="btn-action" style="background: #fef2f2; color: #dc2626; border-color: #fecaca; font-weight: 700;" onclick="togglePaymentForm({{ $appt->id }})">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px; vertical-align: -2px;"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        {{ $appt->payment_method ? 'Update Payment Method' : 'Set Digital Payment Method' }}
                    </button>
                    @if($appt->admin_notes)
                        <span style="font-size: 0.8rem; color: #64748b;"><strong>Note:</strong> {{ $appt->admin_notes }}</span>
                    @endif
                </div>

                <!-- Collapsible Payment Method Form -->
                <div id="payment-form-{{ $appt->id }}" style="display: none; padding: 1.25rem 1.5rem; background: #fafafa; border-top: 1px solid #e2e8f0;">
                    <form action="{{ route('client.appointments.payment-method', $appt->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div style="font-size: 0.9rem; font-weight: 700; color: #0f172a; margin-bottom: 0.75rem;">
                            Update Payment Details for Appointment #{{ str_pad($appt->id, 6, '0', STR_PAD_LEFT) }}
                        </div>

                        <!-- Payment Instructions Box -->
                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 1rem; margin-bottom: 1rem;">
                            <div style="font-size: 0.8rem; font-weight: 800; color: #dc2626; text-transform: uppercase; margin-bottom: 0.4rem;">
                                💡 Official BCTVI Payment Accounts
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; font-size: 0.8rem;">
                                <div style="background: #f8fafc; padding: 0.6rem 0.75rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                                    <strong style="color: #007DFE;">GCash / Maya:</strong><br>
                                    Acc Name: <strong>BCTVI Broadband</strong><br>
                                    Number: <strong style="font-family: monospace; color: #dc2626;">0917 888 2099</strong>
                                </div>
                                <div style="background: #f8fafc; padding: 0.6rem 0.75rem; border-radius: 6px; border: 1px solid #cbd5e1;">
                                    <strong style="color: #6b21a8;">Bank Transfer (BDO):</strong><br>
                                    Acc Name: <strong>Bogo Cable Television Inc.</strong><br>
                                    Acc #: <strong style="font-family: monospace; color: #dc2626;">0012-3456-7890</strong>
                                </div>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 0.35rem; color: #334155;">Digital Payment Method</label>
                                <select name="payment_method" class="form-control" style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px solid #cbd5e1; font-weight: 600;" required>
                                    <option value="GCash" {{ ($appt->payment_method == 'GCash' || !$appt->payment_method || $appt->payment_method == 'Cash') ? 'selected' : '' }}>GCash (E-Wallet)</option>
                                    <option value="Maya" {{ $appt->payment_method == 'Maya' ? 'selected' : '' }}>Maya (E-Wallet)</option>
                                    <option value="Bank Transfer" {{ $appt->payment_method == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                                    <option value="Credit/Debit Card" {{ $appt->payment_method == 'Credit/Debit Card' ? 'selected' : '' }}>Credit / Debit Card</option>
                                </select>
                            </div>
                            <div id="ref-field-{{ $appt->id }}">
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 0.35rem; color: #334155;">Reference / Transaction No.</label>
                                <input type="text" name="reference_number" class="form-control" style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px solid #cbd5e1; font-family: monospace; font-weight: 700;" placeholder="e.g. 10029384756" value="{{ $appt->reference_number }}">
                            </div>
                            <div id="proof-field-{{ $appt->id }}">
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 0.35rem; color: #334155;">Upload Payment Proof (Receipt)</label>
                                <input type="file" name="payment_proof" class="form-control" style="width: 100%; padding: 0.4rem 0.75rem; border-radius: 6px; border: 1px solid #cbd5e1;" accept="image/*">
                            </div>
                        </div>

                        <!-- Pending status advisory -->
                        <div style="background: #fffbebf8; border: 1px solid #fde68a; border-radius: 6px; padding: 0.6rem 0.85rem; font-size: 0.78rem; color: #92400e; margin-bottom: 1rem;">
                            ⏳ <strong>Status Notice:</strong> Your payment will remain marked as <strong>Pending Verification</strong> until verified by our administrator.
                        </div>

                        <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                            <button type="button" class="btn-action" style="background: #fff; border-color: #cbd5e1; color: #475569;" onclick="togglePaymentForm({{ $appt->id }})">Cancel</button>
                            <button type="submit" class="btn-action" style="background: #dc2626; color: #fff; font-weight: 700;">Save &amp; Submit Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 4rem 1rem; background: #fff; border-radius: 12px; border: 1px dashed #cbd5e1;">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin-bottom: 1rem;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <h3 style="margin: 0 0 0.5rem; font-size: 1.1rem; color: #334155;">No appointments found</h3>
                <p style="margin: 0; color: #64748b; font-size: 0.9rem;">You don't have any appointments matching this filter.</p>
            </div>
        @endforelse
    </div>
</div>

@push('scripts')
<script>
    function togglePaymentForm(id) {
        const formEl = document.getElementById('payment-form-' + id);
        if (formEl) {
            formEl.style.display = formEl.style.display === 'none' ? 'block' : 'none';
        }
    }
</script>
@endpush
@endsection
