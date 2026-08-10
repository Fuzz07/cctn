<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['service_name' => 'FTTH – 10 Mbps Plan',  'speed' => '10 Mbps',  'description' => "Unlimited Fiber To The Home (FTTH) Internet Connection With IPTV.\nUp to 10 Mbps.\nPerfect for small households.",          'duration_minutes' => 60,  'price' => 799.00,  'installation_fee' => 0.00, 'status' => 'Active'],
            ['service_name' => 'FTTH – 20 Mbps Plan',  'speed' => '20 Mbps',  'description' => "Unlimited Fiber To The Home (FTTH) Internet Connection With IPTV.\nUp to 20 Mbps.\nGreat for streaming and remote work.",   'duration_minutes' => 60,  'price' => 899.00,  'installation_fee' => 0.00, 'status' => 'Active'],
            ['service_name' => 'FTTH – 30 Mbps Plan',  'speed' => '30 Mbps',  'description' => "Unlimited Fiber To The Home (FTTH) Internet Connection With IPTV.\nUp to 30 Mbps.\nIdeal for medium-sized households.",     'duration_minutes' => 60,  'price' => 999.00,  'installation_fee' => 0.00, 'status' => 'Active'],
            ['service_name' => 'FTTH – 50 Mbps Plan',  'speed' => '50 Mbps',  'description' => "Unlimited Fiber To The Home (FTTH) Internet Connection With IPTV.\nUp to 50 Mbps.\nFor power users and small offices.",     'duration_minutes' => 60,  'price' => 1199.00, 'installation_fee' => 0.00, 'status' => 'Active'],
            ['service_name' => 'FTTH – 100 Mbps Plan', 'speed' => '100 Mbps', 'description' => "Unlimited Fiber To The Home (FTTH) Internet Connection With IPTV.\nUp to 100 Mbps.\nPremium speed for large households.",   'duration_minutes' => 60,  'price' => 1399.00, 'installation_fee' => 0.00, 'status' => 'Active'],
            ['service_name' => 'FTTH – 150 Mbps Plan', 'speed' => '150 Mbps', 'description' => "Unlimited Fiber To The Home (FTTH) Internet Connection With IPTV.\nUp to 150 Mbps.\nOur fastest plan for heavy streaming, gaming, and businesses.", 'duration_minutes' => 60, 'price' => 1699.00, 'installation_fee' => 0.00, 'status' => 'Active'],
            ['service_name' => 'WiFi Installation',    'description' => 'Professional on-site WiFi router installation and network configuration.',                                                  'duration_minutes' => 120, 'price' => 0.00,    'status' => 'Active'],
            ['service_name' => 'WiFi Troubleshooting & Technical Repair Visit', 'description' => 'Schedule a home dispatch for diagnosing connection issues, red LOS light, and fiber repairs.',    'duration_minutes' => 45,  'price' => 0.00,    'status' => 'Active'],
        ];

        foreach ($services as $service) {
            DB::table('services')->insertOrIgnore(array_merge($service, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
