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
    $transitionStyle = 'border-warning bg-warning/15 text-ink';
    $messageClass = 'text-muted';
    $transitionMessage = 'أكمل المتطلبات المذكورة أعلاه قبل الانتقال';

    if ($currentStation === \App\Enums\Station::review) {
        $transitionLabel = 'مكتمل';
        $transitionStyle = 'border-success bg-success/15 text-success';
        $messageClass = 'text-muted';
        $transitionMessage = 'بانتظار توليد PDF';
    } elseif ($isComplete) {
        $nextStatus = app(\App\Services\CaseStateMachine::class)->nextStation($currentStation);
        $transitionLabel = 'جاهز للمحطة التالية';
        $transitionStyle = 'border-success bg-success/15 text-success';
        $messageClass = 'text-muted';
        $transitionMessage = 'المحطة التالية: '.$nextStatus->arabicName();
    }
@endphp

<div class="dga-card" dir="rtl">
    <div class="dga-card-header">ملخص نهاية المحطة</div>
    <div class="dga-card-body space-y-3">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
            <div>
                <dt class="text-muted">المحطة الحالية</dt>
                <dd class="mt-1 font-medium text-ink">{{ $currentStation->arabicName() }}</dd>
            </div>
            <div>
                <dt class="text-muted">دور المستخدم</dt>
                <dd class="mt-1 font-medium text-ink">{{ request()->user()->role->label() }}</dd>
            </div>
            <div>
                <dt class="text-muted">المستندات المكتملة</dt>
                <dd class="mt-1 font-medium text-ink">{{ count($completedDocs) }} / {{ count($requirements) }}</dd>
            </div>
            <div>
                <dt class="text-muted">حالة الانتقال</dt>
                <dd class="mt-1">
                    <span class="dga-badge border {{ $transitionStyle }}">
                        {{ $transitionLabel }}
                    </span>
                </dd>
            </div>
        </dl>

        @if (count($completedDocs) > 0)
            <div>
                <p class="text-xs text-muted mb-1">المستندات المكتملة:</p>
                <ul class="space-y-1 text-sm">
                    @foreach ($completedDocs as $doc)
                        <li class="flex items-center gap-2 text-success">
                            <span>✓</span>
                            <span>{{ $doc['label'] }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if (count($missingList) > 0)
            <div>
                <p class="text-xs text-muted mb-1">المتطلبات الناقصة:</p>
                <ul class="space-y-1 text-sm">
                    @foreach ($missingList as $label)
                        <li class="flex items-center gap-2 text-danger">
                            <span>✕</span>
                            <span>{{ $label }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <p class="text-xs {{ $messageClass }}">{{ $transitionMessage }}</p>
    </div>
</div>
