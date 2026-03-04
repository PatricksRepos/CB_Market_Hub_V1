<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Event</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg border p-6">
                <form method="POST" action="{{ route('events.update', $event) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input name="title" value="{{ old('title', $event->title) }}" required class="mt-1 w-full rounded-lg border-gray-300" maxlength="160">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Description</label>
                        <textarea name="description" rows="6" class="mt-1 w-full rounded-lg border-gray-300" maxlength="6000">{{ old('description', $event->description) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Location</label>
                        <input name="location" value="{{ old('location', $event->location) }}" class="mt-1 w-full rounded-lg border-gray-300" maxlength="160">
                    </div>

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Starts at</label>
                            <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $event->starts_at?->format('Y-m-d\TH:i')) }}" required class="mt-1 w-full rounded-lg border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Ends at</label>
                            <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $event->ends_at?->format('Y-m-d\TH:i')) }}" class="mt-1 w-full rounded-lg border-gray-300">
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        <button class="rounded-lg bg-gray-900 text-white px-5 py-2.5 font-semibold hover:bg-gray-800" type="submit">Save changes</button>
                        <a href="{{ route('events.show', $event) }}" class="rounded-lg border px-4 py-2 hover:bg-gray-50">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
