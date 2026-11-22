<?php

namespace App\Livewire;

use App\Queries\PendingTheoryCases;
use Livewire\Component;

class TheoryPendingCases extends Component
{
    public string $search = '';

    public function render()
    {
        return view('livewire.theory-pending-cases', [
            'pendingCases' => PendingTheoryCases::collection($this->search),
        ]);
    }
}
