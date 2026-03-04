<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EventImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_event_with_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('events.store'), [
            'title' => 'Community Cleanup',
            'description' => 'Join us for a neighborhood cleanup event.',
            'location' => 'City Park',
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDays(2)->format('Y-m-d H:i:s'),
            'image' => UploadedFile::fake()->image('event-banner.jpg'),
        ]);

        $event = Event::query()->first();

        $response->assertRedirect(route('events.show', $event));
        $this->assertNotNull($event->image_path);
        Storage::disk('public')->assertExists($event->image_path);
    }

    public function test_owner_can_remove_event_image(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $event = Event::query()->create([
            'user_id' => $user->id,
            'title' => 'Town Hall',
            'starts_at' => now()->addDay(),
            'is_public' => true,
            'image_path' => UploadedFile::fake()->image('event.jpg')->store('event-images', 'public'),
        ]);

        $this->actingAs($user)->put(route('events.update', $event), [
            'title' => $event->title,
            'description' => $event->description,
            'location' => $event->location,
            'starts_at' => $event->starts_at->format('Y-m-d H:i:s'),
            'ends_at' => null,
            'remove_image' => '1',
        ])->assertRedirect(route('events.show', $event));

        $event->refresh();

        $this->assertNull($event->image_path);
        $this->assertSame([], Storage::disk('public')->files('event-images'));
    }
}
