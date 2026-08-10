@extends('layouts.admin')

@section('title', 'Sales Report - BCTVI Bantayan')

@push('styles')
<style>
    .page-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem; flex-wrap:wrap; gap:1rem; }
    .page-title { font-size:1.5rem; font-weight:800; color:#0f172a; margin:0; }

    .revenue-banner { background:linear-gradient(135deg, #0f172a, #1e293b); border-radius:16px; padding:2rem; margin-bottom:1.5rem; color:#fff; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:1.5rem; }
    .revenue-label { font-size:.85rem; font-weight:700; color:rgba(255,255,255,.65); text-transform:uppercase; letter-spacing:.05em; margin-bottom:.5rem; }
    .revenue-amount { font-size:3rem; font-weight:800; color:#fff; line-height:1; }
    .revenue-sub { font-size:.85rem; color:rgba(255,255,255,.6); margin-top:.35rem; }

    .table-card { background:#fff; border-radius:12px; border:1px solid #e2e8f0; overflow-x:auto; }
    .data-table { width:100%; border-collapse:collapse; text-align:left; font-size:.88rem; }
    .data-table th { padding:.9rem 1rem; background:#f8fafc; border-bottom:2px solid #e2e8f0; color:#64748b; font-weight:700; text-transform:uppercase; font-size:.72rem; letter-spacing:.05em; }
    .data-table td { padding:.9rem 1rem; border-bottom:1px solid #f1f5f9; color:#334155; vertical-align:middle; }
    .data-table tbody tr:hover { background:#f8fafc; }

    .badge-method { padding:.3rem .7rem; border-radius:99px; font-size:.72rem; font-weight:700; text-transform:uppercase; }
    .badge-cash { background:#dcfce7; color:#15803d; }
    .badge-gcash { background:#dbeafe; color:#1d4ed8; }
    .badge-other { background:#f1f5f9; color:#475569; }
</style>
@endpush

@section('content')
<div class="page-header">
    <h1 class="page-title">Sales & Revenue Report</h1>
</div>

<div class="revenue-banner">
    <div>
        <div class="revenue-label">Total Revenue Collected</div>
        <div class="revenue-amount">₱{{ number_format($totalRevenue, 2) }}</div>
        <div class="revenue-sub">{{ $payments->count() }} payment(s) recorded</div>
    </div>
    <div style="text-align:right;">
        <div class="revenue-label">Last Payment</div>
        @if($payments->isNotEmpty())
            <div style="font-size:1.25rem; font-weight:700; color:#fff;">{{ $payments->first()->payment_date ? date('M d, Y', strtotime($payments->first()->payment_date)) : '—' }}</div>
            <div style="font-size:.85rem; color:rgba(255,255,255,.6);">₱{{ number_format($payments->first()->amount_paid, 2) }}</div>
        @else
            <div style="font-size:1rem; color:rgba(255,255,255,.6);">No payments yet</div>
        @endif
    </div>
</div>

<div class="table-card">
    <table class="data-table">
        <thead>
            <tr>
                <th>Receipt #</th>
                <th>Client</th>
                <th>Account #</th>
                <th>Period</th>
                <th>Amount Paid</th>
                <th>Method</th>
                <th>Received By</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $pay)
                @php
                    $methodClass = match(strtolower($pay->payment_method ?? '')) {
                        'cash' => 'badge-cash',
                        'gcash' => 'badge-gcash',
                        default => 'badge-other'
                    };
                @endphp
                <tr>
                    <td><strong style="color:#dc2626; font-family:monospace; font-size:.85rem;">{{ $pay->receipt_no }}</strong></td>
                    <td>
                        <div style="font-weight:700; color:#0f172a;">{{ $pay->client->firstname ?? '' }} {{ $pay->client->lastname ?? '' }}</div>
                    </td>
                    <td style="font-family:monospace; font-size:.85rem; color:#64748b;">{{ $pay->account_number }}</td>
                    <td style="color:#475569;">{{ $pay->billing->statement_period ?? '—' }}</td>
                    <td><strong style="font-size:1rem; color:#0f172a;">₱{{ number_format($pay->amount_paid, 2) }}</strong></td>
                    <td><span class="badge-method {{ $methodClass }}">{{ $pay->payment_method }}</span></td>
                    <td style="color:#64748b;">{{ $pay->received_by ?? '—' }}</td>
                    <td style="color:#64748b;">{{ $pay->payment_date ? date('M d, Y', strtotime($pay->payment_date)) : '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:3rem; color:#94a3b8;">No payment records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
