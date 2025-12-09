<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Http\Message;

use BadMethodCallException;
use IngeniozIt\Psr\Http\Message\Exception\Uri\InvalidPort;
use IngeniozIt\Psr\Http\Message\Exception\Uri\InvalidScheme;
use IngeniozIt\Psr\Http\Message\Exception\Uri\InvalidUri;
use Psr\Http\Message\UriInterface;

use function ctype_alpha;
use function preg_match;
use function strtolower;

readonly class Uri implements UriInterface
{
    private string $scheme;
    private string $user;
    private ?string $password;
    private ?int $port;
    private bool $isStandardPort;
    private string $host;

    public function __construct(string $uri)
    {
        $parsedUri = $this->parseUri($uri);

        $this->scheme = $this->normalizeScheme($parsedUri['scheme']);
        $this->user = $this->normalizeUri($parsedUri['user']);
        $this->password = $parsedUri['pass'] !== '' ? $this->normalizeUri($parsedUri['pass']) : null;
        $this->host = $this->normalizeHost($parsedUri['host']);
        $this->port = $parsedUri['port'] !== '' ? $this->normalizePort((int) $parsedUri['port']) : null;
        $this->isStandardPort = $this->isStandardPort($this->scheme, $this->port);
    }

    private function parseUri(string $url): array
    {
        $pattern = '~^
            (?:(?<scheme>[^:/?#]+):)?
            (?:(?<authority>
                //
                (?:(?<user>[^:@/?#]*)(?::(?<pass>[^@/?#]*))?@)?
                (?<host>\[[^\]/?#]*\]|[^:/?#]*)
                (?::(?<port>\d*))?
            ))?
            (?<path>[^?#]*)
            (?:\?(?<query>[^#]*))?
            (?:\#(?<fragment>.*))?
        $~x';

        preg_match($pattern, $url, $m);

        $uriParts = [
            'scheme'   => $m['scheme'] ?? '',
            'host'     => $m['host'] ?? '',
            'port'     => $m['port'] ?? '',
            'user'     => $m['user'] ?? '',
            'pass'     => $m['pass'] ?? '',
            'path'     => $m['path'] ?? '',
        ];

        if (
            $this->uriHasMissingParts($uriParts) ||
            $this->uriHasAuthorityWithoutHost($m['authority'], $uriParts) ||
            $this->uriHasInvalidIpv6Host($uriParts['host'])
        ) {
            throw new InvalidUri($url);
        }

        return $uriParts;
    }

    /** @param array<string, string> $uriParts */
    private function uriHasMissingParts(array $uriParts): bool
    {
        return $uriParts['scheme'] === '' && $uriParts['host'] === '' && $uriParts['path'] === '';
    }

    /** @param array<string, string> $uriParts */
    private function uriHasAuthorityWithoutHost(string $authority, array $uriParts): bool
    {
        return $authority !== '' && $uriParts['host'] === '' && $uriParts['scheme'] !== 'file';
    }

    private function uriHasInvalidIpv6Host(string $host): bool
    {
        return str_starts_with($host, '[') && !str_ends_with($host, ']');
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getAuthority(): string
    {
        $userInfo = $this->getUserInfo();
        $port = $this->getPort();

        return
            ($userInfo !== '' ? $userInfo . '@' : '') .
            $this->host .
            ($port !== null ? ':' . $port : '');
    }

    public function getUserInfo(): string
    {
        return $this->user .
            ($this->password !== null ? ':' . $this->password : '');
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->isStandardPort ? null : $this->port;
    }

    public function getPath(): string
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function getQuery(): string
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function getFragment(): string
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function withScheme(string $scheme): UriInterface
    {
        $normalizedScheme = $this->normalizeScheme($scheme);

        return $this->scheme === $normalizedScheme ?
            $this :
            clone($this, [
                'scheme' => $normalizedScheme,
                'isStandardPort' => $this->isStandardPort($normalizedScheme, $this->port),
            ]);
    }

    private function normalizeScheme(string $scheme): string
    {
        if ($scheme === '') {
            return '';
        }

        if (!ctype_alpha($scheme[0])) {
            throw InvalidScheme::invalidFirstCharacter($scheme);
        }

        if (!preg_match('/^[a-zA-Z0-9+-.]+$/', $scheme)) {
            throw InvalidScheme::invalidCharacter($scheme);
        }

        return strtolower($scheme);
    }

    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        $normalizedUser = $this->normalizeUri($user);
        $normalizedPassword = $password !== null ? $this->normalizeUri($password) : null;

        if ($this->user === $normalizedUser && $this->password === $normalizedPassword) {
            return $this;
        }

        return clone($this, [
            'user' => $normalizedUser,
            'password' => $normalizedPassword,
        ]);
    }

    /** @SuppressWarnings("PHPMD.UnusedFormalParameter") */
    public function withHost(string $host): UriInterface
    {
        throw new BadMethodCallException('Not implemented');
    }

    private function normalizeHost(string $host): string
    {
        return strtolower($host);
    }

    public function withPort(?int $port): UriInterface
    {
        $normalizedPort = $port !== null ? $this->normalizePort($port) : null;

        if ($this->port === $normalizedPort) {
            return $this;
        }

        return clone($this, [
            'port' => $normalizedPort,
            'isStandardPort' => $this->isStandardPort($this->scheme, $normalizedPort),
        ]);
    }

    private function normalizePort(int $port): int
    {
        if ($port < 1 || $port > 65535) {
            throw new InvalidPort($port);
        }

        return $port;
    }

    private function isStandardPort(string $scheme, ?int $port): bool
    {
        return UriPort::isDefault($scheme, $port);
    }

    /** @SuppressWarnings("PHPMD.UnusedFormalParameter") */
    public function withPath(string $path): UriInterface
    {
        throw new BadMethodCallException('Not implemented');
    }

    /** @SuppressWarnings("PHPMD.UnusedFormalParameter") */
    public function withQuery(string $query): UriInterface
    {
        throw new BadMethodCallException('Not implemented');
    }

    /** @SuppressWarnings("PHPMD.UnusedFormalParameter") */
    public function withFragment(string $fragment): UriInterface
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function __toString(): string
    {
        throw new BadMethodCallException('Not implemented');
    }

    private function normalizeUri(string $string): string
    {
        return rawurlencode(rawurldecode($string));
    }
}
