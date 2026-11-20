<?php

namespace App\Livewire;

use App\Actions\CreateWorkerCase;
use App\Enums\Station;
use App\Exceptions\IncompleteStationException;
use App\Models\WorkerCase;
use App\Services\CaseStateMachine;
use App\Services\StationCompletion;
use Livewire\Attributes\On;
use Livewire\Component;

class Reception extends Component
{
    public string $fullName = '';

    public string $passportNumber = '';

    public string $ticketNumber = '';

    public bool $created = false;

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

    public function create(CreateWorkerCase $createWorkerCase): void
    {
        $this->reset(['created', 'case', 'advancedTo', 'advanceError', 'missingRequirements', 'requirements']);

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
        $this->created = true;

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
        return view('livewire.reception');
    }

    private function refreshRequirements(?StationCompletion $stationCompletion = null): void
    {
        if ($this->case === null) {
            return;
        }

        $stationCompletion = $stationCompletion ?? app(StationCompletion::class);
        $this->requirements = $stationCompletion->requirements($this->case, Station::reception);
        $this->hasMissing = $stationCompletion->missing($this->case, Station::reception) !== [];
    }
}
