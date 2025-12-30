<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use App\Events\ChatMessageSeen;

class ChatSeenController extends Controller
{
    public function store(Request $request)
    {
        $messages = ChatMessage::whereNull('seen_at')
            ->where('user_id', '!=', auth()->id())
            ->get();

        foreach ($messages as $message) {
            $message->update([
                'seen_at' => now(),
            ]);

            broadcast(new ChatMessageSeen($message))->toOthers();
        }

        return response()->json(['status' => 'ok']);
    }
}
