<x-app-layout>
  <x-slot name="header"><h2 class="font-semibold text-xl">Create Post</h2></x-slot>
  <div class="p-6">
    <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data" class="space-y-4">
      @csrf
      <div>
        <label>Type</label>
        <select name="type" class="border rounded w-full">
          <option value="marketplace">Marketplace</option>
          <option value="business">Business / Service</option>
          <option value="discussion">Discussion</option>
        </select>
      </div>
      <div><label>Title</label><input name="title" class="border rounded w-full" required></div>
      <div><label>Body</label><textarea name="body" class="border rounded w-full" rows="6" required></textarea></div>

      <div class="grid grid-cols-2 gap-4">
        <div><label>Action</label><select name="marketplace_action" class="border rounded w-full">
          <option value="">(none)</option><option value="buy">Buy</option><option value="sell">Sell</option><option value="trade">Trade</option>
        </select></div>
        <div><label>Price</label><input name="price" type="number" step="0.01" class="border rounded w-full"></div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div><label>Location</label><input name="location" class="border rounded w-full"></div>
        <div><label>Condition</label><input name="condition" class="border rounded w-full"></div>
      </div>

      <div class="flex items-center gap-2">
        <input type="checkbox" name="is_anonymous" value="1">
        <label>Display as anonymous</label>
        <input name="anonymous_name" placeholder="Anon name (optional)" class="border rounded">
      </div>

      <div><label>Images</label><input type="file" name="images[]" multiple accept="image/*"></div>

      <button class="px-4 py-2 border rounded">Post</button>
    </form>
  </div>
</x-app-layout>
