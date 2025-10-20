<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Clock;

use DateTimeZone;
use Psr\Clock\ClockInterface;
use DateTimeImmutable;

final readonly class Clock implements ClockInterface
{
    public function __construct(
        private ?DateTimeZone $timezone = null,
    ) {
    }

    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', $this->timezone);
    }
}
