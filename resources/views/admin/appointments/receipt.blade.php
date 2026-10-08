<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Receipt #{{ $appointment->id }} - BCTVI Bantayan</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-page: #f1f5f9;
            --bg-card: #ffffff;
            --text-dark: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --text-faint: #94a3b8;
            --border-light: #e2e8f0;
            --bg-subtle: #f8fafc;
            --accent: #dc2626;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', 'Segoe UI', system-ui, sans-serif;
            background: var(--bg-page);
            color: var(--text-dark);
            padding: 24px;
            line-height: 1.5;
        }

        .no-print {
            text-align: center;
            margin-bottom: 20px;
            display: flex;
            gap: 12px;
            justify-content: center;
            align-items: center;
        }

        .btn-print {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--accent);
            color: #fff;
            border: none;
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            box-shadow: 0 4px 14px rgba(220, 38, 38, 0.3);
            font-family: inherit;
            transition: background 0.2s, transform 0.2s;
        }

        .btn-print:hover {
            background: #b91c1c;
            transform: translateY(-1px);
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--bg-card);
            color: var(--text-body);
            border: 1px solid var(--border-light);
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: none;
            font-family: inherit;
            transition: background 0.15s;
        }

        .btn-back:hover {
            background: var(--bg-subtle);
            color: var(--text-dark);
        }

        /* Receipt card */
        .receipt-card {
            max-width: 700px;
            margin: 0 auto;
            background: var(--bg-card);
            border-radius: 16px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.08);
            padding: 2.5rem;
            border: 1px solid var(--border-light);
            position: relative;
            overflow: hidden;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 5.5rem;
            font-weight: 900;
            color: rgba(220, 38, 38, 0.04);
            pointer-events: none;
            text-transform: uppercase;
            white-space: nowrap;
            letter-spacing: 0.05em;
        }

        /* Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid var(--accent);
            padding-bottom: 1.25rem;
            margin-bottom: 1.75rem;
            position: relative;
        }

        .logo-box {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .logo-box img {
            width: 48px;
            height: 48px;
            object-fit: contain;
        }

        .logo-title {
            font-size: 1.7rem;
            font-weight: 900;
            color: var(--accent);
            line-height: 1;
        }

        .logo-sub {
            font-size: 0.72rem;
            color: var(--text-muted);
            font-weight: 700;
            letter-spacing: 0.06em;
            margin-top: 2px;
        }

        .receipt-title { text-align: right; }

        .receipt-title h2 {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--text-dark);
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 6px;
        }

        .ref-badge {
            display: inline-block;
            background: #fef2f2;
            color: var(--accent);
            font-weight: 800;
            font-size: 0.85rem;
            padding: 5px 12px;
            border-radius: 8px;
            border: 1px solid #fecaca;
            font-family: 'Courier New', Courier, monospace;
        }

        .receipt-date {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-weight: 600;
            margin-top: 6px;
        }

        /* Status ribbon */
        .status-ribbon {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0.85rem 1.25rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-weight: 700;
            font-size: 0.88rem;
        }

        .status-ribbon--approved {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .status-ribbon--pending {
            background: linear-gradient(135deg, #fff7ed, #ffedd5);
            color: #c2410c;
            border: 1px solid #fed7aa;
        }

        .status-ribbon--cancelled {
            background: linear-gradient(135deg, #fef2f2, #fee2e2);
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        /* Info grid */
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.75rem;
        }

        .info-block {
            padding: 1rem 1.25rem;
            border-radius: 12px;
            background: var(--bg-subtle);
            border: 1px solid var(--border-light);
        }

        .info-block h4 {
            font-size: 0.68rem;
            text-transform: uppercase;
            color: var(--text-muted);
            letter-spacing: 0.08em;
            font-weight: 800;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .info-block p {
            font-size: 0.88rem;
            font-weight: 600;
            line-height: 1.6;
            color: var(--text-body);
        }

        .info-block p strong {
            color: var(--text-dark);
            font-weight: 800;
        }

        /* Line items table */
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--border-light);
        }

        .table-items th {
            background: #0f172a;
            color: rgba(255, 255, 255, 0.9);
            text-align: left;
            padding: 12px 16px;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 700;
        }

        .table-items td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-light);
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-body);
        }

        .table-items tbody tr:last-child td {
            border-bottom: none;
        }

        .table-items tbody tr:hover {
            background: var(--bg-subtle);
        }

        /* Total box */
        .total-box {
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
            border-radius: 14px;
            padding: 1.5rem 1.75rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            color: #fff;
        }

        .total-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.6);
            font-weight: 700;
            letter-spacing: 0.06em;
            margin-bottom: 4px;
        }

        .total-status {
            font-size: 1rem;
            font-weight: 800;
            color: #4ade80;
        }

        .total-amount {
            font-size: 2rem;
            font-weight: 900;
            color: #fff;
            letter-spacing: -0.02em;
        }

        /* Payment details */
        .payment-details {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .pay-detail {
            padding: 0.85rem 1rem;
            border-radius: 10px;
            border: 1px solid var(--border-light);
            background: var(--bg-subtle);
        }

        .pay-detail-label {
            font-size: 0.65rem;
            font-weight: 700;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 4px;
        }

        .pay-detail-value {
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        /* Footer */
        .footer-note {
            text-align: center;
            font-size: 0.78rem;
            color: var(--text-muted);
            border-top: 1px solid var(--border-light);
            padding-top: 1.25rem;
            margin-top: 1.5rem;
            line-height: 1.7;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff; padding: 0; }
            .receipt-card { box-shadow: none; border: none; border-radius: 0; }
        }

        @media (max-width: 600px) {
            .grid-2, .payment-details { grid-template-columns: 1fr; }
            .header { flex-direction: column; gap: 1rem; }
            .receipt-title { text-align: left; }
            .receipt-card { padding: 1.5rem; }
            .total-amount { font-size: 1.5rem; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <a href="{{ route('admin.appointments') }}" class="btn-back">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg>
            Back to Bookings
        </a>
        <button onclick="window.print()" class="btn-print">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Print Receipt
        </button>
    </div>

    @php
        $client  = $appointment->client;
        $service = $appointment->service;
        $receiptNo = $payment->receipt_no
            ?? ('APT-' . now()->format('Ymd') . '-' . str_pad($appointment->id, 5, '0', STR_PAD_LEFT));
        $amountPaid = $payment
            ? (float) $payment->amount_paid
            : ((float) ($appointment->amount_paid > 0 ? $appointment->amount_paid : ($service->price ?? 0)));
    @endphp

    <div class="receipt-card">
        <div class="watermark">OFFICIAL RECEIPT</div>

        {{-- Header --}}
        <div class="header">
            <div class="logo-box">
                <img src="{{ asset('assets/images/cctn-logo.png') }}" alt="BCTVI Logo">
                <div>
                    <div class="logo-title">BCTVI</div>
                    <div class="logo-sub">Bogo Cable Television Inc. &middot; Bantayan Branch</div>
                </div>
            </div>
            <div class="receipt-title">
                <h2>Booking Receipt</h2>
                <div class="ref-badge">{{ $receiptNo }}</div>
                <div class="receipt-date">
                    {{ $payment ? $payment->payment_date->format('F j, Y · g:i A') : now()->format('F j, Y · g:i A') }}
                </div>
            </div>
        </div>

        {{-- Status --}}
        <div class="status-ribbon status-ribbon--{{ $appointment->status }}">
            @if ($appointment->status === 'approved')
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                Booking Approved &amp; Payment Recorded
            @elseif ($appointment->status === 'pending')
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Booking Pending Approval
            @else
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
                Booking Cancelled
            @endif
        </div>

        {{-- Customer & Booking Info --}}
        <div class="grid-2">
            <div class="info-block">
                <h4>
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Customer Information
                </h4>
                <p><strong>{{ $client->firstname ?? 'Walk-In' }} {{ $client->lastname ?? 'Client' }}</strong></p>
                @if ($client?->account_number)
                    <p>Account #: <span style="font-family:monospace;color:var(--accent);">{{ $client->account_number }}</span></p>
                @endif
                <p>Contact: {{ $client->contact_no ?? 'N/A' }}</p>
                <p>Email: {{ $client->email ?? 'N/A' }}</p>
                @if ($client?->address_barangay)
                    <p>Location: {{ $client->address_barangay }}, {{ $client->address_municipality ?? '' }}</p>
                @endif
            </div>
            <div class="info-block">
                <h4>
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Booking Details
                </h4>
                <p>Booking ID: <strong>#{{ str_pad($appointment->id, 5, '0', STR_PAD_LEFT) }}</strong></p>
                <p>Schedule: <strong>{{ $appointment->preferred_date?->format('F j, Y') }}</strong>
                    @ {{ $appointment->preferred_time ? date('g:i A', strtotime($appointment->preferred_time)) : '—' }}</p>
                <p>Type: {{ ucfirst($appointment->installation_type ?? 'Residential') }}</p>
                @if ($appointment->purok_landmark)
                    <p>Landmark: {{ $appointment->purok_landmark }}</p>
                @endif
            </div>
        </div>

        {{-- Line items --}}
        <table class="table-items">
            <thead>
                <tr>
                    <th>Service / Plan</th>
                    <th>Description</th>
                    <th style="text-align:right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>{{ $service ? str_ireplace('CCTN', 'BCTVI', $service->service_name) : 'Internet Service' }}</strong>
                    </td>
                    <td>
                        {{ $service->speed ?? '' }}
                        @if ($service?->description)
                            <br><span style="font-size:0.8rem;color:var(--text-muted);font-weight:500;">{{ $service->description }}</span>
                        @endif
                    </td>
                    <td style="text-align:right;font-weight:800;color:var(--text-dark);">
                        ₱{{ number_format($amountPaid, 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Payment details strip --}}
        <div class="payment-details">
            <div class="pay-detail">
                <div class="pay-detail-label">Payment Method</div>
                <div class="pay-detail-value">{{ ucfirst($payment->payment_method ?? $appointment->payment_method ?? 'Cash') }}</div>
            </div>
            <div class="pay-detail">
                <div class="pay-detail-label">Reference #</div>
                <div class="pay-detail-value" style="font-family:monospace;">{{ $payment->reference_number ?? $appointment->reference_number ?? 'N/A' }}</div>
            </div>
            <div class="pay-detail">
                <div class="pay-detail-label">Processed By</div>
                <div class="pay-detail-value">{{ $payment->received_by ?? 'Admin Staff' }}</div>
            </div>
        </div>

        {{-- Total --}}
        <div class="total-box">
            <div>
                <div class="total-label">Status</div>
                <div class="total-status">
                    @if ($appointment->status === 'approved')
                        ✓ Approved &amp; Recorded in Sales
                    @else
                        {{ ucfirst($appointment->status) }}
                    @endif
                </div>
            </div>
            <div style="text-align:right;">
                <div class="total-label">Total Amount</div>
                <div class="total-amount">₱{{ number_format($amountPaid, 2) }}</div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer-note">
            Thank you for choosing Bogo Cable Television Inc. (BCTVI)!<br>
            Bantayan Island Branch &middot; Support: 0999 998 8209 &middot; Official Booking Receipt
        </div>
    </div>

</body>
</html>
