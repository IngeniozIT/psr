<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Http\Message;

use BadMethodCallException;
use IngeniozIt\Psr\Http\Message\Exception\Uri\InvalidScheme;
use IngeniozIt\Psr\Http\Message\Exception\Uri\InvalidUri;
use Psr\Http\Message\UriInterface;

use function ctype_alpha;
use function parse_url;
use function preg_match;
use function strtolower;

readonly class Uri implements UriInterface
{
    private string $scheme;

    public function __construct(string $uri)
    {
        $parsedUri = parse_url($uri);
        if ($parsedUri === false) {
            throw new InvalidUri($uri);
        }

        $this->scheme = $this->normalizeScheme($parsedUri['scheme'] ?? '');
    }

    public function getScheme(): string
    {
        return $this->scheme;
    }

    public function getAuthority(): string
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function getUserInfo(): string
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function getHost(): string
    {
        throw new BadMethodCallException('Not implemented');
    }

    public function getPort(): ?int
    {
        throw new BadMethodCallException('Not implemented');
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
            ]);
    }

    private function normalizeScheme(string $scheme): string
    {
        if (!ctype_alpha($scheme[0])) {
            throw InvalidScheme::invalidFirstCharacter($scheme);
        }

        if (!preg_match('/^[a-zA-Z0-9+-.]+$/', $scheme)) {
            throw InvalidScheme::invalidCharacter($scheme);
        }

        return strtolower($scheme);
    }

    /** @SuppressWarnings("PHPMD.UnusedFormalParameter") */
    public function withUserInfo(string $user, ?string $password = null): UriInterface
    {
        throw new BadMethodCallException('Not implemented');
    }

    /** @SuppressWarnings("PHPMD.UnusedFormalParameter") */
    public function withHost(string $host): UriInterface
    {
        throw new BadMethodCallException('Not implemented');
    }

    /** @SuppressWarnings("PHPMD.UnusedFormalParameter") */
    public function withPort(?int $port): UriInterface
    {
        throw new BadMethodCallException('Not implemented');
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
}
