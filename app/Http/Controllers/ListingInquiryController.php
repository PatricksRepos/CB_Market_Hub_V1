<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\ListingInquiry;
use App\Models\Post;
use App\Notifications\SimpleNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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

        if ((int) $listing->user_id === (int) $user->id) {
            return back()->with('status', 'You cannot start a private contact thread on your own listing.');
        }

        if (! $listing->is_active || ! $listing->user_id) {
            return back()->with('status', 'That listing is unavailable for private contact right now.');
        }

        $inquiry = ListingInquiry::firstOrCreate(
            [
                'listing_id' => $listing->id,
                'buyer_user_id' => $user->id,
            ],
            [
                'seller_user_id' => $listing->user_id,
            ]
        );

        $data = $request->validate([
            'body' => ['nullable', 'string', 'max:1500'],
        ]);

        $initialMessage = trim((string) ($data['body'] ?? ''));

        if (! $inquiry->messages()->exists()) {
            $seedMessage = $inquiry->messages()->create([
                'sender_user_id' => $user->id,
                'body' => $initialMessage !== ''
                    ? $initialMessage
                    : sprintf("Hi! I'm interested in your listing: %s", $listing->title),
            ]);

            $inquiry->forceFill([
                'last_message_at' => now(),
                'buyer_last_read_at' => now(),
            ])->save();

            $listing->user?->notify(new SimpleNotification(
                'New private contact on your listing',
                route('contacts.show', $inquiry),
                $user->name.' sent: '.Str::limit($seedMessage->body, 80)
            ));
        } elseif ($initialMessage !== '') {
            $message = $inquiry->messages()->create([
                'sender_user_id' => $user->id,
                'body' => $initialMessage,
            ]);

            $inquiry->forceFill([
                'last_message_at' => now(),
                'buyer_last_read_at' => now(),
            ])->save();

            if ((int) $inquiry->seller_user_id !== (int) $user->id) {
                $listing->user?->notify(new SimpleNotification(
                    'New private contact message',
                    route('contacts.show', $inquiry),
                    $user->name.' sent: '.Str::limit($message->body, 80)
                ));
            }
        }

        return redirect()
            ->route('contacts.show', $inquiry)
            ->with('status', 'Private contact thread ready. Keep marketplace deal details here.');
    }


    public function startFromPost(Request $request, Post $post)
    {
        if (! $this->inquiryTablesExist()) {
            return back()->with('status', 'Sales inquiries are not ready yet. Run: php artisan migrate');
        }

        if ($post->is_hidden || $post->type !== 'marketplace' || ! $post->user_id || $post->is_anonymous) {
            return back()->with('status', 'This seller cannot be contacted privately from this post.');
        }

        $listing = Listing::query()->firstOrCreate(
            [
                'user_id' => $post->user_id,
                'title' => $post->title,
                'category' => $post->marketplace_action ?: 'general',
            ],
            [
                'body' => $post->body,
                'price_cents' => $post->price !== null ? (int) round(((float) $post->price) * 100) : null,
                'location' => $post->location,
                'is_active' => true,
            ]
        );

        return $this->start($request, $listing);
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
            'messages' => fn ($query) => $query->with('sender')->oldest(),
        ]);

        $this->markInquiryReadForUser($inquiry, (int) $request->user()->id);

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

        $message = $inquiry->messages()->create([
            'sender_user_id' => $request->user()->id,
            'body' => $data['body'],
        ]);

        $updates = ['last_message_at' => now()];

        if ((int) $request->user()->id === (int) $inquiry->buyer_user_id) {
            $updates['buyer_last_read_at'] = now();
        } else {
            $updates['seller_last_read_at'] = now();
        }

        $inquiry->forceFill($updates)->save();

        $recipient = (int) $request->user()->id === (int) $inquiry->buyer_user_id
            ? $inquiry->seller
            : $inquiry->buyer;

        if ($recipient && (int) $recipient->id !== (int) $request->user()->id) {
            $recipient->notify(new SimpleNotification(
                'New private contact message',
                route('contacts.show', $inquiry),
                $request->user()->name.' sent: '.Str::limit($message->body, 80)
            ));
        }

        return redirect()->route('contacts.show', $inquiry)->with('status', 'Message sent.');
    }


    public function fetchMessages(Request $request, ListingInquiry $inquiry)
    {
        if (! $this->inquiryTablesExist()) {
            return response()->json(['messages' => []])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
        }

        abort_unless($inquiry->involvesUser($request->user()->id), 403);

        $afterId = (int) $request->query('after_id', 0);

        $messages = $inquiry->messages()
            ->where('id', '>', $afterId)
            ->with('sender')
            ->oldest()
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender_user_id' => $message->sender_user_id,
                    'sender_name' => $message->sender?->name ?? 'User',
                    'body' => $message->body,
                    'created_at' => $message->created_at?->diffForHumans(),
                ];
            });

        $this->markInquiryReadForUser($inquiry, (int) $request->user()->id);

        return response()->json(['messages' => $messages])->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    private function markInquiryReadForUser(ListingInquiry $inquiry, int $userId): void
    {
        if ((int) $inquiry->buyer_user_id === $userId) {
            $inquiry->forceFill(['buyer_last_read_at' => now()])->save();

            return;
        }

        if ((int) $inquiry->seller_user_id === $userId) {
            $inquiry->forceFill(['seller_last_read_at' => now()])->save();
        }
    }

    private function inquiryTablesExist(): bool
    {
        return Schema::hasTable('listing_inquiries')
            && Schema::hasTable('listing_inquiry_messages');
    }
}
