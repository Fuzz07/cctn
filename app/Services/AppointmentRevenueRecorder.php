<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\BillingAccount;
use App\Models\Payment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AppointmentRevenueRecorder
{
    /**
     * Record an approved online booking in the billing and sales ledgers.
     * The appointment link prevents an approval from being counted twice.
     */
    public function record(Appointment $appointment): ?Payment
    {
        if ($appointment->status !== 'approved' || $appointment->is_walkin) {
            return null;
        }

        return DB::transaction(function () use ($appointment) {
            $appointment->loadMissing(['client', 'service']);

            $existing = Payment::where('appointment_id', $appointment->id)->first();
            if ($existing) {
                return $existing;
            }

            $amount = (float) $appointment->amount_paid > 0
                ? (float) $appointment->amount_paid
                : (float) ($appointment->service?->price ?? 0);

            if ($amount <= 0) {
                return null;
            }

            $paymentDate = $appointment->payment_date ?? now();
            $accountNumber = $appointment->client?->account_number
                ?: 'CLIENT-' . str_pad((string) $appointment->client_id, 6, '0', STR_PAD_LEFT);
            $receiptNo = 'APT-' . $paymentDate->format('Ymd') . '-' . str_pad((string) $appointment->id, 5, '0', STR_PAD_LEFT);
            $admin = Auth::guard('admin')->user();

            // payments.billing_id is required by the existing ledger schema.
            $billing = BillingAccount::create([
                'client_id'        => $appointment->client_id,
                'account_number'   => $accountNumber,
                'statement_period' => 'Booking #' . $appointment->id . ' - ' . $paymentDate->format('F Y'),
                'amount_due'       => $amount,
                'penalty_amount'   => 0,
                'total_amount_due' => $amount,
                'status'           => 'paid',
                'due_date'         => $paymentDate->toDateString(),
                'notes'            => 'Automatically created when booking #' . $appointment->id . ' was approved.',
                'paid_at'          => $paymentDate,
            ]);

            return Payment::create([
                'billing_id'       => $billing->id,
                'appointment_id'   => $appointment->id,
                'client_id'        => $appointment->client_id,
                'account_number'   => $accountNumber,
                'amount_paid'      => $amount,
                'payment_method'   => $appointment->payment_method ?: 'Cash',
                'reference_number' => $appointment->reference_number,
                'received_by'      => $admin?->fullname ?? $admin?->username ?? 'Admin Staff',
                'notes'            => 'Booking #' . $appointment->id . ' - ' . ($appointment->service?->service_name ?? 'Service'),
                'payment_date'     => $paymentDate,
                'receipt_no'       => $receiptNo,
            ]);
        });
    }
}
