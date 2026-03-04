<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChatMessage;
use App\Models\ChatReport;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $messages = ChatMessage::with('user')
            ->latest()
            ->take(50)
            ->get()
            ->reverse()
            ->values();

        $prefillMessage = trim((string) $request->query('message', ''));

        return view('chat.index', compact('messages', 'prefillMessage'));
    }

    public function fetch(Request $request)
    {
        $afterId = (int) $request->query('after_id', 0);

        $messages = ChatMessage::with('user')
            ->where('id', '>', $afterId)
            ->latest()
            ->get()
            ->reverse()
            ->values()
            ->map(function (ChatMessage $message) {
                return [
                    'id' => $message->id,
                    'name' => $message->user?->name ?? 'User',
                    'body' => $message->body,
                    'is_deleted' => (bool) $message->is_deleted,
                    'created_at' => $message->created_at?->diffForHumans(),
                    'avatar_url' => $message->user?->avatar_url,
                ];
            });

        return response()->json(['messages' => $messages]);
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'body' => ['required', 'string', 'max:500'],
        ]);

        ChatMessage::create([
            'user_id' => $request->user()->id,
            'body' => $data['body'],
            'is_deleted' => false,
        ]);

        return back()->with('status', 'Message sent.');
    }

    public function delete(Request $request, ChatMessage $message)
    {
        if ($message->user_id !== $request->user()->id && !$request->user()->isAdmin()) {
            abort(403);
        }

        $message->update([
            'body' => '[removed]',
            'is_deleted' => true,
        ]);

        return back()->with('status', 'Message removed.');
    }

    public function report(Request $request, ChatMessage $message)
    {
        ChatReport::create([
            'chat_message_id' => $message->id,
            'user_id' => $request->user()->id,
            'reason' => $request->input('reason'),
        ]);

        return back()->with('status', 'Message reported.');
    }
}
