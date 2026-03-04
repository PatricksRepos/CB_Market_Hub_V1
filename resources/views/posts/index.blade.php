<x-app-layout>
  <x-slot name="header">
    <h2 class="font-semibold text-xl">Cape Breton Community Marketplace</h2>
  </x-slot>

  <div class="p-6 space-y-4">
    <div class="flex items-center gap-4">
      @auth
        <a class="underline" href="{{ route('posts.create') }}">Create Post</a>
      @else
        <a class="underline" href="{{ route('login') }}">Login to Post</a>
      @endauth

      <a class="underline" href="{{ route('polls.index') }}">Discussions (Polls)</a>
      <a class="underline" href="/admin">Admin</a>
    </div>

    <form method="GET" class="flex flex-wrap gap-2 items-center">
      <input name="q" value="{{ request('q') }}" placeholder="Search posts" class="border rounded px-3 py-1">
      <select name="category" class="border rounded">
        <option value="">All Categories</option>
        @foreach($categories as $cat)
          <option value="{{ $cat->id }}" @selected(request('category') == $cat->id)>{{ $cat->name }}</option>
          @foreach($cat->children as $child)
            <option value="{{ $child->id }}" @selected(request('category') == $child->id)>— {{ $child->name }}</option>
          @endforeach
        @endforeach
      </select>

      <button class="px-3 py-1 border rounded">Filter</button>

      @if(request('category') || request('q'))
        <a class="underline" href="{{ route('posts.index') }}">Clear</a>
      @endif
    </form>

    <div class="space-y-3">
      @forelse($posts as $post)
        <div class="p-4 border rounded">
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

          <a class="text-lg underline mt-2 inline-block" href="{{ route('posts.show', $post) }}">{{ $post->title }}</a>

          @if($post->images->first())
            <a href="{{ route('posts.show', $post) }}" class="block mt-2">
              <img class="h-24 w-24 rounded border object-cover" src="{{ asset('storage/'.$post->images->first()->path) }}" alt="Post image thumbnail">
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
