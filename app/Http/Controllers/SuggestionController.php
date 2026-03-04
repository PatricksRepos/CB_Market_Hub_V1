<?php

namespace App\Http\Controllers;

use App\Models\Suggestion;
use App\Models\SuggestionVote;
use App\Models\SuggestionReport;
use Illuminate\Http\Request;

class SuggestionController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');

        $q = Suggestion::query()
            ->with(['user'])
            ->withCount('votes')
            ->latest();

        if ($status !== 'all') $q->where('status', $status);

        $suggestions = $q->paginate(20)->withQueryString();

        return view('suggestions.index', compact('suggestions','status'));
    }

    public function show(Suggestion $suggestion, Request $request)
    {
        $suggestion->load('user')->loadCount('votes');

        $hasVoted = false;
        if ($request->user()) {
            $hasVoted = SuggestionVote::where('suggestion_id',$suggestion->id)
                ->where('user_id',$request->user()->id)
                ->exists();
        }

        return view('suggestions.show', compact('suggestion','hasVoted'));
    }

    public function create()
    {
        return view('suggestions.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required','string','min:3','max:140'],
            'body' => ['nullable','string','max:6000'],
            'is_anonymous' => ['nullable'],
        ]);

        $s = Suggestion::create([
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'status' => 'open',
            'is_anonymous' => (bool)($data['is_anonymous'] ?? false),
        ]);

        return redirect()->route('suggestions.show', $s)->with('status','Suggestion posted.');
    }

    public function edit(Request $request, Suggestion $suggestion)
    {
        abort_unless($suggestion->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        return view('suggestions.edit', compact('suggestion'));
    }

    public function update(Request $request, Suggestion $suggestion)
    {
        abort_unless($suggestion->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        $data = $request->validate([
            'title' => ['required','string','min:3','max:140'],
            'body' => ['nullable','string','max:6000'],
            'is_anonymous' => ['nullable'],
        ]);

        $suggestion->update([
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'is_anonymous' => (bool)($data['is_anonymous'] ?? false),
        ]);

        return redirect()->route('suggestions.show', $suggestion)->with('status', 'Suggestion updated.');
    }

    public function destroy(Request $request, Suggestion $suggestion)
    {
        abort_unless($suggestion->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        $suggestion->delete();

        return redirect()->route('suggestions.index')->with('status', 'Suggestion deleted.');
    }

    public function vote(Request $request, Suggestion $suggestion)
    {
        SuggestionVote::firstOrCreate([
            'suggestion_id' => $suggestion->id,
            'user_id' => $request->user()->id,
        ]);

        return back()->with('status','Voted.');
    }

    public function unvote(Request $request, Suggestion $suggestion)
    {
        SuggestionVote::where('suggestion_id',$suggestion->id)
            ->where('user_id',$request->user()->id)
            ->delete();

        return back()->with('status','Vote removed.');
    }

    public function report(Request $request, Suggestion $suggestion)
    {
        $data = $request->validate([
            'reason' => ['nullable','string','max:120'],
        ]);

        SuggestionReport::create([
            'suggestion_id' => $suggestion->id,
            'user_id' => $request->user()->id,
            'reason' => $data['reason'] ?? null,
        ]);

        return back()->with('status','Reported. Thanks.');
    }

    public function setStatus(Request $request, Suggestion $suggestion)
    {
        if (!$request->user()->isAdmin()) abort(403);

        $data = $request->validate([
            'status' => ['required','in:open,planned,in_progress,done,rejected'],
        ]);

        $suggestion->update(['status' => $data['status']]);
        return back()->with('status','Status updated.');
    }
}
