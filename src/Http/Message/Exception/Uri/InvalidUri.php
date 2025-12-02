<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Http\Message\Exception\Uri;

use InvalidArgumentException;

class InvalidUri extends InvalidArgumentException
{
    public function __construct(string $uri)
    {
        parent::__construct("Invalid URI '$uri' provided");
    }
}
