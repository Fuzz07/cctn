<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Residential / Business installation types. Services declare which account
     * types they are offered to; appointments record the type that was booked.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('services', 'account_type')) {
            Schema::table('services', function (Blueprint $table) {
                $table->enum('account_type', ['residential', 'business', 'both'])
                    ->default('both')
                    ->after('status');
            });
        }

        // Seed the catalogue from the wording already used in each plan description.
        $residential = [
            'FTTH – 10 Mbps Plan',
            'FTTH – 20 Mbps Plan',
            'FTTH – 30 Mbps Plan',
            'FTTH – 100 Mbps Plan',
        ];

        DB::table('services')->whereIn('service_name', $residential)->update(['account_type' => 'residential']);

        if (! Schema::hasColumn('appointments', 'installation_type')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->enum('installation_type', ['residential', 'business'])
                    ->default('residential')
                    ->after('service_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('services', 'account_type')) {
            Schema::table('services', function (Blueprint $table) {
                $table->dropColumn('account_type');
            });
        }

        if (Schema::hasColumn('appointments', 'installation_type')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dropColumn('installation_type');
            });
        }
    }
};
