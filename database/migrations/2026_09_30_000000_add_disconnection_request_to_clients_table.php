<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->string('disconnection_request_status', 20)->nullable()->after('subscription_cancelled_at')->index();
            $table->dateTime('disconnection_requested_at')->nullable()->after('disconnection_request_status');
            $table->dateTime('disconnection_reviewed_at')->nullable()->after('disconnection_requested_at');
            $table->text('disconnection_review_note')->nullable()->after('disconnection_reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'disconnection_request_status',
                'disconnection_requested_at',
                'disconnection_reviewed_at',
                'disconnection_review_note',
            ]);
        });
    }
};
