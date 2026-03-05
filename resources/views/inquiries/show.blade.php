<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Private Contact Thread</h2>
            <a class="text-sm text-gray-600 hover:text-gray-900" href="{{ route('contacts.index') }}">Back to contacts</a>
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
                    <a href="{{ route('listings.show', $inquiry->listing) }}" class="inline-flex mt-3 text-sm underline">View listing details</a>
                @endif
            </div>

            <div class="rounded border border-blue-200 bg-blue-50 p-3 text-sm text-blue-900">
                This thread is private between the buyer and seller for this listing. Community Chat is public and visible platform-wide.
            </div>

            <div id="contactThread" class="bg-white rounded-lg border p-4 space-y-3 max-h-[60vh] overflow-y-auto">
                @php $lastId = 0; @endphp
                @forelse($inquiry->messages as $message)
                    @php
                        $mine = $message->sender_user_id === auth()->id();
                        $lastId = max($lastId, $message->id);
                    @endphp
                    <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                        <div class="max-w-[80%] rounded-lg px-3 py-2 {{ $mine ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-800' }}">
                            <div class="text-xs opacity-75 mb-1">{{ $message->sender?->name ?? 'User' }} • {{ $message->created_at->diffForHumans() }}</div>
                            <div class="whitespace-pre-wrap text-sm">{{ $message->body }}</div>
                        </div>
                    </div>
                @empty
                    <div id="contactThreadEmpty" class="text-gray-600">No messages yet.</div>
                @endforelse
            </div>

            <form method="POST" action="{{ route('contacts.messages.store', $inquiry) }}" class="bg-white rounded-lg border p-4 space-y-3">
                @csrf
                <label for="body" class="block text-sm font-medium text-gray-700">Reply</label>
                <textarea id="body" name="body" rows="4" required maxlength="1500" class="w-full rounded-lg border-gray-300" placeholder="Type your private buyer/seller message...">{{ old('body') }}</textarea>
                @error('body')
                    <p class="text-sm text-red-600">{{ $message }}</p>
                @enderror
                <button class="rounded-lg bg-gray-900 text-white px-4 py-2 hover:bg-gray-800" type="submit">Send message</button>
            </form>
        </div>
    </div>

    <script>
        (function () {
            let lastId = {{ (int) $lastId }};
            const thread = document.getElementById('contactThread');
            const emptyState = document.getElementById('contactThreadEmpty');
            const currentUserId = {{ (int) auth()->id() }};

            function addMessage(message) {
                const mine = Number(message.sender_user_id) === currentUserId;

                const row = document.createElement('div');
                row.className = 'flex ' + (mine ? 'justify-end' : 'justify-start');

                const bubble = document.createElement('div');
                bubble.className = 'max-w-[80%] rounded-lg px-3 py-2 ' + (mine ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-800');

                const meta = document.createElement('div');
                meta.className = 'text-xs opacity-75 mb-1';
                meta.textContent = `${message.sender_name || 'User'} • ${message.created_at || ''}`;

                const body = document.createElement('div');
                body.className = 'whitespace-pre-wrap text-sm';
                body.textContent = message.body || '';

                bubble.appendChild(meta);
                bubble.appendChild(body);
                row.appendChild(bubble);
                thread.appendChild(row);
            }

            async function poll() {
                try {
                    const response = await fetch(`{{ route('contacts.messages.fetch', $inquiry) }}?after_id=${lastId}`, {
                        headers: { Accept: 'application/json' },
                    });

                    if (!response.ok) {
                        return;
                    }

                    const data = await response.json();
                    if (!data.messages || data.messages.length === 0) {
                        return;
                    }

                    if (emptyState) {
                        emptyState.remove();
                    }

                    for (const message of data.messages) {
                        lastId = Math.max(lastId, Number(message.id || 0));
                        addMessage(message);
                    }

                    thread.scrollTop = thread.scrollHeight;
                } catch (error) {
                    // ignore transient poll errors
                }
            }

            thread.scrollTop = thread.scrollHeight;
            setInterval(poll, 2500);
        })();
    </script>
</x-app-layout>
