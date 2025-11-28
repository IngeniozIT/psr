<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Tests\Http\Message;

use IngeniozIt\Psr\Http\Message\Exception\InvalidResource;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use IngeniozIt\Psr\Http\Message\Stream;

class StreamTest extends TestCase
{
    /** @param resource $resource */
    private function getStream($resource): StreamInterface
    {
        return new Stream($resource);
    }

    public function testIsAPsrStream(): void
    {
        /** @var resource $resource */
        $resource = fopen('php://temp', 'r+');
        $stream = $this->getStream($resource);

        self::assertInstanceOf(StreamInterface::class, $stream);
    }

    public function testNeedsAResource(): void
    {
        self::expectException(InvalidResource::class);
        self::expectExceptionMessage("Invalid resource provided");
        /** @phpstan-ignore argument.type */
        new Stream(42);
    }

    public function testCanBeDetached(): void
    {
        /** @var resource $resource */
        $resource = fopen('php://temp', 'r+');
        $stream = $this->getStream($resource);

        $detachedResource = $stream->detach();

        self::assertSame($resource, $detachedResource);
        self::assertNull($stream->detach());
        self::assertNull($stream->getSize());
        $stream->close();
    }

    public function testCanBeClosed(): void
    {
        /** @var resource $resource */
        $resource = fopen('php://temp', 'r+');
        $stream = $this->getStream($resource);

        $stream->close();
        $detachedResource = $stream->detach();

        self::assertNotNull($detachedResource);
        self::assertFalse(is_resource($detachedResource));
    }

    /** @param resource $resource */
    #[DataProvider('provideResourcesWithSize')]
    public function testCanGetStreamSize($resource, int $expectedSize): void
    {
        $size = $this->getStream($resource)->getSize();

        self::assertEquals($expectedSize, $size);
    }

    /** @return array<string, array{resource, int}> */
    public static function provideResourcesWithSize(): array
    {
        /** @var resource $emptyResource */
        $emptyResource = fopen('php://temp', 'r+');

        /** @var resource $nonEmptyResource */
        $nonEmptyResource = fopen('php://temp', 'r+');
        fwrite($nonEmptyResource, 'foo');

        return [
            'empty resource' => [$emptyResource, 0],
            'non-empty resource' => [$nonEmptyResource, 3],
        ];
    }
}
