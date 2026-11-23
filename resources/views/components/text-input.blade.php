@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full rounded-lg border border-navy bg-white px-3 py-2 text-sm text-ink placeholder:text-muted focus:border-primary focus:ring-2 focus:ring-primary/30 focus:outline-none disabled:bg-surface disabled:text-muted']) }}>
