<?php

namespace App\Enums;

enum Station: string
{
    case reception = 'reception';
    case theory = 'theory';
    case practical = 'practical';
    case review = 'review';

    public function arabicName(): string
    {
        return match ($this) {
            Station::reception => 'الاستقبال',
            Station::theory => 'المختبر النظري',
            Station::practical => 'المختبر العملي',
            Station::review => 'مراجعة البيانات',
        };
    }
}
