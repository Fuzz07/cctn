<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The booking now captures the whole installation site - municipality,
     * barangay and purok/landmark - rather than relying on the barangay held
     * on the client profile, since the install site can differ from it.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (! Schema::hasColumn('appointments', 'installation_municipality')) {
                $table->string('installation_municipality', 100)->nullable()->after('installation_address');
            }
            if (! Schema::hasColumn('appointments', 'installation_barangay')) {
                $table->string('installation_barangay', 100)->nullable()->after('installation_municipality');
            }
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            foreach (['installation_municipality', 'installation_barangay'] as $column) {
                if (Schema::hasColumn('appointments', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
