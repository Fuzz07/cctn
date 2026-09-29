<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ApiRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_registration_succeeds_with_valid_data()
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'firstname'            => 'John',
            'lastname'             => 'Doe',
            'email'                => 'johndoe@example.com',
            'username'             => 'johndoe',
            'password'             => 'password123',
            'password_confirmation'=> 'password123',
            'contact_no'           => '09123456789',
            'address_barangay'     => 'San Vicente',
            'address_municipality' => 'Bantayan',
            'address_province'     => 'Cebu',
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'success' => true,
                 ]);

        $this->assertDatabaseHas('clients', [
            'email'    => 'johndoe@example.com',
            'username' => 'johndoe',
        ]);
    }

    public function test_api_registration_fails_on_validation_errors()
    {
        // 1. Test missing required fields
        $response = $this->postJson('/api/v1/auth/register', [
            'firstname' => 'John',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors([
                     'lastname', 'email', 'username', 'password', 'contact_no', 
                     'address_barangay', 'address_municipality', 'address_province'
                 ]);

        // 2. Test duplicate email/username
        \App\Models\Client::create([
            'firstname'            => 'Existing',
            'lastname'             => 'User',
            'email'                => 'johndoe@example.com',
            'username'             => 'johndoe',
            'password'             => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);

        $response2 = $this->postJson('/api/v1/auth/register', [
            'firstname'            => 'John',
            'lastname'             => 'Doe',
            'email'                => 'johndoe@example.com',
            'username'             => 'johndoe',
            'password'             => 'password123',
            'password_confirmation'=> 'password123',
            'contact_no'           => '09123456789',
            'address_barangay'     => 'San Vicente',
            'address_municipality' => 'Bantayan',
            'address_province'     => 'Cebu',
        ]);

        $response2->assertStatus(422)
                  ->assertJsonValidationErrors(['email', 'username']);
    }

    public function test_user_can_register_via_api_and_login_via_web_and_vice_versa()
    {
        // Disable CSRF verification for web route testing
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);

        // ─── Scenario A: Register via API, Login via Web ───
        $this->postJson('/api/v1/auth/register', [
            'firstname'            => 'Api',
            'lastname'             => 'User',
            'email'                => 'apiuser@example.com',
            'username'             => 'apiuser',
            'password'             => 'password123',
            'password_confirmation'=> 'password123',
            'contact_no'           => '09123456789',
            'address_barangay'     => 'San Vicente',
            'address_municipality' => 'Bantayan',
            'address_province'     => 'Cebu',
        ])->assertStatus(201);

        $responseWebLogin = $this->post('/login', [
            'login_input'  => 'apiuser',
            'password'     => 'password123',
            'agree_terms'  => '1',
        ]);
        $responseWebLogin->assertRedirect(route('client.dashboard'));

        // ─── Scenario B: Register via Web, Login via API ───
        $this->post('/register', [
            'firstname'            => 'Web',
            'lastname'             => 'User',
            'email'                => 'webuser@example.com',
            'username'             => 'webuser',
            'password'             => 'password123',
            'password_confirmation'=> 'password123',
            'contact_no'           => '09123456789',
            'address_barangay'     => 'San Vicente',
            'address_municipality' => 'Bantayan',
            'address_province'     => 'Cebu',
            'proof_of_billing'     => UploadedFile::fake()->image('proof.jpg', 600, 800),
            'agree_terms'          => '1',
        ])->assertRedirect(route('client.dashboard'));

        $responseApiLogin = $this->postJson('/api/v1/auth/login', [
            'login_input' => 'webuser',
            'password'    => 'password123',
        ]);
        $responseApiLogin->assertStatus(200)
                         ->assertJsonStructure([
                             'success', 'token', 'client'
                         ]);
    }
}
