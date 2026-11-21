@props(['currentStation', 'case'])

@php
    $requirements = app(\App\Services\StationCompletion::class)->requirements($case, $currentStation);
    $completedDocs = array_values(array_filter($requirements, fn (array $requirement): bool => $requirement['met']));
    $missingList = array_map(
        fn (array $requirement): string => $requirement['label'],
        array_filter($requirements, fn (array $requirement): bool => ! $requirement['met']),
    );
    $isComplete = count($completedDocs) === count($requirements);

    $transitionLabel = 'ناقص';
    $transitionStyle = 'bg-yellow-50 border-yellow-200 text-yellow-800';
    $messageClass = 'text-yellow-700';
    $transitionMessage = 'أكمل المتطلبات المذكورة أعلاه قبل الانتقال';

    if ($currentStation === \App\Enums\Station::review) {
        $transitionLabel = 'مكتمل';
        $transitionStyle = 'bg-green-50 border-green-200 text-green-800';
        $messageClass = 'text-gray-600';
        $transitionMessage = 'بانتظار توليد PDF';
    } elseif ($isComplete) {
        $nextStatus = app(\App\Services\CaseStateMachine::class)->nextStation($currentStation);
        $transitionLabel = 'جاهز للمحطة التالية';
        $transitionStyle = 'bg-green-50 border-green-200 text-green-800';
        $messageClass = 'text-gray-600';
        $transitionMessage = 'المحطة التالية: '.$nextStatus->arabicName();
    }
@endphp

<div class="bg-white rounded-lg border border-gray-200 p-4 space-y-3" dir="rtl">
    <h3 class="font-semibold text-gray-800">ملخص نهاية المحطة</h3>

    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
        <div>
            <dt class="text-gray-500">المحطة الحالية</dt>
            <dd class="mt-1 font-medium text-gray-900">{{ $currentStation->arabicName() }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">دور المستخدم</dt>
            <dd class="mt-1 font-medium text-gray-900">{{ request()->user()->role->label() }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">المستندات المكتملة</dt>
            <dd class="mt-1 font-medium text-gray-900">{{ count($completedDocs) }} / {{ count($requirements) }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">حالة الانتقال</dt>
            <dd class="mt-1">
                <span class="inline-flex items-center px-2 py-1 rounded-md border text-xs font-semibold {{ $transitionStyle }}">
                    {{ $transitionLabel }}
                </span>
            </dd>
        </div>
    </dl>

    @if (count($completedDocs) > 0)
        <div>
            <p class="text-xs text-gray-500 mb-1">المستندات المكتملة:</p>
            <ul class="space-y-1 text-sm">
                @foreach ($completedDocs as $doc)
                    <li class="flex items-center gap-2 text-green-700">
                        <span>✓</span>
                        <span>{{ $doc['label'] }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <p class="text-xs {{ $messageClass }}">{{ $transitionMessage }}</p>
</div>
