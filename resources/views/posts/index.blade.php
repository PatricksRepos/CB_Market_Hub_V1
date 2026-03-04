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

          <a class="text-lg underline" href="{{ route('posts.show', $post) }}">{{ $post->title }}</a>

          <div class="text-sm opacity-70">
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
