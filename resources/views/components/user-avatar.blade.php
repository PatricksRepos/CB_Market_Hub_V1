@props([
    'user' => null,
    'name' => null,
    'size' => 'md',
])

@php
    $displayName = trim((string) ($name ?? $user?->name ?? 'User'));
    $initial = strtoupper(substr($displayName !== '' ? $displayName : 'U', 0, 1));
    $avatarUrl = $user?->avatar_url;

    $sizeMap = [
        'xs' => 28,
        'sm' => 36,
        'md' => 40,
        'lg' => 64,
        'xl' => 96,
    ];

    $avatarPixels = $sizeMap[$size] ?? $sizeMap['md'];
@endphp

<div {{ $attributes->class('relative inline-flex shrink-0 overflow-hidden rounded-full border border-gray-300') }} style="width: {{ $avatarPixels }}px; height: {{ $avatarPixels }}px;">
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
