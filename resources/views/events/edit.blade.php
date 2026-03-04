<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Event</h2>
            <a href="{{ route('events.show', $event) }}" class="text-sm text-gray-600 hover:underline">Back</a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                @if ($errors->any())
                    <div class="mb-4 p-3 rounded bg-red-50 text-red-700 text-sm">
                        <ul class="list-disc list-inside">
                            @foreach ($errors->all() as $e)
                                <li>{{ $e }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('events.update', $event) }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    @method('PATCH')

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

                    <div class="space-y-2">
                        <label for="event_image" class="block text-sm font-medium text-gray-700">Event image (optional)</label>
                        <input id="event_image" type="file" name="image" accept="image/*" class="mt-1 block w-full text-sm text-gray-600">

                        @if($event->image_path)
                            <img id="event_image_preview" src="{{ asset('storage/'.$event->image_path) }}" class="h-36 w-full max-w-xs rounded border object-cover" alt="Current event image">
                            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                                <input type="checkbox" name="remove_image" value="1" class="rounded border-gray-300">
                                Remove current image
                            </label>
                        @else
                            <img id="event_image_preview" class="hidden h-36 w-full max-w-xs rounded border object-cover" alt="Selected event image preview">
                        @endif
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

    <script>
        (() => {
            const imageInput = document.getElementById('event_image');
            const imagePreview = document.getElementById('event_image_preview');

            if (!imageInput || !imagePreview) {
                return;
            }

            imageInput.addEventListener('change', () => {
                const file = imageInput.files?.[0];

                if (!file || !file.type.startsWith('image/')) {
                    return;
                }

                imagePreview.src = URL.createObjectURL(file);
                imagePreview.classList.remove('hidden');
            });
        })();
    </script>
</x-app-layout>
