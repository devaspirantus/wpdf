<?php

namespace App\Models;

use App\Enums\Station;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['case_id', 'station', 'type', 'path', 'created_by'])]
class Document extends Model
{
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'station' => Station::class,
            'created_at' => 'datetime',
        ];
    }

    public function workerCase(): BelongsTo
    {
        return $this->belongsTo(WorkerCase::class, 'case_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
