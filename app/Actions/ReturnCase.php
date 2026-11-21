<?php

namespace App\Actions;

use App\Enums\CaseStatus;
use App\Enums\Station;
use App\Models\CaseReturn;
use App\Models\WorkerCase;
use App\Services\CaseStateMachine;
use Illuminate\Support\Facades\DB;
use LogicException;

class ReturnCase
{
    public function __construct(
        private readonly CaseStateMachine $stateMachine,
    ) {}

    public function handle(WorkerCase $case, Station $station, string $note, int $userId): CaseReturn
    {
        $note = trim($note);

        if ($note === '') {
            throw new LogicException('ملاحظة الإرجاع مطلوبة');
        }

        if ($station === Station::review) {
            throw new LogicException('لا يمكن إرجاع الحالة إلى محطة المراجعة');
        }

        if ($case->status !== CaseStatus::review) {
            throw new LogicException('يمكن الإرجاع فقط من محطة المراجعة');
        }

        return DB::transaction(function () use ($case, $station, $note, $userId) {
            $this->stateMachine->returnTo($case->fresh(), $station);

            $return = CaseReturn::create([
                'case_id' => $case->id,
                'returned_to_station' => $station,
                'note' => $note,
                'returned_by' => $userId,
            ]);

            if ($station === Station::practical) {
                $evaluation = $case->evaluation()->first();

                if ($evaluation !== null) {
                    $evaluation->evaluator_signature = null;
                    $evaluation->worker_signature = null;
                    $evaluation->signed_at = null;
                    $evaluation->save();
                }
            }

            return $return;
        });
    }
}
