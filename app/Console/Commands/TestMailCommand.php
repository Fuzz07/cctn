<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestMailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email? : The recipient email address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email to verify Hostinger or local SMTP configuration';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $recipient = $this->argument('email');
        if (empty($recipient)) {
            $recipient = $this->ask('Enter the recipient email address (e.g. your personal Gmail)');
        }

        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            $this->error("Invalid email address: '{$recipient}'");
            return 1;
        }

        $mailer     = config('mail.default');
        $host       = config('mail.mailers.smtp.host');
        $port       = config('mail.mailers.smtp.port');
        $encryption = config('mail.mailers.smtp.encryption');
        $username   = config('mail.mailers.smtp.username');
        $fromAddr   = config('mail.from.address');
        $fromName   = config('mail.from.name');

        $this->info("=========================================");
        $this->info(" CCTN / BCTVI Mail Configuration Test");
        $this->info("=========================================");
        $this->line("Mailer:       <comment>{$mailer}</comment>");
        $this->line("Host:         <comment>{$host}</comment>");
        $this->line("Port:         <comment>{$port}</comment>");
        $this->line("Encryption:   <comment>" . ($encryption ?: 'none') . "</comment>");
        $this->line("Username:     <comment>" . ($username ?: 'none') . "</comment>");
        $this->line("From Address: <comment>" . ($fromAddr ?: 'none') . "</comment>");
        $this->line("From Name:    <comment>{$fromName}</comment>");
        $this->line("Sending to:   <comment>{$recipient}</comment>");
        $this->info("=========================================");

        // Pre-checks for common Hostinger configuration mistakes
        if ($host === 'mailhog') {
            $this->warn("⚠️  MAIL_HOST is set to 'mailhog'. This will NOT send emails to real Gmail addresses!");
            $this->warn("   For Hostinger, set MAIL_HOST=smtp.hostinger.com in your .env");
        }

        if (!empty($username) && !empty($fromAddr) && strtolower(trim($username)) !== strtolower(trim($fromAddr))) {
            $this->warn("⚠️  HOSTINGER NOTICE: MAIL_FROM_ADDRESS ('{$fromAddr}') does not match MAIL_USERNAME ('{$username}').");
            $this->warn("   Hostinger SMTP rejects emails unless MAIL_FROM_ADDRESS exactly matches MAIL_USERNAME.");
        }

        $this->info("\nAttempting to send test email...");

        try {
            Mail::raw("Hello!\n\nThis is a test email sent from CCTN / BCTVI Broadband system.\nYour SMTP mail server is working properly!\n\nSent at: " . now()->toDateTimeString(), function ($message) use ($recipient, $fromAddr, $fromName, $username) {
                $actualFrom = $fromAddr ?: $username;
                if ($actualFrom && $actualFrom !== 'hello@example.com') {
                    $message->from($actualFrom, $fromName ?: config('app.name', 'CCTN'));
                }
                $message->to($recipient)
                        ->subject('✅ CCTN Mail Configuration Test — ' . date('Y-m-d H:i'));
            });

            $this->info("✅ SUCCESS: Test email sent to {$recipient}!");
            $this->info("Please check your inbox (and spam/junk folder).");
            return 0;
        } catch (\Throwable $e) {
            $this->error("❌ FAILED: Could not send email.");
            $this->error("Error message: " . $e->getMessage());
            $this->line("");
            $this->warn("Troubleshooting tips for Hostinger:");
            $this->line("1. Verify credentials in hPanel > Emails > Email Accounts.");
            $this->line("2. For Hostinger SMTP, use:");
            $this->line("   MAIL_MAILER=smtp");
            $this->line("   MAIL_HOST=smtp.hostinger.com");
            $this->line("   MAIL_PORT=465");
            $this->line("   MAIL_ENCRYPTION=ssl");
            $this->line("   MAIL_USERNAME=noreply@yourdomain.com");
            $this->line("   MAIL_FROM_ADDRESS=noreply@yourdomain.com");
            $this->line("3. Run 'php artisan config:clear' on Hostinger to ensure updated .env is loaded.");
            return 1;
        }
    }
}
