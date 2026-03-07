<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GamificationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_creating_a_post_awards_points_and_badge(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('posts.store'), [
                'type' => 'marketplace',
                'title' => 'Bike for sale',
                'body' => 'Great condition.',
                'marketplace_action' => 'sell',
            ])
            ->assertRedirect();

        $user->refresh();

        $this->assertSame(10, $user->points_total);
        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $user->id,
            'action' => 'post.created',
            'subject_key' => 'post:1',
            'points' => 10,
        ]);
        $this->assertDatabaseHas('badge_user', [
            'user_id' => $user->id,
        ]);
    }

    public function test_reactions_award_points_to_content_owner_only(): void
    {
        $owner = User::factory()->create();
        $reactor = User::factory()->create();

        $post = Post::create([
            'user_id' => $owner->id,
            'type' => 'marketplace',
            'title' => 'Post for reactions',
            'body' => 'Body',
            'marketplace_action' => 'sell',
        ]);

        $this->actingAs($reactor)
            ->post(route('reactions.store'), [
                'type' => 'post',
                'id' => $post->id,
                'emoji' => '👍',
            ])
            ->assertSessionHas('status', 'Reaction saved.');

        $owner->refresh();
        $reactor->refresh();

        $this->assertSame(1, $owner->points_total);
        $this->assertSame(0, $reactor->points_total);

        $this->actingAs($owner)
            ->post(route('reactions.store'), [
                'type' => 'post',
                'id' => $post->id,
                'emoji' => '🔥',
            ])
            ->assertSessionHas('status', 'Reaction saved.');

        $owner->refresh();
        $this->assertSame(1, $owner->points_total);
    }
}
