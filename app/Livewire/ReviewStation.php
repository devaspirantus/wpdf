<?php

namespace App\Livewire;

use App\Actions\SaveEvaluation;
use App\Enums\CaseStatus;
use App\Models\WorkerCase;
use Livewire\Component;

class ReviewStation extends Component
{
    public ?int $caseId = null;

    public function selectCase(int $caseId): void
    {
        $this->caseId = $caseId;
    }

    public function backToList(): void
    {
        $this->reset('caseId');
    }

    public function render()
    {
        $cases = WorkerCase::query()
            ->where('status', CaseStatus::review->value)
            ->orderByDesc('id')
            ->get(['id', 'full_name', 'ticket_number', 'passport_number']);

        $case = null;

        if ($this->caseId !== null) {
            $case = WorkerCase::query()
                ->with(['documents', 'evaluation'])
                ->find($this->caseId);

            if ($case === null || $case->status !== CaseStatus::review) {
                $case = null;
                $this->reset('caseId');
            }
        }

        return view('livewire.review-station', [
            'cases' => $cases,
            'case' => $case,
            'sections' => SaveEvaluation::SECTIONS,
            'scale' => SaveEvaluation::SCALE,
        ]);
    }
}
