<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Edit Suggestion</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded-lg border p-6">
                <form method="POST" action="{{ route('suggestions.update', $suggestion) }}" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Title</label>
                        <input name="title" value="{{ old('title', $suggestion->title) }}" required class="mt-1 w-full rounded-lg border-gray-300" maxlength="140">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Details</label>
                        <textarea name="body" rows="6" class="mt-1 w-full rounded-lg border-gray-300" maxlength="6000">{{ old('body', $suggestion->body) }}</textarea>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_anonymous" value="1" class="rounded border-gray-300" @checked(old('is_anonymous', $suggestion->is_anonymous))>
                        Post anonymously
                    </label>

                    <div class="flex items-center gap-2">
                        <button class="rounded-lg bg-gray-900 text-white px-5 py-2.5 font-semibold hover:bg-gray-800" type="submit">Save changes</button>
                        <a href="{{ route('suggestions.show', $suggestion) }}" class="rounded-lg border px-4 py-2 hover:bg-gray-50">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
