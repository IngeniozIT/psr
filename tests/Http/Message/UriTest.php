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
}
