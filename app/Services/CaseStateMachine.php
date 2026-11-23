<?php

namespace App\Services;

use App\Enums\CaseStatus;
use App\Enums\Station;
use App\Exceptions\IncompleteStationException;
use App\Models\WorkerCase;
use LogicException;

class CaseStateMachine
{
    public function __construct(
        private readonly StationCompletion $stationCompletion,
    ) {}

    public function advance(WorkerCase $case): WorkerCase
    {
        $case = $case->fresh();

        if ($case->status === CaseStatus::review) {
            throw new LogicException('تم إرسال الملف إلى رفع البيانات ولا يمكن المتابعة من المحطة السابقة');
        }

        if ($case->status === CaseStatus::returned) {
            throw new LogicException('الملف مُرجع ويجب اكتمال التصحيح وإعادة الإرسال للمراجعة');
        }

        $station = $case->status !== null ? Station::tryFrom($case->status->value) : null;

        if ($station === null) {
            throw new LogicException('الحالة ليست في محطة يمكن التقدّم منها');
        }

        $missing = $this->stationCompletion->missing($case, $station);

        if ($missing !== []) {
            throw new IncompleteStationException($case, $station, $missing);
        }

        $next = $this->nextStation($station);

        $case->update(['status' => $next->value]);

        return $case->fresh();
    }

    public function nextStation(Station $station): CaseStatus
    {
        return match ($station) {
            Station::reception => CaseStatus::theory,
            Station::theory => CaseStatus::practical,
            Station::practical => CaseStatus::review,
            Station::review => throw new LogicException('لا توجد محطة بعد محطة المراجعة'),
        };
    }

    public function returnTo(WorkerCase $case, Station $station): WorkerCase
    {
        $case = $case->fresh();

        if ($case->status !== CaseStatus::review) {
            throw new LogicException('يمكن الإرجاع فقط من محطة المراجعة');
        }

        if ($station === Station::review) {
            throw new LogicException('لا يمكن إرجاع الحالة إلى محطة المراجعة');
        }

        $case->update(['status' => CaseStatus::returned->value]);

        return $case->fresh();
    }

    public function resubmitToReview(WorkerCase $case): WorkerCase
    {
        $case = $case->fresh();

        if ($case->status !== CaseStatus::returned) {
            throw new LogicException('يمكن إعادة الإرسال للمراجعة فقط من حالة مُرجع');
        }

        $case->update(['status' => CaseStatus::review->value]);

        return $case->fresh();
    }
}
