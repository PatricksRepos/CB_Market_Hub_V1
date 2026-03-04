<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class FeedFiltersTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_can_filter_by_type(): void
    {
        $user = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'General',
            'slug' => Str::slug('General'),
            'is_active' => true,
        ]);

        Post::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'discussion',
            'title' => 'Feed filter target',
            'body' => 'Visible for post filter.',
        ]);

        $response = $this->get(route('feed.index', ['type' => 'post']));

        $response->assertOk();
        $response->assertSeeText('Feed filter target');
    }

    public function test_feed_can_search_post_content(): void
    {
        $user = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'General',
            'slug' => Str::slug('General'),
            'is_active' => true,
        ]);

        Post::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'discussion',
            'title' => 'SWR meter calibration guide',
            'body' => 'Detailed walkthrough',
        ]);

        Post::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'discussion',
            'title' => 'Antenna grounding tips',
            'body' => 'General setup ideas',
        ]);

        $response = $this->get(route('feed.index', ['q' => 'calibration']));

        $response->assertOk();
        $response->assertSeeText('SWR meter calibration guide');
        $response->assertDontSeeText('Antenna grounding tips');
    }
}
