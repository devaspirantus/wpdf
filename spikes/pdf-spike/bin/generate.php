<?php

// Step 0 spike: generate the sample Arabic PDF with the locked driver (Browsershot).
// Usage: php spikes/pdf-spike/bin/generate.php
// Run inside the Sail container (Chrome + Noto fonts live there).

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Blade;
use Spatie\Browsershot\Browsershot;

require dirname(__DIR__, 3).'/vendor/autoload.php';

$spikeDir = dirname(__DIR__);
$outDir = $spikeDir.'/output';
if (! is_dir($outDir)) {
    mkdir($outDir, 0755, true);
}

$app = require dirname(__DIR__, 3).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$data = require $spikeDir.'/data/sample.php';

$sigAssessorBase64 = base64_encode(file_get_contents($spikeDir.'/public/sig-assessor.png'));
$sigCandidateBase64 = base64_encode(file_get_contents($spikeDir.'/public/sig-candidate.png'));

$tpl = file_get_contents($spikeDir.'/templates/pdf.blade.php');
$html = Blade::render($tpl, [
    'data' => $data,
    'scores' => $data['scores'],
    'sigAssessorBase64' => $sigAssessorBase64,
    'sigCandidateBase64' => $sigCandidateBase64,
]);
file_put_contents($outDir.'/rendered.html', $html);
echo 'HTML rendered: '.strlen($html)." bytes\n";

Browsershot::html($html)
    ->format('A4')
    ->margins(15, 12, 15, 12, 'mm')
    ->addChromiumArguments(['no-sandbox', 'disable-dev-shm-usage'])
    ->save($outDir.'/browsershot.pdf');

echo 'Browsershot PDF: '.round(filesize($outDir.'/browsershot.pdf') / 1024).' KB -> output/browsershot.pdf'.PHP_EOL;
