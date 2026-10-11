<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Appointment;
use App\Models\Client;
use App\Models\Payment;
use App\Models\Promo;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class PromoBookingTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private Service $service;
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        Storage::fake('public');

        $this->client = Client::create([
            'firstname' => 'Promo',
            'lastname' => 'Customer',
            'email' => 'promo-customer@example.com',
            'username' => 'promocustomer',
            'password' => bcrypt('password'),
        ]);
        $this->service = Service::create([
            'service_name' => 'Promo Fiber Plan',
            'price' => 1000,
            'status' => 'Active',
        ]);
        $this->admin = Admin::create([
            'fullname' => 'Promo Admin',
            'username' => 'promo_admin',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
        ]);
    }

    private function promo(array $overrides = []): Promo
    {
        return Promo::create(array_merge([
            'title' => 'Christmas Promo',
            'description' => 'Save on installation this season.',
            'discount_percent' => 20,
            'start_date' => today()->toDateString(),
            'end_date' => today()->addDays(10)->toDateString(),
        ], $overrides));
    }

    public function test_active_promos_display_on_the_public_homepage_and_expired_promos_do_not(): void
    {
        $this->promo();
        $this->promo([
            'title' => 'Expired Offer',
            'start_date' => today()->subDays(5)->toDateString(),
            'end_date' => today()->subDay()->toDateString(),
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Active Promos')
            ->assertSee('Christmas Promo')
            ->assertDontSee('Expired Offer');

        Sanctum::actingAs($this->client);
        $this->getJson('/api/v1/services')
            ->assertOk()
            ->assertJsonFragment([
                'service_name' => 'Promo Fiber Plan',
                'promo_title' => 'Christmas Promo',
                'promo_discount_amount' => 200,
                'amount_due' => 800,
            ]);
    }

    public function test_admin_can_create_a_promo_with_percentage_and_active_dates(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.promos.store'), [
                'title' => 'Branch Anniversary',
                'description' => 'Anniversary savings.',
                'discount_percent' => 15,
                'start_date' => today()->toDateString(),
                'end_date' => today()->addDays(4)->toDateString(),
            ])
            ->assertRedirect(route('admin.promos'));

        $this->assertDatabaseHas('promos', [
            'title' => 'Branch Anniversary',
            'discount_percent' => 15,
        ]);
    }

    public function test_booking_snapshots_best_active_promo_and_approved_payment_uses_discounted_amount(): void
    {
        $this->promo();
        $this->promo([
            'title' => 'Anniversary Promo',
            'discount_percent' => 25,
        ]);
        Sanctum::actingAs($this->client);

        $response = $this->postJson('/api/v1/appointments', [
            'service_id' => $this->service->id,
            'preferred_date' => today()->addDay()->format('Y-m-d'),
            'preferred_time' => '09:00',
            'payment_method' => 'GCash',
            'reference_number' => '1234567890123',
            'payment_proof' => UploadedFile::fake()->image('gcash-receipt.jpg', 800, 1200)->size(500),
        ]);

        $response->assertCreated()
            ->assertJsonPath('appointment.promo.title', 'Anniversary Promo')
            ->assertJsonPath('appointment.promo.discount_amount', 250)
            ->assertJsonPath('appointment.promo.amount_due', 750);

        $appointment = Appointment::firstOrFail();
        $this->assertSame(25.0, (float) $appointment->promo_discount_percent);
        $this->assertSame(250.0, (float) $appointment->promo_discount_amount);
        $this->assertSame(750.0, (float) $appointment->amount_due);

        Promo::where('title', 'Anniversary Promo')->update([
            'end_date' => today()->subDay()->toDateString(),
        ]);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.appointments.quick_update'), [
                'appointment_id' => $appointment->id,
                'status' => 'approved',
            ])
            ->assertRedirect(route('admin.appointments'));

        $payment = Payment::where('appointment_id', $appointment->id)->firstOrFail();
        $this->assertSame(750.0, (float) $payment->amount_paid);
        $this->assertDatabaseHas('billing_accounts', [
            'client_id' => $this->client->id,
            'amount_due' => 750,
            'total_amount_due' => 750,
            'status' => 'paid',
        ]);

        $this->get(route('admin.sales.receipt', $payment))
            ->assertOk()
            ->assertSee('Anniversary Promo')
            ->assertSee('Discount: −₱250.00')
            ->assertSee('₱750.00');
    }

    public function test_website_booking_stores_active_promo_and_discounted_price_in_the_booking(): void
    {
        $this->promo([
            'title' => 'Website Booking Promo',
            'discount_percent' => 10,
        ]);

        $this->actingAs($this->client, 'client')
            ->post(route('client.book.submit'), [
                'installation_type' => 'residential',
                'installation_municipality' => 'Bantayan',
                'installation_barangay' => 'Atop-atop',
                'purok_landmark' => 'Purok 1 near the chapel',
                'service_id' => $this->service->id,
                'preferred_date' => today()->addDay()->format('Y-m-d'),
                'preferred_time' => '10:00:00',
                'payment_method' => 'GCash',
                'reference_number' => '1234567890123',
                'payment_proof' => UploadedFile::fake()->image('gcash-receipt.jpg', 800, 1200)->size(500),
            ])
            ->assertRedirect(route('client.appointments'));

        $appointment = Appointment::firstOrFail();
        $this->assertSame('Website Booking Promo', $appointment->promo_title);
        $this->assertSame(100.0, (float) $appointment->promo_discount_amount);
        $this->assertSame(900.0, (float) $appointment->amount_due);

        $this->get(route('client.appointments'))
            ->assertOk()
            ->assertSee('Website Booking Promo')
            ->assertSee('₱900.00');
    }

    public function test_admin_promo_view_is_available_and_dates_are_validated(): void
    {
        $this->actingAs($this->admin, 'admin')
            ->get(route('admin.promos'))
            ->assertOk()
            ->assertSee('Create a Promo');

        $this->post(route('admin.promos.store'), [
            'title' => 'Bad Dates',
            'description' => 'Invalid range',
            'discount_percent' => 10,
            'start_date' => today()->toDateString(),
            'end_date' => today()->subDay()->toDateString(),
        ])->assertSessionHasErrors('end_date');
    }
}
