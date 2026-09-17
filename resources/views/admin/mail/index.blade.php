@extends('layouts.admin')

@section('title', 'Email Diagnostics & Testing - BCTVI Admin')

@push('styles')
<style>
    .diagnostic-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    @media (max-width: 900px) {
        .diagnostic-grid { grid-template-columns: 1fr; }
    }
    .diag-card {
        background: #ffffff;
        border-radius: 14px;
        padding: 1.5rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    }
    .diag-title {
        font-size: 1.1rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .config-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.88rem;
    }
    .config-table tr {
        border-bottom: 1px solid #f1f5f9;
    }
    .config-table tr:last-child {
        border-bottom: none;
    }
    .config-table td {
        padding: 0.65rem 0.25rem;
    }
    .config-label {
        color: #64748b;
        font-weight: 600;
        width: 40%;
    }
    .config-val {
        color: #0f172a;
        font-family: monospace;
        font-weight: 700;
    }
    .badge-ok {
        background: #dcfce7;
        color: #15803d;
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 700;
    }
    .badge-warn {
        background: #fef3c7;
        color: #b45309;
        padding: 0.2rem 0.5rem;
        border-radius: 6px;
        font-size: 0.72rem;
        font-weight: 700;
    }
    .instruction-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 1rem;
        font-size: 0.85rem;
        color: #334155;
        line-height: 1.6;
        margin-top: 1rem;
    }
    .instruction-box pre {
        background: #1e293b;
        color: #f8fafc;
        padding: 0.75rem;
        border-radius: 6px;
        overflow-x: auto;
        font-size: 0.82rem;
        margin: 0.5rem 0;
    }
</style>
@endpush

@section('content')
<div class="page-header" style="margin-bottom: 1.5rem;">
    <div>
        <h1 class="page-title">Email Diagnostics & SMTP Testing</h1>
        <p style="color: #64748b; font-size: 0.9rem; margin: 0.25rem 0 0;">
            Verify Hostinger SMTP connectivity, diagnose email delivery issues, and send test emails to Gmail.
        </p>
    </div>
</div>

@if (session('success_message'))
    <div style="background:#dcfce7; color:#15803d; padding: 1.1rem 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 600; border: 1px solid #bbf7d0; display: flex; align-items: center; gap: 0.5rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        {{ session('success_message') }}
    </div>
@endif

@if (session('mail_error'))
    <div style="background:#fef2f2; color:#b91c1c; padding: 1.1rem 1.25rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 600; border: 1px solid #fecaca; display: flex; align-items: flex-start; gap: 0.5rem;">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="flex-shrink:0; margin-top:2px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        <div>{{ session('mail_error') }}</div>
    </div>
@endif

<div class="diagnostic-grid">
    <!-- Configuration Card -->
    <div class="diag-card">
        <h3 class="diag-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
            Current Active Mail Settings
        </h3>

        <table class="config-table">
            <tr>
                <td class="config-label">MAIL_MAILER</td>
                <td class="config-val">
                    {{ $config['mailer'] ?: 'smtp' }}
                    <span class="badge-ok">Active</span>
                </td>
            </tr>
            <tr>
                <td class="config-label">MAIL_HOST</td>
                <td class="config-val">
                    {{ $config['host'] ?: '(empty)' }}
                    @if($config['host'] === 'mailhog')
                        <span class="badge-warn" title="Mailhog cannot deliver to real Gmail inboxes">Local Only</span>
                    @elseif(str_contains($config['host'] ?? '', 'hostinger'))
                        <span class="badge-ok">Hostinger</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="config-label">MAIL_PORT</td>
                <td class="config-val">{{ $config['port'] ?: '(empty)' }}</td>
            </tr>
            <tr>
                <td class="config-label">MAIL_ENCRYPTION</td>
                <td class="config-val">{{ $config['encryption'] ?: '(none)' }}</td>
            </tr>
            <tr>
                <td class="config-label">MAIL_USERNAME</td>
                <td class="config-val">{{ $config['username'] ?: '(empty)' }}</td>
            </tr>
            <tr>
                <td class="config-label">MAIL_PASSWORD</td>
                <td class="config-val">
                    @if($config['has_password'])
                        <span class="badge-ok">Configured (Hidden)</span>
                    @else
                        <span class="badge-warn">Missing / Empty</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="config-label">MAIL_FROM_ADDRESS</td>
                <td class="config-val">
                    {{ $config['from_address'] ?: '(empty)' }}
                    @if(!empty($config['username']) && !empty($config['from_address']) && strtolower(trim($config['username'])) !== strtolower(trim($config['from_address'])))
                        <span class="badge-warn" title="Hostinger requires From Address to match Username exactly">Mismatch!</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="config-label">MAIL_FROM_NAME</td>
                <td class="config-val">{{ $config['from_name'] ?: '(empty)' }}</td>
            </tr>
            <tr>
                <td class="config-label">QUEUE_CONNECTION</td>
                <td class="config-val">
                    {{ $config['queue'] ?: 'sync' }}
                    @if($config['queue'] === 'sync')
                        <span class="badge-ok">Direct Send</span>
                    @else
                        <span class="badge-warn">Requires Worker</span>
                    @endif
                </td>
            </tr>
        </table>

        @if($config['host'] === 'mailhog')
            <div style="background:#fffbeb; color:#92400e; border:1px solid #fde68a; border-radius:8px; padding:0.75rem; font-size:0.8rem; margin-top:1rem;">
                ⚠️ <strong>Notice:</strong> Your current mail host is set to <code>mailhog</code>. Emails are not sent to real Gmail recipients in this mode. Update your <code>.env</code> file with your Hostinger SMTP credentials to send live emails.
            </div>
        @endif
    </div>

    <!-- Live Test Card -->
    <div class="diag-card">
        <h3 class="diag-title">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
            Send Test Email to Recipient
        </h3>

        <p style="font-size: 0.88rem; color: #64748b; margin: 0 0 1.25rem;">
            Test real email delivery right now. Enter any Gmail or email address below and click <strong>Send Test Email</strong>. If there is an SMTP or Hostinger connection problem, the exact error will be displayed immediately.
        </p>

        <form action="{{ route('admin.mail.test.send') }}" method="POST">
            @csrf
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label class="form-label" style="font-size:0.85rem; font-weight:700; color:#334155; margin-bottom:0.4rem; display:block;">
                    Recipient Email Address (e.g. your personal Gmail)
                </label>
                <input type="email" name="test_email" class="form-control" placeholder="your-email@gmail.com" value="{{ old('test_email', auth('admin')->user()?->email) }}" required style="width:100%; padding:0.75rem; border:1px solid #cbd5e1; border-radius:8px; font-size:0.95rem;">
            </div>

            <button type="submit" class="btn-primary" style="background:#dc2626; color:#ffffff; border:none; padding:0.85rem 1.5rem; border-radius:8px; font-weight:700; font-size:0.95rem; cursor:pointer; display:inline-flex; align-items:center; gap:0.5rem; width:100%; justify-content:center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                Send Test Email Now
            </button>
        </form>

        <div class="instruction-box">
            <strong>📋 Hostinger SMTP Recommended Settings (.env):</strong>
            <pre>MAIL_MAILER=smtp
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=465
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_email_password
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="CCTN Broadband"</pre>
            <ul style="margin: 0.5rem 0 0; padding-left: 1.2rem; font-size: 0.8rem; color: #64748b;">
                <li><strong>Crucial:</strong> <code>MAIL_FROM_ADDRESS</code> MUST match <code>MAIL_USERNAME</code> exactly, or Hostinger will reject it with error 550.</li>
                <li>After editing <code>.env</code> on Hostinger, run <code>php artisan config:clear</code> to reload settings.</li>
            </ul>
        </div>
    </div>
</div>

@if(count($recentLogs) > 0)
    <div class="diag-card" style="margin-top: 1.5rem;">
        <h3 class="diag-title" style="color: #475569; font-size: 0.95rem;">
            Recent Email / Notification Logs
        </h3>
        <div style="background: #0f172a; color: #94a3b8; font-family: monospace; font-size: 0.78rem; padding: 1rem; border-radius: 8px; max-height: 200px; overflow-y: auto;">
            @foreach($recentLogs as $log)
                <div style="margin-bottom: 0.25rem; {{ stripos($log, 'error') !== false ? 'color:#f87171;' : (stripos($log, 'warning') !== false ? 'color:#fbbf24;' : '') }}">
                    {{ $log }}
                </div>
            @endforeach
        </div>
    </div>
@endif

@endsection
