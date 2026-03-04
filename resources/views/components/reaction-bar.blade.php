@props([
    'model',
    'type',
])

@php
    $allowedEmojis = ['👍', '❤️', '😂', '😮', '🎉'];
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
<div class="mt-4 flex flex-wrap items-center gap-2">
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
                    class="inline-flex items-center gap-1 rounded-full border px-2.5 py-1 text-sm {{ $isSelected ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-300 bg-white text-gray-700 hover:bg-gray-50' }}"
                >
                    <span>{{ $emoji }}</span>
                    <span class="text-xs">{{ $count }}</span>
                </button>
            </form>
        @else
            <a href="{{ route('login') }}" class="inline-flex items-center gap-1 rounded-full border border-gray-300 bg-white px-2.5 py-1 text-sm text-gray-700 hover:bg-gray-50">
                <span>{{ $emoji }}</span>
                <span class="text-xs">{{ $count }}</span>
            </a>
        @endauth
    @endforeach
</div>
@endif
