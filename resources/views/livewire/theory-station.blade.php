<x-slot name="header">
    <h2 class="font-semibold text-xl text-ink leading-tight">
        {{ __('المختبر النظري') }}
    </h2>
</x-slot>

<div class="py-12" dir="rtl">
    <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">
        @if ($case === null)
            <div class="max-w-xl mx-auto w-full">
            <div class="dga-card">
                <div class="p-6 text-ink">
                    <h3 class="font-semibold text-ink mb-4">الحالات قيد المعالجة النظرية</h3>

                    <div class="mb-4">
                        <label for="case-search" class="sr-only">بحث بالاسم ورقم التذكرة</label>
                        <input
                            id="case-search"
                            type="text"
                            wire:model.live.debounce.300ms="search"
                            placeholder="ابحث بالاسم أو رقم التذكرة..."
                            class="w-full rounded-md border-line shadow-sm text-sm focus:border-primary focus:ring-primary"
                        />
                    </div>

                    @if ($pendingCases->isEmpty())
                        <p class="text-sm text-muted">لا توجد حالات بانتظار المختبر النظري.</p>
                    @else
                        <div class="overflow-x-auto">
                            <table class="min-w-full dga-table">
                                <thead>
                                    <tr class="text-right">
                                        <th>رقم التذكرة</th>
                                        <th>اسم العامل</th>
                                        <th>رقم الجواز</th>
                                        <th>وقت التحويل</th>
                                        <th>حالة التصوير</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pendingCases as $item)
                                        <tr class="hover:bg-primary-pastel/15 {{ $item->latestReturn ? 'bg-warning/10' : '' }}">
                                            <td class="whitespace-nowrap">{{ $item->ticket_number }}</td>
                                            <td>
                                                <div>
                                                    <span class="font-medium text-ink">{{ $item->full_name }}</span>
                                                    @if ($item->latestReturn)
                                                        <span class="mr-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-warning/20 text-navy">
                                                            مُرجع من رفع البيانات
                                                        </span>
                                                    @endif
                                                </div>
                                                @if ($item->latestReturn?->note)
                                                    <p class="mt-1 text-xs text-muted">ملاحظة الإرجاع: {{ $item->latestReturn->note }}</p>
                                                @endif
                                                @if ($item->latestReturn?->returnedBy)
                                                    <p class="mt-0.5 text-xs text-muted">بواسطة: {{ $item->latestReturn->returnedBy->name }}</p>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap">{{ $item->passport_number }}</td>
                                            <td class="whitespace-nowrap text-muted">
                                                {{ \Illuminate\Support\Carbon::parse($item->created_at)->format('d/m/Y H:i') }}
                                            </td>
                                            <td>
                                                @if ($item->has_theory_photo)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-success/10 text-success">تم</span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-surface text-muted">لم يتم</span>
                                                @endif
                                            </td>
                                            <td class="text-left">
                                                <button
                                                    type="button"
                                                    wire:click="selectCase({{ $item->id }})"
                                                    class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-dark"
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
                    class="text-sm text-primary-dark hover:text-ink"
                >
                    &larr; رجوع للوحة التحكم
                </a>
                <button
                    type="button"
                    wire:click="backToList"
                    class="text-sm text-primary-dark hover:text-ink"
                >
                    &rarr; رجوع إلى القائمة
                </button>
            </div>

            <div class="dga-card">
                <div class="p-6 text-ink">
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
                            <dd class="mt-1 font-medium text-ink">{{ $case->status->arabicName() }}</dd>
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

            <div class="dga-card p-4">
                <h3 class="font-semibold text-ink mb-3">متطلبات استكمال المحطة</h3>
                <ul class="space-y-2 text-sm">
                    @foreach ($requirements as $requirement)
                        <li class="flex items-center gap-2 {{ $requirement['met'] ? 'text-success' : 'text-muted' }}">
                            <span>{{ $requirement['met'] ? '✓' : '○' }}</span>
                            <span>{{ $requirement['label'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            @if ($advanceError)
                <div class="rounded-md bg-danger/10 border border-danger/25 p-4 text-danger text-sm">
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
                <div class="rounded-md bg-success/10 border border-success/25 p-4 text-success text-sm">
                    تم إرسال الملف إلى محطة المراجعة بنجاح
                </div>
            @endif

            @if ($isReturned)
                <div class="rounded-md bg-warning/10 border border-warning/40 p-4 text-navy text-sm">
                    <p class="font-semibold">تم إرجاع هذا الملف من محطة المراجعة</p>
                    @if ($returnNote)
                        <p class="mt-2">ملاحظة الإرجاع: {{ $returnNote }}</p>
                    @endif
                    <div class="mt-3 flex items-center justify-end">
                        <x-primary-button wire:click="resubmit" :disabled="$hasMissing">
                            {{ __('إرجاع إلى رفع البيانات') }}
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