<x-app-layout>
  <x-slot name="header">
    <div class="flex items-center justify-between gap-3">
      <div>
        <h2 class="font-semibold text-xl text-gray-800">Community Posts</h2>
        <p class="text-sm text-gray-500">Browse updates, marketplace posts, and discussions from members.</p>
      </div>

      <div class="flex flex-wrap items-center gap-2">
        @auth
          <a class="rounded-lg bg-gray-900 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800" href="{{ route('posts.create') }}">Create Post</a>
        @else
          <a class="rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-50" href="{{ route('login') }}">Log in to Post</a>
        @endauth

        @auth
          <a class="rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-50" href="{{ route('polls.create') }}">Start Discussion</a>
        @else
          <a class="rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-50" href="{{ route('polls.index') }}">Browse Discussions</a>
        @endauth

        @auth
          @if(auth()->user()->isAdmin())
            <a class="rounded-lg border px-4 py-2 text-sm font-semibold hover:bg-gray-50" href="/admin">Admin</a>
          @endif
        @endauth
      </div>
    </div>
  </x-slot>

  <div class="p-6 space-y-4">
    <form method="GET" class="bg-white rounded-lg border p-4 flex flex-wrap gap-3 items-end">
      <div>
        <label class="block text-xs uppercase tracking-wide text-gray-500">Search</label>
        <input name="q" value="{{ request('q') }}" placeholder="Search posts" class="mt-1 border rounded px-3 py-2">
      </div>

      <div>
        <label class="block text-xs uppercase tracking-wide text-gray-500">Category</label>
        <select name="category" class="mt-1 border rounded px-3 py-2">
          <option value="">All Categories</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
            @foreach($cat->children as $child)
              <option value="{{ $child->id }}" @selected(request('category') == $child->id)>— {{ $child->name }}</option>
            @endforeach
          @endforeach
        </select>
      </div>

      <button class="px-4 py-2 border rounded-lg text-sm font-semibold hover:bg-gray-50">Apply</button>

      @if(request('category') || request('q'))
        <a class="px-4 py-2 border rounded-lg text-sm font-semibold hover:bg-gray-50" href="{{ route('posts.index') }}">Clear</a>
      @endif
    </form>

    <div class="space-y-4">
      @forelse($posts as $post)
        <div class="bg-white p-5 border rounded-lg shadow-sm">
          <div class="text-sm opacity-70">
            {{ $post->type }}
            @if($post->category)
              • {{ $post->category->parent ? $post->category->parent->name.' › '.$post->category->name : $post->category->name }}
            @endif
            @if($post->is_promoted) • PROMOTED @endif
          </div>

          <div class="mt-2 flex items-center gap-2 text-sm text-gray-600">
            @if($post->user?->avatar_url)
              <img src="{{ $post->user->avatar_url }}" alt="{{ $post->user->name }} avatar" class="h-16 w-16 rounded-full object-cover border">
            @else
              <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center text-gray-700 text-xs font-semibold">{{ strtoupper(substr($post->user?->name ?? 'U',0,1)) }}</div>
            @endif
            <span>{{ $post->is_anonymous ? ($post->anonymous_name ?? 'Anon') : ($post->user?->name ?? 'User') }}</span>
            <span>• {{ $post->created_at->diffForHumans() }}</span>
          </div>

          <a class="text-lg font-semibold hover:underline mt-2 inline-block" href="{{ route('posts.show', $post) }}">{{ $post->title }}</a>

          @if($post->images->first())
            <a href="{{ route('posts.show', $post) }}" class="block mt-2">
              <div class="w-full max-w-xl rounded-lg border overflow-hidden bg-gray-100" style="height: 20rem;">
                <img class="w-full h-full object-cover" style="width: 100%; height: 100%; object-fit: cover;" src="{{ asset('storage/'.$post->images->first()->path) }}" alt="Post image thumbnail">
              </div>
            </a>
          @endif

          <div class="text-sm opacity-70 mt-2">
            @if($post->marketplace_action)
              {{ strtoupper($post->marketplace_action) }}
            @endif
            @if($post->location) {{ $post->location }} @endif
            @if($post->price) • ${{ $post->price }} @endif
          </div>
        </div>
      @empty
        <div class="p-4 border rounded opacity-70">No posts yet. Be the first to create one.</div>
      @endforelse
    </div>

    <div>{{ $posts->links() }}</div>
  </div>
</x-app-layout>
