<?php

declare(strict_types=1);

namespace Http\Message;

use IngeniozIt\Psr\Http\Message\Exception\Message\InvalidHeaderName;
use IngeniozIt\Psr\Http\Message\Exception\Message\InvalidHeaderValue;
use IngeniozIt\Psr\Http\Message\Exception\Message\InvalidProtocolVersion;
use IngeniozIt\Psr\Http\Message\Message;
use IngeniozIt\Psr\Http\Message\Response;
use IngeniozIt\Psr\Http\Message\Stream;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

class ResponseTest extends TestCase
{
    protected const string DEFAULT_HTTP_PROTOCOL = '1.1';

    protected function message(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        /** @var resource $resource */
        $resource = fopen('php://temp', 'r+');
        return new Response(new Stream($resource), $code, $reasonPhrase);
    }

    public function testIsAPsrResponse(): void
    {
        $message = $this->message(200);

        $body = $message->getBody();

        self::assertInstanceOf(ResponseInterface::class, $message);
        self::assertInstanceOf(StreamInterface::class, $body);
    }

    public function testHasAStatus(): void
    {
        $message = $this->message(416, 'reason');

        $statusCode = $message->getStatusCode();
        $reasonPhrase = $message->getReasonPhrase();

        self::assertEquals(416, $statusCode);
        self::assertEquals('reason', $reasonPhrase);
    }

    public function testCanUseDefaultReasonPhrase(): void
    {
        $message = $this->message(400);

        $reasonPhrase = $message->getReasonPhrase();

        self::assertEquals('Bad Request', $reasonPhrase);
    }

    public function testUsesEmptyReasonPhraseIfDefaultIsNotKnown(): void
    {
        $message = $this->message(111);

        $reasonPhrase = $message->getReasonPhrase();

        self::assertEquals('', $reasonPhrase);
    }

    public function testCanChageCodeAndReasonPhrase(): void
    {
        $message = $this->message(200, 'code')
            ->withStatus(404, 'another code');

        $code = $message->getStatusCode();
        $reasonPhrase = $message->getReasonPhrase();

        self::assertEquals(404, $code);
        self::assertEquals('another code', $reasonPhrase);
    }

    public function testCanChangeCodeUsingDefaultReasonPhrase(): void
    {
        $message = $this->message(200, 'code')
            ->withStatus(404);

        $code = $message->getStatusCode();
        $reasonPhrase = $message->getReasonPhrase();

        self::assertEquals(404, $code);
        self::assertEquals('Not Found', $reasonPhrase);
    }

    public function testReturnsTheSameInstanceIfCodeAndReasonPhraseDoesNotChange(): void
    {
        $message = $this->message(200, 'code');

        $message2 = $message->withStatus(200, 'code');

        self::assertSame($message, $message2);
    }

    public function testDoesNotReturnTheSameInstanceIfOnlyCodeDoesNotChange(): void
    {
        $message = $this->message(200, 'code');

        $message2 = $message->withStatus(200, 'another code');

        self::assertNotSame($message, $message2);
    }

    public function testDoesNotReturnTheSameInstanceIfOnlyReasonPhraseDoesNotChange(): void
    {
        $message = $this->message(200, 'code');

        $message2 = $message->withStatus(400, 'code');

        self::assertNotSame($message, $message2);
    }
}
