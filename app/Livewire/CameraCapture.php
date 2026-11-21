<?php

namespace App\Livewire;

use App\Actions\StoreCaseDocument;
use App\Enums\Station;
use App\Models\Document;
use App\Models\WorkerCase;
use Illuminate\Http\UploadedFile;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CameraCapture extends Component
{
    #[Locked]
    public int $caseId;

    #[Locked]
    public Station $station;

    #[Locked]
    public string $type;

    #[Locked]
    public string $label;

    public function saveImage(string $imageData): void
    {
        $parts = explode(',', $imageData, 2);

        if (count($parts) !== 2 || ! preg_match('/^data:image\/(jpeg|png);base64$/', $parts[0])) {
            $this->addError('imageData', 'المرفق يجب أن يكون صورة');

            return;
        }

        $binary = base64_decode($parts[1], true);

        if ($binary === false) {
            $this->addError('imageData', 'الصيغة البيانية للصورة غير صالحة');

            return;
        }

        if (strlen($binary) > 5 * 1024 * 1024) {
            $this->addError('imageData', 'حجم الصورة كبير جداً');

            return;
        }

        $info = @getimagesizefromstring($binary);

        if ($info === false) {
            $this->addError('imageData', 'الصورة غير صالحة');

            return;
        }

        $mimeType = str_starts_with($parts[0], 'data:image/png') ? 'image/png' : 'image/jpeg';
        $extension = $mimeType === 'image/png' ? 'png' : 'jpg';

        $tempPath = tempnam(sys_get_temp_dir(), 'capture_');

        file_put_contents($tempPath, $binary);

        try {
            $file = new UploadedFile(
                $tempPath,
                sprintf('%s.%s', $this->type, $extension),
                $mimeType,
                null,
                true,
            );

            app(StoreCaseDocument::class)->handle(
                $this->case(),
                $this->station,
                $this->type,
                $file,
                auth()->id(),
            );
        } finally {
            @unlink($tempPath);
        }

        $this->dispatch('case-document-stored', caseId: $this->caseId);
    }

    public function existingDocument(): ?Document
    {
        return $this->case()->documents()
            ->where('station', $this->station)
            ->where('type', $this->type)
            ->first();
    }

    public function render()
    {
        return view('livewire.camera-capture', [
            'existing' => $this->existingDocument(),
        ]);
    }

    private function case(): WorkerCase
    {
        return WorkerCase::query()->findOrFail($this->caseId);
    }
}
