<?php

namespace Tests\Feature;

use App\Models\ChatMessage;
use App\Models\Event;
use App\Models\Listing;
use App\Models\Poll;
use App\Models\Post;
use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReactSiteOverviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_react_app_page_is_available(): void
    {
        $response = $this->get(route('react.app'));

        $response->assertOk();
        $response->assertSee('React implementation across the site');
        $response->assertSee('react-site-app');
    }

    public function test_site_overview_endpoint_returns_counts_and_sections(): void
    {
        $user = User::factory()->create();

        Post::query()->create([
            'user_id' => $user->id,
            'type' => 'discussion',
            'title' => 'Post one',
            'body' => 'Body one',
        ]);

        Poll::query()->create([
            'user_id' => $user->id,
            'question' => 'Best radio?',
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

        Listing::query()->create([
            'user_id' => $user->id,
            'title' => 'Radio for sale',
            'body' => 'Like new',
            'price_cents' => 10000,
            'location' => 'Dallas',
            'category' => 'gear',
            'is_active' => true,
        ]);

        Suggestion::query()->create([
            'user_id' => $user->id,
            'title' => 'Add dark mode',
            'body' => 'Please add dark mode',
            'status' => 'open',
            'is_anonymous' => false,
        ]);

        ChatMessage::query()->create([
            'user_id' => $user->id,
            'body' => 'Hello React world',
        ]);

        $response = $this->getJson(route('react.site-overview'));

        $response->assertOk();
        $response->assertJsonPath('counts.posts', 1);
        $response->assertJsonPath('counts.polls', 1);
        $response->assertJsonPath('counts.marketplace', 1);
        $response->assertJsonPath('counts.events', 1);
        $response->assertJsonPath('counts.suggestions', 1);
        $response->assertJsonPath('counts.chat', 1);
        $response->assertJsonStructure([
            'counts' => ['feed', 'posts', 'polls', 'marketplace', 'events', 'suggestions', 'chat'],
            'sections' => ['posts', 'polls', 'marketplace', 'events', 'suggestions', 'chat'],
        ]);
    }
}
