@extends('layouts.app')

@section('title', 'My Billing & Statements - BCTVI Bantayan')

@section('content')
<div class="fade-in" style="padding: 1.5rem 0;">
    <div style="margin-bottom: 2rem;">
        <h2 style="font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-bottom: 0.35rem;">Statement of Account &amp; Billing History</h2>
        <p style="color: #64748b; font-size: 0.88rem; margin: 0;">Track your subscription billing statements and payment history.</p>
    </div>

    <!-- BALANCE & ACCOUNT SUMMARY -->
    <div class="glass-card" style="margin-bottom: 1.5rem; padding: 1.5rem; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
            <div>
                <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; font-weight: 700; color: #64748b; margin-bottom: 0.25rem;">Total Outstanding Balance</div>
                <div style="font-size: 2rem; font-weight: 800; color: {{ ($balance > 0) ? '#dc2626' : '#16a34a' }};">
                    ₱{{ number_format($balance, 2) }}
                    @if ($balance == 0)
                        <span style="font-size: 0.8rem; font-weight: 700; background: #dcfce7; color: #15803d; padding: 0.25rem 0.65rem; border-radius: 20px; vertical-align: middle; margin-left: 0.5rem;">Fully Paid</span>
                    @endif
                </div>
            </div>
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 0.85rem 1.25rem; display: flex; align-items: center; gap: 1rem;">
                <div>
                    <span style="font-size: 0.7rem; font-weight: 700; color: #64748b; display: block; text-transform: uppercase;">Your Account Number</span>
                    <strong style="font-size: 1.1rem; color: #dc2626; font-family: monospace;">{{ $client->account_number ?? 'N/A' }}</strong>
                </div>
            </div>
            <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                <a href="{{ route('client.payment-methods') }}" class="btn" style="background: #dc2626; color: #fff; border-radius: 10px; font-weight: 700; font-size: 0.85rem; text-decoration: none; padding: 0.6rem 1.15rem; display: inline-flex; align-items: center; gap: 0.4rem;">
                    <i class="bi bi-wallet2"></i> Manage Payment Methods
                </a>
                <a href="{{ route('client.dashboard') }}" class="btn btn-secondary" style="border-radius: 10px; font-weight: 700; font-size: 0.85rem;">← Back to Dashboard</a>
            </div>
        </div>
    </div>

    <!-- DIGITAL PAYMENT INSTRUCTION BANNER -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; border-radius: 16px; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 4px 15px rgba(0,0,0,0.06); display: flex; align-items: flex-start; gap: 1.25rem;">
        <div style="width: 48px; height: 48px; background: #dc2626; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; box-shadow: 0 4px 12px rgba(220,38,38,0.4);">
            💳
        </div>
        <div style="flex: 1;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.5rem;">
                <h4 style="font-size: 1.05rem; font-weight: 800; margin: 0 0 0.35rem; color: #ffffff;">How to Pay Your Bill (Digital Payments Only)</h4>
                <a href="{{ route('client.payment-methods') }}" style="color: #fca5a5; font-size: 0.8rem; font-weight: 700; text-decoration: none;">
                    Add / View Saved Methods &rarr;
                </a>
            </div>
            <p style="font-size: 0.88rem; color: #cbd5e1; margin: 0 0 0.6rem; line-height: 1.5;">
                We accept secure digital payments via <strong>GCash, Maya, and Online Bank Transfers (BDO, BPI, UnionBank)</strong>. Always use your Account Number (<strong style="color: #f87171; font-family: monospace;">{{ $client->account_number ?? 'N/A' }}</strong>) as reference.
            </p>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; font-size: 0.8rem; color: #94a3b8; font-weight: 600;">
                <span>✓ Instant verification with saved methods</span>
                <span>✓ GCash / Maya E-Wallet Transfer</span>
                <span>✓ Official digital statement tracking</span>
            </div>
        </div>
    </div>

    <!-- STATEMENTS TABLE -->
    <div class="glass-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
        <h3 style="font-size: 1.05rem; font-weight: 700; color: #0f172a; margin-bottom: 1.25rem;">Billing Statements &amp; Payment History</h3>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 0.88rem;">
                <thead>
                    <tr style="border-bottom: 2px solid #e2e8f0; text-align: left; color: #64748b; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em;">
                        <th style="padding: 0.75rem 1rem;">Account No.</th>
                        <th style="padding: 0.75rem 1rem;">Statement Period</th>
                        <th style="padding: 0.75rem 1rem;">Amount Due</th>
                        <th style="padding: 0.75rem 1rem;">Penalty</th>
                        <th style="padding: 0.75rem 1rem;">Total Due</th>
                        <th style="padding: 0.75rem 1rem;">Due Date</th>
                        <th style="padding: 0.75rem 1rem;">Payment Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($statements as $row)
                        @php
                            $st = strtolower($row->status ?? 'unpaid');
                            $badge_bg = '#fef3c7'; $badge_fg = '#d97706'; $badge_label = 'UNPAID';
                            if ($st === 'paid') {
                                $badge_bg = '#dcfce7'; $badge_fg = '#15803d'; $badge_label = 'PAID';
                            } elseif ($st === 'overdue') {
                                $badge_bg = '#fee2e2'; $badge_fg = '#dc2626'; $badge_label = 'OVERDUE';
                            }
                        @endphp
                        <tr style="border-bottom: 1px solid #f1f5f9;">
                            <td style="padding: 1rem;"><strong style="color: #dc2626; font-family: monospace; font-size: 0.85rem;">{{ $row->account_number }}</strong></td>
                            <td style="padding: 1rem; color: #475569; font-weight: 600;">{{ $row->statement_period }}</td>
                            <td style="padding: 1rem; color: #475569;">₱{{ number_format($row->amount_due, 2) }}</td>
                            <td style="padding: 1rem; color: #dc2626;">₱{{ number_format($row->penalty_amount, 2) }}</td>
                            <td style="padding: 1rem;"><strong style="color: #0f172a; font-size: 0.95rem;">₱{{ number_format($row->total_amount_due, 2) }}</strong></td>
                            <td style="padding: 1rem; color: #64748b;">{{ date('M d, Y', strtotime($row->due_date)) }}</td>
                            <td style="padding: 1rem;">
                                <span style="background: {{ $badge_bg }}; color: {{ $badge_fg }}; padding: 0.3rem 0.75rem; border-radius: 20px; font-weight: 800; font-size: 0.72rem; letter-spacing: 0.04em; display: inline-block;">
                                    ● {{ $badge_label }}
                                </span>
                                @if ($st === 'paid' && !empty($row->paid_at))
                                    <div style="font-size: 0.72rem; color: #16a34a; margin-top: 0.25rem; font-weight: 700;">
                                        ✓ Settled via digital payment on {{ date('M d, Y', strtotime($row->paid_at)) }}
                                    </div>
                                @elseif ($st !== 'paid')
                                    <div style="font-size: 0.7rem; color: #64748b; margin-top: 0.25rem;">
                                        Pay via digital method with Acc#
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" style="text-align: center; padding: 2.5rem; color: #94a3b8; font-weight: 500;">No billing statements available yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
