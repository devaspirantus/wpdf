@props(['active', 'icon' => null])

@php
$classes = ($active ?? false)
            ? 'flex items-center gap-3 rounded-xl bg-primary-container px-3 py-2.5 text-sm font-bold text-on-primary-container focus:outline-none transition duration-150 ease-in-out'
            : 'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-on-surface-variant hover:bg-surface-container hover:text-on-surface focus:outline-none focus:bg-surface-container transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    @if($icon)
        <span class="material-symbols-outlined text-[22px] shrink-0">{{ $icon }}</span>
    @endif
    <span class="min-w-0 flex-1">{{ $slot }}</span>
</a>
