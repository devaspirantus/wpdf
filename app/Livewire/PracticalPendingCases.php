<?php

namespace App\Livewire;

use App\Queries\PendingPracticalCases;
use Livewire\Component;

class PracticalPendingCases extends Component
{
    public string $search = '';

    public function render()
    {
        return view('livewire.practical-pending-cases', [
            'pendingCases' => PendingPracticalCases::collection($this->search),
        ]);
    }
}
