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
    $documents = ($case?->documents ?? \Illuminate\Database\Eloquent\Collection::make())->keyBy('type');
@endphp

<x-slot name="header">
    <h2 class="font-semibold text-xl text-ink leading-tight">
        {{ __('مراجعة البيانات') }}
    </h2>
</x-slot>

<div class="py-12" dir="rtl">
    <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if ($returnSuccess !== null)
            <div class="rounded-md bg-success/10 border border-success/25 p-4 text-success text-sm">
                <p class="font-semibold">{{ $returnSuccess }}</p>
                @if ($pdfPath === null)
                    <p class="mt-1">انتقل إلى المحطة المستهدفة لاستكمال المعالجة.</p>
                @else
                    <p class="mt-1">يمكنك الآن تحميل ملف الـPDF من الزر أدناه.</p>
                @endif
            </div>
        @endif

        @if ($case === null)
            <div class="dga-card">
                <div class="dga-card-body">
                    <h3 class="font-semibold text-ink mb-4">الحالات قيد المراجعة</h3>

                    @if ($cases->isEmpty())
                        <p class="text-sm text-muted">لا توجد حالات بانتظار المراجعة.</p>
                    @else
                        <ul class="divide-y divide-line">
                            @foreach ($cases as $item)
                                <li class="py-3 flex items-center justify-between gap-4">
                                    <div class="text-sm">
                                        <div class="flex items-center gap-2">
                                            <p class="font-medium text-ink">{{ $item->full_name }}</p>
                                            @if (($item->returns_count ?? 0) > 0)
                                                <span class="inline-flex items-center rounded-full bg-warning/20 border border-warning/40 px-2 py-0.5 text-xs font-medium text-navy">
                                                    عاد بعد التصحيح
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-muted mt-1">
                                            التذكرة: {{ $item->ticket_number }}
                                            &middot; الجواز: {{ $item->passport_number }}
                                        </p>
                                    </div>
                                    <button
                                        type="button"
                                        wire:click="selectCase({{ $item->id }})"
                                        class="shrink-0 inline-flex items-center px-4 py-2 bg-navy border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-navy/90"
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
                    class="text-sm text-primary-dark hover:text-ink"
                >
                    &larr; رجوع إلى القائمة
                </button>
            </div>

            <div class="dga-card">
                <div class="dga-card-body">
                    <h3 class="font-semibold text-ink mb-3">بيانات العامل</h3>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <dt class="text-muted">الاسم</dt>
                            <dd class="mt-1 font-medium text-ink">{{ $case->full_name }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted">رقم الجواز</dt>
                            <dd class="mt-1 font-medium text-ink">{{ $case->passport_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted">رقم التذكرة</dt>
                            <dd class="mt-1 font-medium text-ink">{{ $case->ticket_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted">الحالة الحالية</dt>
                            <dd class="mt-1 flex items-center gap-2">
                                <span class="font-medium text-ink">{{ $case->status->arabicName() }}</span>
                                @if ($case->returns->isNotEmpty())
                                    <span class="inline-flex items-center rounded-full bg-warning/20 border border-warning/40 px-2 py-0.5 text-xs font-medium text-navy">
                                        عاد بعد التصحيح
                                    </span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="dga-card">
                <div class="dga-card-body">
                    <h3 class="font-semibold text-ink mb-3">التحقق من هوية العامل</h3>
                    <p class="text-sm text-muted mb-4">قارن صورة العامل مع صورة جواز السفر للتحقق من تطابق الهوية.</p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach (['worker_photo', 'passport'] as $type)
                            <div>
                                <p class="text-xs text-muted mb-1">{{ $labels[$type] }}</p>
                                @if ($documents->has($type))
                                    <img
                                        src="{{ route('documents.show', $documents->get($type)) }}"
                                        alt="{{ $labels[$type] }}"
                                        class="w-full border border-line rounded-md bg-white object-cover"
                                    >
                                @else
                                    <div class="w-full h-40 flex items-center justify-center border border-dashed border-line rounded-md text-sm text-muted">
                                        غير مكتمل
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="dga-card">
                <div class="dga-card-body">
                    <h3 class="font-semibold text-ink mb-4">صور المستندات</h3>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        @foreach ($documents as $document)
                            <div>
                                <p class="text-xs text-muted mb-1">{{ $labels[$document->type] }}</p>
                                <img
                                    src="{{ route('documents.show', $document) }}"
                                    alt="{{ $labels[$document->type] }}"
                                    class="w-full h-40 object-cover border border-line rounded-md bg-white"
                                >
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="dga-card">
                <div class="dga-card-body">
                    <h3 class="font-semibold text-ink mb-4">نموذج التقييم العملي</h3>

                    @if ($case->evaluation === null)
                        <div class="rounded-md bg-danger/10 border border-danger/25 p-4 text-danger text-sm">
                            <p class="font-semibold">نموذج التقييم غير مكتمل</p>
                        </div>
                    @else
                        @php $evaluation = $case->evaluation; @endphp

                        @if ($evaluation->signed_at !== null)
                            <div class="rounded-md bg-success/10 border border-success/25 p-4 text-success text-sm mb-4">
                                <p class="font-semibold">تم توقيع النموذج من المقيّم والعامل — النموذج مقفل</p>
                                <p class="mt-1">توقيت التوقيع: {{ $evaluation->signed_at->format('Y-m-d H:i') }}</p>
                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    @if ($evaluation->evaluator_signature)
                                        <div>
                                            <p class="text-xs text-muted mb-1">توقيع المقيّم</p>
                                            <img src="{{ $evaluation->evaluator_signature }}" alt="توقيع المقيّم" class="w-full h-24 border border-line rounded-md bg-white">
                                        </div>
                                    @endif
                                    @if ($evaluation->worker_signature)
                                        <div>
                                            <p class="text-xs text-muted mb-1">توقيع العامل</p>
                                            <img src="{{ $evaluation->worker_signature }}" alt="توقيع العامل" class="w-full h-24 border border-line rounded-md bg-white">
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            <div class="rounded-md bg-danger/10 border border-danger/25 p-4 text-danger text-sm mb-4">
                                <p class="font-semibold">النموذج غير موقّع بعد</p>
                            </div>
                        @endif

                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm mb-4">
                            <div>
                                <dt class="text-muted">المهمة المطلوب تنفيذها</dt>
                                <dd class="mt-1 font-medium text-ink">{{ $evaluation->task }}</dd>
                            </div>
                            <div>
                                <dt class="text-muted">مستوى الصعوبة</dt>
                                <dd class="mt-1 font-medium text-ink">
                                    {{ $difficultyNames[$evaluation->difficulty->value] ?? null }}
                                </dd>
                            </div>
                        </dl>

                        @foreach ($sections as $sectionTitle => $criteria)
                            <fieldset class="mb-4">
                                <legend class="font-semibold text-ink mb-2">{{ $sectionTitle }}</legend>
                                <ol class="divide-y divide-line">
                                    @foreach ($criteria as $key => $criterionText)
                                        @php $score = $evaluation->scores[$key] ?? null; @endphp
                                        <li class="py-3">
                                            <div class="flex items-start justify-between gap-4">
                                                <p class="text-sm text-muted">
                                                    <span class="font-medium text-ink">{{ $key }}. </span>
                                                    {{ $criterionText }}
                                                </p>
                                                <span class="shrink-0 font-semibold text-sm text-ink">
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

            <div class="dga-card">
                <div class="dga-card-body">
                    <h3 class="font-semibold text-ink mb-4">إرجاع الملف إلى محطة</h3>

                    @if ($latestReturn !== null)
                        <div class="rounded-md bg-warning/10 border border-warning/40 p-4 text-navy text-sm mb-4">
                            <p class="font-semibold">إرجاع سابق إلى: {{ $latestReturn->returned_to_station->arabicName() }}</p>
                            <p class="mt-1">ملاحظة: {{ $latestReturn->note }}</p>
                            <p class="mt-1 text-navy">تاريخ الإرجاع: {{ $latestReturn->created_at?->format('Y-m-d H:i') }}</p>
                        </div>
                    @endif

                      <form wire:submit="submitReturn" class="space-y-4">
                        <div>
                            <label for="return-station" class="block text-sm font-medium text-muted mb-1">المحطة المستهدفة</label>
                            <select
                                id="return-station"
                                wire:model="returnStation"
                                class="w-full text-sm"
                            >
                                <option value="">— اختر المحطة —</option>
                                <option value="reception">{{ \App\Enums\Station::reception->arabicName() }}</option>
                                <option value="theory">{{ \App\Enums\Station::theory->arabicName() }}</option>
                                <option value="practical">{{ \App\Enums\Station::practical->arabicName() }}</option>
                            </select>
                            @error('returnStation')
                                <p class="text-danger text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p
                                x-show="$wire.returnStation === 'practical'"
                                class="hidden mt-2 rounded-md bg-orange-50 border border-orange-200 p-3 text-orange-800 text-sm"
                            >
                                <span class="font-semibold">تنبيه:</span> الإرجاع إلى المختبر العملي سيؤدي إلى مسح التوقيعين (المقيّم والفني)، وسيكون من الضروري إعادة التوقيع بعد الإكمال.
                            </p>
                        </div>

                        <div>
                            <label for="return-note" class="block text-sm font-medium text-muted mb-1">ملاحظة الإرجاع</label>
                            <textarea
                                id="return-note"
                                wire:model="returnNote"
                                rows="3"
                                class="w-full text-sm"
                            ></textarea>
                            @error('returnNote')
                                <p class="text-danger text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        @if ($returnError !== null)
                            <div class="rounded-md bg-danger/10 border border-danger/25 p-3 text-danger text-sm">
                                {{ $returnError }}
                            </div>
                        @endif

                        <div class="flex items-center justify-end gap-3">
                            <button
                                type="submit"
                                class="inline-flex items-center px-4 py-2 bg-danger hover:bg-danger/90 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest"
                            >
                                إرجاع الملف
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @if ($pdfError !== null)
                <div class="mb-3 rounded-md bg-danger/10 border border-danger/25 p-3 text-danger text-sm">
                    {{ $pdfError }}
                </div>
            @endif

            <div class="flex items-center justify-end gap-3">
                @if ($pdfPath !== null)
                    <a
                        href="{{ $pdfPath }}"
                        class="inline-flex items-center px-4 py-2 bg-secondary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-secondary-dark"
                    >
                        تحميل PDF
                    </a>
                    <a
                        href="{{ $pdfPrintPath }}"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-dark"
                    >
                        طباعة PDF
                    </a>
                @endif
                <button
                    type="button"
                    wire:click="generatePdf"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-dark disabled:opacity-50"
                >
                    <span wire:loading.remove style="backgound:black;">توليد PDF</span>
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
