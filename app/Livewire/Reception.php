<?php

namespace App\Livewire;

use App\Actions\CreateWorkerCase;
use App\Enums\CaseStatus;
use App\Enums\Station;
use App\Exceptions\IncompleteStationException;
use App\Models\WorkerCase;
use App\Queries\PendingReceptionCases;
use App\Services\CaseStateMachine;
use App\Services\StationCompletion;
use Livewire\Attributes\On;
use Livewire\Component;

class Reception extends Component
{
    public string $fullName = '';

    public string $passportNumber = '';

    public string $ticketNumber = '';

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
        $this->refreshRequirements();
    }

    public function backToList(): void
    {
        $this->case = null;
        $this->resetStationState();
        $this->hasMissing = true;
    }

    public function create(CreateWorkerCase $createWorkerCase): void
    {
        $this->reset(['case', 'advancedTo', 'advanceError', 'missingRequirements', 'requirements', 'isReturned', 'returnNote']);

        $this->validate(
            rules: [
                'fullName' => 'required|string|max:255',
                'passportNumber' => 'required|string|max:255',
                'ticketNumber' => 'required|string|max:255',
            ],
            messages: [
                'required' => 'هذا الحقل مطلوب',
                'max' => 'هذا الحقل يتجاوز الحد المسموح',
            ],
            attributes: [
                'fullName' => 'اسم العامل',
                'passportNumber' => 'رقم الجواز',
                'ticketNumber' => 'رقم التذكرة',
            ]
        );

        $this->case = $createWorkerCase->handle($this->fullName, $this->passportNumber, $this->ticketNumber);

        $this->reset('fullName', 'passportNumber', 'ticketNumber');

        $this->refreshRequirements();
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

            return;
        }

        $this->refreshRequirements();
        $this->advancedTo = true;
    }

    public function resubmit(CaseStateMachine $stateMachine): void
    {
        if ($this->case === null) {
            return;
        }

        $this->reset(['advanceError', 'missingRequirements', 'advancedTo']);

        $this->case = $stateMachine->resubmitToReview($this->case);
        $this->isReturned = false;
        $this->returnNote = null;
        $this->advancedTo = true;
    }

    #[On('case-document-stored')]
    public function documentStored(StationCompletion $stationCompletion, int $caseId): void
    {
        if ($this->case === null || (int) $this->case->id !== $caseId) {
            return;
        }

        $this->refreshRequirements($stationCompletion);
    }

    public function render()
    {
        return view('livewire.reception', [
            'returnedCases' => PendingReceptionCases::collection($this->search),
        ]);
    }

    private function refreshRequirements(?StationCompletion $stationCompletion = null): void
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

        $stationCompletion = $stationCompletion ?? app(StationCompletion::class);
        $this->requirements = $stationCompletion->requirements($this->case, Station::reception);
        $this->hasMissing = $stationCompletion->missing($this->case, Station::reception) !== [];
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
            && $lastReturn->returned_to_station === Station::reception;

        $this->returnNote = $this->isReturned ? $lastReturn->note : null;
    }
}
