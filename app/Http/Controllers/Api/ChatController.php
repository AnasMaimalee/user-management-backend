<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatRoom;
use App\Models\ChatMessage;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Fetch messages for company chat
     */
    public function index(Request $request)
    {
        $room = ChatRoom::first(); // Company Chat

        $messages = ChatMessage::with(['user:id,name', 'employee:id,first_name,last_name'])
            ->where('chat_room_id', $room->id)
            ->latest()
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        return response()->json([
            'room' => $room,
            'messages' => $messages,
        ]);
    }

    /**
     * Send a message
     */
    public function store(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $room = ChatRoom::first();

        $user = $request->user(); // Sanctum user

        $message = ChatMessage::create([
            'chat_room_id' => $room->id,
            'user_id' => $user->employee ? null : $user->id,
            'employee_id' => $user->employee?->id,
            'message' => $request->message,
        ]);

        return response()->json([
            'message' => $message->load(['user:id,name', 'employee:id,first_name,last_name']),
        ], 201);
    }
}
