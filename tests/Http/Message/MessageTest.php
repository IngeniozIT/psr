<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Tests\Http\Message;

use IngeniozIt\Psr\Http\Message\Exception\InvalidHeaderName;
use IngeniozIt\Psr\Http\Message\Exception\InvalidHeaderValue;
use IngeniozIt\Psr\Http\Message\Exception\InvalidProtocolVersion;
use IngeniozIt\Psr\Http\Message\Message;
use IngeniozIt\Psr\Http\Message\Stream;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;

class MessageTest extends TestCase
{
    protected const string DEFAULT_HTTP_PROTOCOL = '1.1';

    protected function message(): MessageInterface
    {
        /** @var resource $resource */
        $resource = fopen('php://temp', 'r+');
        return new Message(new Stream($resource));
    }

    public function testIsAPsrMessage(): void
    {
        $message = $this->message();

        self::assertInstanceOf(MessageInterface::class, $message);
    }

    public function testHasADefaultProtocol(): void
    {
        $message = $this->message();

        self::assertEquals(self::DEFAULT_HTTP_PROTOCOL, $message->getProtocolVersion());
    }

    #[DataProvider('provideProtocolVersions')]
    public function testSupportsHttpProtocol(string $protocolVersion): void
    {
        $message = $this->message()->withProtocolVersion($protocolVersion);

        self::assertEquals($protocolVersion, $message->getProtocolVersion());
    }

    /** @return array<string, string[]> */
    public static function provideProtocolVersions(): array
    {
        return [
            'HTTP/1.0' => ['1.0'],
            'HTTP/1.1' => ['1.1'],
            'HTTP/2' => ['2'],
            'HTTP/3' => ['3'],
        ];
    }

    public function testCannotUseAnInvalidProtocolVersion(): void
    {
        $message = $this->message();

        self::expectException(InvalidProtocolVersion::class);
        self::expectExceptionMessage("Invalid protocol version: HTTP/1.1");
        $message->withProtocolVersion('HTTP/1.1');
    }

    public function testReturnsTheSameInstanceWhenProtocolDoesNotChange(): void
    {
        $message = $this->message()->withProtocolVersion('3');

        $message2 = $message->withProtocolVersion('3');

        self::assertSame($message, $message2);
    }

    public function testHasHeaders(): void
    {
        $message = $this->message()->withHeader('Content-Type', 'text/html');

        $hasHeader = $message->hasHeader('Content-Type');
        $header = $message->getHeader('Content-Type');
        $headerLine = $message->getHeaderLine('Content-Type');
        $headers = $message->getHeaders();

        self::assertTrue($hasHeader);
        self::assertEquals(['text/html'], $header);
        self::assertEquals('text/html', $headerLine);
        self::assertEquals(['Content-Type' => ['text/html']], $headers);
    }

    public function testHeadersCanHaveMultipleValues(): void
    {
        $message = $this->message()->withHeader('Accept', ['text/html', 'application/xhtml+xml']);

        $hasHeader = $message->hasHeader('Accept');
        $header = $message->getHeader('Accept');
        $headerLine = $message->getHeaderLine('Accept');
        $headers = $message->getHeaders();

        self::assertTrue($hasHeader);
        self::assertEquals(['text/html', 'application/xhtml+xml'], $header);
        self::assertEquals('text/html,application/xhtml+xml', $headerLine);
        self::assertEquals(['Accept' => ['text/html', 'application/xhtml+xml']], $headers);
    }

    public function testHeadersAreCaseInsensitive(): void
    {
        $message = $this->message()->withHeader('CONTENT-TYPE', 'text/html');

        $hasHeader = $message->hasHeader('Content-Type');
        $header = $message->getHeader('Content-Type');
        $headerLine = $message->getHeaderLine('Content-Type');
        $headers = $message->getHeaders();

        self::assertTrue($hasHeader);
        self::assertEquals(['text/html'], $header);
        self::assertEquals('text/html', $headerLine);
        self::assertEquals(['CONTENT-TYPE' => ['text/html']], $headers);
    }

    public function testHeadersCanBeEmpty(): void
    {
        $message = $this->message();

        $hasHeader = $message->hasHeader('Content-Type');
        $header = $message->getHeader('Content-Type');
        $headerLine = $message->getHeaderLine('Content-Type');
        $headers = $message->getHeaders();

        self::assertFalse($hasHeader);
        self::assertEquals([], $header);
        self::assertEquals('', $headerLine);
        self::assertEquals([], $headers);
    }

    public function testHeadersCanBeReplaced(): void
    {
        $message = $this->message()
            ->withHeader('Content-Type', 'text/html')
            ->withHeader('Content-Type', 'text/json');

        $hasHeader = $message->hasHeader('Content-Type');
        $header = $message->getHeader('Content-Type');
        $headerLine = $message->getHeaderLine('Content-Type');
        $headers = $message->getHeaders();

        self::assertTrue($hasHeader);
        self::assertEquals(['text/json'], $header);
        self::assertEquals('text/json', $headerLine);
        self::assertEquals(['Content-Type' => ['text/json']], $headers);
    }

    #[DataProvider('provideInvalidHeaderNames')]
    public function testHeaderNameCannotBeInvalid(string $headerName): void
    {
        self::expectException(InvalidHeaderName::class);
        self::expectExceptionMessage("Invalid header name: " . $headerName);
        $this->message()->withHeader($headerName, 'value');
    }

    /** @return array<string, array<string>> */
    public static function provideInvalidHeaderNames(): array
    {
        return [
            'empty name' => [''],
            'invalid character' => ["test\x20name"],
        ];
    }

    #[DataProvider('provideInvalidHeaderValues')]
    public function testHeaderValueCannotBeInvalid(mixed $value, mixed $errorValue): void
    {
        $message = $this->message();

        self::expectException(InvalidHeaderValue::class);
        self::expectExceptionMessage("Invalid header value: " . serialize($errorValue));
        /** @phpstan-ignore argument.type */
        $message->withHeader('Content-Type', $value);
    }

    /** @return array<string, array<mixed|array>> */
    public static function provideInvalidHeaderValues(): array
    {
        return [
            'not a string nor an array' => [42, 42],
            'array that does not contain a string' => [[42], 42],
            'value that contains a NUL' => ["test\x00carriage", "test\x00carriage"],
            'value that contains a carriage return' => ["test\rcarriage", "test\rcarriage"],
            'value that contains a line feed' => ["test\ncarriage", "test\ncarriage"],
        ];
    }

    #[DataProvider('provideNormalizableHeaderValues')]
    public function testNormalizesHeaderValues(string $value, string $expectedResult): void
    {
        $message = $this->message()->withHeader('Content-Type', $value);

        $headerLine = $message->getHeaderLine('Content-Type');

        self::assertEquals($expectedResult, $headerLine);
    }

    /** @return array<string, string[]> */
    public static function provideNormalizableHeaderValues(): array
    {
        return [
            'compatibility sequences are replaced by a space' => ["test\r\n content\r\n\ttype", 'test content type'],
            'values are trimmed' => ['   value   ', 'value'],
        ];
    }

    public function testReturnsTheSameInstanceWhenHeaderDoesNotChange(): void
    {
        $message = $this->message()->withHeader('Content-Type', 'text/html');

        $message2 = $message->withHeader('Content-Type', 'text/html');

        self::assertSame($message, $message2);
    }

    public function testCanAppendAValueToAHeader(): void
    {
        $message = $this->message()
            ->withHeader('Content-Type', 'text/html')
            ->withAddedHeader('Content-Type', 'text/json');

        $header = $message->getHeader('Content-Type');

        self::assertEquals(['text/html', 'text/json'], $header);
    }

    public function testCanRemoveAHeader(): void
    {
        $message = $this->message()
            ->withHeader('Content-Type', 'text/html')
            ->withoutHeader('Content-Type');

        $headers = $message->getHeaders();

        self::assertEquals([], $headers);
    }

    public function testReturnsTheSameInstanceWhenHeaderDoesNotExist(): void
    {
        $message = $this->message();

        $message2 = $message->withoutHeader('Content-Type');

        self::assertSame($message, $message2);
    }

    public function testUsesABody(): void
    {
        /** @var resource $resource */
        $resource = fopen('php://temp', 'r+');
        $body = new Stream($resource);
        $message = $this->message()->withBody($body);

        $body2 = $message->getBody();

        self::assertSame($body, $body2);
    }

    public function testReturnsTheSameInstanceWhenBodyDoesNotChange(): void
    {
        /** @var resource $resource */
        $resource = fopen('php://temp', 'r+');
        $body = new Stream($resource);

        $message = $this->message()->withBody($body);
        $message2 = $message->withBody($body);

        self::assertSame($message, $message2);
    }
}
