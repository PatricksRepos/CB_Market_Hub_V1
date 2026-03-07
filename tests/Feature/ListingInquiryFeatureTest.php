<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\ListingInquiry;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListingInquiryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_can_start_listing_inquiry_and_seed_message(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $listing = Listing::query()->create([
            'user_id' => $seller->id,
            'title' => 'Galaxy DX-959',
            'body' => 'Clean radio, great condition.',
            'price_cents' => 22000,
            'category' => 'sell',
            'is_active' => true,
        ]);

        $this->actingAs($buyer)
            ->post(route('contacts.start', $listing))
            ->assertRedirect();

        $inquiry = ListingInquiry::query()->first();

        $this->assertNotNull($inquiry);
        $this->assertSame($listing->id, $inquiry->listing_id);
        $this->assertSame($buyer->id, $inquiry->buyer_user_id);
        $this->assertSame($seller->id, $inquiry->seller_user_id);
        $this->assertCount(1, $inquiry->messages);
        $this->assertNotNull($inquiry->buyer_last_read_at);
        $this->assertNull($inquiry->seller_last_read_at);
    }


    public function test_buyer_can_message_seller_directly_from_marketplace_post(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $post = Post::query()->create([
            'user_id' => $seller->id,
            'type' => 'marketplace',
            'title' => 'Selling mobile radio',
            'body' => 'Works great.',
            'marketplace_action' => 'sell',
            'is_anonymous' => false,
        ]);

        $this->actingAs($buyer)
            ->post(route('contacts.start.post', $post))
            ->assertRedirect();

        $inquiry = ListingInquiry::query()->first();

        $this->assertNotNull($inquiry);
        $this->assertSame($buyer->id, $inquiry->buyer_user_id);
        $this->assertSame($seller->id, $inquiry->seller_user_id);
        $this->assertCount(1, $inquiry->messages);
    }

    public function test_buyer_can_include_initial_message_when_starting_inquiry(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $listing = Listing::query()->create([
            'user_id' => $seller->id,
            'title' => 'SWR Meter',
            'category' => 'sell',
            'is_active' => true,
        ]);

        $this->actingAs($buyer)
            ->post(route('contacts.start', $listing), [
                'body' => 'Hi, can you share the age and condition?',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('listing_inquiry_messages', [
            'sender_user_id' => $buyer->id,
            'body' => 'Hi, can you share the age and condition?',
        ]);
    }

    public function test_listing_owner_cannot_inquire_on_own_listing(): void
    {
        $seller = User::factory()->create();

        $listing = Listing::query()->create([
            'user_id' => $seller->id,
            'title' => 'President McKinley',
            'category' => 'sell',
            'is_active' => true,
        ]);

        $this->actingAs($seller)
            ->from(route('listings.show', $listing))
            ->post(route('contacts.start', $listing))
            ->assertRedirect(route('listings.show', $listing));

        $this->assertSame(
            'You cannot start a private contact thread on your own listing.',
            session('status')
        );

        $this->assertSame(0, ListingInquiry::query()->count());
    }

    public function test_starting_contact_with_existing_thread_adds_message_when_provided(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $listing = Listing::query()->create([
            'user_id' => $seller->id,
            'title' => 'Desk mic',
            'category' => 'sell',
            'is_active' => true,
        ]);

        $inquiry = ListingInquiry::query()->create([
            'listing_id' => $listing->id,
            'buyer_user_id' => $buyer->id,
            'seller_user_id' => $seller->id,
            'last_message_at' => now(),
        ]);

        $inquiry->messages()->create([
            'sender_user_id' => $buyer->id,
            'body' => 'Initial message.',
        ]);

        $this->actingAs($buyer)
            ->post(route('contacts.start', $listing), ['body' => 'Following up with an offer.'])
            ->assertRedirect(route('contacts.show', $inquiry));

        $this->assertDatabaseHas('listing_inquiry_messages', [
            'listing_inquiry_id' => $inquiry->id,
            'sender_user_id' => $buyer->id,
            'body' => 'Following up with an offer.',
        ]);
    }


    public function test_only_inquiry_participants_can_view_and_message_thread(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();
        $outsider = User::factory()->create();

        $listing = Listing::query()->create([
            'user_id' => $seller->id,
            'title' => 'Antenna Mount',
            'category' => 'sell',
            'is_active' => true,
        ]);

        $inquiry = ListingInquiry::query()->create([
            'listing_id' => $listing->id,
            'buyer_user_id' => $buyer->id,
            'seller_user_id' => $seller->id,
            'last_message_at' => now(),
        ]);

        $this->actingAs($outsider)
            ->get(route('contacts.show', $inquiry))
            ->assertForbidden();

        $this->actingAs($outsider)
            ->post(route('contacts.messages.store', $inquiry), ['body' => 'Can I jump in?'])
            ->assertForbidden();

        $this->actingAs($seller)
            ->post(route('contacts.messages.store', $inquiry), ['body' => 'Still available.'])
            ->assertRedirect(route('contacts.show', $inquiry));

        $this->assertDatabaseHas('listing_inquiry_messages', [
            'listing_inquiry_id' => $inquiry->id,
            'sender_user_id' => $seller->id,
            'body' => 'Still available.',
        ]);
    }


    public function test_seller_gets_notification_when_buyer_starts_contact_thread(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $listing = Listing::query()->create([
            'user_id' => $seller->id,
            'title' => 'Base station mic',
            'category' => 'sell',
            'is_active' => true,
        ]);

        $this->actingAs($buyer)
            ->post(route('contacts.start', $listing))
            ->assertRedirect();

        $seller->refresh();

        $this->assertSame(1, $seller->notifications()->count());
    }

    public function test_other_participant_gets_notification_when_message_is_sent(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $listing = Listing::query()->create([
            'user_id' => $seller->id,
            'title' => 'Mobile setup',
            'category' => 'sell',
            'is_active' => true,
        ]);

        $inquiry = ListingInquiry::query()->create([
            'listing_id' => $listing->id,
            'buyer_user_id' => $buyer->id,
            'seller_user_id' => $seller->id,
            'last_message_at' => now(),
        ]);

        $this->actingAs($buyer)
            ->post(route('contacts.messages.store', $inquiry), ['body' => 'Still interested, is this available?'])
            ->assertRedirect(route('contacts.show', $inquiry));

        $seller->refresh();
        $buyer->refresh();

        $this->assertSame(1, $seller->notifications()->count());
        $this->assertSame(0, $buyer->notifications()->count());
    }


    public function test_seller_fetch_endpoint_receives_buyers_new_message(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $listing = Listing::query()->create([
            'user_id' => $seller->id,
            'title' => 'Power mic',
            'category' => 'sell',
            'is_active' => true,
        ]);

        $inquiry = ListingInquiry::query()->create([
            'listing_id' => $listing->id,
            'buyer_user_id' => $buyer->id,
            'seller_user_id' => $seller->id,
            'last_message_at' => now(),
        ]);

        $this->actingAs($buyer)
            ->post(route('contacts.messages.store', $inquiry), ['body' => 'Can you ship this week?'])
            ->assertRedirect(route('contacts.show', $inquiry));

        $this->actingAs($seller)
            ->getJson(route('contacts.messages.fetch', $inquiry, false).'?after_id=0')
            ->assertOk()
            ->assertJsonPath('messages.0.body', 'Can you ship this week?')
            ->assertJsonPath('messages.0.sender_user_id', $buyer->id);
    }

    public function test_opening_contact_thread_marks_messages_as_read_for_viewer(): void
    {
        $seller = User::factory()->create();
        $buyer = User::factory()->create();

        $listing = Listing::query()->create([
            'user_id' => $seller->id,
            'title' => 'Linear amplifier',
            'category' => 'sell',
            'is_active' => true,
        ]);

        $inquiry = ListingInquiry::query()->create([
            'listing_id' => $listing->id,
            'buyer_user_id' => $buyer->id,
            'seller_user_id' => $seller->id,
            'last_message_at' => now(),
        ]);

        $inquiry->messages()->create([
            'sender_user_id' => $buyer->id,
            'body' => 'Is this still available?',
        ]);

        $this->actingAs($seller)
            ->get(route('contacts.show', $inquiry))
            ->assertOk();

        $this->assertNotNull($inquiry->fresh()->seller_last_read_at);
    }

}
