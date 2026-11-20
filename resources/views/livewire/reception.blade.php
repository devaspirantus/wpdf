<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('محطة الاستقبال') }}
        </h2>
    </x-slot>

    <div class="py-12" dir="rtl">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            @if ($created)
                <div class="mb-4 rounded-md bg-green-50 border border-green-200 p-4 text-green-800 text-sm">
                    تم إنشاء الحالة بنجاح
                </div>
            @endif

            @if (($created) && $case)
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
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
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
        </div>
    </div>
</x-app-layout>
