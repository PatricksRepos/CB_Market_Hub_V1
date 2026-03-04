<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\SimpleNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationReadTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_mark_single_notification_read(): void
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $user->notify(new SimpleNotification('First', route('feed.index'), 'First message'));
        $user->notify(new SimpleNotification('Second', route('feed.index'), 'Second message'));

        $targetNotification = $user->notifications()->first();

        $this->actingAs($user)
            ->post(route('notifications.read-one', $targetNotification->id))
            ->assertRedirect();

        $targetNotification->refresh();

        $this->assertNotNull($targetNotification->read_at);
        $this->assertSame(1, $user->fresh()->unreadNotifications()->count());
    }
}
