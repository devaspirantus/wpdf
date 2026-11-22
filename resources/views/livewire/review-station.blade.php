@php
    $labels = [
        'worker_photo' => 'صورة العامل',
        'passport' => 'صورة جواز السفر',
        'ticket' => 'صورة التذكرة',
        'theory_photo' => 'صورة الاختبار النظري',
        'practical_photo' => 'صورة الاختبار العملي',
    ];
    $difficultyNames = [
        \App\Enums\Difficulty::easy->value => 'سهل',
        \App\Enums\Difficulty::medium->value => 'متوسط',
        \App\Enums\Difficulty::hard->value => 'صعب',
    ];
    $documents = $case?->documents ?? \Illuminate\Database\Eloquent\Collection::make();
@endphp

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 leading-tight">
        {{ __('مراجعة البيانات') }}
    </h2>
</x-slot>

<div class="py-12" dir="rtl">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if ($returnSuccess !== null)
            <div class="rounded-md bg-green-50 border border-green-200 p-4 text-green-800 text-sm">
                <p class="font-semibold">{{ $returnSuccess }}</p>
                @if ($pdfPath === null)
                    <p class="mt-1">انتقل إلى المحطة المستهدفة لاستكمال المعالجة.</p>
                @else
                    <p class="mt-1">يمكنك الآن تحميل ملف الـPDF من الزر أدناه.</p>
                @endif
            </div>
        @endif

        @if ($case === null)
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold text-gray-800 mb-4">الحالات قيد المراجعة</h3>

                    @if ($cases->isEmpty())
                        <p class="text-sm text-gray-500">لا توجد حالات بانتظار المراجعة.</p>
                    @else
                        <ul class="divide-y divide-gray-200">
                            @foreach ($cases as $item)
                                <li class="py-3 flex items-center justify-between gap-4">
                                    <div class="text-sm">
                                        <p class="font-medium text-gray-900">{{ $item->full_name }}</p>
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
                                        عرض
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        @else
            <div class="flex items-center justify-between">
                <button
                    type="button"
                    wire:click="backToList"
                    class="text-sm text-gray-600 hover:text-gray-900"
                >
                    &larr; رجوع إلى القائمة
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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold text-gray-800 mb-3">التحقق من هوية العامل</h3>
                    <p class="text-sm text-gray-600 mb-4">قارن صورة العامل مع صورة جواز السفر للتحقق من تطابق الهوية.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach (['worker_photo', 'passport'] as $type)
                            <div>
                                <p class="text-xs text-gray-600 mb-1">{{ $labels[$type] }}</p>
                                @if ($documents->has($type))
                                    <img
                                        src="{{ route('documents.show', $documents->get($type)) }}"
                                        alt="{{ $labels[$type] }}"
                                        class="w-full border border-gray-200 rounded-md bg-white object-cover"
                                    >
                                @else
                                    <div class="w-full h-40 flex items-center justify-center border border-dashed border-gray-300 rounded-md text-sm text-gray-500">
                                        غير مكتمل
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold text-gray-800 mb-4">صور المستندات</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @foreach ($documents as $document)
                            <div>
                                <p class="text-xs text-gray-600 mb-1">{{ $labels[$document->type] }}</p>
                                <img
                                    src="{{ route('documents.show', $document) }}"
                                    alt="{{ $labels[$document->type] }}"
                                    class="w-full h-40 object-cover border border-gray-200 rounded-md bg-white"
                                >
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold text-gray-800 mb-4">نموذج التقييم العملي</h3>

                    @if ($case->evaluation === null)
                        <div class="rounded-md bg-red-50 border border-red-200 p-4 text-red-800 text-sm">
                            <p class="font-semibold">نموذج التقييم غير مكتمل</p>
                        </div>
                    @else
                        @php $evaluation = $case->evaluation; @endphp

                        @if ($evaluation->signed_at !== null)
                            <div class="rounded-md bg-green-50 border border-green-200 p-4 text-green-800 text-sm mb-4">
                                <p class="font-semibold">تم توقيع النموذج من المقيّم والعامل — النموذج مقفل</p>
                                <p class="mt-1">توقيت التوقيع: {{ $evaluation->signed_at->format('Y-m-d H:i') }}</p>
                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @if ($evaluation->evaluator_signature)
                                        <div>
                                            <p class="text-xs text-gray-600 mb-1">توقيع المقيّم</p>
                                            <img src="{{ $evaluation->evaluator_signature }}" alt="توقيع المقيّم" class="w-full h-24 border border-gray-200 rounded-md bg-white">
                                        </div>
                                    @endif
                                    @if ($evaluation->worker_signature)
                                        <div>
                                            <p class="text-xs text-gray-600 mb-1">توقيع العامل</p>
                                            <img src="{{ $evaluation->worker_signature }}" alt="توقيع العامل" class="w-full h-24 border border-gray-200 rounded-md bg-white">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="rounded-md bg-red-50 border border-red-200 p-4 text-red-800 text-sm mb-4">
                                <p class="font-semibold">النموذج غير موقّع بعد</p>
                            </div>
                        @endif

                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm mb-4">
                            <div>
                                <dt class="text-gray-500">المهمة المطلوب تنفيذها</dt>
                                <dd class="mt-1 font-medium text-gray-900">{{ $evaluation->task }}</dd>
                            </div>
                            <div>
                                <dt class="text-gray-500">مستوى الصعوبة</dt>
                                <dd class="mt-1 font-medium text-gray-900">
                                    {{ $difficultyNames[$evaluation->difficulty->value] ?? null }}
                                </dd>
                            </div>
                        </dl>

                        @foreach ($sections as $sectionTitle => $criteria)
                            <fieldset class="mb-4">
                                <legend class="font-semibold text-gray-800 mb-2">{{ $sectionTitle }}</legend>
                                <ol class="divide-y divide-gray-200">
                                    @foreach ($criteria as $key => $criterionText)
                                        @php $score = $evaluation->scores[$key] ?? null; @endphp
                                        <li class="py-3">
                                            <div class="flex items-start justify-between gap-4">
                                                <p class="text-sm text-gray-700">
                                                    <span class="font-medium text-gray-900">{{ $key }}. </span>
                                                    {{ $criterionText }}
                                                </p>
                                                <span class="shrink-0 font-semibold text-sm text-gray-900">
                                                    {{ $score === null ? '—' : (($scale[$score] ?? null).' ('.$score.')') }}
                                                </span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ol>
                            </fieldset>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold text-gray-800 mb-4">إرجاع الملف إلى محطة</h3>

                    @if ($latestReturn !== null)
                        <div class="rounded-md bg-yellow-50 border border-yellow-200 p-4 text-yellow-800 text-sm mb-4">
                            <p class="font-semibold">إرجاع سابق إلى: {{ $latestReturn->returned_to_station->arabicName() }}</p>
                            <p class="mt-1">ملاحظة: {{ $latestReturn->note }}</p>
                            <p class="mt-1 text-yellow-600">تاريخ الإرجاع: {{ $latestReturn->created_at?->format('Y-m-d H:i') }}</p>
                        </div>
                    @endif

                      <form wire:submit="submitReturn" class="space-y-4">
                        <div>
                            <label for="return-station" class="block text-sm font-medium text-gray-700 mb-1">المحطة المستهدفة</label>
                            <select
                                id="return-station"
                                wire:model="returnStation"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-gray-500 focus:ring-gray-500 text-sm"
                            >
                                <option value="">— اختر المحطة —</option>
                                <option value="reception">{{ \App\Enums\Station::reception->arabicName() }}</option>
                                <option value="theory">{{ \App\Enums\Station::theory->arabicName() }}</option>
                                <option value="practical">{{ \App\Enums\Station::practical->arabicName() }}</option>
                            </select>
                            @error('returnStation')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p
                                x-show="$wire.returnStation === 'practical'"
                                class="hidden mt-2 rounded-md bg-orange-50 border border-orange-200 p-3 text-orange-800 text-sm"
                            >
                                <span class="font-semibold">تنبيه:</span> الإرجاع إلى المختبر العملي سيؤدي إلى مسح التوقيعين (المقيّم والفني)، وسيكون من الضروري إعادة التوقيع بعد الإكمال.
                            </p>
                        </div>

                        <div>
                            <label for="return-note" class="block text-sm font-medium text-gray-700 mb-1">ملاحظة الإرجاع</label>
                            <textarea
                                id="return-note"
                                wire:model="returnNote"
                                rows="3"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-gray-500 focus:ring-gray-500 text-sm"
                            ></textarea>
                            @error('returnNote')
                                <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        @if ($returnError !== null)
                            <div class="rounded-md bg-red-50 border border-red-200 p-3 text-red-800 text-sm">
                                {{ $returnError }}
                            </div>
                        @endif

                        <div class="flex items-center justify-end gap-3">
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500"
                            >
                                إرجاع الملف
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if ($pdfError !== null)
                <div class="mb-3 rounded-md bg-red-50 border border-red-200 p-3 text-red-800 text-sm">
                    {{ $pdfError }}
                </div>
            @endif

            <div class="flex items-center justify-end gap-3">
                @if ($pdfPath !== null)
                    <a
                        href="{{ $pdfPath }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500"
                    >
                        تحميل PDF
                    </a>
                @endif
                <button
                    type="button"
                    wire:click="generatePdf"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 bg-emerald-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-emerald-500 disabled:opacity-50"
                >
                    <span wire:loading.remove>توليد PDF</span>
                    <span wire:loading wire:target="generatePdf" x-cloak>جارٍ التوليد...</span>
                </button>
            </div>

            <x-station-summary
                :currentStation="\App\Enums\Station::review"
                :case="$case"
            />
        @endif
    </div>
</div>
