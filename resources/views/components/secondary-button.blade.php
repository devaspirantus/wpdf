<button {{ $attributes->merge(['type' => 'button', 'class' => 'dga-btn dga-btn-outline px-6 py-2.5 text-base disabled:opacity-40']) }}>
    {{ $slot }}
</button>
