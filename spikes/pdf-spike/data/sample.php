<?php

// Step 0 spike: fake data for the sample Arabic PDF.
// Not real workers — values are illustrative only.

use Carbon\Carbon;

return [
    'center' => 'مركز الاختبار المهنيّ — الرياض',
    'date' => Carbon::create(2026, 11, 19, 10, 30, 0)->setTimezone('Asia/Riyadh')->format('Y/m/d h:i A'),
    'trade' => 'كهربائيّ مبيدات',
    'candidate' => 'مصطفى بن سعيد العمرانيّ',
    'passport' => 'P-1042-5573-8821',
    'ticket' => 'TK-2026-0117',
    'task' => 'صيانة لوحة توزيع كهربائية منخفضة الجهد واستبدال مرخّص معيوب',
    'difficulty' => 'متوسط',
    'assessor' => 'م. خالد بن عبدالله الحربيّ',
    'assessorId' => '1123456789',
    'scores' => [4, 5, 4, 3, 5, 4, 3, 5, 4, 4, 3, 5, 4],
];
