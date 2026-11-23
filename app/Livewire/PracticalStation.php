<?php

namespace App\Livewire;

use App\Actions\SaveEvaluation;
use App\Actions\SignEvaluation;
use App\Enums\CaseStatus;
use App\Enums\Difficulty;
use App\Enums\Station;
use App\Exceptions\IncompleteStationException;
use App\Models\WorkerCase;
use App\Services\CaseStateMachine;
use App\Services\StationCompletion;
use Livewire\Attributes\On;
use Livewire\Component;
use LogicException;

class PracticalStation extends Component
{
    public WorkerCase $case;

    public bool $advancedTo = false;

    public ?string $advanceError = null;

    /** @var list<string> */
    public array $missingRequirements = [];

    /**
     * @var list<array{label: string, met: bool}>
     */
    public array $requirements = [];

    public bool $hasMissing = true;

    public string $task = '';

    public string $difficulty = Difficulty::easy->value;

    /** @var array<int, int> */
    public array $scores = [];

    public bool $evaluationSaved = false;

    public string $evaluatorSignature = '';

    public string $workerSignature = '';

    public bool $isSigned = false;

    public ?string $signedAt = null;

    public bool $isReturned = false;

    public ?string $returnNote = null;

    public function mount(): void
    {
        $this->scores = $this->defaultScores();

        $evaluation = $this->case->evaluation;

        if ($evaluation) {
            $this->task = $evaluation->task;
            $this->difficulty = $evaluation->difficulty->value;
            $this->scores = array_combine(
                array_keys($this->defaultScores()),
                array_values((array) $evaluation->scores),
            );
            $this->syncSignedState();
        }
    }

    public function advance(CaseStateMachine $stateMachine): void
    {
        $this->reset(['advanceError', 'missingRequirements', 'advancedTo']);

        try {
            $this->case = $stateMachine->advance($this->case);
        } catch (IncompleteStationException $e) {
            $this->advanceError = $e->getMessage();
            $this->missingRequirements = $e->missing;

            $this->refreshRequirements();

            return;
        } catch (LogicException $e) {
            $this->advanceError = $e->getMessage();
            $this->missingRequirements = [];

            return;
        }

        $this->advancedTo = true;
    }

    public function resubmit(CaseStateMachine $stateMachine): void
    {
        $this->reset(['advanceError', 'missingRequirements', 'advancedTo']);

        $missing = app(StationCompletion::class)->missing($this->case->fresh(), Station::practical);
        if ($missing !== []) {
            $this->advanceError = 'لا يمكن الإرجاع للمراجعة قبل اكتمال مستندات المختبر العملي';
            $this->missingRequirements = $missing;

            return;
        }

        $this->case = $stateMachine->resubmitToReview($this->case);
        $this->isReturned = false;
        $this->returnNote = null;
        $this->advancedTo = true;
    }

    public function saveEvaluation(SaveEvaluation $action): void
    {
        $action->handle(
            $this->case,
            $this->task,
            Difficulty::from($this->difficulty),
            (array) $this->scores,
        );

        $this->evaluationSaved = true;
        $this->refreshRequirements();
    }

    public function signEvaluation(SignEvaluation $action): void
    {
        $action->handle($this->case, $this->evaluatorSignature, $this->workerSignature);

        $this->syncSignedState();
        $this->refreshRequirements();
    }

    #[On('case-document-stored')]
    public function documentStored(int $caseId): void
    {
        if ((int) $this->case->id !== $caseId) {
            return;
        }

        $this->refreshRequirements();
    }

    public function render()
    {
        $this->refreshRequirements();

        return view('livewire.practical-station', [
            'sections' => SaveEvaluation::SECTIONS,
            'scale' => SaveEvaluation::SCALE,
        ]);
    }

    private function refreshRequirements(): void
    {
        $this->case = $this->case->fresh();
        $this->syncSignedState();
        $this->syncReturnedState();
        $stationCompletion = app(StationCompletion::class);
        $this->requirements = $stationCompletion->requirements($this->case, Station::practical);
        $this->hasMissing = $stationCompletion->missing($this->case, Station::practical) !== [];
    }

    private function syncSignedState(): void
    {
        $evaluation = $this->case->evaluation;

        if ($evaluation && $evaluation->signed_at !== null) {
            $this->isSigned = true;
            $this->signedAt = $evaluation->signed_at->format('Y-m-d H:i');
            $this->evaluatorSignature = (string) $evaluation->evaluator_signature;
            $this->workerSignature = (string) $evaluation->worker_signature;
        } else {
            $this->isSigned = false;
            $this->signedAt = null;
        }
    }

    private function syncReturnedState(): void
    {
        $lastReturn = $this->case->returns()->latest('created_at')->first();

        $this->isReturned = $lastReturn !== null
            && $this->case->status === CaseStatus::returned
            && $lastReturn->returned_to_station === Station::practical;

        $this->returnNote = $this->isReturned ? $lastReturn->note : null;
    }

    /**
     * @return array<int, int>
     */
    private function defaultScores(): array
    {
        $scores = [];
        foreach (SaveEvaluation::SECTIONS as $criteria) {
            foreach (array_keys($criteria) as $key) {
                $scores[$key] = 0;
            }
        }

        return $scores;
    }
}
