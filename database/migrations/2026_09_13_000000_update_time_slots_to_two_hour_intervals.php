<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('time_slots')) {
            return;
        }

        $newSlots = ['08:00:00', '10:00:00', '12:00:00', '14:00:00', '16:00:00', '18:00:00'];

        DB::table('time_slots')->whereNotIn('slot_time', $newSlots)->delete();

        foreach ($newSlots as $slot) {
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

    public function down(): void
    {
    }
};
