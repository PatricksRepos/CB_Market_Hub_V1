<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Listing;
use App\Models\Poll;
use App\Models\Post;
use App\Models\PostComment;
use App\Models\PollComment;
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

        PostComment::query()->create([
            'post_id' => Post::query()->firstOrFail()->id,
            'user_id' => $user->id,
            'body' => 'Post comment sample',
            'created_at' => now()->subSeconds(30),
            'updated_at' => now()->subSeconds(30),
        ]);

        PollComment::query()->create([
            'poll_id' => Poll::query()->firstOrFail()->id,
            'user_id' => $user->id,
            'body' => 'Poll comment sample',
            'created_at' => now()->subSeconds(20),
            'updated_at' => now()->subSeconds(20),
        ]);

        $response = $this->getJson(route('react.feed'));

        $response->assertOk();
        $response->assertJsonStructure([
            'type',
            'q',
            'pagination' => ['page', 'per_page', 'total', 'has_more'],
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
        $this->assertContains('post_comment', array_column($items, 'type'));
        $this->assertContains('poll_comment', array_column($items, 'type'));
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



    public function test_react_feed_endpoint_returns_pagination_metadata_and_pages(): void
    {
        $user = User::factory()->create();

        for ($i = 1; $i <= 8; $i++) {
            Post::query()->create([
                'user_id' => $user->id,
                'type' => 'discussion',
                'title' => "Post {$i}",
                'body' => 'Body',
                'created_at' => now()->subMinutes(30 - $i),
                'updated_at' => now()->subMinutes(30 - $i),
            ]);

            Poll::query()->create([
                'user_id' => $user->id,
                'question' => "Poll {$i}",
                'is_active' => true,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addDay(),
                'results_visibility' => 'public',
                'created_at' => now()->subMinutes(60 - $i),
                'updated_at' => now()->subMinutes(60 - $i),
            ]);
        }

        $pageOne = $this->getJson(route('react.feed', ['page' => 1, 'per_page' => 5]));
        $pageOne->assertOk();
        $this->assertCount(5, $pageOne->json('items'));
        $pageOne->assertJsonPath('pagination.page', 1);
        $pageOne->assertJsonPath('pagination.per_page', 5);
        $pageOne->assertJsonPath('pagination.has_more', true);

        $pageTwo = $this->getJson(route('react.feed', ['page' => 2, 'per_page' => 5]));
        $pageTwo->assertOk();
        $this->assertCount(5, $pageTwo->json('items'));
        $pageTwo->assertJsonPath('pagination.page', 2);
    }

    public function test_react_feed_endpoint_can_filter_comment_types(): void
    {
        $user = User::factory()->create();

        $post = Post::query()->create([
            'user_id' => $user->id,
            'type' => 'discussion',
            'title' => 'Parent post',
            'body' => 'Parent body',
        ]);

        PostComment::query()->create([
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => 'Need help with antenna grounding',
        ]);

        $response = $this->getJson(route('react.feed', ['type' => 'post_comment', 'q' => 'grounding']));

        $response->assertOk();
        $response->assertJsonPath('type', 'post_comment');

        $items = $response->json('items');

        $this->assertCount(1, $items);
        $this->assertSame('post_comment', $items[0]['type']);
    }

}
