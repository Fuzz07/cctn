<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The client profile only carries barangay and municipality. Field technicians
     * need the finer locator - purok / street plus a nearby landmark - to find the
     * installation site, so it is captured per booking.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('appointments', 'purok_landmark')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->string('purok_landmark', 255)->nullable()->after('installation_address');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('appointments', 'purok_landmark')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropColumn('purok_landmark');
            });
        }
    }
};
