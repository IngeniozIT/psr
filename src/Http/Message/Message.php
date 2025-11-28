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
use function is_string;
use function str_replace;
use function strpbrk;
use function strtolower;
use function trim;

readonly class Message implements MessageInterface
{
    private const string INVALID_HEADER_NAME_CHARS = "\x00\x01\x02\x03\x04" .
        "\x05\x06\x07\x08\x09\x0a\x0b\x0c\x0d\x0e\x0f\x10\x11\x12\x13\x14" .
        "\x15\x16\x17\x18\x19\x1a\x1b\x1c\x1d\x1e\x1f\x20";
    private const string INVALID_HEADER_VALUE_CHARS = "\x00\r\n";
    private const array COMPATIBILITY_SEQUENCES = ["\r\n ", "\r\n\t"];

    private string $protocolVersion;

    /** @var array<string, string[]> */
    private array $headers;

    /** @var array<string, string> */
    private array $headersName;

    public function __construct(private StreamInterface $body)
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
        $this->assertHeaderNameIsValid($name);
        $headerValue = array_map(
            $this->sanitizeHeaderValue(...),
            is_array($value) ? $value : [$value],
        );

        if (
            array_key_exists($name, $this->headers) &&
            $headerValue == $this->headers[$name]
        ) {
            return $this;
        }

        $headers = $this->headers;
        $headers[$name] = $headerValue;
        $headersName = $this->headersName;
        $headersName[strtolower($name)] = $name;

        return clone($this, [
            'headers' => $headers,
            'headersName' => $headersName,
        ]);
    }

    private function sanitizeHeaderValue(mixed $value): string
    {
        if (!is_string($value)) {
            throw new InvalidHeaderValue($value);
        }

        $value = str_replace(
            self::COMPATIBILITY_SEQUENCES,
            ' ',
            $value,
        );

        $this->assertHeaderValueIsValid($value);

        return trim($value);
    }

    private function assertHeaderNameIsValid(string $name): void
    {
        if (
            $name === '' ||
            strpbrk($name, self::INVALID_HEADER_NAME_CHARS) !== false
        ) {
            throw new InvalidHeaderName($name);
        }
    }

    private function assertHeaderValueIsValid(string $value): void
    {
        if (
            strpbrk($value, self::INVALID_HEADER_VALUE_CHARS) !== false
        ) {
            throw new InvalidHeaderValue($value);
        }
    }

    /** @param string|string[] $value */
    public function withAddedHeader(string $name, mixed $value): MessageInterface
    {
        $newValue = array_merge(
            $this->getHeader($name),
            is_array($value) ? $value : [$value],
        );
        return $this->withHeader($name, $newValue);
    }

    public function withoutHeader(string $name): MessageInterface
    {
        if (!$this->hasHeader($name)) {
            return $this;
        }

        $headers = $this->headers;
        $headersName = $this->headersName;

        $headerName = strtolower($name);
        /** @phpstan-ignore unset.offset, offsetAccess.notFound, offsetAccess.invalidOffset */
        unset($headers[$headersName[$headerName]]);
        /** @phpstan-ignore unset.offset */
        unset($headersName[$headerName]);

        return clone($this, [
            'headers' => $headers,
            'headersName' => $headersName,
        ]);
    }

    public function getBody(): StreamInterface
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body): MessageInterface
    {
        if ($body === $this->body) {
            return $this;
        }

        return clone ($this, [
            'body' => $body,
        ]);
    }
}
