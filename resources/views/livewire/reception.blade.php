
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">
            {{ __('محطة الاستقبال') }}
        </h2>
    </x-slot>

    <div class="py-12" dir="rtl">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            @if ($case)
                <div class="space-y-4 mt-6">
                    <livewire:camera-capture
                        :case-id="$case->id"
                        :station="\App\Enums\Station::reception"
                        type="worker_photo"
                        label="صورة العامل"
                    />
                    <livewire:camera-capture
                        :case-id="$case->id"
                        :station="\App\Enums\Station::reception"
                        type="passport"
                        label="صورة جواز السفر"
                    />
                    <livewire:camera-capture
                        :case-id="$case->id"
                        :station="\App\Enums\Station::reception"
                        type="ticket"
                        label="صورة التذكرة"
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
                            تم الانتقال إلى المحطة التالية بنجاح
                        </div>
                    @endif

                    @if ($isReturned)
                        <div class="rounded-md bg-warning/10 border border-warning/40 p-4 text-navy text-sm">
                            <p class="font-semibold">تم إرجاع هذا الملف من محطة المراجعة</p>
                            @if ($returnNote)
                                <p class="mt-2">ملاحظة الإرجاع: {{ $returnNote }}</p>
                            @endif
                            <div class="mt-3 flex items-center justify-end">
                                <x-primary-button wire:click="resubmit">
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
                        :currentStation="\App\Enums\Station::reception"
                        :case="$case"
                    />
                </div>
            @endif

            @unless ($case)
                <div class="dga-card">
                    <div class="p-6 text-ink">
                        <h3 class="font-semibold text-ink mb-4">ملفات مَرجعة إلى الاستقبال</h3>

                        <div class="mb-4">
                            <label for="reception-return-search" class="sr-only">بحث بالاسم ورقم التذكرة</label>
                            <input
                                id="reception-return-search"
                                type="text"
                                wire:model.live.debounce.300ms="search"
                                placeholder="ابحث بالاسم أو رقم التذكرة أو رقم الجواز..."
                                class="w-full rounded-md border-line shadow-sm text-sm focus:border-primary focus:ring-primary"
                            />
                        </div>

                        @if ($returnedCases->isEmpty())
                            <p class="text-sm text-muted">لا توجد ملفات مَرجعة إلى الاستقبال حاليًا.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="min-w-full dga-table">
                                    <thead>
                                        <tr class="text-right">
                                            <th>رقم التذكرة</th>
                                            <th>اسم العامل</th>
                                            <th>رقم الجواز</th>
                                            <th>وقت الإرجاع</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($returnedCases as $item)
                                            <tr class="bg-warning/10 hover:bg-primary-pastel/15">
                                                <td class="whitespace-nowrap">{{ $item->ticket_number }}</td>
                                                <td>
                                                    <div>
                                                        <span class="font-medium text-ink">{{ $item->full_name }}</span>
                                                        <span class="mr-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-warning/20 text-navy">
                                                            مُرجع من رفع البيانات
                                                        </span>
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
                                                    {{ \Illuminate\Support\Carbon::parse($item->latestReturn->created_at)->format('d/m/Y H:i') }}
                                                </td>
                                                <td class="text-left">
                                                    <button
                                                        type="button"
                                                        wire:click="selectCase({{ $item->id }})"
                                                        class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-dark"
                                                    >
                                                        فتح الملف
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

                <div class="mt-4 dga-card">
                    <div class="p-6 text-ink">
                        <form wire:submit="create" class="space-y-6">
                            <div>
                                <x-input-label for="full_name" value="اسم العامل" />
                                <x-text-input id="full_name" wire:model="fullName" type="text" class="mt-1 block w-full" />
                                <x-input-error class="mt-2" :messages="$errors->get('fullName')" />
                            </div>

                            <div>
                                <x-input-label for="passport_number" value="رقم الجواز" />
                                <x-text-input id="passport_number" wire:model="passportNumber" type="text" class="mt-1 block w-full" />
                                <x-input-error class="mt-2" :messages="$errors->get('passportNumber')" />
                            </div>

                            <div>
                                <x-input-label for="ticket_number" value="رقم التذكرة" />
                                <x-text-input id="ticket_number" wire:model="ticketNumber" type="text" class="mt-1 block w-full" />
                                <x-input-error class="mt-2" :messages="$errors->get('ticketNumber')" />
                            </div>

                            <div class="flex items-center justify-end">
                                <x-primary-button>
                                    {{ __('إنشاء') }}
                                </x-primary-button>
                            </div>
                        </form>
                    </div>
                </div>
            @endunless
        </div>
    </div>

