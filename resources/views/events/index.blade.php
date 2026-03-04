<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Events</h2>
            @auth
                <a href="{{ route('events.create') }}" class="px-3 py-2 rounded bg-gray-900 text-white text-sm hover:bg-gray-800">Create Event</a>
                <a class="rounded-lg bg-gray-900 text-white px-4 py-2.5 font-semibold hover:bg-gray-800" href="{{ route('events.create') }}">New event</a>
            @endauth
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-3">
            @foreach($events as $e)
                <a href="{{ route('events.show',$e) }}" class="block bg-white rounded-lg border p-4 hover:bg-gray-50">
                    <div class="font-semibold text-lg">{{ $e->title }}</div>
                    <div class="text-sm text-gray-500 mt-1">
                        {{ $e->starts_at->toDayDateTimeString() }}
                        @if($e->location) • {{ $e->location }} @endif
                    </div>
                </a>
            @endforeach

            {{ $events->links() }}
        </div>
    </div>
</x-app-layout>
