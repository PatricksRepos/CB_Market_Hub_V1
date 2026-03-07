@props([
    'user' => null,
    'name' => null,
    'size' => 'h-10 w-10',
    'class' => '',
])

@php
    $displayName = trim((string) ($name ?? $user?->name ?? 'User'));
    $initial = strtoupper(substr($displayName !== '' ? $displayName : 'U', 0, 1));
    $avatarUrl = $user?->avatar_url;
@endphp

<div class="{{ trim("relative inline-flex shrink-0 overflow-hidden rounded-full border {$size} {$class}") }}">
    @if($avatarUrl)
        <img
            src="{{ $avatarUrl }}"
            alt="{{ $displayName }} avatar"
            class="h-full w-full object-cover"
            loading="lazy"
            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
        >
    @endif

    <span
        class="{{ $avatarUrl ? 'hidden' : 'flex' }} h-full w-full items-center justify-center bg-gray-200 text-gray-700 text-sm font-semibold"
        aria-label="{{ $displayName }} initials"
    >
        {{ $initial }}
    </span>
</div>
