<?php

/**
 * Generates two handwriting-style signature PNGs (Arabic, with tashkeel)
 * using GD. Run inside the Sail app container:
 *
 *   docker compose exec app php spikes/pdf-spike/bin/make-signatures.php
 *
 * Output:
 *   spikes/pdf-spike/public/sig-assessor.png
 *   spikes/pdf-spike/public/sig-candidate.png
 */

use Illuminate\Contracts\Console\Kernel;

$root = dirname(__DIR__, 3);
require $root.'/vendor/autoload.php';
$app = require $root.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$outDir = dirname(__DIR__).'/public';
$font = '/usr/share/fonts/truetype/noto/NotoNaskhArabic-Regular.ttf';

$lines = [
    'sig-assessor' => 'محمد عبدالله العتيبيّ',
    'sig-candidate' => 'أحمد إبراهيم السُّلَيمانيّ',
];

foreach ($lines as $name => $text) {
    $w = 640;
    $h = 240;
    $img = imagecreatetruecolor($w, $h);
    $white = imagecolorallocate($img, 255, 255, 255);
    $ink = imagecolorallocate($img, 20, 40, 160);
    imagefilledrectangle($img, 0, 0, $w, $h, $white);

    // Handwriting-style wavy line.
    for ($i = 0; $i < 20; $i++) {
        $x = 40 + $i * 28;
        $y = 110 + (int) round(28 * sin($i * 1.1));
        for ($t = 0; $t < 28; $t++) {
            $x2 = 40 + ($i + 1) * 28;
            $y2 = 110 + (int) round(28 * sin(($i + 1) * 1.1));
            $step = max(1, (int) abs($x2 - $x));
            for ($s = 0; $s <= $step; $s++) {
                $px = (int) ($x + ($x2 - $x) * $s / $step);
                $py = (int) ($y + ($y2 - $y) * $s / $step - $t * 4 / 28);
                imagesetpixel($img, $px, $py, $ink);
            }
        }
    }
    // A crossing stroke, like a signed flourish.
    imageline($img, 120, 90, 520, 150, $ink);

    if (is_file($font)) {
        $size = 42;
        $bbox = imagettfbbox($size, 0, $font, $text);
        $tw = $bbox[2] - $bbox[0];
        imagettftext($img, $size, 0, (int) (($w - $tw) / 2 - $bbox[0]), 200, $ink, $font, $text);
    }

    imagepng($img, $outDir.'/'.$name.'.png');
    imagedestroy($img);
    echo "wrote {$name}.png\n";
}
