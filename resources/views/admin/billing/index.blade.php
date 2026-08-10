@extends('layouts.admin')

@section('title', 'Billing & Payments - BCTVI Bantayan')

@push('styles')
<style>
    .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem; }
    .page-title { font-size:1.5rem; font-weight:800; color:#0f172a; margin:0; }

    .content-grid { display:grid; grid-template-columns:380px 1fr; gap:1.5rem; }
    @media(max-width:1100px){ .content-grid { grid-template-columns:1fr; } }

    .form-card { background:#fff; border-radius:16px; padding:1.75rem; border:1px solid #e2e8f0; box-shadow:0 4px 15px rgba(0,0,0,0.02); }
    .form-card-title { font-size:1.1rem; font-weight:800; color:#0f172a; margin:0 0 1.5rem; display:flex; align-items:center; gap:.5rem; }

    .form-group { margin-bottom:1.25rem; }
    .form-label { display:block; font-size:.85rem; font-weight:700; color:#334155; margin-bottom:.5rem; }
    .form-control { width:100%; padding:.75rem 1rem; border:1px solid #cbd5e1; border-radius:8px; font-size:.9rem; box-sizing:border-box; font-family:inherit; }
    .form-control:focus { outline:none; border-color:#dc2626; box-shadow:0 0 0 3px rgba(220,38,38,.1); }

    .btn-submit { width:100%; background:#0f172a; color:#fff; padding:.85rem; border-radius:8px; font-weight:700; font-size:1rem; border:none; cursor:pointer; margin-top:.5rem; transition:all .2s; }
    .btn-submit:hover { background:#1e293b; }
    .btn-submit.danger { background:#dc2626; }
    .btn-submit.danger:hover { background:#b91c1c; }

    .table-card { background:#fff; border-radius:12px; border:1px solid #e2e8f0; overflow-x:auto; }
    .data-table { width:100%; border-collapse:collapse; text-align:left; font-size:.88rem; }
    .data-table th { padding:.9rem 1rem; background:#f8fafc; border-bottom:2px solid #e2e8f0; color:#64748b; font-weight:700; text-transform:uppercase; font-size:.72rem; letter-spacing:.05em; }
    .data-table td { padding:.9rem 1rem; border-bottom:1px solid #f1f5f9; color:#334155; vertical-align:middle; }
    .data-table tbody tr:hover { background:#f8fafc; }

    .badge { padding:.3rem .7rem; border-radius:99px; font-size:.72rem; font-weight:700; text-transform:uppercase; display:inline-block; }
    .badge-paid { background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; }
    .badge-unpaid { background:#fef3c7; color:#d97706; border:1px solid #fde68a; }
    .badge-overdue { background:#fee2e2; color:#dc2626; border:1px solid #fecaca; }
    
    .btn-sm-pay { padding:.4rem .8rem; border-radius:6px; font-size:.8rem; font-weight:700; background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; cursor:pointer; display:inline-flex; align-items:center; gap:.3rem; white-space:nowrap; }
    .btn-sm-pay:hover { background:#bbf7d0; }

    /* Payment Modal */
    .modal-overlay { position:fixed; inset:0; background:rgba(15,23,42,.6); backdrop-filter:blur(4px); display:flex; align-items:center; justify-content:center; z-index:100; opacity:0; pointer-events:none; transition:opacity .2s; }
    .modal-overlay.active { opacity:1; pointer-events:auto; }
    .modal-content { background:#fff; width:100%; max-width:500px; border-radius:16px; box-shadow:0 20px 40px rgba(0,0,0,.1); transform:scale(.95); transition:transform .2s; }
    .modal-overlay.active .modal-content { transform:scale(1); }
    .modal-header { padding:1.5rem; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; }
    .modal-title { font-size:1.2rem; font-weight:800; color:#0f172a; margin:0; }
    .btn-close { background:none; border:none; color:#94a3b8; cursor:pointer; padding:.4rem; }
    .modal-body { padding:1.5rem; }
    .modal-footer { padding:1rem 1.5rem; background:#f8fafc; border-top:1px solid #e2e8f0; border-radius:0 0 16px 16px; display:flex; justify-content:flex-end; gap:.75rem; }
    .btn-cancel-modal { background:#fff; color:#64748b; padding:.65rem 1.25rem; border-radius:8px; font-weight:600; font-size:.9rem; border:1px solid #e2e8f0; cursor:pointer; text-decoration:none; }
    .btn-save-modal { background:#15803d; color:#fff; padding:.65rem 1.25rem; border-radius:8px; font-weight:700; font-size:.9rem; border:none; cursor:pointer; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">Billing & Payments</h1>
</div>

@if(session('success_message'))
    <div style="background:#dcfce7;color:#15803d;padding:1rem;border-radius:12px;margin-bottom:1.5rem;font-weight:500;border:1px solid #bbf7d0;">
        {{ session('success_message') }}
    </div>
@endif

<div class="content-grid">
    <!-- Create Billing Form -->
    <div>
        <div class="form-card">
            <h3 class="form-card-title">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#dc2626" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                Create Billing Statement
            </h3>
            <form action="{{ route('admin.billing.create') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Client *</label>
                    <select name="client_id" class="form-control" required>
                        <option value="">Select client...</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}">{{ $c->firstname }} {{ $c->lastname }} ({{ $c->account_number ?? 'No Acct#' }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Account Number *</label>
                    <input type="text" name="account_number" class="form-control" value="{{ old('account_number') }}" required placeholder="e.g. 2026-01-01">
                </div>
                <div class="form-group">
                    <label class="form-label">Statement Period *</label>
                    <input type="text" name="statement_period" class="form-control" value="{{ old('statement_period') }}" required placeholder="e.g. January 2025">
                </div>
                <div class="form-group">
                    <label class="form-label">Amount Due (₱) *</label>
                    <input type="number" name="amount_due" class="form-control" step="0.01" value="{{ old('amount_due') }}" required min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Penalty Amount (₱)</label>
                    <input type="number" name="penalty_amount" class="form-control" step="0.01" value="{{ old('penalty_amount', 0) }}" min="0">
                </div>
                <div class="form-group">
                    <label class="form-label">Due Date *</label>
                    <input type="date" name="due_date" class="form-control" value="{{ old('due_date') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" class="form-control" value="{{ old('notes') }}" placeholder="Optional...">
                </div>
                <button type="submit" class="btn-submit danger">Generate Statement</button>
            </form>
        </div>
    </div>

    <!-- Billing Table -->
    <div>
        <div class="table-card">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Client & Account</th>
                        <th>Statement Period</th>
                        <th>Amount Due</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($billings as $bill)
                        @php
                            $statusBadge = match(strtolower($bill->status)) {
                                'paid' => 'badge-paid',
                                'overdue' => 'badge-overdue',
                                default => 'badge-unpaid'
                            };
                        @endphp
                        <tr>
                            <td>
                                <div style="font-weight:700; color:#0f172a;">{{ $bill->client->firstname }} {{ $bill->client->lastname }}</div>
                                <div style="font-size:.8rem; color:#dc2626; font-family:monospace; font-weight:700;">{{ $bill->account_number }}</div>
                            </td>
                            <td style="font-weight:600; color:#334155;">{{ $bill->statement_period }}</td>
                            <td>
                                <div style="font-weight:800; color:#0f172a; font-size:.95rem;">₱{{ number_format($bill->total_amount_due, 2) }}</div>
                                @if($bill->penalty_amount > 0)
                                    <div style="font-size:.75rem; color:#dc2626;">+₱{{ number_format($bill->penalty_amount, 2) }} penalty</div>
                                @endif
                            </td>
                            <td style="color:#64748b;">{{ date('M d, Y', strtotime($bill->due_date)) }}</td>
                            <td>
                                <span class="badge {{ $statusBadge }}">{{ $bill->status }}</span>
                                @if($bill->status == 'paid' && $bill->paid_at)
                                    <div style="font-size:.72rem; color:#16a34a; margin-top:.25rem;">{{ date('M d, Y', strtotime($bill->paid_at)) }}</div>
                                @endif
                            </td>
                            <td>
                                @if($bill->status != 'paid')
                                    <button class="btn-sm-pay" onclick="openPayModal({{ $bill->id }}, '{{ $bill->account_number }}', {{ $bill->total_amount_due }})">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                                        Mark Paid
                                    </button>
                                @else
                                    <span style="font-size:.8rem; color:#94a3b8; font-style:italic;">Settled</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:3rem; color:#94a3b8;">No billing records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal-overlay" id="payModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Record Payment</h3>
            <button class="btn-close" onclick="closePayModal()">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form action="{{ route('admin.billing.payment') }}" method="POST">
            @csrf
            <div class="modal-body">
                <input type="hidden" name="billing_id" id="modal_billing_id">

                <div style="background:#f0fdf4; border-radius:10px; padding:1rem; margin-bottom:1.5rem; border:1px solid #dcfce7;">
                    <div style="font-size:.8rem; font-weight:700; color:#64748b; margin-bottom:.25rem;">Account Number</div>
                    <div style="font-size:1.1rem; font-weight:800; color:#15803d; font-family:monospace;" id="modal_account_no"></div>
                    <div style="font-size:.8rem; font-weight:700; color:#64748b; margin-top:.75rem; margin-bottom:.25rem;">Total Amount Due</div>
                    <div style="font-size:1.5rem; font-weight:800; color:#0f172a;" id="modal_total_due"></div>
                </div>

                <div class="form-group">
                    <label class="form-label">Amount Received (₱) *</label>
                    <input type="number" name="amount_paid" id="modal_amount" class="form-control" step="0.01" min="0.01" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Payment Method *</label>
                    <select name="payment_method" class="form-control" required>
                        <option value="cash">Cash</option>
                        <option value="gcash">GCash</option>
                        <option value="bank_transfer">Bank Transfer</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Received By</label>
                    <input type="text" name="received_by" class="form-control" placeholder="Admin / staff name">
                </div>
                <div class="form-group">
                    <label class="form-label">Notes (Optional)</label>
                    <input type="text" name="pay_notes" class="form-control" placeholder="Any reference or remarks...">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel-modal" onclick="closePayModal()">Cancel</button>
                <button type="submit" class="btn-save-modal">Confirm Payment</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openPayModal(id, accNo, totalDue) {
        document.getElementById('modal_billing_id').value = id;
        document.getElementById('modal_account_no').textContent = accNo;
        document.getElementById('modal_total_due').textContent = '₱' + parseFloat(totalDue).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        document.getElementById('modal_amount').value = parseFloat(totalDue).toFixed(2);
        document.getElementById('payModal').classList.add('active');
    }
    function closePayModal() {
        document.getElementById('payModal').classList.remove('active');
    }
</script>
@endpush
