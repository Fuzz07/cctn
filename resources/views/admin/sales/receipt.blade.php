<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Official Payment Receipt #{{ $payment->receipt_no ?? ('RCT-' . $payment->id) }} - CBTVI</title>
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
            border-radius: 14px;
            box-shadow: 0 4px 25px rgba(0,0,0,0.08);
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
            font-family: 'Courier New', Courier, monospace;
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
            margin: 0 0 4px 0;
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
            border-radius: 10px;
            padding: 1.25rem 1.5rem;
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
        <button onclick="window.print()" class="btn-print">🖨️ Print Sales Payment Receipt</button>
    </div>

    <div class="receipt-card">
        <div class="watermark">OFFICIAL RECEIPT</div>

        <div class="header">
            <div class="logo-box">
                <img src="{{ asset('assets/images/cctn-logo.png') }}" alt="CBTVI Logo" style="width: 44px; height: 44px;">
                <div>
                    <div class="logo-title">CBTVI</div>
                    <div class="logo-sub">Broadband Telecommunications</div>
                </div>
            </div>
            <div class="receipt-title">
                <h2>Sales Revenue Receipt</h2>
                <div class="ref-badge">{{ $payment->receipt_no ?? ('RCT-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT)) }}</div>
            </div>
        </div>

        <div class="grid-2">
            <div class="info-block">
                <h4>Subscriber Information</h4>
                <p><strong>{{ $payment->client->firstname ?? 'Walk-In' }} {{ $payment->client->lastname ?? 'Client' }}</strong></p>
                <p>Account #: <span style="font-family:monospace; color:#dc2626;">{{ $payment->account_number ?? $payment->client?->account_number ?? 'N/A' }}</span></p>
                <p>Contact: {{ $payment->client->contact_no ?? 'N/A' }}</p>
                <p>Email: {{ $payment->client->email ?? 'N/A' }}</p>
            </div>
            <div class="info-block">
                <h4>Payment Transaction Details</h4>
                <p>Date: <strong>{{ $payment->payment_date ? date('F j, Y h:i A', strtotime($payment->payment_date)) : date('F j, Y') }}</strong></p>
                <p>Statement Period: {{ $payment->billing->statement_period ?? date('F Y') }}</p>
                <p>Payment Method: <strong>{{ strtoupper($payment->payment_method ?? 'CASH') }}</strong></p>
                <p>Reference #: {{ $payment->reference_number ?? 'N/A' }}</p>
                <p>Processed By: {{ $payment->received_by ?? 'Admin Staff' }}</p>
            </div>
        </div>

        <table class="table-items">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th>Billing Period</th>
                    <th style="text-align:right;">Amount Paid</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        CBTVI Broadband Cable Service Settlement
                        @if(!empty($payment->notes))
                            <br><small style="color:#64748b; font-weight:normal;">Note: {{ $payment->notes }}</small>
                        @endif
                    </td>
                    <td>{{ $payment->billing->statement_period ?? date('F Y') }}</td>
                    <td style="text-align:right;">₱{{ number_format($payment->amount_paid, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <div class="total-box">
            <div>
                <div style="font-size:0.75rem; text-transform:uppercase; color:#64748b; font-weight:700;">Status</div>
                <div style="font-size:0.9rem; font-weight:800; color:#16a34a;">✓ Payment Settled & Confirmed</div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:0.75rem; text-transform:uppercase; color:#64748b; font-weight:700;">Total Amount Paid</div>
                <div style="font-size:1.6rem; font-weight:900; color:#dc2626;">₱{{ number_format($payment->amount_paid, 2) }}</div>
            </div>
        </div>

        <div class="footer-note">
            Thank you for your payment to Bogo Cable Television Inc. (CBTVI)!<br>
            Bantayan Island Branch &middot; Support Helpline: 0999 998 8209 &middot; Official Sales & Ledger Copy
        </div>
    </div>

</body>
</html>
