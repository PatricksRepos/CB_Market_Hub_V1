<x-app-layout>
  <x-slot name="header"><h2 class="font-semibold text-xl">Marketplace & Posts</h2></x-slot>
  <div class="p-6">
    <a class="underline" href="{{ route('posts.create') }}">Create Post</a>
    <div class="mt-4 space-y-3">
      @foreach($posts as $post)
        <div class="p-4 border rounded">
          <div class="text-sm opacity-70">{{ $post->type }} @if($post->is_promoted) • PROMOTED @endif</div>
          <a class="text-lg underline" href="{{ route('posts.show',$post) }}">{{ $post->title }}</a>
          <div class="text-sm opacity-70">{{ $post->location }} @if($post->price) • ${{ $post->price }} @endif</div>
        </div>
      @endforeach
    </div>
    <div class="mt-6">{{ $posts->links() }}</div>
  </div>
</x-app-layout>
