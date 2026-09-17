<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\File;

class MailDiagnosticController extends Controller
{
    public function index()
    {
        $config = [
            'mailer'     => config('mail.default'),
            'host'       => config('mail.mailers.smtp.host'),
            'port'       => config('mail.mailers.smtp.port'),
            'encryption' => config('mail.mailers.smtp.encryption'),
            'username'   => config('mail.mailers.smtp.username'),
            'has_password' => !empty(config('mail.mailers.smtp.password')),
            'from_address' => config('mail.from.address'),
            'from_name'    => config('mail.from.name'),
            'queue'        => config('queue.default'),
        ];

        // Gather recent mail-related log lines
        $recentLogs = [];
        $logPath = storage_path('logs/laravel.log');
        if (File::exists($logPath)) {
            $content = File::get($logPath);
            $lines = explode("\n", $content);
            $recent = array_slice($lines, -150);
            foreach ($recent as $line) {
                if (stripos($line, 'mail') !== false || stripos($line, 'smtp') !== false || stripos($line, 'payment confirmation') !== false) {
                    $recentLogs[] = trim($line);
                }
            }
        }

        return view('admin.mail.index', compact('config', 'recentLogs'));
    }

    public function sendTest(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        $recipient = $request->test_email;
        $fromAddr  = config('mail.from.address');
        $fromName  = config('mail.from.name');
        $username  = config('mail.mailers.smtp.username');

        try {
            Mail::raw("Hello!\n\nThis is an automated test email sent from the CCTN / BCTVI Broadband Admin Panel to verify that SMTP email delivery is operating correctly.\n\nTime Sent: " . now()->format('Y-m-d h:i:s A') . "\nHost: " . config('mail.mailers.smtp.host') . "\nPort: " . config('mail.mailers.smtp.port') . "\n\nIf you received this email, your Hostinger SMTP setup is working!", function ($message) use ($recipient, $fromAddr, $fromName, $username) {
                $actualFrom = $fromAddr ?: $username;
                if ($actualFrom && $actualFrom !== 'hello@example.com') {
                    $message->from($actualFrom, $fromName ?: config('app.name', 'CCTN'));
                }
                $message->to($recipient)
                        ->subject('✅ CCTN Email Delivery Test — ' . date('M d, Y h:i A'));
            });

            return back()->with('success_message', "✅ Test email successfully sent to {$recipient}! Please check the recipient's inbox and spam folder.");
        } catch (\Throwable $e) {
            $errMsg = $e->getMessage();
            \Illuminate\Support\Facades\Log::error("Manual test mail failed: " . $errMsg, ['exception' => $e]);

            $hint = "";
            if (stripos($errMsg, '550') !== false || stripos($errMsg, 'sender') !== false) {
                $hint = " Tip: On Hostinger, MAIL_FROM_ADDRESS must be set to the EXACT same email address as MAIL_USERNAME.";
            } elseif (stripos($errMsg, 'connection') !== false || stripos($errMsg, 'refused') !== false || stripos($errMsg, 'timed out') !== false) {
                $hint = " Tip: For Hostinger SMTP, use MAIL_PORT=465 with MAIL_ENCRYPTION=ssl (or port 587 with tls). Also ensure MAIL_HOST=smtp.hostinger.com.";
            } elseif (stripos($errMsg, 'auth') !== false || stripos($errMsg, 'credentials') !== false) {
                $hint = " Tip: Use the email password created in Hostinger hPanel > Emails > Email Accounts, not your general Hostinger account login password.";
            }

            return back()->with('mail_error', "❌ Email Delivery Failed: {$errMsg}.{$hint}")->withInput();
        }
    }
}
