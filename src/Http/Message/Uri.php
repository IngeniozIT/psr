<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Http\Message;

use BadMethodCallException;
use IngeniozIt\Psr\Http\Message\Exception\Uri\InvalidUri;
use Psr\Http\Message\UriInterface;

use function preg_match;

readonly class Uri implements UriInterface
{
    private string $scheme;
    private string $user;
    private ?string $password;
    private ?int $port;
    private bool $isStandardPort;
    private string $host;
    private string $path;
    private string $query;
    private string $fragment;

    public function __construct(string $uri)
    {
        $parsedUri = $this->parseUri($uri);

        $this->scheme = UriNormalizer::normalizeScheme($parsedUri['scheme']);
        $this->user = UriNormalizer::normalizeUser($parsedUri['user']);
        $this->password = $parsedUri['pass'] !== '' ? UriNormalizer::normalizePassword($parsedUri['pass']) : null;
        $this->host = UriNormalizer::normalizeHost($parsedUri['host']);
        $this->port = $parsedUri['port'] !== '' ? UriNormalizer::normalizePort((int) $parsedUri['port']) : null;
        $this->isStandardPort = $this->isStandardPort($this->scheme, $this->port);
        $this->path = UriNormalizer::normalizePath($parsedUri['path']);
        $this->query = UriNormalizer::normalizeQuery($parsedUri['query']);
        $this->fragment = UriNormalizer::normalizeFragment($parsedUri['fragment']);
    }

    /** @return array{scheme: string, host: string, port: string, user: string, pass: string, path: string, query: string, fragment: string} */
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

        preg_match($pattern, $url, $matches);

        $uriParts = [
            'scheme'   => $matches['scheme'] ?? '',
            'host'     => $matches['host'] ?? '',
            'port'     => $matches['port'] ?? '',
            'user'     => $matches['user'] ?? '',
            'pass'     => $matches['pass'] ?? '',
            'path'     => $matches['path'] ?? '',
            'query'     => $matches['query'] ?? '',
            'fragment'     => $matches['fragment'] ?? '',
        ];

        if (
            $this->uriHasMissingParts($uriParts) ||
            $this->uriHasAuthorityWithoutHost($matches['authority'], $uriParts) ||
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
        return $this->path;
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getFragment(): string
    {
        return $this->fragment;
    }

    public function withScheme(string $scheme): UriInterface
    {
        $normalizedScheme = UriNormalizer::normalizeScheme($scheme);

        return $this->scheme === $normalizedScheme ?
            $this :
            clone($this, [
                'scheme' => $normalizedScheme,
                'isStandardPort' => $this->isStandardPort($normalizedScheme, $this->port),
            ]);
    }

    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        $normalizedUser = UriNormalizer::normalizeUser($user);
        $normalizedPassword = $password !== null ? UriNormalizer::normalizePassword($password) : null;

        if ($this->user === $normalizedUser && $this->password === $normalizedPassword) {
            return $this;
        }

        return clone($this, [
            'user' => $normalizedUser,
            'password' => $normalizedPassword,
        ]);
    }

    public function withHost(string $host): UriInterface
    {
        $normalizedHost = UriNormalizer::normalizeHost($host);

        if ($this->host === $normalizedHost) {
            return $this;
        }

        return clone($this, [
            'host' => $normalizedHost,
        ]);
    }

    public function withPort(?int $port): UriInterface
    {
        $normalizedPort = $port !== null ? UriNormalizer::normalizePort($port) : null;

        if ($this->port === $normalizedPort) {
            return $this;
        }

        return clone($this, [
            'port' => $normalizedPort,
            'isStandardPort' => $this->isStandardPort($this->scheme, $normalizedPort),
        ]);
    }

    private function isStandardPort(string $scheme, ?int $port): bool
    {
        return UriPort::isDefault($scheme, $port);
    }

    public function withPath(string $path): UriInterface
    {
        $normalizedPath = UriNormalizer::normalizePath($path);

        if ($this->path === $normalizedPath) {
            return $this;
        }

        return clone($this, [
            'path' => $normalizedPath,
        ]);
    }

    public function withQuery(string $query): UriInterface
    {
        $normalizedQuery = UriNormalizer::normalizeQuery($query);

        if ($this->query === $normalizedQuery) {
            return $this;
        }

        return clone($this, [
            'query' => $normalizedQuery,
        ]);
    }

    public function withFragment(string $fragment): UriInterface
    {
        $normalizedFragment = UriNormalizer::normalizeFragment($fragment);

        if ($this->fragment === $normalizedFragment) {
            return $this;
        }

        return clone($this, [
            'fragment' => $normalizedFragment,
        ]);
    }

    public function __toString(): string
    {
        throw new BadMethodCallException('Not implemented');
    }
}
