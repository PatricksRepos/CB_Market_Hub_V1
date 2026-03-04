<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Community Feed</h2>
            <div class="flex items-center gap-2 text-sm">
                @auth
                    <a href="{{ route('posts.create') }}" class="rounded border px-3 py-1.5 hover:bg-gray-50">Add Post</a>
                    <a href="{{ route('events.create') }}" class="rounded border px-3 py-1.5 hover:bg-gray-50">Add Event</a>
                    <a href="{{ route('suggestions.create') }}" class="rounded border px-3 py-1.5 hover:bg-gray-50">Add Suggestion</a>
                @else
                    <a href="{{ route('login') }}" class="underline text-gray-600">Log in to add post/event/suggestion</a>
                @endauth
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-3">
            @foreach ($items as $item)
                <div class="bg-white shadow-sm rounded-lg p-4">
                    @if ($item['type']==='post')
                        <div class="text-xs text-gray-500">New post • {{ $item['at']->diffForHumans() }}</div>
                        <div class="mt-2 flex items-center gap-2 text-sm text-gray-600">
                            @php $postUser = $item['data']->user; @endphp
                            @if($postUser?->avatar_url)
                                <img src="{{ $postUser->avatar_url }}" alt="{{ $postUser->name }} avatar" class="h-16 w-16 rounded-full object-cover border">
                            @else
                                <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center text-gray-700 text-xl font-semibold">{{ strtoupper(substr($postUser?->name ?? 'U',0,1)) }}</div>
                            @endif
                            <span class="font-medium">{{ $item['data']->is_anonymous ? ($item['data']->anonymous_name ?? 'Anon') : ($postUser?->name ?? 'User') }}</span>
                        </div>
                        <div class="font-semibold text-lg mt-2">
                            <a class="hover:underline" href="{{ route('posts.show', $item['data']) }}">{{ $item['data']->title ?? 'Post #'.$item['data']->id }}</a>
                        </div>
                        @if($item['data']->images->first())
                            <a href="{{ route('posts.show', $item['data']) }}" class="block mt-3">
                                <img src="{{ asset('storage/'.$item['data']->images->first()->path) }}" alt="Post image thumbnail" class="h-24 w-24 rounded border object-cover">
                            </a>
                        @endif

                        <x-reaction-bar :model="$item['data']" type="post" />

                    @elseif ($item['type']==='listing')
                        <div class="text-xs text-gray-500">New marketplace listing • {{ $item['at']->diffForHumans() }}</div>
                        <div class="font-semibold text-lg mt-1">
                            <a class="hover:underline" href="{{ route('listings.show', $item['data']) }}">{{ $item['data']->title }}</a>
                        </div>
                        <div class="text-sm text-gray-600">{{ ucfirst($item['data']->category) }} • by {{ $item['data']->user?->name ?? 'User' }}</div>
                        <x-reaction-bar :model="$item['data']" type="listing" />

                    @elseif ($item['type']==='event')
                        <div class="text-xs text-gray-500">New event • {{ $item['at']->diffForHumans() }}</div>
                        <div class="font-semibold text-lg mt-1">
                            <a class="hover:underline" href="{{ route('events.show', $item['data']) }}">{{ $item['data']->title }}</a>
                        </div>
                        <div class="text-sm text-gray-600">Hosted by {{ $item['data']->user?->name ?? 'User' }}</div>
                        <x-reaction-bar :model="$item['data']" type="event" />

                    @elseif ($item['type']==='suggestion')
                        <div class="text-xs text-gray-500">New suggestion • {{ $item['at']->diffForHumans() }}</div>
                        <div class="font-semibold text-lg mt-1">
                            <a class="hover:underline" href="{{ route('suggestions.show', $item['data']) }}">{{ $item['data']->title }}</a>
                        </div>
                        <div class="text-sm text-gray-600">By {{ $item['data']->is_anonymous ? 'Anonymous' : ($item['data']->user?->name ?? 'User') }}</div>
                        <x-reaction-bar :model="$item['data']" type="suggestion" />

                    @elseif ($item['type']==='poll')
                        <div class="text-xs text-gray-500">New poll • {{ $item['at']->diffForHumans() }}</div>
                        <div class="font-semibold text-lg mt-1">
                            <a class="hover:underline" href="{{ route('polls.show', $item['data']) }}">{{ $item['data']->question }}</a>
                        </div>
                        <x-reaction-bar :model="$item['data']" type="poll" />
                    @elseif ($item['type']==='post_comment')
                        <div class="text-xs text-gray-500">Post comment • {{ $item['at']->diffForHumans() }}</div>
                        <div class="mt-1">
                            <span class="font-semibold">{{ $item['data']->user?->name ?? 'User' }}</span>:
                            <span class="text-gray-800">{{ \Illuminate\Support\Str::limit($item['data']->body, 140) }}</span>
                        </div>
                        <div class="text-sm mt-2">
                            <a class="underline" href="{{ route('posts.show', $item['data']->post_id) }}">Open post</a>
                        </div>
                    @elseif ($item['type']==='poll_comment')
                        <div class="text-xs text-gray-500">Poll comment • {{ $item['at']->diffForHumans() }}</div>
                        <div class="mt-1">
                            <span class="font-semibold">{{ $item['data']->user?->name ?? 'User' }}</span>:
                            <span class="text-gray-800">{{ \Illuminate\Support\Str::limit($item['data']->body, 140) }}</span>
                        </div>
                        <div class="text-sm mt-2">
                            <a class="underline" href="{{ route('polls.show', $item['data']->poll_id) }}">Open poll</a>
                        </div>
                        <x-reaction-bar :model="$item['data']" type="poll_comment" />
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</x-app-layout>
