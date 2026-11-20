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
}
