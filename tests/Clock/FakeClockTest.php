<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Tests\Clock;

use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use IngeniozIt\Psr\Clock\FakeClock;
use Psr\Clock\ClockInterface;

class FakeClockTest extends TestCase
{
    public function testIsAPsrClock(): void
    {
        $clock = new FakeClock();

        self::assertInstanceOf(
            ClockInterface::class,
            $clock,
            'FakeClock must be a PSR-20 clock',
        );
    }

    public function testUsesAFixedTime(): void
    {
        $fixedTime = new DateTimeImmutable('2024-01-01 12:34:56');
        $clock = new FakeClock($fixedTime);

        $now = $clock->now();

        self::assertEquals(
            $fixedTime,
            $now,
            'FakeClock must return the fixed time',
        );
    }

    public function testADateIntervalCanBeAdded(): void
    {
        $initialTime = new DateTimeImmutable('2024-01-01 12:34:56');
        $updatedTime = new DateTimeImmutable('2024-01-02 12:34:56');
        $dateInterval = new DateInterval('P1D');
        $clock = new FakeClock($initialTime);

        $clock->add($dateInterval);
        $now = $clock->now();

        self::assertEquals(
            $updatedTime,
            $now,
            'FakeClock must return the updated time after adding an interval',
        );
    }

    public function testADateIntervalCanBeSubtracted(): void
    {
        $initialTime = new DateTimeImmutable('2024-01-02 12:34:56');
        $updatedTime = new DateTimeImmutable('2024-01-01 12:34:56');
        $dateInterval = new DateInterval('P1D');
        $clock = new FakeClock($initialTime);

        $clock->sub($dateInterval);
        $now = $clock->now();

        self::assertEquals(
            $updatedTime,
            $now,
            'FakeClock must return the updated time after subtracting an interval',
        );
    }
}
