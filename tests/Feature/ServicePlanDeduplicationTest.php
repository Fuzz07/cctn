<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Service;
use App\Models\TimeSlot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ServicePlanDeduplicationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_insert_duplicate_service_plan_name_due_to_unique_constraint()
    {
        $this->expectException(QueryException::class);

        // Attempting to insert two services with the exact same plan name
        Service::create([
            'service_name'     => 'FTTH – 10 Mbps Plan',
            'speed'            => '10 Mbps',
            'price'            => 799.00,
            'duration_minutes' => 60,
            'status'           => 'Active',
        ]);
        Service::create([
            'service_name'     => 'FTTH – 10 Mbps Plan',
            'speed'            => '10 Mbps',
            'price'            => 799.00,
            'duration_minutes' => 60,
            'status'           => 'Active',
        ]);
    }

    public function test_booking_page_displays_each_plan_only_once_in_the_dropdown()
    {
        $client = Client::create([
            'firstname' => 'Test',
            'lastname'  => 'Client',
            'email'     => 'test@example.com',
            'username'  => 'testclient',
            'password'  => Hash::make('password123'),
        ]);

        TimeSlot::firstOrCreate(['slot_time' => '08:00:00'], ['is_available' => true]);

        $response = $this->actingAs($client, 'client')->get(route('client.book'));
        $response->assertStatus(200);

        // Verify that view receives services where every service_name is distinct
        $response->assertViewHas('services', function ($services) {
            return $services->count() > 0 
                && $services->pluck('service_name')->count() === $services->pluck('service_name')->unique()->count();
        });

        // Ensure 10 Mbps plan is present exactly once
        $content = $response->getContent();
        $this->assertEquals(1, substr_count($content, 'FTTH – 10 Mbps Plan'));
    }

    public function test_api_services_endpoint_returns_distinct_services()
    {
        $client = Client::create([
            'firstname' => 'Mobile',
            'lastname'  => 'Client',
            'email'     => 'mobile-client@example.com',
            'username'  => 'mobileclient',
            'password'  => Hash::make('password123'),
        ]);
        Sanctum::actingAs($client);

        $response = $this->getJson('/api/v1/services');
        $response->assertStatus(200);

        $data = $response->json('services');
        $names = array_column($data, 'service_name');

        $this->assertNotEmpty($names);
        $this->assertEquals(count($names), count(array_unique($names)), 'All plan names returned by API must be unique');
    }
}
