<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Listing</h2>
            <a class="text-sm text-gray-600 hover:text-gray-900" href="{{ route('listings.index') }}">Back</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded border bg-green-50 p-3 text-green-800">{{ session('status') }}</div>
            @endif

            <div class="bg-white rounded-lg border p-6">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-semibold text-2xl">{{ $listing->title }}</div>
                        <div class="text-sm text-gray-500 mt-1">
                            {{ ucfirst($listing->category) }}
                            @if($listing->location) • {{ $listing->location }} @endif
                            • {{ $listing->created_at->diffForHumans() }}
                        </div>
                        <div class="text-sm mt-2">
                            Posted by:
                            <a class="underline" href="{{ route('profiles.show', $listing->user) }}">{{ $listing->user?->name ?? 'User' }}</a>
                        </div>
                    </div>

                    <div class="text-lg font-bold text-gray-900">
                        @if($listing->price_cents !== null)
                            ${{ number_format($listing->price_cents/100, 2) }}
                        @else
                            —
                        @endif
                    </div>
                </div>

                @if($listing->body)
                    <div class="mt-5 text-gray-800 whitespace-pre-wrap">{{ $listing->body }}</div>
                @else
                    <div class="mt-5 text-gray-600">No details provided.</div>
                @endif

                <x-reaction-bar :model="$listing" type="listing" />

                <div class="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-3 text-sm text-blue-900">
                    <p class="font-medium">How messaging works</p>
                    <p class="mt-1">Use <strong>Contact Seller</strong> for private buyer/seller deal details. Community Chat is public to the full platform.</p>
                </div>

                @auth
                    @if($listing->user && auth()->id() !== $listing->user_id)
                        <div class="mt-4 space-y-3">
                            <form method="POST" action="{{ route('contacts.start', $listing) }}" class="space-y-2 rounded-lg border p-3">
                                @csrf
                                <label for="contact-body" class="block text-sm font-medium text-gray-700">Message to seller</label>
                                <textarea id="contact-body" name="body" rows="3" maxlength="1500" class="w-full rounded-lg border-gray-300" placeholder="Hi! Is this still available?">{{ old('body') }}</textarea>
                                @error('body')
                                    <p class="text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <button type="submit" class="inline-flex items-center rounded-lg border px-3 py-2 text-sm hover:bg-gray-50">Message Seller</button>
                            </form>
                            <a href="{{ route('chat.index') }}" class="inline-flex items-center rounded-lg border px-3 py-2 text-sm hover:bg-gray-50">Open Community Chat (Public)</a>
                        </div>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="mt-4 inline-flex items-center rounded-lg border px-3 py-2 text-sm hover:bg-gray-50">Log in to contact seller</a>
                @endauth

                @auth
                    @if(auth()->id() === $listing->user_id || auth()->user()->isAdmin())
                        <div class="mt-5 flex items-center gap-2">
                            <a class="rounded-lg border px-4 py-2 hover:bg-gray-50" href="{{ route('listings.edit', $listing) }}">Edit</a>
                            <form method="POST" action="{{ route('listings.destroy', $listing) }}" onsubmit="return confirm('Delete this listing?');">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-lg bg-red-600 text-white px-4 py-2 hover:bg-red-700" type="submit">Delete</button>
                            </form>
                        </div>
                    @endif
                @endauth
            </div>
        </div>
    </div>
</x-app-layout>
