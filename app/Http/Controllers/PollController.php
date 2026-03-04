<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use Illuminate\Http\Request;

class PollController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'verified'])->only([
            'create','store','vote','endEarly','updateVisibility','destroy'
        ]);
    }

    public function index(Request $request)
    {
        $status = $request->query('status', 'active'); // active|ended|all
        $sort = $request->query('sort', 'new');        // new|votes|ending

        $q = Poll::query()->with(['options','votes']);

        if ($status === 'active') {
            $q->where(function ($w) {
                $w->whereNull('ends_at')->orWhere('ends_at', '>', now());
            });
        } elseif ($status === 'ended') {
            $q->whereNotNull('ends_at')->where('ends_at', '<=', now());
        }

        if ($sort === 'votes') {
            $q->withCount('votes')->orderByDesc('votes_count')->latest();
        } elseif ($sort === 'ending') {
            // nulls last: order by whether ends_at is null, then ends_at asc
            $q->orderByRaw('ends_at IS NULL asc')->orderBy('ends_at', 'asc')->latest();
        } else {
            $q->latest();
        }

        $polls = $q->paginate(20)->withQueryString();

        return view('polls.index', compact('polls','status','sort'));
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

        // If results are private until end, require a duration.
        if ($data['results_visibility'] === 'after_end' && empty($data['duration_minutes'])) {
            return back()->withErrors(['duration_minutes' => 'Duration is required when results are private until the poll ends.'])->withInput();
        }

        $now = now();
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
            PollOption::create(['poll_id' => $poll->id, 'label' => $label]);
        }

        return redirect()->route('polls.show', $poll)->with('status', 'Poll created.');
    }

    public function show(Poll $poll, Request $request)
    {
        $poll->load(['options','votes','comments.user']);

        $user = $request->user();
        $myVote = null;
        if ($user) {
            $myVote = PollVote::where('poll_id', $poll->id)
                ->where('user_id', $user->id)
                ->first();
        }

        $hasEnded = $poll->ends_at ? now()->gte($poll->ends_at) : false;

        $canSeeResults = match ($poll->results_visibility) {
            'always' => true,
            'after_vote' => (bool) $myVote,
            'after_end' => $hasEnded,
            default => false,
        };

        $isCreator = $user && ((int)$poll->user_id === (int)$user->id);
        $isAdmin = $user && (bool)$user->is_admin;

        return view('polls.show', compact('poll','myVote','hasEnded','canSeeResults','isCreator','isAdmin'));
    }

    public function vote(Request $request, Poll $poll)
    {
        $request->validate([
            'poll_option_id' => ['required','integer','exists:poll_options,id'],
        ]);

        if ($poll->ends_at && now()->gte($poll->ends_at)) {
            return back()->with('status', 'This poll has ended.');
        }

        if (!$poll->options()->where('id', $request->poll_option_id)->exists()) {
            return back()->withErrors(['poll_option_id' => 'Invalid option.']);
        }

        PollVote::updateOrCreate(
            ['poll_id' => $poll->id, 'user_id' => $request->user()->id],
            ['poll_option_id' => $request->poll_option_id]
        );

        return back()->with('status', 'Vote saved.');
    }

    public function endEarly(Request $request, Poll $poll)
    {
        $user = $request->user();
        if (!$user || ((int)$poll->user_id !== (int)$user->id && !$user->is_admin)) {
            abort(403);
        }

        $poll->ends_at = now();
        $poll->save();

        return back()->with('status', 'Poll ended.');
    }

    public function updateVisibility(Request $request, Poll $poll)
    {
        $user = $request->user();
        if (!$user || ((int)$poll->user_id !== (int)$user->id && !$user->is_admin)) {
            abort(403);
        }

        $data = $request->validate([
            'results_visibility' => ['required','in:always,after_vote,after_end'],
        ]);

        // If switching to after_end, require an end time
        if ($data['results_visibility'] === 'after_end' && !$poll->ends_at) {
            return back()->withErrors(['results_visibility' => 'You must set a duration/end time to hide results until the end.']);
        }

        $poll->results_visibility = $data['results_visibility'];
        $poll->save();

        return back()->with('status', 'Visibility updated.');
    }

    public function destroy(Request $request, Poll $poll)
    {
        $user = $request->user();
        if (!$user || ((int)$poll->user_id !== (int)$user->id && !$user->is_admin)) {
            abort(403);
        }

        $poll->delete();
        return redirect()->route('polls.index')->with('status', 'Poll deleted.');
    }
}
