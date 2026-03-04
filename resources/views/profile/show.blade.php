<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $user->name }}'s Profile</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded border bg-green-50 p-3 text-green-800">{{ session('status') }}</div>
            @endif

            <div class="bg-white rounded-lg border p-6">
                <div class="flex items-start gap-4">
                    @if($user->avatar_url)
                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }} avatar" class="h-16 w-16 rounded-full object-cover border">
                    @else
                        <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-semibold">{{ strtoupper(substr($user->name,0,1)) }}</div>
                    @endif

                    <div class="flex-1">
                        <div class="text-2xl font-semibold">{{ $user->name }}</div>
                        @if($user->username)
                            <div class="text-sm text-gray-500">@{{ $user->username }}</div>
                        @endif
                        @if($user->bio)
                            <div class="mt-3 text-gray-800 whitespace-pre-wrap">{{ $user->bio }}</div>
                        @endif
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                    <div class="rounded border p-3"><div class="text-gray-500">Posts</div><div class="font-semibold">{{ $user->posts_count }}</div></div>
                    <div class="rounded border p-3"><div class="text-gray-500">Polls</div><div class="font-semibold">{{ $user->polls_count }}</div></div>
                    <div class="rounded border p-3"><div class="text-gray-500">Listings</div><div class="font-semibold">{{ $user->listings_count }}</div></div>
                    <div class="rounded border p-3"><div class="text-gray-500">Events</div><div class="font-semibold">{{ $user->events_count }}</div></div>
                </div>
            </div>

            <div class="bg-white rounded-lg border p-6">
                <h3 class="font-semibold mb-3">Recent posts</h3>
                @forelse($latestPosts as $post)
                    <div class="py-1"><a class="underline" href="{{ route('posts.show', $post) }}">{{ $post->title }}</a></div>
                @empty
                    <div class="text-gray-500">No posts yet.</div>
                @endforelse
            </div>

            <div class="bg-white rounded-lg border p-6">
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
