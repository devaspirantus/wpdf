<?php

namespace App\Http\Controllers;

use App\Models\WorkerCase;
use Illuminate\Support\Facades\Storage;

class CasePdfController extends Controller
{
    public function download(WorkerCase $case)
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

        return $disk->download("{$path}/{$latest}.pdf", "{$latest}.pdf");
    }
}
