<?php

namespace Tests\Feature;

use App\Models\Client;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ApiRecaptchaTest extends TestCase
{
    use RefreshDatabase;

    private const SITEVERIFY = 'https://www.google.com/recaptcha/api/siteverify';

    protected function setUp(): void
    {
        parent::setUp();

        // Not Google's published test secret: that one passes any token
        // without asking Google, which would hide what these tests check.
        config([
            'services.recaptcha.enabled'    => true,
            'services.recaptcha.site_key'   => 'site-key-for-tests',
            'services.recaptcha.secret_key' => 'secret-key-for-tests',
        ]);

        Client::create([
            'firstname' => 'Juan',
            'lastname'  => 'Dela Cruz',
            'email'     => 'juan@example.com',
            'username'  => 'juan',
            'password'  => Hash::make('password123'),
        ]);
    }

    public function test_login_without_a_token_is_refused_with_a_hint_to_update_the_app()
    {
        Http::fake();

        $response = $this->postJson('/api/v1/auth/login', [
            'login_input' => 'juan',
            'password'    => 'password123',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors(['recaptcha_token']);
        $this->assertStringContainsString(
            'update the BCTVI app',
            $response->json('errors.recaptcha_token.0'),
        );
        Http::assertNothingSent();
    }

    public function test_login_with_a_token_google_accepts_signs_in()
    {
        Http::fake([self::SITEVERIFY => Http::response(['success' => true])]);

        $this->postJson('/api/v1/auth/login', [
            'login_input'     => 'juan',
            'password'        => 'password123',
            'recaptcha_token' => 'token-from-widget',
        ])->assertOk()->assertJsonStructure(['token', 'client']);

        Http::assertSent(fn (HttpRequest $request) => $request->url() === self::SITEVERIFY
            && $request['secret'] === 'secret-key-for-tests'
            && $request['response'] === 'token-from-widget');
    }

    public function test_login_with_a_token_google_rejects_is_refused()
    {
        Http::fake([self::SITEVERIFY => Http::response([
            'success'     => false,
            'error-codes' => ['timeout-or-duplicate'],
        ])]);

        $this->postJson('/api/v1/auth/login', [
            'login_input'     => 'juan',
            'password'        => 'password123',
            'recaptcha_token' => 'spent-token',
        ])->assertStatus(422)->assertJsonValidationErrors(['recaptcha_token']);
    }

    public function test_registration_without_a_token_is_refused()
    {
        Http::fake();

        $this->postJson('/api/v1/auth/register', $this->registration())
            ->assertStatus(422)
            ->assertJsonValidationErrors(['recaptcha_token']);

        $this->assertDatabaseMissing('clients', ['email' => 'maria@example.com']);
    }

    public function test_registration_with_a_token_google_accepts_creates_the_account()
    {
        Http::fake([self::SITEVERIFY => Http::response(['success' => true])]);

        $this->postJson('/api/v1/auth/register', $this->registration() + [
            'recaptcha_token' => 'token-from-widget',
        ])->assertStatus(201);

        $this->assertDatabaseHas('clients', ['email' => 'maria@example.com']);
    }

    public function test_no_token_is_needed_while_recaptcha_is_switched_off()
    {
        config(['services.recaptcha.enabled' => false]);
        Http::fake();

        $this->postJson('/api/v1/auth/login', [
            'login_input' => 'juan',
            'password'    => 'password123',
        ])->assertOk();

        Http::assertNothingSent();
    }

    public function test_website_login_cannot_skip_recaptcha_by_leaving_the_field_out()
    {
        $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class);
        Http::fake();

        $this->post('/login', [
            'login_input' => 'juan',
            'password'    => 'password123',
            'agree_terms' => '1',
        ])->assertSessionHasErrors('g-recaptcha-response');

        $this->assertGuest('client');
    }

    public function test_app_page_shows_the_widget_with_the_site_key_and_theme()
    {
        $this->get('/mobile/recaptcha?theme=dark')
            ->assertOk()
            ->assertSee('data-sitekey="site-key-for-tests"', false)
            ->assertSee('data-theme="dark"', false)
            ->assertSee('data-callback="onToken"', false);
    }

    public function test_app_page_tells_the_app_when_recaptcha_is_switched_off()
    {
        config(['services.recaptcha.enabled' => false]);

        $this->get('/mobile/recaptcha')
            ->assertOk()
            ->assertSee("tell('onNotRequired')", false)
            ->assertDontSee('class="g-recaptcha"', false);
    }

    private function registration(): array
    {
        return [
            'firstname'             => 'Maria',
            'lastname'              => 'Santos',
            'email'                 => 'maria@example.com',
            'username'              => 'maria',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
            'contact_no'            => '09123456789',
            'address_barangay'      => 'San Vicente',
            'address_municipality'  => 'Bantayan',
            'address_province'      => 'Cebu',
        ];
    }
}
