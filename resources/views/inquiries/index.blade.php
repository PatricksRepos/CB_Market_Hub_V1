<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Private Contacts</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-3">
            @if (session('status'))
                <div class="rounded border bg-green-50 p-3 text-green-800">{{ session('status') }}</div>
            @endif


            <div class="rounded border border-blue-200 bg-blue-50 p-3 text-sm text-blue-900">
                Contacts are private threads between buyer and seller for a specific listing. For platform-wide discussion, use Community Chat.
            </div>

            @forelse($inquiries as $inquiry)
                @php
                    $latest = $inquiry->messages->first();
                    $isBuyer = auth()->id() === $inquiry->buyer_user_id;
                    $counterparty = $isBuyer ? $inquiry->seller : $inquiry->buyer;
                @endphp
                <a href="{{ route('contacts.show', $inquiry) }}" class="block bg-white rounded-lg border p-4 hover:bg-gray-50">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="font-semibold">{{ $inquiry->listing?->title ?? 'Listing removed' }}</div>
                            <div class="text-sm text-gray-600 mt-1">
                                With {{ $counterparty?->name ?? 'User' }}
                                @if($inquiry->listing)
                                    • <span class="text-gray-500">${{ number_format(($inquiry->listing->price_cents ?? 0) / 100, 2) }}</span>
                                @endif
                            </div>
                            @if($latest)
                                <div class="text-sm text-gray-700 mt-2">
                                    <span class="font-medium">{{ $latest->sender?->name ?? 'User' }}:</span>
                                    {{ $latest->body }}
                                </div>
                            @endif
                        </div>
                        <div class="text-xs text-gray-500">{{ optional($inquiry->last_message_at ?? $inquiry->updated_at)->diffForHumans() }}</div>
                    </div>
                </a>
            @empty
                <div class="bg-white rounded-lg border p-6 text-gray-600">
                    No contacts yet. Open any marketplace listing and click <strong>Contact Seller (Private)</strong>.
                </div>
            @endforelse

            {{ $inquiries->links() }}
        </div>
    </div>
</x-app-layout>
