<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $poll->question }}</h2>
            <a class="text-sm text-gray-600 hover:text-gray-900" href="{{ route('polls.index') }}">Back</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded border bg-green-50 p-3 text-green-800">{{ session('status') }}</div>
            @endif

            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                    <div class="text-sm text-gray-600">
                        @if ($poll->ends_at)
                            <span class="font-medium">Ends:</span> {{ $poll->ends_at->toDayDateTimeString() }}
                            <span class="ml-2 text-xs px-2 py-1 rounded-full {{ $hasEnded ? 'bg-gray-200 text-gray-700' : 'bg-green-100 text-green-800' }}">
                                {{ $hasEnded ? 'Ended' : 'Active' }}
                            </span>
                            <div class="mt-1 text-xs text-gray-500">
                                <span class="font-medium">Countdown:</span>
                                <span id="countdown" data-ends="{{ $poll->ends_at->toIso8601String() }}">—</span>
                            </div>
                        @else
                            <span class="font-medium">Ends:</span> No end time
                            <span class="ml-2 text-xs px-2 py-1 rounded-full bg-green-100 text-green-800">Active</span>
                        @endif
                    </div>

                    <div class="text-xs px-2 py-1 rounded-full bg-gray-100 text-gray-700 w-fit">
                        @if ($poll->results_visibility==='always') Results: Public
                        @elseif ($poll->results_visibility==='after_vote') Results: After vote
                        @else Results: Private until end
                        @endif
                    </div>
                </div>

                <x-reaction-bar :model="$poll" type="poll" />

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
                                               @checked($myVote && (int)$myVote->poll_option_id === (int)$opt->id)>
                                        <span class="text-base font-medium">{{ $opt->label }}</span>
                                    </label>
                                @endforeach
                            </div>

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

            @if ($isCreator || $isAdmin)
                <div class="bg-white shadow-sm rounded-lg p-6 space-y-3">
                    <div class="font-semibold">Creator tools</div>

                    <div class="flex flex-col sm:flex-row gap-2">
                        <form method="POST" action="{{ route('polls.end', $poll) }}">
                            @csrf
                            <button class="rounded-lg border px-4 py-2 hover:bg-gray-50" type="submit">End poll now</button>
                        </form>

                        <form method="POST" action="{{ route('polls.visibility', $poll) }}" class="flex gap-2 items-center">
                            @csrf
                            @method('PATCH')
                            <select name="results_visibility" class="rounded-lg border-gray-300">
                                <option value="after_end" @selected($poll->results_visibility==='after_end')>Private until end</option>
                                <option value="after_vote" @selected($poll->results_visibility==='after_vote')>After vote</option>
                                <option value="always" @selected($poll->results_visibility==='always')>Public</option>
                            </select>
                            <button class="rounded-lg bg-gray-900 text-white px-4 py-2 hover:bg-gray-800" type="submit">Update</button>
                        </form>

                        <form method="POST" action="{{ route('polls.destroy', $poll) }}"
                              onsubmit="return confirm('Delete this poll?');" class="sm:ml-auto">
                            @csrf
                            @method('DELETE')
                            <button class="rounded-lg bg-red-600 text-white px-4 py-2 hover:bg-red-700" type="submit">Delete</button>
                        </form>
                    </div>

                    <div class="text-xs text-gray-500">
                        Note: “Private until end” requires an end time.
                    </div>
                </div>
            @endif

            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="font-semibold mb-3">Results</div>

                @if (!$canSeeResults)
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

            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="font-semibold mb-3">Comments</div>

                @auth
                    <form method="POST" action="{{ route('polls.comments.store', $poll) }}" class="space-y-2 mb-4">
                        @csrf
                        <textarea name="body" rows="3" class="w-full rounded-lg border-gray-300"
                                  placeholder="Add a comment..." required maxlength="2000"></textarea>
                        <button class="rounded-lg bg-gray-900 text-white px-4 py-2 hover:bg-gray-800" type="submit">
                            Post comment
                        </button>
                    </form>
                @else
                    <div class="text-gray-600 mb-4">
                        <a class="underline" href="{{ route('login') }}">Log in</a> to comment.
                    </div>
                @endauth

                <div class="space-y-4">
                    @forelse ($poll->comments->sortByDesc('created_at') as $comment)
                        <div class="rounded-lg border p-3">
                            <div class="flex items-center justify-between">
                                <div class="text-sm text-gray-700 font-medium">
                                    {{ $comment->user?->name ?? 'User' }}
                                    <span class="text-xs text-gray-500 font-normal">• {{ $comment->created_at->diffForHumans() }}</span>
                                </div>

                                @auth
                                    @if (auth()->user()->is_admin || auth()->id() === $comment->user_id)
                                        <form method="POST" action="{{ route('polls.comments.destroy', [$poll, $comment]) }}"
                                              onsubmit="return confirm('Delete this comment?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="text-xs text-red-600 hover:underline" type="submit">Delete</button>
                                        </form>
                                    @endif
                                @endauth
                            </div>

                            <div class="mt-2 text-gray-800 whitespace-pre-wrap">{{ $comment->body }}</div>
                            <x-reaction-bar :model="$comment" type="poll_comment" />
                        </div>
                    @empty
                        <div class="text-gray-600">No comments yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <script>
    (function(){
      const el = document.getElementById('countdown');
      if (!el) return;
      const endsIso = el.getAttribute('data-ends');
      if (!endsIso) { el.textContent = '—'; return; }
      const ends = new Date(endsIso).getTime();

      function tick(){
        const now = Date.now();
        let diff = Math.max(0, ends - now);
        const s = Math.floor(diff/1000);
        const days = Math.floor(s/86400);
        const hrs = Math.floor((s%86400)/3600);
        const mins = Math.floor((s%3600)/60);
        const secs = s%60;

        const parts = [];
        if (days) parts.push(days+'d');
        parts.push(String(hrs).padStart(2,'0')+'h');
        parts.push(String(mins).padStart(2,'0')+'m');
        parts.push(String(secs).padStart(2,'0')+'s');

        el.textContent = parts.join(' ');
        if (diff <= 0) el.textContent = 'Ended';
      }

      tick();
      setInterval(tick, 1000);
    })();
    </script>
</x-app-layout>
