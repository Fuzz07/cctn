<?php

namespace App\Http\Controllers;

use App\Support\Chatbot\Assistant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * The chat bubble on the website.
 *
 * Open to signed-out visitors on purpose — the landing page carries the bubble
 * too, and the assistant answers what it can without an account. Whether a
 * question needs one is [Assistant]'s decision, not this controller's; all it
 * does is hand over whoever is signed in, if anyone.
 */
class ChatbotController extends Controller
{
    public function __construct(private readonly Assistant $assistant)
    {
    }

    // ─── POST /chat ──────────────────────────────────────────────────────────
    public function reply(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:500',
        ]);

        return response()->json(
            $this->assistant->respond(
                $request->input('message'),
                Auth::guard('client')->user(),
                Assistant::WEB,
            )
        );
    }
}
