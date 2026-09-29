<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MaintenanceMessageResource;
use App\Http\Resources\MaintenanceResource;
use App\Models\MaintenanceRequest;
use App\Models\Notification;
use Illuminate\Http\Request;

class MaintenanceController extends Controller
{
    // ─── GET /api/v1/maintenance ─────────────────────────────────────────────
    public function index(Request $request)
    {
        $client = $request->user();

        $requests = MaintenanceRequest::where('client_id', $client->id)
            ->with('messages')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success'  => true,
            'requests' => MaintenanceResource::collection($requests),
        ]);
    }

    // ─── POST /api/v1/maintenance ────────────────────────────────────────────
    public function store(Request $request)
    {
        $request->validate([
            'subject'     => 'required|string|max:150',
            'description' => 'required|string|max:1000',
            'priority'    => 'required|in:low,medium,high',
        ]);

        $client = $request->user();

        $maintenance = MaintenanceRequest::create([
            'client_id'   => $client->id,
            'subject'     => $request->subject,
            'description' => $request->description,
            'priority'    => ucfirst($request->priority),
            'status'      => 'Open',
        ]);

        Notification::create([
            'for_admin' => true,
            'client_id' => $client->id,
            'title' => 'New Maintenance Request',
            'message' => "{$client->full_name} submitted a maintenance request: {$maintenance->subject}.",
            'link' => 'admin/maintenance',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Maintenance request submitted successfully.',
            'request' => new MaintenanceResource($maintenance),
        ], 201);
    }

    public function storeMessage(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $client = $request->user();
        $maintenance = MaintenanceRequest::where('client_id', $client->id)->findOrFail($id);

        if (strcasecmp($maintenance->status, 'Closed') === 0) {
            return response()->json([
                'success' => false,
                'message' => 'This maintenance request is closed and cannot receive new messages.',
            ], 422);
        }

        $message = $maintenance->messages()->create([
            'sender_type' => 'client',
            'sender_id' => $client->id,
            'message' => trim($request->message),
        ]);

        Notification::create([
            'for_admin' => true,
            'client_id' => $client->id,
            'title' => 'Maintenance Message',
            'message' => "{$client->full_name} added a message to maintenance request #{$maintenance->id}.",
            'link' => 'admin/maintenance',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your message was sent to the administrator.',
            'maintenance_message' => new MaintenanceMessageResource($message),
        ], 201);
    }
}
