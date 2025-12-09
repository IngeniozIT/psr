<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Tests\Http\Message;

use IngeniozIt\Psr\Http\Message\Exception\Uri\InvalidPort;
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

    #[DataProvider('provideInvalidUri')]
    public function testNeedsAValidUri(string $uri): void
    {
        self::expectException(InvalidUri::class);
        self::expectExceptionMessage("Invalid URI '$uri' provided");

        new Uri($uri);
    }

    public static function provideInvalidUri(): array
    {
        return [
            'empty uri' => [''],
            'no scheme, host, nor path' => ['?foo#bar'],
            'authority without host' => ['https://user:pass@:80'],
            'non-closed ipv6' => ['https://[ipv6/path'],
        ];
    }

    public function testHasAScheme(): void
    {
        $uri = new Uri('https://example.com');

        $scheme = $uri->getScheme();

        self::assertEquals('https', $scheme);
    }

    public function testSchemeIsEmptyByDefault(): void
    {
        $uri = new Uri('example.com');

        $scheme = $uri->getScheme();

        self::assertEquals('', $scheme);
    }

    public function testSchemeIsNormalized(): void
    {
        $uri = new Uri('hTtPs0+-.://example.com');

        $scheme = $uri->getScheme();

        self::assertEquals('https0+-.', $scheme);
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

    public function testHasUserInfo(): void
    {
        $uri = new Uri('https://user@example.com');

        $userInfo = $uri->getUserInfo();

        self::assertEquals('user', $userInfo);
    }

    public function testUserInfoIsEmptyByDefault(): void
    {
        $uri = new Uri('https://example.com');

        $userInfo = $uri->getUserInfo();

        self::assertEquals('', $userInfo);
    }

    public function testCanHaveAPassword(): void
    {
        $uri = new Uri('https://user:password@example.com');

        $userInfo = $uri->getUserInfo();

        self::assertEquals('user:password', $userInfo);
    }

    public function testEncodesUserInfo(): void
    {
        $uri = new Uri('https://user+:password+@example.com');

        $userInfo = $uri->getUserInfo();

        self::assertEquals('user%2B:password%2B', $userInfo);
    }

    public function testDoesNotEncodeUserInfoTwice(): void
    {
        $uri = new Uri('https://user%25:password%25@example.com');

        $userInfo = $uri->getUserInfo();

        self::assertEquals('user%25:password%25', $userInfo);
    }

    #[DataProvider('provideUserInfo')]
    public function testCanChangeUserInfo(string $newUser, ?string $newPassword, string $expectedUserInfo): void
    {
        $uri = new Uri('https://user:pass@example.com')
            ->withUserInfo($newUser, $newPassword);

        $userInfo = $uri->getUserInfo();

        self::assertEquals($expectedUserInfo, $userInfo);
    }

    /** @return array<string, array{string, ?string, string}> */
    public static function provideUserInfo(): array
    {
        return [
            'without password' => ['newUser', null, 'newUser'],
            'with password' => ['newUser', 'newPass', 'newUser:newPass'],
            'with the same user' => ['user', null, 'user'],
            'with special characters' => ['newUser+', 'newPass+', 'newUser%2B:newPass%2B'],
            'with special characters encoded once' => ['newUser%2B', 'newPass%2B', 'newUser%2B:newPass%2B'],
        ];
    }

    public function testReturnsSameInstanceIfUserInfoDoesNotChange(): void
    {
        $uri = new Uri('https://user:pass@example.com');

        $uri2 = $uri->withUserInfo('user', 'pass');

        self::assertSame($uri, $uri2);
    }

    #[DataProvider('provideHostNames')]
    public function testHasAHost(string $givenHost, string $expectedHost): void
    {
        $uri = new Uri($givenHost);

        $host = $uri->getHost();

        self::assertEquals($expectedHost, $host);
    }

    public static function provideHostNames(): array
    {
        return [
            'domain name' => ['//example.com', 'example.com'],
        ];
    }

    public function testHostIsEmptyByDefault(): void
    {
        $uri = new Uri('/');

        $host = $uri->getHost();

        self::assertEquals('', $host);
    }

    public function testHasAPort(): void
    {
        $uri = new Uri('https://example.com:1');

        $port = $uri->getPort();

        self::assertEquals(1, $port);
    }

    public function testPortIsNullByDefault(): void
    {
        $uri = new Uri('https://example.com');

        $port = $uri->getPort();

        self::assertNull($port);
    }

    public function testCanRemovePort(): void
    {
        $uri = new Uri('https://example.com:8080')
            ->withPort(null);

        $port = $uri->getPort();

        self::assertNull($port);
    }

    public function testCanChangePort(): void
    {
        $uri = new Uri('https://example.com:8080')
            ->withPort(65535);

        $port = $uri->getPort();

        self::assertEquals(65535, $port);
    }

    #[DataProvider('provideInvalidPorts')]
    public function testPortMustBeValid(int $port): void
    {
        self::expectException(InvalidPort::class);
        self::expectExceptionMessage("Invalid port $port");
        new Uri('https://example.com')
            ->withPort($port);
    }

    /** @return array<string, array{int}> */
    public static function provideInvalidPorts(): array
    {
        return [
            '>= 1' => [0],
            '<= 65535' => [65536],
        ];
    }

    #[DataProvider('provideStandardPorts')]
    public function testReturnsNoPortIfItIsTheStandardForThisScheme(string $scheme, int $port): void
    {
        $uri = new Uri("$scheme://example.com:$port");

        $port = $uri->getPort();

        self::assertNull($port);
    }

    /** @return array<string, array{string, int}> */
    public static function provideStandardPorts(): array
    {
        return [
            'http' => ['http', 80],
            'https' => ['https', 443],
            'ftp' => ['ftp', 21],
            'sftp' => ['sftp', 22],
            'ssh' => ['ssh', 22],
            'ws' => ['ws', 80],
            'wss' => ['wss', 443],
        ];
    }

    public function testReturnsSameInstanceIfPortDoesNotChange(): void
    {
        $uri = new Uri('https://example.com:8080');

        $uri2 = $uri->withPort(8080);

        self::assertSame($uri, $uri2);
    }
}
