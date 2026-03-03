<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Http\Request;

class PollVoteController extends Controller
{
    public function store(Poll $poll, Request $request)
    {
        abort_unless($poll->isOpen(), 403);

        $data = $request->validate([
            'poll_option_id' => 'required|integer',
        ]);

        // option must belong to this poll
        $option = $poll->options()->where('id', $data['poll_option_id'])->firstOrFail();

        // one vote per account (unique index also enforces)
        PollVote::updateOrCreate(
            ['poll_id' => $poll->id, 'user_id' => $request->user()->id],
            ['poll_option_id' => $option->id]
        );

        return redirect()->route('polls.show', $poll)->with('status', 'Vote recorded.');
    }
}
