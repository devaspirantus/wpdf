<?php

namespace App\Livewire;

use App\Actions\FindCaseByTicket;
use App\Models\WorkerCase;
use Livewire\Component;

class CaseSearch extends Component
{
    public string $ticketNumber = '';

    public ?WorkerCase $case = null;

    public bool $searched = false;

    public ?string $statusArabic = null;

    public function search(FindCaseByTicket $findCaseByTicket): void
    {
        $this->reset(['case', 'searched', 'statusArabic']);

        $this->validate([
            'ticketNumber' => 'required|string|max:255',
        ], [
            'required' => 'رقم التذكرة مطلوب',
            'max' => 'رقم التذكرة يتجاوز الحد المسموح',
        ]);

        $this->case = $findCaseByTicket->handle($this->ticketNumber);
        $this->searched = true;

        if ($this->case !== null) {
            $this->statusArabic = $this->case->status->arabicName();
        }
    }

    public function render()
    {
        return view('livewire.case-search');
    }
}
