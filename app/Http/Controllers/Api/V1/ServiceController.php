<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ServiceResource;
use App\Models\Service;

class ServiceController extends Controller
{
    // ─── GET /api/v1/services ────────────────────────────────────────────────
    public function index()
    {
        $services = Service::active()->orderBy('service_name')->get()->unique('service_name')->values();

        return response()->json([
            'success'  => true,
            'services' => ServiceResource::collection($services),
        ]);
    }
}
