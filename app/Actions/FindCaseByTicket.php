<?php

namespace App\Actions;

use App\Models\WorkerCase;

class FindCaseByTicket
{
    public function handle(string $ticketNumber): ?WorkerCase
    {
        return WorkerCase::query()
            ->where('ticket_number', trim($ticketNumber))
            ->first();
    }
}
