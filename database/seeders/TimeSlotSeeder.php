<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TimeSlotSeeder extends Seeder
{
    public function run(): void
    {
        $slots = ['08:00:00', '10:00:00', '12:00:00', '14:00:00', '16:00:00', '18:00:00'];

        // Remove legacy slots not in the new schedule
        DB::table('time_slots')->whereNotIn('slot_time', $slots)->delete();

        foreach ($slots as $slot) {
            DB::table('time_slots')->updateOrInsert(
                ['slot_time' => $slot],
                [
                    'is_available' => true,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]
            );
        }
    }
}
