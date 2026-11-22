<?php

namespace App\Queries;

use App\Enums\CaseStatus;
use App\Enums\Station;
use App\Models\WorkerCase;
use Illuminate\Database\Eloquent\Collection;

class PendingTheoryCases
{
    /**
     * Cases waiting at the theory lab: status is theory, or returned to theory.
     *
     * @return Collection<int, WorkerCase>
     */
    public static function collection(string $search = ''): Collection
    {
        $search = trim($search);

        return WorkerCase::query()
            ->where(function ($q) {
                $q->where('status', CaseStatus::theory)
                    ->orWhere(function ($rq) {
                        $rq->where('status', CaseStatus::returned)
                            ->whereHas('returns', fn ($rq2) => $rq2->where('returned_to_station', Station::theory));
                    });
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', '%'.$search.'%')
                        ->orWhere('ticket_number', 'like', '%'.$search.'%')
                        ->orWhere('passport_number', 'like', '%'.$search.'%');
                });
            })
            ->withExists([
                'documents as has_theory_photo' => function ($q) {
                    $q->where('station', Station::theory)
                        ->where('type', 'theory_photo');
                },
            ])
            ->orderByDesc('created_at')
            ->get();
    }
}
