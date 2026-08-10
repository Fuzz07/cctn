<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use App\Models\TimeSlot;
use App\Models\BillingAccount;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WalkInController extends Controller
{
    public function create()
    {
        $services = Service::where('status', 'Active')->orderBy('price', 'asc')->get();
        $timeSlots = TimeSlot::where('is_available', true)->get();

        return view('admin.walkin.create', compact('services', 'timeSlots'));
    }

    public function store(Request $request)
    {
        $request->validate([
            // Step 1: Client Info
            'full_name'            => 'required|string|max:150',
            'contact_no'           => 'required|string|max:30',
            'email'                => 'required|email|max:100',
            'complete_address'     => 'required|string|max:255',
            'address_barangay'     => 'required|string|max:100',
            'installation_address' => 'required|string|max:255',
            'valid_id_type'        => 'required|string|max:50',
            'valid_id_number'      => 'required|string|max:50',

            // Step 2: WiFi Plan
            'service_id'           => 'required|exists:services,id',

            // Step 3: Installation Schedule
            'preferred_date'       => 'required|date|after_or_equal:today',
            'preferred_time'       => 'required|string',
            'installation_notes'   => 'nullable|string',

            // Step 4: Payment
            'payment_method'       => 'required|in:Cash,GCash,Bank Transfer,Pay Later',
            'cash_received'        => 'nullable|numeric|min:0',
            'gcash_ref'            => 'nullable|string|max:100',
            'gcash_amount'         => 'nullable|numeric|min:0',
            'gcash_date'           => 'nullable|date',
            'bank_name'            => 'nullable|string|max:100',
            'bank_ref'             => 'nullable|string|max:100',
            'bank_amount'          => 'nullable|numeric|min:0',
            'bank_date'            => 'nullable|date',
            'pay_later_due_date'   => 'nullable|date',
            'payment_proof'        => 'nullable|image|mimes:jpeg,png,jpg|max:4096',
        ]);

        // Check slot conflict
        if (Appointment::hasConflict($request->preferred_date, $request->preferred_time)) {
            return back()->withErrors(['preferred_time' => 'The selected date and time slot is already fully booked. Please select another slot.'])->withInput();
        }

        // 1. Find or create Client
        $nameParts = explode(' ', trim($request->full_name), 2);
        $firstname = $nameParts[0];
        $lastname  = $nameParts[1] ?? 'Walk-In';

        $client = Client::where('email', $request->email)->first();
        if (!$client) {
            $acctNo = Client::nextAccountNumber();
            $client = Client::create([
                'account_number'     => $acctNo,
                'firstname'          => $firstname,
                'lastname'           => $lastname,
                'email'              => $request->email,
                'contact_no'         => $request->contact_no,
                'address_barangay'   => $request->address_barangay,
                'address_municipality' => 'Bantayan',
                'address_province'   => 'Cebu',
                'username'           => strtolower(str_replace(' ', '', $firstname)) . rand(100, 999),
                'password'           => bcrypt(Str::random(10)),
            ]);
        }

        $service = Service::findOrFail($request->service_id);
        $monthlyFee = (float) $service->price;
        $installationFee = (float) ($service->installation_fee ?? 1000.00);
        $totalAmountDue = $installationFee + $monthlyFee;

        // Payment status & calculations
        $paymentStatus = 'Pending Payment';
        $amountPaid = 0.00;
        $changeAmount = 0.00;
        $refNo = null;
        $bankName = null;
        $dueDate = null;
        $paymentDate = null;
        $paymentProofPath = null;

        if ($request->hasFile('payment_proof')) {
            $paymentProofPath = $request->file('payment_proof')->store('payment_proofs', 'public');
        }

        if ($request->payment_method === 'Cash') {
            $amountReceived = (float) ($request->cash_received ?? $totalAmountDue);
            $amountPaid = $amountReceived;
            if ($amountReceived >= $totalAmountDue) {
                $paymentStatus = 'Payment Confirmed';
                $changeAmount = $amountReceived - $totalAmountDue;
            }
            $paymentDate = now();
            $refNo = 'CASH-' . strtoupper(Str::random(8));
        } elseif ($request->payment_method === 'GCash') {
            $amountPaid = (float) ($request->gcash_amount ?? $totalAmountDue);
            if ($amountPaid >= $totalAmountDue) {
                $paymentStatus = 'Payment Confirmed';
            }
            $refNo = $request->gcash_ref;
            $paymentDate = $request->gcash_date ? \Carbon\Carbon::parse($request->gcash_date) : now();
        } elseif ($request->payment_method === 'Bank Transfer') {
            $amountPaid = (float) ($request->bank_amount ?? $totalAmountDue);
            if ($amountPaid >= $totalAmountDue) {
                $paymentStatus = 'Payment Confirmed';
            }
            $bankName = $request->bank_name;
            $refNo = $request->bank_ref;
            $paymentDate = $request->bank_date ? \Carbon\Carbon::parse($request->bank_date) : now();
        } elseif ($request->payment_method === 'Pay Later') {
            $paymentStatus = 'Pending Payment';
            $amountPaid = 0.00;
            $dueDate = $request->pay_later_due_date ? \Carbon\Carbon::parse($request->pay_later_due_date) : now()->addDays(7);
        }

        // Generate unique CBTVI Reference Number
        $bookingRef = 'CBTVI-BK-' . date('Y') . '-' . str_pad(Appointment::count() + 1, 4, '0', STR_PAD_LEFT);

        $appointment = Appointment::create([
            'booking_ref'          => $bookingRef,
            'is_walkin'            => true,
            'client_id'            => $client->id,
            'service_id'           => $service->id,
            'preferred_date'       => $request->preferred_date,
            'preferred_time'       => $request->preferred_time,
            'installation_address' => $request->installation_address,
            'message'              => $request->installation_notes,
            'status'               => 'approved',
            'installation_status'  => 'Scheduled',
            'payment_status'       => $paymentStatus,
            'payment_method'       => $request->payment_method,
            'amount_paid'          => $amountPaid,
            'amount_due'           => $totalAmountDue,
            'change_amount'        => $changeAmount,
            'bank_name'            => $bankName,
            'reference_number'     => $refNo,
            'due_date'             => $dueDate,
            'payment_date'         => $paymentDate,
            'valid_id_type'        => $request->valid_id_type,
            'valid_id_number'      => $request->valid_id_number,
            'payment_proof'        => $paymentProofPath,
            'admin_notes'          => 'Registered via Walk-In Portal by Staff.',
        ]);

        // Create billing account record for ledger; backfill the client's account number if missing
        if (!$client->account_number) {
            $client->update(['account_number' => Client::nextAccountNumber()]);
        }
        $billing = BillingAccount::create([
            'client_id'         => $client->id,
            'account_number'    => $client->account_number,
            'statement_period'  => date('F Y'),
            'amount_due'        => $totalAmountDue,
            'penalty_amount'    => 0.00,
            'total_amount_due'  => $totalAmountDue,
            'status'            => $paymentStatus === 'Payment Confirmed' ? 'paid' : 'unpaid',
            'due_date'          => $dueDate ?? now()->addDays(7),
            'notes'             => "Initial Walk-In WiFi Installation Booking (#{$bookingRef})",
            'paid_at'           => $paymentStatus === 'Payment Confirmed' ? now() : null,
        ]);

        if ($amountPaid > 0) {
            Payment::create([
                'billing_id'       => $billing->id,
                'client_id'        => $client->id,
                'account_number'   => $billing->account_number,
                'amount_paid'      => $amountPaid,
                'payment_method'   => strtolower(str_replace(' ', '_', $request->payment_method)),
                'reference_number' => $refNo ?? 'WALKIN-' . strtoupper(Str::random(6)),
                'received_by'      => auth('admin')->user()->username ?? 'Staff',
                'notes'            => "Walk-in WiFi Installation Payment for {$service->service_name}",
                'payment_date'     => $paymentDate ?? now(),
                'receipt_no'       => 'RCT-' . date('Ymd') . '-' . rand(1000, 9999),
            ]);
        }

        return redirect()->route('admin.walkin.create', ['confirmed_id' => $appointment->id])
            ->with('success_message', "Walk-In Client Booking successful! Reference: {$bookingRef}");
    }

    public function receipt($id)
    {
        $appointment = Appointment::with(['client', 'service'])->findOrFail($id);
        return view('admin.walkin.receipt', compact('appointment'));
    }
}
