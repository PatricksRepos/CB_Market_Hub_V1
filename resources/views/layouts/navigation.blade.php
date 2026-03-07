<nav x-data="{ open: false }" class="border-b" style="background-color: var(--surface-bg); border-color: var(--surface-border);">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-14">
            <div class="flex items-center gap-2">
                <a href="{{ route('feed.index') }}" class="font-semibold text-base whitespace-nowrap" style="color: var(--pill-active-text);">CB Community Post</a>

                <div class="hidden sm:flex items-center gap-1 text-sm">
                    <a href="{{ route('feed.index') }}" class="px-2.5 py-1.5 rounded-md {{ request()->routeIs('feed.*') ? 'bg-indigo-200 text-indigo-950' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">Feed</a>
                    <a href="{{ route('posts.index') }}" class="px-2.5 py-1.5 rounded-md {{ request()->routeIs('posts.*') ? 'bg-indigo-200 text-indigo-950' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">Posts</a>
                    <a href="{{ route('polls.index') }}" class="px-2.5 py-1.5 rounded-md {{ request()->routeIs('polls.*') ? 'bg-indigo-200 text-indigo-950' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">Polls</a>
                    <a href="{{ route('listings.index') }}" class="px-2.5 py-1.5 rounded-md {{ request()->routeIs('listings.*') ? 'bg-indigo-200 text-indigo-950' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">Marketplace</a>
                    @auth
                        @php
                            $userId = (int) auth()->id();
                            $contactUnreadCount = \Illuminate\Support\Facades\Schema::hasTable('listing_inquiries')
                                ? \App\Models\ListingInquiry::query()
                                    ->forUser($userId)
                                    ->get()
                                    ->sum(fn ($inquiry) => $inquiry->unreadMessagesCountFor($userId))
                                : 0;
                        @endphp
                        <a href="{{ route('contacts.index') }}" class="px-2.5 py-1.5 rounded-md {{ request()->routeIs('contacts.*') || request()->routeIs('inquiries.*') || request()->routeIs('chat.*') ? 'bg-indigo-200 text-indigo-950' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">
                            Connect
                            @if($contactUnreadCount > 0)
                                <span class="ml-1 text-xs bg-red-100 text-red-800 border border-red-200 rounded-full px-2 py-0.5">{{ $contactUnreadCount }}</span>
                            @endif
                        </a>
                    @endauth
                    <a href="{{ route('events.index') }}" class="px-2.5 py-1.5 rounded-md {{ request()->routeIs('events.*') ? 'bg-indigo-200 text-indigo-950' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">Events</a>
                    <a href="{{ route('suggestions.index') }}" class="px-2.5 py-1.5 rounded-md {{ request()->routeIs('suggestions.*') ? 'bg-indigo-200 text-indigo-950' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">Suggestions</a>
                    @auth
                        @if(auth()->user()->isAdmin())
                            <a href="{{ route('moderation.index') }}" class="px-2.5 py-1.5 rounded-md {{ request()->routeIs('moderation.*') ? 'bg-indigo-200 text-indigo-950' : 'text-gray-700 hover:bg-indigo-50 hover:text-indigo-700' }}">Moderation</a>
                        @endif
                    @endauth
                </div>
            </div>

            <div class="hidden sm:flex sm:items-center sm:ms-4">
                @auth
                    <div class="flex items-center gap-2">
                        <a href="{{ route('profiles.show', auth()->user()) }}" class="inline-flex items-center" title="Open profile">
                            <x-user-avatar :user="auth()->user()" size="xs" />
                        </a>

                        <a class="text-sm px-2.5 py-1.5 rounded-md border text-indigo-700 hover:bg-indigo-50" href="{{ route('notifications.index') }}">
                            Notifications
                            @php $u = auth()->user(); $c = $u?->unreadNotifications()?->count() ?? 0; @endphp
                            <span id="navUnreadBadge" class="ml-2 text-xs bg-red-100 text-red-800 border border-red-200 rounded-full px-2 py-0.5 {{ $c > 0 ? '' : 'hidden' }}">{{ $c }}</span>
                        </a>

                        <a class="text-sm px-2.5 py-1.5 rounded-md border text-indigo-700 hover:bg-indigo-50" href="{{ route('profiles.show', auth()->user()) }}">{{ number_format((int) auth()->user()->points_total) }} pts</a>

                        <a class="text-sm px-2.5 py-1.5 rounded-md border text-indigo-700 hover:bg-indigo-50" href="{{ route('profiles.edit') }}">Edit Profile</a>

                        <button type="button" onclick="toggleThemeMode()" class="text-sm px-2.5 py-1.5 rounded-md border text-indigo-700 hover:bg-indigo-50">
                            <span data-theme-toggle-label>Light mode</span>
                        </button>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="rounded-md bg-indigo-200 text-indigo-950 px-2.5 py-1.5 text-sm hover:bg-indigo-300" type="submit">Log out</button>
                        </form>
                    </div>
                @else
                    <button type="button" onclick="toggleThemeMode()" class="text-sm px-2.5 py-1.5 rounded-md border text-indigo-700 hover:bg-indigo-50">
                        <span data-theme-toggle-label>Light mode</span>
                    </button>
                    <a href="{{ route('login') }}" class="text-sm text-indigo-700 hover:underline">Log in</a>
                    <a href="{{ route('register') }}" class="ms-4 text-sm text-indigo-700 hover:underline">Register</a>
                @endauth
            </div>

            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-700 hover:bg-indigo-50">
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
            <button type="button" onclick="toggleThemeMode()" class="block w-full text-left px-2.5 py-1.5 rounded-md border text-indigo-700 hover:bg-indigo-50">
                <span data-theme-toggle-label>Light mode</span>
            </button>

            <a class="block px-2.5 py-1.5 rounded-md {{ request()->routeIs('feed.*') ? 'bg-indigo-200 text-indigo-950' : 'hover:bg-indigo-50 text-gray-700' }}" href="{{ route('feed.index') }}">Feed</a>
            <a class="block px-2.5 py-1.5 rounded-md {{ request()->routeIs('posts.*') ? 'bg-indigo-200 text-indigo-950' : 'hover:bg-indigo-50 text-gray-700' }}" href="{{ route('posts.index') }}">Posts</a>
            <a class="block px-2.5 py-1.5 rounded-md {{ request()->routeIs('polls.*') ? 'bg-indigo-200 text-indigo-950' : 'hover:bg-indigo-50 text-gray-700' }}" href="{{ route('polls.index') }}">Polls</a>
            <a class="block px-2.5 py-1.5 rounded-md {{ request()->routeIs('suggestions.*') ? 'bg-indigo-200 text-indigo-950' : 'hover:bg-indigo-50 text-gray-700' }}" href="{{ route('suggestions.index') }}">Suggestions</a>
            <a class="block px-2.5 py-1.5 rounded-md {{ request()->routeIs('listings.*') ? 'bg-indigo-200 text-indigo-950' : 'hover:bg-indigo-50 text-gray-700' }}" href="{{ route('listings.index') }}">Marketplace</a>
            @auth
                <a class="block px-2.5 py-1.5 rounded-md {{ request()->routeIs('contacts.*') || request()->routeIs('inquiries.*') || request()->routeIs('chat.*') ? 'bg-indigo-200 text-indigo-950' : 'hover:bg-indigo-50 text-gray-700' }}" href="{{ route('contacts.index') }}">
                    Connect
                    @if(($contactUnreadCount ?? 0) > 0)
                        <span class="ml-1 text-xs bg-red-100 text-red-800 border border-red-200 rounded-full px-2 py-0.5">{{ $contactUnreadCount }}</span>
                    @endif
                </a>
            @endauth
            <a class="block px-2.5 py-1.5 rounded-md {{ request()->routeIs('events.*') ? 'bg-indigo-200 text-indigo-950' : 'hover:bg-indigo-50 text-gray-700' }}" href="{{ route('events.index') }}">Events</a>
            @auth
                @if(auth()->user()->isAdmin())
                    <a class="block px-2.5 py-1.5 rounded-md {{ request()->routeIs('moderation.*') ? 'bg-indigo-200 text-indigo-950' : 'hover:bg-indigo-50 text-gray-700' }}" href="{{ route('moderation.index') }}">Moderation</a>
                @endif
            @endauth
        </div>
    </div>
</nav>
