<?php

declare(strict_types=1);

namespace IngeniozIt\Psr\Http\Message\Exception;

use InvalidArgumentException;

class InvalidHeaderValue extends InvalidArgumentException
{
    public function __construct(mixed $headerValue)
    {
        parent::__construct("Invalid header value: " . serialize($headerValue));
    }
}
