<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Deduplicate services in the database and re-link appointments to canonical service IDs.
     */
    public function up(): void
    {
        $allServices = DB::table('services')->orderBy('id', 'asc')->get();
        
        $grouped = [];
        foreach ($allServices as $srv) {
            // Normalize key by collapsing spaces and standardizing dashes for comparison
            $normKey = mb_strtolower(trim(preg_replace('/\s+/', ' ', str_replace(['–', '—'], '-', $srv->service_name))));
            $grouped[$normKey][] = $srv;
        }

        foreach ($grouped as $normKey => $srvList) {
            if (count($srvList) > 1) {
                // Keep the active service with the lowest ID, or the first record
                usort($srvList, function ($a, $b) {
                    if ($a->status === 'Active' && $b->status !== 'Active') return -1;
                    if ($b->status === 'Active' && $a->status !== 'Active') return 1;
                    return $a->id <=> $b->id;
                });

                $canonical = $srvList[0];
                $duplicateIds = [];
                for ($i = 1; $i < count($srvList); $i++) {
                    $duplicateIds[] = $srvList[$i]->id;
                }

                if (!empty($duplicateIds)) {
                    // Re-link any appointments pointing to duplicate IDs
                    DB::table('appointments')
                        ->whereIn('service_id', $duplicateIds)
                        ->update(['service_id' => $canonical->id]);

                    // Remove duplicate services
                    DB::table('services')
                        ->whereIn('id', $duplicateIds)
                        ->delete();
                }
            }
        }

        // Add unique constraint to prevent future duplicate service records
        try {
            Schema::table('services', function (Blueprint $table) {
                $table->unique('service_name');
            });
        } catch (\Throwable $e) {
            // If already indexed or constrained, continue safely
        }
    }

    public function down(): void
    {
        try {
            Schema::table('services', function (Blueprint $table) {
                $table->dropUnique(['service_name']);
            });
        } catch (\Throwable $e) {
            // Continue safely
        }
    }
};
