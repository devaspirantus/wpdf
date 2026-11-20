<?php

namespace App\Enums;

enum CaseStatus: string
{
    case reception = 'reception';
    case theory = 'theory';
    case practical = 'practical';
    case review = 'review';
    case completed = 'completed';
    case returned = 'returned';
    case cancelled = 'cancelled';

    public function arabicName(): string
    {
        return match ($this) {
            CaseStatus::reception => 'الاستقبال',
            CaseStatus::theory => 'المختبر النظري',
            CaseStatus::practical => 'المختبر العملي',
            CaseStatus::review => 'مراجعة البيانات',
            CaseStatus::completed => 'مكتمل',
            CaseStatus::returned => 'مُرجع',
            CaseStatus::cancelled => 'ملغى',
        };
    }
}
