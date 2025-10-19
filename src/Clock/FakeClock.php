<?php

namespace IngeniozIt\Psr\Clock;

use DateInterval;
use DateTimeImmutable;
use Psr\Clock\ClockInterface;

class FakeClock implements ClockInterface
{
    public function __construct(
        private DateTimeImmutable $now = new DateTimeImmutable(),
    ) {
    }

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }

    public function add(DateInterval $dateInterval): void
    {
        $this->now = $this->now->add($dateInterval);
    }

    public function sub(DateInterval $dateInterval): void
    {
        $this->now = $this->now->sub($dateInterval);
    }
}
