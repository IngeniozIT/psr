<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Tests\Http\Message;

use IngeniozIt\Psr\Http\Message\Exception\InvalidResource;
use IngeniozIt\Psr\Http\Message\Exception\CannotTellStream;
use IngeniozIt\Psr\Http\Message\Exception\CannotSeekStream;
use IngeniozIt\Psr\Http\Message\Exception\CannotWriteToStream;
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
        self::assertTrue($stream->eof());
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

    /** @param resource $resource */
    #[DataProvider('provideResourcesWithStreamPosition')]
    public function testCanTellStreamPosition($resource, int $expectedPosition): void
    {
        $position = $this->getStream($resource)->tell();

        self::assertEquals($expectedPosition, $position);
    }

    /** @return array<string, array{resource, int}> */
    public static function provideResourcesWithStreamPosition(): array
    {
        /** @var resource $startPosition */
        $startPosition = fopen('php://temp', 'r+');

        /** @var resource $endPosition */
        $endPosition = fopen('php://temp', 'r+');
        fwrite($endPosition, 'foo');
        fseek($endPosition, 3);

        return [
            'start position' => [$startPosition, 0],
            'end position' => [$endPosition, 3],
        ];
    }

    public function testThrowsExceptionWhenTellFails(): void
    {
        /** @var resource $resource */
        $resource = fopen('php://stdin', 'r');
        $stream = $this->getStream($resource);

        self::expectException(CannotTellStream::class);
        self::expectExceptionMessage("Could not tell stream");
        $stream->tell();
    }

    public function testThrowsExceptionWhenTellingDetachedStream(): void
    {
        /** @var resource $resource */
        $resource = fopen('php://temp', 'r+');
        $stream = $this->getStream($resource);
        $stream->detach();

        self::expectException(CannotTellStream::class);
        self::expectExceptionMessage("Could not tell stream");
        $stream->tell();
    }

    /** @param resource $resource */
    #[DataProvider('provideResourcesWithEndOfFile')]
    public function testCanTellIfStreamIsAtTheEnd($resource, bool $expectedEndOfFile): void
    {
        $endOfFile = $this->getStream($resource)->eof();

        self::assertEquals($expectedEndOfFile, $endOfFile);
    }

    /** @return array<string, array{resource, bool}> */
    public static function provideResourcesWithEndOfFile(): array
    {
        /** @var resource $startPosition */
        $startPosition = fopen('php://temp', 'r+');

        /** @var resource $endPosition */
        $endPosition = fopen('php://temp', 'r+');
        fwrite($endPosition, 'foo');
        fread($endPosition, 3);

        return [
            'start position' => [$startPosition, false],
            'end position' => [$endPosition, true],
        ];
    }

    /** @param StreamInterface $stream */
    #[DataProvider('provideResourcesWithSeekable')]
    public function testCanTellIfStreamIsSeekable(StreamInterface $stream, bool $expectedSeekable): void
    {
        $seekable = $stream->isSeekable();

        self::assertEquals($expectedSeekable, $seekable);
    }

    /** @return array<string, array{StreamInterface, bool}> */
    public static function provideResourcesWithSeekable(): array
    {
        /** @var resource $seekableResource */
        $seekableResource = fopen('php://temp', 'r+');
        $seekableStream = new Stream($seekableResource);

        /** @var resource $nonSeekableResource */
        $nonSeekableResource = fopen('php://stdin', 'r');
        $nonSeekableStream = new Stream($nonSeekableResource);

        /** @var resource $detachedResource */
        $detachedResource = fopen('php://temp', 'r+');
        $detachedStream = new Stream($detachedResource);
        $detachedStream->detach();

        return [
                'seekable resource' => [$seekableStream, true],
                'non-seekable resource' => [$nonSeekableStream, false],
                'detached stream' => [$detachedStream, false],
        ];
    }

    /** @param StreamInterface $stream */
    #[DataProvider('provideSeekOperations')]
    public function testCanSeek(StreamInterface $stream, int $offset, int $whence, int $expectedPosition): void
    {
        $stream->seek($offset, $whence);
        $position = $stream->tell();

        self::assertEquals($expectedPosition, $position);
    }

    /** @return array<string, array{StreamInterface, int, int, int}> */
    public static function provideSeekOperations(): array
    {
        /** @var resource $resource1 */
        $resource1 = fopen('php://temp', 'r+');
        fwrite($resource1, 'foobar');
        $stream1 = new Stream($resource1);

        /** @var resource $resource2 */
        $resource2 = fopen('php://temp', 'r+');
        fwrite($resource2, 'foobar');
        fseek($resource2, 2);
        $stream2 = new Stream($resource2);

        /** @var resource $resource3 */
        $resource3 = fopen('php://temp', 'r+');
        fwrite($resource3, 'foobar');
        $stream3 = new Stream($resource3);

        return [
            'absolute position' => [$stream1, 3, SEEK_SET, 3],
            'relative to current position' => [$stream2, 2, SEEK_CUR, 4],
            'relative to end' => [$stream3, -2, SEEK_END, 4],
        ];
    }

    /** @param StreamInterface $stream */
    #[DataProvider('provideSeekExceptionCases')]
    public function testThrowsExceptionWhenSeeking(StreamInterface $stream): void
    {
        self::expectException(CannotSeekStream::class);
        self::expectExceptionMessage("Could not seek stream");
        $stream->seek(0);
    }

    /** @return array<string, array{StreamInterface}> */
    public static function provideSeekExceptionCases(): array
    {
        /** @var resource $nonSeekableResource */
        $nonSeekableResource = fopen('php://stdin', 'r');
        $nonSeekableStream = new Stream($nonSeekableResource);

        /** @var resource $detachedResource */
        $detachedResource = fopen('php://temp', 'r+');
        $detachedStream = new Stream($detachedResource);
        $detachedStream->detach();

        return [
            'non-seekable stream' => [$nonSeekableStream],
            'detached stream' => [$detachedStream],
        ];
    }

    /** @param StreamInterface $stream */
    #[DataProvider('provideRewindOperations')]
    public function testCanRewindStream(StreamInterface $stream, int $initialPosition): void
    {
        $stream->rewind();
        $position = $stream->tell();

        self::assertEquals(0, $position);
    }

    /** @return array<string, array{StreamInterface, int}> */
    public static function provideRewindOperations(): array
    {
        /** @var resource $resource1 */
        $resource1 = fopen('php://temp', 'r+');
        fwrite($resource1, 'foobar');
        $stream1 = new Stream($resource1);

        /** @var resource $resource2 */
        $resource2 = fopen('php://temp', 'r+');
        fwrite($resource2, 'foobar');
        fseek($resource2, 3);
        $stream2 = new Stream($resource2);

        return [
            'from start position' => [$stream1, 0],
            'from middle position' => [$stream2, 3],
        ];
    }

    /** @param StreamInterface $stream */
    #[DataProvider('provideRewindExceptionCases')]
    public function testThrowsExceptionWhenRewinding(StreamInterface $stream): void
    {
        self::expectException(CannotSeekStream::class);
        self::expectExceptionMessage("Could not seek stream");
        $stream->rewind();
    }

    /** @return array<string, array{StreamInterface}> */
    public static function provideRewindExceptionCases(): array
    {
        /** @var resource $nonRewindableResource */
        $nonRewindableResource = fopen('php://stdin', 'r');
        $nonRewindableStream = new Stream($nonRewindableResource);

        /** @var resource $detachedResource */
        $detachedResource = fopen('php://temp', 'r+');
        $detachedStream = new Stream($detachedResource);
        $detachedStream->detach();

        return [
            'non-rewindable stream' => [$nonRewindableStream],
            'detached stream' => [$detachedStream],
        ];
    }

    /** @param StreamInterface $stream */
    #[DataProvider('provideResourcesWithWritable')]
    public function testCanTellIfStreamIsWritable(StreamInterface $stream, bool $expectedWritable): void
    {
        $writable = $stream->isWritable();

        self::assertEquals($expectedWritable, $writable);
    }

    /** @return array<string, array{StreamInterface, bool}> */
    public static function provideResourcesWithWritable(): array
    {
        /** @var resource $writableResource */
        $writableResource = fopen('php://temp', 'r+');
        $writableStream = new Stream($writableResource);

        /** @var resource $nonWritableResource */
        $nonWritableResource = fopen('php://stdin', 'r');
        $nonWritableStream = new Stream($nonWritableResource);

        /** @var resource $detachedResource */
        $detachedResource = fopen('php://temp', 'r+');
        $detachedStream = new Stream($detachedResource);
        $detachedStream->detach();

        return [
            'writable resource' => [$writableStream, true],
            'non-writable resource' => [$nonWritableStream, false],
            'detached stream' => [$detachedStream, false],
        ];
    }

    /** @param StreamInterface $stream */
    #[DataProvider('provideWriteOperations')]
    public function testCanWrite(StreamInterface $stream, string $stringToWrite, int $expectedBytesWritten, string $expectedContent): void
    {
        $bytesWritten = $stream->write($stringToWrite);

        self::assertEquals($expectedBytesWritten, $bytesWritten);
        $resource = $stream->detach();
        self::assertNotNull($resource);
        /** @var resource $resource */
        rewind($resource);
        $content = stream_get_contents($resource);
        self::assertEquals($expectedContent, $content);
        fclose($resource);
    }

    /** @return array<string, array{StreamInterface, string, int, string}> */
    public static function provideWriteOperations(): array
    {
        /** @var resource $emptyResource */
        $emptyResource = fopen('php://temp', 'r+');
        $emptyStream = new Stream($emptyResource);

        /** @var resource $existingContentResource */
        $existingContentResource = fopen('php://temp', 'r+');
        fwrite($existingContentResource, 'foo');
        $existingContentStream = new Stream($existingContentResource);

        /** @var resource $middlePositionResource */
        $middlePositionResource = fopen('php://temp', 'r+');
        fwrite($middlePositionResource, 'foobar');
        fseek($middlePositionResource, 3);
        $middlePositionStream = new Stream($middlePositionResource);

        return [
            'write to empty stream' => [$emptyStream, 'hello', 5, 'hello'],
            'write to stream with existing content' => [$existingContentStream, 'bar', 3, 'foobar'],
            'write at middle position' => [$middlePositionStream, 'xyz', 3, 'fooxyz'],
        ];
    }

    /** @param StreamInterface $stream */
    #[DataProvider('provideWriteExceptionCases')]
    public function testThrowsExceptionWhenWriting(StreamInterface $stream): void
    {
        self::expectException(CannotWriteToStream::class);
        self::expectExceptionMessage("Could not write stream");
        $stream->write('test');
    }

    /** @return array<string, array{StreamInterface}> */
    public static function provideWriteExceptionCases(): array
    {
        /** @var resource $nonWritableResource */
        $nonWritableResource = fopen('php://stdin', 'r');
        $nonWritableStream = new Stream($nonWritableResource);

        /** @var resource $detachedResource */
        $detachedResource = fopen('php://temp', 'r+');
        $detachedStream = new Stream($detachedResource);
        $detachedStream->detach();

        return [
            'non-writable stream' => [$nonWritableStream],
            'detached stream' => [$detachedStream],
        ];
    }

    /** @param StreamInterface $stream */
    #[DataProvider('provideMetadataCases')]
    public function testCanGetMetadata(StreamInterface $stream, ?string $key, mixed $expectedValue): void
    {
        $result = $stream->getMetadata($key);

        self::assertEquals($expectedValue, $result);
    }

    /** @return array<string, array{StreamInterface, ?string, mixed}> */
    public static function provideMetadataCases(): array
    {
        /** @var resource $tempResource */
        $tempResource = fopen('php://temp', 'r+');
        $tempStream = new Stream($tempResource);
        $tempMetadata = stream_get_meta_data($tempResource);

        /** @var resource $stdinResource */
        $stdinResource = fopen('php://stdin', 'r');
        $stdinStream = new Stream($stdinResource);
        $stdinMetadata = stream_get_meta_data($stdinResource);

        /** @var resource $detachedResource */
        $detachedResource = fopen('php://temp', 'r+');
        $detachedStream = new Stream($detachedResource);
        $detachedStream->detach();

        return [
            'all metadata from temp' => [$tempStream, null, $tempMetadata],
            'all metadata from stdin' => [$stdinStream, null, $stdinMetadata],
            'mode from temp' => [$tempStream, 'mode', $tempMetadata['mode']],
            'seekable from temp' => [$tempStream, 'seekable', $tempMetadata['seekable']],
            'wrapper_type from temp' => [$tempStream, 'wrapper_type', $tempMetadata['wrapper_type']],
            'mode from stdin' => [$stdinStream, 'mode', $stdinMetadata['mode']],
            'seekable from stdin' => [$stdinStream, 'seekable', $stdinMetadata['seekable']],
            'non-existent key' => [$stdinStream, 'non_existent_key', null],
            'detached stream with null key' => [$detachedStream, null, null],
            'detached stream with specific key' => [$detachedStream, 'mode', null],
        ];
    }
}
