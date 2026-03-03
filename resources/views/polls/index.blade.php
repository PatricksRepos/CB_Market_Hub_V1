<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Polls</h2>
            @auth
                <a class="rounded bg-gray-900 px-4 py-2 text-white hover:bg-gray-800" href="{{ route('polls.create') }}">New Poll</a>
            @endauth
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded border bg-green-50 p-3 text-green-800">{{ session('status') }}</div>
            @endif

            @forelse ($polls as $poll)
                <a href="{{ route('polls.show', $poll) }}" class="block bg-white shadow-sm rounded p-4 hover:bg-gray-50">
                    <div class="font-semibold">{{ $poll->question }}</div>
                    <div class="text-sm text-gray-500">{{ $poll->votes->count() }} votes</div>
                </a>
            @empty
                <div class="bg-white shadow-sm rounded p-6 text-gray-700">
                    <div class="font-semibold mb-1">No polls yet.</div>
                    @auth
                        <a class="underline text-gray-900" href="{{ route('polls.create') }}">Create one</a>
                    @else
                        <a class="underline text-gray-900" href="{{ route('login') }}">Log in</a> to create the first poll.
                    @endauth
                </div>
            @endforelse

            {{ $polls->links() }}
        </div>
    </div>
</x-app-layout>
