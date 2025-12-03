<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Tests\Http\Message;

use IngeniozIt\Psr\Http\Message\Exception\Uri\InvalidScheme;
use IngeniozIt\Psr\Http\Message\Exception\Uri\InvalidUri;
use IngeniozIt\Psr\Http\Message\Uri;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

class UriTest extends TestCase
{
    public function testIsAPsrUri(): void
    {
        $uri = new Uri('https://example.com');

        self::assertInstanceOf(UriInterface::class, $uri);
    }

    public function testNeedsAValidUri(): void
    {
        $badUri = 'https:///path';

        self::expectException(InvalidUri::class);
        self::expectExceptionMessage("Invalid URI '$badUri' provided");

        new Uri($badUri);
    }

    public function testHasAScheme(): void
    {
        $uri = new Uri('https://example.com');

        $scheme = $uri->getScheme();

        self::assertEquals('https', $scheme);
    }

    public function testSchemeIsNormalized(): void
    {
        $uri = new Uri('hTtPs0+-.://example.com');

        $scheme = $uri->getScheme();

        self::assertEquals('https0+-.', $scheme);
    }

    public function testSchemeIsEmptyByDefault(): void
    {
        $uri = new Uri('example.com');

        $scheme = $uri->getScheme();

        self::assertEquals('', $scheme);
    }

    public function testCanChangeScheme(): void
    {
        $uri = new Uri('https://example.com')
            ->withScheme('http');

        $scheme = $uri->getScheme();

        self::assertEquals('http', $scheme);
    }

    public function testReturnsSameInstanceIfSchemeDoesNotChange(): void
    {
        $uri = new Uri('https://example.com');

        $uri2 = $uri->withScheme('https');

        self::assertSame($uri, $uri2);
    }

    #[DataProvider('provideInvalidSchemes')]
    public function testSchemeMustBeValid(string $scheme, string $message): void
    {
        self::expectException(InvalidScheme::class);
        self::expectExceptionMessage($message);
        new Uri('https://example.com')->withScheme($scheme);
    }

    /** @return array<string, array{string, string}> */
    public static function provideInvalidSchemes(): array
    {
        return [
            'invalid first character' => ['-ttps', "Invalid scheme '-ttps': first character must be a letter"],
            'invalid characters' => ['h%tps', "Invalid scheme 'h%tps': scheme contains invalid characters"],
        ];
    }
}
