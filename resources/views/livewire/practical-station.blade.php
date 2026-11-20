<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('المختبر العملي') }}
        </h2>
    </x-slot>

    <div class="py-12" dir="rtl">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-4">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold text-gray-800 mb-3">بيانات العامل</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-gray-500">الاسم</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $case->full_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">رقم الجواز</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $case->passport_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">رقم التذكرة</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $case->ticket_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">الحالة الحالية</dt>
                            <dd class="mt-1 font-medium text-gray-900">{{ $case->status->arabicName() }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <livewire:camera-capture
                :case-id="$case->id"
                :station="\App\Enums\Station::practical"
                type="practical_photo"
                label="صورة الاختبار العملي"
            />

            <div class="bg-white rounded-lg border border-gray-200 p-4">
                <h3 class="font-semibold text-gray-800 mb-3">متطلبات استكمال المحطة</h3>
                <ul class="space-y-2 text-sm">
                    @foreach ($requirements as $requirement)
                        <li class="flex items-center gap-2 {{ $requirement['met'] ? 'text-green-700' : 'text-gray-600' }}">
                            <span>{{ $requirement['met'] ? '✓' : '○' }}</span>
                            <span>{{ $requirement['label'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            @if ($advanceError)
                <div class="rounded-md bg-red-50 border border-red-200 p-4 text-red-800 text-sm">
                    <p class="font-semibold">{{ $advanceError }}</p>
                    @if (count($missingRequirements) > 0)
                        <ul class="mt-2 list-disc pr-5">
                            @foreach ($missingRequirements as $missing)
                                <li>{{ $missing }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif

            @if ($advancedTo)
                <div class="rounded-md bg-green-50 border border-green-200 p-4 text-green-800 text-sm">
                    تم الانتقال إلى المحطة التالية بنجاح
                </div>
            @endif

            <div class="flex items-center justify-end">
                <x-primary-button wire:click="advance" :disabled="$hasMissing">
                    {{ __('المحطة التالية') }}
                </x-primary-button>
            </div>
        </div>
    </div>
</x-app-layout>
