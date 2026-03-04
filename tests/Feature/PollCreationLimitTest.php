<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PollCreationLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_multiple_polls_without_rate_limit_block(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $this->actingAs($user);

        for ($i = 1; $i <= 6; $i++) {
            $response = $this->post(route('polls.store'), [
                'question' => 'Poll question '.$i,
                'options' => ['Option A', 'Option B'],
                'results_visibility' => 'always',
            ]);

            $response->assertRedirect();
        }

        $this->assertDatabaseCount('polls', 6);
    }
}
