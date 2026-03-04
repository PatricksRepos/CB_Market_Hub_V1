<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-2xl text-gray-800">Create Post</h2>
            <a href="{{ route('posts.index') }}" class="text-sm text-gray-600 hover:underline">Back to posts</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl border shadow-sm p-6 md:p-8">
                <p class="text-sm text-gray-500 mb-6">Share with the community using a clean title, clear category, and optional photos.</p>

                @if($errors->any())
                    <div class="mb-5 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                        <div class="font-semibold mb-2">Please fix the following:</div>
                        <ul class="list-disc pl-5 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    @php
                        $categoryTree = $categories->map(function ($parent) {
                            return [
                                'id' => $parent->id,
                                'name' => $parent->name,
                                'children' => $parent->children->map(fn($child) => ['id' => $child->id, 'name' => $child->name])->values(),
                            ];
                        })->values();
                    @endphp

                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Category</label>
                            <select id="category_id" name="category_id" class="mt-1 w-full rounded-lg border-gray-300" required>
                                <option value="">Choose a category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Subcategory</label>
                            <select id="subcategory_id" name="subcategory_id" class="mt-1 w-full rounded-lg border-gray-300">
                                <option value="">Optional subcategory</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Post Type</label>
                            <select name="type" class="mt-1 w-full rounded-lg border-gray-300">
                                <option value="marketplace" @selected(old('type')==='marketplace')>Marketplace</option>
                                <option value="business" @selected(old('type')==='business')>Business / Service</option>
                                <option value="discussion" @selected(old('type','discussion')==='discussion')>Discussion</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Marketplace Action</label>
                            <select name="marketplace_action" class="mt-1 w-full rounded-lg border-gray-300">
                                <option value="">None</option>
                                <option value="buy" @selected(old('marketplace_action')==='buy')>Buy</option>
                                <option value="sell" @selected(old('marketplace_action')==='sell')>Sell</option>
                                <option value="trade" @selected(old('marketplace_action')==='trade')>Trade</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input name="title" value="{{ old('title') }}" class="mt-1 w-full rounded-lg border-gray-300" maxlength="180" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Body</label>
                        <textarea name="body" rows="7" class="mt-1 w-full rounded-lg border-gray-300" required>{{ old('body') }}</textarea>
                    </div>

                    <div class="grid md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Price (optional)</label>
                            <input name="price" type="number" step="0.01" min="0" value="{{ old('price') }}" class="mt-1 w-full rounded-lg border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Location</label>
                            <input name="location" value="{{ old('location') }}" class="mt-1 w-full rounded-lg border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Condition</label>
                            <input name="condition" value="{{ old('condition') }}" class="mt-1 w-full rounded-lg border-gray-300">
                        </div>
                    </div>

                    <div class="rounded-lg border p-4 bg-gray-50">
                        <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700">
                            <input type="checkbox" name="is_anonymous" value="1" @checked(old('is_anonymous')) class="rounded border-gray-300">
                            Post anonymously
                        </label>
                        <input name="anonymous_name" value="{{ old('anonymous_name') }}" placeholder="Display name (optional)" class="mt-3 w-full rounded-lg border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Images</label>
                        <input type="file" name="images[]" multiple accept="image/*" class="mt-1 block w-full text-sm text-gray-600">
                    </div>

                    <div class="pt-2 flex items-center gap-2">
                        <button class="rounded-lg bg-gray-900 text-white px-5 py-2.5 font-semibold hover:bg-gray-800" type="submit">Publish post</button>
                        <a href="{{ route('posts.index') }}" class="rounded-lg border px-4 py-2 hover:bg-gray-50">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const tree = @json($categoryTree);
            const categoryEl = document.getElementById('category_id');
            const subcategoryEl = document.getElementById('subcategory_id');
            const oldSubcategory = @json(old('subcategory_id'));

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

            categoryEl.addEventListener('change', () => {
                subcategoryEl.dataset.userChanged = '1';
                rebuildSubcategories();
            });

            rebuildSubcategories();
        })();
    </script>
</x-app-layout>
