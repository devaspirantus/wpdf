<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('المختبر النظري') }}
    </h2>
</x-slot>

<div class="py-12" dir="rtl">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if ($case === null)
            <div class="max-w-xl mx-auto w-full">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold text-gray-800 mb-4">الحالات قيد المعالجة النظرية</h3>

                    <div class="mb-4">
                        <label for="case-search" class="sr-only">بحث بالاسم ورقم التذكرة</label>
                        <input
                            id="case-search"
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="ابحث بالاسم أو رقم التذكرة..."
                            class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:border-gray-500 focus:ring-gray-500"
                        />
                    </div>

                    @if ($pendingCases->isEmpty())
                        <p class="text-sm text-gray-500">لا توجد حالات بانتظار المختبر النظري.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-right text-gray-500 border-b border-gray-200">
                                        <th class="py-2 pr-0 pl-3 font-medium">رقم التذكرة</th>
                                        <th class="py-2 pl-3 font-medium">اسم العامل</th>
                                        <th class="py-2 pl-3 font-medium">رقم الجواز</th>
                                        <th class="py-2 pl-3 font-medium">وقت التحويل</th>
                                        <th class="py-2 pl-3 font-medium">حالة التصوير</th>
                                        <th class="py-2 pl-0 font-medium"></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @foreach ($pendingCases as $item)
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-3 pr-0 pl-3 whitespace-nowrap">{{ $item->ticket_number }}</td>
                                            <td class="py-3 pl-3">
                                                <span class="font-medium text-gray-900">{{ $item->full_name }}</span>
                                                @if ($item->status === \App\Enums\CaseStatus::returned)
                                                    <span class="mr-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        مُرجع
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-3 pl-3 whitespace-nowrap">{{ $item->passport_number }}</td>
                                            <td class="py-3 pl-3 whitespace-nowrap text-gray-500">
                                                {{ \Illuminate\Support\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}
                                            </td>
                                            <td class="py-3 pl-3">
                                                @if ($item->has_theory_photo)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">تم</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">لم يتم</span>
                                                @endif
                                            </td>
                                            <td class="py-3 pl-0 text-left">
                                                <button
                                                    type="button"
                                                    wire:click="selectCase({{ $item->id }})"
                                                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                                                >
                                                    فتح المعمل
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
            </div>
        @else
            <div class="max-w-xl mx-auto w-full space-y-4">
            <div class="flex items-center gap-4">
                <a
                    href="{{ route('dashboard') }}"
                    class="text-sm text-gray-600 hover:text-gray-900"
                >
                    &larr; رجوع للوحة التحكم
                </a>
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
