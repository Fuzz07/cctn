<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Recaptcha implements Rule
{
    protected string $message = 'Please complete the reCAPTCHA verification to proceed.';

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed   $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        // If reCAPTCHA is explicitly disabled, bypass verification
        if (!config('services.recaptcha.enabled', true)) {
            return true;
        }

        $secret = config('services.recaptcha.secret_key');

        // If no secret key configured, bypass to avoid locking out users in unconfigured environments
        if (empty($secret)) {
            return true;
        }

        // Must have received a token from the frontend
        if (empty($value)) {
            $this->message = 'Please check the "I\'m not a robot" reCAPTCHA box.';
            return false;
        }

        // Google official test secret key always passes
        if ($secret === '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe') {
            return true;
        }

        try {
            $response = Http::asForm()->timeout(5)->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret'   => $secret,
                'response' => $value,
                'remoteip' => request()->ip(),
            ]);

            if ($response->successful() && $response->json('success') === true) {
                return true;
            }

            Log::warning('reCAPTCHA verification failed.', [
                'error_codes' => $response->json('error-codes', []),
                'ip'          => request()->ip(),
            ]);

            $this->message = 'reCAPTCHA verification failed. Please try again.';
            return false;
        } catch (\Throwable $e) {
            Log::error('reCAPTCHA verification exception: ' . $e->getMessage());

            // If in local debug mode and Google is unreachable, allow bypass so development isn't blocked
            if (config('app.debug')) {
                return true;
            }

            $this->message = 'Unable to verify reCAPTCHA at this time. Please try again.';
            return false;
        }
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }
}
