<?php

namespace Tests\Feature;

use App\Models\Listing;
use App\Models\ListingInquiry;
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
            ->post(route('contacts.start', $listing))
            ->assertStatus(422);

        $this->assertSame(0, ListingInquiry::query()->count());
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
}
