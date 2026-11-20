<?php

namespace App\Models;

use App\Enums\CaseStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['full_name', 'passport_number', 'ticket_number', 'status'])]
class WorkerCase extends Model
{
    protected function casts(): array
    {
        return [
            'status' => CaseStatus::class,
        ];
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class, 'case_id');
    }

    public function evaluation(): HasOne
    {
        return $this->hasOne(Evaluation::class, 'case_id');
    }

    public function stationVisits(): HasMany
    {
        return $this->hasMany(StationVisit::class, 'case_id');
    }

    public function returns(): HasMany
    {
        return $this->hasMany(CaseReturn::class, 'case_id');
    }
}
