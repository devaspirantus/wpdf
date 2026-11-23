<?php

namespace App\Actions;

use App\Enums\Difficulty;
use App\Enums\Station;
use App\Models\WorkerCase;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Storage;
use LogicException;
use Spatie\Browsershot\Browsershot;

class GenerateCasePdf
{
    /**
     * @param  array<int, array{type: string, station: Station}>  $requiredDocuments
     */
    public const REQUIRED_DOCUMENTS = [
        ['type' => 'worker_photo', 'station' => 'reception'],
        ['type' => 'passport', 'station' => 'reception'],
        ['type' => 'ticket', 'station' => 'reception'],
        ['type' => 'theory_photo', 'station' => 'theory'],
        ['type' => 'practical_photo', 'station' => 'practical'],
    ];

    public const DOCUMENT_LABELS = [
        'worker_photo' => 'صورة العامل',
        'passport' => 'صورة جواز السفر (صفحة البيانات)',
        'ticket' => 'صورة تذكرة الاختبار',
        'theory_photo' => 'صورة العامل في المعمل النظري',
        'practical_photo' => 'صورة العامل في المعمل العملي',
    ];

    public function handle(WorkerCase $case): string
    {
        $missing = $this->missingItems($case);

        if ($missing !== []) {
            throw new LogicException('لا يمكن توليد الـPDF قبل اكتمال الملف. الناقص: '.implode('، ', $missing));
        }

        $evaluation = $case->evaluation;

        $documents = $case->documents;

        $images = [];
        foreach (self::REQUIRED_DOCUMENTS as $required) {
            $document = $documents->firstWhere('type', $required['type']);

            $images[] = [
                'label' => self::DOCUMENT_LABELS[$required['type']],
                'src' => $this->imageDataUri($document->path),
                'time' => $document->created_at->format('d/m/Y H:i'),
                'station' => $this->stationName(Station::from($required['station'])),
            ];
        }

        $receptionTime = $documents
            ->filter(fn ($d) => $d->station === Station::reception)
            ->min('created_at')
            ->format('d/m/Y H:i');

        $html = Blade::render('pdf.case-report', [
            'case' => $case,
            'evaluation' => $evaluation,
            'images' => $images,
            'receptionTime' => $receptionTime,
            'theoryTime' => $documents->firstWhere('type', 'theory_photo')->created_at->format('d/m/Y H:i'),
            'practicalTime' => $documents->firstWhere('type', 'practical_photo')->created_at->format('d/m/Y H:i'),
            'reviewTime' => $evaluation->signed_at->format('d/m/Y H:i'),
            'sections' => SaveEvaluation::SECTIONS,
            'scale' => SaveEvaluation::SCALE,
            'difficulty' => $this->difficultyName($evaluation->difficulty),
            'centerName' => config('wpdf.center_name'),
        ]);

        $date = now()->format('Y-m-d');
        $relPath = "cases/{$case->id}/pdf/{$case->ticket_number}_{$date}_v1.pdf";
        $dir = Storage::disk('local')->path('cases/'.$case->id.'/pdf');

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $path = $dir.DIRECTORY_SEPARATOR.basename($relPath);

        Browsershot::html($html)
            ->setChromePath('/usr/bin/google-chrome-stable')
            ->format('A4')
            ->margins(0, 0, 12, 0, 'mm')
            ->footerHtml('<div style="font-size:9px; font-family:sans-serif; color:#404942; border-top:1px solid #bfc9c0; width:88%; margin:0 auto; padding-top:3px; text-align:center;">منصة اختبارات العمالة — صفحة <span class="pageNumber"></span> من <span class="totalPages"></span></div>')
            ->showBrowserHeaderAndFooter()
            ->addChromiumArguments(['no-sandbox', 'disable-dev-shm-usage'])
            ->waitUntilNetworkIdle(false)
            ->delay(500)
            ->save($path);

        if (! file_exists($path)) {
            throw new LogicException('فشل توليد ملف الـPDF. حاول مجددا.');
        }

        return $relPath;
    }

    /**
     * @return list<string>
     */
    protected function missingItems(WorkerCase $case): array
    {
        $missing = [];

        $documents = $case->documents;

        foreach (self::REQUIRED_DOCUMENTS as $required) {
            if (! $documents->contains('type', $required['type'])) {
                $missing[] = self::DOCUMENT_LABELS[$required['type']];
            }
        }

        $evaluation = $case->evaluation;

        if ($evaluation === null) {
            $missing[] = 'نموذج التقييم';
        } else {
            if (! $evaluation->evaluator_signature || ! $evaluation->worker_signature) {
                $missing[] = 'التوقيعان (المقيّم والعامل)';
            }
            if (! $evaluation->signed_at) {
                $missing[] = 'تأكيد التوقيع';
            }
        }

        return $missing;
    }

    protected function imageDataUri(string $relativePath): string
    {
        $disk = Storage::disk('local');
        $mime = $disk->mimeType($relativePath) ?? 'application/octet-stream';

        return 'data:'.$mime.';base64,'.base64_encode($disk->get($relativePath));
    }

    protected function difficultyName(Difficulty $difficulty): string
    {
        return [
            'easy' => 'سهل',
            'medium' => 'متوسط',
            'hard' => 'صعب',
        ][$difficulty->value];
    }

    protected function stationName(Station $station): string
    {
        return match ($station) {
            Station::reception => 'محطة الاستقبال',
            Station::theory => 'المختبر النظري',
            Station::practical => 'المختبر العملي',
            Station::review => 'محطة رفع البيانات',
        };
    }
}
