<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Marketplace</h2>
            @auth
                <a class="rounded-lg bg-gray-900 text-white px-4 py-2.5 font-semibold hover:bg-gray-800" href="{{ route('listings.create') }}">New listing</a>
            @endauth
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-3">
            <div class="bg-white rounded-lg border p-4 flex items-center justify-between">
                <div class="text-sm text-gray-600">Category</div>
                <form method="GET" action="{{ route('listings.index') }}">
                    <select name="category" class="rounded-lg border-gray-300" onchange="this.form.submit()">
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
                            <div class="text-xs text-gray-500 mt-2">
                                by {{ $l->user?->name ?? 'User' }}
                            </div>
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
                <div class="bg-white rounded-lg border p-6 text-gray-600">No listings yet.</div>
            @endforelse

            {{ $listings->links() }}
        </div>
    </div>
</x-app-layout>
