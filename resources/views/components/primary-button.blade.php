<button {{ $attributes->merge(['type' => 'submit', 'class' => 'dga-btn dga-btn-primary px-6 py-2.5 text-base']) }}>
    {{ $slot }}
</button>
