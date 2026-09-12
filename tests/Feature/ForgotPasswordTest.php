<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Notifications\PasswordResetCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_request_a_code_and_reset_their_password(): void
    {
        Notification::fake();

        $client = Client::create([
            'firstname' => 'Test',
            'lastname' => 'Client',
            'email' => 'client@example.com',
            'username' => 'testclient',
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->post(route('forgot-password.submit'), [
            'email' => $client->email,
        ]);

        $response->assertRedirect(route('password.reset', ['email' => $client->email]));
        $response->assertSessionHas('success_message');

        $code = null;
        Notification::assertSentTo(
            $client,
            PasswordResetCode::class,
            function (PasswordResetCode $notification) use (&$code) {
                $code = $notification->code;
                return true;
            }
        );

        $this->assertMatchesRegularExpression('/^\d{6}$/', $code);
        $this->assertDatabaseMissing('password_resets', ['token' => $code]);

        $this->get(route('password.reset', [
            'email' => $client->email,
        ]))->assertOk()->assertSee('Verification Code');

        $resetResponse = $this->post(route('password.update'), [
            'email' => $client->email,
            'code' => $code,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $resetResponse->assertRedirect(route('login'));
        $resetResponse->assertSessionHas('success_message');
        $this->get(route('login'))->assertSee('Your password has been reset!');
        $this->assertTrue(Hash::check('new-password', $client->fresh()->password));
        $this->assertDatabaseMissing('password_resets', ['email' => $client->email]);

        $this->post(route('password.update'), [
            'email' => $client->email,
            'code' => $code,
            'password' => 'another-password',
            'password_confirmation' => 'another-password',
        ])->assertSessionHasErrors('code');
    }

    public function test_unknown_email_does_not_claim_that_a_link_was_sent(): void
    {
        Notification::fake();

        $this->post(route('forgot-password.submit'), [
            'email' => 'missing@example.com',
        ])->assertSessionHasErrors('email');

        Notification::assertNothingSent();
    }

    public function test_verification_code_expires_after_fifteen_minutes(): void
    {
        Notification::fake();

        $client = Client::create([
            'firstname' => 'Expired',
            'lastname' => 'Code',
            'email' => 'expired@example.com',
            'username' => 'expiredcode',
            'password' => Hash::make('old-password'),
        ]);

        $this->post(route('forgot-password.submit'), ['email' => $client->email]);

        $code = null;
        Notification::assertSentTo(
            $client,
            PasswordResetCode::class,
            function (PasswordResetCode $notification) use (&$code) {
                $code = $notification->code;
                return true;
            }
        );

        DB::table('password_resets')
            ->where('email', $client->email)
            ->update(['created_at' => now()->subMinutes(16)]);

        $this->post(route('password.update'), [
            'email' => $client->email,
            'code' => $code,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertSessionHasErrors('code');

        $this->assertTrue(Hash::check('old-password', $client->fresh()->password));
        $this->assertDatabaseMissing('password_resets', ['email' => $client->email]);
    }

    public function test_another_code_cannot_be_requested_within_one_minute(): void
    {
        Notification::fake();

        $client = Client::create([
            'firstname' => 'Rate',
            'lastname' => 'Limited',
            'email' => 'limited@example.com',
            'username' => 'ratelimited',
            'password' => Hash::make('old-password'),
        ]);

        $this->post(route('forgot-password.submit'), ['email' => $client->email])
            ->assertRedirect(route('password.reset', ['email' => $client->email]));

        $this->post(route('forgot-password.submit'), ['email' => $client->email])
            ->assertSessionHasErrors('email');

        Notification::assertSentToTimes($client, PasswordResetCode::class, 1);
    }
}
