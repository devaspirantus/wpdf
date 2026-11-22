<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('المختبر النظري') }}
    </h2>
</x-slot>

<div class="py-12" dir="rtl">
    <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if ($case === null)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold text-gray-800 mb-4">الحالات قيد المعالجة النظرية</h3>

                    @if ($pendingCases->isEmpty())
                        <p class="text-sm text-gray-500">لا توجد حالات بانتظار المختبر النظري.</p>
                    @else
                        <ul class="divide-y divide-gray-200">
                            @foreach ($pendingCases as $item)
                                <li class="py-3 flex items-center justify-between gap-4">
                                    <div class="text-sm">
                                        <p class="font-medium text-gray-900 flex items-center gap-2">
                                            {{ $item->full_name }}
                                            @if ($item->status === \App\Enums\CaseStatus::returned)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    مُرجع
                                                </span>
                                            @endif
                                        </p>
                                        <p class="text-gray-500 mt-1">
                                            التذكرة: {{ $item->ticket_number }}
                                            &middot; الجواز: {{ $item->passport_number }}
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="selectCase({{ $item->id }})"
                                        class="shrink-0 inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                                    >
                                        فتح الملف
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        @else
            <div class="flex items-center justify-end">
                <button
                    type="button"
                    wire:click="backToList"
                    class="text-sm text-gray-600 hover:text-gray-900"
                >
                    &rarr; رجوع إلى القائمة
                </button>
            </div>

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
                :station="\App\Enums\Station::theory"
                type="theory_photo"
                label="صورة الاختبار النظري"
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
                    تم إرسال الملف إلى محطة المراجعة بنجاح
                </div>
            @endif

            @if ($isReturned)
                <div class="rounded-md bg-yellow-50 border border-yellow-200 p-4 text-yellow-800 text-sm">
                    <p class="font-semibold">تم إرجاع هذا الملف من محطة المراجعة</p>
                    @if ($returnNote)
                        <p class="mt-2">ملاحظة المراجع: {{ $returnNote }}</p>
                    @endif
                    <div class="mt-3 flex items-center justify-end">
                        <x-primary-button wire:click="resubmit">
                            {{ __('إرسال للمراجعة') }}
                        </x-primary-button>
                    </div>
                </div>
            @else
                <div class="flex items-center justify-end">
                    <x-primary-button wire:click="advance" :disabled="$hasMissing">
                        {{ __('المحطة التالية') }}
                    </x-primary-button>
                </div>
            @endif

            <x-station-summary
                :currentStation="\App\Enums\Station::theory"
                :case="$case"
            />
        @endif
    </div>
</div>
