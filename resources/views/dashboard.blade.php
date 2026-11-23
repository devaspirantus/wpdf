@php
    $station = match (auth()->user()->role?->value) {
        'reception' => ['route' => route('reception'), 'label' => 'محطة الاستقبال'],
        'theory' => ['route' => route('theory'), 'label' => 'بدء الاختبار النظري'],
        'practical' => null,
        'review' => ['route' => route('review'), 'label' => 'محطة المراجعة'],
        default => null,
    };
@endphp
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-ink leading-tight">
            {{ __('لوحة التحكم') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="dga-card">
                <div class="dga-card-body">
                    <p class="mb-1 text-ink">
                        {{ __('مرحباً بعودتك،') }}
                        <span class="font-bold">{{ auth()->user()->name }}</span>
                    </p>
                    <p class="text-sm text-muted mb-4">
                        {{ __('الدور:') }}
                        <span class="font-bold">{{ auth()->user()->role?->label() ?? __('غير محدد') }}</span>
                    </p>
                    @if($station)
                        <a href="{{ $station['route'] }}"
                            class="dga-btn dga-btn-primary">
                            {{ __($station['label']) }}
                            <svg class="me-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                    @elseif (! in_array(auth()->user()->role?->value, ['theory', 'practical'], true))
                        <p class="text-sm font-semibold text-warning">{{ __('لم يتم تحديد محطة لمستخدمك بعد. تواصل مع الإدارة.') }}</p>
                    @endif
                </div>
            </div>

            @if (auth()->user()->role?->value === 'theory')
                <livewire:theory-pending-cases />
            @elseif (auth()->user()->role?->value === 'practical')
                <livewire:practical-pending-cases />
            @endif
        </div>
    </div>
</x-app-layout>
