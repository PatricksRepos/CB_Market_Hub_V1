<?php

namespace Tests\Feature;

use App\Models\Suggestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuggestionFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_vote_and_unvote_suggestion(): void
    {
        $owner = User::factory()->create();
        $voter = User::factory()->create();

        $suggestion = Suggestion::query()->create([
            'user_id' => $owner->id,
            'title' => 'Add night mode',
            'body' => 'A dark theme would help at night.',
            'status' => 'open',
            'is_anonymous' => false,
        ]);

        $this->actingAs($voter)
            ->post(route('suggestions.vote', $suggestion))
            ->assertRedirect();

        $this->assertDatabaseHas('suggestion_votes', [
            'suggestion_id' => $suggestion->id,
            'user_id' => $voter->id,
        ]);

        $this->actingAs($voter)
            ->delete(route('suggestions.unvote', $suggestion))
            ->assertRedirect();

        $this->assertDatabaseMissing('suggestion_votes', [
            'suggestion_id' => $suggestion->id,
            'user_id' => $voter->id,
        ]);
    }

    public function test_user_can_report_suggestion(): void
    {
        $owner = User::factory()->create();
        $reporter = User::factory()->create();

        $suggestion = Suggestion::query()->create([
            'user_id' => $owner->id,
            'title' => 'Bad suggestion',
            'body' => 'This should be reported.',
            'status' => 'open',
            'is_anonymous' => false,
        ]);

        $this->actingAs($reporter)
            ->post(route('suggestions.report', $suggestion), ['reason' => 'spam'])
            ->assertRedirect();

        $this->assertDatabaseHas('suggestion_reports', [
            'suggestion_id' => $suggestion->id,
            'user_id' => $reporter->id,
            'reason' => 'spam',
        ]);
    }

    public function test_non_admin_user_cannot_update_suggestion_status(): void
    {
        $owner = User::factory()->create();
        $nonAdmin = User::factory()->create(['is_admin' => false]);

        $suggestion = Suggestion::query()->create([
            'user_id' => $owner->id,
            'title' => 'Status update target',
            'status' => 'open',
            'is_anonymous' => false,
        ]);

        $this->actingAs($nonAdmin)
            ->patch(route('suggestions.status', $suggestion), ['status' => 'planned'])
            ->assertForbidden();

        $this->assertSame('open', $suggestion->fresh()->status);
    }

    public function test_admin_user_can_update_suggestion_status(): void
    {
        $owner = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);

        $suggestion = Suggestion::query()->create([
            'user_id' => $owner->id,
            'title' => 'Status update target',
            'status' => 'open',
            'is_anonymous' => false,
        ]);

        $this->actingAs($admin)
            ->patch(route('suggestions.status', $suggestion), ['status' => 'planned'])
            ->assertRedirect();

        $this->assertSame('planned', $suggestion->fresh()->status);
    }
}
