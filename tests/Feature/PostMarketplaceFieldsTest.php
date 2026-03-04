<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostMarketplaceFieldsTest extends TestCase
{
    use RefreshDatabase;

    public function test_edit_form_updates_marketplace_fields(): void
    {
        $user = User::factory()->create();
        $category = Category::query()->create(['name' => 'Marketplace']);

        $post = Post::query()->create([
            'user_id' => $user->id,
            'category_id' => $category->id,
            'type' => 'marketplace',
            'title' => 'Selling bike',
            'body' => 'Lightly used.',
            'marketplace_action' => 'sell',
            'price' => 100,
            'location' => 'Sydney',
            'condition' => 'Used',
        ]);

        $this->actingAs($user)->put(route('posts.update', $post), [
            'category_id' => $category->id,
            'type' => 'marketplace',
            'title' => 'Buying bike',
            'body' => 'Looking for a commuter bike.',
            'marketplace_action' => 'buy',
            'price' => 200,
            'location' => 'North Sydney',
            'condition' => 'Any',
        ])->assertRedirect(route('posts.show', $post));

        $post->refresh();

        $this->assertSame('buy', $post->marketplace_action);
        $this->assertSame('200.00', (string) $post->price);
        $this->assertSame('North Sydney', $post->location);
        $this->assertSame('Any', $post->condition);
    }
}
