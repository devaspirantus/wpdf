<?php

namespace App\Livewire;

use App\Actions\SaveEvaluation;
use App\Enums\Difficulty;
use App\Enums\Station;
use App\Exceptions\IncompleteStationException;
use App\Models\WorkerCase;
use App\Services\CaseStateMachine;
use App\Services\StationCompletion;
use Livewire\Attributes\On;
use Livewire\Component;

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
        }

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
        $stationCompletion = app(StationCompletion::class);
        $this->requirements = $stationCompletion->requirements($this->case, Station::practical);
        $this->hasMissing = $stationCompletion->missing($this->case, Station::practical) !== [];
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
