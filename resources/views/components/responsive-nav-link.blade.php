@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-r-4 border-primary text-start text-base font-bold text-primary-dark bg-primary-pastel/25 focus:outline-none focus:text-primary-dark focus:bg-primary-pastel/40 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-r-4 border-transparent text-start text-base font-medium text-muted hover:text-ink hover:bg-surface hover:border-line focus:outline-none focus:text-ink focus:bg-surface focus:border-line transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
