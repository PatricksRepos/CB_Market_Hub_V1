<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModerationAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_user_cannot_access_moderation_page(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_admin' => false,
        ]);

        $this->actingAs($user)
            ->get(route('moderation.index'))
            ->assertForbidden();
    }

    public function test_admin_user_can_access_moderation_page(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
            'is_admin' => true,
        ]);

        $this->actingAs($user)
            ->get(route('moderation.index'))
            ->assertOk()
            ->assertSeeText('Moderation Queue');
    }
}
