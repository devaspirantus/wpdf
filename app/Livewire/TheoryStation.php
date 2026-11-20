<?php

namespace App\Livewire;

use App\Enums\Station;
use App\Exceptions\IncompleteStationException;
use App\Models\WorkerCase;
use App\Services\CaseStateMachine;
use App\Services\StationCompletion;
use Livewire\Attributes\On;
use Livewire\Component;

class TheoryStation extends Component
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

        return view('livewire.theory-station');
    }

    private function refreshRequirements(): void
    {
        $this->case = $this->case->fresh();
        $stationCompletion = app(StationCompletion::class);
        $this->requirements = $stationCompletion->requirements($this->case, Station::theory);
        $this->hasMissing = $stationCompletion->missing($this->case, Station::theory) !== [];
    }
}
