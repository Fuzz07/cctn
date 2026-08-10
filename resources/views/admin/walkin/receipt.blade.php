<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CBTVI Booking Receipt #{{ $appointment->booking_ref ?? $appointment->id }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            color: #0f172a;
            margin: 0;
            padding: 20px;
        }
        .receipt-card {
            max-width: 650px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 2.5rem;
            border: 1px solid #e2e8f0;
            position: relative;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #dc2626;
            padding-bottom: 1.25rem;
            margin-bottom: 1.5rem;
        }
        .logo-box {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .logo-title {
            font-size: 1.6rem;
            font-weight: 900;
            color: #dc2626;
            line-height: 1;
        }
        .logo-sub {
            font-size: 0.75rem;
            color: #64748b;
            font-weight: 700;
            letter-spacing: 0.05em;
        }
        .receipt-title {
            text-align: right;
        }
        .receipt-title h2 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
        }
        .ref-badge {
            display: inline-block;
            background: #fef2f2;
            color: #dc2626;
            font-weight: 800;
            font-size: 0.85rem;
            padding: 4px 10px;
            border-radius: 6px;
            margin-top: 4px;
            border: 1px solid #fecaca;
        }
        .grid-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }
        .info-block h4 {
            margin: 0 0 8px 0;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: 0.05em;
        }
        .info-block p {
            margin: 0;
            font-size: 0.92rem;
            font-weight: 600;
            line-height: 1.4;
        }
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }
        .table-items th {
            background: #f1f5f9;
            text-align: left;
            padding: 10px 12px;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #475569;
        }
        .table-items td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .total-box {
            background: #f8fafc;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            border: 1px solid #e2e8f0;
        }
        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-30deg);
            font-size: 5rem;
            font-weight: 900;
            color: rgba(220, 38, 38, 0.06);
            pointer-events: none;
            text-transform: uppercase;
        }
        .footer-note {
            text-align: center;
            font-size: 0.8rem;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 1rem;
            margin-top: 1.5rem;
        }
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-print {
            background: #dc2626;
            color: #ffffff;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
            font-size: 0.95rem;
            box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
        }
        @media print {
            .no-print { display: none; }
            body { background: #ffffff; padding: 0; }
            .receipt-card { box-shadow: none; border: none; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Print Official Receipt</button>
    </div>

    <div class="receipt-card">
        <div class="watermark">CBTVI PAID</div>

        <div class="header">
            <div class="logo-box">
                <img src="{{ asset('assets/images/cctn-logo.png') }}" alt="CBTVI Logo" style="width: 44px; height: 44px;">
                <div>
                    <div class="logo-title">CBTVI</div>
                    <div class="logo-sub">Broadband Telecommunications</div>
                </div>
            </div>
            <div class="receipt-title">
                <h2>WiFi Installation Receipt</h2>
                <div class="ref-badge">REF: {{ $appointment->booking_ref ?? ('CBTVI-BK-' . $appointment->id) }}</div>
            </div>
        </div>

        <div class="grid-2">
            <div class="info-block">
                <h4>Customer Details</h4>
                <p><strong>{{ $appointment->client->firstname ?? '' }} {{ $appointment->client->lastname ?? '' }}</strong></p>
                <p>Contact: {{ $appointment->client->contact_no ?? 'N/A' }}</p>
                <p>Email: {{ $appointment->client->email ?? 'N/A' }}</p>
                <p>Valid ID: {{ $appointment->valid_id_type ?? 'Government ID' }} ({{ $appointment->valid_id_number ?? 'Verified' }})</p>
            </div>
            <div class="info-block">
                <h4>Installation Schedule</h4>
                <p>Date: <strong>{{ date('F j, Y', strtotime($appointment->preferred_date)) }}</strong></p>
                <p>Time: <strong>{{ date('h:i A', strtotime($appointment->preferred_time)) }}</strong></p>
                <p>Address: {{ $appointment->installation_address ?? $appointment->client->complete_address }}</p>
                <p>Status: <span style="color:#16a34a; font-weight:800;">{{ strtoupper($appointment->installation_status ?? 'Scheduled') }}</span></p>
            </div>
        </div>

        <table class="table-items">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Plan Speed</th>
                    <th style="text-align:right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $appointment->service->service_name ?? 'CBTVI WiFi Plan' }} (1st Month Subscription)</td>
                    <td>{{ $appointment->service->speed ?? 'Fiber Fast' }}</td>
                    <td style="text-align:right;">₱{{ number_format($appointment->service->price ?? 0, 2) }}</td>
                </tr>
                <tr>
                    <td>CBTVI WiFi Modem & Cable Installation Fee</td>
                    <td>Standard</td>
                    <td style="text-align:right;">₱{{ number_format($appointment->service->installation_fee ?? 1000, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="total-box">
            <div>
                <div style="font-size:0.75rem; text-transform:uppercase; color:#64748b; font-weight:700;">Payment Method: {{ $appointment->payment_method ?? 'Cash' }}</div>
                <div style="font-size:0.9rem; font-weight:800; color:#16a34a;">Status: {{ $appointment->payment_status ?? 'Payment Confirmed' }}</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:0.75rem; text-transform:uppercase; color:#64748b; font-weight:700;">Total Amount Paid</div>
                <div style="font-size:1.5rem; font-weight:900; color:#dc2626;">₱{{ number_format($appointment->amount_due ?? (($appointment->service->price ?? 0) + 1000), 2) }}</div>
            </div>
        </div>

        <div class="footer-note">
            Thank you for subscribing to CBTVI High Speed Broadband!<br>
            Bantayan Island Office &middot; Customer Support Helpline: 0999 998 8209 &middot; www.cbtvi-wifi.ph
        </div>
    </div>

</body>
</html>
