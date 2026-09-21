<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function test_the_application_returns_a_successful_response()
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_landing_page_only_displays_paid_broadband_plans()
    {
        // Clear any services created during initial migration
        \App\Models\Service::query()->delete();

        // 1. Create a paid active FTTH plan
        \App\Models\Service::create([
            'service_name' => 'FTTH - Super Fast 50 Mbps Plan',
            'description' => "Great for remote work",
            'duration_minutes' => 60,
            'price' => 999.00,
            'status' => 'Active',
        ]);

        // 2. Create a free technical support service
        \App\Models\Service::create([
            'service_name' => 'WiFi Troubleshooting Dispatch',
            'description' => "Technician checkup",
            'duration_minutes' => 45,
            'price' => 0.00,
            'status' => 'Active',
        ]);

        // 3. Create an inactive paid plan
        \App\Models\Service::create([
            'service_name' => 'FTTH - Legacy 5 Mbps Plan',
            'description' => "Old plan",
            'duration_minutes' => 60,
            'price' => 499.00,
            'status' => 'Inactive',
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);

        // Verify that only the active paid plan is passed to the view's services collection
        $response->assertViewHas('services', function ($services) {
            return $services->count() === 1 
                && $services->first()->service_name === 'FTTH - Super Fast 50 Mbps Plan';
        });
    }
}
