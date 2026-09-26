<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiGoogleSignInTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_sign_in_checks_the_id_token_and_signs_in()
    {
        config([
            'services.google.client_id'         => 'app-client-id',
            'services.google.android_client_id' => null,
        ]);
        Http::fake(['oauth2.googleapis.com/*' => Http::response([
            'aud'            => 'app-client-id',
            'email'          => 'ana@example.com',
            'email_verified' => 'true',
            'given_name'     => 'Ana',
            'family_name'    => 'Santos',
        ])]);

        $this->postJson('/api/v1/auth/google', ['id_token' => 'google-id-token'])
            ->assertOk()
            ->assertJsonStructure(['token', 'client']);

        $this->assertDatabaseHas('clients', ['email' => 'ana@example.com']);
    }
}
