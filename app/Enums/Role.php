<?php

namespace App\Enums;

enum Role: string
{
    case reception = 'reception';
    case theory = 'theory';
    case practical = 'practical';
    case review = 'review';

    public function label(): string
    {
        return match ($this) {
            self::reception => 'الاستقبال',
            self::theory => 'المعمل النظري',
            self::practical => 'المعمل العملي',
            self::review => 'رفع البيانات',
        };
    }
}
