<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Tests\Clock;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use IngeniozIt\Psr\Clock\Clock;
use Psr\Clock\ClockInterface;

class ClockTest extends TestCase
{
    public function testIsAPsrClock(): void
    {
        $clock = new Clock();

        self::assertInstanceOf(
            ClockInterface::class,
            $clock,
            'Clock must be a PSR-20 clock',
        );
    }

    public function testGetsTheCurrentTime(): void
    {
        $clock = new Clock();

        $previous = new DateTimeImmutable();
        $now = $clock->now();
        $next = new DateTimeImmutable();

        self::assertBetween(
            $previous,
            $now,
            $next,
            'Clock time must be between previous and next time'
        );
    }

    public function testCanUseASpecificTimeZone(): void
    {
        $timeZone = new DateTimeZone('Europe/Paris');
        $clock = new Clock($timeZone);

        $now = $clock->now();

        self::assertEquals($timeZone, $now->getTimezone());
    }

    private static function assertBetween(
        DateTimeImmutable $previous,
        DateTimeImmutable $now,
        DateTimeImmutable $next,
        string $message
    ): void {
        self::assertGreaterThanOrEqual(
            $previous,
            $now,
            $message,
        );
        self::assertLessThanOrEqual(
            $next,
            $now,
            $message,
        );
    }
}
