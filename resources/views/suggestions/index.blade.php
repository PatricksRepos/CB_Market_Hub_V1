<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Suggestions</h2>
            @auth
                <a class="rounded-lg bg-gray-900 text-white px-4 py-2.5 font-semibold hover:bg-gray-800" href="{{ route('suggestions.create') }}">New suggestion</a>
            @endauth
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-3">
            @if (session('status'))
                <div class="rounded border bg-green-50 p-3 text-green-800">{{ session('status') }}</div>
            @endif

            <div class="bg-white rounded-lg border p-4 flex items-center justify-between">
                <div class="text-sm text-gray-600">Status</div>
                <form method="GET" action="{{ route('suggestions.index') }}">
                    <select name="status" class="rounded-lg border-gray-300" onchange="this.form.submit()">
                        <option value="all" @selected($status==='all')>All</option>
                        <option value="open" @selected($status==='open')>Open</option>
                        <option value="planned" @selected($status==='planned')>Planned</option>
                        <option value="in_progress" @selected($status==='in_progress')>In progress</option>
                        <option value="done" @selected($status==='done')>Done</option>
                        <option value="rejected" @selected($status==='rejected')>Rejected</option>
                    </select>
                </form>
            </div>

            @forelse($suggestions as $s)
                <div class="bg-white rounded-lg border p-4">
                    <a href="{{ route('suggestions.show',$s) }}" class="block hover:bg-gray-50 rounded">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="font-semibold text-lg">{{ $s->title }}</div>
                                <div class="text-sm text-gray-500 mt-1">
                                    Status: <span class="font-semibold">{{ str_replace('_',' ', $s->status) }}</span>
                                    • {{ $s->created_at->diffForHumans() }}
                                </div>
                                <div class="text-xs text-gray-500 mt-2">
                                    by {{ $s->is_anonymous ? 'Anonymous' : ($s->user?->name ?? 'User') }}
                                </div>
                            </div>
                            <div class="text-sm font-semibold text-gray-900">
                                ▲ {{ $s->votes_count }}
                            </div>
                        </div>
                    </a>

                    @auth
                        @if(auth()->id() === $s->user_id || auth()->user()->isAdmin())
                            <div class="mt-3 flex items-center gap-2">
                                <a href="{{ route('suggestions.edit', $s) }}" class="rounded-lg border px-3 py-1.5 hover:bg-gray-50">Edit</a>
                                <form method="POST" action="{{ route('suggestions.destroy', $s) }}" onsubmit="return confirm('Delete this suggestion?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="rounded-lg bg-red-600 text-white px-3 py-1.5 hover:bg-red-700" type="submit">Delete</button>
                                </form>
                            </div>
                        @endif
                    @endauth
                </div>
            @empty
                <div class="bg-white rounded-lg border p-6 text-gray-600">No suggestions yet.</div>
            @endforelse

            {{ $suggestions->links() }}
        </div>
    </div>
</x-app-layout>
