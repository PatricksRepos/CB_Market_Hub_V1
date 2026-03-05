<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Private Contacts</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-3">
            <div class="inline-flex rounded-lg border bg-white p-1">
                <a href="{{ route('chat.index') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-100">Community Chat</a>
                <a href="{{ route('contacts.index') }}" class="rounded-md bg-gray-900 px-3 py-2 text-sm font-medium text-white">Private Buyer/Seller Messages</a>
            </div>

            <div class="rounded border border-blue-200 bg-blue-50 p-3 text-sm text-blue-900">
                Contacts are private threads between buyer and seller for a specific listing. For platform-wide discussion, use Community Chat.
            </div>

            @forelse($inquiries as $inquiry)
                @php
                    $latest = $inquiry->messages->first();
                    $isBuyer = auth()->id() === $inquiry->buyer_user_id;
                    $counterparty = $isBuyer ? $inquiry->seller : $inquiry->buyer;
                    $unreadCount = $inquiry->unreadMessagesCountFor((int) auth()->id());
                @endphp
                <a href="{{ route('contacts.show', $inquiry) }}" class="block bg-white rounded-lg border p-4 hover:bg-gray-50">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="font-semibold flex items-center gap-2">
                                <span>{{ $inquiry->listing?->title ?? 'Listing removed' }}</span>
                                @if($unreadCount > 0)
                                    <span class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">{{ $unreadCount }} new</span>
                                @endif
                            </div>
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
