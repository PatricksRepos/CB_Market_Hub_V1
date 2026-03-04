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

class ReactFeedEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_react_feed_endpoint_returns_mixed_feed_items(): void
    {
        $user = User::factory()->create(['name' => 'Alex Operator']);

        Post::query()->create([
            'user_id' => $user->id,
            'type' => 'discussion',
            'title' => 'Post sample',
            'body' => 'Post body sample',
            'created_at' => now()->subMinutes(5),
            'updated_at' => now()->subMinutes(5),
        ]);

        Poll::query()->create([
            'user_id' => $user->id,
            'question' => 'Poll sample',
            'is_active' => true,
            'starts_at' => now()->subHour(),
            'ends_at' => now()->addHour(),
            'results_visibility' => 'public',
            'created_at' => now()->subMinutes(4),
            'updated_at' => now()->subMinutes(4),
        ]);

        Event::query()->create([
            'user_id' => $user->id,
            'title' => 'Public event sample',
            'description' => 'Event body sample',
            'location' => 'Hall A',
            'starts_at' => now()->addDay(),
            'ends_at' => now()->addDays(2),
            'is_public' => true,
            'created_at' => now()->subMinutes(3),
            'updated_at' => now()->subMinutes(3),
        ]);

        Suggestion::query()->create([
            'user_id' => $user->id,
            'title' => 'Suggestion sample',
            'body' => 'Suggestion body sample',
            'status' => 'open',
            'is_anonymous' => false,
            'created_at' => now()->subMinutes(2),
            'updated_at' => now()->subMinutes(2),
        ]);

        Listing::query()->create([
            'user_id' => $user->id,
            'title' => 'Listing sample',
            'body' => 'Listing body sample',
            'price_cents' => 10000,
            'location' => 'Dallas',
            'category' => 'gear',
            'is_active' => true,
            'created_at' => now()->subMinute(),
            'updated_at' => now()->subMinute(),
        ]);

        $response = $this->getJson(route('react.feed'));

        $response->assertOk();
        $response->assertJsonStructure([
            'type',
            'q',
            'items' => [
                [
                    'type',
                    'title',
                    'excerpt',
                    'author',
                    'created_at',
                ],
            ],
        ]);

        $items = $response->json('items');

        $this->assertNotEmpty($items);
        $this->assertContains('post', array_column($items, 'type'));
        $this->assertContains('poll', array_column($items, 'type'));
        $this->assertContains('event', array_column($items, 'type'));
        $this->assertContains('suggestion', array_column($items, 'type'));
        $this->assertContains('listing', array_column($items, 'type'));
    }

    public function test_react_feed_endpoint_applies_type_and_search_filters(): void
    {
        $user = User::factory()->create();

        Post::query()->create([
            'user_id' => $user->id,
            'type' => 'discussion',
            'title' => 'Antenna tuning guide',
            'body' => 'SWR setup tips',
        ]);

        Post::query()->create([
            'user_id' => $user->id,
            'type' => 'discussion',
            'title' => 'General channel chat',
            'body' => 'No search keyword here',
        ]);

        Poll::query()->create([
            'user_id' => $user->id,
            'question' => 'Favorite CB channel?',
            'is_active' => true,
            'starts_at' => now(),
            'ends_at' => now()->addDay(),
            'results_visibility' => 'public',
        ]);

        $response = $this->getJson(route('react.feed', ['type' => 'post', 'q' => 'antenna']));

        $response->assertOk();
        $response->assertJsonPath('type', 'post');
        $response->assertJsonPath('q', 'antenna');

        $items = $response->json('items');

        $this->assertCount(1, $items);
        $this->assertSame('post', $items[0]['type']);
        $this->assertSame('Antenna tuning guide', $items[0]['title']);
    }
}
