<nav x-data="{ open: false }" class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex items-center gap-3">
                <a href="{{ route('feed.index') }}" class="font-bold text-gray-900">CB Community</a>

                <div class="hidden sm:flex items-center gap-2">
                    <a href="{{ route('feed.index') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('feed.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">Feed</a>
                    <a href="{{ route('posts.index') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('posts.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">Posts</a>
                    <a href="{{ route('polls.index') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('polls.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">Polls</a>
                    <a href="{{ route('listings.index') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('listings.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">Marketplace</a>
                    <a href="{{ route('events.index') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('events.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">Events</a>
                    <a href="{{ route('suggestions.index') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('suggestions.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">Suggestions</a>
                    <a href="{{ route('chat.index') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('chat.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">Chat</a>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('moderation.index') }}" class="px-3 py-2 rounded-lg {{ request()->routeIs('moderation.*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-100' }}">Moderation</a>
                        @endif
                    @endauth
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @auth
                    <div class="flex items-center gap-3">
                        <a class="text-sm px-3 py-2 rounded-lg border hover:bg-gray-50" href="{{ route('notifications.index') }}">
                            Notifications
                            @php $u = auth()->user(); $c = $u?->unreadNotifications()?->count() ?? 0; @endphp
                            @if ($c > 0)
                                <span class="ml-2 text-xs bg-red-600 text-white rounded-full px-2 py-0.5">{{ $c }}</span>
                            @endif
                        </a>

                        <a class="text-sm px-3 py-2 rounded-lg border hover:bg-gray-50" href="{{ route('profiles.edit') }}">Profile</a>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="rounded-lg bg-gray-900 text-white px-3 py-2 text-sm hover:bg-gray-800" type="submit">Log out</button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:underline">Log in</a>
                    <a href="{{ route('register') }}" class="ms-4 text-sm text-gray-700 hover:underline">Register</a>
                @endauth
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:bg-gray-100">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden border-t">
        <div class="pt-2 pb-3 space-y-1 px-4">
            <a class="block px-3 py-2 rounded-lg hover:bg-gray-100" href="{{ route('feed.index') }}">Feed</a>
            <a class="block px-3 py-2 rounded-lg hover:bg-gray-100" href="{{ route('posts.index') }}">Posts</a>
            <a class="block px-3 py-2 rounded-lg hover:bg-gray-100" href="{{ route('polls.index') }}">Polls</a>
            <a class="block px-3 py-2 rounded-lg hover:bg-gray-100" href="{{ route('suggestions.index') }}">Suggestions</a>
            <a class="block px-3 py-2 rounded-lg hover:bg-gray-100" href="{{ route('chat.index') }}">Chat</a>
            <a class="block px-3 py-2 rounded-lg hover:bg-gray-100" href="{{ route('listings.index') }}">Marketplace</a>
            <a class="block px-3 py-2 rounded-lg hover:bg-gray-100" href="{{ route('events.index') }}">Events</a>
            @auth
                @if(auth()->user()->isAdmin())
                    <a class="block px-3 py-2 rounded-lg hover:bg-gray-100" href="{{ route('moderation.index') }}">Moderation</a>
                @endif
            @endauth
        </div>
    </div>
</nav>
