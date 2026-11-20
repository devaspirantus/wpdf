<?php

namespace App\Models;

use App\Enums\Difficulty;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'case_id',
    'task',
    'difficulty',
    'scores',
    'evaluator_name',
    'evaluator_id_number',
    'evaluator_signature',
    'worker_signature',
    'signed_at',
])]
class Evaluation extends Model
{
    const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'difficulty' => Difficulty::class,
            'scores' => 'array',
            'signed_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function workerCase(): BelongsTo
    {
        return $this->belongsTo(WorkerCase::class, 'case_id');
    }
}
