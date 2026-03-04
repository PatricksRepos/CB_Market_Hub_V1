<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Support\Reactions;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category', 'buy_sell');

        $q = Listing::query()->where('is_active', true)->with('user')->latest();
        if ($category === 'buy_sell') {
            $q->whereIn('category', ['buy', 'sell']);
        } elseif ($category !== 'all') {
            $q->where('category', $category);
        }

        $postQuery = Post::query()
            ->where('is_hidden', false)
            ->whereNotNull('marketplace_action')
            ->with(['user', 'images'])
            ->latest();

        if ($category === 'buy_sell') {
            $postQuery->whereIn('marketplace_action', ['buy', 'sell']);
        } elseif (in_array($category, ['buy', 'sell', 'trade'], true)) {
            $postQuery->where('marketplace_action', $category);
        }

        $listings = $q->paginate(20)->withQueryString();
        $marketPosts = $postQuery->take(20)->get();

        return view('listings.index', compact('listings', 'marketPosts', 'category'));
    }

    public function show(Listing $listing)
    {
        $listing->load(array_filter(['user', Reactions::isEnabled() ? 'reactions' : null]));
        return view('listings.show', compact('listing'));
    }

    public function create()
    {
        return view('listings.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $listing = Listing::create([
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'price_cents' => $this->toPriceCents($data['price'] ?? null),
            'location' => $data['location'] ?? null,
            'category' => $data['category'],
            'is_active' => true,
        ]);

        return redirect()->route('listings.show', $listing)->with('status','Listing posted.');
    }

    public function edit(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id || $request->user()->isAdmin(), 403);
        return view('listings.edit', compact('listing'));
    }

    public function update(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        $data = $this->validateData($request);

        $listing->update([
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'price_cents' => $this->toPriceCents($data['price'] ?? null),
            'location' => $data['location'] ?? null,
            'category' => $data['category'],
        ]);

        return redirect()->route('listings.show', $listing)->with('status', 'Listing updated.');
    }

    public function destroy(Request $request, Listing $listing)
    {
        abort_unless($listing->user_id === $request->user()->id || $request->user()->isAdmin(), 403);

        $listing->delete();

        return redirect()->route('listings.index')->with('status', 'Listing deleted.');
    }

    private function validateData(Request $request): array
    {
        return $request->validate([
            'title' => ['required','string','max:120'],
            'body' => ['nullable','string','max:4000'],
            'price' => ['nullable','numeric','min:0','max:100000'],
            'location' => ['nullable','string','max:120'],
            'category' => ['required','in:general,buy,sell,trade,services'],
        ]);
    }

    private function toPriceCents($price): ?int
    {
        if ($price === null || $price === '') return null;

        return (int) round(((float)$price) * 100);
    }
}
