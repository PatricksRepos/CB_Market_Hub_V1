<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $poll->question }}</h2>
            <a class="text-sm text-gray-600 hover:text-gray-900" href="{{ route('polls.index') }}">Back</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded border bg-green-50 p-3 text-green-800">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm rounded p-6">
                <div class="flex items-center justify-between mb-4">
                    <div class="text-sm text-gray-600">
                        @if ($poll->ends_at)
                            Ends: {{ $poll->ends_at->toDayDateTimeString() }}
                        @else
                            No end time set
                        @endif
                    </div>

                    <div class="text-xs text-gray-500">
                        Results:
                        @if ($poll->results_visibility === 'always')
                            Public
                        @elseif ($poll->results_visibility === 'after_vote')
                            After vote
                        @else
                            Private until end
                        @endif
                    </div>
                </div>

                @auth
                    @if ($poll->ends_at && now()->gte($poll->ends_at))
                        <div class="text-gray-700">Voting is closed.</div>
                    @else
                        <form method="POST" action="{{ route('polls.vote', $poll) }}" class="space-y-4">
                            @csrf

                            <div class="space-y-3">
                                @foreach ($poll->options as $opt)
                                    <label class="flex items-center gap-3 rounded-lg border p-3 hover:bg-gray-50 cursor-pointer">
                                        <input class="h-5 w-5" type="radio" name="poll_option_id" value="{{ $opt->id }}" required
                                               @checked(isset($myVote) && $myVote && (int)$myVote->poll_option_id === (int)$opt->id)>
                                        <span class="text-base font-medium">{{ $opt->label }}</span>
                                    </label>
                                @endforeach
                            </div>

                            <!-- Sticky vote button (always visible) -->
                            <div class="sticky bottom-4">
                                <button type="submit"
                                        class="w-full rounded-lg bg-gray-900 px-6 py-4 text-white text-lg font-bold shadow-lg hover:bg-gray-800">
                                    Vote
                                </button>
                            </div>
                        </form>
                    @endif
                @else
                    <div class="text-gray-600">
                        Please <a class="underline" href="{{ route('login') }}">log in</a> to vote.
                    </div>
                @endauth
            </div>

            <div class="bg-white shadow-sm rounded p-6">
                <div class="font-semibold mb-3">Results</div>

                @if (isset($canSeeResults) && !$canSeeResults)
                    <div class="text-gray-600">
                        Results are hidden right now.
                        @if ($poll->results_visibility === 'after_vote')
                            Vote to reveal results.
                        @elseif ($poll->results_visibility === 'after_end')
                            Results will appear when the poll ends.
                        @endif
                    </div>
                @else
                    @php
                        $total = $poll->votes->count();
                        $byOption = $poll->votes->groupBy('poll_option_id')->map->count();
                    @endphp

                    <div class="space-y-4">
                        @foreach ($poll->options as $opt)
                            @php
                                $count = (int) ($byOption[$opt->id] ?? 0);
                                $pct = $total > 0 ? round(($count / $total) * 100) : 0;
                            @endphp

                            <div>
                                <div class="flex items-center justify-between text-sm">
                                    <div class="font-medium">{{ $opt->label }}</div>
                                    <div class="text-gray-600">{{ $count }} ({{ $pct }}%)</div>
                                </div>
                                <div class="mt-2 h-3 w-full rounded bg-gray-200 overflow-hidden">
                                    <div class="h-full bg-gray-900" style="width: {{ $pct }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 text-xs text-gray-500">Total votes: {{ $total }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
