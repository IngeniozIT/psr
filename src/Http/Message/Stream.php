<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Http\Message;

use BadMethodCallException;
use IngeniozIt\Psr\Http\Message\Exception\CannotSeekStream;
use IngeniozIt\Psr\Http\Message\Exception\CannotTellStream;
use IngeniozIt\Psr\Http\Message\Exception\InvalidResource;
use Psr\Http\Message\StreamInterface;

use function feof;
use function fclose;
use function fseek;
use function fstat;
use function ftell;
use function is_array;
use function is_resource;
use function stream_get_meta_data;

class Stream implements StreamInterface
{
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
        if (!is_resource($this->resource)) {
            return true;
        }

        return feof($this->resource);
    }

    public function isSeekable(): bool
    {
        if (!is_resource($this->resource)) {
            return false;
        }

        $metadata = stream_get_meta_data($this->resource);
        return $metadata['seekable'];
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
        throw new BadMethodCallException('Not implemented');
    }

    /** @SuppressWarnings("PHPMD.UnusedFormalParameter") */
    public function write(string $string): int
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function isReadable(): bool
    {
        throw new BadMethodCallException('Not implemented');
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

    /** @SuppressWarnings("PHPMD.UnusedFormalParameter") */
    public function getMetadata(?string $key = null)
    {
        throw new BadMethodCallException('Not implemented');
    }
}
