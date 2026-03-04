<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">New Suggestion</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="mb-4 rounded border bg-green-50 p-3 text-green-800">{{ session('status') }}</div>
            @endif
            <div class="bg-white rounded-lg border p-6">
                @if ($errors->any())
                    <div class="mb-4 rounded border border-red-200 bg-red-50 p-4">
                        <div class="font-semibold mb-2">Fix these:</div>
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('suggestions.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Topic</label>
                        <input name="title" value="{{ old('title') }}" required class="mt-1 w-full rounded-lg border-gray-300" maxlength="140">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Details (optional)</label>
                        <textarea name="body" rows="6" class="mt-1 w-full rounded-lg border-gray-300" maxlength="6000">{{ old('body') }}</textarea>
                    </div>

                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_anonymous" value="1" class="rounded border-gray-300">
                        Post anonymously
                    </label>

                    <button class="rounded-lg bg-gray-900 text-white px-5 py-2.5 font-semibold hover:bg-gray-800" type="submit">
                        Post topic
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
