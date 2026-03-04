<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Marketplace</h2>
            <a class="rounded-lg bg-gray-900 text-white px-4 py-2.5 font-semibold hover:bg-gray-800" href="{{ route('listings.create') }}">New listing</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-3">
            <div class="bg-white rounded-lg border p-4 flex items-center justify-between">
                <div class="text-sm text-gray-600">Category</div>
                <form method="GET" action="{{ route('listings.index') }}">
                    <select name="category" class="rounded-lg border-gray-300" onchange="this.form.submit()">
                        <option value="buy_sell" @selected($category==='buy_sell')>Buy + Sell (default)</option>
                        <option value="all" @selected($category==='all')>All</option>
                        <option value="general" @selected($category==='general')>General</option>
                        <option value="buy" @selected($category==='buy')>Buy</option>
                        <option value="sell" @selected($category==='sell')>Sell</option>
                        <option value="trade" @selected($category==='trade')>Trade</option>
                        <option value="services" @selected($category==='services')>Services</option>
                    </select>
                </form>
            </div>

            @forelse($listings as $l)
                <a href="{{ route('listings.show',$l) }}" class="block bg-white rounded-lg border p-4 hover:bg-gray-50">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="font-semibold text-lg">{{ $l->title }}</div>
                            <div class="text-sm text-gray-500 mt-1">
                                {{ ucfirst($l->category) }}
                                @if($l->location) • {{ $l->location }} @endif
                                • {{ $l->created_at->diffForHumans() }}
                            </div>
                            <div class="text-xs text-gray-500 mt-2">by {{ $l->user?->name ?? 'User' }}</div>
                        </div>
                        <div class="text-sm font-semibold text-gray-900">
                            @if($l->price_cents !== null)
                                ${{ number_format($l->price_cents/100, 2) }}
                            @else
                                —
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="bg-white rounded-lg border p-6 text-gray-600">No direct listings yet.</div>
            @endforelse

            @if(isset($marketPosts) && $marketPosts->count())
                <div class="bg-white rounded-lg border p-4">
                    <div class="font-semibold">Posts pulled into marketplace</div>
                    <div class="text-xs text-gray-500 mt-1">Showing Buy/Sell post feed items so the marketplace is never empty.</div>
                </div>

                @foreach($marketPosts as $post)
                    <a href="{{ route('posts.show', $post) }}" class="block bg-white rounded-lg border p-4 hover:bg-gray-50">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <div class="font-semibold text-lg">{{ $post->title }}</div>
                                <div class="text-sm text-gray-500 mt-1">
                                    {{ strtoupper($post->marketplace_action ?? 'item') }}
                                    @if($post->location) • {{ $post->location }} @endif
                                    • {{ $post->created_at->diffForHumans() }}
                                </div>
                                <div class="text-xs text-gray-500 mt-2">
                                    by {{ $post->is_anonymous ? ($post->anonymous_name ?? 'Anon') : ($post->user?->name ?? 'User') }}
                                </div>
                            </div>
                            @if($post->images->first())
                                <img class="h-16 w-16 rounded border object-cover" src="{{ asset('storage/'.$post->images->first()->path) }}" alt="thumbnail">
                            @endif
                        </div>
                    </a>
                @endforeach
            @endif

            {{ $listings->links() }}
        </div>
    </div>
</x-app-layout>
