<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;

class DocumentImageController extends Controller
{
    public function show(Document $document)
    {
        abort_if(! $document->workerCase || ! $document->workerCase->exists(), 404, 'المستند غير موجود');

        if (! Storage::disk('local')->exists($document->path)) {
            abort(404, 'المستند غير موجود');
        }

        $mimeType = Storage::disk('local')->mimeType($document->path) ?? 'image/jpeg';

        $extension = pathinfo($document->path, PATHINFO_EXTENSION) ?: 'bin';

        return Storage::disk('local')->response($document->path, 'document.'.$extension, [
            'Content-Type' => $mimeType,
        ]);
    }
}
