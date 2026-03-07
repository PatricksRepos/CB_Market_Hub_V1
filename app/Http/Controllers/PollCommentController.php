<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollComment;
use Illuminate\Http\Request;
use App\Support\Gamification;

class PollCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','verified']);
    }

    public function store(Request $request, Poll $poll)
    {
        $data = $request->validate([
            'body' => ['required','string','min:1','max:2000'],
        ]);

        $comment = PollComment::create([
            'poll_id' => $poll->id,
            'user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        Gamification::award($request->user(), 'poll.comment.created', 'poll_comment:'.$comment->id);

        return back()->with('status', 'Comment posted.');
    }

    public function destroy(Request $request, Poll $poll, PollComment $comment)
    {
        // ensure it belongs to this poll
        if ((int)$comment->poll_id !== (int)$poll->id) {
            abort(404);
        }

        $user = $request->user();
        $canDelete = $user && ($user->is_admin || (int)$comment->user_id === (int)$user->id);

        if (!$canDelete) {
            abort(403);
        }

        $comment->delete();
        return back()->with('status', 'Comment deleted.');
    }
}
