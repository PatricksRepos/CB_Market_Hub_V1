<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatMessage;
use App\Models\ChatReport;

class ChatController extends Controller
{
    public function index()
    {
        $messages = ChatMessage::with('user')
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        return view('chat.index', compact('messages'));
    }

    public function fetch(Request $request)
    {
        $afterId = (int) $request->query('after_id', 0);

        $messages = ChatMessage::with('user')
            ->where('id', '>', $afterId)
            ->latest()
            ->get()
            ->reverse()
            ->values();

        return response()->json($messages);
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:500']
        ]);

        ChatMessage::create([
            'user_id' => auth()->id(),
            'message' => $data['message'],
        ]);

        return back();
    }

    public function delete(ChatMessage $message)
    {
        if ($message->user_id !== auth()->id()) {
            abort(403);
        }

        $message->delete();

        return back();
    }

    public function report(Request $request, ChatMessage $message)
    {
        ChatReport::create([
            'chat_message_id' => $message->id,
            'user_id' => auth()->id(),
            'reason' => $request->input('reason'),
        ]);

        return back()->with('status', 'Message reported');
    }
}
