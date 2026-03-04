<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Listing;
use App\Models\Poll;
use App\Models\PollComment;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\Suggestion;
use App\Support\Reactions;

class FeedController extends Controller
{
    public function index()
    {
        $includeReactions = Reactions::isEnabled();

        $posts = Post::query()->with(array_filter(['user', 'images', $includeReactions ? 'reactions' : null]))->latest()->take(12)->get();
        $polls = Poll::query()->with(array_filter(['user', $includeReactions ? 'reactions' : null]))->latest()->take(8)->get();
        $postComments = PostComment::query()->latest()->with(['user', 'post'])->take(8)->get();
        $pollComments = PollComment::query()->latest()->with(array_filter(['user', 'poll', $includeReactions ? 'reactions' : null]))->take(8)->get();
        $events = Event::query()->where('is_public', true)->with(array_filter(['user', $includeReactions ? 'reactions' : null]))->latest()->take(8)->get();
        $suggestions = Suggestion::query()->with(array_filter(['user', $includeReactions ? 'reactions' : null]))->latest()->take(8)->get();
        $listings = Listing::query()->where('is_active', true)->with(array_filter(['user', $includeReactions ? 'reactions' : null]))->latest()->take(8)->get();

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
