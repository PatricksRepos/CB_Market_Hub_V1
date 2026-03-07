<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between gap-3">
      <h2 class="font-semibold text-xl text-gray-900">Cape Breton Community Marketplace</h2>
      <span class="hidden sm:inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">Fresh posts daily</span>
    </div>
  </x-slot>

  <div class="p-6 space-y-5">
    <div class="flex flex-wrap items-center gap-2">
      @auth
        <a class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" href="{{ route('posts.create') }}">+ Create Post</a>
      @else
        <a class="inline-flex items-center rounded-lg bg-gray-900 px-4 py-2 text-sm font-medium text-white hover:bg-gray-800" href="{{ route('login') }}">Login to Post</a>
      @endauth

      <a class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" href="{{ route('polls.index') }}">Discussions (Polls)</a>
      <a class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" href="/admin">Admin</a>
    </div>

    <form method="GET" class="rounded-xl border border-gray-200 bg-white p-3 sm:p-4 shadow-sm">
      <div class="flex flex-wrap items-center gap-2">
        <input name="q" value="{{ request('q') }}" placeholder="Search posts" class="min-w-[220px] flex-1 rounded-lg border-gray-300 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-indigo-400">
        <select name="category" class="rounded-lg border-gray-300 px-3 py-2 text-sm focus:border-indigo-400 focus:ring-indigo-400">
          <option value="">All Categories</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
            @foreach($cat->children as $child)
              <option value="{{ $child->id }}" @selected(request('category') == $child->id)>— {{ $child->name }}</option>
            @endforeach
          @endforeach
        </select>

        <button class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Filter</button>

        @if(request('category') || request('q'))
          <a class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50" href="{{ route('posts.index') }}">Clear</a>
        @endif
      </div>
    </form>

    <div class="space-y-4">
      @forelse($posts as $post)
        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
          <div class="text-xs font-medium uppercase tracking-wide text-gray-500">
            {{ $post->type }}
            @if($post->category)
              • {{ $post->category->parent ? $post->category->parent->name.' › '.$post->category->name : $post->category->name }}
            @endif
            @if($post->is_promoted) • Promoted @endif
          </div>

          <div class="mt-2 flex items-center gap-2 text-sm text-gray-600">
            <x-user-avatar :user="$post->user" :name="$post->user?->name ?? 'User'" size="h-10 w-10" class="border-gray-200" />
            <span>{{ $post->is_anonymous ? ($post->anonymous_name ?? 'Anon') : ($post->user?->name ?? 'User') }}</span>
            <span>• {{ $post->created_at->diffForHumans() }}</span>
          </div>

          <a class="mt-2 inline-block text-lg font-semibold text-gray-900 hover:text-indigo-700" href="{{ route('posts.show', $post) }}">{{ $post->title }}</a>

          @if($post->images->first())
            <a href="{{ route('posts.show', $post) }}" class="block mt-3">
              <div class="w-full max-w-xl overflow-hidden rounded-xl border border-gray-200 bg-gray-100" style="height: 20rem;">
                <img class="h-full w-full object-cover" src="{{ asset('storage/'.$post->images->first()->path) }}" alt="Post image thumbnail">
              </div>
            </a>
          @endif

          <div class="mt-3 text-sm text-gray-600">
            @if($post->marketplace_action)
              <span class="font-medium">{{ strtoupper($post->marketplace_action) }}</span>
            @endif
            @if($post->location) <span class="ml-1">{{ $post->location }}</span> @endif
            @if($post->price) <span class="ml-1">• ${{ $post->price }}</span> @endif
          </div>
        </div>
      @empty
        <div class="rounded-xl border border-gray-200 bg-white p-4 text-gray-600">No posts yet. Be the first to create one.</div>
      @endforelse
    </div>

    <div>{{ $posts->links() }}</div>
  </div>
</x-app-layout>
