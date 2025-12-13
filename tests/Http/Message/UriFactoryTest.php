<?php

declare(strict_types=1);

namespace Http\Message;

use IngeniozIt\Psr\Http\Message\UriFactory;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriFactoryInterface;

class UriFactoryTest extends TestCase
{
    public function testIsAPsrUriFactory(): void
    {
        $uriFactory = new UriFactory();

        self::assertInstanceOf(UriFactoryInterface::class, $uriFactory);
    }

    public function testCreatesAUri(): void
    {
        $uriFactory = new UriFactory();

        $uri = $uriFactory->createUri('https://user:pass@example.com/path?query=value#fragment');

        self::assertEquals('https://user:pass@example.com/path?query=value#fragment', (string) $uri);
    }

    public function testUriCanBeEmpty(): void
    {
        $uriFactory = new UriFactory();

        $uri = $uriFactory->createUri();

        self::assertEquals('', (string) $uri);
    }
}
