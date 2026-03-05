<?php

namespace Tests\Feature;

use App\Models\ChatMessage;
use App\Models\ChatReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_send_and_fetch_chat_messages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('chat.send'), ['body' => 'Breaker breaker'])
            ->assertRedirect();

        $message = ChatMessage::query()->first();

        $this->assertNotNull($message);
        $this->assertSame('Breaker breaker', $message->body);

        $this->getJson(route('chat.fetch', ['after_id' => 0]))
            ->assertOk()
            ->assertJsonPath('messages.0.body', 'Breaker breaker')
            ->assertJsonPath('messages.0.name', $user->name);
    }

    public function test_user_cannot_delete_another_users_chat_message(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $message = ChatMessage::query()->create([
            'user_id' => $owner->id,
            'body' => 'Owner message',
            'is_deleted' => false,
        ]);

        $this->actingAs($otherUser)
            ->delete(route('chat.delete', $message))
            ->assertForbidden();

        $message->refresh();

        $this->assertFalse($message->is_deleted);
        $this->assertSame('Owner message', $message->body);
    }

    public function test_authenticated_user_can_report_chat_message(): void
    {
        $owner = User::factory()->create();
        $reporter = User::factory()->create();

        $message = ChatMessage::query()->create([
            'user_id' => $owner->id,
            'body' => 'Inappropriate message',
            'is_deleted' => false,
        ]);

        $this->actingAs($reporter)
            ->post(route('chat.report', $message), ['reason' => 'harassment'])
            ->assertRedirect();

        $this->assertDatabaseHas('chat_reports', [
            'chat_message_id' => $message->id,
            'user_id' => $reporter->id,
            'reason' => 'harassment',
        ]);

        $this->assertSame(1, ChatReport::query()->count());
    }
}
