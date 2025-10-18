<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Tests;

use PHPUnit\Framework\TestCase;
use IngeniozIt\Psr\HelloWorld;

final class HelloWorldTest extends TestCase
{
    public function testUnitTestsAreWorking(): void
    {
        $foo = new HelloWorld();

        self::assertEquals('Hello, world!', $foo->helloWorld());
    }
}
