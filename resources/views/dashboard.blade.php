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

    <div class="mx-auto max-w-6xl space-y-6">
        <section class="relative overflow-hidden rounded-2xl bg-navy px-5 py-6 text-white shadow-sm sm:px-8 sm:py-8">
            <div class="absolute left-0 top-0 h-full w-1/2 bg-gradient-to-r from-transparent to-primary/25"></div>
            <div class="relative">
                <p class="text-sm text-white/70">مرحباً بعودتك</p>
                <h3 class="mt-1 text-2xl font-bold sm:text-3xl">{{ auth()->user()->name }}</h3>
                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <span class="dga-badge bg-white/10 text-white">
                        <span class="material-symbols-outlined me-1 text-lg">badge</span>
                        {{ auth()->user()->role?->label() ?? __('غير محدد') }}
                    </span>
                    @if($station)
                        <a href="{{ $station['route'] }}" class="dga-btn bg-white text-navy hover:bg-primary-pastel">
                            {{ __($station['label']) }}
                            <span class="material-symbols-outlined text-lg">arrow_back</span>
                        </a>
                    @elseif (! in_array(auth()->user()->role?->value, ['theory', 'practical'], true))
                        <p class="text-sm font-semibold text-warning">{{ __('لم يتم تحديد محطة لمستخدمك بعد. تواصل مع الإدارة.') }}</p>
                    @endif
                </div>
            </div>
        </section>

        @if (auth()->user()->role?->value === 'theory')
            <livewire:theory-pending-cases />
        @elseif (auth()->user()->role?->value === 'practical')
            <livewire:practical-pending-cases />
        @endif
    </div>
</x-app-layout>
