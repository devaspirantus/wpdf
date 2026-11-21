<?php

namespace App\Services;

use App\Enums\Station;
use App\Models\WorkerCase;

class StationCompletion
{
    /**
     * @return list<array{label: string, met: bool}>
     */
    public function requirements(WorkerCase $case, Station $station): array
    {
        return match ($station) {
            Station::reception => $this->receptionRequirements($case),
            Station::theory => [
                ['label' => 'صورة الاختبار النظري', 'met' => $this->hasDocument($case, $station, 'theory_photo')],
            ],
            Station::practical => $this->practicalRequirements($case),
            Station::review => [],
        };
    }

    /**
     * @return list<string>
     */
    public function missing(WorkerCase $case, Station $station): array
    {
        return array_values(array_map(
            fn (array $requirement) => $requirement['label'],
            array_filter($this->requirements($case, $station), fn (array $requirement) => ! $requirement['met']),
        ));
    }

    public function isComplete(WorkerCase $case, Station $station): bool
    {
        return $this->missing($case, $station) === [];
    }

    /**
     * @return list<array{label: string, met: bool}>
     */
    private function receptionRequirements(WorkerCase $case): array
    {
        return [
            ['label' => 'اسم العامل', 'met' => filled($case->full_name)],
            ['label' => 'رقم الجواز', 'met' => filled($case->passport_number)],
            ['label' => 'رقم التذكرة', 'met' => filled($case->ticket_number)],
            ['label' => 'صورة العامل', 'met' => $this->hasDocument($case, Station::reception, 'worker_photo')],
            ['label' => 'صورة جواز السفر', 'met' => $this->hasDocument($case, Station::reception, 'passport')],
            ['label' => 'صورة التذكرة', 'met' => $this->hasDocument($case, Station::reception, 'ticket')],
        ];
    }

    /**
     * @return list<array{label: string, met: bool}>
     */
    private function practicalRequirements(WorkerCase $case): array
    {
        $evaluation = $case->evaluation()->first();

        return [
            ['label' => 'صورة الاختبار العملي', 'met' => $this->hasDocument($case, Station::practical, 'practical_photo')],
            ['label' => 'نموذج التقييم', 'met' => $evaluation !== null],
            ['label' => 'التوقيعان على النموذج', 'met' => $evaluation !== null && $evaluation->signed_at !== null],
        ];
    }

    private function hasDocument(WorkerCase $case, Station $station, string $type): bool
    {
        return $case->documents()
            ->where('station', $station)
            ->where('type', $type)
            ->exists();
    }
}
