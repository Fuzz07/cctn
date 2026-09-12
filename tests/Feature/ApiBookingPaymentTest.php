<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiBookingPaymentTest extends TestCase
{
    use RefreshDatabase;

    private Client $client;
    private Service $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = Client::create([
            'firstname' => 'Mobile',
            'lastname' => 'Client',
            'email' => 'mobile-client@example.com',
            'username' => 'mobileclient',
            'password' => Hash::make('password123'),
        ]);

        $this->service = Service::create([
            'service_name' => 'Fiber Installation',
            'price' => 999,
            'status' => 'Active',
        ]);

        Sanctum::actingAs($this->client);
    }

    public function test_mobile_booking_requires_payment_reference_and_receipt(): void
    {
        $this->postJson('/api/v1/appointments', [
            'service_id' => $this->service->id,
            'preferred_date' => now()->addDay()->format('Y-m-d'),
            'preferred_time' => '09:00',
            'payment_method' => 'GCash',
        ])->assertStatus(422)
            ->assertJsonValidationErrors(['reference_number', 'payment_proof']);
    }

    public function test_mobile_booking_stores_payment_reference_and_receipt(): void
    {
        Storage::fake('public');

        $response = $this->post('/api/v1/appointments', [
            'service_id' => $this->service->id,
            'preferred_date' => now()->addDay()->format('Y-m-d'),
            'preferred_time' => '09:00',
            'payment_method' => 'GCash',
            'reference_number' => '10029384756',
            'payment_proof' => UploadedFile::fake()->image('gcash-receipt.jpg', 800, 1200)->size(500),
        ]);

        $response->assertCreated()->assertJson([
            'success' => true,
            'rescheduled' => false,
        ]);

        $appointment = Appointment::firstOrFail();
        $this->assertSame('GCash', $appointment->payment_method);
        $this->assertSame('10029384756', $appointment->reference_number);
        $this->assertNotNull($appointment->payment_proof);
        Storage::disk('public')->assertExists($appointment->payment_proof);
    }

    public function test_mobile_can_fetch_slots_with_overtime_flag(): void
    {
        $response = $this->getJson('/api/v1/appointments/slots?date=' . now()->addDay()->format('Y-m-d'));

        $response->assertOk()->assertJson(['success' => true]);
        $slots = $response->json('slots');
        $this->assertNotEmpty($slots);

        $overtimeSlot = collect($slots)->firstWhere('time', '18:00');
        $this->assertNotNull($overtimeSlot);
        $this->assertTrue($overtimeSlot['is_overtime']);
        $this->assertStringContainsString('Overtime', $overtimeSlot['label']);
    }
}
