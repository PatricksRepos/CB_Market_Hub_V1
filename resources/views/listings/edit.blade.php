<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Listing</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg border p-6">
                <form method="POST" action="{{ route('listings.update', $listing) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input name="title" value="{{ old('title', $listing->title) }}" required class="mt-1 w-full rounded-lg border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Details</label>
                        <textarea name="body" rows="6" class="mt-1 w-full rounded-lg border-gray-300">{{ old('body', $listing->body) }}</textarea>
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Price</label>
                            <input type="number" step="0.01" min="0" name="price" value="{{ old('price', $listing->price_cents !== null ? number_format($listing->price_cents / 100, 2, '.', '') : '') }}" class="mt-1 w-full rounded-lg border-gray-300">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Location</label>
                            <input name="location" value="{{ old('location', $listing->location) }}" class="mt-1 w-full rounded-lg border-gray-300">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Category</label>
                        <select name="category" class="mt-1 w-full rounded-lg border-gray-300" required>
                            @foreach(['general','buy','sell','trade','services'] as $c)
                                <option value="{{ $c }}" @selected(old('category', $listing->category) === $c)>{{ ucfirst($c) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button class="rounded-lg bg-gray-900 text-white px-5 py-2.5 font-semibold hover:bg-gray-800" type="submit">Save changes</button>
                        <a href="{{ route('listings.show', $listing) }}" class="rounded-lg border px-4 py-2 hover:bg-gray-50">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
