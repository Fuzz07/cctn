@extends('layouts.app')

@section('title', 'Payment Methods - BCTVI Bantayan')

@push('styles')
<style>
    .pm-card-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.25rem;
        margin-top: 1.5rem;
    }

    .pm-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 18px;
        padding: 1.5rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
        transition: all 0.25s ease;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .pm-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        border-color: #cbd5e1;
    }

    .pm-card.is-default {
        border-color: #dc2626;
        background: linear-gradient(180deg, #ffffff 0%, #fffbfb 100%);
        box-shadow: 0 6px 20px rgba(220, 38, 38, 0.1);
    }

    .pm-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1.25rem;
    }

    .pm-icon-wrap {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #ffffff;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
    }

    .pm-badge-default {
        background: #fef2f2;
        color: #dc2626;
        border: 1px solid #fecaca;
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.3rem 0.65rem;
        border-radius: 99px;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .pm-type-title {
        font-size: 1.15rem;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 0.25rem 0;
    }

    .pm-account-num {
        font-family: 'Courier New', Courier, monospace;
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        letter-spacing: 0.05em;
        margin: 0.5rem 0;
    }

    .pm-account-name {
        font-size: 0.85rem;
        color: #64748b;
        font-weight: 600;
        margin: 0;
        text-transform: uppercase;
    }

    .pm-card-actions {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 1.5rem;
        padding-top: 1rem;
        border-top: 1px solid #f1f5f9;
        gap: 0.5rem;
    }

    .btn-pm-action {
        font-size: 0.8rem;
        font-weight: 700;
        padding: 0.45rem 0.85rem;
        border-radius: 8px;
        border: 1px solid transparent;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
    }

    .btn-pm-default {
        background: #f8fafc;
        color: #475569;
        border-color: #cbd5e1;
    }
    .btn-pm-default:hover {
        background: #dc2626;
        color: #fff;
        border-color: #dc2626;
    }

    .btn-pm-delete {
        background: transparent;
        color: #ef4444;
        border-color: transparent;
    }
    .btn-pm-delete:hover {
        background: #fef2f2;
        color: #dc2626;
    }

    /* Modal Styling */
    .pm-modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        z-index: 1000;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .pm-modal.active {
        display: flex;
    }
    .pm-modal-content {
        background: #ffffff;
        border-radius: 20px;
        max-width: 520px;
        width: 100%;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
        overflow: hidden;
        animation: pmModalIn 0.25s ease-out forwards;
    }
    @keyframes pmModalIn {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
</style>
@endpush

@section('content')
<div class="fade-in" style="max-width: 1100px; margin: 2rem auto; padding: 0 1.5rem;">
    <!-- Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
        <div>
            <h1 style="font-size: 1.65rem; font-weight: 800; color: #0f172a; margin: 0 0 0.35rem 0;">Payment Methods</h1>
            <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Manage your digital payment methods for broadband bookings and bill payments.</p>
        </div>
        <div style="display: flex; gap: 0.75rem;">
            <a href="{{ route('client.billing') }}" class="btn" style="background: #f1f5f9; color: #334155; font-weight: 700; border-radius: 10px; padding: 0.65rem 1.2rem; text-decoration: none;">
                <i class="bi bi-arrow-left"></i> View Billing
            </a>
            <button type="button" class="btn" onclick="openAddModal()" style="background: #dc2626; color: #ffffff; font-weight: 700; border-radius: 10px; padding: 0.65rem 1.25rem; display: inline-flex; align-items: center; gap: 0.5rem; border: none; cursor: pointer; box-shadow: 0 4px 14px rgba(220, 38, 38, 0.3);">
                <i class="bi bi-plus-lg"></i> Add Payment Method
            </button>
        </div>
    </div>

    <!-- Digital Payment Notice -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; border-radius: 16px; padding: 1.5rem; margin-bottom: 2rem; display: flex; align-items: center; gap: 1.25rem; box-shadow: 0 4px 15px rgba(0,0,0,0.06);">
        <div style="width: 48px; height: 48px; background: rgba(220, 38, 38, 0.2); border: 1.5px solid #dc2626; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0; color: #f87171;">
            <i class="bi bi-shield-check"></i>
        </div>
        <div>
            <h4 style="font-size: 1.05rem; font-weight: 800; margin: 0 0 0.25rem; color: #ffffff;">Secure Digital Payments Only</h4>
            <p style="font-size: 0.85rem; color: #94a3b8; margin: 0; line-height: 1.5;">
                Client transactions are processed seamlessly via digital payment methods (GCash, Maya, Bank Transfer, Card). Save your preferred method below to auto-select it when booking appointments or paying monthly statements.
            </p>
        </div>
    </div>

    <!-- Payment Methods Grid -->
    @if ($paymentMethods->count() > 0)
        <div class="pm-card-grid">
            @foreach ($paymentMethods as $pm)
                <div class="pm-card {{ $pm->is_default ? 'is-default' : '' }}">
                    <div>
                        <div class="pm-card-header">
                            <div class="pm-icon-wrap" style="background: {{ $pm->theme_color }};">
                                <i class="bi {{ $pm->icon }}"></i>
                            </div>
                            @if ($pm->is_default)
                                <span class="pm-badge-default">
                                    <i class="bi bi-check-circle-fill"></i> Default Method
                                </span>
                            @endif
                        </div>

                        <div class="pm-type-title">{{ $pm->provider_name }}</div>
                        <div style="font-size: 0.78rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">{{ $pm->formatted_type }}</div>
                        <div class="pm-account-num">{{ $pm->masked_account_number }}</div>
                        <div class="pm-account-name">{{ $pm->account_name }}</div>

                        @if ($pm->notes)
                            <div style="font-size: 0.78rem; color: #64748b; margin-top: 0.5rem; font-style: italic;">
                                "{{ $pm->notes }}"
                            </div>
                        @endif
                    </div>

                    <div class="pm-card-actions">
                        <div>
                            @if (!$pm->is_default)
                                <form action="{{ route('client.payment-methods.default', $pm->id) }}" method="POST" style="display: inline;">
                                    @csrf
                                    <button type="submit" class="btn-pm-action btn-pm-default">
                                        <i class="bi bi-star"></i> Set Default
                                    </button>
                                </form>
                            @else
                                <span style="font-size: 0.8rem; font-weight: 700; color: #16a34a; display: inline-flex; align-items: center; gap: 0.3rem;">
                                    <i class="bi bi-check-lg"></i> Preferred for payments
                                </span>
                            @endif
                        </div>
                        <div style="display: flex; gap: 0.35rem;">
                            <button type="button" class="btn-pm-action" style="background: #f8fafc; color: #475569; border-color: #e2e8f0;" onclick="openEditModal({{ json_encode($pm) }})">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('client.payment-methods.destroy', $pm->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this payment method?');" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-pm-action btn-pm-delete" title="Remove method">
                                    <i class="bi bi-trash3"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <!-- Empty State -->
        <div style="background: #ffffff; border: 2px dashed #cbd5e1; border-radius: 20px; padding: 4rem 2rem; text-align: center;">
            <div style="width: 72px; height: 72px; background: #fef2f2; color: #dc2626; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 2rem; margin-bottom: 1.25rem;">
                <i class="bi bi-credit-card"></i>
            </div>
            <h3 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0 0 0.5rem 0;">No Payment Methods Added Yet</h3>
            <p style="color: #64748b; font-size: 0.9rem; max-width: 440px; margin: 0 auto 1.5rem auto;">
                Add your preferred GCash, Maya, Bank Transfer, or Debit/Credit Card to quickly settle appointments and bills.
            </p>
            <button type="button" onclick="openAddModal()" class="btn" style="background: #dc2626; color: #ffffff; font-weight: 700; border-radius: 10px; padding: 0.75rem 1.5rem; border: none; cursor: pointer;">
                <i class="bi bi-plus-lg"></i> Add Your First Payment Method
            </button>
        </div>
    @endif
</div>

<!-- ADD PAYMENT METHOD MODAL -->
<div class="pm-modal" id="addModal">
    <div class="pm-modal-content">
        <div style="padding: 1.5rem; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-weight: 800; font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="bi bi-plus-circle-fill" style="color: #dc2626;"></i> Add Digital Payment Method
            </div>
            <button type="button" onclick="closeAddModal()" style="background: none; border: none; color: #94a3b8; font-size: 1.4rem; cursor: pointer;"><i class="bi bi-x-lg"></i></button>
        </div>

        <form action="{{ route('client.payment-methods.store') }}" method="POST" style="padding: 1.5rem;">
            @csrf
            
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Payment Category *</label>
                <select name="payment_type" id="add_payment_type" class="form-control" style="width: 100%; padding: 0.7rem; border-radius: 8px; border: 1px solid #cbd5e1; font-weight: 600;" required onchange="onTypeChanged()">
                    <option value="gcash">GCash (E-Wallet)</option>
                    <option value="maya">Maya (E-Wallet)</option>
                    <option value="bank_transfer">Bank Transfer (BDO, BPI, UnionBank, etc.)</option>
                    <option value="credit_card">Credit Card (Visa / Mastercard)</option>
                    <option value="debit_card">Debit Card</option>
                </select>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Provider / Bank Name *</label>
                <input type="text" name="provider_name" id="add_provider_name" class="form-control" value="GCash" style="width: 100%; padding: 0.7rem; border-radius: 8px; border: 1px solid #cbd5e1;" required placeholder="e.g. GCash, Maya, BDO, BPI">
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Account Holder Name *</label>
                <input type="text" name="account_name" class="form-control" value="{{ $client->firstname }} {{ $client->lastname }}" style="width: 100%; padding: 0.7rem; border-radius: 8px; border: 1px solid #cbd5e1;" required placeholder="Registered full name on account">
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;" id="account_num_label">Mobile / Account Number *</label>
                <input type="text" name="account_number" id="add_account_number" class="form-control" value="{{ $client->contact_no }}" style="width: 100%; padding: 0.7rem; border-radius: 8px; border: 1px solid #cbd5e1;" required placeholder="0917XXXXXXX or Account / Card Number">
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Notes / Remarks (Optional)</label>
                <input type="text" name="notes" class="form-control" style="width: 100%; padding: 0.7rem; border-radius: 8px; border: 1px solid #cbd5e1;" placeholder="e.g. Primary personal GCash number">
            </div>

            <div style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="is_default" id="add_is_default" value="1" {{ $paymentMethods->isEmpty() ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #dc2626;">
                <label for="add_is_default" style="font-size: 0.85rem; font-weight: 700; color: #0f172a; cursor: pointer;">Set as default preferred payment method</label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" onclick="closeAddModal()" class="btn" style="background: #f1f5f9; color: #475569; font-weight: 700; border-radius: 8px; padding: 0.65rem 1.25rem; border: none; cursor: pointer;">Cancel</button>
                <button type="submit" class="btn" style="background: #dc2626; color: #ffffff; font-weight: 700; border-radius: 8px; padding: 0.65rem 1.5rem; border: none; cursor: pointer;">Save Method</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT PAYMENT METHOD MODAL -->
<div class="pm-modal" id="editModal">
    <div class="pm-modal-content">
        <div style="padding: 1.5rem; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #ffffff; display: flex; justify-content: space-between; align-items: center;">
            <div style="font-weight: 800; font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem;">
                <i class="bi bi-pencil-square" style="color: #dc2626;"></i> Edit Payment Method
            </div>
            <button type="button" onclick="closeEditModal()" style="background: none; border: none; color: #94a3b8; font-size: 1.4rem; cursor: pointer;"><i class="bi bi-x-lg"></i></button>
        </div>

        <form id="editForm" method="POST" style="padding: 1.5rem;">
            @csrf
            @method('PUT')
            
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Provider / Bank Name *</label>
                <input type="text" name="provider_name" id="edit_provider_name" class="form-control" style="width: 100%; padding: 0.7rem; border-radius: 8px; border: 1px solid #cbd5e1;" required>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Account Holder Name *</label>
                <input type="text" name="account_name" id="edit_account_name" class="form-control" style="width: 100%; padding: 0.7rem; border-radius: 8px; border: 1px solid #cbd5e1;" required>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Account / Mobile Number *</label>
                <input type="text" name="account_number" id="edit_account_number" class="form-control" style="width: 100%; padding: 0.7rem; border-radius: 8px; border: 1px solid #cbd5e1;" required>
            </div>

            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.82rem; font-weight: 700; color: #334155; margin-bottom: 0.35rem;">Notes (Optional)</label>
                <input type="text" name="notes" id="edit_notes" class="form-control" style="width: 100%; padding: 0.7rem; border-radius: 8px; border: 1px solid #cbd5e1;">
            </div>

            <div style="margin-bottom: 1.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <input type="checkbox" name="is_default" id="edit_is_default" value="1" style="width: 18px; height: 18px; accent-color: #dc2626;">
                <label for="edit_is_default" style="font-size: 0.85rem; font-weight: 700; color: #0f172a; cursor: pointer;">Set as default preferred payment method</label>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                <button type="button" onclick="closeEditModal()" class="btn" style="background: #f1f5f9; color: #475569; font-weight: 700; border-radius: 8px; padding: 0.65rem 1.25rem; border: none; cursor: pointer;">Cancel</button>
                <button type="submit" class="btn" style="background: #dc2626; color: #ffffff; font-weight: 700; border-radius: 8px; padding: 0.65rem 1.5rem; border: none; cursor: pointer;">Update Method</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    function openAddModal() {
        document.getElementById('addModal').classList.add('active');
    }
    function closeAddModal() {
        document.getElementById('addModal').classList.remove('active');
    }

    function onTypeChanged() {
        const type = document.getElementById('add_payment_type').value;
        const provInput = document.getElementById('add_provider_name');
        const numLabel = document.getElementById('account_num_label');

        if (type === 'gcash') {
            provInput.value = 'GCash';
            numLabel.innerText = 'GCash Mobile Number *';
        } else if (type === 'maya') {
            provInput.value = 'Maya';
            numLabel.innerText = 'Maya Mobile Number *';
        } else if (type === 'bank_transfer') {
            provInput.value = 'BDO Unibank';
            numLabel.innerText = 'Bank Account Number *';
        } else if (type === 'credit_card' || type === 'debit_card') {
            provInput.value = 'Visa / Mastercard';
            numLabel.innerText = 'Card Number (Last 4 digits or Full) *';
        }
    }

    function openEditModal(pm) {
        document.getElementById('editForm').action = '/payment-methods/' + pm.id;
        document.getElementById('edit_provider_name').value = pm.provider_name || '';
        document.getElementById('edit_account_name').value = pm.account_name || '';
        document.getElementById('edit_account_number').value = pm.account_number || '';
        document.getElementById('edit_notes').value = pm.notes || '';
        document.getElementById('edit_is_default').checked = !!pm.is_default;
        document.getElementById('editModal').classList.add('active');
    }
    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
    }

    // Close on backdrop click
    window.addEventListener('click', function(e) {
        const addM = document.getElementById('addModal');
        const editM = document.getElementById('editModal');
        if (e.target === addM) closeAddModal();
        if (e.target === editM) closeEditModal();
    });
</script>
@endpush
@endsection
