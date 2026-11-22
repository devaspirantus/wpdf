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
     * Route name the user lands on after login. Theory labs land on the
     * dashboard (which shows their pending queue); practical uses the
     * shared case search screen (FR-01: entered via search → case link).
     */
    public function landRoute(): ?string
    {
        return match ($this) {
            self::reception => 'reception',
            self::theory => 'dashboard',
            self::practical => 'case.search',
            self::review => 'review',
        };
    }
}
