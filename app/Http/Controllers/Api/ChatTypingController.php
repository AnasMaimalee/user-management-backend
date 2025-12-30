<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\ChatTyping;

class ChatTypingController extends Controller
{
    public function store(Request $request)
    {
        broadcast(new ChatTyping(
            auth()->user()->name,
            $request->typing
        ))->toOthers();

        return response()->json(['status' => 'ok']);
    }
}
