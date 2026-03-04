<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReactionFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_add_and_toggle_post_reaction(): void
    {
        $user = User::factory()->create();
        $post = Post::query()->create([
            'user_id' => $user->id,
            'type' => 'marketplace',
            'title' => 'Sample post',
            'body' => 'Post body',
        ]);

        $this->actingAs($user)->post(route('reactions.store'), [
            'type' => 'post',
            'id' => $post->id,
            'emoji' => '👍',
        ])->assertRedirect();

        $this->assertDatabaseHas('reactions', [
            'user_id' => $user->id,
            'reactable_type' => $post->getMorphClass(),
            'reactable_id' => $post->id,
            'emoji' => '👍',
        ]);

        $this->actingAs($user)->post(route('reactions.store'), [
            'type' => 'post',
            'id' => $post->id,
            'emoji' => '👍',
        ])->assertRedirect();

        $this->assertDatabaseMissing('reactions', [
            'user_id' => $user->id,
            'reactable_type' => $post->getMorphClass(),
            'reactable_id' => $post->id,
        ]);
    }

    public function test_user_can_switch_reaction_emoji_without_duplicate_records(): void
    {
        $user = User::factory()->create();
        $post = Post::query()->create([
            'user_id' => $user->id,
            'type' => 'marketplace',
            'title' => 'Another post',
            'body' => 'Post body',
        ]);

        Reaction::query()->create([
            'user_id' => $user->id,
            'emoji' => '👍',
            'reactable_type' => $post->getMorphClass(),
            'reactable_id' => $post->id,
        ]);

        $this->actingAs($user)->post(route('reactions.store'), [
            'type' => 'post',
            'id' => $post->id,
            'emoji' => '🎉',
        ])->assertRedirect();

        $this->assertDatabaseHas('reactions', [
            'user_id' => $user->id,
            'reactable_type' => $post->getMorphClass(),
            'reactable_id' => $post->id,
            'emoji' => '🎉',
        ]);

        $this->assertSame(1, Reaction::query()->count());
    }
}
