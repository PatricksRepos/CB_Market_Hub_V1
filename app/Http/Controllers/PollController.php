<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class PollController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified'])->only(['create','store','vote']);
    }

    public function index()
    {
        $polls = Poll::with(['options','votes'])->latest()->paginate(20);
        return view('polls.index', compact('polls'));
    }

    public function create()
    {
        return view('polls.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question' => ['required','string','max:200'],
            'options' => ['required','array','min:2','max:8'],
            'options.*' => ['nullable','string','max:120'],
            'results_visibility' => ['required','in:always,after_vote,after_end'],
            'duration_minutes' => ['nullable','integer','min:1','max:43200'], // up to 30 days
        ]);

        $options = collect($data['options'])
            ->map(fn($v) => is_string($v) ? trim($v) : '')
            ->filter(fn($v) => $v !== '')
            ->values()
            ->all();

        if (count($options) < 2) {
            return back()->withErrors(['options' => 'You need at least 2 options.'])->withInput();
        }

        $now = Carbon::now();
        $endsAt = null;
        if (!empty($data['duration_minutes'])) {
            $endsAt = $now->copy()->addMinutes((int)$data['duration_minutes']);
        }

        $poll = Poll::create([
            'user_id' => $request->user()->id,
            'question' => $data['question'],
            'is_active' => true,
            'starts_at' => $now,
            'ends_at' => $endsAt,
            'results_visibility' => $data['results_visibility'],
        ]);

        foreach ($options as $label) {
            PollOption::create([
                'poll_id' => $poll->id,
                'label' => $label,
            ]);
        }

        return redirect()->route('polls.show', $poll)->with('status', 'Poll created.');
    }

    public function show(Poll $poll, Request $request)
    {
        $poll->load(['options','votes']);

        $user = $request->user();
        $myVote = null;

        if ($user) {
            $myVote = PollVote::where('poll_id', $poll->id)
                ->where('user_id', $user->id)
                ->first();
        }

        $now = Carbon::now();
        $hasEnded = $poll->ends_at ? $now->gte($poll->ends_at) : false;

        $canSeeResults = match ($poll->results_visibility) {
            'always' => true,
            'after_vote' => (bool) $myVote,
            'after_end' => $hasEnded,
            default => false,
        };

        return view('polls.show', compact('poll', 'myVote', 'canSeeResults', 'hasEnded'));
    }

    public function vote(Request $request, Poll $poll)
    {
        $request->validate([
            'poll_option_id' => ['required','integer','exists:poll_options,id'],
        ]);

        // Ensure option belongs to this poll
        if (!$poll->options()->where('id', $request->poll_option_id)->exists()) {
            return back()->withErrors(['poll_option_id' => 'Invalid option.']);
        }

        // Prevent voting after end (if duration set)
        if ($poll->ends_at && now()->gte($poll->ends_at)) {
            return back()->with('status', 'This poll has ended.');
        }

        PollVote::updateOrCreate(
            ['poll_id' => $poll->id, 'user_id' => $request->user()->id],
            ['poll_option_id' => $request->poll_option_id]
        );

        return back()->with('status', 'Vote saved.');
    }
}
