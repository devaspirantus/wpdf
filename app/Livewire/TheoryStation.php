<?php

namespace App\Livewire;

use App\Enums\CaseStatus;
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

    public bool $isReturned = false;

    public ?string $returnNote = null;

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

    public function resubmit(CaseStateMachine $stateMachine): void
    {
        $this->reset(['advanceError', 'missingRequirements', 'advancedTo']);

        $this->case = $stateMachine->resubmitToReview($this->case);
        $this->isReturned = false;
        $this->returnNote = null;
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

        $this->syncReturnedState();

        $stationCompletion = app(StationCompletion::class);
        $this->requirements = $stationCompletion->requirements($this->case, Station::theory);
        $this->hasMissing = $stationCompletion->missing($this->case, Station::theory) !== [];
    }

    private function syncReturnedState(): void
    {
        $lastReturn = $this->case->returns()->latest('created_at')->first();

        $this->isReturned = $lastReturn !== null
            && $this->case->status === CaseStatus::returned
            && $lastReturn->returned_to_station === Station::theory;

        $this->returnNote = $this->isReturned ? $lastReturn->note : null;
    }
}
