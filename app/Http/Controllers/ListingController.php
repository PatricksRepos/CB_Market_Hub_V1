<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category', 'all');

        $q = Listing::query()->where('is_active', true)->with('user')->latest();
        if ($category !== 'all') $q->where('category', $category);

        $listings = $q->paginate(20)->withQueryString();
        return view('listings.index', compact('listings','category'));
    }

    public function show(Listing $listing)
    {
        $listing->load('user');
        return view('listings.show', compact('listing'));
    }

    public function create()
    {
        return view('listings.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required','string','max:120'],
            'body' => ['nullable','string','max:4000'],
            'price' => ['nullable','numeric','min:0','max:100000'],
            'location' => ['nullable','string','max:120'],
            'category' => ['required','in:general,buy,sell,trade,services'],
        ]);

        $priceCents = null;
        if ($data['price'] !== null) $priceCents = (int) round(((float)$data['price']) * 100);

        $listing = Listing::create([
            'user_id' => $request->user()->id,
            'title' => $data['title'],
            'body' => $data['body'] ?? null,
            'price_cents' => $priceCents,
            'location' => $data['location'] ?? null,
            'category' => $data['category'],
            'is_active' => true,
        ]);

        return redirect()->route('listings.show', $listing)->with('status','Listing posted.');
    }
}
