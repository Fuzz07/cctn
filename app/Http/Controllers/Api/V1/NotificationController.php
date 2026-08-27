<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $client = $request->user();

        $notifications = Notification::where('for_admin', false)
            ->where(function ($q) use ($client) {
                $q->where('client_id', $client->id)
                  ->orWhereNull('client_id');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $unreadCount = $notifications->where('is_read', false)->count();

        $formatted = $notifications->map(function ($notif) {
            return [
                'id'         => $notif->id,
                'title'      => $notif->title,
                'message'    => $notif->message,
                'link'       => $notif->link,
                'is_read'    => (bool) $notif->is_read,
                'created_at' => $notif->created_at ? $notif->created_at->toISOString() : null,
            ];
        });

        return response()->json([
            'status' => 'success',
            'data'   => [
                'notifications' => $formatted,
                'unread_count'  => $unreadCount,
            ],
        ]);
    }

    public function markRead(Request $request, $id)
    {
        $client = $request->user();

        $notification = Notification::where('id', $id)
            ->where('for_admin', false)
            ->where(function ($q) use ($client) {
                $q->where('client_id', $client->id)
                  ->orWhereNull('client_id');
            })
            ->firstOrFail();

        $notification->update(['is_read' => true]);

        return response()->json([
            'status'  => 'success',
            'message' => 'Notification marked as read.',
        ]);
    }

    public function markAllRead(Request $request)
    {
        $client = $request->user();

        Notification::where('for_admin', false)
            ->where(function ($q) use ($client) {
                $q->where('client_id', $client->id)
                  ->orWhereNull('client_id');
            })
            ->update(['is_read' => true]);

        return response()->json([
            'status'  => 'success',
            'message' => 'All notifications marked as read.',
        ]);
    }
}
