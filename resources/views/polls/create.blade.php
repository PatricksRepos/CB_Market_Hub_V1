<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Create Poll</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded-lg p-6">
                @if ($errors->any())
                    <div class="mb-4 rounded border border-red-200 bg-red-50 p-4">
                        <div class="font-semibold mb-2">Fix these:</div>
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('polls.store') }}" class="space-y-6"
                      x-data="{
                        options: (function () {
                          const old = @json(old('options', []));
                          if (old && old.length) return old;
                          return ['', ''];
                        })(),
                        add() { if (this.options.length < 8) this.options.push(''); },
                        remove(i) { if (this.options.length > 2) this.options.splice(i, 1); }
                      }">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Question</label>
                        <input name="question" value="{{ old('question') }}" required maxlength="200"
                               class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Poll duration (minutes)</label>
                            <input name="duration_minutes" type="number" min="1" max="43200"
                                   value="{{ old('duration_minutes') }}"
                                   placeholder="e.g. 60 (1 hour)"
                                   class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm">
                            <p class="text-xs text-gray-500 mt-1">If you choose “Private until end”, duration is required.</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Results visibility</label>
                            <select name="results_visibility" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm" required>
                                <option value="after_end" @selected(old('results_visibility','after_end')==='after_end')>Private until poll ends</option>
                                <option value="after_vote" @selected(old('results_visibility')==='after_vote')>Show results after vote</option>
                                <option value="always" @selected(old('results_visibility')==='always')>Always public</option>
                            </select>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <div class="text-sm font-medium text-gray-700">Options (2–8)</div>
                            <button type="button" @click="add()"
                                    class="text-sm underline text-gray-900"
                                    :class="options.length >= 8 ? 'opacity-40 cursor-not-allowed' : ''"
                                    :disabled="options.length >= 8">
                                + Add option
                            </button>
                        </div>

                        <template x-for="(opt, idx) in options" :key="idx">
                            <div class="flex gap-2">
                                <input class="block w-full rounded-lg border-gray-300 shadow-sm"
                                       :name="'options['+idx+']'"
                                       x-model="options[idx]"
                                       :placeholder="'Option ' + (idx+1)"
                                       maxlength="120">
                                <button type="button" @click="remove(idx)"
                                        class="rounded-lg border px-3 py-2"
                                        :class="options.length <= 2 ? 'opacity-40 cursor-not-allowed' : ''"
                                        :disabled="options.length <= 2">
                                    –
                                </button>
                            </div>
                        </template>

                        <p class="text-xs text-gray-500">Fill at least 2 options.</p>
                    </div>

                    <div class="flex gap-3">
                        <button type="submit" class="rounded-lg bg-gray-900 px-5 py-2.5 text-white font-semibold hover:bg-gray-800">
                            Create Poll
                        </button>
                        <a href="{{ route('polls.index') }}" class="rounded-lg border px-5 py-2.5">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
