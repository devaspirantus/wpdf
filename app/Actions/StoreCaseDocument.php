<?php

namespace App\Actions;

use App\Enums\Station;
use App\Models\Document;
use App\Models\WorkerCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class StoreCaseDocument
{
    /**
     * Store an image on the private disk and record it as a Document row.
     *
     * If a Document of the same case/station/type already exists,
     * the old file is deleted and the row is updated (retake handling).
     */
    public function handle(
        WorkerCase $case,
        Station $station,
        string $type,
        UploadedFile $file,
        int $userId,
    ): Document {
        $existing = $case->documents()
            ->where('station', $station)
            ->where('type', $type)
            ->first();

        if ($existing) {
            Storage::disk('local')->delete($existing->path);
        }

        $extension = $file->guessExtension() ?: 'jpg';

        $path = $file->storeAs(
            sprintf('cases/%d/%s', $case->id, $station->value),
            sprintf('%s.%s', $type, $extension),
            'local',
        );

        if ($existing) {
            $existing->update(['path' => $path, 'created_by' => $userId]);

            return $existing;
        }

        return $case->documents()->create([
            'station' => $station,
            'type' => $type,
            'path' => $path,
            'created_by' => $userId,
        ]);
    }
}
