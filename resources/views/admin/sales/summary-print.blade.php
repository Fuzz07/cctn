<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Revenue Summary Report - CBTVI</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            color: #0f172a;
            margin: 0;
            padding: 24px;
        }
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #dc2626;
            padding-bottom: 1rem;
            margin-bottom: 1.5rem;
        }
        .brand-title {
            font-size: 1.8rem;
            font-weight: 900;
            color: #dc2626;
            margin: 0;
        }
        .brand-sub {
            font-size: 0.85rem;
            color: #64748b;
            margin: 2px 0 0 0;
            font-weight: 600;
        }
        .report-meta {
            text-align: right;
        }
        .report-meta h2 {
            margin: 0;
            font-size: 1.25rem;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
        }
        .report-meta p {
            margin: 4px 0 0 0;
            font-size: 0.85rem;
            color: #64748b;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .sum-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1rem 1.25rem;
        }
        .sum-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
        }
        .sum-val {
            font-size: 1.5rem;
            font-weight: 900;
            color: #0f172a;
            margin-top: 4px;
        }

        .table-data {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
            margin-bottom: 2rem;
        }
        .table-data th {
            background: #f1f5f9;
            color: #475569;
            text-transform: uppercase;
            font-size: 0.72rem;
            letter-spacing: 0.05em;
            padding: 10px 12px;
            border-bottom: 2px solid #cbd5e1;
            text-align: left;
        }
        .table-data td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #334155;
        }
        .table-data tr:nth-child(even) {
            background: #fafafa;
        }

        .total-row td {
            font-weight: 800;
            font-size: 0.95rem;
            background: #fef2f2;
            color: #dc2626;
            border-top: 2px solid #dc2626;
        }

        .footer {
            margin-top: 2rem;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            font-size: 0.8rem;
            color: #64748b;
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
        }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print">
        <button onclick="window.print()" class="btn-print">🖨️ Print Sales Revenue Statement</button>
    </div>

    <div class="report-header">
        <div>
            <h1 class="brand-title">CBTVI Bogo Cable Television Inc.</h1>
            <p class="brand-sub">Official Sales & Revenue Ledger Statement &middot; Bantayan Island Branch</p>
        </div>
        <div class="report-meta">
            <h2>Sales Revenue Summary</h2>
            <p>Generated on {{ date('F j, Y h:i A') }}</p>
        </div>
    </div>

    <div class="summary-cards">
        <div class="sum-card">
            <div class="sum-label">Total Revenue Collected</div>
            <div class="sum-val" style="color:#dc2626;">₱{{ number_format($totalRevenue, 2) }}</div>
        </div>
        <div class="sum-card">
            <div class="sum-label">Total Payment Transactions</div>
            <div class="sum-val">{{ $payments->count() }}</div>
        </div>
        <div class="sum-card">
            <div class="sum-label">Average Transaction Value</div>
            <div class="sum-val">₱{{ $payments->count() > 0 ? number_format($totalRevenue / $payments->count(), 2) : '0.00' }}</div>
        </div>
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th>Receipt #</th>
                <th>Client Name</th>
                <th>Account #</th>
                <th>Period</th>
                <th>Method</th>
                <th>Ref #</th>
                <th>Received By</th>
                <th>Date</th>
                <th style="text-align:right;">Amount Paid</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $pay)
                <tr>
                    <td><strong style="font-family:monospace; color:#dc2626;">{{ $pay->receipt_no }}</strong></td>
                    <td><strong>{{ $pay->client->firstname ?? '' }} {{ $pay->client->lastname ?? '' }}</strong></td>
                    <td style="font-family:monospace;">{{ $pay->account_number }}</td>
                    <td>{{ $pay->billing->statement_period ?? '—' }}</td>
                    <td>{{ strtoupper($pay->payment_method ?? 'CASH') }}</td>
                    <td>{{ $pay->reference_number ?? '—' }}</td>
                    <td>{{ $pay->received_by ?? 'Staff' }}</td>
                    <td>{{ $pay->payment_date ? date('M d, Y', strtotime($pay->payment_date)) : '—' }}</td>
                    <td style="text-align:right;"><strong>₱{{ number_format($pay->amount_paid, 2) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding:2rem; color:#94a3b8;">No payment records found.</td>
                </tr>
            @endforelse
            @if($payments->isNotEmpty())
                <tr class="total-row">
                    <td colspan="8" style="text-align:right;">TOTAL REVENUE COLLECTED:</td>
                    <td style="text-align:right;">₱{{ number_format($totalRevenue, 2) }}</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        <div>Certified Correct by CBTVI Accounting & Management</div>
        <div>Printed by Admin User &middot; Page 1 of 1</div>
    </div>

</body>
</html>
