<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Listing;
use App\Models\Poll;
use App\Models\PollComment;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\Suggestion;

class FeedController extends Controller
{
    public function index()
    {
        $posts = Post::query()->with(['user', 'images'])->latest()->take(12)->get();
        $polls = Poll::query()->with('user')->latest()->take(8)->get();
        $postComments = PostComment::query()->latest()->with(['user', 'post'])->take(8)->get();
        $pollComments = PollComment::query()->latest()->with(['user', 'poll'])->take(8)->get();
        $events = Event::query()->where('is_public', true)->with('user')->latest()->take(8)->get();
        $suggestions = Suggestion::query()->with('user')->latest()->take(8)->get();
        $listings = Listing::query()->where('is_active', true)->with('user')->latest()->take(8)->get();

        $items = collect()
            ->concat($posts->map(fn ($p) => ['type' => 'post', 'at' => $p->created_at, 'data' => $p]))
            ->concat($polls->map(fn ($p) => ['type' => 'poll', 'at' => $p->created_at, 'data' => $p]))
            ->concat($events->map(fn ($e) => ['type' => 'event', 'at' => $e->created_at, 'data' => $e]))
            ->concat($suggestions->map(fn ($s) => ['type' => 'suggestion', 'at' => $s->created_at, 'data' => $s]))
            ->concat($listings->map(fn ($l) => ['type' => 'listing', 'at' => $l->created_at, 'data' => $l]))
            ->concat($postComments->map(fn ($c) => ['type' => 'post_comment', 'at' => $c->created_at, 'data' => $c]))
            ->concat($pollComments->map(fn ($c) => ['type' => 'poll_comment', 'at' => $c->created_at, 'data' => $c]))
            ->sortByDesc('at')
            ->take(40)
            ->values();

        return view('feed.index', compact('items'));
    }
}
