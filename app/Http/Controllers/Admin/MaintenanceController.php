<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MaintenanceRequest;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MaintenanceController extends Controller
{
    public function index()
    {
        $requests = MaintenanceRequest::with(['client', 'messages'])->orderBy('created_at', 'desc')->get();
        return view('admin.maintenance.index', compact('requests'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'request_id' => 'required|exists:maintenance_requests,id',
            'status' => 'required|in:Open,In Progress,Resolved,Closed',
            'reply_message' => 'nullable|string|max:1000',
        ]);

        $maintenance = MaintenanceRequest::findOrFail($request->request_id);
        $reply = trim((string) $request->input('reply_message'));

        $attributes = ['status' => $request->status];
        if ($reply !== '') {
            $attributes['follow_up_note'] = $reply;
        }
        $maintenance->update($attributes);

        if ($reply !== '') {
            $maintenance->messages()->create([
                'sender_type' => 'admin',
                'sender_id' => Auth::guard('admin')->id(),
                'message' => $reply,
            ]);

            Notification::create([
                'for_admin' => false,
                'client_id' => $maintenance->client_id,
                'title' => 'Maintenance Request Update',
                'message' => "The administrator replied to maintenance request #{$maintenance->id}.",
                'link' => 'notifications',
            ]);
        }

        $message = $reply !== ''
            ? 'Maintenance request updated and the reply was sent to the client.'
            : 'Maintenance request status updated.';

        return redirect()->route('admin.maintenance')->with('success_message', $message);
    }
}
