<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MarketplacePostFeedVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_marketplace_posts_without_action_are_visible_in_default_feed(): void
    {
        $user = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Buy & Sell',
            'slug' => Str::slug('Buy & Sell'),
            'is_active' => true,
        ]);

        Post::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'marketplace',
            'title' => 'VHF Radio Antenna',
            'body' => 'Like new antenna and mount.',
            'marketplace_action' => null,
        ]);

        $response = $this->get(route('listings.index'));

        $response->assertOk();
        $response->assertSeeText('VHF Radio Antenna');
    }

    public function test_general_filter_only_shows_posts_without_marketplace_action(): void
    {
        $user = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Buy & Sell',
            'slug' => Str::slug('Buy & Sell'),
            'is_active' => true,
        ]);

        Post::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'marketplace',
            'title' => 'General CB item',
            'body' => 'Misc parts lot.',
            'marketplace_action' => null,
        ]);

        Post::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'marketplace',
            'title' => 'For Sale Radio',
            'body' => 'Clean radio for sale.',
            'marketplace_action' => 'sell',
        ]);

        $response = $this->get(route('listings.index', ['category' => 'general']));

        $response->assertOk();
        $response->assertSeeText('General CB item');
        $response->assertDontSeeText('For Sale Radio');
    }
}
