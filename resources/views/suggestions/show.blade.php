<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Suggestion</h2>
            <a class="text-sm text-gray-600 hover:text-gray-900" href="{{ route('suggestions.index') }}">Back</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded border bg-green-50 p-3 text-green-800">{{ session('status') }}</div>
            @endif

            <div class="bg-white rounded-lg border p-6 space-y-3">
                <div class="text-2xl font-semibold">{{ $suggestion->title }}</div>

                <div class="text-sm text-gray-500">
                    Status: <span class="font-semibold">{{ str_replace('_',' ', $suggestion->status) }}</span>
                    • Votes: <span class="font-semibold">{{ $suggestion->votes_count }}</span>
                    • Posted {{ $suggestion->created_at->diffForHumans() }}
                </div>

                <div class="text-sm text-gray-500">
                    by {{ $suggestion->is_anonymous ? 'Anonymous' : ($suggestion->user?->name ?? 'User') }}
                </div>

                @if($suggestion->body)
                    <div class="mt-2 whitespace-pre-wrap text-gray-800">{{ $suggestion->body }}</div>
                @endif

                <div class="pt-3 flex flex-wrap gap-2">
                    @auth
                        @if($hasVoted)
                            <form method="POST" action="{{ route('suggestions.unvote',$suggestion) }}">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-lg border px-4 py-2 hover:bg-gray-50" type="submit">Remove vote</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('suggestions.vote',$suggestion) }}">
                                @csrf
                                <button class="rounded-lg bg-gray-900 text-white px-4 py-2 hover:bg-gray-800" type="submit">Vote ▲</button>
                            </form>
                        @endif

                        <form method="POST" action="{{ route('suggestions.report',$suggestion) }}" class="flex gap-2">
                            @csrf
                            <input name="reason" placeholder="Report reason (optional)" class="rounded-lg border-gray-300">
                            <button class="rounded-lg border px-4 py-2 hover:bg-gray-50" type="submit">Report</button>
                        </form>

                        @if(auth()->id() === $suggestion->user_id || auth()->user()->isAdmin())
                            <a href="{{ route('suggestions.edit', $suggestion) }}" class="rounded-lg border px-4 py-2 hover:bg-gray-50">Edit</a>
                            <form method="POST" action="{{ route('suggestions.destroy', $suggestion) }}" onsubmit="return confirm('Delete this suggestion?');">
                                @csrf
                                @method('DELETE')
                                <button class="rounded-lg bg-red-600 text-white px-4 py-2 hover:bg-red-700" type="submit">Delete</button>
                            </form>
                        @endif

                        @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('suggestions.status',$suggestion) }}" class="flex gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="rounded-lg border-gray-300">
                                    <option value="open">Open</option>
                                    <option value="planned">Planned</option>
                                    <option value="in_progress">In progress</option>
                                    <option value="done">Done</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                                <button class="rounded-lg border px-4 py-2 hover:bg-gray-50" type="submit">Set status</button>
                            </form>
                        @endif
                    @else
                        <div class="text-gray-600">
                            <a class="underline" href="{{ route('login') }}">Log in</a> to vote or report.
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
