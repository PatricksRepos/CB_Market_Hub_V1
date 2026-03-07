<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $user->name }}'s Profile</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded border bg-green-50 p-3 text-green-800">{{ session('status') }}</div>
            @endif

            <div class="bg-white rounded-xl border p-8">
                <div class="text-center">
                    <x-user-avatar :user="$user" size="mx-auto h-24 w-24" class="border-gray-300" />

                    <div class="mt-4 text-2xl font-semibold">{{ $user->name }}</div>
                    @if($user->username)
                        <div class="text-sm text-gray-500">@{{ $user->username }}</div>
                    @endif
                    @if($user->bio)
                        <div class="mt-4 text-gray-800 whitespace-pre-wrap max-w-2xl mx-auto">{{ $user->bio }}</div>
                    @endif
                </div>

                <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                    <div class="rounded border p-3"><div class="text-gray-500">Posts</div><div class="font-semibold">{{ $user->posts_count }}</div></div>
                    <div class="rounded border p-3"><div class="text-gray-500">Polls</div><div class="font-semibold">{{ $user->polls_count }}</div></div>
                    <div class="rounded border p-3"><div class="text-gray-500">Listings</div><div class="font-semibold">{{ $user->listings_count }}</div></div>
                    <div class="rounded border p-3"><div class="text-gray-500">Events</div><div class="font-semibold">{{ $user->events_count }}</div></div>
                </div>

                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                    <div class="rounded border p-3 bg-indigo-50 border-indigo-100">
                        <div class="text-gray-600">Points</div>
                        <div class="font-semibold text-lg">{{ number_format((int) $user->points_total) }}</div>
                    </div>
                    <div class="rounded border p-3 bg-amber-50 border-amber-100">
                        <div class="text-gray-600">Current Badge</div>
                        <div class="font-semibold text-lg">{{ $user->badges->last()?->name ?? 'Newcomer' }}</div>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="text-sm text-gray-500 mb-2">Unlocked badges</div>
                    <div class="flex flex-wrap gap-2">
                        @forelse($user->badges as $badge)
                            <span class="px-3 py-1 rounded-full border text-xs bg-gray-50" title="{{ $badge->description }}">{{ $badge->name }}</span>
                        @empty
                            <span class="text-sm text-gray-500">No badges yet.</span>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border p-6">
                <h3 class="font-semibold mb-3">Recent points activity</h3>
                @forelse($recentPointActivity as $activity)
                    <div class="py-1 text-sm flex justify-between gap-3 border-b last:border-b-0">
                        <span class="text-gray-700">{{ str_replace('.', ' ', $activity->action) }}</span>
                        <span class="font-medium text-indigo-700">+{{ $activity->points }}</span>
                    </div>
                @empty
                    <div class="text-gray-500">No activity yet.</div>
                @endforelse
            </div>

            <div class="bg-white rounded-xl border p-6">
                <h3 class="font-semibold mb-3">Recent posts</h3>
                @forelse($latestPosts as $post)
                    <div class="py-1"><a class="underline" href="{{ route('posts.show', $post) }}">{{ $post->title }}</a></div>
                @empty
                    <div class="text-gray-500">No posts yet.</div>
                @endforelse
            </div>

            <div class="bg-white rounded-xl border p-6">
                <h3 class="font-semibold mb-3">Recent polls</h3>
                @forelse($latestPolls as $poll)
                    <div class="py-1"><a class="underline" href="{{ route('polls.show', $poll) }}">{{ $poll->question }}</a></div>
                @empty
                    <div class="text-gray-500">No polls yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
