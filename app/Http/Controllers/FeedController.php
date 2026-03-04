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
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FeedController extends Controller
{
    public function index(Request $request)
    {
        $includeReactions = Reactions::isEnabled();

        $search = trim((string) $request->string('q', ''));
        $selectedType = (string) $request->string('type', 'all');
        $allowedTypes = ['all', 'post', 'poll', 'event', 'suggestion', 'listing', 'post_comment', 'poll_comment'];

        if (! in_array($selectedType, $allowedTypes, true)) {
            $selectedType = 'all';
        }

        $posts = Post::query()
            ->with(array_filter(['user', 'images', $includeReactions ? 'reactions' : null]))
            ->latest()
            ->take(12)
            ->get();

        $polls = Poll::query()
            ->with(array_filter(['user', $includeReactions ? 'reactions' : null]))
            ->latest()
            ->take(8)
            ->get();

        $postComments = PostComment::query()->latest()->with(['user', 'post'])->take(8)->get();
        $pollComments = PollComment::query()->latest()->with(array_filter(['user', 'poll', $includeReactions ? 'reactions' : null]))->take(8)->get();

        $events = Event::query()
            ->where('is_public', true)
            ->with(array_filter(['user', $includeReactions ? 'reactions' : null]))
            ->latest()
            ->take(8)
            ->get();

        $suggestions = Suggestion::query()
            ->with(array_filter(['user', $includeReactions ? 'reactions' : null]))
            ->latest()
            ->take(8)
            ->get();

        $listings = Listing::query()
            ->where('is_active', true)
            ->with(array_filter(['user', $includeReactions ? 'reactions' : null]))
            ->latest()
            ->take(8)
            ->get();

        $items = collect()
            ->concat($posts->map(fn ($p) => ['type' => 'post', 'at' => $p->created_at, 'data' => $p]))
            ->concat($polls->map(fn ($p) => ['type' => 'poll', 'at' => $p->created_at, 'data' => $p]))
            ->concat($events->map(fn ($e) => ['type' => 'event', 'at' => $e->created_at, 'data' => $e]))
            ->concat($suggestions->map(fn ($s) => ['type' => 'suggestion', 'at' => $s->created_at, 'data' => $s]))
            ->concat($listings->map(fn ($l) => ['type' => 'listing', 'at' => $l->created_at, 'data' => $l]))
            ->concat($postComments->map(fn ($c) => ['type' => 'post_comment', 'at' => $c->created_at, 'data' => $c]))
            ->concat($pollComments->map(fn ($c) => ['type' => 'poll_comment', 'at' => $c->created_at, 'data' => $c]))
            ->when($selectedType !== 'all', fn ($collection) => $collection->where('type', $selectedType))
            ->when($search !== '', fn ($collection) => $collection->filter(fn (array $item) => $this->matchesSearch($item, $search)))
            ->sortByDesc('at')
            ->take(40)
            ->values();

        return view('feed.index', [
            'items' => $items,
            'selectedType' => $selectedType,
            'search' => $search,
            'availableTypes' => [
                'all' => 'All activity',
                'post' => 'Posts',
                'poll' => 'Polls',
                'event' => 'Events',
                'suggestion' => 'Suggestions',
                'listing' => 'Listings',
                'post_comment' => 'Post comments',
                'poll_comment' => 'Poll comments',
            ],
        ]);
    }

    private function matchesSearch(array $item, string $search): bool
    {
        $needle = Str::lower($search);

        return match ($item['type']) {
            'post' => Str::contains(Str::lower(($item['data']->title ?? '').' '.($item['data']->body ?? '')), $needle),
            'poll' => Str::contains(Str::lower($item['data']->question ?? ''), $needle),
            'event' => Str::contains(Str::lower(($item['data']->title ?? '').' '.($item['data']->description ?? '')), $needle),
            'suggestion' => Str::contains(Str::lower(($item['data']->title ?? '').' '.($item['data']->body ?? '')), $needle),
            'listing' => Str::contains(Str::lower(($item['data']->title ?? '').' '.($item['data']->body ?? '').' '.($item['data']->location ?? '')), $needle),
            'post_comment', 'poll_comment' => Str::contains(Str::lower($item['data']->body ?? ''), $needle),
            default => false,
        };
    }
}
