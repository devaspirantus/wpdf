<?php

namespace App\Livewire;

use App\Actions\StoreCaseDocument;
use App\Enums\Station;
use App\Models\Document;
use App\Models\WorkerCase;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;

class CameraCapture extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $caseId;

    #[Locked]
    public Station $station;

    #[Locked]
    public string $type;

    #[Locked]
    public string $label;

    public $image;

    public function capture(StoreCaseDocument $storeCaseDocument): void
    {
        $this->validate(
            rules: [
                'image' => 'required|image|mimes:png,jpg,jpeg,webp|max:5120',
            ],
            messages: [
                'image.required' => 'يرجى التقاط الصورة قبل التأكيد',
                'image.image' => 'المرفق يجب أن يكون صورة',
                'image.max' => 'حجم الصورة كبير جداً',
            ]
        );

        $storeCaseDocument->handle(
            $this->case(),
            $this->station,
            $this->type,
            $this->image,
            auth()->id(),
        );

        $this->reset('image');

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
