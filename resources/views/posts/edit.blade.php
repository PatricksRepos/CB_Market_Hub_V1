<x-app-layout>
  <x-slot name="header"><h2 class="font-semibold text-xl">Edit Post</h2></x-slot>
  <div class="p-6">
    <form method="POST" action="{{ route('posts.update',$post) }}" enctype="multipart/form-data" class="space-y-4">
      @csrf @method('PUT')
      <div>
        <label>Type</label>
        <select name="type" class="border rounded w-full">
          <option value="marketplace" @selected($post->type==='marketplace')>Marketplace</option>
          <option value="business" @selected($post->type==='business')>Business / Service</option>
          <option value="discussion" @selected($post->type==='discussion')>Discussion</option>
        </select>
      </div>
      <div><label>Title</label><input name="title" class="border rounded w-full" value="{{ $post->title }}" required></div>
      <div><label>Body</label><textarea name="body" class="border rounded w-full" rows="6" required>{{ $post->body }}</textarea></div>
      <div><label>Add images</label><input type="file" name="images[]" multiple accept="image/*"></div>
      <button class="px-4 py-2 border rounded">Save</button>
    </form>
  </div>
</x-app-layout>
