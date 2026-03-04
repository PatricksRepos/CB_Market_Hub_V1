<x-app-layout>
  <x-slot name="header"><h2 class="font-semibold text-xl">{{ $post->title }}</h2></x-slot>

  <div class="p-6 space-y-4">
    <div class="flex items-center gap-2 text-sm opacity-70">
      @if(!$post->is_anonymous)
        @if($post->user?->avatar_url)
          <img src="{{ $post->user->avatar_url }}" alt="{{ $post->user->name }} avatar" class="h-16 w-16 rounded-full object-cover border">
        @else
          <div class="h-16 w-16 rounded-full bg-gray-200 flex items-center justify-center text-gray-700 text-xs font-semibold">{{ strtoupper(substr($post->user?->name ?? 'U',0,1)) }}</div>
        @endif
      @endif

      @if($post->category)
        {{ $post->category->parent ? $post->category->parent->name.' › '.$post->category->name : $post->category->name }} •
      @endif
      Posted by:
      @if($post->is_anonymous)
        {{ $post->anonymous_name ?? 'Anon' }}
      @else
        <a class="underline" href="{{ route('profiles.show', $post->user) }}">{{ $post->user->name }}</a>
      @endif
      • {{ $post->created_at->diffForHumans() }}
    </div>

    <div class="border rounded p-4 whitespace-pre-wrap">{{ $post->body }}</div>

    <x-reaction-bar :model="$post" type="post" />

    @if($post->marketplace_action && !$post->is_anonymous && $post->user)
      <a
        href="{{ route('chat.index', ['message' => 'Hi '.$post->user->name.', I\'m interested in your post: '.$post->title]) }}"
        class="inline-flex items-center rounded-lg border px-3 py-2 text-sm hover:bg-gray-50"
      >
        Contact {{ $post->user->name }}
      </a>
    @endif

    @if($post->images->count())
      <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
        @foreach($post->images as $img)
          <img class="rounded border object-cover h-44 w-full" src="{{ asset('storage/'.$img->path) }}" alt="image">
        @endforeach
      </div>
    @endif

    @auth
      <div class="flex gap-3">
        <a class="underline" href="{{ route('posts.edit',$post) }}">Edit</a>
        <form method="POST" action="{{ route('posts.destroy',$post) }}">
          @csrf @method('DELETE')
          <button class="underline" onclick="return confirm('Hard delete this post?')">Delete</button>
        </form>
      </div>

      <hr>

      <form method="POST" action="{{ route('reports.store') }}" class="space-y-2">
        @csrf
        <input type="hidden" name="reportable_type" value="post">
        <input type="hidden" name="reportable_id" value="{{ $post->id }}">

        <label>Report this post</label>
        <select name="reason" class="border rounded">
          <option value="spam">Spam</option>
          <option value="scam">Scam</option>
          <option value="hate">Hate</option>
          <option value="harassment">Harassment</option>
          <option value="illegal">Illegal</option>
          <option value="other">Other</option>
        </select>

        <input name="details" class="border rounded w-full" placeholder="Optional details">
        <button class="px-3 py-1 border rounded">Submit report</button>
      </form>
    @else
      <div class="border rounded p-4">
        <a class="underline" href="{{ route('login') }}">Login</a> to report or create posts.
      </div>
    @endauth
  </div>
</x-app-layout>
