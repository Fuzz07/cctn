@extends('layouts.app')

@section('title', 'Account Settings')

@push('styles')
<style>
    body { background-color: var(--bg-page); color: var(--text-dark); }
    .client-settings { max-width: 1180px; margin: 2rem auto; padding: 0 1.5rem; }
    .settings-heading { display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem; }
    .settings-heading h1 { margin: 0; font-size: 1.65rem; font-weight: 800; color: var(--text-dark); }
    .settings-heading p { margin: 0.3rem 0 0; color: var(--text-muted); font-size: 0.9rem; }
    .settings-back { display: inline-flex; align-items: center; gap: 0.45rem; color: var(--text-body); text-decoration: none; font-size: 0.88rem; font-weight: 700; }
    .settings-back:hover { color: #dc2626; }

    .settings-layout { display: grid; grid-template-columns: 230px minmax(0, 1fr); gap: 1.5rem; align-items: start; }
    .settings-nav { position: sticky; top: 6rem; padding: 0.65rem; border: 1px solid var(--border-light); border-radius: 16px; background: var(--bg-card); box-shadow: 0 4px 16px rgba(0,0,0,0.03); }
    .settings-nav-label { padding: 0.65rem 0.75rem 0.5rem; color: var(--text-faint); font-size: 0.7rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; }
    .settings-tab { width: 100%; display: flex; align-items: center; gap: 0.7rem; padding: 0.8rem 0.85rem; border: 0; border-radius: 10px; background: transparent; color: var(--text-muted); font: inherit; font-size: 0.86rem; font-weight: 700; text-align: left; cursor: pointer; }
    .settings-tab:hover { color: var(--text-dark); background: var(--bg-page); }
    .settings-tab.active { color: #dc2626; background: #fef2f2; }
    :root[data-theme="dark"] .settings-tab.active { background: rgba(220, 38, 38, 0.12); color: #f87171; }

    .settings-panel { display: none; }
    .settings-panel.active { display: block; }
    .settings-card { padding: 1.75rem; border: 1px solid var(--border-light); border-radius: 18px; background: var(--bg-card); box-shadow: 0 4px 16px rgba(0,0,0,0.03); }
    .settings-card + .settings-card { margin-top: 1.25rem; }
    .settings-card-header { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-bottom: 1.4rem; }
    .settings-card-title { display: flex; align-items: center; gap: 0.55rem; margin: 0; color: var(--text-dark); font-size: 1.05rem; font-weight: 800; }
    .settings-card-description { margin: -0.8rem 0 1.4rem; color: var(--text-muted); font-size: 0.85rem; line-height: 1.55; }

    .settings-form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .settings-form-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }
    .settings-field { display: flex; flex-direction: column; gap: 0.35rem; margin-bottom: 1rem; }
    .settings-label { color: var(--text-body); font-size: 0.82rem; font-weight: 700; }
    .settings-input { width: 100%; box-sizing: border-box; padding: 0.7rem 0.85rem; border: 1px solid var(--border); border-radius: 10px; background: var(--bg-page); color: var(--text-dark); font: inherit; font-size: 0.88rem; }
    .settings-input:focus { outline: none; border-color: #dc2626; background: var(--bg-card); box-shadow: 0 0 0 3px rgba(220,38,38,0.1); }
    .settings-save { display: inline-flex; align-items: center; gap: 0.45rem; padding: 0.72rem 1.2rem; border: 0; border-radius: 10px; background: #0f172a; color: #fff; font-weight: 700; cursor: pointer; }
    .settings-save:hover { background: #dc2626; }

    .service-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.25rem; align-items: start; }
    .service-grid .settings-card { height: 100%; box-sizing: border-box; }
    .status-pill { display: inline-block; flex-shrink: 0; padding: 0.25rem 0.7rem; border-radius: 999px; font-size: 0.72rem; font-weight: 700; white-space: nowrap; }
    .status-pill.active { color: #15803d; border: 1px solid rgba(34,197,94,0.35); background: rgba(34,197,94,0.12); }
    .status-pill.inactive { color: #b91c1c; border: 1px solid rgba(220,38,38,0.3); background: rgba(220,38,38,0.08); }
    .status-pill.pending { color: #a16207; border: 1px solid rgba(245,158,11,0.4); background: rgba(245,158,11,0.12); }
    .status-pill.neutral { color: var(--text-muted); border: 1px solid var(--border-light); background: var(--bg-subtle); }
    .service-facts { display: grid; gap: 0.55rem; margin: 0 0 1rem; }
    .service-facts div { display: flex; justify-content: space-between; gap: 1rem; font-size: 0.86rem; }
    .service-facts dt { color: var(--text-muted); }
    .service-facts dd { margin: 0; color: var(--text-dark); font-weight: 700; text-align: right; }
    .service-note { margin: 0 0 1rem; color: var(--text-muted); font-size: 0.86rem; line-height: 1.55; }
    .plan-list { display: flex; flex-direction: column; gap: 0.6rem; }
    .plan-row { display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.7rem 0.85rem; border: 1px solid var(--border-light); border-radius: 12px; }
    .plan-row strong { display: block; color: var(--text-dark); font-size: 0.88rem; }
    .plan-row span { color: var(--text-muted); font-size: 0.78rem; }
    .subscribe-button { flex-shrink: 0; padding: 0.5rem 0.95rem; border-radius: 10px; background: #dc2626; color: #fff !important; font-size: 0.82rem; font-weight: 700; text-decoration: none; }
    .subscribe-button:hover { background: #b91c1c; }
    .danger-button { padding: 0.68rem 1.05rem; border: 1px solid rgba(220,38,38,0.35); border-radius: 10px; background: transparent; color: #b91c1c; font-size: 0.86rem; font-weight: 700; cursor: pointer; }
    .danger-button:hover { background: rgba(220,38,38,0.08); }
    .danger-button:disabled { opacity: 0.6; cursor: not-allowed; }
    .service-message { margin-bottom: 1rem; padding: 0.85rem 1rem; border: 1px solid rgba(245,158,11,0.4); border-radius: 8px; background: rgba(245,158,11,0.1); color: #92400e; font-size: 0.85rem; line-height: 1.45; }
    .service-message.info { border-color: rgba(59,130,246,0.35); background: rgba(59,130,246,0.08); color: #1e40af; }

    .client-confirm-modal { display: none; position: fixed; inset: 0; z-index: 100000; align-items: center; justify-content: center; padding: 1rem; }
    .client-confirm-modal.is-open { display: flex; }
    .client-confirm-modal__backdrop { position: absolute; inset: 0; background: rgba(15,23,42,0.55); backdrop-filter: blur(4px); }
    .client-confirm-modal__dialog { position: relative; width: min(100%, 430px); padding: 2rem; border-radius: 16px; background: var(--bg-card); box-shadow: 0 20px 60px rgba(0,0,0,0.18); text-align: center; }
    .client-confirm-modal__icon { width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.1rem; border-radius: 50%; background: rgba(220,38,38,0.1); color: #dc2626; }
    .client-confirm-modal__title { margin: 0 0 0.4rem; color: var(--text-dark); font-size: 1.1rem; }
    .client-confirm-modal__message { margin: 0 0 1.5rem; color: var(--text-muted); font-size: 0.9rem; line-height: 1.5; }
    .client-confirm-modal__actions { display: flex; gap: 0.75rem; }
    .client-confirm-modal__button { flex: 1; padding: 0.65rem 1rem; border-radius: 8px; font-size: 0.9rem; font-weight: 700; cursor: pointer; }
    .client-confirm-modal__cancel { border: 1px solid var(--border-light); background: var(--bg-subtle); color: var(--text-body); }
    .client-confirm-modal__confirm { border: 0; background: #dc2626; color: #fff; }

    :root[data-theme="dark"] .status-pill.active { color: #4ade80; }
    :root[data-theme="dark"] .status-pill.inactive,
    :root[data-theme="dark"] .danger-button { color: #f87171; }
    :root[data-theme="dark"] .status-pill.pending,
    :root[data-theme="dark"] .service-message { color: #fbbf24; }
    :root[data-theme="dark"] .service-message.info { color: #93c5fd; }

    @media (max-width: 860px) {
        .settings-layout { grid-template-columns: 1fr; }
        .settings-nav { position: static; display: grid; grid-template-columns: 1fr 1fr; }
        .settings-nav-label { grid-column: 1 / -1; }
        .service-grid { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
        .client-settings { margin-top: 1.25rem; padding: 0 1rem; }
        .settings-heading { align-items: flex-start; }
        .settings-heading p { max-width: 18rem; }
        .settings-card { padding: 1.2rem; }
        .settings-form-grid-2, .settings-form-grid-3 { grid-template-columns: 1fr; gap: 0; }
    }
</style>
@endpush

@section('content')
<div class="client-settings">
    <div class="settings-heading">
        <div>
            <h1>Settings</h1>
            <p>Manage your profile, subscription, and connection preferences.</p>
        </div>
        <a href="{{ route('client.dashboard') }}" class="settings-back"><i class="bi bi-arrow-left"></i> Dashboard</a>
    </div>

    <div class="settings-layout">
        <nav class="settings-nav" aria-label="Settings sections">
            <div class="settings-nav-label">Account settings</div>
            <button type="button" class="settings-tab" data-settings-tab="profile">
                <i class="bi bi-person"></i> Profile
            </button>
            <button type="button" class="settings-tab" data-settings-tab="service">
                <i class="bi bi-wifi"></i> Subscription &amp; Service
            </button>
        </nav>

        <div>
            <section class="settings-panel" data-settings-panel="profile">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <h2 class="settings-card-title"><i class="bi bi-pencil-square"></i> Edit Profile</h2>
                    </div>
                    <p class="settings-card-description">Update your personal information and password.</p>

                    <form action="{{ route('client.update-profile') }}" method="POST">
                        @csrf
                        <div class="settings-form-grid-3">
                            <div class="settings-field">
                                <label class="settings-label" for="firstname">First Name</label>
                                <input type="text" name="firstname" id="firstname" class="settings-input" value="{{ old('firstname', $client->firstname) }}" required data-restrict="name" autocomplete="given-name">
                            </div>
                            <div class="settings-field">
                                <label class="settings-label" for="middlename">Middle Name</label>
                                <input type="text" name="middlename" id="middlename" class="settings-input" value="{{ old('middlename', $client->middlename) }}" data-restrict="name" autocomplete="additional-name">
                            </div>
                            <div class="settings-field">
                                <label class="settings-label" for="lastname">Last Name</label>
                                <input type="text" name="lastname" id="lastname" class="settings-input" value="{{ old('lastname', $client->lastname) }}" required data-restrict="name" autocomplete="family-name">
                            </div>
                        </div>

                        <div class="settings-form-grid-2">
                            <div class="settings-field">
                                <label class="settings-label" for="email">Email Address</label>
                                <input type="email" name="email" id="email" class="settings-input" value="{{ old('email', $client->email) }}" required autocomplete="email">
                            </div>
                            <div class="settings-field">
                                <label class="settings-label" for="username">Username</label>
                                <input type="text" name="username" id="username" class="settings-input" value="{{ old('username', $client->username) }}" required autocomplete="username">
                            </div>
                        </div>

                        <div class="settings-form-grid-3">
                            <div class="settings-field">
                                <label class="settings-label" for="contact_no">Contact No.</label>
                                <input type="tel" name="contact_no" id="contact_no" class="settings-input" value="{{ old('contact_no', $client->contact_no) }}" required data-restrict="mobile" inputmode="numeric" maxlength="11" pattern="09[0-9]{9}" placeholder="09123456789" autocomplete="tel">
                            </div>
                            <div class="settings-field">
                                <label class="settings-label" for="profile_birthdate">Birth Date</label>
                                <input type="date" name="birthdate" id="profile_birthdate" class="settings-input" value="{{ old('birthdate', optional($client->birthdate)->format('Y-m-d')) }}" required>
                            </div>
                            <div class="settings-field">
                                <label class="settings-label" for="profile_age">Age</label>
                                <input type="number" id="profile_age" class="settings-input" readonly data-age-for="profile_birthdate" style="background:var(--bg-subtle); cursor:not-allowed;">
                            </div>
                        </div>

                        <div class="settings-form-grid-3">
                            <div class="settings-field">
                                <label class="settings-label" for="gender">Gender</label>
                                <select name="gender" id="gender" class="settings-input" required>
                                    <option value="Male" {{ old('gender', $client->gender) === 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender', $client->gender) === 'Female' ? 'selected' : '' }}>Female</option>
                                </select>
                            </div>
                            <div class="settings-field">
                                <label class="settings-label" for="civil_status">Civil Status</label>
                                <select name="civil_status" id="civil_status" class="settings-input" required>
                                    @foreach (['Single', 'Married', 'Widowed', 'Legally Separated'] as $status)
                                        <option value="{{ $status }}" {{ old('civil_status', $client->civil_status) === $status ? 'selected' : '' }}>{{ $status }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="settings-field">
                                <label class="settings-label" for="place_of_birth">Place of Birth</label>
                                <input type="text" name="place_of_birth" id="place_of_birth" class="settings-input" value="{{ old('place_of_birth', $client->place_of_birth) }}" data-restrict="name">
                            </div>
                        </div>

                        <div class="settings-form-grid-3">
                            <div class="settings-field">
                                <label class="settings-label" for="profile_municipality">Municipality</label>
                                <select name="address_municipality" id="profile_municipality" class="settings-input" required>
                                    @foreach (\App\Support\ServiceArea::municipalities() as $municipality)
                                        <option value="{{ $municipality }}" {{ old('address_municipality', $client->address_municipality) === $municipality ? 'selected' : '' }}>{{ $municipality }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="settings-field">
                                <label class="settings-label" for="profile_barangay">Barangay</label>
                                <select name="address_barangay" id="profile_barangay" class="settings-input" required data-barangay-for="profile_municipality" data-old="{{ old('address_barangay', $client->address_barangay) }}"></select>
                            </div>
                            <div class="settings-field">
                                <label class="settings-label" for="address_province">Province</label>
                                <input type="text" name="address_province" id="address_province" class="settings-input" value="Cebu" readonly>
                            </div>
                        </div>

                        <hr style="border:0; border-top:1px solid var(--border-light); margin:1.2rem 0 1.5rem;">
                        <div class="settings-field">
                            <label class="settings-label" for="new_password">New Password <span style="font-weight:400; color:var(--text-faint);">(Leave blank to keep current)</span></label>
                            <input type="password" name="new_password" id="new_password" class="settings-input" placeholder="••••••••" autocomplete="new-password">
                        </div>
                        <div style="display:flex; justify-content:flex-end; margin-top:0.5rem;">
                            <button type="submit" class="settings-save"><i class="bi bi-check2"></i> Save Changes</button>
                        </div>
                    </form>
                </div>
            </section>

            <section class="settings-panel" data-settings-panel="service">
                <div class="service-grid">
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <h2 class="settings-card-title"><i class="bi bi-card-checklist"></i> Subscription</h2>
                            <span class="status-pill {{ $client->isAccountActive() ? 'active' : 'inactive' }}">{{ $client->isAccountActive() ? 'Active' : 'Inactive' }}</span>
                        </div>

                        @if($client->isAccountActive())
                            <dl class="service-facts">
                                <div><dt>Current plan</dt><dd>{{ $client->currentService?->service_name ?? 'Active plan' }}</dd></div>
                                @if($client->subscription_started_at)
                                    <div><dt>Active since</dt><dd>{{ $client->subscription_started_at->format('M d, Y') }}</dd></div>
                                @endif
                                @if($client->subscription_ends_at)
                                    <div><dt>Ends</dt><dd>{{ $client->subscription_ends_at->format('M d, Y') }}</dd></div>
                                @endif
                            </dl>
                            <p class="service-note">Unsubscribing makes your subscription inactive. Your account and history will remain available.</p>
                            <form action="{{ route('client.unsubscribe') }}" method="POST" onsubmit="return showClientConfirmModal(this);" data-confirm-title="Unsubscribe from your plan?" data-confirm-message="Your subscription will become inactive right away. Your account and history will be kept." data-confirm-label="Unsubscribe">
                                @csrf
                                <button type="submit" class="danger-button">Unsubscribe</button>
                            </form>
                        @else
                            <p class="service-note">
                                {{ $client->subscription_status_label }}.
                                @if($client->currentService) Your last plan was <strong>{{ $client->currentService->service_name }}</strong>. @endif
                                Choose a plan to subscribe again.
                            </p>
                            @if($pendingPlan)
                                <div class="service-message"><strong>{{ $pendingPlan->service?->service_name ?? 'Your plan' }} is awaiting activation</strong><br>Requested {{ $pendingPlan->created_at->format('M d, Y') }}.</div>
                            @endif
                            <div class="plan-list">
                                @forelse($availablePlans as $plan)
                                    <div class="plan-row">
                                        <div><strong>{{ $plan->service_name }}</strong><span>₱{{ number_format($plan->price, 0) }}/month</span></div>
                                        <a href="{{ route('client.book', ['service_id' => $plan->id]) }}" class="subscribe-button">Subscribe</a>
                                    </div>
                                @empty
                                    <p class="service-note" style="margin:0;">No plans are available right now.</p>
                                @endforelse
                            </div>
                        @endif
                    </div>

                    <div class="settings-card">
                        <div class="settings-card-header">
                            <h2 class="settings-card-title"><i class="bi bi-router"></i> Disconnection</h2>
                            @if($client->hasPendingDisconnectionRequest())
                                <span class="status-pill pending">Pending review</span>
                            @elseif($client->isDisconnected())
                                <span class="status-pill inactive">Disconnected</span>
                            @elseif($client->current_service_id)
                                <span class="status-pill active">Connected</span>
                            @else
                                <span class="status-pill neutral">No service</span>
                            @endif
                        </div>

                        @if(! \App\Models\Client::supportsDisconnectionRequests())
                            <p class="service-note">Disconnection requests are temporarily unavailable. Please contact support.</p>
                        @elseif($client->hasPendingDisconnectionRequest())
                            <div class="service-message"><strong>Disconnection request pending</strong><br>Sent {{ $client->disconnection_requested_at?->format('M d, Y \a\t g:i A') }}. An administrator will review it.</div>
                            <button type="button" class="danger-button" disabled>Request Pending</button>
                        @elseif($client->isDisconnected())
                            <p class="service-note">Your service was disconnected{{ $client->disconnection_reviewed_at ? ' on ' . $client->disconnection_reviewed_at->format('M d, Y') : '' }}. Your account and history are kept. Subscribe to a plan to reconnect.</p>
                        @elseif($client->canRequestDisconnection())
                            <p class="service-note">Request disconnection of your {{ $client->currentService?->service_name ?? 'current service' }}. An administrator will review the request.</p>
                            @if($client->disconnection_request_status === 'rejected')
                                <div class="service-message info">Your previous disconnection request was declined.</div>
                            @endif
                            <form action="{{ route('client.disconnection.request') }}" method="POST" onsubmit="return showClientConfirmModal(this);" data-confirm-title="Request Disconnection?" data-confirm-message="Your request will be sent to an administrator for review. Your account and history will be kept." data-confirm-label="Confirm Request">
                                @csrf
                                <button type="submit" class="danger-button">Request Disconnection</button>
                            </form>
                        @else
                            <p class="service-note">You do not have a connected service to disconnect.</p>
                        @endif
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<div id="client-confirm-modal" class="client-confirm-modal" role="dialog" aria-modal="true" aria-labelledby="client-confirm-title" aria-describedby="client-confirm-message" aria-hidden="true">
    <div class="client-confirm-modal__backdrop" onclick="hideClientConfirmModal()"></div>
    <div class="client-confirm-modal__dialog">
        <div class="client-confirm-modal__icon" aria-hidden="true"><i class="bi bi-exclamation-lg" style="font-size:1.5rem;"></i></div>
        <h2 id="client-confirm-title" class="client-confirm-modal__title"></h2>
        <p id="client-confirm-message" class="client-confirm-modal__message"></p>
        <div class="client-confirm-modal__actions">
            <button type="button" class="client-confirm-modal__button client-confirm-modal__cancel" onclick="hideClientConfirmModal()">Cancel</button>
            <button type="button" id="client-confirm-button" class="client-confirm-modal__button client-confirm-modal__confirm" onclick="confirmClientAction()"></button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/form-restrictions.js') }}?v={{ filemtime(public_path('assets/js/form-restrictions.js')) }}"></script>
@include('partials.address-age-scripts')
<script>
(function () {
    var initialTab = @json(request('tab') === 'service' ? 'service' : 'profile');
    var tabs = document.querySelectorAll('[data-settings-tab]');
    var panels = document.querySelectorAll('[data-settings-panel]');

    function selectTab(name, updateUrl) {
        tabs.forEach(function (tab) {
            var selected = tab.dataset.settingsTab === name;
            tab.classList.toggle('active', selected);
            tab.setAttribute('aria-selected', selected ? 'true' : 'false');
        });
        panels.forEach(function (panel) {
            panel.classList.toggle('active', panel.dataset.settingsPanel === name);
        });
        if (updateUrl && window.history.replaceState) {
            var url = new URL(window.location.href);
            if (name === 'profile') url.searchParams.delete('tab');
            else url.searchParams.set('tab', name);
            window.history.replaceState({}, '', url);
        }
    }

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () { selectTab(tab.dataset.settingsTab, true); });
    });
    selectTab(initialTab, false);
})();

var clientConfirmForm = null;
var clientConfirmTrigger = null;

function showClientConfirmModal(form) {
    if (form.dataset.confirmed === 'true') return true;
    clientConfirmForm = form;
    clientConfirmTrigger = document.activeElement;
    document.getElementById('client-confirm-title').textContent = form.dataset.confirmTitle || 'Confirm action?';
    document.getElementById('client-confirm-message').textContent = form.dataset.confirmMessage || 'Please confirm that you want to continue.';
    document.getElementById('client-confirm-button').textContent = form.dataset.confirmLabel || 'Confirm';
    var modal = document.getElementById('client-confirm-modal');
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    document.getElementById('client-confirm-button').focus();
    return false;
}

function hideClientConfirmModal() {
    var modal = document.getElementById('client-confirm-modal');
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    clientConfirmForm = null;
    if (clientConfirmTrigger) clientConfirmTrigger.focus();
}

function confirmClientAction() {
    if (!clientConfirmForm) return;
    var form = clientConfirmForm;
    form.dataset.confirmed = 'true';
    document.body.style.overflow = '';
    if (form.requestSubmit) form.requestSubmit();
    else form.submit();
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') hideClientConfirmModal();
});
</script>
@endpush
