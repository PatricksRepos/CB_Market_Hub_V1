<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\ListingInquiry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class ListingInquiryController extends Controller
{
    public function index(Request $request)
    {
        if (! $this->inquiryTablesExist()) {
            return redirect()->route('listings.index')
                ->with('status', 'Sales inquiries are not ready yet. Run: php artisan migrate');
        }

        $user = $request->user();

        $inquiries = ListingInquiry::query()
            ->forUser($user->id)
            ->with(['listing', 'buyer', 'seller'])
            ->with(['messages' => fn ($query) => $query->latest()->limit(1)->with('sender')])
            ->orderByDesc('last_message_at')
            ->orderByDesc('updated_at')
            ->paginate(20);

        return view('inquiries.index', compact('inquiries'));
    }

    public function start(Request $request, Listing $listing)
    {
        if (! $this->inquiryTablesExist()) {
            return back()->with('status', 'Sales inquiries are not ready yet. Run: php artisan migrate');
        }

        $user = $request->user();

        abort_if($listing->user_id === $user->id, 422, 'You cannot inquire on your own listing.');

        $inquiry = ListingInquiry::firstOrCreate(
            [
                'listing_id' => $listing->id,
                'buyer_user_id' => $user->id,
            ],
            [
                'seller_user_id' => $listing->user_id,
            ]
        );

        if (! $inquiry->messages()->exists()) {
            $inquiry->messages()->create([
                'sender_user_id' => $user->id,
                'body' => sprintf("Hi! I'm interested in your listing: %s", $listing->title),
            ]);

            $inquiry->forceFill(['last_message_at' => now()])->save();
        }

        return redirect()->route('inquiries.show', $inquiry);
    }

    public function show(Request $request, ListingInquiry $inquiry)
    {
        if (! $this->inquiryTablesExist()) {
            return redirect()->route('listings.index')
                ->with('status', 'Sales inquiries are not ready yet. Run: php artisan migrate');
        }

        abort_unless($inquiry->involvesUser($request->user()->id), 403);

        $inquiry->load([
            'listing.user',
            'buyer',
            'seller',
            'messages.sender',
        ]);

        return view('inquiries.show', compact('inquiry'));
    }

    public function storeMessage(Request $request, ListingInquiry $inquiry)
    {
        if (! $this->inquiryTablesExist()) {
            return back()->with('status', 'Sales inquiries are not ready yet. Run: php artisan migrate');
        }

        abort_unless($inquiry->involvesUser($request->user()->id), 403);

        $data = $request->validate([
            'body' => ['required', 'string', 'max:1500'],
        ]);

        $inquiry->messages()->create([
            'sender_user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        $inquiry->forceFill(['last_message_at' => now()])->save();

        return redirect()->route('inquiries.show', $inquiry)->with('status', 'Message sent.');
    }

    private function inquiryTablesExist(): bool
    {
        return Schema::hasTable('listing_inquiries')
            && Schema::hasTable('listing_inquiry_messages');
    }
}
