@props([
    'model',
    'type',
])

@php
    $allowedEmojis = \App\Models\Reaction::ALLOWED_EMOJIS;
    $reactionsEnabled = \App\Support\Reactions::isEnabled();
    $reactions = $reactionsEnabled
        ? ($model->relationLoaded('reactions') ? $model->reactions : $model->reactions()->get(['id', 'user_id', 'emoji']))
        : collect();

    $reactionCounts = collect($allowedEmojis)
        ->mapWithKeys(fn (string $emoji) => [$emoji => 0])
        ->merge($reactions->groupBy('emoji')->map->count())
        ->only($allowedEmojis);

    $myReaction = auth()->check() ? $reactions->firstWhere('user_id', auth()->id())?->emoji : null;
@endphp

@if ($reactionsEnabled)
<div class="mt-4 rounded-xl border border-gray-200 bg-gray-50 p-3">
    <div class="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-500">Reactions</div>
    <div class="flex flex-wrap items-center gap-2">
        @foreach ($allowedEmojis as $emoji)
            @php
                $isSelected = $myReaction === $emoji;
                $count = (int) ($reactionCounts[$emoji] ?? 0);
            @endphp

            @auth
                <form method="POST" action="{{ route('reactions.store') }}">
                    @csrf
                    <input type="hidden" name="type" value="{{ $type }}">
                    <input type="hidden" name="id" value="{{ $model->id }}">
                    <input type="hidden" name="emoji" value="{{ $emoji }}">
                    <button
                        type="submit"
                        class="inline-flex min-w-[64px] items-center justify-center gap-1.5 rounded-full border px-3 py-1.5 text-sm font-medium shadow-sm transition {{ $isSelected ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-gray-200 bg-white text-gray-700 hover:border-indigo-300 hover:text-indigo-700 hover:shadow' }}"
                    >
                        <span class="text-base leading-none">{{ $emoji }}</span>
                        <span class="rounded-full bg-black/5 px-1.5 py-0.5 text-xs {{ $isSelected ? 'bg-white/20 text-white' : 'text-gray-600' }}">{{ $count }}</span>
                    </button>
                </form>
            @else
                <a href="{{ route('login') }}" class="inline-flex min-w-[64px] items-center justify-center gap-1.5 rounded-full border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 shadow-sm transition hover:border-indigo-300 hover:text-indigo-700 hover:shadow">
                    <span class="text-base leading-none">{{ $emoji }}</span>
                    <span class="rounded-full bg-black/5 px-1.5 py-0.5 text-xs text-gray-600">{{ $count }}</span>
                </a>
            @endauth
        @endforeach
    </div>
</div>
@endif
