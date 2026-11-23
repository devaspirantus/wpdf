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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('لوحة التحكم') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <p class="mb-1">{{ __('مرحباً بعودتك،') }} <span class="font-semibold">{{ auth()->user()->name }}</span></p>
                    <p class="text-sm text-gray-600 mb-4">
                        {{ __('الدور:') }}
                        <span class="font-semibold">{{ auth()->user()->role?->label() ?? __('غير محدد') }}</span>
                    </p>
                    @if($station)
                        <a href="{{ $station['route'] }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-semibold rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            {{ __($station['label']) }}
                            <svg class="me-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                        </a>
                    @elseif (! in_array(auth()->user()->role?->value, ['theory', 'practical'], true))
                        <p class="text-sm text-amber-700">{{ __('لم يتم تحديد محطة لمستخدمك بعد. تواصل مع الإدارة.') }}</p>
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
