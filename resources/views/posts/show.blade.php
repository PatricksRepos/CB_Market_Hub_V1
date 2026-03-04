<x-app-layout>
  <x-slot name="header"><h2 class="font-semibold text-xl">{{ $post->title }}</h2></x-slot>

  <div class="p-6 space-y-4">
    <div class="text-sm opacity-70">
      @if($post->category)
      {{ $post->category->parent ? $post->category->parent->name.' › '.$post->category->name : $post->category->name }} •
    @endif
      Posted by:
      @if($post->is_anonymous) {{ $post->anonymous_name ?? 'Anon' }}
      @else {{ $post->user->name }}
      @endif
      • {{ $post->created_at->diffForHumans() }}
    </div>

    <div class="border rounded p-4 whitespace-pre-wrap">{{ $post->body }}</div>

    @if($post->images->count())
      <div class="grid grid-cols-3 gap-3">
        @foreach($post->images as $img)
          <img class="rounded border" src="{{ asset('storage/'.$img->path) }}" alt="image">
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
