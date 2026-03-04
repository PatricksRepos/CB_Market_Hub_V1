<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Polls</h2>
                <div class="text-sm text-gray-500">Browse active polls, ended polls, and vote.</div>
            </div>
            @auth
                <a class="rounded-lg bg-gray-900 px-4 py-2.5 text-white font-semibold hover:bg-gray-800"
                   href="{{ route('polls.create') }}">New Poll</a>
            @endauth
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded border bg-green-50 p-3 text-green-800">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm rounded-lg p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex gap-2">
                    @php
                        $tab = $status ?? 'active';
                        $mk = fn($s) => route('polls.index', ['status'=>$s,'sort'=>$sort ?? 'new']);
                    @endphp
                    <a class="px-3 py-2 rounded-lg border {{ $tab==='active' ? 'bg-gray-900 text-white border-gray-900' : 'hover:bg-gray-50' }}"
                       href="{{ $mk('active') }}">Active</a>
                    <a class="px-3 py-2 rounded-lg border {{ $tab==='ended' ? 'bg-gray-900 text-white border-gray-900' : 'hover:bg-gray-50' }}"
                       href="{{ $mk('ended') }}">Ended</a>
                    <a class="px-3 py-2 rounded-lg border {{ $tab==='all' ? 'bg-gray-900 text-white border-gray-900' : 'hover:bg-gray-50' }}"
                       href="{{ $mk('all') }}">All</a>
                </div>

                <form method="GET" action="{{ route('polls.index') }}" class="flex items-center gap-2">
                    <input type="hidden" name="status" value="{{ $tab }}">
                    <label class="text-sm text-gray-600">Sort</label>
                    <select name="sort" class="rounded-lg border-gray-300" onchange="this.form.submit()">
                        <option value="new" @selected(($sort ?? 'new')==='new')>Newest</option>
                        <option value="votes" @selected(($sort ?? '')==='votes')>Most votes</option>
                        <option value="ending" @selected(($sort ?? '')==='ending')>Ending soon</option>
                    </select>
                </form>
            </div>

            @forelse ($polls as $poll)
                @php
                    $ended = $poll->ends_at ? now()->gte($poll->ends_at) : false;
                    $endsIn = $poll->ends_at ? $poll->ends_at->diffForHumans(now(), ['parts'=>2, 'short'=>true]) : null;
                @endphp

                <a href="{{ route('polls.show', $poll) }}" class="block bg-white shadow-sm rounded-lg p-4 hover:bg-gray-50">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="font-semibold text-lg">{{ $poll->question }}</div>
                            <div class="text-sm text-gray-500 mt-1">
                                {{ $poll->votes->count() }} votes
                                @if ($poll->ends_at)
                                    • {{ $ended ? 'Ended' : 'Ends in '.$endsIn }}
                                @else
                                    • No end time
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-col items-end gap-2">
                            <span class="text-xs px-2 py-1 rounded-full {{ $ended ? 'bg-gray-200 text-gray-700' : 'bg-green-100 text-green-800' }}">
                                {{ $ended ? 'Ended' : 'Active' }}
                            </span>
                            <span class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700">
                                @if ($poll->results_visibility==='always') Results: Public
                                @elseif ($poll->results_visibility==='after_vote') Results: After vote
                                @else Results: Private until end
                                @endif
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="bg-white shadow-sm rounded-lg p-6 text-gray-700">
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
