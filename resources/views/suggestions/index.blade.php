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
                <a href="{{ route('suggestions.show',$s) }}" class="block bg-white rounded-lg border p-4 hover:bg-gray-50">
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
            @empty
                <div class="bg-white rounded-lg border p-6 text-gray-600">No suggestions yet.</div>
            @endforelse

            {{ $suggestions->links() }}
        </div>
    </div>
</x-app-layout>
