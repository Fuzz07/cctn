<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillingAccount;
use App\Models\Payment;
use App\Models\Client;
use App\Models\Notification;
use App\Notifications\BillingPaymentReceiptNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class BillingController extends Controller
{
    public function index()
    {
        $billings = BillingAccount::with('client')->orderBy('id', 'desc')->get();
        $clients  = Client::active()->orderBy('firstname')->get();

        return view('admin.billing.index', compact('billings', 'clients'));
    }

    public function createBilling(Request $request)
    {
        $request->validate([
            'client_id'        => 'required|exists:clients,id',
            'account_number'   => ['required', 'string', 'regex:/^\d+$/'],
            'statement_period' => 'required|string',
            'amount_due'       => 'required|numeric|min:0|max:99999.99',
            'penalty_amount'   => 'nullable|numeric|min:0|max:99999.99',
            'due_date'         => 'required|date|after_or_equal:2026-01-01',
        ], [
            'amount_due.max' => 'The amount due may contain at most five digits before the decimal point.',
            'penalty_amount.max' => 'The penalty amount may contain at most five digits before the decimal point.',
        ]);

        $totalDue = $request->amount_due + ($request->penalty_amount ?? 0);

        if ($totalDue > 99999.99) {
            return back()->withErrors([
                'amount_due' => 'The total payment amount cannot exceed ₱99,999.99.',
            ])->withInput();
        }

        BillingAccount::create([
            'client_id'        => $request->client_id,
            'account_number'   => $request->account_number,
            'statement_period' => $request->statement_period,
            'amount_due'       => $request->amount_due,
            'penalty_amount'   => $request->penalty_amount ?? 0,
            'total_amount_due' => $totalDue,
            'due_date'         => $request->due_date,
            'notes'            => $request->notes ?? '',
            'status'           => 'unpaid',
        ]);

        return redirect()->route('admin.billing')->with('success_message', 'Billing statement created successfully.');
    }

    public function recordPayment(Request $request)
    {
        $request->validate([
            'billing_id'  => 'required|exists:billing_accounts,id',
            'amount_paid' => 'required|numeric|min:0.01|max:99999.99',
        ], [
            'amount_paid.max' => 'The payment amount may contain at most five digits before the decimal point.',
        ]);

        $billing = BillingAccount::findOrFail($request->billing_id);
        $admin = Auth::guard('admin')->user();

        // Guard: amount paid must cover the full balance to mark as paid
        if ((float) $request->amount_paid < (float) $billing->total_amount_due) {
            return back()->withErrors([
                'amount_paid' => 'Partial payments are not supported. The amount paid (₱' . number_format((float) $request->amount_paid, 2) . ') is less than the total amount due (₱' . number_format((float) $billing->total_amount_due, 2) . '). Please adjust the amount or update the billing record first.',
            ])->withInput();
        }

        $receiptNo = 'RCP-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

        $payment = Payment::create([
            'billing_id'       => $billing->id,
            'client_id'        => $billing->client_id,
            'account_number'   => $billing->account_number,
            'amount_paid'      => $request->amount_paid,
            'payment_method'   => $request->payment_method ?? 'cash',
            'reference_number' => $request->reference_number ?? '',
            'received_by'      => $request->received_by ?? $admin->fullname,
            'notes'            => $request->pay_notes ?? '',
            'payment_date'     => now(),
            'receipt_no'       => $receiptNo,
        ]);

        $billing->update(['status' => 'paid', 'paid_at' => now()]);

        // Notify the client and the admin panel that the payment receipt has been issued
        $client = Client::find($billing->client_id);
        $clientName = $client ? trim("{$client->firstname} {$client->lastname}") : $billing->account_number;
        $amountPaid = number_format((float) $request->amount_paid, 2);

        Notification::create([
            'for_admin' => false,
            'client_id' => $billing->client_id,
            'title'     => 'Payment Received — Receipt ' . $receiptNo,
            'message'   => "Your payment of ₱{$amountPaid} for {$billing->statement_period} has been received and recorded. Official Receipt No: {$receiptNo}. Thank you for keeping your account updated!",
            'link'      => 'billing',
        ]);

        Notification::create([
            'for_admin' => true,
            'title'     => 'Payment Recorded — Receipt ' . $receiptNo,
            'message'   => "₱{$amountPaid} received from {$clientName} (Acct: {$billing->account_number}) for {$billing->statement_period}. Receipt No: {$receiptNo}.",
            'link'      => 'admin/billing',
        ]);

        // Send email receipt to client's Gmail
        if ($client && !empty($client->email)) {
            try {
                $client->notify(new BillingPaymentReceiptNotification($payment, $billing));
            } catch (\Throwable $e) {
                Log::warning("Could not send billing payment receipt email to {$client->email}: " . $e->getMessage());
            }
        }

        return redirect()->route('admin.billing')->with('success_message', "Payment recorded. Receipt: {$receiptNo}");
    }
}
