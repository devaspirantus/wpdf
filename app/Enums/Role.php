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

    /**
     * Route name the user lands on after login. Theory and practical labs
     * land on the dashboard (which shows their pending queue).
     */
    public function landRoute(): ?string
    {
        return match ($this) {
            self::reception => 'reception',
            self::theory => 'dashboard',
            self::practical => 'dashboard',
            self::review => 'review',
        };
    }
}
