<?php

namespace App\Models;

use App\Enums\Station;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['case_id', 'returned_to_station', 'note', 'returned_by'])]
class CaseReturn extends Model
{
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'returned_to_station' => Station::class,
            'created_at' => 'datetime',
        ];
    }

    public function workerCase(): BelongsTo
    {
        return $this->belongsTo(WorkerCase::class, 'case_id');
    }

    public function returnedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'returned_by');
    }
}
