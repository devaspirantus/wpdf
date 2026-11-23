<?php

namespace App\Queries;

use App\Enums\CaseStatus;
use App\Enums\Station;
use App\Models\WorkerCase;
use Illuminate\Database\Eloquent\Collection;

class PendingReceptionCases
{
    /**
     * Cases returned to the reception station by the review station.
     *
     * @return Collection<int, WorkerCase>
     */
    public static function collection(string $search = ''): Collection
    {
        $search = trim($search);

        return WorkerCase::query()
            ->where('status', CaseStatus::returned)
            ->whereHas('latestReturn', fn ($q) => $q->where('returned_to_station', Station::reception))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', '%'.$search.'%')
                        ->orWhere('ticket_number', 'like', '%'.$search.'%')
                        ->orWhere('passport_number', 'like', '%'.$search.'%');
                });
            })
            ->with('latestReturn.returnedBy')
            ->orderByDesc('created_at')
            ->get();
    }
}
