<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('البحث عن ملف العامل') }}
        </h2>
    </x-slot>

    <div class="py-12" dir="rtl">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
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
                <div class="rounded-md bg-red-50 border border-red-200 p-4 text-red-800 text-sm">
                    لم يتم العثور على ملف برقم التذكرة هذا
                </div>
            @endif

            @if ($case !== null)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900">
                        <h3 class="font-semibold text-gray-800 mb-3">بيانات الملف</h3>
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
                                <dd class="mt-1 font-medium text-gray-900">{{ $statusArabic }}</dd>
                            </div>
                        </dl>

                        @if ($case->status === \App\Enums\CaseStatus::theory)
                            <div class="mt-4">
                                <a
                                    href="{{ route('theory', $case) }}"
                                    class="inline-flex items-center gap-1 rounded-md bg-indigo-600 px-4 py-2 text-xs font-semibold leading-5 text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
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
</x-app-layout>
