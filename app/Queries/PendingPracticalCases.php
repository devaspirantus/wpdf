<?php

namespace App\Queries;

use App\Enums\CaseStatus;
use App\Enums\Station;
use App\Models\WorkerCase;
use Illuminate\Database\Eloquent\Collection;

class PendingPracticalCases
{
    /**
     * Cases waiting at the practical lab: status is practical, or returned to practical.
     *
     * @return Collection<int, WorkerCase>
     */
    public static function collection(string $search = ''): Collection
    {
        $search = trim($search);

        return WorkerCase::query()
            ->where(function ($q) {
                $q->where('status', CaseStatus::practical)
                    ->orWhere(function ($rq) {
                        $rq->where('status', CaseStatus::returned)
                            ->whereHas('latestReturn', fn ($rq2) => $rq2->where('returned_to_station', Station::practical));
                    });
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', '%'.$search.'%')
                        ->orWhere('ticket_number', 'like', '%'.$search.'%')
                        ->orWhere('passport_number', 'like', '%'.$search.'%');
                });
            })
            ->with('latestReturn.returnedBy')
            ->withExists([
                'documents as has_practical_photo' => function ($q) {
                    $q->where('station', Station::practical)
                        ->where('type', 'practical_photo');
                },
            ])
            ->with('evaluation')
            ->orderByDesc('created_at')
            ->get();
    }
}
