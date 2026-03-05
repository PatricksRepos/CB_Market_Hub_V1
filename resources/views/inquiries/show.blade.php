<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Inquiry Thread</h2>
            <a class="text-sm text-gray-600 hover:text-gray-900" href="{{ route('inquiries.index') }}">Back to inquiries</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded border bg-green-50 p-3 text-green-800">{{ session('status') }}</div>
            @endif

            <div class="bg-white rounded-lg border p-4">
                <div class="font-semibold text-lg">{{ $inquiry->listing?->title ?? 'Listing removed' }}</div>
                <div class="text-sm text-gray-600 mt-1">
                    Buyer: {{ $inquiry->buyer?->name ?? 'User' }} • Seller: {{ $inquiry->seller?->name ?? 'User' }}
                </div>
                @if($inquiry->listing)
                    <a href="{{ route('listings.show', $inquiry->listing) }}" class="inline-flex mt-3 text-sm underline">View listing</a>
                @endif
            </div>

            <div class="bg-white rounded-lg border p-4 space-y-3 max-h-[60vh] overflow-y-auto">
                @forelse($inquiry->messages as $message)
                    @php $mine = $message->sender_user_id === auth()->id(); @endphp
                    <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%] rounded-lg px-3 py-2 {{ $mine ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-800' }}">
                            <div class="text-xs opacity-75 mb-1">{{ $message->sender?->name ?? 'User' }} • {{ $message->created_at->diffForHumans() }}</div>
                            <div class="whitespace-pre-wrap text-sm">{{ $message->body }}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-gray-600">No messages yet.</div>
                @endforelse
            </div>

            <form method="POST" action="{{ route('inquiries.messages.store', $inquiry) }}" class="bg-white rounded-lg border p-4 space-y-3">
                @csrf
                <label for="body" class="block text-sm font-medium text-gray-700">Reply</label>
                <textarea id="body" name="body" rows="4" required maxlength="1500" class="w-full rounded-lg border-gray-300" placeholder="Type your message..."></textarea>
                @error('body')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                <button class="rounded-lg bg-gray-900 text-white px-4 py-2 hover:bg-gray-800" type="submit">Send message</button>
            </form>
        </div>
    </div>
</x-app-layout>
