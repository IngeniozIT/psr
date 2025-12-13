<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Http\Message;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

class StreamFactory implements StreamFactoryInterface
{
    public function createStream(string $content = ''): StreamInterface
    {
        $stream = $this->createStreamFromFile('php://temp', 'r+');
        $stream->write($content);
        return $stream;
    }

    public function createStreamFromFile(string $filename, string $mode = 'r'): StreamInterface
    {
        /** @var resource $resource */
        $resource = fopen($filename, $mode);

        return $this->createStreamFromResource($resource);
    }

    /** @param resource $resource */
    public function createStreamFromResource($resource): StreamInterface
    {
        return new Stream($resource);
    }
}
