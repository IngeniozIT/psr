<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Http\Message;

use BadMethodCallException;
use IngeniozIt\Psr\Http\Message\Exception\InvalidHeaderName;
use IngeniozIt\Psr\Http\Message\Exception\InvalidHeaderValue;
use IngeniozIt\Psr\Http\Message\Exception\InvalidProtocolVersion;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

use function array_key_exists;
use function floatval;
use function implode;
use function is_array;
use function str_replace;
use function strpbrk;
use function strtolower;
use function trim;

readonly class Message implements MessageInterface
{
    private const string INVALID_HEADER_NAME_CHARS = "\x00\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0a\x0b\x0c\x0d\x0e\x0f\x10\x11\x12\x13\x14\x15\x16\x17\x18\x19\x1a\x1b\x1c\x1d\x1e\x1f\x20";
    private const string INVALID_HEADER_VALUE_CHARS = "\x00\r\n";
    private const array COMPATIBILITY_SEQUENCES = ["\r\n ", "\r\n\t"];

    private string $protocolVersion;

    /** @var array<string, string[]> */
    private array $headers;

    /** @var array<string, string> */
    private array $headersName;

    public function __construct()
    {
        $this->protocolVersion = '1.1';
        $this->headers = [];
        $this->headersName = [];
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
        return $this->headers;
    }

    public function hasHeader(string $name): bool
    {
        return array_key_exists(strtolower($name), $this->headersName);
    }

    /** @return string[] */
    public function getHeader(string $name): array
    {
        /** @phpstan-ignore return.type */
        return $this->hasHeader($name) ?
            /** @phpstan-ignore offsetAccess.notFound, offsetAccess.notFound, offsetAccess.invalidOffset */
            $this->headers[$this->headersName[strtolower($name)]] :
            [];
    }

    public function getHeaderLine(string $name): string
    {
        return implode(',', $this->getHeader($name));
    }

    /** @param string|string[] $value */
    public function withHeader(string $name, mixed $value): MessageInterface
    {
        $this->assertNameIsValid($name);
        $headers = $this->headers;
        $headers[$name] = $this->sanitizeHeaderValue($value);
        $headersName = $this->headersName;
        $headersName[strtolower($name)] = $name;

        return clone($this, [
            'headers' => $headers,
            'headersName' => $headersName,
        ]);
    }

    private function assertNameIsValid(string $name): void
    {
        if ($name === '' || strpbrk($name, self::INVALID_HEADER_NAME_CHARS) !== false) {
            throw new InvalidHeaderName($name);
        }
    }

    /**
     * @param string|string[] $value
     * @return string[]
     */
    private function sanitizeHeaderValue(mixed $value): array
    {
        $sanitizedValue = [];

        $cleanValue = !is_array($value) ? [$value] : $value;
        foreach ($cleanValue as $valueItem) {
            /** @phpstan-ignore function.alreadyNarrowedType */
            if (!\is_string($valueItem)) {
                throw new InvalidHeaderValue($value);
            }

            $valueItem = str_replace(self::COMPATIBILITY_SEQUENCES, ' ', $valueItem);

            if (strpbrk($valueItem, self::INVALID_HEADER_VALUE_CHARS) !== false) {
                throw new InvalidHeaderValue($valueItem);
            }

            $sanitizedValue[] = trim($valueItem);
        }

        return $sanitizedValue;
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
