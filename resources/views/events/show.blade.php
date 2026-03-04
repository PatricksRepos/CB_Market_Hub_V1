<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $event->title }}</h2>
            <a class="text-sm text-gray-600 hover:text-gray-900" href="{{ route('events.index') }}">Back</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded border bg-green-50 p-3 text-green-800">{{ session('status') }}</div>
            @endif

            <div class="bg-white rounded-lg border p-6">
                <div class="text-sm text-gray-500">
                    Starts: {{ $event->starts_at->toDayDateTimeString() }}
                    @if($event->ends_at) • Ends: {{ $event->ends_at->toDayDateTimeString() }} @endif
                    @if($event->location) • {{ $event->location }} @endif
                </div>

                <div class="text-sm mt-2">
                    Hosted by:
                    <a class="underline" href="{{ route('profiles.show', $event->user) }}">{{ $event->user?->name ?? 'User' }}</a>
                </div>

                @if($event->description)
                    <div class="mt-4 whitespace-pre-wrap text-gray-800">{{ $event->description }}</div>
                @endif

                @auth
                    @if(auth()->id() === $event->user_id || auth()->user()->isAdmin())
                        <div class="mt-5 flex items-center gap-2">
                            <a class="rounded-lg border px-4 py-2 hover:bg-gray-50" href="{{ route('events.edit', $event) }}">Edit</a>
                            <form method="POST" action="{{ route('events.destroy', $event) }}" onsubmit="return confirm('Delete this event?');">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-lg bg-red-600 text-white px-4 py-2 hover:bg-red-700" type="submit">Delete</button>
                            </form>
                        </div>
                    @endif
                @endauth
            </div>

            @auth
                <div class="bg-white rounded-lg border p-6">
                    <div class="font-semibold mb-3">RSVP</div>
                    <form method="POST" action="{{ route('events.rsvp', $event) }}" class="flex flex-col sm:flex-row gap-2">
                        @csrf
                        <select name="status" class="rounded-lg border-gray-300">
                            <option value="yes" @selected($my && $my->status==='yes')>Yes</option>
                            <option value="maybe" @selected($my && $my->status==='maybe')>Maybe</option>
                            <option value="no" @selected($my && $my->status==='no')>No</option>
                        </select>
                        <button class="rounded-lg bg-gray-900 text-white px-4 py-2 hover:bg-gray-800" type="submit">Save</button>
                    </form>
                </div>
            @else
                <div class="bg-white rounded-lg border p-6 text-gray-600">
                    <a class="underline" href="{{ route('login') }}">Log in</a> to RSVP.
                </div>
            @endauth
        </div>
    </div>
</x-app-layout>
