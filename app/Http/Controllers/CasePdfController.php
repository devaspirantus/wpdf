<?php

namespace App\Http\Controllers;

use App\Models\WorkerCase;
use Illuminate\Support\Facades\Storage;

class CasePdfController extends Controller
{
    public function download(WorkerCase $case)
    {
        $latest = $this->latest($case);

        return $latest->disk->download($latest->path, $latest->name);
    }

    public function print(WorkerCase $case)
    {
        $latest = $this->latest($case);

        return response($latest->disk->get($latest->path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.rawurlencode($latest->name).'"',
        ]);
    }

    private function latest(WorkerCase $case): object
    {
        abort_if(! $case->exists(), 404, 'الحالة غير موجودة');

        $disk = Storage::disk('local');
        $path = "cases/{$case->id}/pdf";

        if (! $disk->exists($path)) {
            abort(404, 'لم يتم توليد ملف الـPDF بعد');
        }

        $files = collect($disk->files($path))
            ->flatMap(function (string $file) {
                $parts = pathinfo($file);

                return ($parts['extension'] ?? '') === 'pdf' ? [$parts['filename']] : [];
            })
            ->sort()
            ->values();

        $latest = $files->last();

        if ($latest === null) {
            abort(404, 'لم يتم توليد ملف الـPDF بعد');
        }

        return (object) [
            'disk' => $disk,
            'path' => "{$path}/{$latest}.pdf",
            'name' => "{$latest}.pdf",
        ];
    }
}
