<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">New Listing</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg border p-6">
                @if ($errors->any())
                    <div class="mb-4 rounded border border-red-200 bg-red-50 p-4">
                        <div class="font-semibold mb-2">Fix these:</div>
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('listings.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input name="title" value="{{ old('title') }}" required class="mt-1 w-full rounded-lg border-gray-300">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Category</label>
                            <select name="category" class="mt-1 w-full rounded-lg border-gray-300">
                                <option value="general" @selected(old('category')==='general')>General</option>
                                <option value="buy" @selected(old('category')==='buy')>Buy</option>
                                <option value="sell" @selected(old('category')==='sell')>Sell</option>
                                <option value="trade" @selected(old('category')==='trade')>Trade</option>
                                <option value="services" @selected(old('category')==='services')>Services</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Price (optional)</label>
                            <input name="price" type="number" step="0.01" min="0" class="mt-1 w-full rounded-lg border-gray-300" value="{{ old('price') }}">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Location (optional)</label>
                        <input name="location" class="mt-1 w-full rounded-lg border-gray-300" value="{{ old('location') }}">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Details</label>
                        <textarea name="body" rows="5" class="mt-1 w-full rounded-lg border-gray-300">{{ old('body') }}</textarea>
                    </div>

                    <button class="rounded-lg bg-gray-900 text-white px-5 py-2.5 font-semibold hover:bg-gray-800" type="submit">
                        Post listing
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
