<?php

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AppointmentPaymentConfirmedNotification extends Notification
{
    use Queueable;

    public Appointment $appointment;

    public function __construct(Appointment $appointment)
    {
        $this->appointment = $appointment;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $appointment = $this->appointment;
        $clientName = trim(($notifiable->firstname ?? '') . ' ' . ($notifiable->lastname ?? ''));
        if (empty($clientName)) {
            $clientName = 'Valued Client';
        }

        $serviceName = $appointment->service->service_name ?? 'WiFi Service';
        $formattedDate = date('F d, Y', strtotime($appointment->preferred_date));
        $formattedTime = date('g:i A', strtotime($appointment->preferred_time));
        $paymentMethod = $appointment->payment_method ?: 'Digital Payment';
        $refNumber = $appointment->reference_number ?: 'N/A';
        $amount = (float) ($appointment->amount_paid > 0 ? $appointment->amount_paid : ($appointment->amount_due > 0 ? $appointment->amount_due : ($appointment->service->price ?? 0)));
        $formattedAmount = number_format($amount, 2);

        $mail = (new MailMessage)
            ->subject('Payment Confirmed — Appointment #' . str_pad($appointment->id, 5, '0', STR_PAD_LEFT) . ' - CCTN')
            ->greeting("Hello {$clientName},")
            ->line('Great news! Your payment has been successfully verified and confirmed.')
            ->line('Here are your confirmed appointment and payment details:')
            ->line("**Appointment Ref:** #" . str_pad($appointment->id, 5, '0', STR_PAD_LEFT))
            ->line("**Service Plan:** {$serviceName}")
            ->line("**Scheduled Date:** {$formattedDate} at {$formattedTime}")
            ->line("**Payment Method:** {$paymentMethod}")
            ->line("**Transaction Reference:** {$refNumber}")
            ->line("**Amount Verified:** ₱{$formattedAmount}");

        if (!empty($appointment->installation_address)) {
            $mail->line("**Installation Address:** " . $appointment->installation_address);
        }

        if (!empty($appointment->admin_notes)) {
            $mail->line("**Notes from Staff:** " . $appointment->admin_notes);
        }

        return $mail
            ->line('Our technical deployment team has scheduled your service and will arrive on the agreed slot.')
            ->line('Thank you for choosing CCTN!')
            ->salutation('Best regards,  ' . "\n" . config('app.name', 'CCTN') . ' Team');
    }
}
