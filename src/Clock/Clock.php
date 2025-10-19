<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Clock;

use Psr\Clock\ClockInterface;
use DateTimeImmutable;

final readonly class Clock implements ClockInterface
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
