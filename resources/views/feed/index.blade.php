<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-3">
            <h2 class="font-semibold text-xl leading-tight" style="color: var(--pill-active-text);">Community Feed</h2>
            <div class="flex items-center gap-2 text-sm">
                @auth
                    <a href="{{ route('posts.create') }}" class="rounded border px-3 py-1.5 text-indigo-950" style="background-color:#c7d2fe;border-color:#a5b4fc;">Add Post</a>
                    <a href="{{ route('events.create') }}" class="rounded border px-3 py-1.5 text-indigo-950" style="background-color:#c7d2fe;border-color:#a5b4fc;">Add Event</a>
                    <a href="{{ route('suggestions.create') }}" class="rounded border px-3 py-1.5 text-indigo-950" style="background-color:#c7d2fe;border-color:#a5b4fc;">Add Suggestion</a>
                @else
                    <a href="{{ route('login') }}" class="underline text-indigo-700">Log in to add post/event/suggestion</a>
                @endauth
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-5">
            <div class="bg-white shadow-sm rounded-lg p-5 space-y-4" style="border: 1px solid var(--surface-border); background-color: var(--surface-bg);">
                <form method="GET" action="{{ route('feed.index') }}" class="grid gap-3 sm:grid-cols-12 sm:items-end">
                    <div class="sm:col-span-6">
                        <label for="q" class="block text-xs uppercase tracking-wide text-gray-500">Search</label>
                        <input id="q" type="text" name="q" value="{{ $search }}" placeholder="Search posts, events, listings..." class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" style="border-color:#cbd5e1;">
                    </div>
                    <div class="sm:col-span-4">
                        <label for="type" class="block text-xs uppercase tracking-wide text-gray-500">Type</label>
                        <select id="type" name="type" class="mt-1 w-full rounded border-gray-300 focus:border-indigo-500 focus:ring-indigo-500" style="border-color:#cbd5e1;">
                            @foreach($availableTypes as $typeValue => $typeLabel)
                                <option value="{{ $typeValue }}" @selected($selectedType === $typeValue)>{{ $typeLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-2 flex gap-2">
                        <button type="submit" class="inline-flex items-center rounded border px-3 py-2 text-sm text-indigo-950" style="background-color:#c7d2fe;border-color:#a5b4fc;">Apply</button>
                        <a href="{{ route('feed.index') }}" class="inline-flex items-center rounded border px-3 py-2 text-sm text-indigo-700 hover:bg-indigo-50" style="border-color:#a5b4fc;">Reset</a>
                    </div>
                </form>

                <div class="flex flex-wrap gap-2">
                    @foreach($availableTypes as $typeValue => $typeLabel)
                        @php
                            $count = $typeValue === 'all'
                                ? (int) collect($typeCounts)->sum()
                                : (int) ($typeCounts[$typeValue] ?? 0);
                        @endphp
                        <a
                            href="{{ route('feed.index', array_filter(['type' => $typeValue, 'q' => $search !== '' ? $search : null])) }}"
                            class="inline-flex items-center gap-2 rounded-full border px-3 py-1 text-xs {{ $selectedType === $typeValue ? 'border-indigo-300 bg-indigo-200 text-indigo-950' : 'border-gray-300 text-gray-600 hover:bg-indigo-50 hover:text-indigo-700' }}"
                        >
                            <span>{{ $typeLabel }}</span>
                            <span class="rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $selectedType === $typeValue ? "bg-white text-indigo-700" : "bg-white text-gray-700" }}">{{ $count }}</span>
                        </a>
                    @endforeach
                </div>
            </div>

            @forelse ($items as $item)
                <div class="bg-white shadow-sm rounded-lg p-5">
                    @if ($item['type']==='post')
                        <div class="text-xs text-gray-500">New post • {{ $item['at']->diffForHumans() }}</div>
                        <div class="mt-2 flex items-center gap-2 text-sm text-gray-600">
                            @php $postUser = $item['data']->user; @endphp
                            <x-user-avatar :user="$postUser" :name="$postUser?->name ?? 'User'" size="lg" />
                            <span class="font-medium">{{ $item['data']->is_anonymous ? ($item['data']->anonymous_name ?? 'Anon') : ($postUser?->name ?? 'User') }}</span>
                        </div>
                        <div class="font-semibold text-lg mt-2">
                            <a class="hover:underline" href="{{ route('posts.show', $item['data']) }}">{{ $item['data']->title ?? 'Post #'.$item['data']->id }}</a>
                        </div>
                        @if($item['data']->images->first())
                            <a href="{{ route('posts.show', $item['data']) }}" class="block mt-3">
                                <div class="w-full max-w-xl rounded-lg border overflow-hidden bg-gray-100" style="height: 20rem;">
                                    <img
                                        src="{{ asset('storage/'.$item['data']->images->first()->path) }}"
                                        alt="Post image thumbnail"
                                        class="w-full h-full object-cover"
                                        style="width: 100%; height: 100%; object-fit: cover;"
                                    >
                                </div>
                            </a>
                        @endif

                        <div class="mt-4">
                            <x-reaction-bar :model="$item['data']" type="post" />
                        </div>

                    @elseif ($item['type']==='listing')
                        <div class="text-xs text-gray-500">New marketplace listing • {{ $item['at']->diffForHumans() }}</div>
                        <div class="font-semibold text-lg mt-1">
                            <a class="hover:underline" href="{{ route('listings.show', $item['data']) }}">{{ $item['data']->title }}</a>
                        </div>
                        <div class="text-sm text-gray-600">{{ ucfirst($item['data']->category) }} • by {{ $item['data']->user?->name ?? 'User' }}</div>
                        <div class="mt-3">
                            <x-reaction-bar :model="$item['data']" type="listing" />
                        </div>

                    @elseif ($item['type']==='event')
                        <div class="text-xs text-gray-500">New event • {{ $item['at']->diffForHumans() }}</div>
                        <div class="font-semibold text-lg mt-1">
                            <a class="hover:underline" href="{{ route('events.show', $item['data']) }}">{{ $item['data']->title }}</a>
                        </div>
                        <div class="text-sm text-gray-600">Hosted by {{ $item['data']->user?->name ?? 'User' }}</div>
                        <div class="mt-3">
                            <x-reaction-bar :model="$item['data']" type="event" />
                        </div>

                    @elseif ($item['type']==='suggestion')
                        <div class="text-xs text-gray-500">New suggestion • {{ $item['at']->diffForHumans() }}</div>
                        <div class="font-semibold text-lg mt-1">
                            <a class="hover:underline" href="{{ route('suggestions.show', $item['data']) }}">{{ $item['data']->title }}</a>
                        </div>
                        <div class="text-sm text-gray-600">By {{ $item['data']->is_anonymous ? 'Anonymous' : ($item['data']->user?->name ?? 'User') }}</div>
                        <div class="mt-3">
                            <x-reaction-bar :model="$item['data']" type="suggestion" />
                        </div>

                    @elseif ($item['type']==='poll')
                        <div class="text-xs text-gray-500">New poll • {{ $item['at']->diffForHumans() }}</div>
                        <div class="font-semibold text-lg mt-1">
                            <a class="hover:underline" href="{{ route('polls.show', $item['data']) }}">{{ $item['data']->question }}</a>
                        </div>
                        <div class="mt-3">
                            <x-reaction-bar :model="$item['data']" type="poll" />
                        </div>
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
                        <div class="mt-3">
                            <x-reaction-bar :model="$item['data']" type="poll_comment" />
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white shadow-sm rounded-lg p-6 text-center" style="border: 1px solid var(--surface-border); background-color: var(--surface-bg);">
                    <p class="text-gray-700 font-medium">No activity matched your filters.</p>
                    <p class="text-sm text-gray-500 mt-1">Try clearing your search or switching to another feed type.</p>
                    <a href="{{ route('feed.index') }}" class="inline-flex items-center rounded border px-3 py-1.5 mt-4 text-sm text-indigo-700 hover:bg-indigo-50" style="border-color:#a5b4fc;">Clear filters</a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
