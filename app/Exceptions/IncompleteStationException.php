<?php

namespace App\Exceptions;

use App\Enums\Station;
use App\Models\WorkerCase;
use RuntimeException;

class IncompleteStationException extends RuntimeException
{
    public function __construct(
        public readonly WorkerCase $case,
        public readonly Station $station,
        public readonly array $missing,
    ) {
        parent::__construct($this->buildMessage($station, $missing));
    }

    private function buildMessage(Station $station, array $missing): string
    {
        return sprintf('المحطة %s غير مكتملة: %s', $station->arabicName(), implode('، ', $missing));
    }
}
