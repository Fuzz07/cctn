<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only backfill when the columns are being added for the first time, so a
        // re-run never overwrites subscription data that already exists.
        $needsBackfill = ! Schema::hasColumn('clients', 'account_status');

        Schema::table('clients', function (Blueprint $table) {
            if (! Schema::hasColumn('clients', 'account_status')) {
                $table->string('account_status', 20)->default('Inactive')->after('archived_at')->index();
            }
            if (! Schema::hasColumn('clients', 'subscription_status')) {
                $table->string('subscription_status', 20)->nullable()->after('account_status')->index();
            }
            if (! Schema::hasColumn('clients', 'current_service_id')) {
                $table->unsignedBigInteger('current_service_id')->nullable()->after('subscription_status');
            }
            if (! Schema::hasColumn('clients', 'current_appointment_id')) {
                $table->unsignedBigInteger('current_appointment_id')->nullable()->after('current_service_id');
            }
            if (! Schema::hasColumn('clients', 'subscription_started_at')) {
                $table->dateTime('subscription_started_at')->nullable()->after('current_appointment_id');
            }
            if (! Schema::hasColumn('clients', 'subscription_ends_at')) {
                $table->dateTime('subscription_ends_at')->nullable()->after('subscription_started_at')->index();
            }
            if (! Schema::hasColumn('clients', 'subscription_cancelled_at')) {
                $table->dateTime('subscription_cancelled_at')->nullable()->after('subscription_ends_at');
            }
        });

        if (! Schema::hasColumn('appointments', 'subscription_ends_at')) {
            Schema::table('appointments', function (Blueprint $table) {
                $table->dateTime('subscription_ends_at')->nullable()->after('due_date');
            });
        }

        if (! $needsBackfill) {
            return;
        }

        DB::table('clients')->orderBy('id')->each(function ($client) {
            $latestPlan = DB::table('appointments')
                ->where('client_id', $client->id)
                ->orderByDesc('id')
                ->first();

            $archived = isset($client->archived_at) && $client->archived_at !== null;
            $isActive = ! $archived && $latestPlan && $latestPlan->status === 'approved';

            DB::table('clients')->where('id', $client->id)->update([
                'account_status'          => $isActive ? 'Active' : 'Inactive',
                'subscription_status'     => $isActive
                    ? 'active'
                    : (($archived || ($latestPlan && $latestPlan->status === 'cancelled')) ? 'cancelled' : null),
                'current_service_id'      => $latestPlan->service_id ?? null,
                'current_appointment_id'  => $latestPlan->id ?? null,
                'subscription_started_at' => $isActive ? ($latestPlan->updated_at ?? $latestPlan->created_at) : null,
                'subscription_cancelled_at' => ! $isActive && $latestPlan
                    ? ($latestPlan->updated_at ?? now())
                    : null,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn('subscription_ends_at');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn([
                'account_status',
                'subscription_status',
                'current_service_id',
                'current_appointment_id',
                'subscription_started_at',
                'subscription_ends_at',
                'subscription_cancelled_at',
            ]);
        });
    }
};
