<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Update the live plan lineup to the current Bogo Cable Television Inc. flyer:
     * 10/20/30/50/100/150 Mbps at 799/899/999/1199/1399/1699 with free installation.
     */
    public function up(): void
    {
        $plans = [
            ['service_name' => 'FTTH – 10 Mbps Plan',  'speed' => '10 Mbps',  'price' => 799.00,  'description' => "Unlimited Fiber To The Home (FTTH) Internet Connection With IPTV.\nUp to 10 Mbps.\nPerfect for small households."],
            ['service_name' => 'FTTH – 20 Mbps Plan',  'speed' => '20 Mbps',  'price' => 899.00,  'description' => "Unlimited Fiber To The Home (FTTH) Internet Connection With IPTV.\nUp to 20 Mbps.\nGreat for streaming and remote work."],
            ['service_name' => 'FTTH – 30 Mbps Plan',  'speed' => '30 Mbps',  'price' => 999.00,  'description' => "Unlimited Fiber To The Home (FTTH) Internet Connection With IPTV.\nUp to 30 Mbps.\nIdeal for medium-sized households."],
            ['service_name' => 'FTTH – 50 Mbps Plan',  'speed' => '50 Mbps',  'price' => 1199.00, 'description' => "Unlimited Fiber To The Home (FTTH) Internet Connection With IPTV.\nUp to 50 Mbps.\nFor power users and small offices."],
            ['service_name' => 'FTTH – 100 Mbps Plan', 'speed' => '100 Mbps', 'price' => 1399.00, 'description' => "Unlimited Fiber To The Home (FTTH) Internet Connection With IPTV.\nUp to 100 Mbps.\nPremium speed for large households."],
            ['service_name' => 'FTTH – 150 Mbps Plan', 'speed' => '150 Mbps', 'price' => 1699.00, 'description' => "Unlimited Fiber To The Home (FTTH) Internet Connection With IPTV.\nUp to 150 Mbps.\nOur fastest plan for heavy streaming, gaming, and businesses."],
        ];

        foreach ($plans as $plan) {
            DB::table('services')->updateOrInsert(
                ['service_name' => $plan['service_name']],
                array_merge($plan, [
                    'installation_fee' => 0.00,
                    'duration_minutes' => 60,
                    'status'           => 'Active',
                    'updated_at'       => now(),
                ])
            );
        }

        // The 5 Mbps plan is no longer offered
        DB::table('services')
            ->where('service_name', 'FTTH – 5 Mbps Plan')
            ->update(['status' => 'Inactive', 'updated_at' => now()]);
    }

    public function down(): void
    {
        $oldPrices = [
            'FTTH – 10 Mbps Plan'  => 899.00,
            'FTTH – 20 Mbps Plan'  => 999.00,
            'FTTH – 30 Mbps Plan'  => 1099.00,
            'FTTH – 50 Mbps Plan'  => 1299.00,
            'FTTH – 100 Mbps Plan' => 1599.00,
        ];

        foreach ($oldPrices as $name => $price) {
            DB::table('services')
                ->where('service_name', $name)
                ->update(['price' => $price, 'installation_fee' => 1000.00, 'updated_at' => now()]);
        }

        DB::table('services')
            ->where('service_name', 'FTTH – 5 Mbps Plan')
            ->update(['status' => 'Active', 'updated_at' => now()]);

        DB::table('services')
            ->where('service_name', 'FTTH – 150 Mbps Plan')
            ->update(['status' => 'Inactive', 'updated_at' => now()]);
    }
};
