<?php

namespace App\Models;

use App\Enums\Station;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['case_id', 'station', 'user_id', 'started_at', 'completed_at'])]
class StationVisit extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return [
            'station' => Station::class,
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function workerCase(): BelongsTo
    {
        return $this->belongsTo(WorkerCase::class, 'case_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
