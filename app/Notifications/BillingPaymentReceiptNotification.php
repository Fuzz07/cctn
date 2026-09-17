<?php

namespace App\Notifications;

use App\Models\Payment;
use App\Models\BillingAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BillingPaymentReceiptNotification extends Notification
{
    use Queueable;

    public Payment $payment;
    public ?BillingAccount $billing;

    public function __construct(Payment $payment, ?BillingAccount $billing = null)
    {
        $this->payment = $payment;
        $this->billing = $billing ?: $payment->billing;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $payment = $this->payment;
        $billing = $this->billing;
        $clientName = trim(($notifiable->firstname ?? '') . ' ' . ($notifiable->lastname ?? ''));
        if (empty($clientName)) {
            $clientName = 'Valued Client';
        }

        $receiptNo = $payment->receipt_no ?: ('RCT-' . str_pad($payment->id, 6, '0', STR_PAD_LEFT));
        $amountPaid = number_format((float) $payment->amount_paid, 2);
        $statementPeriod = $billing?->statement_period ?? date('F Y');
        $accountNumber = $payment->account_number ?: ($billing?->account_number ?? 'N/A');
        $paymentMethod = ucfirst(str_replace('_', ' ', $payment->payment_method ?: 'Cash'));
        $refNumber = $payment->reference_number ?: 'N/A';
        $receivedBy = $payment->received_by ?: 'Authorized Cashier';
        $paymentDate = $payment->payment_date ? date('F d, Y h:i A', strtotime($payment->payment_date)) : date('F d, Y h:i A');

        $mail = (new MailMessage)
            ->subject("Official Payment Receipt — {$receiptNo} - CCTN")
            ->greeting("Hello {$clientName},")
            ->line("Thank you for your payment! This email serves as your official electronic receipt.")
            ->line("**Official Receipt No:** {$receiptNo}")
            ->line("**Account Number:** {$accountNumber}")
            ->line("**Statement Period:** {$statementPeriod}")
            ->line("**Amount Paid:** ₱{$amountPaid}")
            ->line("**Payment Method:** {$paymentMethod}")
            ->line("**Transaction Reference:** {$refNumber}")
            ->line("**Payment Date:** {$paymentDate}")
            ->line("**Received By:** {$receivedBy}");

        if (!empty($payment->notes)) {
            $mail->line("**Notes:** " . $payment->notes);
        }

        return $mail
            ->action('View Your Account & Statements', url('/billing'))
            ->line('Your account has been credited and is in good standing. Thank you for your continued patronage!')
            ->salutation('Best regards,  ' . "\n" . config('app.name', 'CCTN') . ' Billing Dept.');
    }
}
