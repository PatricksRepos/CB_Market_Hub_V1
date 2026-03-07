<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Listing;
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

    public function test_marketplace_feed_excludes_non_marketplace_post_types(): void
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
            'type' => 'business',
            'title' => 'Antenna Tuning Service',
            'body' => 'Professional tune up.',
            'marketplace_action' => null,
        ]);

        $response = $this->get(route('listings.index'));

        $response->assertOk();
        $response->assertDontSeeText('Antenna Tuning Service');
    }


    public function test_marketplace_post_card_shows_private_message_button_when_seller_has_listing(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $category = Category::query()->create([
            'name' => 'Buy & Sell',
            'slug' => Str::slug('Buy & Sell'),
            'is_active' => true,
        ]);

        Listing::query()->create([
            'user_id' => $seller->id,
            'title' => 'Seller listing',
            'category' => 'sell',
            'is_active' => true,
        ]);

        Post::query()->create([
            'user_id' => $seller->id,
            'category_id' => $category->id,
            'type' => 'marketplace',
            'title' => 'Selling antenna from post',
            'body' => 'Message me for details.',
            'marketplace_action' => 'sell',
            'is_anonymous' => false,
        ]);

        $response = $this->actingAs($buyer)->get(route('listings.index'));

        $response->assertOk();
        $response->assertSeeText('Message Seller');
    }

    public function test_invalid_marketplace_category_falls_back_to_default_filter(): void
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
            'title' => 'Buying SWR Meter',
            'body' => 'Need one in working order.',
            'marketplace_action' => 'buy',
        ]);

        Post::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'marketplace',
            'title' => 'General Coax Cable',
            'body' => 'Unused length of coax cable.',
            'marketplace_action' => null,
        ]);

        Post::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'marketplace',
            'title' => 'Trade Mic for Mount',
            'body' => 'Looking to trade.',
            'marketplace_action' => 'trade',
        ]);

        $response = $this->get(route('listings.index', ['category' => 'invalid-value']));

        $response->assertOk();
        $response->assertSeeText('Buying SWR Meter');
        $response->assertSeeText('General Coax Cable');
        $response->assertDontSeeText('Trade Mic for Mount');
    }
}
