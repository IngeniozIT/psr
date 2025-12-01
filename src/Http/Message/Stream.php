<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Http\Message;

use BadMethodCallException;
use IngeniozIt\Psr\Http\Message\Exception\CannotSeekStream;
use IngeniozIt\Psr\Http\Message\Exception\CannotTellStream;
use IngeniozIt\Psr\Http\Message\Exception\CannotWriteToStream;
use IngeniozIt\Psr\Http\Message\Exception\InvalidResource;
use Psr\Http\Message\StreamInterface;

use function feof;
use function fclose;
use function fseek;
use function fstat;
use function ftell;
use function fwrite;
use function is_array;
use function is_resource;
use function stream_get_meta_data;
use function str_contains;

class Stream implements StreamInterface
{
    private const array WRITABLE_MODES = ['w', 'a', 'x', 'c'];

    /** @param ?resource $resource */
    public function __construct(private mixed $resource)
    {
        if (!is_resource($this->resource)) {
            throw new InvalidResource();
        }
    }

    public function __toString(): string
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function close(): void
    {
        if (is_resource($this->resource)) {
            fclose($this->resource);
        }
    }

    public function detach()
    {
        $resource = $this->resource;
        $this->resource = null;

        return $resource;
    }

    public function getSize(): ?int
    {
        if (!is_resource($this->resource)) {
            return null;
        }

        $stats = fstat($this->resource);
        return is_array($stats) ?
            $stats['size'] :
            null;
    }

    public function tell(): int
    {
        if (!is_resource($this->resource)) {
            throw new CannotTellStream();
        }

        $position = ftell($this->resource);

        if ($position === false) {
            throw new CannotTellStream();
        }

        return $position;
    }

    public function eof(): bool
    {
        return !is_resource($this->resource) || feof($this->resource);
    }

    public function isSeekable(): bool
    {
        /** @phpstan-ignore return.type */
        return is_resource($this->resource) ?
            $this->getMetadata('seekable') :
            false;
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        if (!$this->isSeekable()) {
            throw new CannotSeekStream();
        }

        /** @phpstan-ignore argument.type */
        fseek($this->resource, $offset, $whence);
    }

    public function rewind(): void
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        $mode = $this->getMetadata('mode');

        /** @phpstan-ignore offsetAccess.nonOffsetAccessible */
        return $mode === 'r+' || in_array($mode[0] ?? '', self::WRITABLE_MODES);
    }

    public function write(string $string): int
    {
        if (!$this->isWritable()) {
            throw new CannotWriteToStream();
        }

        /** @phpstan-ignore argument.type, return.type */
        return fwrite($this->resource, $string);
    }

    public function isReadable(): bool
    {
        $mode = $this->getMetadata('mode');

        if (!is_string($mode)) {
            return false;
        }

        return ($mode[0] ?? '') === 'r' || str_contains($mode, '+');
    }

    /** @SuppressWarnings("PHPMD.UnusedFormalParameter") */
    public function read(int $length): string
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function getContents(): string
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function getMetadata(?string $key = null)
    {
        if (!is_resource($this->resource)) {
            return null;
        }

        $metadata = stream_get_meta_data($this->resource);

        return $key === null ? $metadata : $metadata[$key] ?? null;
    }
}
