<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Http\Message;

use BadMethodCallException;
use IngeniozIt\Psr\Http\Message\Exception\InvalidProtocolVersion;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

use function floatval;

readonly class Message implements MessageInterface
{
    public function __construct(
        private string $protocolVersion = '1.1',
    ) {
    }

    public function getProtocolVersion(): string
    {
        return $this->protocolVersion;
    }

    public function withProtocolVersion(string $version): MessageInterface
    {
        if ($version === $this->protocolVersion) {
            return $this;
        }

        if (floatval($version) != $version) {
            throw new InvalidProtocolVersion($version);
        }

        return clone($this, [
            'protocolVersion' => $version,
        ]);
    }

    public function getHeaders(): array
    {
        throw new BadMethodCallException('Not implemented');
    }

    /** @SuppressWarnings("PHPMD.UnusedFormalParameter") */
    public function hasHeader(string $name): bool
    {
        throw new BadMethodCallException('Not implemented');
    }

    /** @SuppressWarnings("PHPMD.UnusedFormalParameter") */
    public function getHeader(string $name): array
    {
        throw new BadMethodCallException('Not implemented');
    }

    /** @SuppressWarnings("PHPMD.UnusedFormalParameter") */
    public function getHeaderLine(string $name): string
    {
        throw new BadMethodCallException('Not implemented');
    }

    /** @SuppressWarnings("PHPMD.UnusedFormalParameter") */
    public function withHeader(string $name, $value): MessageInterface
    {
        throw new BadMethodCallException('Not implemented');
    }

    /** @SuppressWarnings("PHPMD.UnusedFormalParameter") */
    public function withAddedHeader(string $name, $value): MessageInterface
    {
        throw new BadMethodCallException('Not implemented');
    }

    /** @SuppressWarnings("PHPMD.UnusedFormalParameter") */
    public function withoutHeader(string $name): MessageInterface
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function getBody(): StreamInterface
    {
        throw new BadMethodCallException('Not implemented');
    }

    /** @SuppressWarnings("PHPMD.UnusedFormalParameter") */
    public function withBody(StreamInterface $body): MessageInterface
    {
        throw new BadMethodCallException('Not implemented');
    }
}
