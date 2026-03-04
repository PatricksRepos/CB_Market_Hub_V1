<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Event</h2>
            <a href="{{ route('events.index') }}" class="text-sm text-gray-600 hover:underline">Back</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @if ($errors->any())
                    <div class="mb-4 p-3 rounded bg-red-50 text-red-700 text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('events.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input name="title" value="{{ old('title') }}" class="mt-1 w-full rounded border-gray-300" required />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="4" class="mt-1 w-full rounded border-gray-300">{{ old('description') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Location</label>
                        <input name="location" value="{{ old('location') }}" class="mt-1 w-full rounded border-gray-300" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Starts at</label>
                            <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}" class="mt-1 w-full rounded border-gray-300" required />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ends at (optional)</label>
                            <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}" class="mt-1 w-full rounded border-gray-300" />
                        </div>
                    </div>

                    <div class="pt-2">
                        <button class="px-4 py-2 rounded bg-gray-900 text-white hover:bg-gray-800">Create Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
