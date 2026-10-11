<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Services\PromoPricing;

class ServiceController extends Controller
{
    // ─── GET /api/v1/services ────────────────────────────────────────────────
    public function index(PromoPricing $promoPricing)
    {
        $services = Service::active()->orderBy('service_name')->get()->unique('service_name')->values();
        $promo = $promoPricing->activePromo();
        $services = $services->map(function (Service $service) use ($promoPricing, $promo) {
            return array_merge(
                (new ServiceResource($service))->resolve(),
                $promoPricing->snapshotForPromo((float) $service->price, $promo),
            );
        });

        return response()->json([
            'success'  => true,
            'services' => $services,
        ]);
    }
}
