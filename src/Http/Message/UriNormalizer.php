<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Http\Message;

use IngeniozIt\Psr\Http\Message\Exception\Uri\InvalidPort;
use IngeniozIt\Psr\Http\Message\Exception\Uri\InvalidScheme;

use function ctype_alpha;
use function preg_match;
use function rawurldecode;
use function rawurlencode;
use function strtolower;

class UriNormalizer
{
    public static function normalizeScheme(string $scheme): string
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

    public static function normalizeHost(string $host): string
    {
        return strtolower($host);
    }

    public static function normalizePort(int $port): int
    {
        if ($port < 1 || $port > 65535) {
            throw new InvalidPort($port);
        }

        return $port;
    }

    public static function normalizeUri(string $string): string
    {
        return rawurlencode(rawurldecode($string));
    }
}
