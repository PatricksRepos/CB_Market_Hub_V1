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
            </div>
        </div>
    </div>
</x-app-layout>
