@extends('layouts.app')

@section('title', 'My Appointments - BCTVI Bantayan')

@push('styles')
<style>
    .page-header { margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
    .page-title { font-size: 1.5rem; font-weight: 800; color: var(--text-dark); margin: 0; }
    .page-subtitle { color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem; }
    
    .filter-pills { display: flex; gap: 0.5rem; overflow-x: auto; padding-bottom: 0.5rem; }
    .filter-pill { padding: 0.5rem 1rem; border-radius: 99px; background: var(--bg-card); border: 1px solid var(--border-light); color: var(--text-muted); font-size: 0.85rem; font-weight: 600; text-decoration: none; white-space: nowrap; transition: all 0.2s; }
    .filter-pill:hover { background: var(--bg-subtle); color: var(--text-dark); }
    .filter-pill.active { background: #0f172a; color: #fff; border-color: #0f172a; }
    :root[data-theme="dark"] .filter-pill.active { background: #dc2626; color: #fff; border-color: #dc2626; }

    .appt-card { background: var(--bg-card); border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid var(--border-light); margin-bottom: 1rem; overflow: hidden; display: flex; flex-direction: column; transition: transform 0.2s; }
    .appt-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.06); }
    
    .appt-header { padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-light); background: var(--bg-subtle); }
    .appt-ref { font-family: monospace; font-weight: 700; color: var(--text-faint); font-size: 0.9rem; }
    
    .appt-body { padding: 1.5rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
    @media (max-width: 640px) { .appt-body { grid-template-columns: 1fr; } }
    
    .appt-detail { display: flex; flex-direction: column; gap: 0.25rem; }
    .appt-label { font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: var(--text-faint); letter-spacing: 0.05em; }
    .appt-value { font-size: 1rem; font-weight: 600; color: var(--text-dark); }
    
    .status-badge { padding: 0.35rem 0.85rem; border-radius: 99px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 0.35rem; }
    .status-badge.pending { background: #fff7ed; color: #ea580c; border: 1px solid #ffedd5; }
    .status-badge.approved { background: #f0fdf4; color: #16a34a; border: 1px solid #dcfce7; }
    .status-badge.cancelled { background: #fef2f2; color: #dc2626; border: 1px solid #fee2e2; }
    .status-badge.completed { background: var(--bg-page); color: #475569; border: 1px solid var(--border-light); }

    .appt-footer { padding: 1rem 1.5rem; background: var(--bg-card); border-top: 1px solid var(--bg-subtle); display: flex; justify-content: flex-end; gap: 0.75rem; }
    .btn-action { padding: 0.5rem 1rem; border-radius: 6px; font-size: 0.85rem; font-weight: 600; text-decoration: none; cursor: pointer; border: 1px solid transparent; transition: all 0.2s; }
    .btn-outline-danger { background: var(--bg-card); color: #dc2626; border-color: #fca5a5; }
    .btn-outline-danger:hover { background: #fef2f2; }
</style>
@endpush

@section('content')
<div style="max-width: 1100px; margin: 2rem auto; padding: 0 1.5rem;">
    <div class="page-header">
        <div>
            <h1 class="page-title">My Appointments</h1>
            <p class="page-subtitle">Track your booking requests and history</p>
        </div>
        <a href="{{ route('client.book') }}" class="btn" style="background:#dc2626; color:#fff; padding:0.75rem 1.5rem; border-radius:8px; text-decoration:none; font-weight:700; box-shadow:0 4px 12px rgba(220,38,38,0.25); display:inline-flex; align-items:center; gap:0.4rem;">+ Book New</a>
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
                            <span style="background: var(--bg-subtle); border: 1px solid var(--border); padding: 0.2rem 0.6rem; border-radius: 6px; font-size: 0.85rem; font-weight: 700; color: var(--text-dark);">
                                {{ $appt->payment_method ?: 'GCash (Digital Payment)' }}
                            </span>
                            @if($appt->reference_number)
                                <span style="font-size: 0.8rem; color: var(--text-muted);">Ref: {{ $appt->reference_number }}</span>
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

                <!-- Footer Action: Payment Method Update & Delete -->
                <div class="appt-footer" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                        <button type="button" class="btn-action" style="background: #fef2f2; color: #dc2626; border-color: #fecaca; font-weight: 700;" onclick="togglePaymentForm({{ $appt->id }})">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin-right: 4px; vertical-align: -2px;"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                            {{ $appt->payment_method ? 'Update Payment Method' : 'Set Digital Payment Method' }}
                        </button>

                        <form action="{{ route('client.appointments.destroy', $appt->id) }}" method="POST" data-booking-reference="REF #{{ str_pad($appt->id, 6, '0', STR_PAD_LEFT) }}" style="margin: 0;">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn-action btn-outline-danger" onclick="showBookingDeleteModal(this.form)" style="font-weight: 700; display: inline-flex; align-items: center; gap: 0.35rem;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2 2v2"></path></svg>
                                Delete Booking
                            </button>
                        </form>
                    </div>
                    @if($appt->admin_notes)
                        <span style="font-size: 0.8rem; color: var(--text-muted);"><strong>Note:</strong> {{ $appt->admin_notes }}</span>
                    @endif
                </div>

                <!-- Collapsible Payment Method Form -->
                <div id="payment-form-{{ $appt->id }}" style="display: none; padding: 1.25rem 1.5rem; background: var(--bg-subtle); border-top: 1px solid var(--border-light);">
                    <form action="{{ route('client.appointments.payment-method', $appt->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div style="font-size: 0.9rem; font-weight: 700; color: var(--text-dark); margin-bottom: 0.75rem;">
                            Update Payment Details for Appointment #{{ str_pad($appt->id, 6, '0', STR_PAD_LEFT) }}
                        </div>

                        <!-- Payment Instructions Box -->
                        <div style="background: var(--bg-card); border: 1px solid var(--border-light); border-radius: 10px; padding: 1rem; margin-bottom: 1rem;">
                            <div style="font-size: 0.8rem; font-weight: 800; color: #dc2626; text-transform: uppercase; margin-bottom: 0.4rem;">
                                💡 Official BCTVI Payment Accounts
                            </div>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem; font-size: 0.8rem;">
                        <!-- Payment Accounts Info -->
                        <div style="background: var(--bg-card); border: 1px solid var(--border); border-radius: 8px; padding: 0.85rem 1rem; margin-bottom: 1rem;">
                            <div style="font-size: 0.78rem; font-weight: 700; color: #1d4ed8; text-transform: uppercase; margin-bottom: 0.4rem;">GCash Official Account</div>
                            <div style="background: var(--bg-page); padding: 0.6rem 0.75rem; border-radius: 6px; border: 1px solid var(--border);">
                                <strong style="color: #007DFE;">GCash:</strong> Account Name: <strong>JO*Y M.</strong> &bull; Number: <strong style="font-family: monospace; color: #dc2626;">+63 985 838 ****</strong>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
                            <div>
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 0.35rem; color: var(--text-body);">Digital Payment Method</label>
                                <select name="payment_method" class="form-control" style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px solid var(--border); font-weight: 600;" required>
                                    <option value="GCash" selected>GCash (E-Wallet)</option>
                                </select>
                            </div>
                            <div id="ref-field-{{ $appt->id }}">
                                <label style="display: block; font-size: 0.8rem; font-weight: 700; margin-bottom: 0.35rem; color: var(--text-body);">Reference / Transaction No.</label>
                                <input type="text" name="reference_number" class="form-control" style="width: 100%; padding: 0.5rem 0.75rem; border-radius: 6px; border: 1px solid var(--border); font-family: monospace; font-weight: 700;" placeholder="e.g. 1002938475601" value="{{ $appt->reference_number }}" inputmode="numeric" maxlength="13" pattern="[0-9]{13}" oninput="this.value = this.value.replace(/\D/g, '').slice(0, 13)">
                            </div>
                            <div id="proof-field-{{ $appt->id }}">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                                    <label style="display: block; font-size: 0.8rem; font-weight: 700; color: var(--text-body); margin: 0;">Upload Payment Proof</label>
                                    <button type="button" onclick="toggleSampleReceiptModal(true)" style="background: none; border: none; color: #1d4ed8; font-size: 0.75rem; font-weight: 700; cursor: pointer; padding: 0;">View Sample</button>
                                </div>
                                <input type="file" name="payment_proof" class="form-control" style="width: 100%; padding: 0.4rem 0.75rem; border-radius: 6px; border: 1px solid var(--border);" accept="image/jpeg,image/png,image/jpg,image/webp">
                                <small style="display: block; font-size: 0.72rem; color: var(--text-muted); margin-top: 0.25rem;">Vertical (portrait) GCash screenshot (min 300&times;500px, max 4MB).</small>
                            </div>
                        </div>

                        <!-- GCash Receipt Required Notice -->
                        <div style="background: #eff6ff; border-left: 3px solid #3b82f6; border-radius: 4px; padding: 0.55rem 0.85rem; font-size: 0.78rem; color: #1e40af; margin-bottom: 0.75rem;">
                            <strong>GCash Receipt Required:</strong> Only official GCash payment receipts submitted through this interface with a visible reference number will be accepted.
                        </div>

                        <!-- Pending status advisory -->
                        <div style="background: #fffbebf8; border: 1px solid #fde68a; border-radius: 6px; padding: 0.6rem 0.85rem; font-size: 0.78rem; color: #92400e; margin-bottom: 1rem;">
                            ⏳ <strong>Status Notice:</strong> Your payment will remain marked as <strong>Pending Verification</strong> until verified by our administrator.
                        </div>

                        <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                            <button type="button" class="btn-action" style="background: var(--bg-card); border-color: var(--border); color: #475569;" onclick="togglePaymentForm({{ $appt->id }})">Cancel</button>
                            <button type="submit" class="btn-action" style="background: #dc2626; color: #fff; font-weight: 700;">Save &amp; Submit Payment</button>
                        </div>
                    </form>
                </div>
            </div>
        @empty
            <div style="text-align: center; padding: 4rem 1rem; background: var(--bg-card); border-radius: 12px; border: 1px dashed var(--border);">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.5" style="margin-bottom: 1rem;"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <h3 style="margin: 0 0 0.5rem; font-size: 1.1rem; color: var(--text-body);">No appointments found</h3>
                <p style="margin: 0; color: var(--text-muted); font-size: 0.9rem;">You don't have any appointments matching this filter.</p>
            </div>
        @endforelse
    </div>
</div>

@include('partials.delete-booking-modal')

<!-- Sample GCash Receipt Modal -->
<div id="sampleReceiptModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.65); backdrop-filter: blur(4px); align-items: center; justify-content: center; padding: 1rem;" onclick="if(event.target === this) toggleSampleReceiptModal(false)">
    <div style="background: #ffffff; border-radius: 16px; max-width: 400px; width: 100%; max-height: 90vh; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="padding: 1.25rem 1.5rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-size: 1.05rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 0.5rem;">
                <span style="display: inline-block; width: 10px; height: 10px; border-radius: 50%; background: #2563eb;"></span>
                Sample GCash Receipt
            </h3>
            <button type="button" onclick="toggleSampleReceiptModal(false)" style="background: none; border: none; font-size: 1.5rem; line-height: 1; color: #94a3b8; cursor: pointer; padding: 0;">&times;</button>
        </div>
        <div style="padding: 1.25rem 1.5rem; text-align: center;">
            <div style="margin-bottom: 1rem; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 8px; padding: 0.75rem; font-size: 0.8rem; color: #1e40af; text-align: left;">
                <strong>GCash Receipt Required:</strong> Please make sure your uploaded screenshot clearly shows the <strong>Total Amount Sent</strong> and <strong>Ref No. / Reference Number</strong> in vertical (portrait) view.
            </div>
            <img src="{{ asset('assets/images/gcash-sample-receipt.png') }}"
                 alt="Valid GCash receipt sample"
                 style="width: 100%; max-width: 280px; height: auto; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; display: inline-block;">
        </div>
        <div style="padding: 1rem 1.5rem; border-top: 1px solid #e2e8f0; text-align: right;">
            <button type="button" onclick="toggleSampleReceiptModal(false)" style="background: #2563eb; color: #fff; font-weight: 600; font-size: 0.85rem; padding: 0.5rem 1.25rem; border-radius: 8px; border: none; cursor: pointer;">
                Got it
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function toggleSampleReceiptModal(show) {
        const modal = document.getElementById('sampleReceiptModal');
        if (modal) {
            modal.style.display = show ? 'flex' : 'none';
        }
    }
    function togglePaymentForm(id) {
        const formEl = document.getElementById('payment-form-' + id);
        if (formEl) {
            formEl.style.display = formEl.style.display === 'none' ? 'block' : 'none';
        }
    }

    /* ── Portrait screenshot validation for each payment update form ── */
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('input[name="payment_proof"][type="file"]').forEach(function (input) {
            const form = input.closest('form');

            input.addEventListener('change', function () {
                const file = this.files && this.files[0];

                // Clear previous custom error
                let errEl = input.nextElementSibling;
                if (errEl && errEl.classList.contains('proof-validation-error')) {
                    errEl.remove();
                }

                if (!file) return;

                // ── Size lower bound (15 KB) ──
                if (file.size < 15 * 1024) {
                    showInlineError(input, 'The file is too small to be a real screenshot. Please upload the full GCash receipt.');
                    input.value = '';
                    return;
                }

                // ── Size upper bound (4 MB) ──
                if (file.size > 4 * 1024 * 1024) {
                    showInlineError(input, 'File exceeds 4 MB. Please upload a smaller screenshot.');
                    input.value = '';
                    return;
                }

                // ── Orientation + resolution ──
                const url = URL.createObjectURL(file);
                const img = new Image();
                img.onload = function () {
                    const w = img.naturalWidth;
                    const h = img.naturalHeight;
                    URL.revokeObjectURL(url);

                    if (h <= w) {
                        showInlineError(input, 'Please upload a vertical (portrait) GCash screenshot. Landscape images are not accepted.');
                        input.value = '';
                        return;
                    }
                    if (w < 300 || h < 500) {
                        showInlineError(input, 'Screenshot resolution too low (' + w + '×' + h + ' px). Upload a full-resolution portrait screenshot.');
                        input.value = '';
                        return;
                    }
                    // Valid — show a small confirmation
                    showInlineSuccess(input, '✓ Portrait screenshot accepted (' + w + '×' + h + ' px).');
                };
                img.onerror = function () {
                    URL.revokeObjectURL(url);
                    showInlineError(input, 'Could not read the image. Please try a different file.');
                    input.value = '';
                };
                img.src = url;
            });

            // ── Block form submit if proof is invalid ──
            if (form) {
                form.addEventListener('submit', function (e) {
                    const errEl = input.nextElementSibling;
                    if (errEl && errEl.classList.contains('proof-validation-error')) {
                        e.preventDefault();
                        input.focus();
                    }
                });
            }
        });

        function showInlineError(input, msg) {
            clearInlineFeedback(input);
            const span = document.createElement('span');
            span.className = 'proof-validation-error';
            span.style.cssText = 'display:block;font-size:0.75rem;font-weight:600;color:#dc2626;margin-top:0.25rem;';
            span.textContent = msg;
            input.insertAdjacentElement('afterend', span);
        }

        function showInlineSuccess(input, msg) {
            clearInlineFeedback(input);
            const span = document.createElement('span');
            span.className = 'proof-validation-error'; // same class so it gets cleaned up
            span.style.cssText = 'display:block;font-size:0.75rem;font-weight:700;color:#16a34a;margin-top:0.25rem;';
            span.textContent = msg;
            input.insertAdjacentElement('afterend', span);
        }

        function clearInlineFeedback(input) {
            const existing = input.nextElementSibling;
            if (existing && existing.classList.contains('proof-validation-error')) {
                existing.remove();
            }
        }
    });
</script>
@endpush
@endsection
