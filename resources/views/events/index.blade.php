<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Events</h2>
            <a class="rounded-lg bg-gray-900 text-white px-4 py-2.5 font-semibold hover:bg-gray-800" href="{{ route('events.create') }}">New event</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-3">
            @if (session('status'))
                <div class="rounded border bg-green-50 p-3 text-green-800">{{ session('status') }}</div>
            @endif

            @forelse($events as $e)
                <div class="bg-white rounded-lg border p-4">
                    <a href="{{ route('events.show',$e) }}" class="block hover:bg-gray-50 rounded">
                        <div class="font-semibold text-lg">{{ $e->title }}</div>
                        <div class="text-sm text-gray-500 mt-1">
                            {{ $e->starts_at->toDayDateTimeString() }}
                            @if($e->location) • {{ $e->location }} @endif
                        </div>
                        <div class="text-xs text-gray-500 mt-2">Hosted by {{ $e->user?->name ?? 'User' }}</div>
                    </a>

                    @auth
                        @if(auth()->id() === $e->user_id || auth()->user()->isAdmin())
                            <div class="mt-3 flex items-center gap-2">
                                <a class="rounded-lg border px-3 py-1.5 hover:bg-gray-50" href="{{ route('events.edit', $e) }}">Edit</a>
                                <form method="POST" action="{{ route('events.destroy', $e) }}" onsubmit="return confirm('Delete this event?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg bg-red-600 text-white px-3 py-1.5 hover:bg-red-700" type="submit">Delete</button>
                                </form>
                            </div>
                        @endif
                    @endauth
                </div>
            @empty
                <div class="bg-white rounded-lg border p-6 text-gray-600">No events yet.</div>
            @endforelse

            {{ $events->links() }}
        </div>
    </div>
</x-app-layout>
