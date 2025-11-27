<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Tests\Http\Message;

use IngeniozIt\Psr\Http\Message\Exception\InvalidProtocolVersion;
use IngeniozIt\Psr\Http\Message\Message;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;

class MessageTest extends TestCase
{
    protected const DEFAULT_HTTP_PROTOCOL = '1.1';

    protected function message(): MessageInterface
    {
        return new Message();
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
}
