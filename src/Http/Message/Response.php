<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Http\Message;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;

readonly class Response extends Message implements ResponseInterface
{
    private string $reasonPhrase;

    public function __construct(
        StreamInterface $body,
        private int $statusCode,
        string $reasonPhrase = '',
    ) {
        parent::__construct($body);
        $this->reasonPhrase = HttpStatusCode::normalizeReasonPhrase($this->statusCode, $reasonPhrase);
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function withStatus(int $code, string $reasonPhrase = ''): ResponseInterface
    {
        $normalizedPhrase = HttpStatusCode::normalizeReasonPhrase($code, $reasonPhrase);

        if ($this->statusCode === $code && $this->reasonPhrase === $normalizedPhrase) {
            return $this;
        }

        return clone($this, [
            'statusCode' => $code,
            'reasonPhrase' => $normalizedPhrase,
        ]);
    }

    public function getReasonPhrase(): string
    {
        return $this->reasonPhrase;
    }
}
