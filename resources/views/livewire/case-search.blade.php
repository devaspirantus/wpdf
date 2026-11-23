<x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">
            {{ __('البحث عن ملف العامل') }}
        </h2>
    </x-slot>

    <div class="py-12" dir="rtl">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="dga-card">
                <div class="dga-card-body">
                    <form wire:submit="search" class="space-y-6">
                        <div>
                            <x-input-label for="ticket_number" value="رقم التذكرة" />
                            <x-text-input id="ticket_number" wire:model="ticketNumber" type="text" class="mt-1 block w-full" />
                            <x-input-error class="mt-2" :messages="$errors->get('ticketNumber')" />
                        </div>

                        <div class="flex items-center justify-end">
                            <x-primary-button>
                                {{ __('بحث') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            @if ($searched && $case === null)
                <div class="rounded-md bg-danger/10 border border-danger/25 p-4 text-danger text-sm">
                    لم يتم العثور على ملف برقم التذكرة هذا
                </div>
            @endif

            @if ($case !== null)
                <div class="dga-card">
                    <div class="dga-card-body">
                        <h3 class="font-semibold text-ink mb-3">بيانات الملف</h3>
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
                                <dd class="mt-1 font-medium text-ink">{{ $statusArabic }}</dd>
                            </div>
                        </dl>

                        @if ($case->status === \App\Enums\CaseStatus::practical)
                            <div class="mt-4">
                                <a
                                    href="{{ route('practical', $case) }}"
                                    class="inline-flex items-center gap-1 rounded-md bg-primary px-4 py-2 text-xs font-semibold leading-5 text-white hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                >
                                    الانتقال إلى المختبر العملي
                                </a>
                            </div>
                        @endif

                        @if ($case->status === \App\Enums\CaseStatus::theory)
                            <div class="mt-4">
                                <a
                                    href="{{ route('theory', $case) }}"
                                    class="inline-flex items-center gap-1 rounded-md bg-primary px-4 py-2 text-xs font-semibold leading-5 text-white hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2"
                                >
                                    الانتقال إلى المختبر النظري
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>
    </div>

