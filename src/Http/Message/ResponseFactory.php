<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Http\Message;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;

final readonly class ResponseFactory implements ResponseFactoryInterface
{
    public function __construct(private StreamFactoryInterface $streamFactory)
    {
    }

    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        return new Response(
            $this->streamFactory->createStream(),
            $code,
            $reasonPhrase,
        );
    }
}
