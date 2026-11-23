<?php

namespace App\Livewire;

use App\Enums\CaseStatus;
use App\Enums\Station;
use App\Exceptions\IncompleteStationException;
use App\Models\WorkerCase;
use App\Queries\PendingTheoryCases;
use App\Services\CaseStateMachine;
use App\Services\StationCompletion;
use Livewire\Attributes\On;
use Livewire\Component;

class TheoryStation extends Component
{
    public ?WorkerCase $case = null;

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

    public string $search = '';

    public function selectCase(int $id): void
    {
        $case = WorkerCase::query()->find($id);

        if ($case === null) {
            return;
        }

        $this->case = $case;
        $this->resetStationState();
    }

    public function backToList(): void
    {
        $this->case = null;
        $this->resetStationState();
        $this->hasMissing = true;
    }

    public function advance(CaseStateMachine $stateMachine): void
    {
        if ($this->case === null) {
            return;
        }

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
        if ($this->case === null) {
            return;
        }

        $this->reset(['advanceError', 'missingRequirements', 'advancedTo']);

        $missing = app(StationCompletion::class)->missing($this->case->fresh(), Station::theory);
        if ($missing !== []) {
            $this->advanceError = 'لا يمكن الإرجاع للمراجعة قبل اكتمال مستندات المختبر النظري';
            $this->missingRequirements = $missing;

            return;
        }

        $this->case = $stateMachine->resubmitToReview($this->case);
        $this->isReturned = false;
        $this->returnNote = null;
        $this->advancedTo = true;
    }

    #[On('case-document-stored')]
    public function documentStored(int $caseId): void
    {
        if ($this->case === null || (int) $this->case->id !== $caseId) {
            return;
        }

        $this->refreshRequirements();
    }

    public function render()
    {
        $this->refreshRequirements();

        $cases = $this->pendingCases();

        return view('livewire.theory-station', ['pendingCases' => $cases]);
    }

    private function pendingCases()
    {
        return PendingTheoryCases::collection($this->search);
    }

    private function refreshRequirements(): void
    {
        if ($this->case === null) {
            $this->requirements = [];
            $this->hasMissing = true;
            $this->isReturned = false;
            $this->returnNote = null;

            return;
        }

        $this->case = $this->case->fresh();

        $this->syncReturnedState();

        $stationCompletion = app(StationCompletion::class);
        $this->requirements = $stationCompletion->requirements($this->case, Station::theory);
        $this->hasMissing = $stationCompletion->missing($this->case, Station::theory) !== [];
    }

    private function resetStationState(): void
    {
        $this->advanceError = null;
        $this->advancedTo = false;
        $this->missingRequirements = [];
        $this->requirements = [];
        $this->hasMissing = true;
        $this->isReturned = false;
        $this->returnNote = null;
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
