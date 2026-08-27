<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Support\Chatbot\Assistant;
use Illuminate\Http\Request;

class ChatbotController extends Controller
{
    public function __construct(private readonly Assistant $assistant)
    {
    }

    // ─── POST /api/v1/chat ───────────────────────────────────────────────────
    //
    // The same assistant the website's chat bubble talks to. Sending an empty
    // message returns the opening greeting, which is what the app asks for when
    // the screen is first opened.
    public function reply(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:500',
        ]);

        $answer = $this->assistant->respond(
            $request->input('message'),
            $request->user(),
            Assistant::APP,
        );

        return response()->json(['success' => true] + $answer);
    }
}
