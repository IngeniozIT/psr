<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Http\Message;

use Psr\Http\Message\UriInterface;

use function ltrim;

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
        $parsedUri = UriNormalizer::parseUri($uri);

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
        $scheme = $this->getScheme();
        $authority = $this->getAuthority();
        $path = $this->getPath();
        $query = $this->getQuery();
        $fragment = $this->getFragment();

        return
            ($scheme !== '' ? $scheme . ':' : '') .
            ($authority !== '' ? '//' . $authority : '') .
            ($path !== '' ? '/' . ltrim($path, '/') : '') .
            ($query !== '' ? '?' . $query : '') .
            ($fragment !== '' ? '#' . $fragment : '');
    }
}
