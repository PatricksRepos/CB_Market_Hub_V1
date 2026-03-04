<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl">Edit Post</h2></x-slot>
    <div class="p-6">
        <form method="POST" action="{{ route('posts.update',$post) }}" enctype="multipart/form-data" class="space-y-4 bg-white border rounded-xl p-6 max-w-3xl">
            @csrf @method('PUT')

            @php
                $currentParent = $post->category?->parent_id ? $post->category->parent_id : $post->category_id;
                $currentSub = $post->category?->parent_id ? $post->category_id : null;
                $categoryTree = $categories->map(fn($parent) => [
                    'id' => $parent->id,
                    'name' => $parent->name,
                    'children' => $parent->children->map(fn($child) => ['id' => $child->id, 'name' => $child->name])->values(),
                ])->values();
            @endphp

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label>Category</label>
                    <select id="category_id" name="category_id" class="border rounded w-full">
                        <option value="">Select Category</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $currentParent) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label>Subcategory</label>
                    <select id="subcategory_id" name="subcategory_id" class="border rounded w-full">
                        <option value="">Optional subcategory</option>
                    </select>
                </div>
            </div>

            <div>
                <label>Type</label>
                <select name="type" class="border rounded w-full">
                    <option value="marketplace" @selected(old('type',$post->type)==='marketplace')>Marketplace</option>
                    <option value="business" @selected(old('type',$post->type)==='business')>Business / Service</option>
                </select>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label>Marketplace Action</label>
                    <select name="marketplace_action" class="border rounded w-full">
                        <option value="" @selected(old('marketplace_action', $post->marketplace_action) === null)>None</option>
                        <option value="buy" @selected(old('marketplace_action', $post->marketplace_action) === 'buy')>Buy</option>
                        <option value="sell" @selected(old('marketplace_action', $post->marketplace_action) === 'sell')>Sell</option>
                        <option value="trade" @selected(old('marketplace_action', $post->marketplace_action) === 'trade')>Trade</option>
                    </select>
                </div>
                <div>
                    <label>Price (optional)</label>
                    <input name="price" type="number" step="0.01" min="0" value="{{ old('price', $post->price) }}" class="border rounded w-full">
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label>Location</label>
                    <input name="location" class="border rounded w-full" value="{{ old('location', $post->location) }}">
                </div>
                <div>
                    <label>Condition</label>
                    <input name="condition" class="border rounded w-full" value="{{ old('condition', $post->condition) }}">
                </div>
            </div>

            <div><label>Title</label><input name="title" class="border rounded w-full" value="{{ old('title',$post->title) }}" required></div>
            <div><label>Body</label><textarea name="body" class="border rounded w-full" rows="6" required>{{ old('body',$post->body) }}</textarea></div>
            <div><label>Add images</label><input type="file" name="images[]" multiple accept="image/*"></div>
            <button class="px-4 py-2 border rounded">Save</button>
        </form>
    </div>

    <script>
        (function () {
            const tree = @json($categoryTree);
            const categoryEl = document.getElementById('category_id');
            const subcategoryEl = document.getElementById('subcategory_id');
            const oldSubcategory = @json(old('subcategory_id', $currentSub));

            function rebuildSubcategories() {
                const selected = parseInt(categoryEl.value || '0', 10);
                const category = tree.find(c => c.id === selected);
                subcategoryEl.innerHTML = '<option value="">Optional subcategory</option>';

                if (!category || !category.children || category.children.length === 0) {
                    subcategoryEl.disabled = true;
                    return;
                }

                subcategoryEl.disabled = false;
                category.children.forEach(child => {
                    const option = document.createElement('option');
                    option.value = child.id;
                    option.textContent = child.name;
                    if (String(child.id) === String(oldSubcategory)) option.selected = true;
                    subcategoryEl.appendChild(option);
                });
            }

            categoryEl.addEventListener('change', rebuildSubcategories);
            rebuildSubcategories();
        })();
    </script>
</x-app-layout>
