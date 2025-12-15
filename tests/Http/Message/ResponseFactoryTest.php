<?php

declare(strict_types=1);

namespace Http\Message;

use IngeniozIt\Psr\Http\Message\StreamFactory;
use IngeniozIt\Psr\Http\Message\ResponseFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

class ResponseFactoryTest extends TestCase
{
    public function testIsAPsrResponseFactory(): void
    {
        $responseFactory = new ResponseFactory(new StreamFactory());

        self::assertInstanceOf(ResponseFactoryInterface::class, $responseFactory);
    }

    public function testCreatesAResponse(): void
    {
        $responseFactory = new ResponseFactory(new StreamFactory());

        $response = $responseFactory->createResponse(404, 'reason phrase');
        $statusCode = $response->getStatusCode();
        $reasonPhrase = $response->getReasonPhrase();

        self::assertInstanceOf(ResponseInterface::class, $response);
        self::assertEquals(404, $statusCode);
        self::assertEquals('reason phrase', $reasonPhrase);
    }

    public function testCanCreateAResponseWithDefaultValues(): void
    {
        $responseFactory = new ResponseFactory(new StreamFactory());

        $response = $responseFactory->createResponse();
        $statusCode = $response->getStatusCode();
        $reasonPhrase = $response->getReasonPhrase();

        self::assertEquals(200, $statusCode);
        self::assertEquals('OK', $reasonPhrase);
    }
}
