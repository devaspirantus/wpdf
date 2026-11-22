<?php

namespace App\Livewire;

use App\Actions\GenerateCasePdf;
use App\Actions\ReturnCase;
use App\Actions\SaveEvaluation;
use App\Enums\CaseStatus;
use App\Enums\Station;
use App\Models\WorkerCase;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use LogicException;

class ReviewStation extends Component
{
    public ?int $caseId = null;

    public ?string $returnStation = null;

    public string $returnNote = '';

    public ?string $returnError = null;

    public ?string $returnSuccess = null;

    public ?string $pdfError = null;

    public ?string $pdfPath = null;

    public ?string $pdfPrintPath = null;

    public function selectCase(int $caseId): void
    {
        $this->caseId = $caseId;
        $this->resetReturnForm();
        $this->resetPdf();
    }

    public function backToList(): void
    {
        $this->reset('caseId');
        $this->resetReturnForm();
        $this->resetPdf();
    }

    public function generatePdf(): void
    {
        $this->resetPdf();

        $caseId = $this->caseId;

        if ($caseId === null) {
            $this->pdfError = 'لا توجد حالة مختارة';

            return;
        }

        $case = WorkerCase::find($caseId);

        if ($case === null) {
            $this->pdfError = 'الحالة غير موجودة';

            return;
        }

        try {
            app(GenerateCasePdf::class)->handle($case);
            $this->pdfPath = route('pdf.download', $case);
            $this->pdfPrintPath = route('pdf.print', $case);
            $this->returnSuccess = 'تم توليد ملف الـPDF بنجاح';
        } catch (LogicException $e) {
            $this->pdfError = $e->getMessage();
        }
    }

    public function submitReturn(): void
    {
        $this->validate([
            'returnStation' => ['required', 'string', 'in:reception,theory,practical'],
            'returnNote' => ['required', 'string', 'max:500'],
        ]);

        $caseId = $this->caseId;

        if ($caseId === null) {
            $this->returnError = 'لا توجد حالة مختارة';

            return;
        }

        $case = WorkerCase::find($caseId);

        if ($case === null) {
            $this->returnError = 'الحالة غير موجودة';

            return;
        }

        try {
            app(ReturnCase::class)->handle(
                $case,
                Station::from($this->returnStation),
                $this->returnNote,
                Auth::id(),
            );
            $this->caseId = null;
            $this->resetReturnForm();
            $this->returnSuccess = 'تم إرجاع الملف بنجاح';
        } catch (LogicException $e) {
            $this->returnError = $e->getMessage();
        }
    }

    public function render()
    {
        $cases = WorkerCase::query()
            ->where('status', CaseStatus::review->value)
            ->orderByDesc('id')
            ->get(['id', 'full_name', 'ticket_number', 'passport_number']);

        $case = null;
        $latestReturn = null;
        $caseId = $this->caseId;

        if ($caseId !== null) {
            $case = WorkerCase::query()
                ->with(['documents', 'evaluation', 'returns' => function ($query) {
                    $query->orderByDesc('id');
                }])
                ->find($caseId);

            if ($case === null || $case->status !== CaseStatus::review) {
                $case = null;
                $this->reset('caseId');
                $this->resetReturnForm();
                $caseId = null;
            }

            $latestReturn = $case?->returns->first();
        }

        return view('livewire.review-station', [
            'cases' => $cases,
            'case' => $case,
            'latestReturn' => $latestReturn,
            'sections' => SaveEvaluation::SECTIONS,
            'scale' => SaveEvaluation::SCALE,
        ]);
    }

    protected function resetReturnForm(): void
    {
        $this->reset('returnStation');
        $this->reset('returnNote');
        $this->reset('returnError');
        $this->reset('returnSuccess');
    }

    protected function resetPdf(): void
    {
        $this->reset('pdfError');
        $this->reset('pdfPath');
        $this->reset('pdfPrintPath');
    }
}
