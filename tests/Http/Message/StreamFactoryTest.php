<?php

declare(strict_types=1);

namespace Http\Message;

use IngeniozIt\Psr\Http\Message\StreamFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use ValueError;

class StreamFactoryTest extends TestCase
{
    public function testIsAPsrStreamFactory(): void
    {
        $streamFactory = new StreamFactory();

        self::assertInstanceOf(StreamFactoryInterface::class, $streamFactory);
    }

    public function testCanCreateAStream(): void
    {
        $streamFactory = new StreamFactory();

        $stream = $streamFactory->createStream();

        self::assertInstanceOf(StreamInterface::class, $stream);
    }

    public function testCanCreateAStreamWithContent(): void
    {
        $streamFactory = new StreamFactory();

        $stream = $streamFactory->createStream('test');

        self::assertEquals('test', (string) $stream);
    }

    public function testCanCreateAStreamFromAFile(): void
    {
        $streamFactory = new StreamFactory();

        $stream = $streamFactory->createStreamFromFile(__FILE__);

        self::assertEquals(file_get_contents(__FILE__), (string) $stream);
    }

    public function testCannotCreateAStreamFromAnInvalidFile(): void
    {
        $streamFactory = new StreamFactory();

        self::expectException(ValueError::class);
        $streamFactory->createStreamFromFile("\0");
    }

    public function testCanCreateAStreamFromAResource(): void
    {
        $streamFactory = new StreamFactory();
        /** @var resource $resource */
        $resource = fopen(__FILE__, 'r');

        $stream = $streamFactory->createStreamFromResource($resource);

        self::assertEquals(file_get_contents(__FILE__), (string) $stream);
    }
}
