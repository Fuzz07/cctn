<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetCode extends Notification
{
    use Queueable;

    public string $code;
    public int $expiresInMinutes;

    public function __construct(string $code, int $expiresInMinutes)
    {
        $this->code = $code;
        $this->expiresInMinutes = $expiresInMinutes;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your BCTVI password reset code')
            ->greeting('Hello ' . ($notifiable->firstname ?: 'Client') . ',')
            ->line('Use this verification code to reset your BCTVI password:')
            ->line('**' . $this->code . '**')
            ->line("This code expires in {$this->expiresInMinutes} minutes and can only be used once.")
            ->line('If you did not request a password reset, you can ignore this email.');
    }
}
