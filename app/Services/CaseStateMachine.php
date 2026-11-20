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
}
