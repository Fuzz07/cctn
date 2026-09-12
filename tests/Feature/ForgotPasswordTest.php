<?php

namespace Tests\Feature;

use App\Models\Client;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ForgotPasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_request_a_link_and_reset_their_password(): void
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

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success_message');
        $this->get(route('login'))->assertSee('We have emailed your password reset link!');

        $token = null;
        Notification::assertSentTo(
            $client,
            ResetPassword::class,
            function (ResetPassword $notification) use (&$token) {
                $token = $notification->token;
                return true;
            }
        );

        $this->get(route('password.reset', [
            'token' => $token,
            'email' => $client->email,
        ]))->assertOk()->assertSee('Create a New Password');

        $resetResponse = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $client->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $resetResponse->assertRedirect(route('login'));
        $resetResponse->assertSessionHas('success_message');
        $this->get(route('login'))->assertSee('Your password has been reset!');
        $this->assertTrue(Hash::check('new-password', $client->fresh()->password));

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $client->email,
            'password' => 'another-password',
            'password_confirmation' => 'another-password',
        ])->assertSessionHasErrors('email');
    }

    public function test_unknown_email_does_not_claim_that_a_link_was_sent(): void
    {
        Notification::fake();

        $this->post(route('forgot-password.submit'), [
            'email' => 'missing@example.com',
        ])->assertSessionHasErrors('email');

        Notification::assertNothingSent();
    }
}
