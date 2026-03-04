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
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class FeedController extends Controller
{
    private const FEED_TYPES = ['all', 'post', 'poll', 'event', 'suggestion', 'listing', 'post_comment', 'poll_comment'];

    public function index(Request $request)
    {
        $search = trim((string) $request->string('q', ''));
        $selectedType = $this->normalizeFeedType((string) $request->string('type', 'all'));

        $items = $this->collectFeedItems(Reactions::isEnabled())
            ->when($selectedType !== 'all', fn (Collection $collection) => $collection->where('type', $selectedType))
            ->when($search !== '', fn (Collection $collection) => $collection->filter(fn (array $item) => $this->matchesSearch($item, $search)))
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

    public function reactFeed(Request $request)
    {
        $search = trim((string) $request->string('q', ''));
        $selectedType = $this->normalizeFeedType((string) $request->string('type', 'all'));

        $items = $this->collectFeedItems(false)
            ->when($selectedType !== 'all', fn (Collection $collection) => $collection->where('type', $selectedType))
            ->when($search !== '', fn (Collection $collection) => $collection->filter(fn (array $item) => $this->matchesSearch($item, $search)))
            ->sortByDesc('at')
            ->take(20)
            ->map(fn (array $item) => $this->mapReactFeedItem($item))
            ->values();

        return response()->json([
            'type' => $selectedType,
            'q' => $search,
            'items' => $items,
        ]);
    }

    public function reactSummary()
    {
        return response()->json([
            'posts' => Post::query()->count(),
            'polls' => Poll::query()->count(),
            'events' => Event::query()->where('is_public', true)->count(),
            'suggestions' => Suggestion::query()->count(),
            'listings' => Listing::query()->where('is_active', true)->count(),
        ]);
    }

    private function collectFeedItems(bool $includeReactions): Collection
    {
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

        $postComments = PostComment::query()->latest()->with(['user', 'post'])->take(8)->get();
        $pollComments = PollComment::query()->latest()->with(array_filter(['user', 'poll', $includeReactions ? 'reactions' : null]))->take(8)->get();

        return collect()
            ->concat($posts->map(fn (Post $post) => ['type' => 'post', 'at' => $post->created_at, 'data' => $post]))
            ->concat($polls->map(fn (Poll $poll) => ['type' => 'poll', 'at' => $poll->created_at, 'data' => $poll]))
            ->concat($events->map(fn (Event $event) => ['type' => 'event', 'at' => $event->created_at, 'data' => $event]))
            ->concat($suggestions->map(fn (Suggestion $suggestion) => ['type' => 'suggestion', 'at' => $suggestion->created_at, 'data' => $suggestion]))
            ->concat($listings->map(fn (Listing $listing) => ['type' => 'listing', 'at' => $listing->created_at, 'data' => $listing]))
            ->concat($postComments->map(fn (PostComment $comment) => ['type' => 'post_comment', 'at' => $comment->created_at, 'data' => $comment]))
            ->concat($pollComments->map(fn (PollComment $comment) => ['type' => 'poll_comment', 'at' => $comment->created_at, 'data' => $comment]));
    }

    private function mapReactFeedItem(array $item): array
    {
        $type = (string) $item['type'];
        $data = $item['data'];

        return match ($type) {
            'post' => $this->reactCard($type, (string) ($data->title ?? ''), (string) ($data->body ?? ''), (string) optional($data->user)->name, $data->created_at),
            'poll' => $this->reactCard($type, (string) ($data->question ?? ''), 'Community poll', (string) optional($data->user)->name, $data->created_at),
            'event' => $this->reactCard($type, (string) ($data->title ?? ''), (string) ($data->description ?? ''), (string) optional($data->user)->name, $data->created_at),
            'suggestion' => $this->reactCard($type, (string) ($data->title ?? ''), (string) ($data->body ?? ''), (string) optional($data->user)->name, $data->created_at),
            'listing' => $this->reactCard($type, (string) ($data->title ?? ''), (string) ($data->body ?? ''), (string) optional($data->user)->name, $data->created_at),
            'post_comment' => $this->reactCard($type, 'Comment on post', (string) ($data->body ?? ''), (string) optional($data->user)->name, $data->created_at),
            'poll_comment' => $this->reactCard($type, 'Comment on poll', (string) ($data->body ?? ''), (string) optional($data->user)->name, $data->created_at),
            default => $this->reactCard($type, 'Feed item', '', '', null),
        };
    }

    private function reactCard(string $type, string $title, string $body, string $author, $createdAt): array
    {
        return [
            'type' => $type,
            'title' => $title,
            'excerpt' => Str::limit($body, 120),
            'author' => $author,
            'created_at' => optional($createdAt)->toIso8601String(),
        ];
    }

    private function normalizeFeedType(string $selectedType): string
    {
        return in_array($selectedType, self::FEED_TYPES, true) ? $selectedType : 'all';
    }

    public function reactFeed(Request $request)
    {
        $search = trim((string) $request->string('q', ''));
        $selectedType = (string) $request->string('type', 'all');
        $allowedTypes = ['all', 'post', 'poll', 'event', 'suggestion', 'listing'];

        if (! in_array($selectedType, $allowedTypes, true)) {
            $selectedType = 'all';
        }

        $posts = Post::query()
            ->with('user:id,name')
            ->latest()
            ->take(12)
            ->get()
            ->map(fn (Post $post) => [
                'type' => 'post',
                'title' => (string) $post->title,
                'excerpt' => Str::limit((string) $post->body, 120),
                'author' => (string) optional($post->user)->name,
                'created_at' => optional($post->created_at)->toIso8601String(),
                'search_text' => Str::lower((string) $post->title.' '.(string) $post->body),
            ]);

        $polls = Poll::query()
            ->with('user:id,name')
            ->latest()
            ->take(8)
            ->get()
            ->map(fn (Poll $poll) => [
                'type' => 'poll',
                'title' => (string) $poll->question,
                'excerpt' => 'Community poll',
                'author' => (string) optional($poll->user)->name,
                'created_at' => optional($poll->created_at)->toIso8601String(),
                'search_text' => Str::lower((string) $poll->question),
            ]);

        $events = Event::query()
            ->where('is_public', true)
            ->with('user:id,name')
            ->latest()
            ->take(8)
            ->get()
            ->map(fn (Event $event) => [
                'type' => 'event',
                'title' => (string) $event->title,
                'excerpt' => Str::limit((string) $event->description, 120),
                'author' => (string) optional($event->user)->name,
                'created_at' => optional($event->created_at)->toIso8601String(),
                'search_text' => Str::lower((string) $event->title.' '.(string) $event->description),
            ]);

        $suggestions = Suggestion::query()
            ->with('user:id,name')
            ->latest()
            ->take(8)
            ->get()
            ->map(fn (Suggestion $suggestion) => [
                'type' => 'suggestion',
                'title' => (string) $suggestion->title,
                'excerpt' => Str::limit((string) $suggestion->body, 120),
                'author' => (string) optional($suggestion->user)->name,
                'created_at' => optional($suggestion->created_at)->toIso8601String(),
                'search_text' => Str::lower((string) $suggestion->title.' '.(string) $suggestion->body),
            ]);

        $listings = Listing::query()
            ->where('is_active', true)
            ->with('user:id,name')
            ->latest()
            ->take(8)
            ->get()
            ->map(fn (Listing $listing) => [
                'type' => 'listing',
                'title' => (string) $listing->title,
                'excerpt' => Str::limit((string) $listing->body, 120),
                'author' => (string) optional($listing->user)->name,
                'created_at' => optional($listing->created_at)->toIso8601String(),
                'search_text' => Str::lower((string) $listing->title.' '.(string) $listing->body.' '.(string) $listing->location),
            ]);

        $items = collect()
            ->concat($posts)
            ->concat($polls)
            ->concat($events)
            ->concat($suggestions)
            ->concat($listings)
            ->when($selectedType !== 'all', fn ($collection) => $collection->where('type', $selectedType))
            ->when($search !== '', fn ($collection) => $collection->filter(fn (array $item) => Str::contains($item['search_text'], Str::lower($search))))
            ->sortByDesc('created_at')
            ->take(20)
            ->map(fn (array $item) => collect($item)->except('search_text')->all())
            ->values();

        return response()->json([
            'type' => $selectedType,
            'q' => $search,
            'items' => $items,
        ]);
    }

    public function reactSummary()
    {
        return response()->json([
            'posts' => Post::query()->count(),
            'polls' => Poll::query()->count(),
            'events' => Event::query()->where('is_public', true)->count(),
            'suggestions' => Suggestion::query()->count(),
            'listings' => Listing::query()->where('is_active', true)->count(),
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
