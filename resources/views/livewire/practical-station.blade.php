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

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="font-semibold text-gray-800 mb-4">نموذج التقييم العملي</h3>

                    @if ($evaluationSaved)
                        <div class="rounded-md bg-green-50 border border-green-200 p-3 text-green-800 text-sm mb-4">
                            تم حفظ التقييم بنجاح
                        </div>
                    @endif

                    <form wire:submit="saveEvaluation" class="space-y-6">
                        @error('task')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror
                        @error('difficulty')
                            <p class="text-red-600 text-sm">{{ $message }}</p>
                        @enderror

                        <div>
                            <label for="task" class="block text-sm font-medium text-gray-700 mb-1">
                                المهمة المطلوب تنفيذها
                            </label>
                            <input
                                id="task"
                                type="text"
                                wire:model.live="task"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-opacity-50"
                            >
                        </div>

                        <div>
                            <label for="difficulty" class="block text-sm font-medium text-gray-700 mb-1">
                                مستوى الصعوبة
                            </label>
                            <select
                                id="difficulty"
                                wire:model.live="difficulty"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring focus:ring-opacity-50"
                            >
                                <option value="easy">سهل</option>
                                <option value="medium">متوسط</option>
                                <option value="hard">صعب</option>
                            </select>
                        </div>

                        @foreach ($sections as $sectionTitle => $criteria)
                            <fieldset>
                                <legend class="font-semibold text-gray-800 mb-2">{{ $sectionTitle }}</legend>
                                <ol class="divide-y divide-gray-200">
                                    @foreach ($criteria as $key => $criterionText)
                                        <li class="py-3">
                                            <div class="flex items-start justify-between gap-4">
                                                <p class="text-sm text-gray-700">
                                                    <span class="font-medium text-gray-900">{{ $key }}. </span>
                                                    {{ $criterionText }}
                                                </p>
                                                @error("scores.{$key}")
                                                    <p class="text-red-600 text-sm whitespace-nowrap">{{ $message }}</p>
                                                @enderror
                                                <select
                                                    wire:model.live="scores.{{ $key }}"
                                                    class="shrink-0 rounded-md border-gray-300 text-sm shadow-sm focus:ring focus:ring-opacity-50"
                                                >
                                                    @foreach ($scale as $value => $label)
                                                        <option value="{{ $value }}">{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </li>
                                    @endforeach
                                </ol>
                            </fieldset>
                        @endforeach

                        <div class="flex items-center justify-end">
                            <x-primary-button type="submit">
                                حفظ التقييم
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

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

