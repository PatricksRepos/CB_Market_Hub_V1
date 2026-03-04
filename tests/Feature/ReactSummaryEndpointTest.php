<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Listing;
use App\Models\Poll;
use App\Models\Post;
use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReactSummaryEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_react_summary_endpoint_returns_expected_counts(): void
    {
        $user = User::factory()->create();

        Post::query()->create([
            'user_id' => $user->id,
            'type' => 'discussion',
            'title' => 'Post one',
            'body' => 'Body one',
        ]);
        Post::query()->create([
            'user_id' => $user->id,
            'type' => 'discussion',
            'title' => 'Post two',
            'body' => 'Body two',
        ]);
        Poll::query()->create([
            'user_id' => $user->id,
            'question' => 'Best CB setup?',
            'is_active' => true,
            'starts_at' => now(),
            'ends_at' => now()->addDay(),
            'results_visibility' => 'public',
        ]);
        Event::query()->create([
            'user_id' => $user->id,
            'title' => 'Public meetup',
            'description' => 'Open event',
            'location' => 'Hall',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
            'is_public' => true,
        ]);
        Event::query()->create([
            'user_id' => $user->id,
            'title' => 'Private meetup',
            'description' => 'Private event',
            'location' => 'HQ',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
            'is_public' => false,
        ]);
        Suggestion::query()->create([
            'user_id' => $user->id,
            'title' => 'Add dark mode',
            'body' => 'Please add dark mode',
            'status' => 'open',
            'is_anonymous' => false,
        ]);
        Listing::query()->create([
            'user_id' => $user->id,
            'title' => 'Radio for sale',
            'body' => 'Like new',
            'price_cents' => 10000,
            'location' => 'Dallas',
            'category' => 'gear',
            'is_active' => true,
        ]);
        Listing::query()->create([
            'user_id' => $user->id,
            'title' => 'Old radio',
            'body' => 'For parts',
            'price_cents' => 2000,
            'location' => 'Austin',
            'category' => 'gear',
            'is_active' => false,
        ]);

        $response = $this->getJson(route('react.summary'));

        $response->assertOk()->assertExactJson([
            'posts' => 2,
            'polls' => 1,
            'events' => 1,
            'suggestions' => 1,
            'listings' => 1,
        ]);
    }
}
